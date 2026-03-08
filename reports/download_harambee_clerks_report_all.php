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

/* get admins (recorded_by) - only SQL we run */
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

/* Collect data per clerk and collect unique payment methods from member-level data (no SQL for payment methods) */
$allPaymentMethods = []; // associative set
$clerksData = []; // keyed by clerk_id
$grandTotals = [
    'method_count' => [],   // method => total members (count)
    'method_amount' => [],  // method => total amount
    'overall_count' => 0,   // total UNIQUE members counted (fixed)
    'overall_amount' => 0
];

foreach ($clerks as $clerk_id_raw) {
    $clerk_id = (int)$clerk_id_raw;
    if ($clerk_id <= 0) continue;

    $clerk_info = getHeadParishAdminDetailsById($conn, $head_parish_id, $clerk_id);
    if (!$clerk_info) continue;

    $clerkRow = [
        'clerk_id' => $clerk_id,
        'full_name' => $clerk_info['full_name'],
        'phone' => $clerk_info['phone'],
        'methods' => [], // method => ['count' => int, 'amount' => float]
        'total_count' => 0, // FIXED: unique members only
        'total_amount' => 0.0
    ];

    $member_ids = getMemberIdsRecordedByAdmin($conn, $harambee_id, $contribution_date, $target, $clerk_id);

    // FIX: Track unique members per clerk (so they count once no matter how many payment methods)
    $uniqueMembersThisClerk = [];

    // iterate members exactly as original logic (use provided helper functions)
    foreach ($member_ids as $member_id) {
        $member = getSingleMemberHarambeeDetails($conn, $member_id, $harambee_id, $target);

        if ($member === false || empty($member)) {
            continue;
        }

        // preserve group-name dedup logic from original
        static $processed_groups_per_clerk;
        if (!isset($processed_groups_per_clerk)) $processed_groups_per_clerk = [];
        if (!isset($processed_groups_per_clerk[$clerk_id])) $processed_groups_per_clerk[$clerk_id] = [];

        if ($member['group_name'] != null) {
            if (in_array($member['group_name'], $processed_groups_per_clerk[$clerk_id])) {
                continue;
            }
            $processed_groups_per_clerk[$clerk_id][] = $member['group_name'];
        }

        $contribution_result = getTotalContributionsBetweenDates($conn, $harambee_id, $member_id, $contribution_date, $contribution_date, $target_table, $clerk_id);
        if ($contribution_result === false) {
            continue;
        }

        // FIX: Count member ONCE for this clerk regardless of payment methods
        if (!isset($uniqueMembersThisClerk[$member_id])) {
            $uniqueMembersThisClerk[$member_id] = true;

            $clerkRow['total_count'] += 1;
            $grandTotals['overall_count'] += 1;
        }

        $amount_on_date = (float)($contribution_result['on_date_contributions'] ?? 0);
        $payment_methods_raw = $contribution_result['payment_methods'] ?? [];

        // normalize payment methods into array of trimmed human-friendly strings
        $methods = [];
        if (is_array($payment_methods_raw)) {
            foreach ($payment_methods_raw as $m) {
                $m = trim($m);
                if ($m === '' ) continue;
                $methods[] = $m;
            }
        } else {
            // assuming CSV string
            $tmp = trim((string)$payment_methods_raw);
            if ($tmp !== '') {
                $parts = array_map('trim', explode(',', $tmp));
                foreach ($parts as $m) {
                    if ($m === '') continue;
                    $methods[] = $m;
                }
            }
        }

        // if no methods recorded, classify as "Unknown"
        if (empty($methods)) {
            $methods = ['Unknown'];
        }

        // update clerkRow and global sets
        foreach ($methods as $method) {
            $method_key = $method; // keep original label
            $allPaymentMethods[$method_key] = true;

            if (!isset($clerkRow['methods'][$method_key])) {
                $clerkRow['methods'][$method_key] = ['count' => 0, 'amount' => 0.0];
            }
            // count member once for this method and add amount_on_date to this method
            $clerkRow['methods'][$method_key]['count'] += 1;
            $clerkRow['methods'][$method_key]['amount'] += $amount_on_date;

            // FIX: Removed counting here (member counting is now unique per member)
            $clerkRow['total_amount'] += $amount_on_date;

            if (!isset($grandTotals['method_count'][$method_key])) $grandTotals['method_count'][$method_key] = 0;
            if (!isset($grandTotals['method_amount'][$method_key])) $grandTotals['method_amount'][$method_key] = 0.0;

            $grandTotals['method_count'][$method_key] += 1;
            $grandTotals['method_amount'][$method_key] += $amount_on_date;

            $grandTotals['overall_amount'] += $amount_on_date;
        }
    }

    $clerksData[] = $clerkRow;
}

/* prepare ordered list of payment methods */
$paymentMethods = array_keys($allPaymentMethods);
sort($paymentMethods, SORT_NATURAL | SORT_FLAG_CASE);

/* build HTML */
$html = '
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Harambee Recording Statistics</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:Helvetica, Arial, sans-serif; margin:20px; color:#222;}
.container{width:95%; margin:0 auto;}
.header{text-align:center; margin-bottom:12px;}
.header h1{font-size:14px; color:#3498db; margin-bottom:4px;}
.header h2{font-size:12px; color:#2c3e50; margin-bottom:0;}
table{width:100%; border-collapse:collapse; margin-top:10px;}
th,td{border:1px solid #000; padding:6px; font-size:10px;}
th{background:#f8f8f8; font-weight:700; text-align:center;}
.text-right{text-align:right;}
.text-center{text-align:center;}
.footer{text-align:center; font-size:11px; color:#666; margin-top:14px;}
.method-top{background:#eaeaea; font-weight:700;}
</style>
</head>
<body>
<div class="container">
<div class="header">
<h1>K.K.K.T ' . htmlspecialchars($parish_info['diocese_name']) . '</h1>
<h2>' . htmlspecialchars($parish_info['province_name'] . ' | ' . $parish_info['head_parish_name']) . '</h2>
<p style="margin-top:8px; font-size:11px; font-weight:bold;">ORODHA YA MAKARANI NA KIASI CHA MICHANGO YA HARAMBEE WALICHOPOKEA TAREHE ' . date('d/m/Y', strtotime($contribution_date)) . '</p>
</div>

<table>
<thead>
<tr>
<th rowspan="2">#</th>
<th rowspan="2">Jina la Karani</th>';

foreach ($paymentMethods as $method) {
    $html .= '<th class="method-top" colspan="2">' . htmlspecialchars($method) . '</th>';
}

$html .= '<th rowspan="2">Jumla Idadi</th><th rowspan="2">Jumla Kiasi</th>
</tr>
<tr>';

foreach ($paymentMethods as $method) {
    $html .= '<th>Idadi</th><th>Kiasi</th>';
}

$html .= '</tr>
</thead>
<tbody>';

/* rows per clerk */
$rowNo = 1;
foreach ($clerksData as $clerkRow) {
    $html .= '<tr>';
    $html .= '<td class="text-center">' . $rowNo . '</td>';
    $html .= '<td>' . htmlspecialchars($clerkRow['full_name']) . '</td>';

    foreach ($paymentMethods as $method) {
        $count = $clerkRow['methods'][$method]['count'] ?? 0;
        $amount = $clerkRow['methods'][$method]['amount'] ?? 0.0;
        $html .= '<td class="text-center">' . intval($count) . '</td>';
        $html .= '<td class="text-right">' . number_format($amount, 0) . '</td>';
    }

    $html .= '<td class="text-center" style="font-weight:700;">' . intval($clerkRow['total_count']) . '</td>';
    $html .= '<td class="text-right" style="font-weight:700;">' . number_format($clerkRow['total_amount'], 0) . '</td>';
    $html .= '</tr>';
    $rowNo++;
}

/* grand total row */
$html .= '<tr style="background:#f0f0f0; font-weight:700;">
<td colspan="2" class="text-center">Jumla Kuu</td>';

foreach ($paymentMethods as $method) {
    $mCount = $grandTotals['method_count'][$method] ?? 0;
    $mAmount = $grandTotals['method_amount'][$method] ?? 0.0;
    $html .= '<td class="text-center">' . intval($mCount) . '</td>';
    $html .= '<td class="text-right">' . number_format($mAmount, 0) . '</td>';
}

$html .= '<td class="text-center">' . intval($grandTotals['overall_count']) . '</td>';
$html .= '<td class="text-right">' . number_format($grandTotals['overall_amount'], 0) . '</td>';
$html .= '</tr>';

$html .= '</tbody></table>';

date_default_timezone_set('Africa/Nairobi');
$printedOn = date('d M Y, H:i');

$html .= '<div class="footer">Kanisa Langu - SEWMR Technologies | Printed on ' . $printedOn . '</div>';
$html .= '</div></body></html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$contribution_date_formatted = date('d_m_Y', strtotime($contribution_date));
$filename = "Recorded_Harambee_Contributions_{$contribution_date_formatted}.pdf";
$dompdf->stream($filename, array("Attachment" => false));
?>
