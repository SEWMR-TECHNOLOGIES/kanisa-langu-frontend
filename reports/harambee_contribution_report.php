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
$category = isset($_GET['category']) ? $_GET['category'] : 'completed';
$sub_parish_id = isset($_GET['sub_parish_id']) ? $_GET['sub_parish_id'] : null;
$exclude_members_in_groups = isset($_GET['exclude_members_in_groups']) ? $_GET['exclude_members_in_groups'] : false;
$report_for = isset($_GET['report_for']) ? $_GET['report_for'] : null;
$community_id = isset($_GET['community_id']) ? $_GET['community_id'] : null;

// Decrypt community_id if report is for community
if ($report_for === 'community' && $community_id) {
    try {
        $community_id = decryptData($community_id);
        if (empty($community_id) || !preg_match('/^[a-zA-Z0-9]+$/', $community_id)) {
            echo json_encode(["success" => false, "message" => "Invalid community ID."]);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Error decrypting community ID: " . $e->getMessage()]);
        exit;
    }
}

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


$sw_category_name = '';

switch($category){
    case 'completed':
        $sw_category_name = 'WALIO MALIZA';
        break;
    case 'on_progress':
        $sw_category_name = 'WANAOENDELEA';
        break;
    case 'not_contributed':
        $sw_category_name = 'AMBAO HAWAJATOA';
        break;
    default:
        $sw_category_name = "INVALID CATEGORY";
}

// Validate target
$valid_targets = ['head-parish', 'sub-parish', 'community', 'groups'];
if (!in_array($target, $valid_targets)) {
    echo json_encode(["success" => false, "message" => "Invalid target type provided."]);
    exit;
}

// Validate category
if (empty($category) || !in_array($category, ['completed', 'on_progress', 'not_contributed'])) {
    echo json_encode(["success" => false, "message" => "Invalid category selected."]);
    exit;
}


// Validate harambee_id
if (empty($sub_parish_id) || !preg_match('/^[a-zA-Z0-9]+$/', $harambee_id)) {
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
    
    // Filter by community_id if report_for=community
    if ($report_for === 'community' && $community_id && $member['community_id'] != $community_id) {
        continue;
    }
    
    if($exclude_members_in_groups){
        if ($member['is_in_groups']){
            continue;
        }
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

// Sort the members alphabetically by their first name
usort($all_member_details_array, function ($a, $b) {
    return strcmp($a['first_name'], $b['first_name']);
});


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
            font-size:14px;
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
                            getSubParishName($sub_parish_id, $conn) . 
                            ($report_for === 'community' && $community_id ? ' JUMUIYA YA ' . getCommunityName($community_id, $conn) : '') . 
                            ' KWA <span class="date">' . $sw_category_name . '</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align:left;">LENGO:. TZS ' . number_format($harambee_details['amount'], 0) . '</td>
                        <td style="text-align:right;">KIPINDI:. ' . htmlspecialchars(date("d M Y", strtotime($harambee_details['from_date']))) . ' HADI ' . htmlspecialchars(date("d M Y", strtotime($harambee_details['to_date']))) . '</td>
                    </tr>
                </thead>
            </table>
        </div>';

    
    
    
$totalTarget = 0;
$totalContributed = 0;
$totalBalance = 0;
$html .= '
    <div class="table-container">
    <div class="table-separator"></div>
        <table border="1" cellpadding="5" cellspacing="0">
            <thead> 
                <tr>
                    <th>#</th>
                    <th>Jina</th>
                    <th>Simu</th>
                    <th>Ahadi</th>
                    <th>Taslimu</th>
                    <th>Salio</th>
                    <th>Mafanikio %</th>
                </tr>
            </thead>
            <tbody>';
        $counter = 1;
        foreach ($all_member_details_array as $member) {
            // Check if the member's category matches the selected category
            if ($member['category'] !== $category) {
                continue; // Skip members whose category doesn't match
            }
            $totalTarget += $member['target'];
            $totalContributed += $member['contribution'];
            // Only add to totalBalance if balance is greater than 0
            $totalBalance += $member['balance'];

            
            $row_style = ($member['is_in_groups']) ? 'color:blue;' : 'color:black';
            
            $html .= '<tr style="'.$row_style.'">
                <td>' . $counter . '</td>
                <td>' . strtoupper($member['name']) . '</td>
                <td>' . (substr($member['phone'], 0, 3) === '255' ? '0' . substr($member['phone'], 3) : strtoupper($member['phone'])) . '</td>
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
                <td colspan="3" style="text-align: center;">Jumla Kuu</td>
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
// Get the sub-parish name
$sub_parish_name = getSubParishName($sub_parish_id, $conn);

// Conditionally get community name if report is for community
$community_name = ($report_for === 'community' && $community_id) ? ' - ' . getCommunityName($community_id, $conn) : '';

// Construct the filename
$filename = $sub_parish_name . $community_name . " Harambee Contribution Report " . $sw_category_name . ".pdf";

// Stream the PDF
$dompdf->stream($filename, array("Attachment" => false));

?>
