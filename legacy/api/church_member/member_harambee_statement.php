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
$member_id = isset($_GET['member_id']) ? $_GET['member_id'] : null;
$harambee_id = isset($_GET['harambee_id']) ? $_GET['harambee_id'] : null;
$target = isset($_GET['target']) ? $_GET['target'] : null;

if ($member_id) {
    
        // Further validation if necessary
        if (empty($member_id) || !preg_match('/^[a-zA-Z0-9]+$/', $member_id)) {
             header("Location: /error.php?message=Invalid member ID.");
            exit;
        }
} else {
    // Handle case where member ID is not provided
    echo "Member ID is required.";
    exit;
}

if ($harambee_id) {

        
        // Further validation if necessary
        if (empty($harambee_id) || !preg_match('/^[a-zA-Z0-9]+$/', $harambee_id)) {
            header("Location: /error.php?message=Invalid harambee ID.");
            exit;
        }
} else {
    // Handle case where harambee ID is not provided
    echo "Harambee ID is required.";
    exit;
}

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

// Call the function to get individual contributions
$contribution_result = getContributionsByDate($conn, $member_id, $harambee_id, $target_table);

// Fetch total contributions
$total_contribution = 0;
$contributions = [];
while ($row = $contribution_result->fetch_assoc()) {
    $contributions[] = $row;
    $total_contribution += $row['amount_contributed'];
}

// Fetch member details based on ID if available
if ($member_id) {
    // Call the getMemberDetails function
    $member = getSingleMemberHarambeeDetails($conn, $member_id, $harambee_id, $target);

    // Check if member exists
    if ($member) {
        // Determine the full name or group name and make it uppercase
        $full_name = ($member['group_name'] == null) ? strtoupper(getMemberFullName($member)) : strtoupper($member['group_name']);
        
        // Sanitize the full name for a valid filename (removing any special characters)
        $sanitized_name = preg_replace('/[^A-Za-z0-9\- ]/', '', $full_name);
        
        // Get harambee details
        $harambee_details = get_harambee_details($conn, $harambee_id, $target);
        
        // Construct the HTML output based on whether group_name is null or not
        if ($member['group_name'] == null) {
            // Show full details: name, envelope number, phone
            $member_info = '
            <div class="member-info">
                <table class="table-auto w-full">
                    <tr>
                        <td><strong>JINA:</strong> ' . htmlspecialchars($full_name) . '</td>
                        <td><strong>Bahasha Na.:</strong> ' . htmlspecialchars($member['envelope_number']) . '</td>
                        <td><strong>SIMU:</strong> ' . htmlspecialchars($member['phone']) . '</td>
                    </tr>
                </table>
            </div>';
        } else {
            // Show only the group name
            $member_info = '
            <div class="member-info">
                <table class="table-auto w-full">
                    <tr>
                        <td><strong>JINA:</strong> ' . htmlspecialchars($full_name) . '</td>
                    </tr>
                </table>
            </div>';
        }
        
        // Prepare HTML content
        $html = '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="shortcut icon" type="image/png" href="/assets/images/logos/favicon.png" />
            <title>' . $full_name . ' Envelope Statement</title>
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
                    margin: 20px;
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
                    <h1 style="font-size: 12px; color: #3498db; margin: 0;">K.K.K.T ' . $member['diocese_name'] . '</h1>
                    <h1 style="font-size: 12px; color: #2c3e50; margin: 5px 0;">' . $member['province_name'] . ' | ' . $member['head_parish_name'] . '</h1>
                    <h1 style="font-size: 12px; color: #16a085; margin: 5px 0;">HARAMBEE KWA AJILI YA ' . htmlspecialchars(strtoupper($harambee_details['description'])) . '</h1>
                    <div style="font-size: 12px; color: #16a085; margin: 5px 0;">
                       KUTOKA: ' . htmlspecialchars(date("d M Y", strtotime($harambee_details['from_date']))) . ' HADI: ' . htmlspecialchars(date("d M Y", strtotime($harambee_details['to_date']))) . '
                    </div>
                </div>'.$member_info;

                
        // Contributions section
        $html .= '
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>TAREHE</th>
                                <th>KIASI (TZS)</th>
                                <th>NJIA YA MALIPO</th>
                            </tr>
                        </thead>
                        <tbody>';

        foreach ($contributions as $contribution) {
            $html .= '
                            <tr>
                                <td>' . htmlspecialchars(date("d M Y", strtotime($contribution['contribution_date']))) . '</td>
                                <td>' . number_format($contribution['amount_contributed'], 0) . '</td>
                                <td>' . htmlspecialchars($contribution['payment_method']) . '</td>
                            </tr>';
        }

        // If there are no contributions, add a message
        if (empty($contributions)) {
            $html .= '
                            <tr>
                                <td colspan="3" class="empty-cell">No contributions found</td>
                            </tr>';
        }

        $html .= '
                        </tbody>
                    </table>
                </div>';

    // Call the function to get member target and contributions
    $memberDetails = getMemberTargetAndContributions($conn, $harambee_id, $member_id, $target);

    if ($memberDetails === false) {
        echo json_encode(["success" => false, "message" => "Invalid target or unable to fetch details"]);
        exit();
    }

    // Extract target and contribution
    $target_amount = $memberDetails['target_amount'];
    $total_contribution = $memberDetails['total_contribution'];

    // Calculate balance and percentage
    $balance = ($target_amount == 0 && $total_contribution > 0) ? 0 : $target_amount - $total_contribution;
    $percentage = ($target_amount > 0) ? ($total_contribution / $target_amount) * 100 : 0;

    // Format the numbers
    $formatted_target = 'TZS ' . number_format($target_amount, 0);
    $formatted_contribution = 'TZS ' . number_format($total_contribution, 0);
    $formatted_balance = 'TZS ' . number_format($balance, 0);
    $formatted_percentage = number_format($percentage, 2) . '%';
    
            // Add the summary table for target, total contribution, balance, and percentage
            $html .= '
                <div>
                    <table>
                        <thead>
                            <tr>
                                <th>LENGO</th>
                                <th>TASLIMU</th>
                                <th>SALIO</th>
                                <th>MAFANIKIO</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>' . $formatted_target . '</td>
                                <td>' . $formatted_contribution . '</td>
                                <td>' . $formatted_balance . '</td>
                                <td>' . $formatted_percentage . '</td>
                            </tr>
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

        // Replace spaces with underscores to create the filename
        $filename = str_replace(' ', '_', $sanitized_name) . "_harambee_statement.pdf";

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
