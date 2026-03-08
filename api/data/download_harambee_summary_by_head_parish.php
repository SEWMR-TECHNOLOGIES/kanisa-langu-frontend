<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');
define('ALLOWED_TARGETS', ['head-parish', 'sub-parish', 'community', 'group']);
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
    $from_date = isset($_POST['from_date']) ? $conn->real_escape_string($_POST['from_date']) : null;
    $to_date = isset($_POST['to_date']) ? $conn->real_escape_string($_POST['to_date']) : null;
    
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
    
    if(in_array($target, ALLOWED_TARGETS)){
       $report_url = "https://www.kanisalangu.sewmrtechnologies.com/reports/download_head_parish_harambee_summary.php?harambee_id=$encrypted_harambee_id&target=$target&from_date=$from_date&to_date=$to_date"; 
    }else{
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
