<?php
header('Content-Type: application/json');
date_default_timezone_set('Africa/Nairobi');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');

// Ensure head_parish_id is in session
if (!isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Head Parish ID is missing from session"]);
    exit();
}

$head_parish_id = $_SESSION['head_parish_id'];

// Check database connection
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data and decode it
    $data = json_decode(file_get_contents('php://input'), true);
    
    $target = isset($data['target']) ? $conn->real_escape_string($data['target']) : '';
    $items = isset($data['items']) ? $data['items'] : [];

    // Validate required parameters
    if (empty($target) || !in_array($target, ['head-parish', 'sub-parish', 'community', 'group'])) {
        echo json_encode(["success" => false, "message" => "Invalid target specified"]);
        exit();
    }

    if (empty($items)) {
        echo json_encode(["success" => false, "message" => "No items provided"]);
        exit();
    }
    
    // Extract description from request data
    $description = isset($data['description']) ? trim($data['description']) : '';
    
    if ($description === '') {
        echo json_encode(["success" => false, "message" => "Description of the expense is required"]);
        exit();
    }

 
 $group_ids = array_unique(array_map(function($item) {
    return is_array($item) && isset($item['expenseGroupId']) ? intval($item['expenseGroupId']) : 0;
}, $items));

    error_log(print_r($items, true));

    list($valid, $error, $accountId) = validateExpenseGroupsAccount($conn, $target, $head_parish_id, $group_ids);

    if (!$valid) {
        echo json_encode(["success" => false, "message" => $error]);
        exit();
    }


    $expense_group_id = $group_ids[0];
    $recorded_by = $_SESSION['head_parish_admin_id'] ?? null; 
    
    // 1. Create grouped expense request record based on target
    switch ($target) {
        case 'head-parish':
            $groupedInsertSql = "INSERT INTO head_parish_grouped_expense_requests (head_parish_id, description, recorded_by, account_id) VALUES (?, ?, ?, ?)";
            $updateSql = "UPDATE head_parish_grouped_expense_requests SET submission_datetime = ? WHERE grouped_request_id = ?";
            $stmtGroup = $conn->prepare($groupedInsertSql);
            $stmtGroup->bind_param("isii", $head_parish_id, $description, $recorded_by, $accountId);
            break;
    
        case 'sub-parish':
            $sub_parish_id = intval($items[0]['subParishId']);
            $groupedInsertSql = "INSERT INTO sub_parish_grouped_expense_requests (head_parish_id, sub_parish_id, description, recorded_by, account_id) VALUES (?, ?, ?, ?, ?)";
            $updateSql = "UPDATE sub_parish_grouped_expense_requests SET submission_datetime = ? WHERE grouped_request_id = ?";
            $stmtGroup = $conn->prepare($groupedInsertSql);
            $stmtGroup->bind_param("isisi", $head_parish_id, $sub_parish_id, $description, $recorded_by, $accountId);
            break;
    
        case 'community':
            $sub_parish_id = intval($items[0]['subParishId']);
            $community_id = intval($items[0]['communityId']);
            $groupedInsertSql = "INSERT INTO community_grouped_expense_requests (head_parish_id, sub_parish_id, community_id, description, recorded_by, account_id) VALUES (?, ?, ?, ?, ?, ?)";
            $updateSql = "UPDATE community_grouped_expense_requests SET submission_datetime = ? WHERE grouped_request_id = ?";
            $stmtGroup = $conn->prepare($groupedInsertSql);
            $stmtGroup->bind_param("iisiii", $head_parish_id, $sub_parish_id, $community_id, $description, $recorded_by, $accountId);
            break;
    
        case 'group':
            $group_id = intval($items[0]['groupId']);
            $groupedInsertSql = "INSERT INTO group_grouped_expense_requests (head_parish_id, group_id, description, recorded_by, account_id) VALUES (?, ?, ?, ?, ?)";
            $updateSql = "UPDATE group_grouped_expense_requests SET submission_datetime = ? WHERE grouped_request_id = ?";
            $stmtGroup = $conn->prepare($groupedInsertSql);
            $stmtGroup->bind_param("iisii", $head_parish_id, $group_id, $description, $recorded_by, $accountId);
            break;
    
        default:
            echo json_encode(["success" => false, "message" => "Unsupported target"]);
            exit();
    }


    if (!$stmtGroup->execute()) {
        echo json_encode(["success" => false, "message" => "Failed to create grouped expense request: " . $stmtGroup->error]);
        exit();
    }

    $grouped_request_id = $stmtGroup->insert_id;
    $stmtGroup->close();
    
     // 2. Insert each expense request linked to this grouped_request_id
    
    // Prepare SQL statements
    $insert_query = '';
    $stmt = null;
    
    // Specify the filter column
    $referenceId = '';
    $request_datetime = null;
    foreach ($items as $item) {
        $expense_group_id = intval($item['expenseGroupId']);
        $expense_name_id = intval($item['expenseNameId']);
        $request_amount = floatval($item['requestAmount']);
        $request_description = $conn->real_escape_string($item['requestDescription']);
        // Convert request date to datetime
        $request_date_raw = $item['requestDate'] ?? '';
        if (empty($request_date_raw)) {
            echo json_encode(["success" => false, "message" => "Request Date is missing"]);
            exit();
        }

        $today = date('Y-m-d');
        $current_time = date('H:i:s');

        if ($request_date_raw === $today) {
            $request_datetime = (new DateTime('now', new DateTimeZone('Africa/Nairobi')))->format('Y-m-d H:i:s');
        } else {
            $request_datetime = $request_date_raw . ' ' . $current_time;
        }

        switch ($target) {
            case 'head-parish':
                $insert_query = "INSERT INTO head_parish_expense_requests (head_parish_id, expense_group_id, expense_name_id, request_amount, request_datetime, request_description, grouped_request_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($insert_query);
                $stmt->bind_param("iiisssi", $head_parish_id, $expense_group_id, $expense_name_id, $request_amount, $request_datetime, $request_description, $grouped_request_id);
                $referenceId = $head_parish_id;
                break;

            case 'sub-parish':
                $sub_parish_id = intval($item['subParishId']);
                $insert_query = "INSERT INTO sub_parish_expense_requests (head_parish_id, sub_parish_id, expense_group_id, expense_name_id, request_amount, request_datetime, request_description, grouped_request_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($insert_query);
                $stmt->bind_param("iiissssi", $head_parish_id, $sub_parish_id, $expense_group_id, $expense_name_id, $request_amount, $request_datetime, $request_description, $grouped_request_id);
                $referenceId = $sub_parish_id;
                break;

            case 'community':
                $sub_parish_id = intval($item['subParishId']);
                $community_id = intval($item['communityId']);
                $insert_query = "INSERT INTO community_expense_requests (head_parish_id, sub_parish_id, community_id, expense_group_id, expense_name_id, request_amount, request_datetime, request_description, grouped_request_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($insert_query);
                $stmt->bind_param("iiiiiissi", $head_parish_id, $sub_parish_id, $community_id, $expense_group_id, $expense_name_id, $request_amount, $request_datetime, $request_description, $grouped_request_id);
                $referenceId = $community_id;
                break;

            case 'group':
                $group_id = intval($item['groupId']);
                $insert_query = "INSERT INTO group_expense_requests (head_parish_id, group_id, expense_group_id, expense_name_id, request_amount, request_datetime, request_description, grouped_request_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($insert_query);
                $stmt->bind_param("iiiiissi", $head_parish_id, $group_id, $expense_group_id, $expense_name_id, $request_amount, $request_datetime, $request_description, $grouped_request_id);
                $referenceId = $group_id;
                break;
        }

        // Execute the statement
        if (!$stmt->execute()) {
            echo json_encode(["success" => false, "message" => "Failed to record request: " . $stmt->error]);
            exit();
        }
    }

    $stmtUpdate = $conn->prepare($updateSql);
    $stmtUpdate->bind_param("si", $request_datetime, $grouped_request_id);

    if (!$stmtUpdate->execute()) {
        echo json_encode(["success" => false, "message" => "Failed to update submission datetime: " . $stmtUpdate->error]);
        exit();
    }
    $stmtUpdate->close();
    
    // Build the message for the notification
    $notificationMessage = '<p>This is to notify you of a new expense request. Find a detailed summary below:</strong>.</p>';
    
    // Start the table
    $notificationMessage .= '
    <table style="width:100%; border-collapse: collapse; margin-top: 10px;">
        <thead>
            <tr>
                <th style="border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2; text-align: left;">Expense Group</th>
                <th style="border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2; text-align: left;">Expense Name</th>
                <th style="border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2; text-align: right;">Amount Requested</th>
                <th style="border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2; text-align: left;">Description</th>
            </tr>
        </thead>
        <tbody>';
    
    // Loop through each item to create a row in the table
    foreach ($items as $item) {
        $notificationMessage .= '
        <tr>
            <td style="border: 1px solid #ddd; padding: 8px;">' . htmlspecialchars($item['expenseGroupName']) . '</td>
            <td style="border: 1px solid #ddd; padding: 8px;">' . htmlspecialchars($item['expenseRequestName']) . '</td>
            <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">' . number_format($item['requestAmount'], 0) . '</td>
            <td style="border: 1px solid #ddd; padding: 8px;">' . htmlspecialchars($item['requestDescription']) . '</td>
        </tr>';
    }
    
    // Close the table and add the closing message
    $notificationMessage .= '
        </tbody>
    </table>';
    
    $notificationMessage .= '<p>We kindly request you to review this expense request at your earliest convenience, as your prompt attention is greatly appreciated.</p>';
    $notificationMessage .= '<p>Best regards,<br>The Finance Team</p>';


    // Send the notification
    $role = 'accountant';
    $subject = "Expense Request Notification";
    $title = "New Expense Request";
    $adminId = $referenceId;
    $notificationSent = sendExpenseRequestNotification($conn, $target, $role, $notificationMessage, $subject, $title, $adminId);
    $smsSent = sendExpenseRequestSms($conn, $head_parish_id, $role, $target, $description);
    if ($notificationSent) {
        echo json_encode(["success" => true, "message" => "All requests recorded successfully and notification sent"]);
    } else {
        echo json_encode(["success" => true, "message" => "All requests recorded successfully, but notification failed"]);
    }
    // echo json_encode(["success" => true, "message" => "All requests recorded successfully"]);

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

// Close the database connection
$conn->close();
?>
