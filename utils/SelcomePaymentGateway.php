<?php
class SelcomC2BClient {
    private string $baseUrl;
    private string $apiKey;
    private string $apiSecret;
    private string $bearerToken;

    public function __construct($baseUrl, $apiKey, $apiSecret, $bearerToken) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->bearerToken = $bearerToken;
    }

    private function generateDigestHeaders(array $data): array {
        date_default_timezone_set('Africa/Dar_es_Salaam');
        $timestamp = date('c');
        $signedFields = implode(',', array_keys($data));
        $signString = "timestamp=$timestamp";
        foreach (explode(',', $signedFields) as $key) {
            $signString .= "&$key={$data[$key]}";
        }
        $digest = base64_encode(hash_hmac('sha256', $signString, $this->apiSecret, true));
        return [
            "Authorization: SELCOM " . base64_encode($this->apiKey),
            "Digest-Method: HS256",
            "Digest: $digest",
            "Timestamp: $timestamp",
            "Signed-Fields: $signedFields",
            "Content-Type: application/json"
        ];
    }

    private function sendRequest(string $endpoint, string $method, array $data = [], bool $useBearer = false): array {
        $url = $this->baseUrl . $endpoint;
        $headers = $useBearer
            ? ["Authorization: Bearer {$this->bearerToken}", "Content-Type: application/json"]
            : $this->generateDigestHeaders($data);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url . ($method === 'GET' ? '?' . http_build_query($data) : ''));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        if ($method !== 'GET') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response, true);
    }

    // 1. Push USSD to client
    public function pushUSSD(array $payload): array {
        return $this->sendRequest('/v1/wallet/pushussd', 'POST', $payload);
    }

    // 2. Payment Lookup
    public function lookup(array $payload): array {
        return $this->sendRequest('/lookup', 'POST', $payload, true);
    }

    // 3. Payment Validation
    public function validate(array $payload): array {
        return $this->sendRequest('/validation', 'POST', $payload, true);
    }

    // 4. Payment Notification
    public function notify(array $payload): array {
        return $this->sendRequest('/notification', 'POST', $payload, true);
    }

    // 5. Transaction Status Check
    public function queryStatus(array $payload): array {
        return $this->sendRequest('/v1/c2b/query-status', 'GET', $payload);
    }
}
?>
