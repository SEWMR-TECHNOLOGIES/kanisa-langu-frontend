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
$options->setChroot([$_SERVER['DOCUMENT_ROOT'] . '/assets/fonts/']);
$dompdf->setOptions($options);

$imageData = base64_encode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/assets/images/logos/kkkt-logo.jpg'));
$src = 'data:image/jpeg;base64,' . $imageData;

// Set the timezone to Africa/Nairobi
date_default_timezone_set('Africa/Nairobi');

// Generate the timestamp
$timestamp = date('l, F j, Y g:i A'); 


// Extract variables from GET parameters
$harambee = isset($_GET['harambee_id']) ? $_GET['harambee_id'] : null;
$sub_parish = isset($_GET['sub_parish_id']) ? $_GET['sub_parish_id'] : null;
$community = isset($_GET['community_id']) ? $_GET['community_id'] : null;
$group = isset($_GET['group_id']) ? $_GET['group_id'] : null;
$target = isset($_GET['target']) ? $_GET['target'] : null;

// Define a generic function to validate and decrypt IDs
function validateAndDecryptId($id, $type, $isRequired = false) {
    if (empty($id)) {
        if ($isRequired) {
            header("Location: /error.php?message=$type ID is required.");
            exit;
        }
        return null;
    }

    try {
        // Attempt to decrypt the ID
        $decryptedId = decryptData($id);

        // Validate the decrypted ID
        if (empty($decryptedId) || !preg_match('/^[a-zA-Z0-9]+$/', $decryptedId)) {
            header("Location: /error.php?message=Invalid $type ID.");
            exit;
        }

        return $decryptedId;
    } catch (Exception $e) {
        // Handle decryption failure
        header("Location: /error.php?message=" . urlencode("Failed to decrypt $type ID: " . $e->getMessage()));
        exit;
    }
}

// Validate and decrypt harambee_id
$harambee_id = validateAndDecryptId($harambee, 'harambee', true);

// Validate and decrypt sub_parish_id (required)
$sub_parish_id = validateAndDecryptId($sub_parish, 'sub-parish', true);

// Validate and decrypt community_id (required in all cases)
$community_id = validateAndDecryptId($community, 'community', true);

// Validate and decrypt group_id (required if target is groups)
if ($target === 'groups') {
    $group_id = validateAndDecryptId($group, 'group', true);
}



if(!isset($_SESSION['head_parish_id'])){
     header("Location: /error.php?message=" . urlencode("Unauthorized"));
    exit();
}
$head_parish_id = $_SESSION['head_parish_id'];
$parish_info = getParishInfo($conn, $head_parish_id);

$result = getSubParishAndCommunityNames($conn, $community_id);

if ($result) {
    // Store the results in variables
    $community_name = $result['community_name'] ?? "N/A";
    $sub_parish_name = $result['sub_parish_name'] ?? "N/A";
} else {
    // If no data is found, assign "N/A"
    $community_name = "N/A";
    $sub_parish_name = "N/A";
}




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
$member_ids = getHarambeeMemberIds($conn, $harambee_id, $target, $community_id);

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
    $contribution_result = getContributionsByDate($conn, $member_id, $harambee_id, $target_table);
    
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
        'name' => $full_name,
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

// Iterate through each member
foreach ($member_details_array as $member) {
    $sub_parish_name = $member['sub_parish_name'];
    $member_type = $member['member_type'];

    // If the sub parish doesn't exist in our data, initialize it
    if (!isset($sub_parishes_data[$sub_parish_name])) {
        $sub_parishes_data[$sub_parish_name] = [
            'without_target' => [
                'count' => ['Mwenyeji' => 0, 'Mgeni' => 0],
                'total_contribution' => ['Mwenyeji' => 0, 'Mgeni' => 0]
            ],
            'with_target' => [
                'count' => ['Mwenyeji' => 0, 'Mgeni' => 0],
                'target_amount' => ['Mwenyeji' => 0, 'Mgeni' => 0]
            ],
            'completed' => [
                'count' => ['Mwenyeji' => 0, 'Mgeni' => 0],
                'total_contribution' => ['Mwenyeji' => 0, 'Mgeni' => 0]
            ],
            'on_progress' => [
                'count' => ['Mwenyeji' => 0, 'Mgeni' => 0],
                'total_contribution' => ['Mwenyeji' => 0, 'Mgeni' => 0],
                'total_balance' => ['Mwenyeji' => 0, 'Mgeni' => 0]
            ],
            'not_contributed' => [
                'count' => ['Mwenyeji' => 0, 'Mgeni' => 0],
                'total_target' => ['Mwenyeji' => 0, 'Mgeni' => 0]
            ]
        ];
    }

    // Without Target
    if ($member['target'] == 0) {
        $sub_parishes_data[$sub_parish_name]['without_target']['count'][$member_type]++;
        $sub_parishes_data[$sub_parish_name]['without_target']['total_contribution'][$member_type] += $member['contribution'];
    } 
    // With Target
    else {
        $sub_parishes_data[$sub_parish_name]['with_target']['count'][$member_type]++;
        $sub_parishes_data[$sub_parish_name]['with_target']['target_amount'][$member_type] += $member['target'];

        // Completed (contributed full target)
        if ($member['contribution'] >= $member['target']) {
            $sub_parishes_data[$sub_parish_name]['completed']['count'][$member_type]++;
            $sub_parishes_data[$sub_parish_name]['completed']['total_contribution'][$member_type] += $member['contribution'];
        }
        // On Progress (partially contributed)
        elseif ($member['contribution'] > 0 && $member['contribution'] < $member['target']) {
            $sub_parishes_data[$sub_parish_name]['on_progress']['count'][$member_type]++;
            $sub_parishes_data[$sub_parish_name]['on_progress']['total_contribution'][$member_type] += $member['contribution'];
            $sub_parishes_data[$sub_parish_name]['on_progress']['total_balance'][$member_type] += $member['balance'];
        }
        // Not Contributed (has a target but no contribution)
        elseif ($member['contribution'] == 0) {
            $sub_parishes_data[$sub_parish_name]['not_contributed']['count'][$member_type]++;
            $sub_parishes_data[$sub_parish_name]['not_contributed']['total_target'][$member_type] += $member['target'];
        }
    }
}

// Transform the $sub_parishes_data into $harambee_data structure
foreach ($sub_parishes_data as $sub_parish_name => $sub_parish_data) {
    $harambee_data[] = array_merge(['sub_parish_name' => $sub_parish_name], $sub_parish_data);
}

// Initialize grand total variables
$grand_total_with_target_mwenyeji_count = 0;
$grand_total_with_target_mwenyeji_amount = 0;
$grand_total_with_target_mgeni_count = 0;
$grand_total_with_target_mgeni_amount = 0;
$grand_total_with_target_total_amount = 0;
$grand_total_completed_mwenyeji_count = 0;
$grand_total_completed_mwenyeji_amount = 0;
$grand_total_on_progress_mwenyeji_count = 0;
$grand_total_on_progress_mwenyeji_contribution = 0;
$grand_total_on_progress_mwenyeji_balance = 0;
$grand_total_not_contributed_mwenyeji_count = 0;
$grand_total_not_contributed_mwenyeji_amount = 0;
$grand_total_without_target_mwenyeji_count = 0;
$grand_total_without_target_mwenyeji_contribution = 0;
$grand_total_total_contribution = 0;
$grand_total_total_balance = 0;

// Initialize grand total variables
$grand_total_completed_count_mwenyeji = 0;
$grand_total_completed_contribution_mwenyeji = 0;
$grand_total_completed_count_mgeni = 0;
$grand_total_completed_contribution_mgeni = 0;

$grand_total_on_progress_count_mwenyeji = 0;
$grand_total_on_progress_contribution_mwenyeji = 0;
$grand_total_on_progress_count_mgeni = 0;
$grand_total_on_progress_contribution_mgeni = 0;

$grand_total_not_contributed_count_mwenyeji = 0;
$grand_total_not_contributed_target_mwenyeji = 0;
$grand_total_not_contributed_count_mgeni = 0;
$grand_total_not_contributed_target_mgeni = 0;

$grand_total_completed_mgeni_count = 0;
$grand_total_completed_mgeni_amount = 0;
$grand_total_on_progress_mgeni_count = 0;
$grand_total_on_progress_mgeni_contribution = 0;
$grand_total_on_progress_mgeni_balance = 0;
$grand_total_not_contributed_mgeni_count = 0;
$grand_total_not_contributed_mgeni_amount = 0;
$grand_total_without_target_mgeni_count = 0;
$grand_total_without_target_mgeni_contribution = 0;

// Prepare HTML content
$html = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/png" href="/assets/images/logos/favicon.png" />
    <title>Harambee Community Report</title>
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
            background-color: #fff;
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
        .footer {
            position: fixed;
            bottom: 0px;
            left: 0;
            right: 0;
            height: 30px;
            text-align: center;
            font-size: 12px;
            color: #888;
        }
        .footer .page-number:before {
            content: "Page " counter(page);
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
                        <td colspan="2">TAARIFA YA HARAMBEE YA ' . strtoupper(htmlspecialchars($harambee_details['description'])) . ' MTAA WA '. $sub_parish_name.' JUMUIYA YA '.$community_name.' | '.$timestamp.'</td>
                    </tr>
                </thead>
            </table>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th colspan="2">A</th>
                        <th colspan="16">AHADI ZA WAKRISTO NA UTOAJI WA HARAMBEE</th>
                    </tr>
                    <tr style="text-align:center;font-weight:bold">
                        <td rowspan="2">#</td>
                        <td rowspan="2">JUMUIYA</td>
                        <td colspan="2">WENYEJI</td>
                        <td colspan="2">WAGENI</td>
                        <td rowspan="2">JUMLA KUU</td>
                        <td colspan="2">WALIOMALIZA</td>
                        <td colspan="3">WANAOENDELEA</td>
                        <td colspan="2">BADO HAWAJATOA</td>
                        <td colspan="2">HAWAKUAHIDI</td>
                        <td colspan="2">JUMLA KUU</td>
                    </tr>
                    <tr style="text-align:center;font-weight:bold">
                        <td>Idadi</td>
                        <td>Ahadi</td>
                        <td>Idadi</td>
                        <td>Ahadi</td>
                        <td>Idadi</td>
                        <td>Taslimu</td>
                        <td>Idadi</td>
                        <td>Taslimu</td>
                        <td>Salio</td>
                        <td>Idadi</td>
                        <td>Kiasi</td>
                        <td>Idadi</td>
                        <td>Taslimu</td>
                        <td>Taslimu</td>
                        <td>Salio</td>
                    </tr>
                </thead>
                <tbody>';

// Iterate through the harambee_data array and display only sub-parish names
$row_number = 1;
foreach ($harambee_data as $sub_parish_data) {
    $html .= '
        <tr>
            <td class="text-center">' . $row_number++ . '</td>
            <td class="text-left">' . htmlspecialchars($community_name) . '</td>
            <td class="text-center">' . $sub_parish_data['with_target']['count']['Mwenyeji'] . '</td>
            <td class="text-center">' . number_format($sub_parish_data['with_target']['target_amount']['Mwenyeji'], 0) . '</td>
            <td class="text-center">' . $sub_parish_data['with_target']['count']['Mgeni'] . '</td>
            <td class="text-center">' . number_format($sub_parish_data['with_target']['target_amount']['Mgeni'], 0) . '</td>
            <td class="text-center">' . number_format($sub_parish_data['with_target']['target_amount']['Mwenyeji'] + $sub_parish_data['with_target']['target_amount']['Mgeni'], 0) . '</td>
            <td class="text-center">' . ($sub_parish_data['completed']['count']['Mwenyeji'] + $sub_parish_data['completed']['count']['Mgeni']) . '</td>
                <td class="text-center">' . number_format(
                    $sub_parish_data['completed']['total_contribution']['Mwenyeji'] 
                    + $sub_parish_data['completed']['total_contribution']['Mgeni'], 
                    0
                ) . '</td>
                <td class="text-center">' . ($sub_parish_data['on_progress']['count']['Mwenyeji'] + $sub_parish_data['on_progress']['count']['Mgeni']) . '</td>
                <td class="text-center">' . number_format(
                    $sub_parish_data['on_progress']['total_contribution']['Mwenyeji'] 
                    + $sub_parish_data['on_progress']['total_contribution']['Mgeni'], 
                    0
                ) . '</td>
                <td class="text-center">' . number_format(
                    $sub_parish_data['on_progress']['total_balance']['Mwenyeji'] 
                    + $sub_parish_data['on_progress']['total_balance']['Mgeni'], 
                    0
                ) . '</td>
                <td class="text-center">' . ($sub_parish_data['not_contributed']['count']['Mwenyeji'] + $sub_parish_data['not_contributed']['count']['Mgeni']) . '</td>
                <td class="text-center">' . number_format(
                    $sub_parish_data['not_contributed']['total_target']['Mwenyeji'] 
                    + $sub_parish_data['not_contributed']['total_target']['Mgeni'], 
                    0
                ) . '</td>
            <td class="text-center">' . ($sub_parish_data['without_target']['count']['Mwenyeji'] + $sub_parish_data['without_target']['count']['Mgeni']) . '</td>
            <td class="text-center">' . number_format(
                $sub_parish_data['without_target']['total_contribution']['Mwenyeji'] 
                + $sub_parish_data['without_target']['total_contribution']['Mgeni'], 
                0
            ) . '</td>
            <td class="text-center">' . number_format(
                $sub_parish_data['completed']['total_contribution']['Mwenyeji'] 
                + $sub_parish_data['on_progress']['total_contribution']['Mwenyeji'] 
                + $sub_parish_data['without_target']['total_contribution']['Mwenyeji'] 
                + $sub_parish_data['completed']['total_contribution']['Mgeni'] 
                + $sub_parish_data['on_progress']['total_contribution']['Mgeni'] 
                + $sub_parish_data['without_target']['total_contribution']['Mgeni'], 
                0
            ) . '</td>
            <td class="text-center">' . number_format(
                $sub_parish_data['on_progress']['total_balance']['Mwenyeji'] 
                + $sub_parish_data['not_contributed']['total_target']['Mwenyeji'] 
                + $sub_parish_data['on_progress']['total_balance']['Mgeni'] 
                + $sub_parish_data['not_contributed']['total_target']['Mgeni'], 
                0
            ) . '</td>
        </tr>';


// Increment grand total values for Mgeni similar to Mgeni
$grand_total_with_target_mwenyeji_count += $sub_parish_data['with_target']['count']['Mwenyeji'];
$grand_total_with_target_mwenyeji_amount += $sub_parish_data['with_target']['target_amount']['Mwenyeji'];
$grand_total_with_target_mgeni_count += $sub_parish_data['with_target']['count']['Mgeni'];
$grand_total_with_target_mgeni_amount += $sub_parish_data['with_target']['target_amount']['Mgeni'];

$grand_total_completed_mwenyeji_count += $sub_parish_data['completed']['count']['Mwenyeji'];
$grand_total_completed_mwenyeji_amount += $sub_parish_data['completed']['total_contribution']['Mwenyeji'];
$grand_total_completed_mgeni_count += $sub_parish_data['completed']['count']['Mgeni'];
$grand_total_completed_mgeni_amount += $sub_parish_data['completed']['total_contribution']['Mgeni'];

$grand_total_on_progress_mwenyeji_count += $sub_parish_data['on_progress']['count']['Mwenyeji'];
$grand_total_on_progress_mwenyeji_contribution += $sub_parish_data['on_progress']['total_contribution']['Mwenyeji'];
$grand_total_on_progress_mwenyeji_balance += $sub_parish_data['on_progress']['total_balance']['Mwenyeji'];
$grand_total_on_progress_mgeni_count += $sub_parish_data['on_progress']['count']['Mgeni'];
$grand_total_on_progress_mgeni_contribution += $sub_parish_data['on_progress']['total_contribution']['Mgeni'];
$grand_total_on_progress_mgeni_balance += $sub_parish_data['on_progress']['total_balance']['Mgeni'];

$grand_total_not_contributed_mwenyeji_count += $sub_parish_data['not_contributed']['count']['Mwenyeji'];
$grand_total_not_contributed_mwenyeji_amount += $sub_parish_data['not_contributed']['total_target']['Mwenyeji'];
$grand_total_not_contributed_mgeni_count += $sub_parish_data['not_contributed']['count']['Mgeni'];
$grand_total_not_contributed_mgeni_amount += $sub_parish_data['not_contributed']['total_target']['Mgeni'];

$grand_total_without_target_mwenyeji_count += $sub_parish_data['without_target']['count']['Mwenyeji'];
$grand_total_without_target_mwenyeji_contribution += $sub_parish_data['without_target']['total_contribution']['Mwenyeji'];
$grand_total_without_target_mgeni_count += $sub_parish_data['without_target']['count']['Mgeni'];
$grand_total_without_target_mgeni_contribution += $sub_parish_data['without_target']['total_contribution']['Mgeni'];

// Increment total contribution and balance for Mgeni similar to Mgeni
$grand_total_total_contribution += $sub_parish_data['completed']['total_contribution']['Mwenyeji'] 
    + $sub_parish_data['on_progress']['total_contribution']['Mwenyeji'] 
    + $sub_parish_data['without_target']['total_contribution']['Mwenyeji'] 
    + $sub_parish_data['completed']['total_contribution']['Mgeni'] 
    + $sub_parish_data['on_progress']['total_contribution']['Mgeni'] 
    + $sub_parish_data['without_target']['total_contribution']['Mgeni'];

$grand_total_total_balance += $sub_parish_data['on_progress']['total_balance']['Mwenyeji'] 
    + $sub_parish_data['not_contributed']['total_target']['Mwenyeji'] 
    + $sub_parish_data['on_progress']['total_balance']['Mgeni'] 
    + $sub_parish_data['not_contributed']['total_target']['Mgeni'];

}
// Grand total row
$html .= '<tr style="text-align:center;font-weight:bold">
            <td colspan="2">JUMLA KUU</td>
            <td>' . $grand_total_with_target_mwenyeji_count . '</td>
            <td>' . number_format($grand_total_with_target_mwenyeji_amount, 0) . '</td>
            <td>' . $grand_total_with_target_mgeni_count . '</td>
            <td>' . number_format($grand_total_with_target_mgeni_amount, 0) . '</td>
            <td>' . number_format($grand_total_with_target_mwenyeji_amount + $grand_total_with_target_mgeni_amount, 0) . '</td>
            <td>' . ($grand_total_completed_mwenyeji_count + $grand_total_completed_mgeni_count) . '</td>
            <td>' . number_format($grand_total_completed_mwenyeji_amount + $grand_total_completed_mgeni_amount, 0) . '</td>
            <td>' . ($grand_total_on_progress_mwenyeji_count + $grand_total_on_progress_mgeni_count) . '</td>
            <td>' . number_format($grand_total_on_progress_mwenyeji_contribution + $grand_total_on_progress_mgeni_contribution, 0) . '</td>
            <td>' . number_format($grand_total_on_progress_mwenyeji_balance + $grand_total_on_progress_mgeni_balance, 0) . '</td>
            <td>' . ($grand_total_not_contributed_mwenyeji_count + $grand_total_not_contributed_mgeni_count) . '</td>
            <td>' . number_format($grand_total_not_contributed_mwenyeji_amount + $grand_total_not_contributed_mgeni_amount, 0) . '</td>
            <td>' . ($grand_total_without_target_mwenyeji_count + $grand_total_without_target_mgeni_count) . '</td>
            <td>' . number_format($grand_total_without_target_mwenyeji_contribution + $grand_total_without_target_mgeni_contribution, 0) . '</td>
            <td>' . number_format($grand_total_total_contribution, 0) . '</td>
            <td>' . number_format($grand_total_total_balance, 0) . '</td>
        </tr>
        '
        ;

// If no sub-parishes exist, show a message
if (empty($harambee_data)) {
    $html .= '
        <tr>
            <td colspan="2" class="empty-cell">Hakuna taarifa ya Harambee/td>
        </tr>';
}

$html .= '
                </tbody>
            </table>
        </div>';

$html .= '<!-- Second Table: Contributions -->
    <div class="table-container">
        <div class="table-separator"></div>
        <table>
            <thead>
                <tr>
                    <th colspan="2">B</th>
                    <th colspan="16">MCHANGANUO WA UTOAJI WA AHADI KWA WENYEJI NA WAGENI</th>
                </tr>
                <tr style="text-align:center;font-weight:bold">
                    <td rowspan="3">#</td>
                    <td rowspan="3">JUMUIYA</td>
                    <td colspan="5">WALIOMALIZA</td>
                    <td colspan="7">WANAOENDELEA</td>
                    <td colspan="4">BADO HAWAJATOA</td>
                </tr>
                <tr style="text-align:center;font-weight:bold">
                    <td colspan="3">WENYEJI</td>
                    <td colspan="2">WAGENI</td>
                    <td colspan="4">WENYEJI</td>
                    <td colspan="3">WAGENI</td>
                    <td colspan="2">WENYEJI</td>
                    <td colspan="2">WAGENI</td>
                </tr>
                <tr style="text-align:center;font-weight:bold">
                    <td>Idadi</td>
                    <td colspan="2">Taslimu</td>
                    <td>Idadi</td>
                    <td>Taslimu</td>
                    <td>Idadi</td>
                    <td colspan="3">Taslimu</td>
                    <td>Idadi</td>
                    <td colspan="2">Taslimu</td>
                    <td>Idadi</td>
                    <td>Kiasi</td>
                    <td>Idadi</td>
                    <td>Kiasi</td>
                </tr>
            </thead>
            <tbody>';

            // Check if there is data to display for contributions
            if (!empty($harambee_data)) {
                // Iterate through the harambee_data array and display only sub-parish names
                $row_number = 1;
                foreach ($harambee_data as $sub_parish_data) {
                 // Update grand totals for Mwenyeji
                $grand_total_completed_count_mwenyeji += $sub_parish_data['completed']['count']['Mwenyeji'];
                $grand_total_completed_contribution_mwenyeji += $sub_parish_data['completed']['total_contribution']['Mwenyeji'];
                $grand_total_on_progress_count_mwenyeji += $sub_parish_data['on_progress']['count']['Mwenyeji'];
                $grand_total_on_progress_contribution_mwenyeji += $sub_parish_data['on_progress']['total_contribution']['Mwenyeji'];
                $grand_total_not_contributed_count_mwenyeji += $sub_parish_data['not_contributed']['count']['Mwenyeji'];
                $grand_total_not_contributed_target_mwenyeji += $sub_parish_data['not_contributed']['total_target']['Mwenyeji'];
                
                // Update grand totals for Mgeni
                $grand_total_completed_count_mgeni += $sub_parish_data['completed']['count']['Mgeni'];
                $grand_total_completed_contribution_mgeni += $sub_parish_data['completed']['total_contribution']['Mgeni'];
                $grand_total_on_progress_count_mgeni += $sub_parish_data['on_progress']['count']['Mgeni'];
                $grand_total_on_progress_contribution_mgeni += $sub_parish_data['on_progress']['total_contribution']['Mgeni'];
                $grand_total_not_contributed_count_mgeni += $sub_parish_data['not_contributed']['count']['Mgeni'];
                $grand_total_not_contributed_target_mgeni += $sub_parish_data['not_contributed']['total_target']['Mgeni'];
                    $html .= '
                    <tr>
                        <td class="text-center">' . $row_number++ . '</td>
                        <td class="text-left">' . htmlspecialchars($community_name) . '</td>
                        <td class="text-center">' . $sub_parish_data['completed']['count']['Mwenyeji'] . '</td>
                        <td class="text-center" colspan="2">' . number_format($sub_parish_data['completed']['total_contribution']['Mwenyeji'], 0) . '</td>
                        <td class="text-center">' . $sub_parish_data['completed']['count']['Mgeni'] . '</td>
                        <td class="text-center">' . number_format($sub_parish_data['completed']['total_contribution']['Mgeni'], 0) . '</td>
                        <td class="text-center">' . $sub_parish_data['on_progress']['count']['Mwenyeji'] . '</td>
                        <td class="text-center" colspan="3">' . number_format($sub_parish_data['on_progress']['total_contribution']['Mwenyeji'], 0) . '</td>
                        <td class="text-center">' . $sub_parish_data['on_progress']['count']['Mgeni'] . '</td>
                        <td class="text-center" colspan="2">' . number_format($sub_parish_data['on_progress']['total_contribution']['Mgeni'], 0) . '</td>
                        <td class="text-center">' . $sub_parish_data['not_contributed']['count']['Mwenyeji'] . '</td>
                        <td class="text-center">' . number_format($sub_parish_data['not_contributed']['total_target']['Mwenyeji'], 0) . '</td>
                        <td class="text-center">' . $sub_parish_data['not_contributed']['count']['Mgeni'] . '</td>
                        <td class="text-center">' . number_format($sub_parish_data['not_contributed']['total_target']['Mgeni'], 0) . '</td>
                    </tr>';
                }

                // Output grand totals row
                $html .= '
                <tr style="text-align:center;font-weight:bold">
                    <td colspan="2">JUMLA KUU</td>
                    <td>' . $grand_total_completed_count_mwenyeji . '</td>
                    <td colspan="2">' . number_format($grand_total_completed_contribution_mwenyeji, 0) . '</td>
                    <td>' . $grand_total_completed_count_mgeni . '</td>
                    <td>' . number_format($grand_total_completed_contribution_mgeni, 0) . '</td>
                    <td>' . $grand_total_on_progress_count_mwenyeji . '</td>
                    <td colspan="3">' . number_format($grand_total_on_progress_contribution_mwenyeji, 0) . '</td>
                    <td>' . $grand_total_on_progress_count_mgeni . '</td>
                    <td colspan="2">' . number_format($grand_total_on_progress_contribution_mgeni, 0) . '</td>
                    <td>' . $grand_total_not_contributed_count_mwenyeji . '</td>
                    <td>' . number_format($grand_total_not_contributed_target_mwenyeji, 0) . '</td>
                    <td>' . $grand_total_not_contributed_count_mgeni . '</td>
                    <td>' . number_format($grand_total_not_contributed_target_mgeni, 0) . '</td>
                </tr>';
            } else {
                // If no data, show "No Harambee Data" message
                $html .= '
                <tr>
                    <td colspan="18" style="text-align:center;">NO HARAMBEE DATA</td>
                </tr>';
            }

            $html .= '</tbody>
                    </table>
                </div>';

// Initialize an array to store member details and a set to track processed group names
$all_member_details_array = [];
$all_members_processed_groups = [];

foreach ($member_ids as $member_id) {
    // Get the member details
    $member = getSingleMemberHarambeeDetails($conn, $member_id, $harambee_id, $target);

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
    
    // Categorize member based on contribution and target
    if ($target_amount > 0 && $total_contribution == 0) {
        $contribution_category = 'not_contributed';  // Not Contributed if target > 0 but no contribution
    } elseif ($target_amount >= 0 && $total_contribution >= $target_amount && $total_contribution > 0) {
        $contribution_category = 'completed';  // Completed if contribution is equal or greater than target
    } elseif ($target_amount > 0 && $total_contribution < $target_amount) {
        $contribution_category = 'on_progress';  // On Progress if contribution is less than target
    }


    // Store details in the array with the category
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
        'is_in_groups' => $member['is_in_groups'],
        'target' => $target_amount,
        'contribution' => $total_contribution, 
        'balance' => $balance,
        'percentage' => $percentage,
        'category' => $contribution_category  
    ];
}

// Define the priority order for contribution categories
$contribution_order = [
    'completed' => 1,
    'on_progress' => 2,
    'not_contributed' => 3
];

// Sort the members based on the contribution category and then by name
usort($all_member_details_array, function ($a, $b) use ($contribution_order) {
    // Compare based on contribution category first (by priority)
    $category_comparison = $contribution_order[$a['category']] - $contribution_order[$b['category']];
    
    // If categories are the same, then compare by first name
    if ($category_comparison === 0) {
        return strcmp($a['first_name'], $b['first_name']);
    }
    
    return $category_comparison;
});


$total_harambee_contribution = getTotalContributionBySubParishFromArray($all_member_details_array, $sub_parish_id);

$totalTarget = 0;
$totalContributed = 0;
$totalBalance = 0;
$html .= '
    <div class="table-container">
    <div class="table-separator"></div>
        <table>
            <thead>
                <tr>
                    <th colspan="1">C</th>
                    <th colspan="7">MCHANGANUO WA UTOAJI</th>
                </tr>
                <tr>
                    <th>#</th>
                    <th>Jina</th>
                    <th>Simu</th>
                    <th>Kundi</th>
                    <th>Ahadi</th>
                    <th>Taslimu</th>
                    <th>Salio</th>
                    <th>Mafanikio %</th>
                </tr>
            </thead>
            <tbody>';
        $counter = 1;
        foreach ($all_member_details_array as $member) {
            $totalTarget += $member['target'];
            $totalContributed += $member['contribution'];
            // Only add to totalBalance if balance is greater than 0
            if ($member['balance'] > 0) {
                $totalBalance += $member['balance'];
            }
            
            // Modify row style based on contribution category
            switch ($member['category']) {
                case 'not_contributed':
                    // Red for not contributed
                    $row_style = 'color:red;';
                    break;
                case 'completed':
                    // Green for completed (you can customize the color)
                    $row_style = 'color:blue;';
                    break;
                case 'on_progress':
                    // Yellow for on progress (you can customize the color)
                    $row_style = 'color:black;';
                    break;
            }
            
            $html .= '<tr style="'.$row_style.'">
                <td>' . $counter . '</td>
                <td>' . strtoupper($member['name']) . '</td>
                <td>' . strtoupper(preg_replace('/^255/', '0', $member['phone'])) . '</td>
                <td>' . strtoupper($member['member_type']) . '</td>
                <td style="text-align: right;">' . number_format($member['target'], 0) . '</td>
                <td style="text-align: right;">' . number_format($member['contribution'], 0) . '</td>
                <td style="text-align: right; color: ' . ($member['balance'] < 0 ? 'green' : 'inherit') . ';">' . 
                    ($member['balance'] < 0 ? "+" : "") . number_format(abs($member['balance']), 0) . 
                '</td>
                <td style="text-align: center;">' . number_format($member['percentage'], 2) . '%</td>
            </tr>';

          $counter++;          
        }

            // Add grand total row
            $html .= '<tr style="font-weight: bold;">
                <td colspan="4" style="text-align: center;">Jumla Kuu</td>
                <td style="text-align: right;">' . number_format($totalTarget, 0) . '</td>
                <td style="text-align: right;">' . number_format($totalContributed, 0) . '</td>
                <td style="text-align: right; color: ' . ($totalBalance < 0 ? 'green' : 'inherit') . ';">' . 
                    ($totalBalance < 0 ? "+" : "") . number_format(abs($totalBalance), 0) . 
                '</td>
                <td style="text-align: center;">-</td> <!-- No percentage for the grand total -->
            </tr>';

$html .= '</tbody>
        </table>
    </div>
</div>

        <div class="footer">
            <p><span class="page-number"></span> | Printed on '.$timestamp.' | Kanisa Langu - SEWMR Technologies</p>
        </div>
    </div>
</body>
</html>';


// Load HTML into Dompdf
$dompdf->loadHtml($html);

// Set paper size and orientation
$dompdf->setPaper('A4', 'landscape');

// Render the HTML as PDF
$dompdf->render();

$canvas = $dompdf->get_canvas();

// Add page numbers to each page dynamically
// $canvas->pageText(270, 800, "Page {PAGE_NUM} of {PAGE_COUNT}", null, 12, array(0, 0, 0));

// Stream the PDF with a dynamic filename
$filename = $community_name." harambee_report.pdf";
$dompdf->stream($filename, array("Attachment" => false));
?>
