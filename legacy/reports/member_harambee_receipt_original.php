<?php
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
$src = 'data:image/jpeg;base64,'.$imageData;

// Initialize member ID variable
$member = isset($_GET['member']) ? $_GET['member'] : null;
$harambee = isset($_GET['harambee']) ? $_GET['harambee'] : null;
$target = isset($_GET['target']) ? $_GET['target'] : null;
$date = isset($_GET['date']) ? $_GET['date'] : null;

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

// Fetch member details based on ID if available
if ($member_id) {
    // Call the getMemberDetails function
    $result = getMemberDetails($conn, $member_id);

    // Check if member exists
    if ($result->num_rows > 0) {
        $member = $result->fetch_assoc();
        
        // Check if the title is not null, then concatenate the title with the name
        $full_name = (!empty($member['title']) ? $member['title'] . '. ' : '') 
                     . strtoupper($member['first_name'] . ' ' . $member['middle_name'] . ' ' . $member['last_name']);
        
        // Sanitize the full name for a valid filename (removing any special characters)
        $sanitized_name = preg_replace('/[^A-Za-z0-9\- ]/', '', $full_name);
        
        // Get harambee details
        $harambee_details = get_harambee_details($conn, $harambee_id, $target);
        
        // Prepare HTML content
        $html = '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="shortcut icon" type="image/png" href="/assets/images/logos/favicon.png" />
            <title>' . $full_name . ' Harambee Receipt</title>
            <style>
                @font-face {
                    font-family: "Barlow";
                    src: url("../assets/fonts/Barlow-Regular.ttf") format("truetype");
                    font-weight: normal;
                    font-style: normal;
                }
                @font-face {
                    font-family: "Barlow";
                    src: url("../assets/fonts/Barlow-Bold.ttf") format("truetype");
                    font-weight: bold;
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
                    width: 80%;
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
                p {
                    font-size: 12px;
                }
                .member-info {
                    margin-top: 20px;
                    font-size: 14px;
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
                    padding: 8px;
                    text-align: center;
                }
                th {
                    background-color: #FF6D60;
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
                    <h1 style="font-size: 12px; color: #3498db; margin: 0;">K.K.K.T ' . $member['diocese_name'] . '</h1>
                    <h1 style="font-size: 12px; color: #2c3e50; margin: 5px 0;">' . $member['province_name'] . ' | ' . $member['head_parish_name'] . ' | ' . $member['sub_parish_name'] . '</h1>
                    <h1 style="font-size: 12px; color: #16a085; margin: 5px 0;">STAKABADHI YA HARAMBEE KWA AJILI YA ' . htmlspecialchars(strtoupper($harambee_details['description'])) . '</h1>
                    <div style="font-size: 12px; color: #16a085; margin: 5px 0;">TAREHE '.$formatted_date.'</div>
                </div>


                <div class="member-info">
                    <table class="table-auto w-full">
                        <tr>
                            <td><strong>JINA:</strong> ' . strtoupper($sanitized_name) . '</td>
                            <td><strong>Bahasha Na.:</strong> ' . $member['envelope_number'] . '</td>
                            <td><strong>SIMU:</strong> ' . $member['phone'] . '</td>
                        </tr>
                    </table>
                </div>';

                
        // Get contributions grouped by payment method for the selected date
        $contributions_by_method = getTotalContributionsOnDate($conn, $harambee_id, $member_id, $date, $target_table);
        $total_contribution_before_date = $contributions_by_method['total_before_date'];
        $total_contribution_on_date = 0;
        $html .= '
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Payment Method</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>';
        
        // Loop through the contributions and populate the table
        foreach ($contributions_by_method['on_date_contributions'] as $payment_method => $amount) {
            $total_contribution_on_date += $amount;
            $html .= '
                <tr>
                    <td>' . htmlspecialchars($payment_method) . '</td>
                    <td>' . number_format($amount, 0) . '</td>
                </tr>';
        }
        // Convert the total contribution on the date into words
        $total_contribution_in_words = numberToWords($total_contribution_on_date);
        $html .= '
                <tr>
                    <td colspan="2" style="color:blue">' . strtoupper($total_contribution_in_words)  . ' TANZANIAN SHILLINGS ONLY</td>
                </tr>';
        
        // If there are no contributions, display a message
        if (empty($contributions_by_method['on_date_contributions'])) {
            $html .= '
                <tr>
                    <td colspan="2" class="empty-cell">No contributions on ' . $formatted_date . '</td>
                </tr>';
        }
        
        $html .= '
                </tbody>
            </table>
        </div>';


    // Call the function to get member target and contributions
    $memberDetails = getMemberTargetAndContributions($conn, $harambee_id, $member_id, $target);
    $from_date = date("d M Y", strtotime($harambee_details['from_date']));
    
    
    if ($memberDetails === false) {
        echo json_encode(["success" => false, "message" => "Invalid target or unable to fetch details"]);
        exit();
    }

    // Extract target and contribution
    $target_amount = $memberDetails['target_amount'];
    $total_contribution = $total_contribution_before_date + $total_contribution_on_date;
    // Calculate balance and percentage
    $balance = ($target_amount == 0 && $total_contribution > 0) ? 0 : abs($target_amount - $total_contribution);
    $percentage = ($target_amount > 0) ? calculatePercentage($total_contribution, $target_amount) : 0;
    $percentage_color = ($percentage >= 100) ? 'green' : 'red';
    $balance_text = ($percentage >= 100) ? 'ZIDIO' : 'SALIO';
    // Format the numbers
    $formatted_target = 'TZS ' . number_format($target_amount, 0);

    
            // Add the summary table for target, total contribution, balance, and percentage
            $html .= '
                <div>
                    <table>
                        <thead>
                            <tr>
                                <td style="font-weight:bold;">JUMLA KUU</td>
                                <td style="color:blue">TZS ' . number_format($total_contribution, 0) . '</td>
                                <td style="font-weight:bold;">' . $balance_text . '</td>
                                <td style="color: ' . $percentage_color . ';">TZS ' . number_format($balance, 0) . '</td>
                                <td style="font-weight:bold;">MAFANIKIO</td>
                                <td style="color: ' . $percentage_color . ';">' . number_format($percentage, 2) . '%</td>
                            </tr>
                        </thead>
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
