<?php
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/libraries/vendor/autoload.php');

use Dompdf\Dompdf;

$dompdf = new Dompdf();
$options = $dompdf->getOptions();
$options->setFontCache('fonts');
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);
$options->setChroot([$_SERVER['DOCUMENT_ROOT'] . '/assets/fonts/']);
$dompdf->setOptions($options);

$imageData = base64_encode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/assets/images/logos/kkkt-logo.jpg'));
$src = 'data:image/jpeg;base64,' . $imageData;

$harambee_id = isset($_GET['harambee_id']) ? $_GET['harambee_id'] : null;
$target = isset($_GET['target']) ? $_GET['target'] : null;
$contribution_date = isset($_GET['contribution_date']) ? $_GET['contribution_date'] : date('Y-m-d');

if ($harambee_id) {
    try {
        $harambee_id = decryptData($harambee_id);
        if (empty($harambee_id) || !preg_match('/^[a-zA-Z0-9]+$/', $harambee_id)) {
            echo json_encode(["success" => false, "message" => "Invalid harambee ID."]);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Error decrypting harambee ID: " . $e->getMessage()]);
        exit;
    }
} else {
    echo json_encode(["success" => false, "message" => "Harambee ID is required."]);
    exit;
}

$valid_targets = ['head-parish', 'sub-parish', 'community', 'groups'];
if (!in_array($target, $valid_targets)) {
    echo json_encode(["success" => false, "message" => "Invalid target type provided."]);
    exit;
}

if ($contribution_date) {
    $date = DateTime::createFromFormat('Y-m-d', $contribution_date);
    if (!$date || $date->format('Y-m-d') !== $contribution_date) {
        echo json_encode(["success" => false, "message" => "Invalid contribution date. Please use the format YYYY-MM-DD."]);
        exit;
    }
} else {
    echo json_encode(["success" => false, "message" => "Contribution date is required."]);
    exit;
}

if (!isset($_SESSION['head_parish_id'])) {
    header("Location: /error.php?message=" . urlencode("Unauthorized"));
    exit();
}

$head_parish_id = $_SESSION['head_parish_id'];
$parish_info = getParishInfo($conn, $head_parish_id);
$harambee_details = get_harambee_details($conn, $harambee_id, $target);

/**
 * Determine target table based on target type
 */
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

/**
 * Fetch unique clerks (recorded_by) for the given harambee and date.
 * Using contribution_date to find records for the day
 */
$stmt = $conn->prepare("SELECT DISTINCT recorded_by FROM {$target_table} WHERE harambee_id = ? AND DATE(contribution_date) = ? AND recorded_by IS NOT NULL");
$stmt->bind_param("ss", $harambee_id, $contribution_date);
$stmt->execute();
$result = $stmt->get_result();
$clerks = [];
while ($row = $result->fetch_assoc()) {
    $clerks[] = $row['recorded_by'];
}
$stmt->close();


if (empty($clerks)) {
    echo json_encode(["success" => false, "message" => "No records found for the specified date/harambee."]);
    exit();
}

/**
 * Initialize overall aggregates (across all clerks)
 */
$overallGrandTotal = 0;
$overallAttendedCount = 0;
$overallPaymentMethodTotals = [];

/**
 * Start building HTML (header + parish info)
 */
$html = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/png" href="/assets/images/logos/favicon.png" />
    <title>Harambee Recording Statistics</title>
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
            font-family: helvetica; 
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
            font-size:10px;
        }
        td {
            font-size: 10px;
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
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color:#fff; font-weight:bold; text-align:center;">
                        <td colspan="2" style="padding: 10px;">
                            ORODHA YA WANAUSHARIKA AMBAO MICHANGO YAO YA HARAMBEE IMEREKODIWA LEO TAREHE ' . date('d/m/Y', strtotime($contribution_date)) . '
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="text-align:left; padding: 8px;"><strong>Harambee:</strong> ' . $harambee_details['description'] . '</td>
                        <td style="text-align:right; padding: 8px;"><strong>KIPINDI:</strong> ' . htmlspecialchars(date("d M Y", strtotime($harambee_details['from_date']))) . ' HADI ' . htmlspecialchars(date("d M Y", strtotime($harambee_details['to_date']))) . '</td>
                    </tr>
                </tbody>
            </table>
        </div>';  

/**
 * For each clerk (recorded_by) produce same detailed output that was originally generated for a single clerk.
 */
foreach ($clerks as $clerk_id) {

    // Ensure clerk_id is numeric (defensive)
    $clerk_id = (int)$clerk_id;
    if ($clerk_id <= 0) {
        continue;
    }

    // Get clerk admin info
    $clerk_info = getHeadParishAdminDetailsById($conn, $head_parish_id, $clerk_id);
    if (!$clerk_info) {
        // Skip if cannot fetch clerk info
        continue;
    }

    // Fetch member ids recorded by this clerk on the date
    $on_date_member_ids = getMemberIdsRecordedByAdmin($conn, $harambee_id, $contribution_date, $target, $clerk_id);

    $on_date_member_details_array = [];
    $on_date_processed_groups = [];
    $grouped_by_community = [];

    foreach ($on_date_member_ids as $member_id) {
        $member = getSingleMemberHarambeeDetails($conn, $member_id, $harambee_id, $target);

        if ($member['group_name'] != null) {
            if (in_array($member['group_name'], $on_date_processed_groups)) {
                continue;
            }
            $full_name = $member['group_name'];
            $on_date_processed_groups[] = $member['group_name'];
        } else {
            $full_name = getMemberFullName($member);
        }

        $contribution_result = getTotalContributionsBetweenDates($conn, $harambee_id, $member_id, $contribution_date, $contribution_date, $target_table, $clerk_id);
        if ($contribution_result === false) {
            echo json_encode(["success" => false, "message" => "Invalid target or unable to fetch details"]);
            exit();
        }
     
        $amount_contributed_before_date = $contribution_result['total_before_date'];
        $amount_contributed_on_date = $contribution_result['on_date_contributions'];
        $total_contributed_up_to_date = $contribution_result['total_contributed'];
        $latest_local_timestamp = $contribution_result['latest_local_timestamp'];
        $payment_methods = $contribution_result['payment_methods'];
        $memberDetails = getMemberTargetAndContributions($conn, $harambee_id, $member_id, $target);
        
        if ($memberDetails === false) {
            echo json_encode(["success" => false, "message" => "Invalid target or unable to fetch details"]);
            exit();
        }

        $target_amount = $memberDetails['target_amount'];
        $total_contribution = $memberDetails['total_contribution'];
        $balance = $target_amount - $total_contribution;
        $balance = ($target_amount > 0) ? ($target_amount - $total_contribution) : 0;
        $percentage = $target_amount > 0 ? calculatePercentage($total_contribution, $target_amount) : 0.00;

        $member_details = [
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
            'target' => $target_amount,
            'contribution' => $total_contribution,
            'amount_contributed_before_date' => $amount_contributed_before_date,
            'amount_contributed_on_date' => $amount_contributed_on_date,
            'total_contributed_up_to_date' => $total_contributed_up_to_date,
            'balance' => $balance,
            'percentage' => $percentage,
            'latest_local_timestamp' => $latest_local_timestamp,
            'payment_methods' => $payment_methods
        ];

        $on_date_member_details_array[] = $member_details;
    }

    // Sort all by latest_local_timestamp ascending
    usort($on_date_member_details_array, function ($a, $b) {
        return strtotime($a['latest_local_timestamp']) - strtotime($b['latest_local_timestamp']);
    });

    // Group into phases (same logic as original)
    $grouped_phases = [];
    $current_group = [];
    $previous_time = null;

    foreach ($on_date_member_details_array as $member) {
        $timestamp = $member['latest_local_timestamp'];

        if ($timestamp === null) {
            continue;
        }

        $current_time = new DateTime($timestamp);

        if ($previous_time === null) {
            $current_group[] = $member;
            $previous_time = $current_time;
        } else {
            $diff_in_minutes = abs($previous_time->getTimestamp() - $current_time->getTimestamp()) / 60;

            if ($diff_in_minutes <= 60) {
                $current_group[] = $member;
            } else {
                $grouped_phases[] = $current_group;
                $current_group = [$member];
            }

            $previous_time = $current_time;
        }
    }

    if (!empty($current_group)) {
        $grouped_phases[] = $current_group;
    }

    // Append per-clerk header (similar to original single-admin header)
    $html .= '
    <div class="table-container" style="margin-top:18px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tbody>
                <tr>
                    <td style="padding: 8px;"><strong>Jina la Karani:</strong> ' . $clerk_info['full_name'] . '</td>
                    <td style="padding: 8px;text-align:right;"><strong>Nambari ya Simu:</strong> ' . $clerk_info['phone'] . '</td>
                </tr>
            </tbody>
        </table>
    </div>';

    // Per-clerk aggregates
    $clerkGrandTotal = 0;
    $clerkAttendedCount = 0;
    $clerkPaymentMethodTotals = [];

    // For each phase for this clerk produce the same tables
    foreach ($grouped_phases as $index => $group) {
        $totalTarget = 0;
        $totalContributedBeforeDate = 0;
        $totalContributedOnDate = 0;
        $totalContributedUpToDate = 0;
        $totalBalance = 0;
        $phase = $index + 1;
        $groupPaymentMethodTotals = [];

        $timestamps = [];
        foreach ($group as $member) {
            if (!empty($member['latest_local_timestamp'])) {
                $timestamps[] = new DateTime($member['latest_local_timestamp']);
            }
        }
        $timeRange = '';
        if (!empty($timestamps)) {
            usort($timestamps, fn($a, $b) => $a <=> $b);
            $minTime = $timestamps[0]->format('h:i A');
            $maxTime = end($timestamps)->format('h:i A');
            $timeRange = " ($minTime - $maxTime)";
        }

        $html .= '
        <table border="1" cellpadding="5" cellspacing="0" style="margin-top:10px;">
                <thead>
                    <tr>
                        <th colspan="9">AWAMU YA ' . $phase . $timeRange . '</th>
                    </tr>
                    <tr>
                        <th style="width:4%">#</th>
                        <th style="width:20%">Jina</th>
                        <th style="width:12%">Jumuiya</th>
                        <th style="width:12%">Ahadi</th>
                        <th style="width:11%">Taslimu Mpya</th>
                        <th style="width:11%">Taslimu Kuu</th>
                        <th style="width:11%">Salio</th>
                        <th style="width:12%">Muda</th>
                        <th style="width:7%">Njia za Malipo</th>
                    </tr>
                </thead>
                <tbody>';
        $counter = 1;
        foreach ($group as $member) {
            $totalTarget += $member['target'];
            $totalContributedBeforeDate += $member['amount_contributed_before_date'];
            $totalContributedOnDate += $member['amount_contributed_on_date'];
            $totalContributedUpToDate += $member['contribution'];
            $contribution_time = (new DateTime($member['latest_local_timestamp']))->format('h:i:s A');

            foreach ($member['payment_methods'] as $method) {
                $method = ucfirst(trim($method));
            
                // Add to overall totals
                if (!isset($overallPaymentMethodTotals[$method])) {
                    $overallPaymentMethodTotals[$method] = 0;
                }
                $overallPaymentMethodTotals[$method] += $member['amount_contributed_on_date'];
            
                // Add to clerk-specific totals
                if (!isset($clerkPaymentMethodTotals[$method])) {
                    $clerkPaymentMethodTotals[$method] = 0;
                }
                $clerkPaymentMethodTotals[$method] += $member['amount_contributed_on_date'];

                // Add to group-specific totals
                if (!isset($groupPaymentMethodTotals[$method])) {
                    $groupPaymentMethodTotals[$method] = 0;
                }
                $groupPaymentMethodTotals[$method] += $member['amount_contributed_on_date'];
            }

            if ($member['balance'] > 0) {
                $totalBalance += $member['balance'];
            }
            $sub_parish_code = strtoupper(substr($member['sub_parish_name'], 0, 3));
            $community_display = ucwords(strtolower($member['community_name'])) . ' - ' . $sub_parish_code;

            $html .= '<tr>
                <td style="text-align:center">' . $counter . '</td>
                <td>' . ucwords(strtolower($member['name'])) . '</td>
                <td>' . $community_display . '</td>
                <td style="text-align: right;">' . number_format($member['target'], 0) . '</td>
                <td style="text-align: right;color:blue;">' . number_format($member['amount_contributed_on_date'], 0) . '</td>
                <td style="text-align: right;">' . number_format($member['contribution'], 0) . '</td>
                <td style="text-align: right; color: ' . ($member['balance'] < 0 ? 'green' : 'inherit') . ';">' . 
                    ($member['balance'] < 0 ? "+" : "") . number_format(abs($member['balance']), 0) . 
                '</td>
                <td style="text-align: right;">' .$contribution_time .'</td>
               <td>' . implode(', ', array_map('ucfirst', array_filter(array_map('trim', $member['payment_methods'])))) . '</td>
            </tr>';
        
            $counter++; $clerkAttendedCount++; $overallAttendedCount++;
        }

        $clerkGrandTotal += $totalContributedOnDate;
        $overallGrandTotal += $totalContributedOnDate;

        $html .= '<tr style="font-weight: bold;">
            <td colspan="3" style="text-align: center;">Jumla Kuu</td>
            <td style="text-align: right;">' . number_format($totalTarget, 0) . '</td>
            <td style="text-align: right;color:blue;">' . number_format($totalContributedOnDate, 0) . '</td>
            <td style="text-align: right;">' . number_format($totalContributedUpToDate, 0) . '</td>
            <td style="text-align: right; color: ' . ($totalBalance < 0 ? 'green' : 'inherit') . ';">' . 
                ($totalBalance < 0 ? "+" : "") . number_format(abs($totalBalance), 0) . 
            '</td>
            <td style="text-align: center;"> - </td>
            <td style="text-align: center;"> - </td>
        </tr>
        <tr style="font-weight: bold;">
            <td colspan="9" style="text-align: center;background-color: #f0f0f0;"> Muhtasari wa Njia za Malipo kwa Awamu ya ' . $phase . '</td>
        </tr>
        <tr>
            <td colspan="6" style="text-align:center;font-weight:bold;">Njia ya Malipo</td>
            <td colspan="3" style="text-align:center;font-weight:bold;">Kiasi</td>
        </tr>
        ';

        foreach ($groupPaymentMethodTotals as $method => $amount) {
            $html .= '<tr>
                <td colspan="6">' . htmlspecialchars($method) . '</td>
                <td colspan="3" style="text-align: right; color: blue;">' . number_format($amount, 0) . '</td>
            </tr>';
        }
        
        $html .= '</tbody>
    </table>';
    } // end phases loop for clerk

    // After listing all phases for this clerk, show per-clerk payment method summary
    $html .= '
    <table border="1" cellpadding="5" cellspacing="0" style="margin-top: 12px;">
        <thead>
            <tr>
                <th colspan="2" style="text-align: center; background-color: #f0f0f0;">
                    Muhtasari wa Kiasi kwa Kila Njia ya Malipo - ' . htmlspecialchars($clerk_info['full_name']) . '
                </th>
            </tr>
            <tr>
                <th style="width: 70%;">Njia ya Malipo</th>
                <th style="width: 30%;">Kiasi</th>
            </tr>
        </thead>
        <tbody>';

    foreach ($clerkPaymentMethodTotals as $method => $amount) {
        $html .= '<tr>
            <td>' . htmlspecialchars($method) . '</td>
            <td style="text-align: right; color: blue;">' . number_format($amount, 0) . '</td>
        </tr>';
    }

    $html .= '</tbody></table>';

    // Per-clerk summary line
    $html .= '
    <div class="table-container" style="margin-top:10px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tbody>
                <tr>
                    <td style="padding: 8px;"><strong>Idadi ya Washarika ('.$clerk_info['full_name'].'):</strong> ' . $clerkAttendedCount . '</td>
                    <td style="padding: 8px;text-align:right;"><strong>Jumla Kuu Taslimu ('.$clerk_info['full_name'].'):</strong> ' . number_format($clerkGrandTotal, 0) . '</td>
                </tr>
            </tbody>
        </table>
    </div>';
} // end clerks loop

// After processing all clerks, append overall payment method summary and overall totals
$html .= '
<table border="1" cellpadding="5" cellspacing="0" style="margin-top: 20px;">
    <thead>
        <tr>
            <th colspan="2" style="text-align: center; background-color: #f0f0f0;">
                Muhtasari wa Kiasi kwa Kila Njia ya Malipo kwa Awamu Zote
            </th>
        </tr>
        <tr>
            <th style="width: 70%;">Njia ya Malipo</th>
            <th style="width: 30%;">Kiasi</th>
        </tr>
    </thead>
    <tbody>';
        
foreach ($overallPaymentMethodTotals as $method => $amount) {
    $html .= '<tr>
        <td>' . htmlspecialchars($method) . '</td>
        <td style="text-align: right; color: blue;">' . number_format($amount, 0) . '</td>
    </tr>';
}

$html .= '</tbody></table>';

date_default_timezone_set('Africa/Nairobi');
$printedOn = date('d M Y, H:i');

$html .='
    <div class="table-container" style="margin-top:14px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tbody>
                <tr>
                    <td style="padding: 8px;"><strong>Idadi ya Washarika (Jumla):</strong> ' . $overallAttendedCount . '</td>
                    <td style="padding: 8px;text-align:right;"><strong>Jumla Kuu Taslimu (Jumla):</strong> ' . number_format($overallGrandTotal, 0) . '</td>
                </tr>
            </tbody>
        </table>
    </div>';  

$html .= '</div>

<div class="footer">
    <p>Kanisa Langu - SEWMR Technologies | Printed on ' . $printedOn . '</p>
</div>
</body>
</html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'potrait');
$dompdf->render();

$contribution_date_formatted = date('d_m_Y', strtotime($contribution_date));
$filename = "Recorded Harambee Contributions on {$contribution_date_formatted}.pdf";
$dompdf->stream($filename, array("Attachment" => false));
?>
