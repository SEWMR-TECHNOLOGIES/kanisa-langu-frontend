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
    $account_id = isset($_POST['account_id']) ? intval($_POST['account_id']) : null;
    $sub_parish_id = isset($_POST['sub_parish_id']) ? intval($_POST['sub_parish_id']) : null;
    $community_id = isset($_POST['community_id']) ? intval($_POST['community_id']) : null;
    $group_id = isset($_POST['group_id']) ? intval($_POST['group_id']) : null;
    $expense_budget_target_amount = isset($_POST['expense_budget_target_amount']) ? floatval($_POST['expense_budget_target_amount']) : null;
    $expense_budget_target_start_date = isset($_POST['start_date']) ? $conn->real_escape_string($_POST['start_date']) : '';
    $expense_budget_target_end_date = isset($_POST['end_date']) ? $conn->real_escape_string($_POST['end_date']) : '';

    // Validate the target input
    if (empty($target) || !in_array($target, ['head-parish', 'sub-parish', 'community', 'group'])) {
        echo json_encode(["success" => false, "message" => "Invalid target specified"]);
        exit();
    }

    // Validate the account ID input
    if (empty($account_id)) {
        echo json_encode(["success" => false, "message" => "Please select a valid bank account"]);
        exit();
    }

    // Validate the revenue target amount
    if (empty($expense_budget_target_amount) || $expense_budget_target_amount <= 0) {
        echo json_encode(["success" => false, "message" => "Revenue target amount must be a positive number"]);
        exit();
    }

    // Validate start and end dates
    $current_year = date('Y');
    $expected_start_date = "$current_year-01-01";
    $expected_end_date = "$current_year-12-31";

    if ($expense_budget_target_start_date !== $expected_start_date) {
        echo json_encode(["success" => false, "message" => "Start date must be in the format Y-01-01 for the current year"]);
        exit();
    }

    if ($expense_budget_target_end_date !== $expected_end_date) {
        echo json_encode(["success" => false, "message" => "End date must be in the format Y-12-31 for the current year"]);
        exit();
    }

    // Define table and condition for the target
    $table = '';
    $where_clause = '';
    $insert_columns = '';
    $insert_values = '';

    switch ($target) {
        case 'head-parish':
            $table = 'head_parish_annual_expense_budget';
            $where_clause = "head_parish_id = $head_parish_id AND expense_budget_target_start_date = '$expense_budget_target_start_date' AND expense_budget_target_end_date = '$expense_budget_target_end_date' AND bank_account_id = $account_id";
            $insert_columns = "(head_parish_id, bank_account_id, expense_budget_target_start_date, expense_budget_target_end_date, expense_budget_target_amount)";
            $insert_values = "(?, ?, ?, ?, ?)";
            break;

        case 'sub-parish':
            if ($sub_parish_id <= 0) {
                echo json_encode(["success" => false, "message" => "Sub Parish is required for sub-parish target"]);
                exit();
            }
            $table = 'sub_parish_annual_expense_budget';
            $where_clause = "head_parish_id = $head_parish_id AND sub_parish_id = $sub_parish_id AND expense_budget_target_start_date = '$expense_budget_target_start_date' AND expense_budget_target_end_date = '$expense_budget_target_end_date' AND bank_account_id = $account_id";
            $insert_columns = "(head_parish_id, sub_parish_id, bank_account_id, expense_budget_target_start_date, expense_budget_target_end_date, expense_budget_target_amount)";
            $insert_values = "(?, ?, ?, ?, ?, ?)";
            break;

        case 'community':
            if ($community_id <= 0 || $sub_parish_id <= 0) {
                echo json_encode(["success" => false, "message" => "Community and Sub Parish are required for community target"]);
                exit();
            }
            $table = 'community_annual_expense_budget';
            $where_clause = "head_parish_id = $head_parish_id AND sub_parish_id = $sub_parish_id AND community_id = $community_id AND expense_budget_target_start_date = '$expense_budget_target_start_date' AND expense_budget_target_end_date = '$expense_budget_target_end_date' AND bank_account_id = $account_id";
            $insert_columns = "(head_parish_id, sub_parish_id, community_id, bank_account_id, expense_budget_target_start_date, expense_budget_target_end_date, expense_budget_target_amount)";
            $insert_values = "(?, ?, ?, ?, ?, ?, ?)";
            break;

        case 'group':
            if ($group_id <= 0) {
                echo json_encode(["success" => false, "message" => "Group is required for group target"]);
                exit();
            }
            $table = 'group_annual_expense_budget';
            $where_clause = "head_parish_id = $head_parish_id AND group_id = $group_id AND expense_budget_target_start_date = '$expense_budget_target_start_date' AND expense_budget_target_end_date = '$expense_budget_target_end_date' AND bank_account_id = $account_id";
            $insert_columns = "(head_parish_id, group_id, bank_account_id, expense_budget_target_start_date, expense_budget_target_end_date, expense_budget_target_amount)";
            $insert_values = "(?, ?, ?, ?, ?, ?)";
            break;
    }

    // Check if a record exists
    $check_query = "SELECT annual_expense_budget_id FROM $table WHERE $where_clause";
    $result = $conn->query($check_query);

    if ($result->num_rows > 0) {
        // Update existing record
        $row = $result->fetch_assoc();
        $annual_expense_budget_id = $row['annual_expense_budget_id'];

        $update_query = "UPDATE $table SET expense_budget_target_amount = ? WHERE annual_expense_budget_id = ? AND bank_account_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("dii", $expense_budget_target_amount, $annual_expense_budget_id, $account_id);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Expense Budget updated successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to update budget: " . $stmt->error]);
        }
    } else {
        // Insert new record if no existing one is found
        $insert_query = "INSERT INTO $table $insert_columns VALUES $insert_values";
        $stmt = $conn->prepare($insert_query);

        if ($target === 'head-parish') {
            $stmt->bind_param("iissd", $head_parish_id, $account_id, $expense_budget_target_start_date, $expense_budget_target_end_date, $expense_budget_target_amount);
        } elseif ($target === 'sub-parish') {
            $stmt->bind_param("iiissd", $head_parish_id, $sub_parish_id, $account_id, $expense_budget_target_start_date, $expense_budget_target_end_date, $expense_budget_target_amount);
        } elseif ($target === 'community') {
            $stmt->bind_param("iiiissd", $head_parish_id, $sub_parish_id, $community_id, $account_id, $expense_budget_target_start_date, $expense_budget_target_end_date, $expense_budget_target_amount);
        } elseif ($target === 'group') {
            $stmt->bind_param("iiissd", $head_parish_id, $group_id, $account_id, $expense_budget_target_start_date, $expense_budget_target_end_date, $expense_budget_target_amount);
        }

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "New expense budget record added successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to add new budget record: " . $stmt->error]);
        }
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

// Close the database connection
$conn->close();
?>
