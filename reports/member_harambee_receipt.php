<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/libraries/vendor/autoload.php');

// Initialize member ID variable
$member = isset($_GET['member']) ? $_GET['member'] : null;
$harambee = isset($_GET['harambee']) ? $_GET['harambee'] : null;
$target = isset($_GET['target']) ? $_GET['target'] : null;
$date = isset($_GET['date']) ? $_GET['date'] : null;

use Dompdf\Dompdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;

// Instantiate Dompdf
$dompdf = new Dompdf();

// Set up the options
$options = $dompdf->getOptions();
$options->setFontCache('fonts'); 
$options->set('isRemoteEnabled', true); 
$options->setChroot([$_SERVER['DOCUMENT_ROOT'] . '/assets/fonts/']);
$dompdf->setOptions($options);

// Path to the upward semicircle SVG file
$upward_svg_path = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/icons/upward_semi_circle.svg';
$upward_imageData = base64_encode(file_get_contents($upward_svg_path));
$upward_src = 'data:image/svg+xml;base64,' . $upward_imageData;

// Path to the downward semicircle SVG file
$downward_svg_path = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/icons/downward_semi_circle.svg';
$downward_imageData = base64_encode(file_get_contents($downward_svg_path));
$downward_src = 'data:image/svg+xml;base64,' . $downward_imageData;

// Path to the star SVG file
$star_svg_path = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/icons/star.svg'; 
$star_imageData = base64_encode(file_get_contents($star_svg_path));
$star_src = 'data:image/svg+xml;base64,' . $star_imageData;



// Function to render repeated characters (e.g., asterisks)
function renderRepeatedCharacters($character, $count) {
    return str_repeat($character, $count);
}

if ($member) {
    try {
        // Attempt to decrypt the member ID
        $member_id = decryptData($member);
        
        // Further validation if necessary
        if (empty($member_id) || !preg_match('/^[a-zA-Z0-9]+$/', $member_id)) {
            header("Location: /error.php?message=Invalid member ID.");
            exit;
        }
    } catch (Exception $e) {
        // Handle decryption failure
        header("Location: /error.php?message=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    // Handle case where member ID is not provided
    echo "Member ID is required.";
    exit;
}

if ($harambee) {
    try {
        // Attempt to decrypt the harambee ID
        $harambee_id = decryptData($harambee);
        
        // Further validation if necessary
        if (empty($harambee_id) || !preg_match('/^[a-zA-Z0-9]+$/', $harambee)) {
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

// Check if the date is provided and validate it
if ($date) {
    // Validate date format (YYYY-MM-DD)
    $date_pattern = '/^\d{4}-\d{2}-\d{2}$/';
    if (!preg_match($date_pattern, $date)) {
        header("Location: /error.php?message=Invalid date format.");
        exit;
    }
} else {
    // Handle case where the date is not provided
    echo "Date is required.";
    exit;
}

$formatted_date  = date("d M Y", strtotime($date));
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

$reportUrl = "https://kanisalangu.sewmrtechnologies.com/reports/member_harambee_receipt.php?member=$member&harambee=$harambee&date=$date&target=$target";

// Fetch member details based on ID if available
if ($member_id) {
    // Call the getMemberDetails function
    $member = getSingleMemberHarambeeDetails($conn, $member_id, $harambee_id, $target);

    // Check if member exists
    if ($member) {
        
        // Determine the full name or group name and make it uppercase
        $full_name = ($member['group_name'] == null) ? strtoupper(getMemberFullName($member)) : strtoupper($member['group_name']);
        
        // Construct the member details
        $member_details = ($member['group_name'] == null) 
            ? $full_name . ' | ' . $member['envelope_number'] . ' | ' . $member['phone'] 
            : $full_name; 

        $phone = $member['phone'];

        // Generate the QR code for the phone number
        $qrCode = QrCode::create($reportUrl)
            ->setSize(150)  // Set the size of the QR code
            ->setMargin(10) // Set the margin
            ->setEncoding(new Encoding('UTF-8'));
        
        $writer = new PngWriter();
        $qrCodeImage = $writer->write($qrCode);
        $qrCodeBase64 = base64_encode($qrCodeImage->getString());


        // Sanitize the full name for a valid filename (removing any special characters)
        $sanitized_name = preg_replace('/[^A-Za-z0-9\- ]/', '', $full_name);
        
        // Get harambee details
        $harambee_details = get_harambee_details($conn, $harambee_id, $target);
        $harambee_description = strtoupper($harambee_details['description']);
        $from_date = date("d M Y", strtotime($harambee_details['from_date']));
        $to_date = date("d M Y", strtotime($harambee_details['to_date']));
        
    
// Prepare HTML content
$html = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . $full_name . ' Harambee Receipt</title>
    <style>
        @font-face {
            font-family: "Barlow";
            src: url("../assets/fonts/Barlow-Regular.ttf") format("truetype");
        }
        body {
            font-family: "Barlow"; 
            margin: 0;
            padding: 0;
            color: #333;
            background-color: #fff;
            box-sizing:border-box;
            font-size:9px;
        }
        .container {
            width: 60%;
            margin: 20px auto;
            padding:10px;
            background-color: #eee;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index:-2;
        }
        h1 {
            font-size: 18px;
            margin: 0;
            text-align: center;
            font-weight: bold;
        }
        p {
            text-align: center;
            margin: 0;
        }
        .table-container {
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 4px;
            text-align: left;
        }
        th {
            font-weight: bold;
        }
        .amount-words {
            text-align: center;
        }
        .thank-you {
            text-align: center;
            font-weight: bold;
        }
        
        .asterisks {
            text-align: center;
            letter-spacing:10px;
        }
        .top, .bottom {
            position:relative;
        }
        .semi-circle{
             width: 10px;
             height:13px;
             margin: 0 1px;
        }
        .stars img {
            width: 30px;
            margin: 0 1px;
        }
        .top{
            top:21;
        }
        .bottom{
            bottom:18;
        }
        .content{
            position:relative;
            z-index:-1;
        }
    </style>
</head>
<body>
    <div class="wave top">';
        
// Loop to generate repeated semicircles at the top
for ($i = 0; $i < 35; $i++) {
    $html .= '<img class="semi-circle" src="' . $upward_src . '" alt="Upward Semicircle">';
}
// Get contributions grouped by payment method for the selected date
        $contributions_by_method = getTotalContributionsOnDate($conn, $harambee_id, $member_id, $date, $target_table);
        $total_contribution_before_date = $contributions_by_method['total_before_date'];
        $total_contribution_on_date = 0;
$html .= '
        </div>
    <div class="container">
        <div class="content">
        <p style="margin: 0; font-weight: bold; line-height: 1.2;">K.K.K.T ' . $member['diocese_name'] . '</p>
        <p style="margin: 5px 0; font-weight: bold; line-height: 1.2;">' . $member['province_name'] . ' | ' . $member['head_parish_name'] . '</p>
        <p style="margin: 5px 0; font-weight: bold; line-height: 1.2;">MTAA WA ' . $member['sub_parish_name'] . ' | JUMUIYA YA  ' . strtoupper($member['community_name']) . '</p>
        <p style="font-weight: bold; line-height: 1.2;">' . htmlspecialchars($member_details) . '</p>

        
        <div class="asterisks" style="margin-top:10px">'.
            renderRepeatedCharacters('*', 20) . 
        '</div>
        <div style="text-align:center; max-width: 200px; margin: 0 auto;">
            STAKABADHI YA MCHANGO WA HARAMBEE
            <P>KWA AJILI YA '.$harambee_description.'.</P>
            <p>'.$from_date.' - '.$to_date.'</p>
        </div>

 <div class="asterisks">'.
            renderRepeatedCharacters('*', 20) . 
        '</div>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <td style="text-align:left;">Tarehe</td>
                        <td style="text-align:right;">'.$formatted_date.'</td>
                    </tr>
                    <tr>
                        <th style="text-align:left;">Maelezo</th>
                        <th style="text-align:right;">Kiasi</th>
                    </tr>
                </thead>
                <tbody>';
                   // Loop through the contributions and populate the table
        foreach ($contributions_by_method['on_date_contributions'] as $payment_method => $amount) {
            // Only include contributions with an amount greater than 0
            if ($amount > 0) {
                $total_contribution_on_date += $amount;
                $html .= '
                    <tr>
                        <td style="text-align:left">' . htmlspecialchars($payment_method) . '</td>
                        <td style="text-align:right">' . number_format($amount, 0) . '</td>
                    </tr>';
            }
        }
            $html .= '
                <tr>
                    <td style="text-align:left;font-weight:bold;">JUMLA</td>
                    <td style="text-align:right;font-weight:bold;">' . number_format($total_contribution_on_date, 0) . '</td>
                </tr>';
        $number = new Number($total_contribution_on_date);
        $total_contribution_in_words = $number->convertToWords();
        // Call the function to get member target and contributions
        $memberHarambeeDetails = getMemberTargetAndContributions($conn, $harambee_id, $member_id, $target);
        // Extract target and contribution
        $target_amount = $memberHarambeeDetails['target_amount'];
        $total_contribution = $total_contribution_before_date + $total_contribution_on_date;
        // Calculate balance and percentage
        $balance = ($target_amount == 0 && $total_contribution > 0) ? 0 : abs($target_amount - $total_contribution);
        $percentage = ($target_amount > 0) ? calculatePercentage($total_contribution, $target_amount) : 0;
        $percentage_color = ($percentage >= 100) ? 'green' : 'red';
        $balance_text = ($percentage >= 100) ? 'Ziada' : 'Salio';
    // Format the numbers
    $formatted_target = 'TZS ' . number_format($target_amount, 0);
        $html .= '
                </tbody>
            </table>
        </div>
         <div class="asterisks">'.
            renderRepeatedCharacters('*', 20) .
        '</div>
        <p class="amount-words">'.strtoupper($total_contribution_in_words).' TU.</p>
         <div class="asterisks">'.
            renderRepeatedCharacters('*', 20) .
        '</div>
        <table>
            <tr>
                <td style="text-align:left;">Ahadi</td>
                <td style="text-align:right;">'.number_format($target_amount).'</td>
            </tr>
            <tr>
                <td style="text-align:left;">Taslimu</td>
                <td style="text-align:right;">'.number_format($total_contribution, 0).'</td>
            </tr>
            <tr>
                <td style="text-align:left;">'.$balance_text.'</td>
                <td style="text-align:right;">'.number_format($balance, 0).'</td>
            </tr>
        </table>
         <div class="asterisks">'.
            renderRepeatedCharacters('*', 20) . 
        '</div>
        <p class="thank-you">MUNGU AKUBARIKI</p>
        <div style="text-align: center;margin-top:10px;">
            <img src="data:image/png;base64,' . $qrCodeBase64 . '" alt="Bar Code" height="20" style="display: block; margin: 0 auto;">
        </div>
        </div>
    </div>
<div class="wave bottom">';

    // Loop to generate repeated semicircles at the bottom
    for ($i = 0; $i < 35; $i++) {
        $html .= '<img class="semi-circle" src="' . $downward_src . '" alt="Downward Semicircle">';
    }
    
    $html .= '
            </div>
    </body>
    </html>';
    
    // Load HTML into Dompdf
    $dompdf->loadHtml($html);
    
    // Set paper size and orientation
    $dompdf->setPaper('A5', 'portrait');
    
    // Render the HTML as PDF
    $dompdf->render();

    // Replace spaces with underscores to create the filename
    $filename = str_replace(' ', '_', $sanitized_name) . "_harambee_receipt_$date.pdf";

    // Stream the PDF with the dynamic filename
    $dompdf->stream($filename, array("Attachment" => false));

    } else {
        // Handle member not found
        echo "Member not found.";
    }
} else {
    // Handle invalid member ID
    echo "Invalid member ID.";
}

?>
