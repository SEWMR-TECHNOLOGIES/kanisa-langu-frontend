<?php
header('Content-Type: application/json');
session_start();

// Include necessary files
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/PaymentGateway.php');

// Check database connection
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Accept only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit();
}

// Validate required input
if (empty($_POST['checkoutRequestId'])) {
    echo json_encode(["success" => false, "message" => "checkoutRequestId is required"]);
    exit();
}

$checkoutRequestId = trim($_POST['checkoutRequestId']);

// Check transaction status
$paymentGateway = new PaymentGateway($conn);
$statusResponse = $paymentGateway->checkTransactionStatus($checkoutRequestId);

if ($statusResponse['status']) {
    // Fetch payment details
    $sql = "SELECT * FROM sunday_service_payments WHERE CheckoutRequestID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $checkoutRequestId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $paymentDetails = $result->fetch_assoc();
        $paymentStatus = $paymentDetails['payment_status'];

        if ($paymentStatus === 'Completed') {
            echo json_encode([
                "success" => false,
                "message" => "Payment already processed",
                "payment_details" => $paymentDetails
            ]);
        } else {
            // Update payment status and record contribution
            $updateSql = "UPDATE sunday_service_payments SET payment_status = 'Completed' WHERE CheckoutRequestID = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("s", $checkoutRequestId);
            $updateStmt->execute();
            $updateStmt->close();

            // Record contribution
            $memberId = $paymentDetails['member_id'];
            $headParishId = $paymentDetails['head_parish_id'];
            $amountPaid = $paymentDetails['amount_paid'];
            $local_timestamp = $paymentDetails['payment_date'];
            $service_date = $paymentDetails['service_date'];
            $revenue_stream_id = $paymentDetails['revenue_stream_id'];
            $member = getMemberDetails($conn, $memberId)->fetch_assoc();
            $sub_parish_id = $member['sub_parish_id'];
            $community_id = $member['community_id'];
            $member_phone = $member['phone'];
            $member_email = $member['email'];

            $paymentDate = date('Y-m-d', strtotime($local_timestamp));
            $payment_method = "Mobile Payment";
            $recordedBy = null;

            // Insert into head_parish_revenues table
            $revenueSql = "INSERT INTO head_parish_revenues 
                           (revenue_stream_id, head_parish_id, sub_parish_id, service_number, revenue_amount, 
                            payment_method, recorded_by, revenue_date, description) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $serviceNumber = 1; // Set appropriate service number
            $description = "Payment for Sunday service"; 

            $revenueStmt = $conn->prepare($revenueSql);
            $revenueStmt->bind_param(
                "iiiiiisss",
                $revenue_stream_id,
                $headParishId,
                $sub_parish_id,
                $serviceNumber,
                $amountPaid,
                $payment_method,
                $recordedBy,
                $service_date,
                $description
            );

            if ($revenueStmt->execute()) {
                // Send SMS notification about the offering payment
                $smsSent = sendOfferingSMS($conn, $headParishId, $memberId, $revenue_stream_id, $amountPaid);
                if ($smsSent) {
                    echo json_encode(["success" => true, "message" => "Payment completed, revenue recorded, and SMS sent successfully"]);
                } else {
                    echo json_encode(["success" => true, "message" => "Payment completed and revenue recorded, but failed to send SMS"]);
                }
            } else {
                echo json_encode(["success" => false, "message" => "Failed to record revenue"]);
            }

            $revenueStmt->close();
            $stmt->close();
        }
    } else {
        echo json_encode(["success" => false, "message" => "No payment record found for the provided checkoutRequestId"]);
    }
} else {
    echo json_encode(["success" => false, "message" => $statusResponse['message']]);
}

// Close database connection
$conn->close();
?>
