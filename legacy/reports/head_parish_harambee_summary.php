<?php
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');

// Initialize variables from GET parameters
$harambee = isset($_GET['harambee']) ? $_GET['harambee'] : null;
$target = isset($_GET['target']) ? $_GET['target'] : null;
$contribution_date = isset($_GET['contribution_date']) ? $_GET['contribution_date'] : date('Y-m-d');

if ($harambee) {
    try {
        // Attempt to decrypt the harambee ID
        $harambee_id = decryptData($harambee);
        
        // Further validation if necessary
        if (empty($harambee_id) || !preg_match('/^[a-zA-Z0-9]+$/', $harambee_id)) {
            header("Location: /error.php?message=Invalid harambee ID.");
            exit;
        }
    } catch (Exception $e) {
        // Handle decryption failure
        header("Location: /error.php?message=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    // Handle case where harambee ID is not provided
    echo json_encode(["success" => false, "message" => "Harambee ID is required."]);
    exit;
}

if(!isset($_SESSION['head_parish_id'])){
    header("Location: /error.php?message=" . urlencode("Unauthorized"));
    exit();
}
$head_parish_id = $_SESSION['head_parish_id'];
$parish_info = getParishInfo($conn, $head_parish_id);
// Get harambee details
$harambee_details = get_harambee_details($conn, $harambee_id, $target);
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
        echo json_encode(["success" => false, "message" => "Invalid target type provided"]);
        exit();
}

// Get all member IDs
$member_ids = getHarambeeMemberIdsByContributionDate($conn, $harambee_id, $target, $contribution_date);

// Initialize an array to store member details and a set to track processed group names
$member_details_array = [];
$processed_groups = [];

foreach ($member_ids as $member_id) {
    // Get the member details
    $member = getSingleMemberHarambeeDetails($conn, $member_id, $harambee_id, $target);
    
    // Check if the member belongs to a group
    if ($member['group_name'] != null) {
        // If this group has already been processed, skip this member
        if (in_array($member['group_name'], $processed_groups)) {
            continue; // Skip the current iteration
        }
        // Add the group name to the processed groups set
        $full_name = $member['group_name'];
        $processed_groups[] = $member['group_name'];
    } else {
        // If the member is not part of a group, use their individual full name
        $full_name = getMemberFullName($member);
    }
    
    // Get contributions by date
    $contribution_result = getContributionsByDate($conn, $member_id, $harambee_id, $target_table, $contribution_date);
    
    // Get member target and contributions
    $memberDetails = getMemberTargetAndContributions($conn, $harambee_id, $member_id, $target);
    
    if ($memberDetails === false) {
        echo json_encode(["success" => false, "message" => "Invalid target or unable to fetch details"]);
        exit();
    }

    // Extract target and contribution amounts
    $target_amount = $memberDetails['target_amount'];
    $total_contribution = $memberDetails['total_contribution'];
    $balance = $target_amount - $total_contribution;
    $balance = ($target_amount > 0) ? ($target_amount - $total_contribution) : 0;
    $percentage = $target_amount > 0 ? calculatePercentage($total_contribution, $target_amount) : 0.00;

    // Store details in the array
    $member_details_array[] = [
        'name' => htmlspecialchars($full_name),
        'member_id' => $member['member_id'],
        'sub_parish_id' => $member['sub_parish_id'],
        'community_id' => $member['community_id'],
        'first_name' => $member['first_name'],
        'middle_name' => $member['middle_name'],
        'last_name' => $member['last_name'],
        'envelope_number' => $member['envelope_number'],
        'title' => $member['title'],
        'member_type' => $member['member_type'], 
        'phone' => $member['phone'],
        'email' => $member['email'],
        'diocese_name' => $member['diocese_name'],
        'province_name' => $member['province_name'],
        'head_parish_name' => $member['head_parish_name'],
        'sub_parish_name' => str_replace('MTAA WA ', '', $member['sub_parish_name']),
        'community_name' => $member['community_name'],
        'harambee_description' => $member['harambee_description'],
        'target' => $target_amount,
        'contribution' => $total_contribution,
        'balance' => $balance,
        'percentage' => $percentage,
        'responsibility' => $member['responsibility'], 
        'group_name' => $member['group_name'] ?? null 
    ];
}


$harambee_data = [];

// Initialize sub-parish wise storage
$sub_parishes_data = [];

// Initialize array for structured data by sub-parish
$sub_parishes_data = [];

// Iterate over the member details to build the grouped structure
foreach ($member_details_array as $member) {
    $sub_parish_id = $member['sub_parish_id'];
    $sub_parish_name = $member['sub_parish_name'];
    $community_id = $member['community_id'];
    $community_name = $member['community_name'];

    // Initialize sub-parish if not already present
    if (!isset($sub_parishes_data[$sub_parish_id])) {
        $sub_parishes_data[$sub_parish_id] = [
            'sub_parish_id' => $sub_parish_id,
            'sub_parish_name' => $sub_parish_name,
            'communities' => []
        ];
    }

    // Initialize community data within the sub-parish
    if (!isset($sub_parishes_data[$sub_parish_id]['communities'][$community_id])) {
        $sub_parishes_data[$sub_parish_id]['communities'][$community_id] = [
            'community_id' => $community_id,
            'community_name' => $community_name,
            'Cash' => 0,
            'Bank Transfer' => 0,
            'Mobile Payment' => 0,
            'Card' => 0,
            'member_count' => 0  // Initialize member count
        ];
    }

    // Increment the member count for this community
    $sub_parishes_data[$sub_parish_id]['communities'][$community_id]['member_count']++;

    $payment_methods = getContributionMethods($conn, $member['member_id'], $harambee_id, $target, $contribution_date);

    if (is_array($payment_methods)) {
        foreach ($payment_methods as $method => $amount) {
            $sub_parishes_data[$sub_parish_id]['communities'][$community_id][$method] += $amount;
        }
    } else {
        $payment_methods = [];
    }
}

// Convert community data to a structured array for JSON output
foreach ($sub_parishes_data as &$sub_parish) {
    $sub_parish['communities'] = array_values($sub_parish['communities']);
}

// Wrap the result in the desired format
$response = [
    'success' => true,
    'data' => array_values($sub_parishes_data)
];

// Print the JSON response
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
