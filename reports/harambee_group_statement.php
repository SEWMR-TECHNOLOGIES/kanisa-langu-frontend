<?php
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/libraries/vendor/autoload.php');
// header('Content-Type: application/json');

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
$harambee_group_id = isset($_GET['harambee_group_id']) ? $_GET['harambee_group_id'] : null;
$target = isset($_GET['target']) ? $_GET['target'] : null;
$timestamp = isset($_GET['timestamp']) ? $_GET['timestamp'] : null;

if (!$harambee_group_id || !$target || !$timestamp) {
    echo json_encode(["success" => false, "message" => "Missing required parameters."]);
    exit;
}

// Check if timestamp is passed
if ($timestamp) {
    // Convert the timestamp to a DateTime object
    $dateTime = new DateTime($timestamp);
    
     // Extract the date and time
    $date = $dateTime->format('d-m-Y');  // For example, "22-11-2024"
    $time = $dateTime->format('h:i A');  // For example, "10:30 PM"
}



// Validate target
$valid_targets = ['head-parish', 'sub-parish', 'community', 'groups'];
if (!in_array($target, $valid_targets)) {
    echo json_encode(["success" => false, "message" => "Invalid target type provided."]);
    exit;
}

if (!isset($_SESSION['head_parish_id'])) {
    header("Location: /error.php?message=" . urlencode("Unauthorized"));
    exit();
}
$head_parish_id = $_SESSION['head_parish_id'];
$parish_info = getParishInfo($conn, $head_parish_id);

$harambee_id = getHarambeeIdFromHarambeeGroup($conn, $harambee_group_id, $target);

$harambee_details = get_harambee_details($conn, $harambee_id, $target);
// Pretty print the response as JSON
// echo json_encode($harambee_details, JSON_PRETTY_PRINT);

// Get the Harambee group info
$harambee_group_info = getHarambeeGroupInfo($conn, $target, $harambee_group_id);
$harambee_group_start_date = $harambee_group_info['date_created'];

// Pretty print the response as JSON
// echo json_encode($harambee_group_info, JSON_PRETTY_PRINT);

// Get the list of member IDs for the Harambee group
$harambeeGroupMemberIds = getHarambeeGroupMemberIds($conn, $target, $harambee_group_id);


// Get contributions for the members by date
$harambee_members_contribution_by_date = getHarambeeGroupContributions($conn, $harambee_id, $harambeeGroupMemberIds, $harambee_group_start_date, $target);

// Output the contributions in JSON format
// echo json_encode($harambee_members_contribution_by_date, JSON_PRETTY_PRINT);

// Initialize the individual contributions array
$individual_contributions = [];

$group_members = 0;
// Iterate through each member ID and get their individual contributions
foreach ($harambeeGroupMemberIds as $member_id) {
    // Get member details
    $member_details = getMemberDetails($conn, $member_id)->fetch_assoc();
    
    // Construct full name
    $first_name = isset($member_details['first_name']) ? $member_details['first_name'] : '';
    $middle_name = isset($member_details['middle_name']) ? $member_details['middle_name'] : '';
    $last_name = isset($member_details['last_name']) ? $member_details['last_name'] : '';
    $full_name = trim($first_name . ' ' . $middle_name . ' ' . $last_name);  // Concatenate the names and trim any extra spaces
    
    // Get member's contributions
    $member_contribution = getHarambeeGroupMemberContribution($conn, $harambee_id, $member_id, $harambee_group_start_date, $target);
    $member_harambee_group_target = getHarambeeGroupMemberTarget($conn, $harambee_id, $member_id, $target);
    
    $member_harambee_group_target = ($member_harambee_group_target > 0) ? $member_harambee_group_target : $member_contribution;
    
    // Store the full name and contributions for each member
    $individual_contributions[$member_id] = [
        'full_name' => $full_name,
        'contribution' => $member_contribution,
        'target' => $member_harambee_group_target
    ];
    $group_members++;
}

// Sort the individual contributions by the 'contribution' field in descending order (highest to lowest)
usort($individual_contributions, function($a, $b) {
    return $b['contribution'] - $a['contribution']; // Sort in descending order
});

$harambee_group_target = str_replace([','], '', $harambee_group_info['harambee_group_target']);

// Convert it back to a numeric value
$harambee_group_target = (float) $harambee_group_target;

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
        
        @page {
            margin: 30px 30px 40px 30px; /* top, right, bottom, left */
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
            background-color: #000;
            color: #fff;
        }
        td {
            background-color: #fff;
            font-size: 12px;
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
        
        .header-bg{
            background-color:#B5C0D0;
        }
        .summary-row{
            background-color:#F5F7F8;
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
        .separator {
            width: 99.5%;
            background-color: #000;  
            height: 0.5px;           
            margin: 0 auto;          
        }
        .text-center{
            text-align:center;
        }
        .text-right{
            text-align:right;
        }
        .text-bold{
            font-weight:bold;
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
             <h1 style="font-size: 12px; color: #2c3e50; margin: 5px 0;">HARAMBEE GROUP CONTRIBUTION STATEMENT AS OF ' . $date . ' ' . $time . '</h1>
        </div>
        <div>
            <table>
                <tr>
                    <td rowspan="2" class="text-center text-bold">Harambee Details</td>
                    <td class="text-center text-bold">Description</td>
                    <td class="text-center text-bold">Start Date</td>
                    <td class="text-center text-bold">End Date</td>
                    <td class="text-center text-bold">Target Amount</td>
                </tr>
                <tr>
                    <td class="text-center">' . strtoupper(htmlspecialchars($harambee_details['description'])) . '</td>
                    <td class="text-center">'.htmlspecialchars(date("d M Y", strtotime($harambee_details['from_date']))).'</td>
                    <td class="text-center">'.htmlspecialchars(date("d M Y", strtotime($harambee_details['to_date']))).'</td>
                    <td class="text-center">TZS ' . number_format($harambee_details['amount'], 0) . '</td>
                </tr>
            </table>
            <div class="separator"></div>
            <table>
                <tr>
                    <td rowspan="2" class="text-center text-bold">Group Details</td>
                    <td class="text-center text-bold">Name</td>
                    <td class="text-center text-bold">Description</td>
                    <td class="text-center text-bold">No. of Members</td>
                    <td class="text-center text-bold">Date Created</td>
                    <td class="text-center text-bold">Target Amount</td>
                </tr>
                <tr>
                    <td class="text-center">'.$harambee_group_info['group_name'].'</td>
                    <td class="text-center">'.$harambee_group_info['harambee_group_description'].'</td>
                    <td class="text-center">'.$group_members.'</td>
                    <td class="text-center">'.htmlspecialchars(date("d M Y", strtotime($harambee_group_info['date_created']))).'</td>
                    <td class="text-center">TZS ' . number_format($harambee_group_target, 0) . '</td>
                </tr>
            </table>
            <div class="table-separator"></div>
        </div>
        <div>
            <table>
                <tr>
                    <td colspan="5" class="text-center text-bold header-bg">Contributions By Date</td>
                </tr>
                <tr>
                    <td class="text-center text-bold">#</td>
                    <td class="text-center text-bold">Date</td>
                    <td class="text-center text-bold">Details</td>
                    <td class="text-center text-bold">Total Contribution</td>
                    <td class="text-center text-bold">Balance</td>
                </tr>
                <tr>
                    <td class="text-center">1</td>
                    <td class="text-center">'.htmlspecialchars(date("d-m-Y", strtotime($harambee_group_info['date_created']))).'</td>
                    <td class="text-center">O/Balance</td>
                    <td class="text-center">0</td>
                    <td class="text-center">TZS ' . number_format($harambee_group_target, 0) . '</td>
                </tr>';
                $counter = 2;
                $sub_total = 0;
                foreach ($harambee_members_contribution_by_date as $contribution) { 
                $sub_total += $contribution['total_contribution'];
                $html .= '<tr>
                            <td class="text-center">' . $counter++ . '</td>
                            <td class="text-center">' . htmlspecialchars(date("d-m-Y", strtotime($contribution['contribution_date']))) . '</td>
                            <td class="text-center">Contribution</td>
                            <td class="text-center">TZS ' . number_format($contribution['total_contribution'], 0) . '</td>
                            <td class="text-center">TZS ' . number_format($harambee_group_target - $sub_total, 0) . '</td>
                          </tr>';

                }
        $html .= '<tr>
                    <td colspan="3" class="text-center text-bold summary-row">Grand Total</td>
                    <td class="text-center text-bold summary-row">TZS ' . number_format($sub_total, 0) . '</td>
                    <td class="text-center text-bold summary-row">TZS ' . number_format($harambee_group_target - $sub_total, 0) . '</td>
                  </tr>';

            $html .= '
            </table>
            <div class="table-separator"></div>
        </div>';
        
        // Group Member Contributions Table
        $html .= '
        <div>
            <table>
                <thead>
                <tr>
                    <td colspan="5" class="text-center text-bold header-bg">Group Members Contributions</td>
                </tr>
                <tr>
                    <td class="text-center text-bold" style="width:7%">#</td>
                    <td class="text-center text-bold">Name</td>
                    <td class="text-center text-bold">Target</td>
                    <td class="text-center text-bold">Total Contribution</td>
                    <td class="text-center text-bold">Balance</td>
                </tr>
                </thead>';
        
        $counter = 1;
        
        // Loop through the individual member contributions
        foreach ($individual_contributions as $member_id => $member_data) {
            $balance = $member_data['target'] - $member_data['contribution'];
            $isPositiveBalance = ($balance < 0);
        
            // Determine balance formatting
            $balanceDisplay = number_format(abs($balance), 0);
            $balanceColor = $isPositiveBalance ? 'style="color: green;"' : ''; // Green for positive balance
            $balancePrefix = $isPositiveBalance ? '+' : ''; // Add "+" for positive balance
        
            // Append HTML row
            $html .= '<tr>
                        <td class="text-center">' . $counter++ . '</td>
                        <td>' . htmlspecialchars($member_data['full_name']) . '</td>
                        <td class="text-center">' . number_format($member_data['target'], 0) . '</td>
                        <td class="text-center">' . number_format($member_data['contribution'], 0) . '</td>
                        <td class="text-center" ' . $balanceColor . '>' . $balancePrefix . $balanceDisplay . '</td>
                      </tr>';
        }

        
        $html .= '</table>
        </div>';
$html .='<div class="footer">
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


// Construct the filename with sub-parish name and date range
$filename = "harambee group statement.pdf";
$dompdf->stream($filename, array("Attachment" => false));
?>
