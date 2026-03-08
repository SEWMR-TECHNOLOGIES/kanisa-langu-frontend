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
    $revenue_date = isset($_POST['revenue_date']) ? $conn->real_escape_string($_POST['revenue_date']) : null;
    $target = isset($_POST['target']) ? $conn->real_escape_string($_POST['target']) : null;
    

    if (empty($target)) {
        echo json_encode(["success" => false, "message" => "Revenue target is required"]);
        exit();
    }
    
    if (empty($revenue_date)) {
        echo json_encode(["success" => false, "message" => "Revenue Date is required"]);
        exit();
    }
    $report_url = '';

    switch ($target) {
        case 'head-parish':
            $report_url = "https://kanisalangu.sewmrtechnologies.com/reports/verify_head_parish_revenues.php?target=head-parish&revenue_date=$revenue_date";
            break;

        case 'sub-parish':
            $sub_parish_id = isset($_POST['sub_parish_id']) ? intval($_POST['sub_parish_id']) : 0;
            if ($sub_parish_id <= 0) {
                echo json_encode(["success" => false, "message" => "Sub Parish is required"]);
                exit();
            }
            $encrypted_sub_parish_id = encryptData($sub_parish_id);  // Encrypt the sub_parish_id
            $report_url = "https://kanisalangu.sewmrtechnologies.com/reports/verify_sub_parish_revenues.php?target=sub-parish&sub_parish_id=$encrypted_sub_parish_id&revenue_date=$revenue_date";
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
            $report_url = "https://kanisalangu.sewmrtechnologies.com/reports/verify_community_revenues.php?target=community&sub_parish_id=$encrypted_sub_parish_id&community_id=$encrypted_community_id&revenue_date=$revenue_date";
            break;

        case 'group':
            $group_id = isset($_POST['group_id']) ? intval($_POST['group_id']) : 0;
            if ($group_id <= 0) {
                echo json_encode(["success" => false, "message" => "Group is required"]);
                exit();
            }
            $encrypted_group_id = encryptData($group_id);  // Encrypt the group_id
            $report_url = "https://kanisalangu.sewmrtechnologies.com/reports/verify_groups_revenues.php?target=groups&group_id=$encrypted_group_id&revenue_date=$revenue_date";
            break;

        default:
            echo json_encode(["success" => false, "message" => "Invalid Revenue target"]);
            exit();
    }

    // Return the report URL if no errors
    echo json_encode(["success" => true, "message" => "Verification URL generated successfully", "url" => $report_url]);
} else {
    // If the request method is not POST
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

// Close the database connection
$conn->close();
?>
