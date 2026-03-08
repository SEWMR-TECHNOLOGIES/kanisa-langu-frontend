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
    $year = isset($_POST['year']) ? intval($_POST['year']) : date('Y');
    $member_id = isset($_POST['member_id']) ? intval($_POST['member_id']) : null;

    if (!$year || !$member_id) {
        echo json_encode(["success" => false, "message" => "Missing required parameters."]);
        exit();
    }

    $member = getMemberDetails($conn, $member_id)->fetch_assoc();
    if (!$member) {
        echo json_encode(["success" => false, "message" => "Member not found."]);
        exit();
    }
    $member_name = strtoupper(getMemberFullName($member));
    
    
    $envelopeData = fetchMemberEnvelopeData($conn, $member_id, $year);

    if (empty($envelopeData)) {
        echo json_encode(["success" => false, "message" => "Unable to fetch envelope data."]);
        exit();
    }

    $total_contribution = $envelopeData['total_envelope_contribution'];
    $target_amount = $envelopeData['yearly_envelope_target'];

    
   // Fetch contributions
    $contributions = [];
    $total_received = 0;
    $current_balance = $target_amount;
    $stmt = $conn->prepare("SELECT envelope_contribution_id, contribution_date, amount as amount_contributed, payment_method 
                            FROM envelope_contribution 
                            WHERE member_id = ? AND YEAR(contribution_date) = ? 
                            ORDER BY contribution_date ASC");
    $stmt->bind_param("ii", $member_id, $year);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            // Track balance and handle over-contributions
            if($target_amount > 0){
                $current_balance -= $row['amount_contributed'];
            }else{
                $current_balance = 0;
            }
            $status = $current_balance < 0 ? "Extra" : "Balance";
            
            $receipt_number = "RCPT-" . date("Ymd", strtotime($row['contribution_date'])) . "-" . str_pad($row['envelope_contribution_id'], 4, "0", STR_PAD_LEFT);
            
            $total_received += $row['amount_contributed'];
            // Store the contribution details
            $contributions[] = [
                "target_amount" => $target_amount,
                "member_name" => $member_name,
                "receipt_number" => $receipt_number,
                "date" => $row['contribution_date'],
                "amount" => $row['amount_contributed'],
                "payment_method" => $row['payment_method'],
                "total_received" => $total_received,
                "balance_on_date" => $current_balance, 
                "status" => $status // Add status for extra contribution
            ];
        }
    }
    $stmt->close();
    
    
    // Sort contributions by date in descending order after processing the balance logic
    usort($contributions, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']); // Compare dates for descending order
    });

    // Return the JSON response with contributions and status
    echo json_encode([
        "success" => true,
        "receipts" => $contributions
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}

$conn->close();
