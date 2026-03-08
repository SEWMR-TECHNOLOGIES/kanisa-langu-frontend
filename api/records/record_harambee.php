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

// Check DB connection
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $target = $_POST['target'] ?? '';
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $from_date = $_POST['from_date'] ?? null;
    $to_date = $_POST['to_date'] ?? null;
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0.00;
    $account_id = isset($_POST['account_id']) ? intval($_POST['account_id']) : 0;

    // Validation
    if (empty($target)) {
        echo json_encode(["success" => false, "message" => "Target is required"]);
        exit();
    }

    if (empty($name)) {
        echo json_encode(["success" => false, "message" => "Harambee Name is required"]);
        exit();
    }

    if (empty($description)) {
        echo json_encode(["success" => false, "message" => "Description is required"]);
        exit();
    }

    if (empty($from_date) || empty($to_date)) {
        echo json_encode(["success" => false, "message" => "From and To dates are required"]);
        exit();
    }

    if (strtotime($to_date) <= strtotime($from_date)) {
        echo json_encode(["success" => false, "message" => "To date must be greater than From date"]);
        exit();
    }

    if ($amount <= 0) {
        echo json_encode(["success" => false, "message" => "Amount must be greater than 0"]);
        exit();
    }

    if ($account_id <= 0) {
        echo json_encode(["success" => false, "message" => "Bank account cannot be blank"]);
        exit();
    }

    // Begin transaction
    $conn->begin_transaction();

    try {

        switch ($target) {

            // =====================================================
            // HEAD PARISH
            // =====================================================
            case 'head_parish':

                $stmt = $conn->prepare("
                    INSERT INTO head_parish_harambee
                    (head_parish_id, account_id, name, description, from_date, to_date, amount)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->bind_param(
                    "iissssd",
                    $head_parish_id,
                    $account_id,
                    $name,
                    $description,
                    $from_date,
                    $to_date,
                    $amount
                );
                break;

            // =====================================================
            // SUB PARISH
            // =====================================================
            case 'sub_parish':

                $sub_parish_id = intval($_POST['sub_parish_id'] ?? 0);

                if ($sub_parish_id <= 0) {
                    throw new Exception("Sub Parish ID is required");
                }

                $stmt = $conn->prepare("
                    INSERT INTO sub_parish_harambee
                    (sub_parish_id, head_parish_id, account_id, name, description, from_date, to_date, amount)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->bind_param(
                    "iiissssd",
                    $sub_parish_id,
                    $head_parish_id,
                    $account_id,
                    $name,
                    $description,
                    $from_date,
                    $to_date,
                    $amount
                );
                break;

            // =====================================================
            // COMMUNITY
            // =====================================================
            case 'community':

                $community_id = intval($_POST['community_id'] ?? 0);
                $sub_parish_id = intval($_POST['sub_parish_id'] ?? 0);

                if ($community_id <= 0 || $sub_parish_id <= 0) {
                    throw new Exception("Community and Sub Parish are required");
                }

                $stmt = $conn->prepare("
                    INSERT INTO community_harambee
                    (community_id, sub_parish_id, head_parish_id, account_id, name, description, from_date, to_date, amount)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->bind_param(
                    "iiiissssd",
                    $community_id,
                    $sub_parish_id,
                    $head_parish_id,
                    $account_id,
                    $name,
                    $description,
                    $from_date,
                    $to_date,
                    $amount
                );
                break;

            // =====================================================
            // GROUP
            // =====================================================
            case 'group':

                $group_id = intval($_POST['group_id'] ?? 0);

                if ($group_id <= 0) {
                    throw new Exception("Group ID is required");
                }

                $stmt = $conn->prepare("
                    INSERT INTO groups_harambee
                    (group_id, head_parish_id, account_id, name, description, from_date, to_date, amount)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->bind_param(
                    "iiissssd",
                    $group_id,
                    $head_parish_id,
                    $account_id,
                    $name,
                    $description,
                    $from_date,
                    $to_date,
                    $amount
                );
                break;

            default:
                throw new Exception("Invalid target");
        }

        // Execute
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        $conn->commit();

        echo json_encode([
            "success" => true,
            "message" => "Harambee recorded successfully"
        ]);

        $stmt->close();

    } catch (Exception $e) {

        $conn->rollback();

        echo json_encode([
            "success" => false,
            "message" => "Failed to record Harambee: " . $e->getMessage()
        ]);
    }

} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>