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
    $sql = "SELECT * FROM envelope_payments WHERE CheckoutRequestID = ?";
    $fetchStmt = $conn->prepare($sql);
    $fetchStmt->bind_param("s", $checkoutRequestId);
    $fetchStmt->execute();
    $result = $fetchStmt->get_result();

    if ($result->num_rows > 0) {
        $paymentDetails = $result->fetch_assoc();
        $paymentStatus = $paymentDetails['payment_status'];
        $fetchStmt->close();

        if ($paymentStatus == 'Completed') {
            echo json_encode([
                "success" => false,
                "message" => "Payment already processed",
                "payment_details" => $paymentDetails
            ]);
        } else {
            // Update payment status
            $updateSql = "UPDATE envelope_payments SET payment_status = 'Completed' WHERE CheckoutRequestID = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("s", $checkoutRequestId);
            $updateStmt->execute();
            $updateStmt->close();

            // Record contribution
            $memberId = $paymentDetails['member_id'];
            $headParishId = $paymentDetails['head_parish_id'];
            $amountPaid = $paymentDetails['amount_paid'];
            $local_timestamp = $paymentDetails['payment_date'];

            $member = getMemberDetails($conn, $memberId)->fetch_assoc();
            $sub_parish_id = $member['sub_parish_id'];
            $community_id = $member['community_id'];
            $member_phone = $member['phone'];
            $member_email = $member['email'];

            $paymentDate = date('Y-m-d', strtotime($local_timestamp));
            $payment_method = "Mobile Payment";
            $recordedBy = null;

            // Insert contribution
            $contributionStmt = $conn->prepare(
                "INSERT INTO envelope_contribution (member_id, amount, contribution_date, recorded_by, head_parish_id, sub_parish_id, community_id, payment_method, local_timestamp) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $contributionStmt->bind_param(
                "idssiiiss",
                $memberId,
                $amountPaid,
                $paymentDate,
                $recordedBy,
                $headParishId,
                $sub_parish_id,
                $community_id,
                $payment_method,
                $local_timestamp
            );

            if ($contributionStmt->execute()) {
                sendEnvelopeContributionSMS($conn, $memberId, $amountPaid, $paymentDate);

                $program = 'bahasha';
                $revenue_stream_id = getRevenueStreamIdByProgram($conn, $headParishId, $program);

                if (!$revenue_stream_id) {
                    echo json_encode(["success" => false, "message" => "No revenue stream mapped to the program: harambee"]);
                    exit();
                }

                // Insert head parish revenue
                $revenueStmt = $conn->prepare("INSERT INTO head_parish_revenues (
                    revenue_stream_id, head_parish_id, sub_parish_id, service_number,
                    revenue_amount, payment_method, recorded_by, revenue_date, recorded_from
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'web')");

                $service_number = 1;
                $recorded_by = null;
                $payment_method = 'Mobile Payment';

                $revenueStmt->bind_param(
                    "iiiidsss",
                    $revenue_stream_id,
                    $headParishId,
                    $sub_parish_id,
                    $service_number,
                    $amountPaid,
                    $payment_method,
                    $recorded_by,
                    $paymentDate
                );

                if (!$revenueStmt->execute()) {
                    echo json_encode(["success" => false, "message" => "Something went wrong"]);
                    exit();
                }
                $revenueStmt->close();

                echo json_encode(["success" => true, "message" => "Payment completed successfully"]);
            } else {
                echo json_encode(["success" => false, "message" => "Failed to record contribution"]);
            }

            $contributionStmt->close();
        }
    } else {
        $fetchStmt->close();
        echo json_encode(["success" => false, "message" => "No payment record found for the provided checkoutRequestId"]);
    }
} else {
    echo json_encode(["success" => false, "message" => $statusResponse['message']]);
}

// Close database connection
$conn->close();
?>
