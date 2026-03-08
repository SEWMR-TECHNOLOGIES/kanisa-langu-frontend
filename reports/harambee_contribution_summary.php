<?php
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/libraries/vendor/autoload.php');

use Dompdf\Dompdf;

// Instantiate Dompdf
$dompdf = new Dompdf();

// Set up the options
$options = $dompdf->getOptions();
$options->setFontCache('fonts');
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);
$options->setChroot([$_SERVER['DOCUMENT_ROOT'] . '/assets/fonts/']);
$dompdf->setOptions($options);

$imageData = base64_encode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/assets/images/logos/kkkt-logo.jpg'));
$src = 'data:image/jpeg;base64,' . $imageData;

// Initialize variables from GET parameters
$harambee_id = isset($_GET['harambee_id']) ? $_GET['harambee_id'] : null;
$target = isset($_GET['target']) ? $_GET['target'] : null;
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-d');
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d');
$sub_parish_id = isset($_GET['sub_parish_id']) ? $_GET['sub_parish_id'] : null;
$group_by_communities = isset($_GET['group_by_communities']) ? $_GET['group_by_communities'] : false;

// Validate harambee_id
if ($harambee_id) {
    try {
        // Attempt to decrypt the harambee ID
        $harambee_id = decryptData($harambee_id);

        // Further validation if necessary
        if (empty($harambee_id) || !preg_match('/^[a-zA-Z0-9]+$/', $harambee_id)) {
            echo json_encode(["success" => false, "message" => "Invalid harambee ID."]);
            exit;
        }
    } catch (Exception $e) {
        // Handle decryption failure
        echo json_encode(["success" => false, "message" => "Error decrypting harambee ID: " . $e->getMessage()]);
        exit;
    }
} else {
    // Handle case where harambee ID is not provided
    echo json_encode(["success" => false, "message" => "Harambee ID is required."]);
    exit;
}

// Validate sub_parish_id if required
if ($sub_parish_id) {
    try {
        // Attempt to decrypt the sub_parish ID
        $sub_parish_id = decryptData($sub_parish_id);

        // Further validation if necessary
        if (empty($sub_parish_id) || !preg_match('/^[a-zA-Z0-9]+$/', $sub_parish_id)) {
            echo json_encode(["success" => false, "message" => "Invalid sub parish ID."]);
            exit;
        }
    } catch (Exception $e) {
        // Handle decryption failure
        echo json_encode(["success" => false, "message" => "Error decrypting sub parish ID: " . $e->getMessage()]);
        exit;
    }
} else {
    // Handle case where sub_parish ID is not provided (optional, depending on your business logic)
    echo json_encode(["success" => false, "message" => "Sub Parish ID is required."]);
    exit;
}


// Validate target
$valid_targets = ['head-parish', 'sub-parish', 'community', 'groups'];
if (!in_array($target, $valid_targets)) {
    echo json_encode(["success" => false, "message" => "Invalid target type provided."]);
    exit;
}

// Validate from_date
if (!strtotime($from_date)) {
    echo json_encode(["success" => false, "message" => "Invalid contribution date format."]);
    exit;
}

// Validate to_date if provided
if ($to_date !== null) {
    if (!strtotime($to_date)) {
        echo json_encode(["success" => false, "message" => "Invalid to_date format."]);
        exit;
    }
    // Ensure to_date is not before from_date
    if (strtotime($to_date) < strtotime($from_date)) {
        echo json_encode(["success" => false, "message" => "'to_date' must be greater than or equal to 'from_date'."]);
        exit;
    }
}


// Validate harambee_id
if (empty($sub_parish_id) || !preg_match('/^[a-zA-Z0-9]+$/', $sub_parish_id)) {
        echo json_encode(["success" => false, "message" => "Invalid sub-parish ID."]);
    exit;
}

if (!isset($_SESSION['head_parish_id'])) {
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

// Get IDs for memmber contributed on date
$on_date_member_ids = getHarambeeMemberIdsByContributionDate($conn, $harambee_id, $target, $from_date, $to_date);


// Initialize an array to store member details and a set to track processed group names
$on_date_member_details_array = [];
$on_date_processed_groups = [];
$grouped_by_community = [];

// MEMBERS ON DATE

foreach ($on_date_member_ids as $member_id) {
    // Get the member details
    $member = getSingleMemberHarambeeDetails($conn, $member_id, $harambee_id, $target);
    
    // If sub_parish_id is provided, filter members based on it
    if ($sub_parish_id && $member['sub_parish_id'] != $sub_parish_id) {
        continue; // Skip this member if their sub_parish_id does not match
    }
    
    // Check if the member belongs to a group
    if ($member['group_name'] != null) {
        // If this group has already been processed, skip this member
        if (in_array($member['group_name'], $on_date_processed_groups)) {
            continue; // Skip the current iteration
        }
        // Add the group name to the processed groups set
        $full_name = $member['group_name'];
        $on_date_processed_groups[] = $member['group_name'];
    } else {
        // If the member is not part of a group, use their individual full name
        $full_name = getMemberFullName($member);
    }
    
    // Get contributions by date (or date range)
    $contribution_result =  getTotalContributionsBetweenDates($conn, $harambee_id, $member_id, $from_date, $to_date, $target_table);
    if ($contribution_result === false) {
        echo json_encode(["success" => false, "message" => "Invalid target or unable to fetch details"]);
        exit();
    }
    // Extract contribution amount before and after
    $amount_contributed_before_date = $contribution_result['total_before_date'];
    $amount_contributed_on_date = $contribution_result['on_date_contributions'];
    $total_contributed_up_to_date = $contribution_result['total_contributed'];
    

	// Get member target and contributions
    $memberDetails = getMemberTargetAndContributions($conn, $harambee_id, $member_id, $target);
    
    if ($memberDetails === false) {
        echo json_encode(["success" => false, "message" => "Invalid target or unable to fetch details"]);
        exit();
    }

    // Extract target and contribution amounts
    $target_amount = $memberDetails['target_amount'];
    $total_contribution = $memberDetails['total_contribution'];
    $balance = $target_amount - $total_contributed_up_to_date;
    $balance = ($target_amount > 0) ? ($target_amount - $total_contributed_up_to_date) : 0;
    $percentage = $target_amount > 0 ? calculatePercentage($total_contributed_up_to_date, $target_amount) : 0.00;

    // Store details in the array
    $member_details = [
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
        'contribution' => $total_contribution, // not based on date but full amount (wont be used mut let it remain)
        'amount_contributed_before_date' => $amount_contributed_before_date,
        'amount_contributed_on_date' => $amount_contributed_on_date,
        'total_contributed_up_to_date' => $total_contributed_up_to_date,
        'balance' => $balance,
        'percentage' => $percentage
    ];
    
    if ($group_by_communities) {
        // Group members by their community_id
        $community_id = $member['community_id'];
        if (!isset($grouped_by_community[$community_id])) {
            $grouped_by_community[$community_id] = [];
        }
        $grouped_by_community[$community_id][] = $member_details;
    }
    
    $on_date_member_details_array[] = $member_details;
    
    
}


// DISTRIBUTED AMOUNT FOR THAT SUB PARISH
$sub_parish_distributed_amount = getDistributedAmount($conn, $target, $sub_parish_id, $harambee_id);

// Initialize sub-parish wise storage - ON DATE
$on_date_harambee_data = [];
$on_date_sub_parishes_data = [];

// Iterate over the member details to build the grouped structure
foreach ($on_date_member_details_array as $member) {
    $sub_parish_id = $member['sub_parish_id'];
    $sub_parish_name = $member['sub_parish_name'];
    $sub_parish_distributed_amount = $sub_parish_distributed_amount;
    $community_id = $member['community_id'];
    $community_name = $member['community_name'];

    // Initialize sub-parish if not already present
    if (!isset($on_date_sub_parishes_data[$sub_parish_id])) {
        $on_date_sub_parishes_data[$sub_parish_id] = [
            'sub_parish_id' => $sub_parish_id,
            'sub_parish_name' => $sub_parish_name,
            'distributed_amount' => $sub_parish_distributed_amount,
            'communities' => []
        ];
    }

    // Initialize community data within the sub-parish
    if (!isset($on_date_sub_parishes_data[$sub_parish_id]['communities'][$community_id])) {
        $on_date_sub_parishes_data[$sub_parish_id]['communities'][$community_id] = [
            'community_id' => $community_id,
            'community_name' => $community_name,
            'total_before_date' => 0,
            'total_on_date' => 0,
            'total_contributed_up_to_date' => 0,
            'member_count' => 0  // Initialize member count
        ];
    }

    // Sum the contributions for this community
    $on_date_sub_parishes_data[$sub_parish_id]['communities'][$community_id]['total_before_date'] += $member['amount_contributed_before_date'];
    $on_date_sub_parishes_data[$sub_parish_id]['communities'][$community_id]['total_on_date'] += $member['amount_contributed_on_date'];
    $on_date_sub_parishes_data[$sub_parish_id]['communities'][$community_id]['total_contributed_up_to_date'] += $member['total_contributed_up_to_date'];

    // Increment the member count for this community
    $on_date_sub_parishes_data[$sub_parish_id]['communities'][$community_id]['member_count']++;
}

// Initialize grand total variables
$grand_total_before_date = 0;
$grand_total_on_date = 0;
$grand_total_contributed_up_to_date = 0;
$grand_member_count = 0;  // Initialize grand member count

// Iterate over the sub-parishes to calculate grand totals and member count
foreach ($on_date_sub_parishes_data as $sub_parish) {  // Remove the reference (`&`)
    foreach ($sub_parish['communities'] as $community) {  // Remove the reference (`&`)
        // Sum the contributions for this community
        $grand_total_before_date += $community['total_before_date'];
        $grand_total_on_date += $community['total_on_date'];
        $grand_total_contributed_up_to_date += $community['total_contributed_up_to_date'];

        // Sum the member counts for this community
        $grand_member_count += $community['member_count'];
    }

    // Reindex communities to structure for JSON output (to reset array keys)
    $sub_parish['communities'] = array_values($sub_parish['communities']);
}


// Prepare final response data
$on_date_harambee_data['sub_parishes'] = array_values($on_date_sub_parishes_data);



// ********************************************************************************************************* //

// ALL MEMBERS 
// Get all member IDs
$all_member_ids = getHarambeeMemberIds($conn, $harambee_id, $target);
// Initialize an array to store member details and a set to track processed group names
$all_member_details_array = [];
$all_members_processed_groups = [];

foreach ($all_member_ids as $member_id) {
    // Get the member details
    $member = getSingleMemberHarambeeDetails($conn, $member_id, $harambee_id, $target);
    
    // If sub_parish_id is provided, filter members based on it
    if ($sub_parish_id && $member['sub_parish_id'] != $sub_parish_id) {
        continue; // Skip this member if their sub_parish_id does not match
    }
    
    // Check if the member belongs to a group
    if ($member['group_name'] != null) {
        // If this group has already been processed, skip this member
        if (in_array($member['group_name'], $all_members_processed_groups)) {
            continue; // Skip the current iteration
        }
        // Add the group name to the processed groups set
        $full_name = $member['group_name'];
        $all_members_processed_groups[] = $member['group_name'];
    } else {
        // If the member is not part of a group, use their individual full name
        $full_name = getMemberFullName($member);
    }
    
 
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
        'percentage' => $percentage
    ];
}


$total_harambee_contribution = getTotalContributionBySubParishFromArray($all_member_details_array, $sub_parish_id);

// Prepare HTML content
$html = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/png" href="/assets/images/logos/favicon.png" />
    <title>Harambee Statement</title>
    <style>
        @font-face {
            font-family: "Barlow";
            src: url("../assets/fonts/Barlow-Regular.ttf") format("truetype");
            font-weight: normal;
            font-style: normal;
        }
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }
        .date{
            color: #4187f8;
        }
        body {
            font-family: "Barlow"; 
            margin: 25px;
            padding: 0;
            color: #333;
            background-color: #fff;
        }
        .container {
            width: 90%;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .header img {
            max-width: 150px;
            margin-bottom: 10px;
        }
        h1 {
            font-size: 14px;
            color: #333;
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px;
        }
        th {
            background-color: #fff;
            color: #000;
            font-size:12px;
        }
        td {
            font-size: 12px;
            border: 1px solid #000;
        }
        .empty-cell {
            background-color: #fce8e6;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            font-size: 12px;
            color: #888;
        }
        
        .table-container{
            width:100%;
        }
        
        .table-separator {
            width: 99.5%;               
            background-color: #000;  
            height: 2px;            
            margin: 0 auto;          
        }
        .text-center{
            text-align:center;
        }
        .text-right{
            text-align:right;
        }
        .text-left{
            text-align:left;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header" style="text-align: center; margin-bottom: 20px;">
            <h1 style="font-size: 12px; color: #3498db; margin: 0;">K.K.K.T ' . $parish_info['diocese_name'] . '</h1>
            <h1 style="font-size: 12px; color: #2c3e50; margin: 5px 0;">' . $parish_info['province_name'] . ' | ' . $parish_info['head_parish_name'] . '</h1>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr style="background-color:#fff;font-weight:bold;text-align:center;">
                        <td colspan="2">
                            TAARIFA YA MAPATO YA HARAMBEE YA ' . strtoupper(htmlspecialchars($harambee_details['description'])) . ' MTAA WA ' . 
                            getSubParishName($sub_parish_id, $conn) . ' KUTOKA <span class="date">' . 
                            date('d/m/Y', strtotime($from_date)) . '</span> MPAKA <span class="date">' . date('d/m/Y', strtotime($to_date)) . '</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align:left;">LENGO:. TZS ' . number_format($harambee_details['amount'], 0) . '</td>
                        <td style="text-align:right;">KIPINDI:. ' . htmlspecialchars(date("d M Y", strtotime($harambee_details['from_date']))) . ' HADI ' . htmlspecialchars(date("d M Y", strtotime($harambee_details['to_date']))) . '</td>
                    </tr>
                </thead>
            </table>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>UWIANO</th>
                        <th>TASLIMU YA AWALI</th>
                        <th>TASLIMU MPYA</th>
                        <th>TASLIMU KUU</th>
                        <th>SALIO KUU</th>
                        <th>IDADI</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="text-align: center; font-weight: bold;">' . number_format($sub_parish_distributed_amount, 2) . '</td>
                        <td style="text-align: center; font-weight: bold;">' . number_format($total_harambee_contribution - $grand_total_on_date, 2) . '</td>
                        <td style="text-align: center; font-weight: bold;color:blue;">' . number_format($grand_total_on_date, 2) . '</td>
                        <td style="text-align: center; font-weight: bold;">' . number_format($total_harambee_contribution, 2) . '</td>
                        <td style="text-align: center; font-weight: bold;">' . number_format($sub_parish_distributed_amount - $total_harambee_contribution, 2) . '</td>
                        <td style="text-align: center; font-weight: bold;">' . $grand_member_count . '</td>
                    </tr>
                </tbody>
            </table>
        </div>';

$html .= '<div class="table-container">
            <div class="table-separator"></div>
        <table border="1" cellpadding="5" cellspacing="0">
            <thead>
                <tr>
                    <th>JUMUIYA</th>
                    <th>IDADI</th>
                    <th>TASLIMU</th>
                    <th>JUMUIYA</th>
                    <th>IDADI</th>
                    <th>TASLIMU</th>
                </tr>
            </thead>
            <tbody>';

$communityData = [];
foreach ($on_date_sub_parishes_data as $sub_parish) {
    foreach ($sub_parish['communities'] as $community) {
        $communityData[] = [
            'name' => htmlspecialchars($community['community_name']),
            'total_contributions' => number_format($community['total_on_date'], 2),
            'contribution_count' => $community['member_count']
        ];
    }
}

// Ensure $communityData is not empty and is an array
if (empty($communityData)) {
    // Handle the case where there's no data, if needed
    echo "No community data available.";
    exit;
}

// Calculate the chunk size, ensuring it is at least 1
$chunkLength = ceil(count($communityData) / 2);
if ($chunkLength < 1) {
    $chunkLength = 1; // Ensure at least 1 element per chunk
}

// Split the data into two chunks
$columns = array_chunk($communityData, $chunkLength);

// Ensure $columns[1] exists, even if there's only one chunk
if (!isset($columns[1])) {
    $columns[1] = []; // If only one chunk is created, make sure $columns[1] is an empty array
}

// Find the maximum row count between the two chunks
$maxRows = max(count($columns[0]), count($columns[1]));

// Initialize the serial number
$serialNumber = 1;



for ($i = 0; $i < $maxRows; $i++) {
    $html .= '<tr>';
    
    // First column community data
    if (isset($columns[0][$i])) {
        // $html .= '<td style="font-weight: bold; text-align: center;">' . $serialNumber++ . '</td>'; // Bold serial number
        $html .= '<td>' . $columns[0][$i]['name'] . '</td>';
        $html .= '<td style="text-align: center;">' . $columns[0][$i]['contribution_count'] . '</td>'; // Center IDADI
        $html .= '<td style="text-align: center;">' . $columns[0][$i]['total_contributions'] . '</td>'; // Center AMOUNT
    } else {
        $html .= '<td></td><td></td><td></td>'; // Empty cells if no data
    }

    // Second column community data
    if (isset($columns[1][$i])) {
        // $html .= '<td style="font-weight: bold; text-align: center;">' . $serialNumber++ . '</td>'; // Bold serial number
        $html .= '<td>' . $columns[1][$i]['name'] . '</td>';
        $html .= '<td style="text-align: center;">' . $columns[1][$i]['contribution_count'] . '</td>'; // Center IDADI
        $html .= '<td style="text-align: center;">' . $columns[1][$i]['total_contributions'] . '</td>'; // Center AMOUNT
    } else {
        $html .= '<td></td><td></td><td></td>'; // Empty cells if no data
    }

    $html .= '</tr>';
}

$totalTarget = 0;
$totalContributedBeforeDate = 0;
$totalContributedOnDate = 0;
$totalContributedUpToDate = 0;
$totalBalance = 0;

usort($on_date_member_details_array, function($a, $b) {
    return strcmp($a['name'], $b['name']);
});

if(!$group_by_communities){
    $html .= '</tbody>
            </table>
        </div>
        <div class="table-container">
        <div class="table-separator"></div>
            <table border="1" cellpadding="5" cellspacing="0">
                <thead> 
                    <tr>
                        <th>#</th>
                        <th>Jina</th>
                        <th>Ahadi</th>
                        <th>Taslimu ya Awali</th>
                        <th>Taslimu Mpya</th>
                        <th>Taslimu Kuu</th>
                        <th>Salio</th>
                        <th>Mafanikio %</th>
                    </tr>
                </thead>
                <tbody>';
            $counter = 1;
            foreach ($on_date_member_details_array as $member) {
                $totalTarget += $member['target'];
                $totalContributedBeforeDate += $member['amount_contributed_before_date'];
                $totalContributedOnDate += $member['amount_contributed_on_date'];
                $totalContributedUpToDate += $member['total_contributed_up_to_date'];
                // Only add to totalBalance if balance is greater than 0
                if ($member['balance'] > 0) {
                    $totalBalance += $member['balance'];
                }
                $html .= '<tr>
                    <td>' . $counter . '</td>
                    <td>' . strtoupper($member['name']) . '</td>
                    <td style="text-align: right;">' . number_format($member['target'], 0) . '</td>
                    <td style="text-align: right;">' . number_format($member['amount_contributed_before_date'], 0) . '</td>
                    <td style="text-align: right;color:blue;">' . number_format($member['amount_contributed_on_date'], 0) . '</td>
                    <td style="text-align: right;">' . number_format($member['total_contributed_up_to_date'], 0) . '</td>
                    <td style="text-align: right; color: ' . ($member['balance'] < 0 ? 'green' : 'inherit') . ';">' . 
                        ($member['balance'] < 0 ? "+" : "") . number_format(abs($member['balance']), 0) . 
                    '</td>
                    <td style="text-align: center;">' . number_format($member['percentage'], 2) . '%</td>
                </tr>';
    
              $counter++;          
            }
    
                // Add grand total row
                $html .= '<tr style="font-weight: bold;">
                    <td colspan="2" style="text-align: center;">Jumla Kuu</td>
                    <td style="text-align: right;">' . number_format($totalTarget, 0) . '</td>
                    <td style="text-align: right;">' . number_format($totalContributedBeforeDate, 0) . '</td>
                    <td style="text-align: right;">' . number_format($totalContributedOnDate, 0) . '</td>
                    <td style="text-align: right;">' . number_format($totalContributedUpToDate, 0) . '</td>
                    <td style="text-align: right; color: ' . ($totalBalance < 0 ? 'green' : 'inherit') . ';">' . 
                        ($totalBalance < 0 ? "+" : "") . number_format(abs($totalBalance), 0) . 
                    '</td>
                    <td style="text-align: center;">-</td> <!-- No percentage for the grand total -->
                </tr>';
    
    $html .= '</tbody>
            </table>
        </div>';
}
else{
    $html .= '</tbody>
        </table>
    </div>
    <div class="table-container">
    <div class="table-separator"></div>
        <table border="1" cellpadding="5" cellspacing="0">
            <thead> 
                <tr>
                    <th>#</th>
                    <th>Jina</th>
                    <th>Ahadi</th>
                    <th>Taslimu ya Awali</th>
                    <th>Taslimu Mpya</th>
                    <th>Taslimu Kuu</th>
                    <th>Salio</th>
                    <th>Mafanikio %</th>
                </tr>
            </thead>
            <tbody>';

$counter = 1;
foreach ($grouped_by_community as $community_id => $members) {
    // Get the community name (replace with your actual method to fetch community name if needed)
    $community_name = strtoupper($members[0]['community_name']); // Assumes all members in a group have the same community name

    // Print community name as the initial row
    $html .= '<tr style="font-weight: bold;">
                <td colspan="8" style="text-align: center;">JUMUIYA YA ' . $community_name . '</td>
              </tr>';

    // Now print the member details for this community
    foreach ($members as $member) {
        $totalTarget += $member['target'];
        $totalContributedBeforeDate += $member['amount_contributed_before_date'];
        $totalContributedOnDate += $member['amount_contributed_on_date'];
        $totalContributedUpToDate += $member['total_contributed_up_to_date'];
        
        // Only add to totalBalance if balance is greater than 0
        if ($member['balance'] > 0) {
            $totalBalance += $member['balance'];
        }

        $html .= '<tr>
                    <td>' . $counter . '</td>
                    <td>' . strtoupper($member['name']) . '</td>
                    <td style="text-align: right;">' . number_format($member['target'], 0) . '</td>
                    <td style="text-align: right;">' . number_format($member['amount_contributed_before_date'], 0) . '</td>
                    <td style="text-align: right;color:blue;">' . number_format($member['amount_contributed_on_date'], 0) . '</td>
                    <td style="text-align: right;">' . number_format($member['total_contributed_up_to_date'], 0) . '</td>
                    <td style="text-align: right; color: ' . ($member['balance'] < 0 ? 'green' : 'inherit') . ';">' . 
                        ($member['balance'] < 0 ? "+" : "") . number_format(abs($member['balance']), 0) . 
                    '</td>
                    <td style="text-align: center;">' . number_format($member['percentage'], 2) . '%</td>
                  </tr>';

        $counter++;          
    }
}

// Add grand total row
$html .= '<tr style="font-weight: bold;">
            <td colspan="2" style="text-align: center;">Jumla Kuu</td>
            <td style="text-align: right;">' . number_format($totalTarget, 0) . '</td>
            <td style="text-align: right;">' . number_format($totalContributedBeforeDate, 0) . '</td>
            <td style="text-align: right;">' . number_format($totalContributedOnDate, 0) . '</td>
            <td style="text-align: right;">' . number_format($totalContributedUpToDate, 0) . '</td>
            <td style="text-align: right; color: ' . ($totalBalance < 0 ? 'green' : 'inherit') . ';">' . 
                ($totalBalance < 0 ? "+" : "") . number_format(abs($totalBalance), 0) . 
            '</td>
            <td style="text-align: center;">-</td> <!-- No percentage for the grand total -->
        </tr>';

$html .= '</tbody>
        </table>
    </div>';

}

$html .= '</div>

<div class="footer">
    <p>Kanisa Langu - SEWMR Technologies</p>
</div>
</body>
</html>';

// Load HTML into Dompdf
$dompdf->loadHtml($html);

// Set paper size and orientation
$dompdf->setPaper('A4', 'potrait');

// Render the HTML as PDF
$dompdf->render();

// Assuming $from_date and $to_date are your date variables
$from_date_formatted = date('d_m_Y', strtotime($from_date));
$to_date_formatted = date('d_m_Y', strtotime($to_date));

// Get the sub-parish name using the provided function
$sub_parish_name = getSubParishName($sub_parish_id, $conn);

// Construct the filename with sub-parish name and date range
$filename = $sub_parish_name . " Harambee Contribution from " . $from_date_formatted . " to " . $to_date_formatted . ".pdf";
$dompdf->stream($filename, array("Attachment" => false));
?>
