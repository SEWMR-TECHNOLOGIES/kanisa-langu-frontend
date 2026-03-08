<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$file = __DIR__ . "/messages.json";
if (!file_exists($file)) {
    echo json_encode([]);
    exit;
}

$messages = json_decode(file_get_contents($file), true);
echo json_encode($messages);
?>
