<?php
header('Content-Type: application/json');
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');

// Check if the database connection is successful
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Ensure it's a GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Retrieve and validate required parameters
    $harambee_id = isset($_GET['harambee_id']) ? intval($_GET['harambee_id']) : null;
    $member_id = isset($_GET['member_id']) ? intval($_GET['member_id']) : null;
    $target = isset($_GET['target']) ? $_GET['target'] : null;

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
            $current_balance -= $row['amount_contributed'];
            $status = $current_balance < 0 ? "Extra Contribution" : "Balance Remaining";
            
            $receipt_number = "RCPT-" . date("Ymd", strtotime($row['contribution_date'])) . "-" . str_pad($row['harambee_contribution_id'], 4, "0", STR_PAD_LEFT);
            
            // Store the contribution details
            $contributions[] = [
                "member_name" => $member_name,
                "receipt_number" => $receipt_number,
                "date" => $row['contribution_date'],
                "amount" => $row['amount_contributed'],
                "payment_method" => $row['payment_method'],
                "balance_on_date" => $current_balance < 0 ? 0 : $current_balance, // Stop balance at 0 if over-contributed
                "status" => $status // Add status for extra contribution
            ];
        }
    }
    
    $stmt->close();
    
    // Return the JSON response with contributions and status
    echo json_encode([
        "success" => true,
        "receipts" => $contributions
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}

$conn->close();
