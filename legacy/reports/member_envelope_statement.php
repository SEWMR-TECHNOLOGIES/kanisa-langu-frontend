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

// Initialize member ID variable
$member = isset($_GET['member']) ? $_GET['member'] : null;

if ($member) {
    try {
        // Attempt to decrypt the member ID
        $member_id = decryptData($member);
        
        // Further validation if necessary
        if (empty($member_id) || !preg_match('/^[a-zA-Z0-9]+$/', $member_id)) {
             header("Location: /error.php?message=Invalid member ID.");
            exit;
        }
        
        // Continue with the rest of your logic using $member_id
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

// Get the year from GET parameter or use the current year as default
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Validate the year
if (!is_numeric($year) || $year < 2000 || $year > date('Y')) {
    $year = date('Y');
}

$imageData = base64_encode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/assets/images/logos/kkkt-logo.jpg'));
$src = 'data:image/jpeg;base64,'.$imageData;


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
        // Prepare HTML content
        $html = '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="shortcut icon" type="image/png" href="/assets/images/logos/favicon.png" />
            <title>'.$full_name.' Envelope Statement</title>
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
                p{
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
                    background-color: #4187f8;
                    color: #fff;
                }
                td {
                    background-color: #f9f9f9;
                    font-size: 12px;
                    border:1px solid #fff;
                }
                .empty-cell{
                    background-color:rgba(65, 135, 248, 0.5);
                }
                .week-header{
                    background-color:#4187f8;
                }
                .summary-header{
                    background-color:#4187f8;
                    font-size: 10px;
                }
                .bold {
                    font-weight:bold;
                }
                .total-text {
                    color:#4187f8;
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
            <div class="container">';
            $html .= '
                <div class="header" style="text-align: center; margin-bottom: 20px;">
                    <img src="' . $src . '" alt="Church Logo" height="50" style="margin-bottom: 10px;">
                    <h1 style="font-size: 12px; color: #3498db; margin: 0;">K.K.K.T ' . $member['diocese_name'] . '</h1>
                    <h1 style="font-size: 12px; color: #2c3e50; margin: 5px 0;">' . $member['province_name'] . ' | ' . $member['head_parish_name'] . ' | MTAA WA ' . $member['sub_parish_name'] . '</h1>
                    <h1 style="font-size: 12px; color: #16a085; margin: 5px 0;">SADAKA YANGU KWA BWANA | MWAKA: ' . $year . '</h1>
                </div>';
            
                $html .= '
                <div class="member-info">
                    <table class="table-auto w-full">
                        <tr>
                            <td><strong>JINA:</strong> ' . strtoupper($sanitized_name) . '</td>
                            <td><strong>Bahasha Na.:</strong> ' . $member['envelope_number'] . '</td>
                            <td><strong>SIMU:</strong> ' . $member['phone'] . '</td>
                        </tr>
                    </table>
                </div>

             <div class="table-container">
                    <table>
                        <thead>
                            <tr>';

        // First Half of the Year Months
        for ($half_year = 1; $half_year <= 2; $half_year++) {
            $html .= '<th class="week-header">WIKI</th>';
            $months = ($half_year == 1) ? array('JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN') : array('JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC');
            foreach ($months as $month) {
                $html .= '<th class="week-header">' . $month . '</th>';
            }

            $html .= '          </tr>
                            </thead>
                            <tbody>';

            // Loop through 5 weeks
            for ($week = 0; $week < 5; $week++) {
                $html .= '<tr><td class="bold">' . ($week + 1) . '</td>';
                
                foreach ($months as $month) {
                    // Extract month number and format it
                    $month_number = date_parse($month)['month'];
                    $current_month = sprintf("%02d", $month_number); 
                    $current_year = $year;
                    
                    // Calculate Sunday date for the current month and week
                    $sunday_date = date('Y-m-d', strtotime("first Sunday of $month $current_year +$week weeks"));
                    
                    // Calculate the start date as six days before the Sunday date
                    $start_date = date('Y-m-d', strtotime($sunday_date . ' -6 days'));
                    
                    // Calculate the last day of the current month
                    $last_day_of_month = date('Y-m-t', strtotime("$current_year-$current_month-01"));
                    
                    // Adjust the start date if it goes back to the previous month
                    if (date('m', strtotime($start_date)) != $current_month) {
                        $start_date = "$current_year-$current_month-01";
                    }
                    
                    // Adjust the end date if it goes into the next month
                    if (date('m', strtotime($sunday_date)) != $current_month) {
                        $sunday_date = $last_day_of_month;
                    }
                    
                    // Debug output
                    // error_log("Week " . ($week + 1) . ", Month $month: Start Date: $start_date, End Date: $sunday_date");
                    
                    // Query to fetch sum of envelope amounts collected between the start date and Sunday date
                    $stmt_fetch_weekly_amount = $conn->prepare("SELECT SUM(amount) FROM envelope_contribution WHERE member_id = ? AND DATE(contribution_date) BETWEEN ? AND ?");
                    $stmt_fetch_weekly_amount->bind_param("iss", $member_id, $start_date, $sunday_date);
                    $stmt_fetch_weekly_amount->execute();
                    $stmt_fetch_weekly_amount->bind_result($weekly_amount);
                    $stmt_fetch_weekly_amount->fetch();
                    
                    // Debug output
                    // error_log("Weekly Amount: $weekly_amount");
                    
                    // Display fetched amount or leave cell blank if no amount collected
                    if ($weekly_amount > 0.00) {
                        $html .= '<td>' . number_format($weekly_amount) . '</td>';
                    } else {
                        $html .= '<td class="empty-cell"></td>';
                    }
                    
                    $stmt_fetch_weekly_amount->close();
                }
                
                $html .= '</tr>'; // End of week row
            }


            // Grand Total Row
            $html .= '<tr><td class="bold">JUMLA</td>';
            foreach ($months as $month) {
                // Extract month number and format it
                $month_number = date_parse($month)['month'];
                $current_month = sprintf("%02d", $month_number); // Ensure leading zero
                $current_year = $year;
                
                // Calculate the first and last day of the current month
                $start_date = "$current_year-$current_month-01";
                $last_day_of_month = date('Y-m-t', strtotime($start_date));
                
                // Fetch total collected amount for Sundays of the current month within the date range
                $stmt_fetch_total_amount = $conn->prepare("SELECT SUM(amount) FROM envelope_contribution WHERE member_id = ? AND DATE(contribution_date) BETWEEN ? AND ?");
                $stmt_fetch_total_amount->bind_param("iss", $member_id, $start_date, $last_day_of_month);
                $stmt_fetch_total_amount->execute();
                $stmt_fetch_total_amount->bind_result($total_amount);
                $stmt_fetch_total_amount->fetch();

                if ($total_amount > 0.00) {
                    $html .= '<td class="bold">' . number_format($total_amount) . '</td>';
                } else {
                    $html .= '<td class="bold">0</td>';
                }
                $stmt_fetch_total_amount->close();
            }
            $html .= '</tr>'; 
        }

        $member_data = fetchMemberEnvelopeData($conn, $member_id, $year);


        $total_envelope_contribution = $member_data['total_envelope_contribution'];
        $yearly_envelope_target = $member_data['yearly_envelope_target'];
        $total_annual_envelopes = $member_data['total_annual_envelopes'];
        $total_envelopes_until_today = $member_data['total_envelopes_until_today'];
        $member_contributions_until_today = $member_data['member_contributions_until_today'];

        // $envelope_balance  = ($total_envelope_contribution > $yearly_envelope_target) ? '+'. number_format($total_envelope_contribution - $yearly_envelope_target, 0) : number_format($yearly_envelope_target - $total_envelope_contribution, 0);
        if ($yearly_envelope_target == 0 && $total_envelope_contribution > 0) {
            // Case: No target but contribution exists
            $envelope_title = 'SALIO';
            $envelope_balance = number_format(0, 0);
        } elseif ($total_envelope_contribution > $yearly_envelope_target) {
            // Case: Contribution greater than target
            $envelope_title = 'ZIADA';
            $envelope_balance = '+' . number_format($total_envelope_contribution - $yearly_envelope_target, 0);
        } else {
            // Case: Normal balance (not yet reached target)
            $envelope_title = 'SALIO HADI SASA';
            $envelope_balance = number_format($yearly_envelope_target - $total_envelope_contribution, 0);
        }
        $html .= '        </tbody>
                    </table>
                </div>';
                $html .= '
                <div class="overflow-x-auto"> 
                    <table class="table-auto w-full border-collapse">
                        <thead>
                            <tr>
                                <th class="summary-header">AHADI YANGU KWA BWANA</th>
                                <th class="summary-header">MUNGU AMENIBARIKI KUMTOLEA</th>
                                <th class="summary-header">'.$envelope_title.'</th>
                                <th class="summary-header">USHIRIKI WANGU WA IBADA</th>
                                <th class="summary-header">KATI YA IBADA</th>
                                <th class="summary-header">UPUNGUFU WA USHIRIKI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bold total-text">
                                <td class="text-center">' . number_format($yearly_envelope_target) . '</td>
                                <td class="text-center">' . number_format($total_envelope_contribution) . '</td>
                                <td class="text-center">' . $envelope_balance . '</td>
                                <td class="text-center">' . $member_contributions_until_today . '</td>
                                <td class="text-center">' . $total_envelopes_until_today . '</td>
                                <td class="text-center">' . ($total_envelopes_until_today - $member_contributions_until_today) . '</td>
                            </tr>
                        </tbody>
                    </table>
                </div>';
                
        // Footer with Totals
        $html .= '
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
        $filename = str_replace(' ', '_', $sanitized_name) . "_member_envelope_report.pdf";// Replace spaces with underscores to create the filename
        
        // Stream the PDF with the dynamic filename
        $dompdf->stream($filename, array("Attachment" => false));
        echo 'Error: Member not found.';
    }
} else {
    echo 'Error: Member ID is missing.';
}
?>
