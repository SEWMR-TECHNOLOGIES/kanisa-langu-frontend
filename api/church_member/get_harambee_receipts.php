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
    $harambee_id = isset($_POST['harambee_id']) ? intval($_POST['harambee_id']) : null;
    $member_id = isset($_POST['member_id']) ? intval($_POST['member_id']) : null;
    $target = isset($_POST['target']) ? $_POST['target'] : null;

    if (!$harambee_id || !$member_id || !$target) {
        echo json_encode(["success" => false, "message" => "Missing required parameters."]);
        exit();
    }

    // Determine the target table based on the 'target' parameter
    $target_table = '';
    switch ($target) {
        case 'head-parish':
            $target_table = 'head_parish_harambee_contribution';
            break;
        case 'sub-parish':
            $target_table = 'sub_parish_harambee_contribution';
            break;
        case 'community':
            $target_table = 'community_harambee_contribution';
            break;
        case 'groups':
            $target_table = 'groups_harambee_contribution';
            break;
        default:
            echo json_encode(["success" => false, "message" => "Invalid target type provided."]);
            exit();
    }

    // Fetch member details based on ID if available
    if ($member_id) {
        // Call the getMemberDetails function
        $member = getSingleMemberHarambeeDetails($conn, $member_id, $harambee_id, $target);

        // Check if member exists
        if ($member) {
            // Determine the full name or group name and make it uppercase
            $member_name = ($member['group_name'] == null) ? strtoupper(getMemberFullName($member)) : strtoupper($member['group_name']);
        }
    }
    
   // Fetch member's target and contributions
    $memberDetails = getMemberTargetAndContributions($conn, $harambee_id, $member_id, $target);

    if ($memberDetails === false) {
        echo json_encode(["success" => false, "message" => "Unable to fetch member details."]);
        exit();
    }

    // Extract and calculate values
    $target_amount = $memberDetails['target_amount'];
    $total_received = 0;
    // Fetch contributions
    $contributions = [];
    $current_balance = $target_amount;
    $stmt = $conn->prepare("SELECT harambee_contribution_id, contribution_date, amount as amount_contributed, payment_method 
                            FROM $target_table 
                            WHERE member_id = ? AND harambee_id = ? 
                            ORDER BY contribution_date ASC");
    $stmt->bind_param("ii", $member_id, $harambee_id);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            // Track balance and handle over-contributions
            if($target_amount > 0){
                $current_balance -= $row['amount_contributed'];
            }else{
                $current_balance = 0;
            }
            
            $status = $current_balance < 0 ? "Extra" : "Remaining";
            
            // Generate receipt number
            $receipt_number = "RCPT-" . date("Ymd", strtotime($row['contribution_date'])) . "-" . str_pad($row['harambee_contribution_id'], 4, "0", STR_PAD_LEFT);
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
