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

$sub_parish_id = isset($_GET['sub_parish_id']) ? $_GET['sub_parish_id'] : null;
$community_id = isset($_GET['community_id']) ? $_GET['community_id'] : null;
$gender = trim($_GET['gender']);

// Validate sub_parish_id
if (empty($sub_parish_id) || !preg_match('/^[a-zA-Z0-9]+$/', $sub_parish_id)) {
    echo json_encode(["success" => false, "message" => "Invalid sub-parish ID."]);
}

// Validate community_id
if (empty($community_id) || !preg_match('/^[a-zA-Z0-9]+$/', $community_id)) {
    echo json_encode(["success" => false, "message" => "Invalid sub-parish ID."]);
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

switch ($gender) {
    case 'Male':
        $gender_text = 'JINSIA YA KIUME';
        break;
    case 'Female':
        $gender_text = 'JINSIA YA KIKE';
        break;
    case '':
        $gender_text = 'HAWANA TAARIFA ZA JINSIA';
        break;
    case 'all':
    default:
        $gender_text = 'JINISA ZOTE';
        break;
}


// Prepare HTML content
$html = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/png" href="/assets/images/logos/favicon.png" />
    <title>'.$community_name.' Members List</title>
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
            <h1 style="font-size: 12px; color: #2c3e50; margin: 5px 0;">ORODHA YA WANAJUMUIYA MTAA WA '. $sub_parish_name.' JUMUIYA YA '.$community_name.' | '.$timestamp.'</h1>
            <h1 style="font-size: 12px; color: #a23b56; margin: 5px 0;">'.$gender_text.'</h1>
        </div>';
        

    $html .= '
        <div class="table-container">
        <div class="table-separator"></div>
            <table>
                <thead>
                    <tr>
                        <th colspan="6">ORODHA YA WANAJUMUIYA YA ' . htmlspecialchars($community_name) . '</th>
                    </tr>
                    <tr>
                        <th>#</th>
                        <th>JINA</th>
                        <th>SIMU</th>
                        <th>BAHASHA</th>
                        <th>MTAA</th>
                        <th>JUMUIYA</th>
                    </tr>
                </thead>
                <tbody>';
    
    // Step 1: Collect member details
    $members = [];
    $memberIds = getMemberIdsByLocation($conn, $head_parish_id, $sub_parish_id, $community_id, $gender);
    
    foreach ($memberIds as $memberId) {
        $details = getMemberDetails($conn, $memberId)->fetch_assoc();
        if ($details) {
            $details['full_name'] = trim($details['title'] . ' ' . $details['first_name'] . ' ' . $details['middle_name'] . ' ' . $details['last_name']);
            $members[] = $details;
        }
    }
    
    // Step 2: Sort alphabetically by full name
    usort($members, function ($a, $b) {
        return strcmp($a['full_name'], $b['full_name']);
    });
    
    // Step 3: Render rows
    $counter = 1;
    
    foreach ($members as $details) {
        $rowStyle = ($details['type'] === 'Mgeni') ? ' style="background-color: #d0e7ff;"' : '';
    
        $html .= '
            <tr' . $rowStyle . '>
                <td>' . $counter++ . '</td>
                <td>' . htmlspecialchars($details['full_name']) . '</td>
                <td>' . (!empty($details['phone']) ? htmlspecialchars(preg_replace('/^255/', '0', $details['phone'])) : '') . '</td>
                <td>' . (!empty($details['envelope_number']) ? htmlspecialchars($details['envelope_number']) : '') . '</td>
                <td>' . htmlspecialchars($details['sub_parish_name']) . '</td>
                <td>' . htmlspecialchars($details['community_name']) . '</td>
            </tr>';
    }
    
    $html .= '</tbody>
            </table>
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
$dompdf->setPaper('A4', 'portrait');

// Render the HTML as PDF
$dompdf->render();

$canvas = $dompdf->get_canvas();

// Add page numbers to each page dynamically
// $canvas->pageText(270, 800, "Page {PAGE_NUM} of {PAGE_COUNT}", null, 12, array(0, 0, 0));

// Stream the PDF with a dynamic filename
$filename = $community_name." members list.pdf";
$dompdf->stream($filename, array("Attachment" => false));
?>
