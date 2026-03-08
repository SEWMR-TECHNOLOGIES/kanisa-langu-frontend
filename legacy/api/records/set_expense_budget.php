<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

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
    $target = isset($_POST['target']) ? $conn->real_escape_string($_POST['target']) : '';
    $expense_group_id = isset($_POST['expense_group_id']) ? intval($_POST['expense_group_id']) : null;
    $expense_name_id = isset($_POST['expense_name_id']) ? intval($_POST['expense_name_id']) : null;
    $budgeted_amount = isset($_POST['budgeted_amount']) ? floatval($_POST['budgeted_amount']) : null;
    $start_date = isset($_POST['start_date']) ? $conn->real_escape_string($_POST['start_date']) : '';
    $end_date = isset($_POST['end_date']) ? $conn->real_escape_string($_POST['end_date']) : '';
    $budget_description = isset($_POST['budget_description']) ? $conn->real_escape_string($_POST['budget_description']) : '';
    $sub_parish_id = isset($_POST['sub_parish_id']) ? intval($_POST['sub_parish_id']) : null;
    $community_id = isset($_POST['community_id']) ? intval($_POST['community_id']) : null;
    $group_id = isset($_POST['group_id']) ? intval($_POST['group_id']) : null;

    // Validate required parameters common to all cases
    if (empty($target) || !in_array($target, ['head-parish', 'sub-parish', 'community', 'group'])) {
        echo json_encode(["success" => false, "message" => "Invalid target specified"]);
        exit();
    }
    // Check each required field individually and give a specific error message
    if (empty($expense_group_id)) {
        echo json_encode(["success" => false, "message" => "Expense group is required"]);
        exit();
    }
    
    if (empty($expense_name_id)) {
        echo json_encode(["success" => false, "message" => "Expense name is required"]);
        exit();
    }
    
    if (empty($budgeted_amount)) {
        echo json_encode(["success" => false, "message" => "Budgeted amount is required"]);
        exit();
    }
    
    // Ensure budgeted amount is greater than zero
    if ($budgeted_amount <= 0) {
        echo json_encode(["success" => false, "message" => "Budgeted amount must be greater than zero"]);
        exit();
    }
    
    if (empty($start_date)) {
        echo json_encode(["success" => false, "message" => "Start date is required"]);
        exit();
    }
    
    if (empty($end_date)) {
        echo json_encode(["success" => false, "message" => "End date is required"]);
        exit();
    }

    // Check that start_date is not later than end_date
    if (strtotime($start_date) > strtotime($end_date)) {
        echo json_encode(["success" => false, "message" => "Start date cannot be greater than end date"]);
        exit();
    }

    // Initialize variables
    $table = '';
    $unique_condition = '';
    $params = [];
    $types = '';

    // Additional validation and setup based on target
    switch ($target) {
        case 'head-parish':
            $table = 'head_parish_expense_budgets';
            $unique_condition = "head_parish_id = ? AND expense_group_id = ? AND expense_name_id = ? AND start_date = ? AND end_date = ?";
            $params = [$head_parish_id, $expense_group_id, $expense_name_id, $start_date, $end_date];
            $types = "iisss"; // Corresponds to (head_parish_id, expense_group_id, expense_name_id, start_date, end_date)
            break;

        case 'sub-parish':
            if (empty($sub_parish_id)) {
                echo json_encode(["success" => false, "message" => "Please select a valid Sub Parish"]);
                exit();
            }
            $table = 'sub_parish_expense_budgets';
            $unique_condition = "head_parish_id = ? AND sub_parish_id = ? AND expense_group_id = ? AND expense_name_id = ? AND start_date = ? AND end_date = ?";
            $params = [$head_parish_id, $sub_parish_id, $expense_group_id, $expense_name_id, $start_date, $end_date];
            $types = "iiisss"; // Corresponds to (head_parish_id, sub_parish_id, expense_group_id, expense_name_id, start_date, end_date)
            break;

        case 'community':
            if (empty($sub_parish_id) || empty($community_id)) {
                echo json_encode(["success" => false, "message" => "Please select valid Sub Parish and Community"]);
                exit();
            }
            $table = 'community_expense_budgets';
            $unique_condition = "head_parish_id = ? AND sub_parish_id = ? AND community_id = ? AND expense_group_id = ? AND expense_name_id = ? AND start_date = ? AND end_date = ?";
            $params = [$head_parish_id, $sub_parish_id, $community_id, $expense_group_id, $expense_name_id, $start_date, $end_date];
            $types = "iiissss"; // Corresponds to (head_parish_id, sub_parish_id, community_id, expense_group_id, expense_name_id, start_date, end_date)
            break;

        case 'group':
            if (empty($group_id)) {
                echo json_encode(["success" => false, "message" => "Please select a valid Group"]);
                exit();
            }
            $table = 'group_expense_budgets';
            $unique_condition = "head_parish_id = ? AND group_id = ? AND expense_group_id = ? AND expense_name_id = ? AND start_date = ? AND end_date = ?";
            $params = [$head_parish_id, $group_id, $expense_group_id, $expense_name_id, $start_date, $end_date];
            $types = "iiisss"; // Corresponds to (head_parish_id, group_id, expense_group_id, expense_name_id, start_date, end_date)
            break;

        default:
            echo json_encode(["success" => false, "message" => "Unknown budget target"]);
            exit();
    }

    // Check if record already exists
    $check_query = "SELECT budget_id FROM $table WHERE $unique_condition";
    $stmt = $conn->prepare($check_query);
    
    // Manually bind parameters for the check query
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update existing record
        $row = $result->fetch_assoc();
        $budget_id = $row['budget_id'];
        $update_query = "UPDATE $table SET budgeted_amount = ?, budget_description = ? WHERE budget_id = ?";
        $stmt = $conn->prepare($update_query);
        // Manually bind parameters for the update
        $stmt->bind_param("dsi", $budgeted_amount, $budget_description, $budget_id);
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Budget updated successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to update budget: " . $stmt->error]);
        }
    } else {
        // Insert new record
        $insert_query = "INSERT INTO $table (head_parish_id, expense_group_id, expense_name_id, budgeted_amount, start_date, end_date, budget_description" . 
                        ($target == 'sub-parish' ? ", sub_parish_id" : "") . 
                        ($target == 'community' ? ", sub_parish_id, community_id" : "") . 
                        ($target == 'group' ? ", group_id" : "") . 
                        ") VALUES (?, ?, ?, ?, ?, ?, ?" . str_repeat(", ?", ($target == 'sub-parish' ? 1 : ($target == 'community' ? 2 : ($target == 'group' ? 1 : 0)))) . ")";

        $stmt = $conn->prepare($insert_query);

        // Manually bind parameters for the insert
        if ($target == 'sub-parish') {
            $stmt->bind_param("iiidsss", $head_parish_id, $expense_group_id, $expense_name_id, $budgeted_amount, $start_date, $end_date, $budget_description, $sub_parish_id);
        } elseif ($target == 'community') {
            $stmt->bind_param("iiidsssii", $head_parish_id, $expense_group_id, $expense_name_id, $budgeted_amount, $start_date, $end_date, $budget_description, $sub_parish_id, $community_id);
        } elseif ($target == 'group') {
            $stmt->bind_param("iiidsssi", $head_parish_id, $expense_group_id, $expense_name_id, $budgeted_amount, $start_date, $end_date, $budget_description, $group_id);
        } else {
            // Default case for head-parish
            $stmt->bind_param("iiidsss", $head_parish_id, $expense_group_id, $expense_name_id, $budgeted_amount, $start_date, $end_date, $budget_description);
        }

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Budget recorded successfully", "budget_id" => $stmt->insert_id]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to record budget: " . $stmt->error]);
        }
    }
    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

// Close the database connection
$conn->close();
?>
