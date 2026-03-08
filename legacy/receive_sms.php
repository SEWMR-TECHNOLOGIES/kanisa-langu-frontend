<?php
// receive_sms.php

header('Content-Type: application/json');

// Path to JSON file where messages will be stored
$jsonFile = __DIR__ . '/messages.json';

// Read the raw POST data
$raw = file_get_contents('php://input');

// Decode JSON
$data = json_decode($raw, true);

// Validate JSON
if ($data === null) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
    error_log("SMS Receiver: Invalid JSON received: $raw");
    exit;
}

// Extract fields
$sender = $data['sender'] ?? 'Unknown';
$message = $data['message'] ?? '';
$received_at = $data['received_at'] ?? date('c');

// Log to error log
error_log("SMS received from {$sender} at {$received_at}: {$message}");

// Load existing messages from file, or start empty array
$messages = [];
if (file_exists($jsonFile)) {
    $fileContents = file_get_contents($jsonFile);
    $messages = json_decode($fileContents, true) ?? [];
}

// Append new message
$messages[] = [
    'sender' => $sender,
    'message' => $message,
    'received_at' => $received_at,
];

// Save back to JSON file
if (file_put_contents($jsonFile, json_encode($messages, JSON_PRETTY_PRINT)) === false) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to write to JSON file']);
    error_log("SMS Receiver: Failed to write message to {$jsonFile}");
    exit;
}

// Respond back
http_response_code(200);
echo json_encode(['status' => 'ok']);
