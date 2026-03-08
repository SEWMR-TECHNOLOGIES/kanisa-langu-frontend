<?php
header('Content-Type: application/json');
session_start();
date_default_timezone_set('Africa/Nairobi');

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit();
}

// Check session role
if (!isset($_SESSION['head_parish_admin_role'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized: No role found"]);
    exit();
}

$head_parish_id = $_SESSION['head_parish_id'];
$transaction_type = 'expense';
$role = $_SESSION['head_parish_admin_role'];
// Treat admin as chairperson
if ($role === 'admin') $role = 'chairperson';

if ($role !== 'chairperson' && $role !== 'pastor') {
    echo json_encode(["success" => false, "message" => "Unauthorized: Only chairperson or pastor can respond"]);
    exit();
}

// Get input JSON
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(["success" => false, "message" => "Invalid JSON"]);
    exit();
}

$request_id = isset($input['request_id']) ? intval($input['request_id']) : 0;
$target = isset($input['target']) ? $input['target'] : '';
$approval = isset($input['approval']) ? (bool)$input['approval'] : null;
$rejection_reason = isset($input['rejection_reason']) ? trim($input['rejection_reason']) : '';
$approved_amount = isset($input['approved_amount']) ? $input['approved_amount'] : null;

if (!$request_id || !$target || !is_bool($approval)) {
    echo json_encode(["success" => false, "message" => "Missing or invalid input data"]);
    exit();
}

// Validate amount only if pastor approves
if ($role === 'pastor' && $approval) {
    if ($approved_amount === null || !is_numeric($approved_amount) || $approved_amount < 0) {
        echo json_encode(["success" => false, "message" => "Invalid approved amount"]);
        exit();
    }
}

// Map target to table
$tables = [
    'head-parish' => 'head_parish_expense_requests',
    'sub-parish' => 'sub_parish_expense_requests',
    'community' => 'community_expense_requests',
    'group' => 'group_expense_requests'
];

if (!array_key_exists($target, $tables)) {
    echo json_encode(["success" => false, "message" => "Invalid target"]);
    exit();
}

$table = $tables[$target];

// Fetch the request and check conditions
$stmt = $conn->prepare("SELECT pastor_approval, chairperson_approval, request_amount FROM $table WHERE request_id = ?");
$stmt->bind_param("i", $request_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Request not found"]);
    exit();
}

$request = $result->fetch_assoc();

// Chairperson cannot reject if pastor already approved
if ($role === 'chairperson' && $approval === false && $request['pastor_approval'] === 1) {
    echo json_encode(["success" => false, "message" => "Chairperson cannot reject a request already approved by pastor"]);
    exit();
}

// Prepare update query parts
$now = date('Y-m-d H:i:s');
if ($role === 'pastor') {
    $approval_col = 'pastor_approval';
    $seen_col = 'pastor_seen';
    $rejection_col = 'pastor_rejection_remarks';
    $approval_datetime_col = 'pastor_approval_datetime';
} else { // chairperson
    $approval_col = 'chairperson_approval';
    $seen_col = 'chairperson_seen';
    $rejection_col = 'chairperson_rejection_remarks';
    $approval_datetime_col = 'chairperson_approval_datetime';
}

$approvalInt = $approval ? 1 : 0;

// If pastor, update status and approved amount
if ($role === 'pastor') {
    $status = $approval ? 'Approved' : 'Rejected';
    $final_approved_amount = $approved_amount;

    $updateSql = "UPDATE $table SET 
        $approval_col = ?, 
        $rejection_col = ?, 
        $approval_datetime_col = ?, 
        $seen_col = 1,
        request_amount = ?, 
        request_status = ?
        WHERE request_id = ?";

    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param(
        "issdsi",
        $approvalInt,
        $rejection_reason,
        $now,
        $final_approved_amount,
        $status,
        $request_id
    );
} else {
    // Chairperson: update only approval, remarks, timestamp, seen
    $updateSql = "UPDATE $table SET 
        $approval_col = ?, 
        $rejection_col = ?, 
        $approval_datetime_col = ?, 
        $seen_col = 1
        WHERE request_id = ?";

    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param(
        "issi",
        $approvalInt,
        $rejection_reason,
        $now,
        $request_id
    );
}

if ($stmt->execute()) {
    $action = $approval ? 'approved' : 'rejected';
    $message = "Request $action by $role";

    if ($approval) {
        $smsSent = sendExpenseRequestSms($conn, $head_parish_id, $role, $target);
        // error_log("Testing");
    }

    // Get expense details using the helper function
    $expenseDetails = getExpenseRequestDetails($conn, $target, $request_id);
    // If pastor approved, notify admins
    if ($role === 'pastor' && $approval && $target != 'head-parish') {

        if ($expenseDetails) {
            // Determine management level and display name
            switch ($target) {
                case 'head-parish':
                    $management_level = 'head-parish';
                    $level_display_name = ''; // optional, head parish may not need a name
                    break;
                case 'sub-parish':
                    $management_level = 'sub-parish';
                    $level_display_name = getSubParishName($expenseDetails['sub_parish_id'], $conn);
                    break;
                case 'community':
                    $management_level = 'community';
                    $level_display_name = getCommunityName($expenseDetails['community_id'], $conn);
                    break;
                case 'group':
                    $management_level = 'group';
                    $level_display_name = getGroupName($expenseDetails['group_id'], $conn);
                    break;
            }

           $totals = getTotalRevenueAndExpenses(
                $conn,
                $management_level,
                $head_parish_id,
                $expenseDetails['sub_parish_id'],
                $expenseDetails['community_id'],
                $expenseDetails['group_id']
            );

            $total_revenue = $totals['total_revenue'] ?? 0.00;
            $total_expense = $totals['total_expense'] ?? 0.00;

            $admins_result = getSystemAdmins(
                $conn,
                $management_level,
                $head_parish_id,
                $expenseDetails['sub_parish_id'],
                $expenseDetails['community_id'],
                $expenseDetails['group_id']
            );
            $transaction_date = $expenseDetails['expense_date'] ?? date('Y-m-d');
            if (!empty($admins_result['success']) && $admins_result['success'] && !empty($admins_result['admins'])) {
                foreach ($admins_result['admins'] as $admin) {
                    notifyAdminsSMS(
                        $conn,
                        $management_level,
                        $level_display_name,
                        $transaction_type,
                        $expenseDetails['expense_name'],
                        $final_approved_amount,
                        $total_revenue,
                        $total_expense,
                        $admin['name'],
                        $admin['phone'],
                        $head_parish_id,
                        $transaction_date
                    );
                }
            }
        }
    }
    $recordedBy = $expenseDetails['recorded_by'] ?? $_SESSION['head_parish_admin_id'];

    if ($role === 'pastor' && $approval && $expenseDetails) {
        $txnSql = "INSERT INTO transactions 
            (account_id, head_parish_id, sub_parish_id, community_id, group_id, management_level, amount, type, description, txn_date, recorded_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmtTxn = $conn->prepare($txnSql);

        // Handle nullable IDs
        $subParishId   = $expenseDetails['sub_parish_id'] ?? null;
        $communityId   = $expenseDetails['community_id'] ?? null;
        $groupId       = $expenseDetails['group_id'] ?? null;
        $recordedBy    = $expenseDetails['recorded_by'] ?? $_SESSION['head_parish_admin_id'];

        // Bind params, use "s" for nullable integers
        $stmtTxn->bind_param(
            "iissssdsssis",
            $expenseDetails['account_id'],  // i
            $head_parish_id,                // i
            $subParishId,                   // s (nullable)
            $communityId,                   // s (nullable)
            $groupId,                       // s (nullable)
            $target,                        // s
            $final_approved_amount,         // d
            $transaction_type,              // s
            $expenseDetails['expense_name'],// s
            $expenseDetails['expense_date'],// s
            $recordedBy,                    // i
            $now                             // s
        );

        $stmtTxn->execute();
        $stmtTxn->close();
    }

    echo json_encode(["success" => true, "message" => $message]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update request: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
