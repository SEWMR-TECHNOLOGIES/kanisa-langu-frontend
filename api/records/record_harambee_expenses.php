<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');

// Ensure session contains necessary IDs
if (!isset($_SESSION['head_parish_id'], $_SESSION['head_parish_admin_id'])) {
    echo json_encode(["success" => false, "message" => "Session expired or invalid. Please login again."]);
    exit();
}
$head_parish_id = $_SESSION['head_parish_id'];
$recorded_by = $_SESSION['head_parish_admin_id'];

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed. Please try again later."]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit();
}

// Collect input
$target = isset($_POST['target']) ? $conn->real_escape_string($_POST['target']) : '';
$harambee_id = isset($_POST['harambee_id']) ? intval($_POST['harambee_id']) : 0;
$expense_name_id = isset($_POST['expense_name_id']) ? intval($_POST['expense_name_id']) : 0;
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0.0;
$description = isset($_POST['description']) ? $conn->real_escape_string($_POST['description']) : '';
$expense_date = isset($_POST['expense_date']) ? $conn->real_escape_string($_POST['expense_date']) : '';

// Validate input with user-friendly messages
$valid_targets = ['head-parish','sub-parish','community','group'];
if (!in_array($target, $valid_targets)) {
    echo json_encode(["success" => false, "message" => "Please select a valid target."]);
    exit();
}
if ($harambee_id <= 0) {
    echo json_encode(["success" => false, "message" => "Please select a valid Harambee."]);
    exit();
}
if ($expense_name_id <= 0) {
    echo json_encode(["success" => false, "message" => "Please select a valid expense name."]);
    exit();
}
if ($amount <= 0) {
    echo json_encode(["success" => false, "message" => "Amount must be greater than zero."]);
    exit();
}
if (empty($description)) {
    echo json_encode(["success" => false, "message" => "Please provide a description for the expense."]);
    exit();
}
if (empty($expense_date) || !DateTime::createFromFormat('Y-m-d', $expense_date)) {
    echo json_encode(["success" => false, "message" => "Please provide a valid expense date in YYYY-MM-DD format."]);
    exit();
}

// Create Nairobi timestamp for created_at
$datetime = new DateTime('now', new DateTimeZone('Africa/Nairobi'));
$created_at = $datetime->format('Y-m-d H:i:s');

$conn->begin_transaction();

try {
    // Map target to harambee table
    $harambee_table = match($target) {
        'head-parish' => 'head_parish_harambee',
        'sub-parish' => 'sub_parish_harambee',
        'community' => 'community_harambee',
        'group' => 'groups_harambee'
    };

    // Map target to expense table
    $expense_table = match($target) {
        'head-parish' => 'head_parish_expense_names',
        'sub-parish' => 'sub_parish_expense_names',
        'community' => 'community_expense_names',
        'group' => 'group_expense_names'
    };

    // Check harambee exists
    $stmt = $conn->prepare("SELECT 1 FROM $harambee_table WHERE harambee_id=? AND head_parish_id=? LIMIT 1");
    $stmt->bind_param("ii", $harambee_id, $head_parish_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 0) {
        throw new Exception("Selected Harambee does not exist. Please choose a valid one.");
    }
    $stmt->close();

    // Check expense_name exists in the correct table
    $stmt = $conn->prepare("SELECT 1 FROM $expense_table WHERE expense_name_id=? LIMIT 1");
    $stmt->bind_param("i", $expense_name_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 0) {
        throw new Exception("Selected expense name does not exist for the selected target. Please choose a valid one.");
    }
    $stmt->close();

    // Insert expense
    $stmt = $conn->prepare("
        INSERT INTO harambee_expenses 
        (target, harambee_id, head_parish_id, expense_name_id, amount, description, expense_date, recorded_by, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "siiidssss",
        $target,
        $harambee_id,
        $head_parish_id,
        $expense_name_id,
        $amount,
        $description,
        $expense_date,
        $recorded_by,
        $created_at
    );
    $stmt->execute();

    $conn->commit();
    echo json_encode(["success" => true, "message" => "Expense recorded successfully."]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}

$conn->close();
?>
