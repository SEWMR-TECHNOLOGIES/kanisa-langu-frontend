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
    $target = isset($_POST['target']) ? $conn->real_escape_string($_POST['target']) : null;
    
    // Validate basic inputs
    if ($harambee_id <= 0) {
        echo json_encode(["success" => false, "message" => "Please select Harambee"]);
        exit();
    }

    if (empty($target)) {
        echo json_encode(["success" => false, "message" => "Summary target is required"]);
        exit();
    }

    $encrypted_harambee_id = encryptData($harambee_id);  
    $report_url = '';

    switch ($target) {
        case 'head-parish':
            $sub_parish_id = isset($_POST['sub_parish_id']) ? intval($_POST['sub_parish_id']) : 0;
            $community_id = isset($_POST['community_id']) ? intval($_POST['community_id']) : 0;
            if ($sub_parish_id <= 0 || $community_id <= 0) {
                echo json_encode(["success" => false, "message" => "Sub Parish and Community are required"]);
                exit();
            }
            $encrypted_sub_parish_id = encryptData($sub_parish_id);  // Encrypt the sub_parish_id
            $encrypted_community_id = encryptData($community_id);  // Encrypt the community_id
            $report_url = "https://www.kanisalangu.sewmrtechnologies.com/reports/non_harambee_participants_report.php?harambee_id=$encrypted_harambee_id&target=head-parish&sub_parish_id=$encrypted_sub_parish_id&community_id=$encrypted_community_id";
            break;
    
        case 'sub-parish':
            $sub_parish_id = isset($_POST['sub_parish_id']) ? intval($_POST['sub_parish_id']) : 0;
            $community_id = isset($_POST['community_id']) ? intval($_POST['community_id']) : 0;
            if ($sub_parish_id <= 0 || $community_id <= 0) {
                echo json_encode(["success" => false, "message" => "Sub Parish and Community are required"]);
                exit();
            }
            $encrypted_sub_parish_id = encryptData($sub_parish_id);  // Encrypt the sub_parish_id
            $encrypted_community_id = encryptData($community_id);  // Encrypt the community_id
            $report_url = "https://www.kanisalangu.sewmrtechnologies.com/reports/non_harambee_participants_report.php?harambee_id=$encrypted_harambee_id&target=sub-parish&sub_parish_id=$encrypted_sub_parish_id&community_id=$encrypted_community_id";
            break;
    
        case 'community':
            $sub_parish_id = isset($_POST['sub_parish_id']) ? intval($_POST['sub_parish_id']) : 0;
            $community_id = isset($_POST['community_id']) ? intval($_POST['community_id']) : 0;
            if ($sub_parish_id <= 0 || $community_id <= 0) {
                echo json_encode(["success" => false, "message" => "Sub Parish and Community are required"]);
                exit();
            }
            $encrypted_sub_parish_id = encryptData($sub_parish_id);  // Encrypt the sub_parish_id
            $encrypted_community_id = encryptData($community_id);  // Encrypt the community_id
            $report_url = "https://www.kanisalangu.sewmrtechnologies.com/reports/non_harambee_participants_report.php?harambee_id=$encrypted_harambee_id&target=community&sub_parish_id=$encrypted_sub_parish_id&community_id=$encrypted_community_id";
            break;
    
        case 'group':
            $sub_parish_id = isset($_POST['sub_parish_id']) ? intval($_POST['sub_parish_id']) : 0;
            $community_id = isset($_POST['community_id']) ? intval($_POST['community_id']) : 0;
            if ($sub_parish_id <= 0 || $community_id <= 0) {
                echo json_encode(["success" => false, "message" => "Sub Parish and Community are required"]);
                exit();
            }
            $group_id = isset($_POST['group_id']) ? intval($_POST['group_id']) : 0;
            if ($group_id <= 0) {
                echo json_encode(["success" => false, "message" => "Group is required"]);
                exit();
            }
            $encrypted_sub_parish_id = encryptData($sub_parish_id);  // Encrypt the sub_parish_id
            $encrypted_community_id = encryptData($community_id);  // Encrypt the community_id
            $encrypted_group_id = encryptData($group_id);  // Encrypt the group_id
            $report_url = "https://www.kanisalangu.sewmrtechnologies.com/reports/non_harambee_participants_report.php?harambee_id=$encrypted_harambee_id&target=group&sub_parish_id=$encrypted_sub_parish_id&community_id=$encrypted_community_id&group_id=$encrypted_group_id";
            break;
    
        default:
            echo json_encode(["success" => false, "message" => "Invalid distribution target"]);
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
