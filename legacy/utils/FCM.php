<?php
// Function to get the OAuth token for Firebase Cloud Messaging
function getOAuthToken() {
    // Path to your service account key JSON file
    $serviceAccountKeyFile = $_SERVER['DOCUMENT_ROOT'] . '/utils/kanisa-langu-c9400-c1be2e2656c7.json';

    // Load the service account key
    $key = json_decode(file_get_contents($serviceAccountKeyFile), true);

    $clientEmail = $key['client_email'];
    $privateKey = str_replace("\\n", "\n", $key['private_key']); 

    // JWT Header
    $header = json_encode([
        'alg' => 'RS256',
        'typ' => 'JWT',
    ]);

    // JWT Claims
    $now = time();
    $payload = json_encode([
        'iss' => $clientEmail,
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        'aud' => 'https://oauth2.googleapis.com/token',
        'iat' => $now,
        'exp' => $now + 3600, // Token valid for 1 hour
    ]);

    // Encode Header and Payload
    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

    // Create the signature
    $signature = '';
    openssl_sign("$base64UrlHeader.$base64UrlPayload", $signature, $privateKey, OPENSSL_ALGO_SHA256);
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

    // Create the JWT
    $jwt = "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";

    // Get the access token
    $url = 'https://oauth2.googleapis.com/token';
    $postFields = http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwt,
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    
    $response = curl_exec($ch);
    curl_close($ch);

    // Decode the response to get the access token
    $responseData = json_decode($response, true);
    return $responseData['access_token'] ?? null;
}

function sendPushNotificationToFCMToken($fcmToken, $title, $message) {
    $accessToken = getOAuthToken();
    if (!$accessToken) {
        return ['success' => false, 'error' => 'Failed to get OAuth token'];
    }

    $url = 'https://fcm.googleapis.com/v1/projects/kanisa-langu-c9400/messages:send'; // <-- Adjust project ID here
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $accessToken,
    ];

    $body = json_encode([
        'message' => [
            'token' => $fcmToken,
            'notification' => [
                'title' => $title,
                'body' => $message,
            ],
        ],
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

    $response = curl_exec($ch);
    curl_close($ch);

    $decodedResponse = json_decode($response, true);

    if (isset($decodedResponse['name'])) {
        return ['success' => true];
    } else {
        return ['success' => false, 'error' => $response ?: 'Unknown error'];
    }
}

function sendPushNotificationToTopic($topic, $title, $message) {
    $accessToken = getOAuthToken();
    if (!$accessToken) {
        return ['success' => false, 'error' => 'Failed to get OAuth token'];
    }

    $url = 'https://fcm.googleapis.com/v1/projects/kanisa-langu-c9400/messages:send'; // <-- Adjust project ID here
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $accessToken,
    ];

    $body = json_encode([
        'message' => [
            'topic' => $topic,
            'notification' => [
                'title' => $title,
                'body' => $message,
            ],
        ],
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

    $response = curl_exec($ch);
    curl_close($ch);

    $decodedResponse = json_decode($response, true);

    if (isset($decodedResponse['name'])) {
        return ['success' => true];
    } else {
        return ['success' => false, 'error' => $response ?: 'Unknown error'];
    }
}



?>
