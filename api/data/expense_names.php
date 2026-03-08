<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $target = isset($_GET['target']) ? $_GET['target'] : 'head-parish';
    $expenseGroupId = isset($_GET['expense_group_id']) ? intval($_GET['expense_group_id']) : null;

    // Build the SQL query based on the target
    switch ($target) {
        case 'sub-parish':
            $table = 'sub_parish_expense_names';
            break;
        case 'community':
            $table = 'community_expense_names';
            break;
        case 'group':
            $table = 'group_expense_names';
            break;
        case 'head-parish':
        default:
            $table = 'head_parish_expense_names';
            break;
    }

    // Prepare the SQL query
    if ($expenseGroupId !== null) {
        $sql = "SELECT expense_name_id, expense_name FROM $table WHERE expense_group_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $expenseGroupId);
    } else {
        $sql = "SELECT expense_name_id, expense_name FROM $table";
        $stmt = $conn->prepare($sql);
    }

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $expenseNames = [];

        while ($row = $result->fetch_assoc()) {
            $expenseNames[] = $row;
        }

        echo json_encode([
            "success" => true,
            "data" => $expenseNames
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to fetch expense names: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
