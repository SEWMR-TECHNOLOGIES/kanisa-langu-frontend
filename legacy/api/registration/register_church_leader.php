<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/validation_functions.php');

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve input data from POST request
    $title_id = isset($_POST['title_id']) && trim($_POST['title_id']) !== '' ? (int)$_POST['title_id'] : null;
    $first_name = isset($_POST['first_name']) ? ucfirst(strtolower($conn->real_escape_string($_POST['first_name']))) : '';
    $middle_name = isset($_POST['middle_name']) ? ucfirst(strtolower($conn->real_escape_string($_POST['middle_name']))) : null;
    $last_name = isset($_POST['last_name']) ? ucfirst(strtolower($conn->real_escape_string($_POST['last_name']))) : '';
    $gender = isset($_POST['gender']) ? $conn->real_escape_string($_POST['gender']) : '';
    $type = isset($_POST['type']) ? $conn->real_escape_string($_POST['type']) : '';
    $head_parish_id = isset($_POST['head_parish_id']) && trim($_POST['head_parish_id']) !== '' ? (int)$_POST['head_parish_id'] : null;
    $role_id = isset($_POST['role_id']) && trim($_POST['role_id']) !== '' ? (int)$_POST['role_id'] : null;
    $appointment_date = isset($_POST['appointment_date']) ? $conn->real_escape_string($_POST['appointment_date']) : '';
    $end_date = isset($_POST['end_date']) ? $conn->real_escape_string($_POST['end_date']) : null;
    $status = isset($_POST['status']) ? $conn->real_escape_string($_POST['status']) : 'Active';

    // Validate mandatory fields
    if (empty($first_name)) {
        echo json_encode(["success" => false, "message" => "First name is required"]);
        exit();
    }
    if (empty($last_name)) {
        echo json_encode(["success" => false, "message" => "Last name is required"]);
        exit();
    }
    if (empty($gender)) {
        echo json_encode(["success" => false, "message" => "Gender is required"]);
        exit();
    }
    if (empty($type)) {
        echo json_encode(["success" => false, "message" => "Leader type (Mgeni/Mwenyeji) is required"]);
        exit();
    }
    if (empty($appointment_date)) {
        echo json_encode(["success" => false, "message" => "Appointment date is required"]);
        exit();
    }
    if (empty($role_id)) {
        echo json_encode(["success" => false, "message" => "Role ID is required"]);
        exit();
    }

    // Validate appointment date format (ensure it's in YYYY-MM-DD format)
    $appointmentDate = DateTime::createFromFormat('Y-m-d', $appointment_date);
    if (!$appointmentDate || $appointmentDate->format('Y-m-d') !== $appointment_date) {
        echo json_encode(["success" => false, "message" => "Invalid appointment date format. Use YYYY-MM-DD"]);
        exit();
    }

    // Optional: Validate end date (if provided) to ensure it's after appointment date
    if ($end_date) {
        $endDate = DateTime::createFromFormat('Y-m-d', $end_date);
        if (!$endDate || $endDate->format('Y-m-d') !== $end_date) {
            echo json_encode(["success" => false, "message" => "Invalid end date format. Use YYYY-MM-DD"]);
            exit();
        }
        if ($endDate < $appointmentDate) {
            echo json_encode(["success" => false, "message" => "End date must be after the appointment date"]);
            exit();
        }
    }

    // SQL query to insert the new church leader
    $sql = "INSERT INTO church_leaders (title_id, first_name, middle_name, last_name, gender, type, head_parish_id, role_id, appointment_date, end_date, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Prepare and execute the query
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssssisss", $title_id, $first_name, $middle_name, $last_name, $gender, $type, $head_parish_id, $role_id, $appointment_date, $end_date, $status);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Church leader registered successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to register church leader: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
