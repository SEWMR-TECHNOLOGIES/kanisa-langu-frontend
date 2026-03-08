<?php
header('Content-Type: application/json');
session_start(); // Ensure session is started to access session variables

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Check for session variables
    if (!isset($_SESSION['diocese_id']) || !isset($_SESSION['diocese_admin_role'])) {
        echo json_encode(["success" => true, "data" => []]); // Return empty data if session variables are missing
        exit();
    }

    $diocese_id = (int)$_SESSION['diocese_id'];
    $diocese_admin_role = $_SESSION['diocese_admin_role'];

    // Ensure the role is 'admin' before proceeding
    if ($diocese_admin_role !== 'admin') {
        echo json_encode(["success" => true, "data" => []]); // Return empty data if the role is not admin
        exit();
    }

    // Fetch provinces related to the same diocese as the logged-in admin
    $sql = "SELECT province_id, province_name FROM provinces WHERE diocese_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $diocese_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $provinces = [];
        while ($row = $result->fetch_assoc()) {
            $provinces[] = $row;
        }
        echo json_encode(["success" => true, "data" => $provinces]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to fetch provinces: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
