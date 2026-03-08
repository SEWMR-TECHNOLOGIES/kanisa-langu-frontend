<?php
header('Content-Type: application/json');

// Include the connection file and PaymentGateway class
include '../utils/connection.php';
include 'PaymentGateway.php';

// Instantiate the PaymentGateway class
$paymentGateway = new PaymentGateway($conn);

// Get the input data from the request
$data = json_decode(file_get_contents('php://input'), true);

// Validate the input data
if (!isset($data['memberId'], $data['phoneNumber'], $data['amount'], $data['description'], $data['clientDateTime'])) {
    echo json_encode(['status' => false, 'message' => 'Missing required parameters']);
    exit;
}

// Sanitize and validate input data
$memberId = trim($data['memberId']);
$phoneNumber = trim($data['phoneNumber']);
$amount = trim($data['amount']);
$paymentReason = trim($data['description']);
$paymentDate = trim($data['clientDateTime']);  

// Check if fields are empty
if (empty($memberId) || empty($phoneNumber) || empty($amount) || empty($paymentReason) || empty($paymentDate)) {
    echo json_encode(['status' => false, 'message' => 'All fields are required']);
    exit;
}

// Validate phone number format
if (!preg_match('/^255[0-9]{9}$/', $phoneNumber)) {
    echo json_encode(['status' => false, 'message' => 'Phone number must start with 255 and be followed by 9 digits']);
    exit;
}

// Validate amount (must be a positive number)
if (!is_numeric($amount) || $amount <= 0) {
    echo json_encode(['status' => false, 'message' => 'Amount must be a positive number']);
    exit;
}

// Validate payment date format (optional, depending on your needs)
if (!strtotime($paymentDate)) {
    echo json_encode(['status' => false, 'message' => 'Invalid payment date format']);
    exit;
}

// Request payment
$response = $paymentGateway->requestPayment($phoneNumber, $amount, $paymentReason);

// Log the response to the error log
// error_log(print_r($response, true));

// Extract necessary details
$CheckoutRequestID = $response['CheckoutRequestID'] ?? null;
$TransactionReference = $response['TransactionReference'] ?? null;

// Insert payment data into the database
if ($response['status']) {
    $paymentGateway->insertPaymentData($memberId, $response, $amount, $paymentReason, $paymentDate);  // Pass paymentReason and paymentDate
    // Return necessary details for status checking
    echo json_encode([
        'status' => true,
        'message' => 'Payment request successful',
        'checkoutRequestId' => $CheckoutRequestID,
        'transactionReference' => $TransactionReference,
        'paymentReason' => $paymentReason,
        'paymentDate' => $paymentDate 
    ]);
} else {
    echo json_encode([
        'status' => false,
        'message' => $response['detail']
    ]);
}
?>
