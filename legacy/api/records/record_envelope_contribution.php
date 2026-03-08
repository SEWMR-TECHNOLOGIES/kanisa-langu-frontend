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
    $member_id = isset($_POST['member_id']) ? intval($_POST['member_id']) : 0;
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0.00;
    $contribution_date = isset($_POST['contribution_date']) ? $conn->real_escape_string($_POST['contribution_date']) : null; 
    $local_timestamp = isset($_POST['local_timestamp']) ? $conn->real_escape_string($_POST['local_timestamp']) : null; 
    $payment_method = isset($_POST['payment_method']) ? $conn->real_escape_string($_POST['payment_method']) : null;

    // Validate mandatory fields
    if ($member_id <= 0) {
        echo json_encode(["success" => false, "message" => "Please select a valid member."]);
        exit();
    }

    if ($amount <= 0) {
        echo json_encode(["success" => false, "message" => "Amount must be greater than 0."]);
        exit();
    }

    if (empty($contribution_date)) {
        echo json_encode(["success" => false, "message" => "Contribution date is required."]);
        exit();
    }

    if (empty($local_timestamp)) {
        echo json_encode(["success" => false, "message" => "Local timestamp is required."]);
        exit();
    }

    if (empty($payment_method)) {
        echo json_encode(["success" => false, "message" => "Payment method is required."]);
        exit();
    }

    // Fetch sub_parish_id and community_id based on member_id
    $location_data = getSubParishAndCommunity($member_id, $conn);
    $sub_parish_id = $location_data['sub_parish_id'];
    $community_id = $location_data['community_id'];

    if (!$sub_parish_id || !$community_id) {
        echo json_encode(["success" => false, "message" => "Sub Parish ID and Community ID could not be found for the member."]);
        exit();
    }

    // Prepare to insert contributions into the envelope_contribution table
    $stmt = $conn->prepare("INSERT INTO envelope_contribution (member_id, amount, contribution_date, recorded_by, head_parish_id, sub_parish_id, community_id, payment_method, local_timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisisssss", $member_id, $amount, $contribution_date, $_SESSION['head_parish_admin_id'], $head_parish_id, $sub_parish_id, $community_id, $payment_method, $local_timestamp);

    if (!$stmt->execute()) {
        echo json_encode(["success" => false, "message" => "Failed to record contribution for member ID $member_id"]);
        $stmt->close();
        exit();
    }
    $stmt->close();

    // Fetch member details for notifications
    $member = getMemberDetails($conn, $member_id);
    
    // Send email notification
    // if (!sendEnvelopeContributionEmail($amount, $member)) {
    //     echo json_encode(["success" => false, "message" => "Failed to send email to member ID $member_id"]);
    //     exit();
    // }

    // Send SMS notification
    // if (!sendEnvelopeContributionSMS($conn, $amount, $member)) {
    //     echo json_encode(["success" => false, "message" => "Failed to send SMS to member ID $member_id"]);
    //     exit();
    // }

    echo json_encode(["success" => true, "message" => "Contribution recorded successfully"]);

} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();

?>
