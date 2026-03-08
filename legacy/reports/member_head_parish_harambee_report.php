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

// Initialize variables from GET parameters
$harambee = isset($_GET['harambee']) ? $_GET['harambee'] : null;
$target = isset($_GET['target']) ? $_GET['target'] : null;

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
    echo "Harambee ID is required.";
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
$member_ids = getHarambeeMemberIds($conn, $harambee_id, $target);

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
        'name' => htmlspecialchars($full_name),
        'target' => $target_amount,
        'contribution' => $total_contribution,
        'balance' => $balance,
        'percentage' => $percentage
    ];
}


// Prepare HTML content
$html = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
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
        body {
            font-family: "Barlow"; 
            margin: 0;
            padding: 0;
            color: #333;
            background-color: #f4f4f4;
        }
        .container {
            width: 85%;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
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
        .table-container {
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #fff;
            padding: 4px;
        }
        th {
            background-color: #BA5536;
            color: #fff;
        }
        td {
            background-color: #f9f9f9;
            font-size: 12px;
            border: 1px solid #fff;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header" style="text-align: center; margin-bottom: 20px;">
            <img src="' . $src . '" alt="Church Logo" height="50" style="margin-bottom: 10px;">
            <h1 style="font-size: 12px; color: #3498db; margin: 0;">K.K.K.T ' . $parish_info['diocese_name'] . '</h1>
            <h1 style="font-size: 12px; color: #2c3e50; margin: 5px 0;">' . $parish_info['province_name'] . ' | ' . $parish_info['head_parish_name'] . '</h1>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr style="background-color:#eee;font-weight:bold;text-align:center;">
                        <td colspan="2">TAARIFA YA HARAMBEE YA ' . strtoupper(htmlspecialchars($harambee_details['description'])) . '</td>
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
                        <th>#</th>
                        <th>JINA</th>
                        <th>AHADI</th>
                        <th>TASLIMU</th>
                        <th>SALIO / ZIDIO</th>
                        <th>MAFANIKIO (%)</th>
                    </tr>
                </thead>
                <tbody>';
                
                $total_contributions = 0;
                $total_target = 0;
                $total_balance = 0;
                $row_number = 1; // Row counter for numbering
        
                foreach ($member_details_array as $detail) {
                    $formatted_target = 'TZS ' . number_format($detail['target'], 0);
                    $formatted_contribution = 'TZS ' . number_format($detail['contribution'], 0);
                    
                    // Style balance and ensure negative values are treated as 0
                    $balance_value = ($detail['balance'] < 0) ? 0 : $detail['balance'];
                    $formatted_balance = 'TZS ' . number_format($balance_value, 0);
                    $balance_style = ($detail['balance'] < 0) ? "color:green;text-align:right;" : "color:black;text-align:right;";
                    
                    // Ensure percentage exists and calculate percentage style based on its value
                    $percentage = isset($detail['percentage']) ? $detail['percentage'] : 0.00;
                    $percentage_style = ($percentage >= 100.00) ? "color:green;text-align:center;" : "color:black;text-align:center;";
                    
                    // Add to totals
                    $total_target += $detail['target'];
                    $total_contributions += $detail['contribution'];
                    $total_balance += $balance_value;
                    
                    // Add rows to the table
                    $html .= '
                    <tr>
                        <td>' . $row_number++ . '</td>
                        <td>' . ucwords(strtolower($detail['name'])) . '</td>
                        <td>' . $formatted_target . '</td>
                        <td>' . $formatted_contribution . '</td>
                        <td style="' . $balance_style . '">' . $formatted_balance . '</td>
                        <td style="' . $percentage_style . '">' . number_format($percentage, 2) . '</td>
                    </tr>';
                }
        
                // If no members exist, add a row indicating the absence of contributions
                if (empty($member_details_array)) {
                    $html .= '
                    <tr>
                        <td colspan="6" class="empty-cell">No member contributions found</td>
                    </tr>';
                }
                $total_percentage = calculatePercentage($total_contributions, $total_target);
                $percentage_style = ($total_percentage >= 100.00) ? "color:green;text-align:center;" : "color:black;text-align:center;";
                // Add the grand total row
                $html .= '
                <tr>
                    <td colspan="2" style="font-weight:bold;">JUMLA KUU</td>
                    <td>' . 'TZS ' . number_format($total_target, 0) . '</td>
                    <td>' . 'TZS ' . number_format($total_contributions, 0) . '</td>
                    <td style="text-align:right;">' . 'TZS ' . number_format($total_balance, 0) . '</td>
                    <td style="' . $percentage_style . '">' . number_format($total_percentage, 2) . '</td>
                </tr>';
        
                $html .= '
                </tbody>
            </table>
        </div>

        <div class="footer">
            <p>Kanisa Langu - SEWMR Technologies</p>
        </div>
    </div>
</body>
</html>';

// Load HTML into Dompdf
$dompdf->loadHtml($html);

// Set paper size and orientation
$dompdf->setPaper('A4', 'portrait');

// Render the HTML as PDF
$dompdf->render();

// Stream the PDF with a dynamic filename
$filename = $parish_info['head_parish_name']." harambee_report.pdf";
$dompdf->stream($filename, array("Attachment" => false));
?>
