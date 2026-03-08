<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');

if (!isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Head Parish ID is missing from session"]);
    exit();
}

$head_parish_id = $_SESSION['head_parish_id'];

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $harambee_id = isset($_POST['harambee_id']) ? intval($_POST['harambee_id']) : 0;
    $target = isset($_POST['target']) ? $conn->real_escape_string($_POST['target']) : null;
    $admin_id = isset($_POST['admin_id']) ? $conn->real_escape_string($_POST['admin_id']) : null;
    $contribution_date = isset($_POST['contribution_date']) ? $conn->real_escape_string($_POST['contribution_date']) : null;

    $all = isset($_POST['all']) ? filter_var($_POST['all'], FILTER_VALIDATE_BOOLEAN) : false;
    $detailed = isset($_POST['detailed']) ? filter_var($_POST['detailed'], FILTER_VALIDATE_BOOLEAN) : false;

    if ($harambee_id <= 0) {
        echo json_encode(["success" => false, "message" => "Please select Harambee"]);
        exit();
    }

    if (!$all && ($admin_id <= 0 || empty($admin_id))) {
        echo json_encode(["success" => false, "message" => "Please select user"]);
        exit();
    }

    if (empty($target)) {
        echo json_encode(["success" => false, "message" => "Harambee target is required"]);
        exit();
    }

    if (empty($contribution_date)) {
        echo json_encode(["success" => false, "message" => "Please select a contribution date"]);
        exit();
    }

    $date_obj = DateTime::createFromFormat('Y-m-d', $contribution_date);
    $valid_format = $date_obj && $date_obj->format('Y-m-d') === $contribution_date;

    if (!$valid_format) {
        echo json_encode(["success" => false, "message" => "Invalid date format. Please use YYYY-MM-DD"]);
        exit();
    }

    $allowed_targets = ['head-parish', 'sub-parish', 'community', 'groups'];
    if (!in_array($target, $allowed_targets)) {
        echo json_encode(["success" => false, "message" => "Invalid harambee target"]);
        exit();
    }

    $encrypted_harambee_id = encryptData($harambee_id);

    if ($all) {

        if ($detailed) {
            $report_url = "https://kanisalangu.sewmrtechnologies.com/reports/download_harambee_clerks_report_all_detailed.php?" .
                          "harambee_id=$encrypted_harambee_id&target=$target&contribution_date=$contribution_date";
        } else {
            $report_url = "https://kanisalangu.sewmrtechnologies.com/reports/download_harambee_clerks_report_all.php?" .
                          "harambee_id=$encrypted_harambee_id&target=$target&contribution_date=$contribution_date";
        }

    } else {
        $encrypted_admin_id = encryptData($admin_id);

        $report_url = "https://kanisalangu.sewmrtechnologies.com/reports/download_harambee_clerks_report.php?" .
                      "harambee_id=$encrypted_harambee_id&target=$target&admin_id=$encrypted_admin_id&contribution_date=$contribution_date";
    }

    echo json_encode(["success" => true, "message" => "Report URL generated successfully", "url" => $report_url]);

} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
