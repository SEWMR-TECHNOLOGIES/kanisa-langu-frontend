<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/FCM.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Only POST method allowed']);
    exit;
}

$title = $_POST['title'] ?? '';
$message = $_POST['message'] ?? '';
$target = $_POST['target'] ?? 'news'; // default target is 'news'

// is_topic: if key exists, use its boolean value; if missing, default true
$isTopic = !isset($_POST['is_topic']) || ($_POST['is_topic'] == 'on' || $_POST['is_topic'] == '1' || $_POST['is_topic'] === true);

if (empty($title) || empty($message)) {
    echo json_encode(['success' => false, 'error' => 'Missing title or message']);
    exit;
}

if ($isTopic) {
    $response = sendPushNotificationToTopic($target, $title, $message);
} else {
    $response = sendPushNotificationToFCMToken($target, $title, $message);
}

if ($response['success']) {
    echo json_encode(['success' => true, 'message' => 'Notification sent successfully']);
} else {
    echo json_encode(['success' => false, 'error' => $response['error'] ?? 'Unknown error']);
}
