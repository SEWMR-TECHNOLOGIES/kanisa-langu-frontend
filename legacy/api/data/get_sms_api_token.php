<?php
header('Content-Type: application/json');

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php'); 

if (!isset($_SESSION['head_parish_id'])) {
    echo json_encode([
        "success"  => false,
        "message" => "Head parish ID not found in session."
    ]);
    exit();
}

$head_parish_id = intval($_SESSION['head_parish_id']);

if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "DB connection failed: " . $conn->connect_error
    ]);
    exit();
}

// Fetch SMS API credentials
$smsCredentials = get_sms_credentials($conn, $head_parish_id);

if (!$smsCredentials || empty($smsCredentials['api_token'])) {
    echo json_encode([
        "success"   => false,
        "message"   => "SMS API token not set."
    ]);
    exit();
}

// Call the backend API for remaining SMS
$apiUrl = 'https://api.sewmrsms.co.tz/api/v1/sms/remaining-sms';

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $smsCredentials['api_token']
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Handle response
if ($httpCode !== 200 || !$response) {
    echo json_encode([
        "success" => false,
        "message" => "Failed to fetch remaining SMS from backend."
    ]);
    exit();
}

$data = json_decode($response, true);
if (!$data) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid response from backend."
    ]);
    exit();
}

// Return the data to front-end
echo json_encode($data);
$conn->close();
