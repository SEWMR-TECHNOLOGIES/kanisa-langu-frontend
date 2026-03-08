<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/PaymentGateway.php');

// Check database connection
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit();
}

// Validate required fields
if (!isset($_POST['memberId'])) {
    echo json_encode(["success" => false, "message" => "memberId is required"]);
    exit();
}

if (!isset($_POST['phoneNumber'])) {
    echo json_encode(["success" => false, "message" => "phoneNumber is required"]);
    exit();
}

if (!isset($_POST['amount'])) {
    echo json_encode(["success" => false, "message" => "amount is required"]);
    exit();
}

if (!isset($_POST['description'])) {
    echo json_encode(["success" => false, "message" => "description is required"]);
    exit();
}

if (!isset($_POST['clientDateTime'])) {
    echo json_encode(["success" => false, "message" => "clientDateTime is required"]);
    exit();
}


if (!isset($_POST['headParishId'])) {
    echo json_encode(["success" => false, "message" => "headParishId is required"]);
    exit();
}

// Sanitize inputs
$memberId = intval($_POST['memberId']);
$phoneNumber = trim($_POST['phoneNumber']);
$amount = floatval($_POST['amount']);
$paymentReason = $conn->real_escape_string(trim($_POST['description']));
$paymentDate = trim($_POST['clientDateTime']);
$headParishId = intval($_POST['headParishId']);

// Validate phone number format
if (!preg_match('/^255[0-9]{9}$/', $phoneNumber)) {
    echo json_encode(["success" => false, "message" => "Phone number must start with 255 and be followed by 9 digits"]);
    exit();
}

// Validate amount (must be a positive number)
if ($amount <= 0) {
    echo json_encode(["success" => false, "message" => "Amount must be a positive number"]);
    exit();
}

// Validate payment date format
if (!strtotime($paymentDate)) {
    echo json_encode(["success" => false, "message" => "Invalid payment date format"]);
    exit();
}


$memberDetailsResult = getMemberDetails($conn, $memberId);
$memberRow = $memberDetailsResult->fetch_assoc();

$fullNameParts = array_filter([
    strtolower($memberRow['first_name'] ?? ''),
    strtolower($memberRow['middle_name'] ?? ''),
    strtolower($memberRow['last_name'] ?? '')
]);

$buyerName = ucwords(implode(' ', $fullNameParts));

// Check if envelope_number exists and is not empty
if (!empty(trim($memberRow['envelope_number']))) {
    $buyerName .= " [" . trim($memberRow['envelope_number']) . "]";
}

$buyerEmail = !empty($memberRow['email']) ? $memberRow['email'] : 'payments@kanisalangu.sewmrtechnologies.com';

$paymentReason = "Malipo ya sadaka ya Bahasha kutoka kwa $buyerName"; 
// Request payment
$paymentGateway = new PaymentGateway($conn);
$response = $paymentGateway->requestPayment($phoneNumber, $amount, $paymentReason, $buyerName, $buyerEmail);


// Handle the payment response
if ($response['status']) {
    // Insert payment data using the reusable function
    $paymentGateway->insertEnvelopePaymentData(
        $memberId,
        $headParishId,
        $response,
        $amount,
        $paymentReason,
        $paymentDate,
        'Pending'
    );

    echo json_encode([
        "success" => true,
        "message" => "Payment request successful",
        "checkoutRequestId" => $response['response']['order_id'] ?? null,
        "transactionReference" => $response['response']['wallet_payment_response']['transid'] ?? null,
        "paymentReason" => $paymentReason,
        "paymentDate" => $paymentDate
    ]);

} else {
    // Handle payment request failure
    echo json_encode([
        "success" => false,
        "message" => $response['message'] ?? 'Payment failed'
    ]);
}

// Close database connection
$conn->close();
?>
