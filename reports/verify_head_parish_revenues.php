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

// Initialize variables from GET parameters
$target = isset($_GET['target']) ? $_GET['target'] : null;
$revenue_date = isset($_GET['revenue_date']) ? $_GET['revenue_date'] : null;
$sub_parish_id = isset($_GET['sub_parish_id']) ? $_GET['sub_parish_id'] : null;
$community_id = isset($_GET['community_id']) ? $_GET['community_id'] : null;
$group_id = isset($_GET['group_id']) ? $_GET['group_id'] : null;

// Validate revenue date
if (empty($revenue_date)) {
    header("Location: /error.php?message=" . urlencode("Revenue date is required."));
    exit();
}

// Process each ID
$sub_parish_id = processId($sub_parish_id, "Sub Parish");
$community_id = processId($community_id, "Community");
$group_id = processId($group_id, "Group");

// Call validation function
validateTarget($target, $sub_parish_id, $community_id, $group_id);

if (!isset($_SESSION['head_parish_id'])) {
    header("Location: /error.php?message=" . urlencode("Unauthorized"));
    exit();
}

$head_parish_id = $_SESSION['head_parish_id'];
$parish_info = getParishInfo($conn, $head_parish_id);

// Determine the target table based on the 'target' parameter
switch ($target) {
    case 'head-parish':
        $revenue_table = 'head_parish_revenues';
        $other_head_parish_revenues = 'other_head_parish_revenues';
        break;
    case 'sub-parish':
        $revenue_table = 'sub_parish_revenues';
        break;
    case 'community':
        $revenue_table = 'community_revenues';
        break;
    case 'groups':
        $revenue_table = 'group_revenues';
        break;
    default:
        echo json_encode(["success" => false, "message" => "Invalid revenue target type provided"]);
        exit();
}

// Convert revenue_date to DateTime object
$revenueDateObj = new DateTime($revenue_date);

// Get Monday of the current week
$monday = clone $revenueDateObj;
$monday->modify('Monday this week');

// Get Sunday of the current week
$sunday = clone $monday;
$sunday->modify('Sunday this week');

// Format the date range
$mondayFormatted = $monday->format('Y-m-d');
$sundayFormatted = $sunday->format('Y-m-d');

/**
 * 1) REVENUES BY SERVICE (combined head_parish_revenues + other_head_parish_revenues)
 */
$query_service = "SELECT service_number, SUM(revenue_amount) AS total_revenue
                  FROM (
                      SELECT service_number, revenue_amount
                      FROM head_parish_revenues
                      WHERE head_parish_id = ? AND revenue_date BETWEEN ? AND ?

                      UNION ALL

                      SELECT service_number, revenue_amount
                      FROM other_head_parish_revenues
                      WHERE head_parish_id = ? AND revenue_date BETWEEN ? AND ?
                  ) AS combined_revenues
                  GROUP BY service_number";
$stmt = $conn->prepare($query_service);
$stmt->bind_param("ssssss", $head_parish_id, $mondayFormatted, $sundayFormatted, $head_parish_id, $mondayFormatted, $sundayFormatted);
$stmt->execute();
$result_service = $stmt->get_result();

/**
 * 2) REVENUES BY SUB PARISH (head_parish_revenues only)
 */
$query_sub_parish = "SELECT hr.sub_parish_id, sp.sub_parish_name, SUM(hr.revenue_amount) AS total_revenue
                     FROM head_parish_revenues hr
                     JOIN sub_parishes sp ON hr.sub_parish_id = sp.sub_parish_id
                     WHERE hr.head_parish_id = ? AND hr.revenue_date BETWEEN ? AND ?
                     GROUP BY hr.sub_parish_id, sp.sub_parish_name";
$stmt = $conn->prepare($query_sub_parish);
$stmt->bind_param("sss", $head_parish_id, $mondayFormatted, $sundayFormatted);
$stmt->execute();
$result_sub_parish = $stmt->get_result();

/**
 * 3) REVENUES BY SOURCE (combined head_parish_revenues + other_head_parish_revenues)
 */
$query_revenue_stream = "SELECT revenue_stream_id, revenue_stream_name, SUM(revenue_amount) AS total_revenue
                         FROM (
                             SELECT hr.revenue_stream_id, hpr.revenue_stream_name, hr.revenue_amount
                             FROM head_parish_revenues hr
                             JOIN head_parish_revenue_streams hpr ON hr.revenue_stream_id = hpr.revenue_stream_id
                             WHERE hr.head_parish_id = ? AND hr.revenue_date BETWEEN ? AND ?

                             UNION ALL

                             SELECT ohr.revenue_stream_id, hpr.revenue_stream_name, ohr.revenue_amount
                             FROM other_head_parish_revenues ohr
                             JOIN head_parish_revenue_streams hpr ON ohr.revenue_stream_id = hpr.revenue_stream_id
                             WHERE ohr.head_parish_id = ? AND ohr.revenue_date BETWEEN ? AND ?
                         ) AS combined_revenue_streams
                         GROUP BY revenue_stream_id, revenue_stream_name
                         ORDER BY total_revenue DESC";
$stmt = $conn->prepare($query_revenue_stream);
$stmt->bind_param("ssssss", $head_parish_id, $mondayFormatted, $sundayFormatted, $head_parish_id, $mondayFormatted, $sundayFormatted);
$stmt->execute();
$result_revenue_stream = $stmt->get_result();

/**
 * 4) REVENUES BY BANK ACCOUNTS (combined across all tables in the week)
 * NOTE: We will FETCH into an array so we can print it at the END in the new final section.
 */
$query_bank_account_revenue = "
    SELECT hpb.account_name, SUM(combined_revenues.revenue_amount) AS total_revenue
    FROM head_parish_bank_accounts hpb
    LEFT JOIN (
        SELECT hr.revenue_amount, hpr.account_id
        FROM head_parish_revenues hr
        JOIN head_parish_revenue_streams hpr ON hr.revenue_stream_id = hpr.revenue_stream_id
        WHERE hr.revenue_date BETWEEN ? AND ? AND hr.head_parish_id = ?

        UNION ALL

        SELECT ohr.revenue_amount, hpr.account_id
        FROM other_head_parish_revenues ohr
        JOIN head_parish_revenue_streams hpr ON ohr.revenue_stream_id = hpr.revenue_stream_id
        WHERE ohr.revenue_date BETWEEN ? AND ? AND ohr.head_parish_id = ?

        UNION ALL

        SELECT sr.revenue_amount, hpr.account_id
        FROM sub_parish_revenues sr
        JOIN head_parish_revenue_streams hpr ON sr.revenue_stream_id = hpr.revenue_stream_id
        WHERE sr.revenue_date BETWEEN ? AND ? AND sr.head_parish_id = ?

        UNION ALL

        SELECT cr.revenue_amount, hpr.account_id
        FROM community_revenues cr
        JOIN head_parish_revenue_streams hpr ON cr.revenue_stream_id = hpr.revenue_stream_id
        WHERE cr.revenue_date BETWEEN ? AND ? AND cr.head_parish_id = ?

        UNION ALL

        SELECT gr.revenue_amount, hpr.account_id
        FROM group_revenues gr
        JOIN head_parish_revenue_streams hpr ON gr.revenue_stream_id = hpr.revenue_stream_id
        WHERE gr.revenue_date BETWEEN ? AND ? AND gr.head_parish_id = ?
    ) AS combined_revenues ON hpb.account_id = combined_revenues.account_id
    GROUP BY hpb.account_id, hpb.account_name
    ORDER BY hpb.account_name ASC
";
$stmt = $conn->prepare($query_bank_account_revenue);
$stmt->bind_param(
    "sssssssssssssss",
    $mondayFormatted, $sundayFormatted, $head_parish_id,
    $mondayFormatted, $sundayFormatted, $head_parish_id,
    $mondayFormatted, $sundayFormatted, $head_parish_id,
    $mondayFormatted, $sundayFormatted, $head_parish_id,
    $mondayFormatted, $sundayFormatted, $head_parish_id
);
$stmt->execute();
$result_bank_account_revenue = $stmt->get_result();

$accountRows = [];
$grand_total_revenue = 0;
while ($row = $result_bank_account_revenue->fetch_assoc()) {
    $amt = (float)$row['total_revenue'];
    $grand_total_revenue += $amt;
    $accountRows[] = [
        'account_name' => $row['account_name'],
        'total_revenue' => $amt
    ];
}

/**
 * 5) NEW: REVENUES COLLECTIONS FROM MONDAY TO SUNDAY (daily totals + running total)
 * Combined across all tables in the week (same scope as accounts section).
 */
$query_daily_collections = "
    SELECT revenue_date, SUM(revenue_amount) AS total_revenue
    FROM (
        SELECT revenue_date, revenue_amount
        FROM head_parish_revenues
        WHERE revenue_date BETWEEN ? AND ? AND head_parish_id = ?

        UNION ALL

        SELECT revenue_date, revenue_amount
        FROM other_head_parish_revenues
        WHERE revenue_date BETWEEN ? AND ? AND head_parish_id = ?

        UNION ALL

        SELECT revenue_date, revenue_amount
        FROM sub_parish_revenues
        WHERE revenue_date BETWEEN ? AND ? AND head_parish_id = ?

        UNION ALL

        SELECT revenue_date, revenue_amount
        FROM community_revenues
        WHERE revenue_date BETWEEN ? AND ? AND head_parish_id = ?

        UNION ALL

        SELECT revenue_date, revenue_amount
        FROM group_revenues
        WHERE revenue_date BETWEEN ? AND ? AND head_parish_id = ?
    ) t
    GROUP BY revenue_date
    ORDER BY revenue_date ASC
";
$stmt = $conn->prepare($query_daily_collections);
$stmt->bind_param(
    "sssssssssssssss",
    $mondayFormatted, $sundayFormatted, $head_parish_id,
    $mondayFormatted, $sundayFormatted, $head_parish_id,
    $mondayFormatted, $sundayFormatted, $head_parish_id,
    $mondayFormatted, $sundayFormatted, $head_parish_id,
    $mondayFormatted, $sundayFormatted, $head_parish_id
);
$stmt->execute();
$result_daily = $stmt->get_result();

$dailyMap = []; // key: Y-m-d, value: total_revenue
while ($row = $result_daily->fetch_assoc()) {
    $dailyMap[$row['revenue_date']] = (float)$row['total_revenue'];
}

// ---------- HTML / PDF BUILD ----------
$total_revenue_services = 0;

$htm = '<html><head>
            <title>'.$parish_info['head_parish_name'].' REVENUE VERIFICATION REPORT</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 0;
                }

                .header {
                    text-align: center;
                    margin-bottom: 20px;
                }

                .logo {
                    width: 60px;
                    height: auto;
                }

                .date {
                    color: #4187f8;
                }

                h1 {
                    font-size: 14px;
                    color: #333;
                    margin-bottom: 5px;
                    font-weight: normal;
                }

                .table-container {
                    width: 100%;
                    margin: 20px 0;
                }

                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                }

                th, td {
                    border: 1px solid #000;
                    padding: 8px;
                }

                th {
                    font-weight: normal;
                    font-size:12px;
                }

                td {
                    font-size: 12px;
                    color: #333;
                }

                .footer {
                    text-align: center;
                    margin-top: 40px;
                    font-size: 12px;
                    color: #888;
                }

                .text-center { text-align: center; }
                .text-right { text-align: right; }
                .text-left { text-align: left; }

                .full-row{
                    background-color: #156494;
                    color:white;
                    font-weight:bold;
                }
            </style>
        </head><body>';

$htm .= '
    <div class="header">
        <img class="logo" src="' . $src . '"/>
        <h1 style="color: #3498db;">K.K.K.T ' . $parish_info['diocese_name'] . '</h1>
        <h1>' . $parish_info['province_name'] . ' | ' . $parish_info['head_parish_name'] . '</h1>
        <h1>REVENUES VERIFICATION REPORT FROM
            <span class="date">' . htmlspecialchars(date("d M Y", strtotime($mondayFormatted))) . '</span>
            TO
            <span class="date">' . htmlspecialchars(date("d M Y", strtotime($sundayFormatted))) . '</span>
        </h1>
    </div>';

/**
 * TABLE: REVENUES BY SERVICE
 */
$htm .= '<div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th colspan="3" class="full-row">REVENUES BY SERVICE</th>
                    </tr>
                    <tr>
                        <th style="width:7%">#</th>
                        <th>SERVICE</th>
                        <th>COLLECTED AMOUNT</th>
                    </tr>
                </thead>
                <tbody>';

$index = 1;
while ($row = $result_service->fetch_assoc()) {
    $total_revenue_services += (float)$row['total_revenue'];

    $htm .= '<tr>
                <td class="text-center">' . $index++ . '</td>
                <td class="text-left">' . strtoupper(getOrdinal($row['service_number'])) . '</td>
                <td class="text-center">' . number_format($row['total_revenue'], 0) . '</td>
            </tr>';
}

$htm .= '</tbody></table></div>';

/**
 * TABLE: REVENUES BY SUB PARISH
 */
$htm .= '<div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th colspan="3" class="full-row">REVENUES BY SUB PARISH</th>
                    </tr>
                    <tr>
                        <th style="width:7%">#</th>
                        <th style="width:45%">SUB PARISH NAME</th>
                        <th>COLLECTED AMOUNT</th>
                    </tr>
                </thead>
                <tbody>';

$index = 1;
while ($row = $result_sub_parish->fetch_assoc()) {
    $htm .= '<tr>
                <td class="text-center">' . $index++ . '</td>
                <td>' . $row['sub_parish_name'] . '</td>
                <td class="text-center">' . number_format($row['total_revenue'], 0) . '</td>
            </tr>';
}

$htm .= '</tbody></table></div>';

/**
 * TABLE: REVENUES BY SOURCE
 */
$htm .= '<div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th colspan="4" class="full-row">REVENUES BY SOURCE</th>
                    </tr>
                    <tr>
                        <th style="width:7%">#</th>
                        <th style="width:45%">REVENUE SOURCE</th>
                        <th>COLLECTED AMOUNT</th>
                        <th>PERCENTAGE (%)</th>
                    </tr>
                </thead>
                <tbody>';

$index = 1;
while ($row = $result_revenue_stream->fetch_assoc()) {
    $denom = ($total_revenue_services > 0) ? $total_revenue_services : 1;
    $percentage = ((float)$row['total_revenue'] / $denom) * 100;

    $htm .= '<tr>
                <td class="text-center">' . $index++ . '</td>
                <td>' . $row['revenue_stream_name'] . '</td>
                <td class="text-center">' . number_format($row['total_revenue'], 0) . '</td>
                <td class="text-center">' . number_format($percentage, 2) . '%</td>
            </tr>';
}

$htm .= '</tbody></table></div>';

/**
 * NEW FINAL SECTION:
 * - REVENUES COLLECTIONS FROM: MONDAY TO SUNDAY (daily totals + running total)
 * - REVENUES BY ACCOUNTS
 */
$htm .= '<div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th colspan="5" class="full-row">REVENUES COLLECTIONS FROM: MONDAY TO SUNDAY</th>
                    </tr>
                    <tr>
                        <th style="width:7%">No.</th>
                        <th style="width:23%">Day</th>
                        <th style="width:20%">Date</th>
                        <th style="width:25%">Amount</th>
                        <th style="width:25%">Total</th>
                    </tr>
                </thead>
                <tbody>';

$runningTotal = 0;
$no = 1;

// Build exactly 7 rows: Monday -> Sunday (even if amount is 0)
$periodStart = new DateTime($mondayFormatted);
for ($i = 0; $i < 7; $i++) {
    $d = clone $periodStart;
    $d->modify("+{$i} day");
    $key = $d->format('Y-m-d');

    $amount = isset($dailyMap[$key]) ? (float)$dailyMap[$key] : 0;
    $runningTotal += $amount;

    $htm .= '<tr>
                <td class="text-center">' . $no++ . '</td>
                <td class="text-left">' . $d->format('l') . '</td>
                <td class="text-center">' . $d->format('d/m/Y') . '</td>
                <td class="text-center">' . number_format($amount, 0) . '</td>
                <td class="text-center">' . number_format($runningTotal, 0) . '</td>
            </tr>';
}

$htm .= '</tbody></table></div>';

/**
 * FINAL: REVENUES BY ACCOUNTS (printed at the end, as requested)
 */
$htm .= '<div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th colspan="3" class="full-row">REVENUES BY ACCOUNTS</th>
                    </tr>
                    <tr>
                        <th style="width:7%">#</th>
                        <th style="width:45%">ACCOUNT NAME</th>
                        <th>COLLECTED AMOUNT</th>
                    </tr>
                </thead>
                <tbody>';

$index = 1;
foreach ($accountRows as $r) {
    $htm .= '<tr>
                <td class="text-center">' . $index++ . '</td>
                <td>' . htmlspecialchars($r['account_name']) . '</td>
                <td class="text-center">' . number_format($r['total_revenue'], 0) . '</td>
            </tr>';
}

$htm .= '</tbody></table></div>';

/**
 * TOTAL REVENUE (uses the accounts scope = grand total across all included tables)
 */
$htm .= '<div class="table-container">
            <table style="width:100%; border-collapse: collapse; margin-top: 20px;border:none;">
                <thead>
                    <tr style="background-color:#38557c;">
                        <th style="text-align:left; font-size:16px; color:#fff; padding:10px;border:none;">TOTAL REVENUE COLLECTED</th>
                        <th style="text-align:right; font-size:18px; color:#fff; padding:10px;border:none;">TZS ' . number_format($grand_total_revenue, 0) . '</th>
                    </tr>
                </thead>
            </table>
        </div>';

$htm .= '<div class="footer">
            <p>Printed on ' . $timestamp . ' | Kanisa Langu - SEWMR Technologies</p>
        </div>';

$htm .= '</body></html>';

$dompdf->loadHtml($htm);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream($parish_info['head_parish_name'] . " revenues_verification_report.pdf", ["Attachment" => false]);
?>
