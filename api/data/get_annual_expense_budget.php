<?php
header('Content-Type: application/json');
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Check if head_parish_id is set in session
    if (!isset($_SESSION['head_parish_id'])) {
        echo json_encode(["success" => false, "message" => "Head parish ID is not available in the session"]);
        exit();
    }
    
    $head_parish_id = (int) $_SESSION['head_parish_id'];
    $account_id = isset($_GET['account_id']) ? (int) $_GET['account_id'] : null;
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;
    
    if (!$account_id || !$start_date || !$end_date) {
        echo json_encode(["success" => false, "message" => "Missing required parameters"]);
        exit();
    }
    
    // Prepare SQL query to fetch expense budget target
    $sql = "SELECT expense_budget_target_amount 
            FROM head_parish_annual_expense_budget 
            WHERE head_parish_id = ? AND bank_account_id = ?
            AND expense_budget_target_start_date = ? AND expense_budget_target_end_date = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiss", $head_parish_id, $account_id, $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode(["success" => true, "data" => ["expense_budget_target" => $row['expense_budget_target_amount']]]);
    } else {
        echo json_encode(["success" => false, "message" => "Expense budget target not found"]);
    }
    
    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
