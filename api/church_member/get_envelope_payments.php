<?php
header('Content-Type: application/json');
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');

// Check if the database connection is successful
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Ensure it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and validate required parameters
    $member_id = isset($_POST['memberId']) ? intval($_POST['memberId']) : null;
    $head_parish_id = isset($_POST['headParishId']) ? intval($_POST['headParishId']) : null;
    $year = isset($_POST['year']) ? $_POST['year'] : null;

    // Validate the required parameters
    if (!$head_parish_id || !$member_id || !$year) {
        echo json_encode(["success" => false, "message" => "Missing required parameters."]);
        exit();
    }

    // Fetch the payment records for the given parameters
    $stmt = $conn->prepare("SELECT 
        payment_id,
        member_id,
        PaymentGateway,
        MerchantRequestID,
        CheckoutRequestID,
        TransactionReference,
        amount_paid,
        payment_reason,
        payment_date,
        payment_status
    FROM envelope_payments 
    WHERE member_id = ? 
      AND YEAR(payment_date) = ? 
      AND head_parish_id = ? 
    ORDER BY payment_date DESC");
    
    $stmt->bind_param("isi", $member_id, $year, $head_parish_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $payments = [];
        
        // Loop through the results and format them
        while ($row = $result->fetch_assoc()) {
            $payments[] = [
                "payment_id" => $row['payment_id'],
                "member_id" => $row['member_id'],
                "PaymentGateway" => $row['PaymentGateway'],
                "MerchantRequestID" => $row['MerchantRequestID'],
                "CheckoutRequestID" => $row['CheckoutRequestID'],
                "TransactionReference" => $row['TransactionReference'],
                "amount_paid" => $row['amount_paid'],
                "payment_reason" => $row['payment_reason'],
                "payment_date" => $row['payment_date'],
                "payment_status" => $row['payment_status']
            ];
        }
        
        // Return the JSON response with payments
        echo json_encode([
            "success" => true,
            "payments" => $payments
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to fetch payments."]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}

$conn->close();
?>
