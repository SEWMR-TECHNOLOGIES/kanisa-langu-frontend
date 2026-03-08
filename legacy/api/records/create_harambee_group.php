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
    $harambee_id = isset($_POST['harambee_id']) ? intval($_POST['harambee_id']) : 0;
    $group_name = isset($_POST['group_name']) ? $conn->real_escape_string($_POST['group_name']) : '';
    $group_target = isset($_POST['group_target']) ? floatval($_POST['group_target']) : 0.00;
    $description = isset($_POST['group_description']) ? $conn->real_escape_string($_POST['group_description']) : '';
    $target = isset($_POST['target']) ? $conn->real_escape_string($_POST['target']) : '';

    // Validate each input field separately and provide specific error messages
    if ($harambee_id <= 0) {
        echo json_encode(["success" => false, "message" => "Please select a valid harambee"]);
        exit();
    }

    if (empty($group_name)) {
        echo json_encode(["success" => false, "message" => "Group name is required"]);
        exit();
    }

    if ($group_target <= 0) {
        echo json_encode(["success" => false, "message" => "Group target must be provided and greater than 0"]);
        exit();
    }

    if (empty($target)) {
        echo json_encode(["success" => false, "message" => "Target is required"]);
        exit();
    }

    // Check for duplicate group name within the same harambee_id
    $duplicateCheckQuery = "";
    $check_stmt = null;

    switch ($target) {
        case 'head-parish':
            $duplicateCheckQuery = "SELECT 1 FROM head_parish_harambee_groups WHERE harambee_group_name = ? AND harambee_id = ?";
            break;
        case 'sub-parish':
            $duplicateCheckQuery = "SELECT 1 FROM sub_parish_harambee_groups WHERE harambee_group_name = ? AND harambee_id = ?";
            break;
        case 'community':
            $duplicateCheckQuery = "SELECT 1 FROM community_harambee_groups WHERE harambee_group_name = ? AND harambee_id = ?";
            break;
        case 'group':
            $duplicateCheckQuery = "SELECT 1 FROM groups_harambee_groups WHERE harambee_group_name = ? AND harambee_id = ?";
            break;
        default:
            echo json_encode(["success" => false, "message" => "Invalid harambee group target"]);
            exit();
    }

    // Prepare and execute the duplicate check
    $check_stmt = $conn->prepare($duplicateCheckQuery);
    $check_stmt->bind_param("si", $group_name, $harambee_id);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Group name already exists for the selected harambee"]);
        $check_stmt->close();
        exit();
    }
    $check_stmt->close();

    // Prepare to insert if no duplicate is found
    $conn->begin_transaction();
    $insert_stmt = null;

    switch ($target) {
        case 'head-parish':
            $sub_parish_id = isset($_POST['sub_parish_id']) ? intval($_POST['sub_parish_id']) : 0;
            if ($sub_parish_id <= 0) {
                echo json_encode(["success" => false, "message" => "Sub Parish is required"]);
                exit();
            }
            $insert_stmt = $conn->prepare("INSERT INTO head_parish_harambee_groups (harambee_group_name, harambee_group_target, harambee_id, head_parish_id, sub_parish_id, description) VALUES (?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("sdiiis", $group_name, $group_target, $harambee_id, $head_parish_id, $sub_parish_id, $description);
            break;

        case 'sub-parish':
            $sub_parish_id = isset($_POST['sub_parish_id']) ? intval($_POST['sub_parish_id']) : 0;
            if ($sub_parish_id <= 0) {
                echo json_encode(["success" => false, "message" => "Sub Parish is required"]);
                exit();
            }
            $insert_stmt = $conn->prepare("INSERT INTO sub_parish_harambee_groups (harambee_group_name, harambee_group_target, harambee_id, sub_parish_id, head_parish_id, description) VALUES (?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("sdiiis", $group_name, $group_target, $harambee_id, $sub_parish_id, $head_parish_id, $description);
            break;

        case 'community':
            $sub_parish_id = isset($_POST['sub_parish_id']) ? intval($_POST['sub_parish_id']) : 0;
            $community_id = isset($_POST['community_id']) ? intval($_POST['community_id']) : 0;
            if ($community_id <= 0 || $sub_parish_id <= 0) {
                echo json_encode(["success" => false, "message" => "Sub Parish and Community are required"]);
                exit();
            }
            $insert_stmt = $conn->prepare("INSERT INTO community_harambee_groups (harambee_group_name, harambee_group_target, harambee_id, community_id, sub_parish_id, head_parish_id, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("sdiiiis", $group_name, $group_target, $harambee_id, $community_id, $sub_parish_id, $head_parish_id, $description);
            break;

        case 'group':
            $group_id = isset($_POST['group_id']) ? intval($_POST['group_id']) : 0;
            if ($group_id <= 0) {
                echo json_encode(["success" => false, "message" => "Group is required"]);
                exit();
            }
            $insert_stmt = $conn->prepare("INSERT INTO groups_harambee_groups (harambee_group_name, harambee_group_target, harambee_id, group_id, head_parish_id, description) VALUES (?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("sdiiis", $group_name, $group_target, $harambee_id, $group_id, $head_parish_id, $description);
            break;
    }

    // Execute the insert statement
    if ($insert_stmt->execute()) {
        $conn->commit(); // Commit transaction
        echo json_encode(["success" => true, "message" => "Harambee group created successfully"]);
    } else {
        $conn->rollback(); // Rollback transaction on failure
        echo json_encode(["success" => false, "message" => "Failed to create harambee group: " . $insert_stmt->error]);
    }

    // Close the insert statement
    $insert_stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
