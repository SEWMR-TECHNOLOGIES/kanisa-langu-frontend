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

// Validate required field - checkoutRequestId
if (!isset($_POST['checkoutRequestId'])) {
    echo json_encode(["success" => false, "message" => "checkoutRequestId is required"]);
    exit();
}

// Sanitize input
$checkoutRequestId = trim($_POST['checkoutRequestId']);

// Validate that checkoutRequestId is not empty
if (empty($checkoutRequestId)) {
    echo json_encode(["success" => false, "message" => "checkoutRequestId cannot be empty"]);
    exit();
}

// Check transaction status
$paymentGateway = new PaymentGateway($conn);
$statusResponse = $paymentGateway->checkTransactionStatus($checkoutRequestId);

// If true, payment has already been made
if ($statusResponse['status']) {
    // Payment is successful, now check if it's already completed in the database
    $paymentStatus = 'Pending'; // Default to Pending

    $sql = "SELECT * FROM harambee_payments WHERE CheckoutRequestID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $checkoutRequestId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Payment already exists in the database
        $paymentDetails = $result->fetch_assoc();
        $paymentStatus = $paymentDetails['payment_status'];

        if ($paymentStatus == 'Completed') {
            // Payment already completed, return the details
            echo json_encode([
                "success" => false,
                "message" => "Payment already processed",
                "payment_details" => $paymentDetails
            ]);
        } else {
            // Payment is made but not yet marked as completed, update the payment details
            // Extract the harambee payment details here
            $memberId = $paymentDetails['member_id'];
            $harambeeId = $paymentDetails['harambee_id'];
            $headParishId = $paymentDetails['head_parish_id'];
            $amountPaid = $paymentDetails['amount_paid'];
            $paymentGateway = $paymentDetails['PaymentGateway'];
            $merchantRequestId = $paymentDetails['MerchantRequestID'];
            $transactionReference = $paymentDetails['TransactionReference'];
            $target = $paymentDetails['target'];
            $local_timestamp = $paymentDetails['payment_date'];
            
            $selected_member_id = $memberId;
            $payment_method = "Mobile Payment";
            // Convert payment_date to YYYYY-MM-DD format
            $paymentDate = date('Y-m-d', strtotime($paymentDetails['payment_date']));

            // Additional processing for updating the payment status if required
            $updateSql = "UPDATE harambee_payments SET payment_status = 'Completed' WHERE CheckoutRequestID = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("s", $checkoutRequestId);
            $updateStmt->execute();
            $updateStmt->close();
    
    
            // Fetch members based on the harambee_id
            $members_response = getMemberHarambeeDetails($conn, $selected_member_id, $harambeeId, $target); 
    
            if (empty($members_response['members'])) {
                echo json_encode(["success" => false, "message" => "No members found for the given harambee."]);
                exit();
            }
            
            $isMrAndMrs = false;
            $mrAndMrsName = "";
            $notifiedGrouMembers = false;
            // Call hasGroupTargetType to check if the member already has a group target type
            if (hasGroupTargetType($conn, $harambeeId, $selected_member_id, $target)) {
                $isMrAndMrs = true;
                // Get the member IDs from getMrAndMrsMembersIds function
                $mrandmrsids = getMrAndMrsMembersIds($conn, $harambeeId, $memberId, $target);
                $mrAndMrsName = $mrandmrsids[0]['group_name'];
            }
            // Prepare to insert contributions
            foreach ($members_response['members'] as $member) {
                $sub_parish_id = $member['sub_parish_id'];
                $community_id = $member['community_id'];
                $member_id = $member['member_id'];
                $member_phone = $member['phone'];
                $member_email = $member['email'];
                
                // Determine which contribution table to use
                $contribution_table = '';
                switch ($target) {
                    case 'head-parish':
                        $contribution_table = 'head_parish_harambee_contribution';
                        break;
                    case 'sub-parish':
                        $contribution_table = 'sub_parish_harambee_contribution';
                        break;
                    case 'community':
                        $contribution_table = 'community_harambee_contribution';
                        break;
                    case 'groups':
                        $contribution_table = 'groups_harambee_contribution';
                        break;
                    default:
                        echo json_encode(["success" => false, "message" => "Invalid target specified."]);
                        exit();
                }
        
                // Assuming $recordedBy might be null, handle accordingly
                $recordedBy = null; // or some logic to assign it dynamically
                
                $stmt = $conn->prepare("INSERT INTO $contribution_table (harambee_id, member_id, amount, contribution_date, recorded_by, head_parish_id, sub_parish_id, community_id, payment_method, local_timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                // Binding parameters, ensuring null values are handled properly
                $stmt->bind_param("iidssiiiss", $harambeeId, $member_id, $amountPaid, $paymentDate, $recordedBy, $headParishId, $sub_parish_id, $community_id, $payment_method, $local_timestamp);
                
                if (!$stmt->execute()) {
                    echo json_encode(["success" => false, "message" => "Failed to record contribution for member ID $member_id"]);
                    $stmt->close();
                    exit();
                }
                
                $stmt->close();

        
                // Recalculate the total contribution after insertion
                $total_contribution = calculateTotalContributions($conn, $member_id, $harambeeId, $contribution_table)->fetch_assoc()['total_contribution'] ?? 0;
                // Update member array with the new total contribution
                $member['total_contribution'] = $total_contribution;
        
                // Send email
                if (!sendHarambeeContributionEmail($conn, $amountPaid, $member, $paymentDate, $target, $harambeeId)) {
                    echo json_encode(["success" => false, "message" => "Failed to send email to member ID $member_id"]);
                    exit();
                }
            
                // Send SMS
                if (!sendHarambeeContributionSMS($conn, $amountPaid, $member, $paymentDate, $target, $harambeeId)) {
                    echo json_encode(["success" => false, "message" => "Failed to send SMS to member ID $member_id"]);
                    exit();
                }
                
                // Retrieve group details
                $harambeeGroupDetails = getHarambeeGroupDetails($conn, $harambeeId, $member_id, $target);
                $contributing_member = $member; 
                
                if ($harambeeGroupDetails  && $notifiedGrouMembers == false) {
                    $harambee_group_id = $harambeeGroupDetails['harambee_group_id'];
                    $group_name = $harambeeGroupDetails['harambee_group_name'];
                    $group_target = $harambeeGroupDetails['harambee_group_target'];
                    $harambee_description = $harambeeGroupDetails['harambee_description'];
                    $date_created = $harambeeGroupDetails['date_created'];
                    $contribution_start_date = $date_created;
                    // Retrieve the member IDs for the specified group
                    $harambeeGroupMemberIds = getHarambeeGroupMemberIds($conn, $target, $harambee_group_id);
                
                    if ($harambeeGroupMemberIds) {
                        // Calculate the total contributions for this group
                        $totalHarambeeGroupContributions = geTotalHarambeeGroupContribution($conn, $harambeeGroupMemberIds, $harambeeId, $target, $contribution_start_date);
                        // Notify each member by SMS, including their contribution amount
                        foreach ($harambeeGroupMemberIds as $harambee_group_member_id) {
                            // Fetch member details based on the member ID
                            $result = getMemberDetails($conn, $harambee_group_member_id);
                            
                            // Ensure member details were fetched successfully
                            if ($result && $harambee_group_member = $result->fetch_assoc()) { 
                                // Call the SMS notification function
                                notifyHarambeeGroupMembersBySMS(
                                    $conn,
                                    $group_target,
                                    $totalHarambeeGroupContributions,
                                    $group_name,
                                    $harambee_description,
                                    $harambee_group_member, 
                                    $contributing_member,
                                    $amountPaid,
                                    $paymentDate,
                                    $target,
                                    $harambeeId,
                                    $isMrAndMrs,
                                    $mrAndMrsName
                                ); 
                            }
                        }
                    }
                }
                $notifiedGrouMembers = true;
            }
            
            if($target == 'head-parish'){
                $program = 'harambee';
                $revenue_stream_id = getRevenueStreamIdByProgram($conn, $headParishId, $program);
                
                if (!$revenue_stream_id) {
                    echo json_encode(["success" => false, "message" => "No revenue stream mapped to the program: harambee"]);
                    exit();
                }

                
                // Assuming $sub_parish_id is fetched from one of the members already
                $stmt = $conn->prepare("INSERT INTO head_parish_revenues (
                    revenue_stream_id, head_parish_id, sub_parish_id, service_number,
                    revenue_amount, payment_method, recorded_by, revenue_date, recorded_from
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'web')");
                
                $service_number = 1;
                $recorded_by = null;
                $payment_method = 'Mobile Payment';
                
                $stmt->bind_param(
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
                
                if (!$stmt->execute()) {
                    echo json_encode(["success" => false, "message" => "Something went wrong"]);
                    exit();
                }
                $stmt->close();
            }

            echo json_encode(["success" => true, "message" => "Payment completed successfully"]);
    
        }
    } else {
        // No payment record found for the given CheckoutRequestID
        echo json_encode([
            "success" => false,
            "message" => "No payment record found for the provided checkoutRequestId."
        ]);
    }

} else {
    // Handle the status response directly from the function
    echo json_encode([
        "success" => false,
        "message" => $statusResponse['message']
    ]);
}

// Close database connection
$conn->close();
?>
