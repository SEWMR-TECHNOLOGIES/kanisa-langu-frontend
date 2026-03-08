<?php

class PaymentGateway
{
    // Constants
    private const CLIENT_ID = '81VxN2cwGpwjDZcS8FlXdMfaX6aWHsjt1LtLwV6V';
    private const CLIENT_SECRET = 'PeJ3IHVor0QouaodDj0xdtNaCtQWCEzxuj1pMGo7c2lt5zBc66ppVsrMErz6xvHelLy7n9xYTd2g42oefdzs3hTADlt4mkxhcM5Ge3PVq1tgwSz4AIDyQEJoyNcM0Etn';
    private const AUTHORIZATION_URL = 'https://api.sasapay.co.tz/api/v1/auth/token/?grant_type=client_credentials';
    private const PAYMENT_REQUEST_URL = 'https://api.sasapay.co.tz/api/v1/payments/request-payment/';
    private const TRANSACTION_STATUS_URL = 'https://api.sasapay.co.tz/api/v1/transactions/status/';
    private const ACCOUNT_VALIDATION_URL = 'https://api.sasapay.co.tz/api/v1/accounts/account-validation/';
    private const MERCHANT_CODE = '12587';
    private const CALLBACK_URL = 'https://elerai.sewmr.com/payments/';

    private $conn;

    // Constructor to set up the database connection
    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // Function to get access token
    private function authenticate()
    {
        $authString = base64_encode(self::CLIENT_ID . ':' . self::CLIENT_SECRET);
        $headers = [
            'Authorization: Basic ' . $authString
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::AUTHORIZATION_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        return $data['access_token'] ?? null;
    }
    // Function to validate account
    public function validateAccount($channelCode, $accountNumber)
    {
        $accessToken = $this->authenticate();
        if (!$accessToken) {
            return ['status' => false, 'message' => 'Authentication failed'];
        }

        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];

        $postData = [
            'merchant_code' => self::MERCHANT_CODE,
            'channel_code' => $channelCode,
            'account_number' => $accountNumber
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::ACCOUNT_VALIDATION_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    // Function to request payment
    public function requestPayment($phoneNumber, $amount, $transactionDesc)
    {
        $networkCode = $this->identifyNetworkByPhoneNumber($phoneNumber);
        $merchantRequestID = $this->generateMerchantRequestID();

        if ($networkCode === 'UNKNOWN') {
            return ['status' => false, 'message' => 'Invalid network for the given phone number'];
        }

        $accessToken = $this->authenticate();
        if (!$accessToken) {
            return ['status' => false, 'message' => 'Authentication failed'];
        }

        // Validate the account before proceeding with payment
        $validationResponse = $this->validateAccount($networkCode, $phoneNumber);
        if (!$validationResponse['status']) {
            return ['status' => false, 'message' => 'Account validation failed: ' . $validationResponse['message']];
        }

        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];

        $postData = [
            'MerchantCode' => self::MERCHANT_CODE,
            'NetworkCode' => $networkCode,
            'PhoneNumber' => $phoneNumber,
            'TransactionDesc' => $transactionDesc,
            'AccountReference' => $merchantRequestID,
            'Currency' => 'TZS',
            'Amount' => $amount,
            'CallBackURL' => self::CALLBACK_URL
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::PAYMENT_REQUEST_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

   // Function to check transaction status
    public function checkTransactionStatus($checkoutRequestId)
    {
        $accessToken = $this->authenticate();
        if (!$accessToken) {
            return ['status' => false, 'message' => 'Authentication failed'];
        }

        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];

        $postData = [
            'MerchantCode' => self::MERCHANT_CODE,
            'CallbackUrl'=> self::CALLBACK_URL,
            'CheckoutRequestId' => $checkoutRequestId
        ];


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::TRANSACTION_STATUS_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

        $response = curl_exec($ch);
        curl_close($ch);

        $statusData = json_decode($response, true);

        // Check if response was successful and transaction status
        if ($statusData['status'] === true) {
            $resultCode = $statusData['data']['ResultCode'];
            $resultDescription = $statusData['data']['ResultDescription'];
            $paid = $statusData['data']['Paid'];

            if ($resultCode == "0" && $paid) {
                // Payment successful
                return [
                    'status' => true,
                    'message' => "Payment Successful: " . $resultDescription
                ];
            } elseif ($resultCode == "PENDING" || !$paid) {
                // Payment is still pending
                return [
                    'status' => false,
                    'message' => "Payment Still Pending"
                ];
            } else {
                // Payment failed
                return [
                    'status' => false,
                    'message' => "Payment Failed: " . $resultDescription
                ];
            }
        } else {
            // Error in status check response
            return [
                'status' => false,
                'message' => "Error checking status: " . $statusData['message']
            ];
        }
    }

    // Function to get Network Code by provider
    private function getNetworkCode($provider)
    {
        $networkCodes = [
            'VODACOM' => 'VODACOM',
            'TIGO' => 'TIGO',
            'AIRTEL' => 'AIRTELMONEYTZ',
            'HALOPESA' => 'HALOPESA'
        ];

        return $networkCodes[$provider] ?? 'UNKNOWN';
    }

    // Function to identify network and get the corresponding code
    private function identifyNetworkByPhoneNumber($phoneNumber)
    {
        // Remove the 255 prefix
        $cleanedPhoneNumber = substr($phoneNumber, 3);

        // Extract the next two digits after 255
        $networkPrefix = substr($cleanedPhoneNumber, 0, 2);

        // Map network prefixes to providers
        $networkMap = [
            '76' => 'VODACOM',
            '75' => 'VODACOM',
            '74' => 'VODACOM',
            '65' => 'TIGO',
            '71' => 'TIGO',
            '77' => 'TIGO',
            '67' => 'TIGO',
            '69' => 'AIRTEL',
            '68' => 'AIRTEL',
            '78' => 'AIRTEL',
            '61' => 'HALOPESA',
            '62' => 'HALOPESA'
        ];

        $provider = $networkMap[$networkPrefix] ?? 'UNKNOWN';

        return $this->getNetworkCode($provider);
    }

    // Function to generate MerchantRequestID based on the last insert ID
    private function generateMerchantRequestID()
    {
        // Get the last inserted ID from the payments table
        $result = $this->conn->query("SELECT MAX(payment_id) AS last_id FROM harambee_payments");
        $row = $result->fetch_assoc();
        $lastId = $row['last_id'] ?? 0;
        $newId = $lastId + 1;

        // Generate the MerchantRequestID based on the current year, month, and new ID
        $year = date('Y');
        $month = date('m');
        $merchantRequestID = "KL_{$year}_{$month}_{$newId}";

        return $merchantRequestID;
    }

    public function insertHarambeePaymentData($memberId, $harambeeId, $headParishId, $responseData, $amount, $paymentReason, $paymentDate, $target = 'head-parish', $paymentStatus = 'Pending')
    {
        $paymentGateway = ($responseData['PaymentGateway'] == 'TIGO') ? 'MIXX BY YAS' : $responseData['PaymentGateway'];
        $checkoutRequestID = $responseData['CheckoutRequestID'] ?? null;
        $transactionReference = $responseData['TransactionReference'] ?? null;
        $merchantRequestID = $responseData['MerchantRequestID'] ?? null;
    
        $sql = "INSERT INTO harambee_payments 
                (member_id, harambee_id, head_parish_id, PaymentGateway, MerchantRequestID, CheckoutRequestID, TransactionReference, amount_paid, payment_reason, target, payment_date, payment_status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
        $stmt = $this->conn->prepare($sql);
    
        if ($stmt === false) {
            // Log error for debugging
            error_log('SQL prepare error: ' . $this->conn->error);
            return;
        }
    
        // Bind parameters
        $stmt->bind_param("iiissssdssss", $memberId, $harambeeId, $headParishId, $paymentGateway, $merchantRequestID, $checkoutRequestID, $transactionReference, $amount, $paymentReason, $target, $paymentDate, $paymentStatus);
    
        if (!$stmt->execute()) {
            // Handle error - you can log this or return an error message
            error_log('Database insert error: ' . $stmt->error);
        }
    
        $stmt->close();
    }
    
    public function insertEnvelopePaymentData($memberId, $headParishId, $responseData, $amount, $paymentReason, $paymentDate, $paymentStatus = 'Pending')
    {
        $paymentGateway = ($responseData['PaymentGateway'] == 'TIGO') ? 'MIXX BY YAS' : $responseData['PaymentGateway'];
        $checkoutRequestID = $responseData['CheckoutRequestID'] ?? null;
        $transactionReference = $responseData['TransactionReference'] ?? null;
        $merchantRequestID = $responseData['MerchantRequestID'] ?? null;
    
        $sql = "INSERT INTO envelope_payments 
                (member_id, head_parish_id, PaymentGateway, MerchantRequestID, CheckoutRequestID, TransactionReference, amount_paid, payment_reason, payment_date, payment_status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
        $stmt = $this->conn->prepare($sql);
    
        if ($stmt === false) {
            // Log error for debugging
            error_log('SQL prepare error: ' . $this->conn->error);
            return;
        }
    
        // Bind parameters
        $stmt->bind_param("iissssdsss", $memberId, $headParishId, $paymentGateway, $merchantRequestID, $checkoutRequestID, $transactionReference, $amount, $paymentReason, $paymentDate, $paymentStatus);
    
        if (!$stmt->execute()) {
            // Handle error - you can log this or return an error message
            error_log('Database insert error: ' . $stmt->error);
        }
    
        $stmt->close();
    }


    public function insertSundayServicePaymentData($memberId, $headParishId, $responseData, $amount, $paymentReason, $paymentDate, $serviceId, $revenueStreamId, $serviceDate, $paymentStatus = 'Pending')
    {
        // Set PaymentGateway based on responseData
        $paymentGateway = ($responseData['PaymentGateway'] == 'TIGO') ? 'MIXX BY YAS' : $responseData['PaymentGateway'];
        
        // Optional data from response
        $checkoutRequestID = $responseData['CheckoutRequestID'] ?? null;
        $transactionReference = $responseData['TransactionReference'] ?? null;
        $merchantRequestID = $responseData['MerchantRequestID'] ?? null;
    
        // SQL Query to insert payment data into sunday_service_payments
        $sql = "INSERT INTO sunday_service_payments 
                    (member_id, head_parish_id, PaymentGateway, MerchantRequestID, CheckoutRequestID, TransactionReference, 
                     revenue_stream_id, service_id, amount_paid, payment_reason, payment_date, service_date, payment_status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
        // Prepare the SQL statement
        $stmt = $this->conn->prepare($sql);
    
        if ($stmt === false) {
            // Log error for debugging
            error_log('SQL prepare error: ' . $this->conn->error);
            return;
        }
    
        // Bind parameters in the correct order
        $stmt->bind_param("iissssiiissss", 
            $memberId, $headParishId, $paymentGateway, $merchantRequestID, $checkoutRequestID, 
            $transactionReference, $revenueStreamId, $serviceId, $amount, $paymentReason, $paymentDate, $serviceDate, $paymentStatus
        );
    
        // Execute the statement
        if (!$stmt->execute()) {
            // Handle error - you can log this or return an error message
            error_log('Database insert error: ' . $stmt->error);
        }
    
        // Close the prepared statement
        $stmt->close();
    }

}

?>
