<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');

if (!isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Head Parish ID is missing from session"]);
    exit();
}

$head_parish_id = $_SESSION['head_parish_id'];

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $harambee_id = isset($_POST['harambee_id']) ? $conn->real_escape_string($_POST['harambee_id']) : null;
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0.00;
    $target_table = isset($_POST['target_table']) ? $conn->real_escape_string($_POST['target_table']) : null;
    $contribution_date = isset($_POST['contribution_date']) ? $conn->real_escape_string($_POST['contribution_date']) : null;
    $local_timestamp = isset($_POST['local_timestamp']) ? $conn->real_escape_string($_POST['local_timestamp']) : null;
    $payment_method = isset($_POST['payment_method']) ? $conn->real_escape_string($_POST['payment_method']) : null;
    $selected_member_id = isset($_POST['member_id']) ? $conn->real_escape_string($_POST['member_id']) : null;


    // Validate mandatory fields separately
    if (empty($selected_member_id)) { // New validation for member_id
        echo json_encode(["success" => false, "message" => "Member is required."]);
        exit();
    }

    if (empty($harambee_id)) {
        echo json_encode(["success" => false, "message" => "Please select Harambee"]);
        exit();
    }

    if ($amount <= 0) {
        echo json_encode(["success" => false, "message" => "Amount must be greater than 0."]);
        exit();
    }

    if (empty($contribution_date)) {
        echo json_encode(["success" => false, "message" => "Contribution date is required."]);
        exit();
    }

    if (empty($local_timestamp)) {
        echo json_encode(["success" => false, "message" => "Local timestamp is required."]);
        exit();
    }

    if (empty($payment_method)) {
        echo json_encode(["success" => false, "message" => "Payment method is required."]);
        exit();
    }

    // Fetch members based on the harambee_id
    $members_response = getMemberHarambeeDetails($conn, $selected_member_id, $harambee_id, $target_table); 
    
    
    if (empty($members_response['members'])) {
        echo json_encode(["success" => false, "message" => "No members found for the given harambee."]);
        exit();
    }

    $conn->begin_transaction(); // Start transaction


    try {
        $isMrAndMrs = false;
        $mrAndMrsName = null;
        $notifiedGrouMembers = false;

        if (hasGroupTargetType($conn, $harambee_id, $selected_member_id, $target_table)) {
            $isMrAndMrs = true;
            $mrandmrsids = getMrAndMrsMembersIds($conn, $harambee_id, $selected_member_id, $target_table);
            $mrAndMrsName = $mrandmrsids[0]['group_name'];
        }
        
        foreach ($members_response['members'] as $member) {
            $sub_parish_id = $member['sub_parish_id'];
            $community_id = $member['community_id'];
            $member_id = $member['member_id'];
            $member_phone = $member['phone'];
            $member_email = $member['email'];

            switch ($target_table) {
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
                    throw new Exception("Invalid target specified.");
            }

            $stmt = $conn->prepare("INSERT INTO $contribution_table (harambee_id, member_id, amount, contribution_date, recorded_by, head_parish_id, sub_parish_id, community_id, payment_method, local_timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iidssiiiss", $harambee_id, $member_id, $amount, $contribution_date, $_SESSION['head_parish_admin_id'], $head_parish_id, $sub_parish_id, $community_id, $payment_method, $local_timestamp);

            if (!$stmt->execute()) {
                throw new Exception("Failed to record contribution for member ID $member_id");
            }
            $stmt->close();

            $total_contribution = calculateTotalContributions($conn, $member_id, $harambee_id, $contribution_table)->fetch_assoc()['total_contribution'] ?? 0;
            $member['total_contribution'] = $total_contribution;
            $target = $target_table;

           $smsSent = sendHarambeeContributionSMS($conn, $amount, $member, $contribution_date, $target, $harambee_id);

            $email = trim($member['email'] ?? '');
            $emailSent = true; // assume true unless we attempt and fail

            if ($email !== '') {
                $emailSent = sendHarambeeContributionEmail($conn, $amount, $member, $contribution_date, $target, $harambee_id);
                if (!$emailSent) {
                    throw new Exception("Failed to send email to member ID $member_id");
                }
            }

            if (!$smsSent) {
                if ($email === '' || !$emailSent) {
                    throw new Exception("Failed to send SMS to member ID $member_id and no successful fallback via email");
                }
            }

            $harambeeGroupDetails = getHarambeeGroupDetails($conn, $harambee_id, $member_id, $target_table);
            $contributing_member_name = $member['first_name'];

            if ($harambeeGroupDetails && !$notifiedGrouMembers) {
                $stmt = $conn->prepare("INSERT INTO delayed_harambee_notifications (harambee_id, member_id, target, contribution_date, amount, contributing_member_name, mr_and_mrs_name, is_mr_and_mrs) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iissdssi", $harambee_id, $member_id, $target_table, $contribution_date, $amount, $contributing_member_name, $mrAndMrsName, $isMrAndMrs);
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to record delayed notification for member ID $selected_member_id");
                }
                $stmt->close();
            }

            $notifiedGrouMembers = true;
        }
        
        if($target_table == 'head-parish'){
            $program = 'harambee';
            $revenue_stream_id = getRevenueStreamIdByProgram($conn, $head_parish_id, $program);
            
            if (!$revenue_stream_id) {
                throw new Exception("No revenue stream mapped to the program: harambee");
            }
            
            // Assuming $sub_parish_id is fetched from one of the members already
            $stmt = $conn->prepare("INSERT INTO head_parish_revenues (
                revenue_stream_id, head_parish_id, sub_parish_id, service_number,
                revenue_amount, payment_method, recorded_by, revenue_date, recorded_from
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'web')");
            
            $service_number = 1;
            $recorded_by = $_SESSION['head_parish_admin_id']; 
            
            $stmt->bind_param(
                "iiiidsss",
                $revenue_stream_id,
                $head_parish_id,
                $sub_parish_id,
                $service_number,
                $amount,
                $payment_method,
                $recorded_by,
                $contribution_date
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert into head_parish_revenues");
            }
            $stmt->close();
        }

        $conn->commit(); 
        echo json_encode(["success" => true, "message" => "Harambee Contribution and revenue recorded successfully"]);
    } catch (Exception $e) {
        $conn->rollback(); // Roll back transaction if any step fails
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }

} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();

?>
