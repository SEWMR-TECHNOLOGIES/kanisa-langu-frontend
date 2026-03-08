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

// Check the database connection
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Validate the year and report_order
define('DEFAULT_YEAR', 2024);
$valid_report_orders = ['default', 'ascending', 'descending'];

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account_id = isset($_POST['account_id']) ? intval($_POST['account_id']) : 0;
    $year = isset($_POST['year']) ? $conn->real_escape_string($_POST['year']) : date('Y');
    $report_order = isset($_POST['report_order']) ? $conn->real_escape_string($_POST['report_order']) : 'default';
    $order_column = isset($_POST['order_column']) ? $conn->real_escape_string($_POST['order_column']) : 'expense_identifier';
    $target = isset($_POST['target']) ? $conn->real_escape_string($_POST['target']) : null;

    // Define valid values for order columns
    $valid_order_columns = ['expense_budget', 'total_expense_amount', 'annual_budget_balance', 'expenditure_percentage','expense_identifier'];


    // Validate basic inputs
    if ($account_id <= 0) {
        echo json_encode(["success" => false, "message" => "Please select Bank Account"]);
        exit();
    }

    if (empty($target)) {
        echo json_encode(["success" => false, "message" => "OGO target is required"]);
        exit();
    }

    // Validate year
    if (!is_numeric($year) || $year < DEFAULT_YEAR || $year > date('Y')) {
        echo json_encode(["success" => false, "message" => "Please select a valid year between " . DEFAULT_YEAR . " and the current year."]);
        exit();
    }

    // Validate report_order
    if (!in_array($report_order, $valid_report_orders)) {
        echo json_encode(["success" => false, "message" => "Invalid report order selected"]);
        exit();
    }

    // Validate order_column
    if (!in_array($order_column, $valid_order_columns)) {
        echo json_encode(["success" => false, "message" => "Invalid ordering column selected"]);
        exit();
    }
    $encrypted_account_id = encryptData($account_id);  // Encrypt the account_id
    $report_url = '';

    // Generate report URL based on the target
    switch ($target) {
        case 'head-parish':
            $report_url = "https://kanisalangu.sewmrtechnologies.com/reports/ogo.php?account_id=$encrypted_account_id&year=$year&report_order=$report_order&order_column=$order_column&target=head-parish";
            break;
    
        case 'sub-parish':
            $sub_parish_id = isset($_POST['sub_parish_id']) ? intval($_POST['sub_parish_id']) : 0;
            if ($sub_parish_id <= 0) {
                echo json_encode(["success" => false, "message" => "Sub Parish is required"]);
                exit();
            }
            $encrypted_sub_parish_id = encryptData($sub_parish_id);  // Encrypt the sub_parish_id
            $report_url = "https://kanisalangu.sewmrtechnologies.com/reports/ogo.php?account_id=$encrypted_account_id&year=$year&report_order=$report_order&order_column=$order_column&target=sub-parish&sub_parish_id=$encrypted_sub_parish_id";
            break;
    
        case 'community':
            $sub_parish_id = isset($_POST['sub_parish_id']) ? intval($_POST['sub_parish_id']) : 0;
            $community_id = isset($_POST['community_id']) ? intval($_POST['community_id']) : 0;
            if ($community_id <= 0 || $sub_parish_id <= 0) {
                echo json_encode(["success" => false, "message" => "Community and Sub Parish are required"]);
                exit();
            }
            $encrypted_sub_parish_id = encryptData($sub_parish_id);  // Encrypt the sub_parish_id
            $encrypted_community_id = encryptData($community_id);  // Encrypt the community_id
            $report_url = "https://kanisalangu.sewmrtechnologies.com/reports/ogo.php?account_id=$encrypted_account_id&year=$year&report_order=$report_order&order_column=$order_column&target=community&sub_parish_id=$encrypted_sub_parish_id&community_id=$encrypted_community_id";
            break;
    
        case 'group':
            $group_id = isset($_POST['group_id']) ? intval($_POST['group_id']) : 0;
            if ($group_id <= 0) {
                echo json_encode(["success" => false, "message" => "Group is required"]);
                exit();
            }
            $encrypted_group_id = encryptData($group_id);  // Encrypt the group_id
            $report_url = "https://kanisalangu.sewmrtechnologies.com/reports/ogo.php?account_id=$encrypted_account_id&year=$year&report_order=$report_order&order_column=$order_column&target=group&group_id=$encrypted_group_id";
            break;
    
        default:
            echo json_encode(["success" => false, "message" => "Invalid OGO target"]);
            exit();
    }


    // Return the report URL if no errors
    echo json_encode(["success" => true, "message" => "Report URL generated successfully", "url" => $report_url]);
} else {
    // If the request method is not POST
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

// Close the database connection
$conn->close();
?>
