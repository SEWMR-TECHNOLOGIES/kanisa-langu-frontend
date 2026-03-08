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

// Check the request method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $harambee_id = isset($_POST['harambee_id']) ? intval($_POST['harambee_id']) : 0;
    $target = isset($_POST['target']) ? $conn->real_escape_string($_POST['target']) : null;
    $category = isset($_POST['category']) ? $conn->real_escape_string($_POST['category']) : null;
    $exclude_members_in_groups = isset($_POST['exclude_members_in_groups']) ? $conn->real_escape_string($_POST['exclude_members_in_groups']) : false;
    $download_xlsx = isset($_POST['download_xlsx']) && $_POST['download_xlsx'] === 'true';

    // Validate inputs
    if ($harambee_id <= 0) {
        echo json_encode(["success" => false, "message" => "Please select Harambee"]);
        exit();
    }
    if (empty($target)) {
        echo json_encode(["success" => false, "message" => "Summary target is required"]);
        exit();
    }
    if (empty($category)) {
        echo json_encode(["success" => false, "message" => "Please select valid category"]);
        exit();
    }

    $encrypted_harambee_id = encryptData($harambee_id);
    $pdf_url = '';
    $xls_url = '';

    switch ($target) {
        case 'head-parish':
            $sub_parish_id = isset($_POST['sub_parish_id']) ? intval($_POST['sub_parish_id']) : 0;
            if ($sub_parish_id <= 0) {
                echo json_encode(["success" => false, "message" => "Sub Parish is required"]);
                exit();
            }
            $encrypted_sub_parish_id = encryptData($sub_parish_id);

            $community_id = isset($_POST['community_id']) && !empty($_POST['community_id']) ? intval($_POST['community_id']) : null;

            if ($community_id) {
                $encrypted_community_id = encryptData($community_id);
                $pdf_url = "https://www.kanisalangu.sewmrtechnologies.com/reports/harambee_contribution_report.php?harambee_id=$encrypted_harambee_id&target=head-parish&report_for=community&sub_parish_id=$encrypted_sub_parish_id&community_id=$encrypted_community_id&category=$category&exclude_members_in_groups=$exclude_members_in_groups";
                $xls_url = "https://www.kanisalangu.sewmrtechnologies.com/reports/harambee_contribution_report_xlsx.php?harambee_id=$encrypted_harambee_id&target=head-parish&report_for=community&sub_parish_id=$encrypted_sub_parish_id&community_id=$encrypted_community_id&category=$category&exclude_members_in_groups=$exclude_members_in_groups";
            } else {
                $pdf_url = "https://www.kanisalangu.sewmrtechnologies.com/reports/harambee_contribution_report.php?harambee_id=$encrypted_harambee_id&target=head-parish&sub_parish_id=$encrypted_sub_parish_id&category=$category&exclude_members_in_groups=$exclude_members_in_groups";
                $xls_url = "https://www.kanisalangu.sewmrtechnologies.com/reports/harambee_contribution_report_xlsx.php?harambee_id=$encrypted_harambee_id&target=head-parish&sub_parish_id=$encrypted_sub_parish_id&category=$category&exclude_members_in_groups=$exclude_members_in_groups";
            }
            break;

        case 'sub-parish':
            $sub_parish_id = isset($_POST['sub_parish_id']) ? intval($_POST['sub_parish_id']) : 0;
            if ($sub_parish_id <= 0) {
                echo json_encode(["success" => false, "message" => "Sub Parish is required"]);
                exit();
            }
            $encrypted_sub_parish_id = encryptData($sub_parish_id);
            $pdf_url = "https://www.kanisalangu.sewmrtechnologies.com/reports/harambee_contribution_report.php?harambee_id=$encrypted_harambee_id&target=sub-parish&sub_parish_id=$encrypted_sub_parish_id&category=$category&exclude_members_in_groups=$exclude_members_in_groups";
            $xls_url = "https://www.kanisalangu.sewmrtechnologies.com/reports/harambee_contribution_report_xlsx.php?harambee_id=$encrypted_harambee_id&target=sub-parish&sub_parish_id=$encrypted_sub_parish_id&category=$category&exclude_members_in_groups=$exclude_members_in_groups";
            break;

        case 'community':
            $sub_parish_id = isset($_POST['sub_parish_id']) ? intval($_POST['sub_parish_id']) : 0;
            $community_id = isset($_POST['community_id']) ? intval($_POST['community_id']) : 0;
            if ($community_id <= 0 || $sub_parish_id <= 0) {
                echo json_encode(["success" => false, "message" => "Community and Sub Parish are required"]);
                exit();
            }
            $encrypted_sub_parish_id = encryptData($sub_parish_id);
            $encrypted_community_id = encryptData($community_id);
            $pdf_url = "https://www.kanisalangu.sewmrtechnologies.com/reports/harambee_contribution_report.php?harambee_id=$encrypted_harambee_id&target=community&sub_parish_id=$encrypted_sub_parish_id&community_id=$encrypted_community_id&category=$category&exclude_members_in_groups=$exclude_members_in_groups";
            $xls_url = "https://www.kanisalangu.sewmrtechnologies.com/reports/harambee_contribution_report_xlsx.php?harambee_id=$encrypted_harambee_id&target=community&sub_parish_id=$encrypted_sub_parish_id&community_id=$encrypted_community_id&category=$category&exclude_members_in_groups=$exclude_members_in_groups";
            break;

        case 'group':
            $group_id = isset($_POST['group_id']) ? intval($_POST['group_id']) : 0;
            if ($group_id <= 0) {
                echo json_encode(["success" => false, "message" => "Group is required"]);
                exit();
            }
            $encrypted_group_id = encryptData($group_id);
            $pdf_url = "https://www.kanisalangu.sewmrtechnologies.com/reports/harambee_contribution_report.php?harambee_id=$encrypted_harambee_id&target=group&group_id=$encrypted_group_id&category=$category&exclude_members_in_groups=$exclude_members_in_groups";
            $xls_url = "https://www.kanisalangu.sewmrtechnologies.com/reports/harambee_contribution_report_xlsx.php?harambee_id=$encrypted_harambee_id&target=group&group_id=$encrypted_group_id&category=$category&exclude_members_in_groups=$exclude_members_in_groups";
            break;

        default:
            echo json_encode(["success" => false, "message" => "Invalid distribution target"]);
            exit();
    }

    $final_url = $download_xlsx ? $xls_url : $pdf_url;

    echo json_encode([
        "success" => true,
        "message" => "Report URL generated successfully",
        "url" => $final_url,
        "pdf_url" => $pdf_url,
        "xls_url" => $xls_url
    ]);

} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
