<?php
header('Content-Type: application/json');
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');

// Check if the database connection is successful
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Ensure it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and validate required parameters
    $member_id = isset($_POST['member_id']) ? intval($_POST['member_id']) : null;
    $year = isset($_POST['year']) ? intval($_POST['year']) : null;

    if (!$member_id || !$year) {
        echo json_encode(["success" => false, "message" => "Missing required parameters."]);
        exit();
    }

    // Fetch member's envelope data
    $envelopeData = fetchMemberEnvelopeData($conn, $member_id, $year);

    if (empty($envelopeData)) {
        echo json_encode(["success" => false, "message" => "Unable to fetch envelope data."]);
        exit();
    }

    // Calculate the balance
    $balance = $envelopeData['yearly_envelope_target'] - $envelopeData['total_envelope_contribution'];

    // Restructure the data to include balance
    $response = [
        "success" => true,
        "total_contribution" => $envelopeData['total_envelope_contribution'],
        "target_amount" => $envelopeData['yearly_envelope_target'],
        "balance" => $balance,
        "total_annual_envelopes" => (int) $envelopeData['total_annual_envelopes'],
        "envelopes_until_today" => (int) $envelopeData['total_envelopes_until_today'],
        "member_contributions_until_today" => (int) $envelopeData['member_contributions_until_today']
    ];

    // Return the JSON response
    echo json_encode($response);
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}

$conn->close();
