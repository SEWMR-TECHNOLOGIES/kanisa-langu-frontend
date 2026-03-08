<?php
// auth_check.php

// Allow all origins
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Define your secret
define('ACCESS_SECRET', 'TEK@2022'); 

// Make sure content type is JSON
header('Content-Type: application/json');

// Read the raw POST data
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

// Validate JSON
if ($data === null) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
    exit;
}

// Check for secret
$secret = $data['secret'] ?? '';

if ($secret === ACCESS_SECRET) {
    echo json_encode(['status' => 'ok', 'access' => true]);
} else {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'access' => false, 'message' => 'Invalid secret']);
}
?>
