<?php
header('Content-Type: application/json');

// Include the connection file and additional functionalities
include '../utils/connection.php';
include '../utils/get_member_ids.php';
require_once '../api/send_harambee_receipt_message.php';
require_once '../api/get_member_next_sms_info.php';

require_once('/home/sewmxekb/kanisalangu.sewmrtechnologies.com/config/second_connection.php');
require_once('/home/sewmxekb/kanisalangu.sewmrtechnologies.com/utils/helpers.php');

// Get the input data from the request
$data = json_decode(file_get_contents('php://input'), true);

// Validate the input data
if (!isset($data['checkoutRequestId'], $data['description'])) {
    echo json_encode(['status' => false, 'message' => 'Missing required parameters']);
    exit;
}

// Sanitize and validate input data
$checkoutRequestId = trim($data['checkoutRequestId']);
$description = trim($data['description']);

// Check if fields are empty
if (empty($checkoutRequestId) || empty($description)) {
    echo json_encode(['status' => false, 'message' => 'All fields are required']);
    exit;
}

// Fetch memberId, sub_parish_id, and payment_date based on checkoutRequestId
$query = "SELECT cm.member_id, cm.sub_parish_id, cmp.payment_date 
          FROM church_members cm 
          INNER JOIN church_members_payments cmp ON cm.member_id = cmp.member_id 
          WHERE cmp.CheckoutRequestID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $checkoutRequestId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => false, 'message' => 'No payment found for the provided Checkout Request ID']);
    exit;
}

$member = $result->fetch_assoc();
$selectedMemberId = $member['member_id'];
$subParishId = $member['sub_parish_id'];
$paymentDate = $member['payment_date']; 
$year = date('Y');

// Get the list of member IDs
$memberIds = getMemberIds($memberId, $year);

// Fetch payment amount from the payments table
$query = "SELECT amount_paid FROM church_members_payments WHERE CheckoutRequestID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $checkoutRequestId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => false, 'message' => 'No amount found for the provided Checkout Request ID']);
    exit;
}

$payment = $result->fetch_assoc();
$amountPaid = $payment['amount_paid'];

// Convert the payment date to a DateTime object
$paymentDateTimeObj = new DateTime($paymentDate);

// Calculate the date of the upcoming Sunday's date based on the payment date
$dayOfWeek = $paymentDateTimeObj->format('w'); // 0 (for Sunday) through 6 (for Saturday)
if ($dayOfWeek == 0) {
    // Today is Sunday, no change needed
    $sundayDate = $paymentDateTimeObj->format('Y-m-d');
} else {
    // Move to the upcoming Sunday
    $sundayDate = $paymentDateTimeObj->modify('next sunday')->format('Y-m-d');
}

$currentDateFormatted = $paymentDateTimeObj->format('Y-m-d');

// Function to update payment status
function updatePaymentStatus($conn, $checkoutRequestId) {
    $query = "UPDATE church_members_payments SET payment_status = 'Completed' WHERE CheckoutRequestID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $checkoutRequestId);
    $stmt->execute();
}

// Determine whether to update `harambee` or `envelope` based on the description
if (stripos($description, 'harambee') !== false) {

    $payment_method = 'Mobile Payment';
    $target_table = 'head-parish';
    $harambee_id = 1;
    $head_parish_id = 2;
    $amount = $amountPaid;
    $local_timestamp = date("Y-m-d H:i:s");
    // Fetch members based on the harambee_id
    $members_response = getMemberHarambeeDetails($new_conn, $selectedMemberId, $harambee_id, $target_table); 
    
    if (empty($members_response['members'])) {
        echo json_encode(["status" => false, "message" => "No members found for the given harambee."]);
        exit();
    }

    // Update payment status
    updatePaymentStatus($conn, $checkoutRequestId);

    // Prepare to insert contributions
    foreach ($members_response['members'] as $member) {
        $sub_parish_id = $member['sub_parish_id'];
        $community_id = $member['community_id'];
        $member_id = $member['member_id'];
        $member_phone = $member['phone'];
        $member_email = $member['email'];
        
        // Determine which contribution table to use
        $contribution_table = 'head_parish_harambee_contribution';

        // Insert contribution into the respective table
        $stmt = $new_conn->prepare("INSERT INTO $contribution_table (harambee_id, member_id, amount, contribution_date, recorded_by, head_parish_id, sub_parish_id, community_id, payment_method, local_timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iidssiiiss", $harambee_id, $member_id, $amount, $paymentDate, 1, $head_parish_id, $sub_parish_id, $community_id, $payment_method, $local_timestamp);

        if (!$stmt->execute()) {
            echo json_encode(["status" => false, "message" => "Failed to record contribution for member ID $member_id"]);
            $stmt->close();
            exit();
        }
        $stmt->close();

        // Recalculate the total contribution after insertion
        $total_contribution = calculateTotalContributions($new_conn, $member_id, $harambee_id, $contribution_table)->fetch_assoc()['total_contribution'] ?? 0;;
        // Update member array with the new total contribution
        $member['total_contribution'] = $total_contribution;

        $target = $target_table;
        
        // Send email
        if (!sendHarambeeContributionEmail($amount, $member)) {
            echo json_encode(["status" => false, "message" => "Failed to send email to member ID $member_id"]);
            exit();
        }
    
        // Send SMS
        if (!sendHarambeeContributionSMS($new_conn, $amount, $member, $contribution_date, $target, $harambee_id)) {
            echo json_encode(["status" => false, "message" => "Failed to send SMS to member ID $member_id"]);
            exit();
        }
        
        // Retrieve group details
        $harambeeGroupDetails = getHarambeeGroupDetails($new_conn, $harambee_id, $member_id, $target_table);
        $contributing_member = $member; 
        
        if ($harambeeGroupDetails) {
            $harambee_group_id = $harambeeGroupDetails['harambee_group_id'];
            $group_name = $harambeeGroupDetails['harambee_group_name'];
            $group_target = $harambeeGroupDetails['harambee_group_target'];
            $harambee_description = $harambeeGroupDetails['harambee_description'];
            $date_created = $harambeeGroupDetails['date_created'];
            $contribution_start_date = $date_created;
            // Retrieve the member IDs for the specified group
            $harambeeGroupMemberIds = getHarambeeGroupMemberIds($new_conn, $target_table, $harambee_group_id);
        
            if ($harambeeGroupMemberIds) {
                // Calculate the total contributions for this group
                $totalHarambeeGroupContributions = geTotalHarambeeGroupContribution($new_conn, $harambeeGroupMemberIds, $harambee_id, $target_table, $contribution_start_date);
                // Notify each member by SMS, including their contribution amount
                foreach ($harambeeGroupMemberIds as $harambee_group_member_id) {
                    // Fetch member details based on the member ID
                    $result = getMemberDetails($new_conn, $harambee_group_member_id);
                    
                    // Ensure member details were fetched successfully
                    if ($result && $harambee_group_member = $result->fetch_assoc()) { 
                        // Call the SMS notification function
                        notifyHarambeeGroupMembersBySMS(
                            $new_conn,
                            $group_target,
                            $totalHarambeeGroupContributions,
                            $group_name,
                            $harambee_description,
                            $harambee_group_member, 
                            $contributing_member,
                            $amount,
                            $contribution_date,
                            $target,
                            $harambee_id
                        ); 
                    }
                }
            }
        }
        
        


    }

    echo json_encode(["status" => true, "message" => "Contributions recorded successfully"]);

    // Respond with success for Harambee
    echo json_encode([
        'status' => true,
        'message' => 'Payment approved and recorded as Harambee successfully',
        'redirect' => '/sewmr/member/dashboard'
    ]);
} elseif (stripos($description, 'bahasha') !== false) {
    // Insert into `envelope` table with the Sunday date
    $query = "INSERT INTO envelope (envelope_member_id, envelope_amount, envelope_date) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ids', $memberId, $amountPaid, $sundayDate);
    $stmt->execute();

    updatePaymentStatus($conn, $checkoutRequestId);

    // Respond with success for Bahasha
    echo json_encode([
        'status' => true,
        'message' => 'Payment approved and recorded as Bahasha successfully',
        'redirect' => '/sewmr/member/dashboard'
    ]);

} elseif (stripos($description, 'fungu la kumi') !== false) {
    // Handle Fungu La Kumi payment
    $query = "INSERT INTO fungu_la_kumi (member_id, amount, date_paid) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ids', $memberId, $amountPaid, $currentDateFormatted);
    $stmt->execute();

    updatePaymentStatus($conn, $checkoutRequestId);

    echo json_encode([
        'status' => true,
        'message' => 'Payment approved and recorded as Fungu La Kumi successfully',
        'redirect' => '/sewmr/member/dashboard'
    ]);

} elseif (stripos($description, 'shukrani ya pekee') !== false) {
    // Handle Shukrani Ya Pekee payment
    $query = "INSERT INTO shukrani_ya_pekee (member_id, amount, date_paid) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ids', $memberId, $amountPaid, $currentDateFormatted);
    $stmt->execute();

    updatePaymentStatus($conn, $checkoutRequestId);

    echo json_encode([
        'status' => true,
        'message' => 'Payment approved and recorded as Shukrani Ya Pekee successfully',
        'redirect' => '/sewmr/member/dashboard'
    ]);

} else {
    echo json_encode(['status' => false, 'message' => 'Invalid description provided']);
    exit;
}
?>

