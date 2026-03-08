<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');

// Check if head_parish_id is in session
if (!isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Head Parish ID is missing from session"]);
    exit();
}

$head_parish_id = $_SESSION['head_parish_id'];
$transaction_type = 'revenue';
// Check the database connection
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target = isset($_POST['target']) ? $conn->real_escape_string($_POST['target']) : '';
    $revenue_stream_id = isset($_POST['revenue_stream_id']) ? intval($_POST['revenue_stream_id']) : 0;
    $revenue_amount = isset($_POST['revenue_amount']) ? floatval($_POST['revenue_amount']) : 0.00;
    $payment_method = isset($_POST['payment_method']) ? $conn->real_escape_string($_POST['payment_method']) : 'Cash';
    $revenue_date = isset($_POST['revenue_date']) ? $conn->real_escape_string($_POST['revenue_date']) : null;
    $description = isset($_POST['description']) ? $conn->real_escape_string($_POST['description']) : null;
    $sub_parish_id = null;
    $community_id = null;
    $group_id = null;

    // Validate common inputs
    if (empty($target)) {
        echo json_encode(["success" => false, "message" => "Target is required"]);
        exit();
    }
    if ($revenue_stream_id <= 0) {
        echo json_encode(["success" => false, "message" => "Valid revenue stream is required"]);
        exit();
    }
    if ($revenue_amount <= 0) {
        echo json_encode(["success" => false, "message" => "Revenue amount must be greater than 0"]);
        exit();
    }
    if (empty($revenue_date)) {
        echo json_encode(["success" => false, "message" => "Revenue date is required"]);
        exit();
    }
    
    $management_level = null;
    
    // Begin transaction
    $conn->begin_transaction();

    // Determine the target table and required session admin ID
    $table = '';
    $recorded_by = null;
    $stmt = null;

    switch ($target) {
        case 'head_parish':
            $management_level = 'head-parish';
            // Check if head parish admin ID is in session
            if (!isset($_SESSION['head_parish_admin_id'])) {
                echo json_encode(["success" => false, "message" => "Head Parish Admin ID is missing from session"]);
                exit();
            }
            $recorded_by = $_SESSION['head_parish_admin_id'];

            // Get the required service_number for head parish revenues
            $service_number = isset($_POST['service_number']) ? intval($_POST['service_number']) : 0;
            if ($service_number <= 0) {
                echo json_encode(["success" => false, "message" => "Service number is required for head parish revenue"]);
                exit();
            }
            // Get the required sub_parish_id
            $sub_parish_id = isset($_POST['sub_parish_id']) ? intval($_POST['sub_parish_id']) : 0;
            if ($sub_parish_id <= 0) {
                echo json_encode(["success" => false, "message" => "Sub Parish is required for head parish revenue"]);
                exit();
            }
            
            $table = 'head_parish_revenues';
            $sql = "INSERT INTO $table (revenue_stream_id, head_parish_id, sub_parish_id, service_number, revenue_amount, payment_method, recorded_by, revenue_date, description) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiiidssss", $revenue_stream_id, $head_parish_id, $sub_parish_id, $service_number, $revenue_amount, $payment_method, $recorded_by, $revenue_date, $description);
            break;
            
        case 'other':
            $management_level = 'head-parish';
            // Check if head parish admin ID is in session
            if (!isset($_SESSION['head_parish_admin_id'])) {
                echo json_encode(["success" => false, "message" => "Head Parish Admin ID is missing from session"]);
                exit();
            }
            $recorded_by = $_SESSION['head_parish_admin_id'];

            // Get the required service_number for head parish revenues
            $service_number = isset($_POST['service_number']) ? intval($_POST['service_number']) : 0;
            if ($service_number <= 0) {
                echo json_encode(["success" => false, "message" => "Service number is required for head parish revenue"]);
                exit();
            }

            $table = 'other_head_parish_revenues';
            $sql = "INSERT INTO $table (revenue_stream_id, head_parish_id, service_number, revenue_amount, payment_method, recorded_by, revenue_date, description) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiidssss", $revenue_stream_id, $head_parish_id, $service_number, $revenue_amount, $payment_method, $recorded_by, $revenue_date, $description);
            break;
            
        case 'sub_parish':
            $management_level = 'sub-parish';
            // Check if sub parish admin ID is in session
            if (!isset($_SESSION['head_parish_admin_id'])) {
                echo json_encode(["success" => false, "message" => "Head Parish Admin ID is missing from session"]);
                exit();
            }
            $recorded_by = null;

            // Get the required sub_parish_id
            $sub_parish_id = isset($_POST['sub_parish_id']) ? intval($_POST['sub_parish_id']) : 0;
            if ($sub_parish_id <= 0) {
                echo json_encode(["success" => false, "message" => "Sub Parish is required for sub parish revenue"]);
                exit();
            }

            $table = 'sub_parish_revenues';
            $sql = "INSERT INTO $table (head_parish_id, sub_parish_id, revenue_stream_id, revenue_amount, payment_method, recorded_by, revenue_date, description) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiiidsss", $head_parish_id, $sub_parish_id, $revenue_stream_id, $revenue_amount, $payment_method, $recorded_by, $revenue_date, $description);
            break;

        case 'community':
            $management_level = 'community';
            // Check if community admin ID is in session
            if (!isset($_SESSION['head_parish_admin_id'])) {
                echo json_encode(["success" => false, "message" => "Head Parish ID is missing from session"]);
                exit();
            }
            $recorded_by = null;

            // Get the required sub_parish_id and community_id
            $sub_parish_id = isset($_POST['sub_parish_id']) ? intval($_POST['sub_parish_id']) : 0;
            $community_id = isset($_POST['community_id']) ? intval($_POST['community_id']) : 0;
            if ($sub_parish_id <= 0 || $community_id <= 0) {
                echo json_encode(["success" => false, "message" => "Sub Parish and Community are required for community revenue"]);
                exit();
            }

            $table = 'community_revenues';
            $sql = "INSERT INTO $table (head_parish_id, sub_parish_id, community_id, revenue_stream_id, revenue_amount, payment_method, recorded_by, revenue_date, description) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiiidssss", $head_parish_id, $sub_parish_id, $community_id, $revenue_stream_id, $revenue_amount, $payment_method, $recorded_by, $revenue_date, $description);
            break;

        case 'group':
            $management_level = 'group';
            // Check if group admin ID is in session
            if (!isset($_SESSION['head_parish_admin_id'])) {
                echo json_encode(["success" => false, "message" => "Head Parish ID is missing from session"]);
                exit();
            }
            $recorded_by = null;

            // Get the required group_id
            $group_id = isset($_POST['group_id']) ? intval($_POST['group_id']) : 0;
            if ($group_id <= 0) {
                echo json_encode(["success" => false, "message" => "Group is required for group revenue"]);
                exit();
            }

            $table = 'group_revenues';
            $sql = "INSERT INTO $table (head_parish_id, group_id, revenue_stream_id, revenue_amount, payment_method, recorded_by, revenue_date, description) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiidssss", $head_parish_id, $group_id, $revenue_stream_id, $revenue_amount, $payment_method, $recorded_by, $revenue_date, $description);
            break;

        default:
            echo json_encode(["success" => false, "message" => "Invalid target"]);
            exit();
    }

    // Execute the statement
    if ($stmt->execute()) {
        // Update the account balance after revenue is recorded
        $account_id = get_account_id_by_revenue_stream($conn, $revenue_stream_id, 'head_parish');
        if ($account_id) {
            $update_success = update_account_balance($conn, $account_id, $revenue_amount, 'head_parish');
            if (!$update_success) {
                $conn->rollback(); // Rollback transaction if account balance update fails
                echo json_encode(["success" => false, "message" => "Revenue recorded but failed to update account balance"]);
                exit();
            }
        }
    
        $conn->commit(); // Commit transaction
    
        // Get totals
        $totals = getTotalRevenueAndExpenses($conn, $management_level, $head_parish_id, $sub_parish_id, $community_id, $group_id);
        $total_revenue = $totals['total_revenue'] ?? 0.00;
        $total_expense = $totals['total_expense'] ?? 0.00;
    
        // Get revenue stream name
        $revenue_stream_name = getRevenueStreamName($conn, $head_parish_id, $revenue_stream_id) ?? null;
    
        // Notify admins if management level is NOT head-parish
        if (strtolower($management_level) !== 'head-parish') {
            // Get all admins for this management level
            $admins_result = getSystemAdmins($conn, $management_level, $head_parish_id, $sub_parish_id, $community_id, $group_id);
            if (!empty($admins_result['success']) && $admins_result['success'] && !empty($admins_result['admins'])) {
                // Get management level display names
                $level_names = getManagementLevelNames($conn, $management_level, $head_parish_id, $sub_parish_id, $community_id, $group_id);
                $level_display_name = '';
                switch (strtolower($management_level)) {
                    case 'sub-parish':
                        $level_display_name = '' . ($level_names['sub_parish_name'] ?? '');
                        break;
                    case 'community':
                        $level_display_name = '' . ($level_names['community_name'] ?? '');
                        break;
                    case 'group':
                        $level_display_name = $level_names['group_name'] ?? '';
                        break;
                }
            
                // Loop through each admin and send SMS
                foreach ($admins_result['admins'] as $admin) {
                    notifyAdminsSMS(
                        $conn,
                        $management_level,
                        $level_display_name,
                        $transaction_type,
                        $revenue_stream_name,
                        $revenue_amount,
                        $total_revenue,
                        $total_expense,
                        $admin['name'],
                        $admin['phone'],
                        $head_parish_id,
                        $revenue_date
                    );
                }
            }
        }
    
        echo json_encode(["success" => true, "message" => "Revenue recorded successfully"]);
    } else {
        $conn->rollback(); // Rollback transaction on failure
        echo json_encode(["success" => false, "message" => "Failed to record revenue: " . $stmt->error]);
    }


    // Close the statement
    $stmt->close();
} else {
    // If the request method is not POST
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

// Close the database connection
$conn->close();
?>
