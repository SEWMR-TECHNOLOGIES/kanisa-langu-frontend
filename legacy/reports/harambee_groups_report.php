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
$harambee_id = isset($_GET['harambee_id']) ? $_GET['harambee_id'] : null;
$target = isset($_GET['target']) ? $_GET['target'] : null;
$timestamp = isset($_GET['timestamp']) ? $_GET['timestamp'] : null;
$sub_parish_id = isset($_GET['sub_parish_id']) ? $_GET['sub_parish_id'] : null;
if (!$harambee_id || !$target || !$timestamp) {
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
$harambee_details = get_harambee_details($conn, $harambee_id, $target);

$harambee_groups = getHarambeeGroupsContributions($conn, $harambee_id, $target, $sub_parish_id);

// Sort the individual contributions by the 'contribution' field in descending order (highest to lowest)
usort($harambee_groups, function($a, $b) {
    return $b['total_contribution'] - $a['total_contribution']; // Sort in descending order
});



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
             <h1 style="font-size: 12px; color: #2c3e50; margin: 5px 0;">HARAMBEE GROUPS CONTRIBUTION SUMMARY AS OF ' . $date . ' ' . $time . '</h1>
        </div>
        <div>
            <table>
                <thead>
              <tr>
                    <td class="text-center text-bold">#</td>
                    <td class="text-center text-bold">Group Name</td>
                    <td class="text-center text-bold">Date Created</td>
                    <td class="text-center text-bold">Target</td>
                    <td class="text-center text-bold">Total Contribution</td>
                    <td class="text-center text-bold">Balance</td>
                    <td class="text-center text-bold">Achievement (%)</td>
                </tr>
                </thead>
                <tbody>';
        
        $grand_total_target = 0;
        $grand_total_contribution = 0;
        $grand_total_balance = 0;
        $counter = 1;
        
        foreach ($harambee_groups as $group) {
            // Get group details
            $group_name = $group['group_name'];
            $group_description = $group['group_description'];
            $group_target = $group['group_target'];
            $total_contribution = $group['total_contribution'];
            $balance = $group['balance'];
            $date_created = $group['date_created'];
            
            // Calculate achievement percentage
            $achievement = ($group_target > 0) ? ($total_contribution / $group_target) * 100 : 0;
        
            // Accumulate grand totals
            $grand_total_target += $group_target;
            $grand_total_contribution += $total_contribution;
            $grand_total_balance += $balance;
        
            // Add group row to HTML
            $html .= '<tr>
                <td class="text-center">' . $counter++ . '</td>
                <td>' . htmlspecialchars($group_name) . '</td>
                <td>'.htmlspecialchars(date("d-m-Y", strtotime($date_created))).'</td>
                <td class="text-center">' . number_format($group_target, 0) . '</td>
                <td class="text-center">' . number_format($total_contribution, 0) . '</td>
                <td class="text-center">' . number_format($balance, 0) . '</td>
                <td class="text-center">' . number_format($achievement, 2) . '%</td>
            </tr>';
        }
        
        // Calculate overall achievement percentage
        $grand_total_achievement = ($grand_total_target > 0) ? ($grand_total_contribution / $grand_total_target) * 100 : 0;
        
        // Add the grand total row
        $html .= '<tr>
            <td colspan="3" class="text-center text-bold summary-row">Grand Total</td>
            <td class="text-center text-bold summary-row">' . number_format($grand_total_target, 0) . '</td>
            <td class="text-center text-bold summary-row">' . number_format($grand_total_contribution, 0) . '</td>
            <td class="text-center text-bold summary-row">' . number_format($grand_total_balance, 0) . '</td>
            <td class="text-center text-bold summary-row">' . number_format($grand_total_achievement, 2) . '%</td>
        </tr>';

        $html .= '
                </tbody>
            </table>
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
$filename = "harambee groups statement.pdf";
$dompdf->stream($filename, array("Attachment" => false));
?>
