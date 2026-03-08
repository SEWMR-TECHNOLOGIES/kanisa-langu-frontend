<?php
header('Content-Type: application/json');
session_start();
set_time_limit(0); // no limit

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');


// Check if head_parish_id is in session
if (!isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Head Parish ID is missing from session"]);
    exit();
}

$head_parish_id = $_SESSION['head_parish_id'];

// Check the database connection
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $harambee_id = isset($_POST["harambee_id"]) ? intval($_POST["harambee_id"]) : 0;
    $target = isset($_POST["target"]) ? $conn->real_escape_string($_POST["target"]) : null;
    $category = isset($_POST["category"]) ? $conn->real_escape_string($_POST["category"]) : null;
    $message_template = isset($_POST["message_template"]) ? $conn->real_escape_string($_POST["message_template"]) : null;
    $exclude_members_in_groups = isset($_POST["exclude_members_in_groups"]) ? $conn->real_escape_string($_POST["exclude_members_in_groups"]) : false;

    if ($harambee_id <= 0) {
        echo json_encode(["success" => false, "message" => "Please select Harambee"]);
        exit();
    }

    if (empty($target)) {
        echo json_encode(["success" => false, "message" => "Summary target is required"]);
        exit();
    }
    
    if (empty($category)) {
        echo json_encode(["success" => false, "message" => "Please select valid category"]);
        exit();
    }

    if (empty($message_template)) {
        echo json_encode(["success" => false, "message" => "Message can not be empty"]);
        exit();
    }

    $encrypted_harambee_id = encryptData($harambee_id);
    $report_url = '';

    switch ($target) {
        case 'head-parish':
        case 'sub-parish':
            $sub_parish_id = isset($_POST['sub_parish_id']) ? intval($_POST['sub_parish_id']) : 0;
            if ($sub_parish_id <= 0) {
                echo json_encode(["success" => false, "message" => "Sub Parish is required"]);
                exit();
            }
            break;

        case 'community':
            $sub_parish_id = isset($_POST['sub_parish_id']) ? intval($_POST['sub_parish_id']) : 0;
            $community_id = isset($_POST['community_id']) ? intval($_POST['community_id']) : 0;
            if ($community_id <= 0 || $sub_parish_id <= 0) {
                echo json_encode(["success" => false, "message" => "Community and Sub Parish are required"]);
                exit();
            }
            break;

        case 'group':
            $group_id = isset($_POST['group_id']) ? intval($_POST['group_id']) : 0;
            if ($group_id <= 0) {
                echo json_encode(["success" => false, "message" => "Group is required"]);
                exit();
            }
            break;

        default:
            echo json_encode(["success" => false, "message" => "Invalid distribution target"]);
            exit();
    }

} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit();
}

// Parish and Harambee details
$parish_info = getParishInfo($conn, $head_parish_id);
$harambee_details = get_harambee_details($conn, $harambee_id, $target);

// Determine target table
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
        echo json_encode(["success" => false, "message" => "Invalid target type provided"]);
        exit();
}

// Members and exclusions
$all_member_ids = getHarambeeMemberIds($conn, $harambee_id, $target);
$excluded_ids = getExcludedMemberIds($conn, $head_parish_id, $target, $harambee_id);

$all_member_details_array = [];
$all_members_processed_groups = [];

foreach ($all_member_ids as $member_id) {
    $member = getSingleMemberHarambeeDetails($conn, $member_id, $harambee_id, $target);

    if (isset($sub_parish_id) && $sub_parish_id && $member['sub_parish_id'] != $sub_parish_id) {
        continue;
    }

    if($exclude_members_in_groups && $member['is_in_groups']){
        continue;
    }

    if ($member['group_name'] != null) {
        if (in_array($member['group_name'], $all_members_processed_groups)) continue;
        $full_name = $member['group_name'];
        $all_members_processed_groups[] = $member['group_name'];
    } else {
        $full_name = getMemberFullName($member);
    }

    $memberDetails = getMemberTargetAndContributions($conn, $harambee_id, $member_id, $target);
    if ($memberDetails === false) {
        echo json_encode(["success" => false, "message" => "Invalid target or unable to fetch details"]);
        exit();
    }

    $target_amount = $memberDetails['target_amount'];
    $total_contribution = $memberDetails['total_contribution'];
    $balance = ($target_amount > 0) ? ($target_amount - $total_contribution) : 0;
    $percentage = $target_amount > 0 ? calculatePercentage($total_contribution, $target_amount) : 0.00;

    if ($target_amount > 0 && $total_contribution == 0) {
        $contribution_category = 'not_contributed';  
    } elseif ($target_amount >= 0 && $total_contribution >= $target_amount && $total_contribution > 0) {
        $contribution_category = 'completed';  
    } elseif ($target_amount > 0 && $total_contribution < $target_amount) {
        $contribution_category = 'on_progress';  
    }

    $all_member_details_array[] = [
        'name' => htmlspecialchars($full_name),
        'member_id' => $member['member_id'],
        'sub_parish_id' => $member['sub_parish_id'],
        'community_id' => $member['community_id'],
        'first_name' => $member['first_name'],
        'middle_name' => $member['middle_name'],
        'last_name' => $member['last_name'],
        'envelope_number' => $member['envelope_number'],
        'title' => $member['title'],
        'group_name' => $member['group_name'],
        'member_type' => $member['member_type'], 
        'phone' => $member['phone'],
        'email' => $member['email'],
        'diocese_name' => $member['diocese_name'],
        'province_name' => $member['province_name'],
        'head_parish_name' => $member['head_parish_name'],
        'head_parish_id' => $member['head_parish_id'],
        'sub_parish_name' => str_replace('MTAA WA ', '', $member['sub_parish_name']),
        'community_name' => $member['community_name'],
        'harambee_description' => $member['harambee_description'],
        'is_in_groups' => $member['is_in_groups'],
        'target' => $target_amount,
        'contribution' => $total_contribution, 
        'balance' => $balance,
        'percentage' => $percentage,
        'category' => $contribution_category  
    ];
}

foreach ($all_member_details_array as $member) {
    if ($member['category'] !== $category) continue;
    if (in_array($member['member_id'], $excluded_ids)) continue;

    $smsInfo = get_sms_credentials($conn, $member['head_parish_id']);
    if (!$smsInfo) continue;

    $smsClient = new SewmrSMSClient($smsInfo['api_token'], $smsInfo['sender_id']);

    $accountantDetails = getHarambeeAccountant($conn, $target, $harambee_id, 'harambee');
    $accountant_phone = $accountantDetails['phone'] ?? '';

    $recipientName = $member['group_name'] ?: ucwords(strtolower($member['first_name']));

    $formatted_target = number_format($member['target'], 0); 
    $formatted_contribution = number_format($member['contribution'], 0); 
    $formatted_balance = number_format($member['balance'], 0); 
    $formatted_percentage = number_format($member['percentage'], 2); 

    $message = str_replace(
        ['{NAME}', '{TARGET}', '{CONTRIBUTION}', '{BALANCE}', '{PROGRESS}', '{PHONE}'],
        [$recipientName, $formatted_target, $formatted_contribution, $formatted_balance, $formatted_percentage, $accountant_phone],
        $message_template
    );

    $message = str_replace(['\r\n', '\r', '\n'], "\n", $message);

    try {
        $response = $smsClient->sendQuickSMS(null, $message, [$member['phone']]);
    } catch (Exception $e) {
        error_log("SewmrSMSClient exception for {$member['phone']}: " . $e->getMessage());
        continue;
    }

    if (!isset($response['success']) || $response['success'] !== true) {
        $errorMessage = $response['message'] ?? 'Unknown error';
        error_log("Failed to send SMS to {$member['phone']}: $errorMessage");
        continue;
    }

    error_log("SMS sent successfully to {$member['phone']}");
    sleep(1);
}

echo json_encode(["success" => true, "message" => "Notifications sent successfully!"]);
exit();
?>
