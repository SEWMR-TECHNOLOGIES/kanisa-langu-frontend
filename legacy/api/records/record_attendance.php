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

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target = isset($_POST['target']) ? $conn->real_escape_string($_POST['target']) : '';
    $event_title = isset($_POST['event_title']) ? $conn->real_escape_string($_POST['event_title']) : '';
    $attendance_date = isset($_POST['attendance_date']) ? $conn->real_escape_string($_POST['attendance_date']) : null;
    $male_attendance = isset($_POST['male_attendance']) ? intval($_POST['male_attendance']) : 0;
    $female_attendance = isset($_POST['female_attendance']) ? intval($_POST['female_attendance']) : 0;
    $children_attendance = isset($_POST['children_attendance']) ? intval($_POST['children_attendance']) : 0;
    
    // Validate common inputs
    if (empty($event_title)) {
        echo json_encode(["success" => false, "message" => "Event title is required"]);
        exit();
    }
    if (empty($attendance_date)) {
        echo json_encode(["success" => false, "message" => "Attendance date is required"]);
        exit();
    }

    // Begin transaction
    $conn->begin_transaction();

    // Determine the target table and required session admin ID
    $table = '';
    $recorded_by = null;
    $stmt = null;

    switch ($target) {
        case 'head_parish':
            // Check if head parish admin ID is in session
            if (!isset($_SESSION['head_parish_admin_id'])) {
                echo json_encode(["success" => false, "message" => "Head Parish Admin ID is missing from session"]);
                exit();
            }
            $recorded_by = $_SESSION['head_parish_admin_id'];
        
            // Get the required sub_parish_id and service_number
            $sub_parish_id = isset($_POST['sub_parish_id']) && $_POST['sub_parish_id'] > 0 ? intval($_POST['sub_parish_id']) : null;
            $service_number = isset($_POST['service_number']) ? intval($_POST['service_number']) : 0;
        
            // Only require service number, sub_parish_id can be null
            if ($service_number <= 0) {
                echo json_encode(["success" => false, "message" => "Service Number is required for head parish attendance"]);
                exit();
            }
        
            $table = 'head_parish_attendance';

            $sql = "INSERT INTO $table 
                    (event_title, head_parish_id, sub_parish_id, service_number, male_attendance, female_attendance, children_attendance, recorded_by, date_recorded, attendance_date) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            // Use "i" for integers and "s" for strings
            // For nullable integers, pass null and MySQL will accept it
            $stmt->bind_param(
                "siissiiiss",
                $event_title,
                $head_parish_id,
                $sub_parish_id,       // null is OK here
                $service_number,
                $male_attendance,
                $female_attendance,
                $children_attendance,
                $recorded_by,
                $attendance_date,
                $attendance_date
            );

            break;
            
        case 'sub_parish':
            // Check if sub parish admin ID is in session
            if (!isset($_SESSION['sub_parish_admin_id'])) {
                echo json_encode(["success" => false, "message" => "Sub Parish Admin ID is missing from session"]);
                exit();
            }
            $recorded_by = $_SESSION['sub_parish_admin_id'];

            // Get the required sub_parish_id
            $sub_parish_id = isset($_POST['sub_parish_id']) ? intval($_POST['sub_parish_id']) : 0;
            if ($sub_parish_id <= 0) {
                echo json_encode(["success" => false, "message" => "Sub Parish is required for sub parish attendance"]);
                exit();
            }

            $table = 'sub_parish_attendance';
            $sql = "INSERT INTO $table (event_title, head_parish_id, sub_parish_id, male_attendance, female_attendance, children_attendance, recorded_by, date_recorded, attendance_date) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("siissiiiss", $event_title, $head_parish_id, $sub_parish_id, $male_attendance, $female_attendance, $children_attendance, $recorded_by, $attendance_date, $attendance_date);
            break;

        case 'community':
            // Check if community admin ID is in session
            if (!isset($_SESSION['community_admin_id'])) {
                echo json_encode(["success" => false, "message" => "Community Admin ID is missing from session"]);
                exit();
            }
            $recorded_by = $_SESSION['community_admin_id'];

            // Get the required sub_parish_id and community_id
            $sub_parish_id = isset($_POST['sub_parish_id']) ? intval($_POST['sub_parish_id']) : 0;
            $community_id = isset($_POST['community_id']) ? intval($_POST['community_id']) : 0;
            if ($sub_parish_id <= 0 || $community_id <= 0) {
                echo json_encode(["success" => false, "message" => "Sub Parish and Community are required for community attendance"]);
                exit();
            }

            $table = 'community_attendance';
            $sql = "INSERT INTO $table (event_title, head_parish_id, sub_parish_id, community_id, male_attendance, female_attendance, children_attendance, recorded_by, date_recorded, attendance_date) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("siissiiiss", $event_title, $head_parish_id, $sub_parish_id, $community_id, $male_attendance, $female_attendance, $children_attendance, $recorded_by, $attendance_date, $attendance_date);
            break;

        case 'group':
            // Check if group admin ID is in session
            if (!isset($_SESSION['group_admin_id'])) {
                echo json_encode(["success" => false, "message" => "Group Admin ID is missing from session"]);
                exit();
            }
            $recorded_by = $_SESSION['group_admin_id'];

            // Get the required group_id
            $group_id = isset($_POST['group_id']) ? intval($_POST['group_id']) : 0;
            if ($group_id <= 0) {
                echo json_encode(["success" => false, "message" => "Group is required for group attendance"]);
                exit();
            }

            $table = 'group_attendance';
            $sql = "INSERT INTO $table (event_title, head_parish_id, group_id, male_attendance, female_attendance, children_attendance, recorded_by, date_recorded, attendance_date) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("siissiiiss", $event_title, $head_parish_id, $group_id, $male_attendance, $female_attendance, $children_attendance, $recorded_by, $attendance_date, $attendance_date);
            break;

        default:
            echo json_encode(["success" => false, "message" => "Invalid target"]);
            exit();
    }

    // Execute the statement
    if ($stmt->execute()) {
        $conn->commit(); // Commit transaction
        echo json_encode(["success" => true, "message" => "Attendance recorded successfully"]);
    } else {
        $conn->rollback(); // Rollback transaction on failure
        echo json_encode(["success" => false, "message" => "Failed to record attendance: " . $stmt->error]);
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
