<?php 
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/libraries/vendor/autoload.php');

use Dompdf\Dompdf;

// Instantiate Dompdf
$dompdf = new Dompdf();
$options = $dompdf->getOptions();
$options->setFontCache('fonts');
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);
$options->setChroot([$_SERVER['DOCUMENT_ROOT'] . '/assets/fonts/']);
$dompdf->setOptions($options);

$imageData = base64_encode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/assets/images/logos/kkkt-logo.jpg'));
$src = 'data:image/jpeg;base64,' . $imageData;

// Check if the user is authenticated
if (!isset($_SESSION['head_parish_id'])) {
    header("Location: /error.php?message=" . urlencode("Unauthorized"));
    exit();
}

$head_parish_id = $_SESSION['head_parish_id'];

// Ensure 'year' is provided in the request
if (!isset($_GET['year']) || empty($_GET['year'])) {
    header("Location: /error.php?message=" . urlencode("Year is required."));
    exit();
}

$reportType = $_GET['report_type'] ?? null;

if (!in_array($reportType, ['annual', 'quarterly', 'custom'])) {
    header("Location: /error.php?message=" . urlencode("Invalid report type."));
    exit();
}

// YEAR — always required except custom (but still helpful)
$year = filter_var($_GET['year'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 2024, 'max_range' => date('Y')]
]);

if (!$year) {
    header("Location: /error.php?message=" . urlencode("Invalid year."));
    exit();
}

$today = date('Y-m-d');

// Prepare variables
$startDate = null;
$endDate = null;

switch ($reportType) {

    case 'annual':
        // Always full-year range
        $startDate = "$year-01-01";
        $endDate   = "$year-12-31";
        break;

    case 'quarterly':
        $quarter = $_GET['quarter'] ?? null;

        if (!in_array($quarter, ['Q1', 'Q2', 'Q3', 'Q4'])) {
            header("Location: /error.php?message=" . urlencode("Invalid quarter."));
            exit();
        }

        // Quarter ranges
        $ranges = [
            'Q1' => ['01-01', '03-31'],
            'Q2' => ['04-01', '06-30'],
            'Q3' => ['07-01', '09-30'],
            'Q4' => ['10-01', '12-31']
        ];

        $startDate = $year . '-' . $ranges[$quarter][0];
        $endDate   = $year . '-' . $ranges[$quarter][1];

        break;

    case 'custom':
        // Date inputs
        $startDate = $_GET['start_date'] ?? null;
        $endDate   = $_GET['end_date'] ?? null;

        // Basic format test
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) ||
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {

            header("Location: /error.php?message=" . urlencode("Invalid date format."));
            exit();
        }

        // Same year constraint
        $yearStart = substr($startDate, 0, 4);
        $yearEnd   = substr($endDate, 0, 4);

        if ($yearStart !== $yearEnd) {
            header("Location: /error.php?message=" . urlencode("Start and end dates must be in the same year."));
            exit();
        }

        // Override $year to match custom range
        $year = (int)$yearStart;

        // Validate against allowed year limits
        if ($year < 2024 || $year > date('Y')) {
            header("Location: /error.php?message=" . urlencode("Year out of allowed range."));
            exit();
        }

        // Chronology
        if ($startDate > $endDate) {
            header("Location: /error.php?message=" . urlencode("End date cannot be earlier than start date."));
            exit();
        }

        // No future dates
        if ($startDate > $today || $endDate > $today) {
            header("Location: /error.php?message=" . urlencode("Dates cannot exceed today's date."));
            exit();
        }

        break;
}


// Define constants for the current and previous year
define('CURRENT_YEAR', $year);
define('PREVIOUS_YEAR', $year - 1);

// Define quarterly date ranges as constants
define('Q1_START', '01-01');
define('Q1_END', '03-31');
define('Q2_START', '04-01');
define('Q2_END', '06-30');
define('Q3_START', '07-01');
define('Q3_END', '09-30');
define('Q4_START', '10-01');
define('Q4_END', '12-31');

// Define quarterly percentage completion constants
define('Q1_PERCENTAGE', 25);
define('Q2_PERCENTAGE', 50);
define('Q3_PERCENTAGE', 75);
define('Q4_PERCENTAGE', 100);

// Check if Quarter is set
$quarter = $_GET['quarter'] ?? null;
$valid_quarters = ['Q1', 'Q2', 'Q3', 'Q4'];

if ($quarter && !in_array($quarter, $valid_quarters)) {
    header("Location: /error.php?message=" . urlencode("Invalid quarter selection."));
    exit();
}

// Define quarter start and end dates
$quarter_dates = [
    'Q1' => ['start' => Q1_START, 'end' => Q1_END, 'percentage' => Q1_PERCENTAGE],
    'Q2' => ['start' => Q2_START, 'end' => Q2_END, 'percentage' => Q2_PERCENTAGE],
    'Q3' => ['start' => Q3_START, 'end' => Q3_END, 'percentage' => Q3_PERCENTAGE],
    'Q4' => ['start' => Q4_START, 'end' => Q4_END, 'percentage' => Q4_PERCENTAGE],
];

// If quarter is selected, determine date ranges and percentage
if ($quarter) {
    define('CURRENT_YEAR_QUARTER_START', CURRENT_YEAR . '-' . $quarter_dates[$quarter]['start']);
    define('CURRENT_YEAR_QUARTER_END', CURRENT_YEAR . '-' . $quarter_dates[$quarter]['end']);
    
    define('PREVIOUS_YEAR_QUARTER_START', PREVIOUS_YEAR . '-' . $quarter_dates[$quarter]['start']);
    define('PREVIOUS_YEAR_QUARTER_END', PREVIOUS_YEAR . '-' . $quarter_dates[$quarter]['end']);

    define('CURRENT_QUARTER_PERCENTAGE', $quarter_dates[$quarter]['percentage']);
}

// Current period constants
define('CURRENT_PERIOD_START', $startDate);
define('CURRENT_PERIOD_END', $endDate);

// Previous period constants: shift by one year
define('PREVIOUS_PERIOD_START', (new DateTime($startDate))->modify('-1 year')->format('Y-m-d'));
define('PREVIOUS_PERIOD_END', (new DateTime($endDate))->modify('-1 year')->format('Y-m-d'));


// Mapping of quarters to words
$quarterNames = [
    'Q1' => 'Kwanza',
    'Q2' => 'Pili',
    'Q3' => 'Tatu',
    'Q4' => 'Nne'
];

// Determine the quarter word dynamically
if ($reportType === 'quarterly' && isset($quarterNames[$quarter])) {
    $quarterWord = $quarterNames[$quarter];
    $periodLabel = "ROBO YA $quarterWord KWA MWAKA $year";
} elseif ($reportType === 'annual') {
    $periodLabel = "MWAKA $year KUANZIA";
} else { // custom
    $periodLabel = "MWAKA $year KUANZIA";
}

// Convert date strings to the desired format
$displayStartDate = DateTime::createFromFormat('Y-m-d', $startDate)->format('M d Y');
$displayEndDate   = DateTime::createFromFormat('Y-m-d', $endDate)->format('M d Y');


// Fetch parish info
$parish_info = getParishInfo($conn, $head_parish_id);


// Load the stats function (ensure the file is required above)
$stats = getAttendanceEnvelopeStatistics(
    $conn,
    $head_parish_id,
    CURRENT_YEAR,
    PREVIOUS_YEAR,
    $startDate,
    $endDate
);


// Load the revenue statistics function
$revenueStats = getRevenueStatistics(
    $conn,
    $head_parish_id,
    CURRENT_YEAR,
    PREVIOUS_YEAR,
    $startDate,
    $endDate,
    $reportType
);


// Load the other head parish revenue statistics
$otherRevenueStats = getOtherHeadParishRevenueStats(
    $conn,
    $head_parish_id,
    CURRENT_YEAR,
    PREVIOUS_YEAR,
    $startDate,
    $endDate,
    $reportType
);


$otherOperationLevelsRevenueStats = getAllHeadParishRevenueStats(
    $conn,
    $head_parish_id,
    CURRENT_YEAR,
    PREVIOUS_YEAR,
    $startDate,   
    $endDate,     
    $reportType   
);


// Prepare table data from stats
$curr_adult    = $stats['current_year']['actual_adult_attendance'];
$prev_adult    = $stats['previous_year']['actual_adult_attendance'];

$curr_children = $stats['current_year']['actual_children_attendance'];
$prev_children = $stats['previous_year']['actual_children_attendance'];

$curr_env      = $stats['current_year']['average_envelope_usage'];
$prev_env      = $stats['previous_year']['average_envelope_usage'];

$sub_parishes = $stats['current_year']['per_sub_parish'] ?? [];
$adult_bench    = $stats['adult_benchmark'];
$children_bench = $stats['children_benchmark'];
$env_bench      = $stats['no_envelope_benchmark'];

$sub_summary = [];
foreach(array_slice($sub_parishes, 0, 3, true) as $name => $num){
    $sub_summary[] = strtoupper(substr($name, 0, 1)) . " - " . $num;
}
$sub_summary_str = implode(", ", $sub_summary);

// ---------- GRAND TOTAL CALCULATION ----------
// We compute grand totals by summing all PREV, CURR and BUD across B1 (revenueStats), B2 (otherRevenueStats) and C (otherOperationLevelsRevenueStats)
$grandPrev = 0.0;
$grandCurr = 0.0;
$grandBud  = 0.0;

// Sum B1: revenueStats (accounts -> subparishes)
if (!empty($revenueStats) && is_array($revenueStats)) {
    foreach ($revenueStats as $account_name => $subParishes) {
        foreach ($subParishes as $sub_name => $values) {
            $grandPrev += (float)($values['PREV'] ?? 0);
            $grandCurr += (float)($values['CURR'] ?? 0);
            $grandBud  += (float)($values['BUD'] ?? 0);
        }
    }
}

// Sum B2: otherRevenueStats
if (!empty($otherRevenueStats) && is_array($otherRevenueStats)) {
    foreach ($otherRevenueStats as $stream_name => $values) {
        $grandPrev += (float)($values['PREV'] ?? 0);
        $grandCurr += (float)($values['CURR'] ?? 0);
        $grandBud  += (float)($values['BUD'] ?? 0);
    }
}

// Sum C: otherOperationLevelsRevenueStats (MITAA, JUMUIYA, IDARA_NA_VIKUNDI)
if (!empty($otherOperationLevelsRevenueStats) && is_array($otherOperationLevelsRevenueStats)) {
    foreach (['MITAA','JUMUIYA','IDARA_NA_VIKUNDI'] as $sectionKey) {
        if (!empty($otherOperationLevelsRevenueStats[$sectionKey]) && is_array($otherOperationLevelsRevenueStats[$sectionKey])) {
            foreach ($otherOperationLevelsRevenueStats[$sectionKey] as $name => $values) {
                $grandPrev += (float)($values['PREV'] ?? 0);
                $grandCurr += (float)($values['CURR'] ?? 0);
                $grandBud  += (float)($values['BUD'] ?? 0);
            }
        }
    }
}

// ---------- END GRAND TOTAL CALCULATION ----------



// Prepare HTML content
$html = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/png" href="/assets/images/logos/favicon.png" />
    <title>Income Statement</title>
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
            font-family: Helvetica; 
            margin: 25px;
            padding: 0;
            color: #333;
            background-color: #fff;
            
        }
        .container {
            width: 90%;
            margin: 4px auto;
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
            padding: 1px;
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
            margin-bottom: 8px;
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
        
        .column-bg-color{
            background-color:#ECEFF1;
        }
        
        .section-color{
            background-color:yellow;
        }
        
        .header-row-bg{
            background-color:#D1E4BB;
        }

        .total-row {
            background-color: #F0F4C3;
        }

        .grand-total {
            background-color: #D1C4E9;
            font-size: 12px;
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
        <div class="header" style="text-align: center; margin-bottom: 5px;">
            <h1 style="font-size: 10px; color: #3498db; margin: 0;">
                K.K.K.T ' . strtoupper($parish_info['diocese_name']) . ' | ' . strtoupper($parish_info['province_name']) . ' | ' . strtoupper($parish_info['head_parish_name']) . '
            </h1>
            <h1 style="font-size: 10px; color: #2c3e50; margin: 2px 0;">
                TAARIFA YA MAPATO NGAZI ZOTE ZA USHARIKA ' . strtoupper($periodLabel) . '
                <span class="date">' . strtoupper($displayStartDate) . ' - ' . strtoupper($displayEndDate) . '</span>
            </h1>
        </div>
    </div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th class="section-color" style="width:3%">A</th>
                <th colspan="6" class="header-row-bg">MAHUDHURIO NA MATUMIZI YA BAHASHA</th>
            </tr>
            <tr class="header-row-bg">
                <th>Na.</th>
                <th>Kundi</th>
                <th>Halisi ' . DateTime::createFromFormat("Y-m-d", PREVIOUS_PERIOD_START)->format("M") . '-' . DateTime::createFromFormat("Y-m-d", PREVIOUS_PERIOD_END)->format("M") . ' ' . substr(PREVIOUS_YEAR, -2) . '</th>
                <th>Halisi ' . DateTime::createFromFormat("Y-m-d", CURRENT_PERIOD_START)->format("M") . '-' . DateTime::createFromFormat("Y-m-d", CURRENT_PERIOD_END)->format("M") . ' ' . substr(CURRENT_YEAR, -2) . '</th>
                <th>Bajeti ' . DateTime::createFromFormat("Y-m-d", CURRENT_PERIOD_START)->format("M") . '-' . DateTime::createFromFormat("Y-m-d", CURRENT_PERIOD_END)->format("M") . ' ' . substr(CURRENT_YEAR, -2) . '</th>
                <th>Tofauti ya Bajeti</th>
                <th>Halisi vs Bajeti (%)</th>
            </tr>

        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td class="text-left">Watu Wazima</td>
                <td class="text-right">' . number_format($prev_adult, 0) . '</td>
                <td class="text-right">' . number_format($curr_adult, 0) . '</td>
                <td class="text-right">' . number_format($adult_bench, 0) . '</td>
                <td class="text-right">' . number_format($curr_adult - $adult_bench, 0) . '</td>
                <td class="text-right">' . round(calculatePercentage($curr_adult, $adult_bench), 2) . '%</td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td class="text-left">Watoto</td>
                <td class="text-right">' . number_format($prev_children, 0) . '</td>
                <td class="text-right">' . number_format($curr_children, 0) . '</td>
                <td class="text-right">' . number_format($children_bench, 0) . '</td>
                <td class="text-right">' . number_format($curr_children -$children_bench, 0) . '</td>
                <td class="text-right">' . round(calculatePercentage($curr_children, $children_bench), 2) . '%</td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td class="text-left">Bahasha (' . $sub_summary_str . ')</td>
                <td class="text-right">' . number_format($prev_env, 0) . '</td>
                <td class="text-right">' . number_format($curr_env, 0) . '</td>
                <td class="text-right">' . number_format($curr_adult, 0) . '</td>
                <td class="text-right">' . number_format($curr_env -$curr_adult, 0) . '</td>
                <td class="text-right">' . round(calculatePercentage($curr_env, $curr_adult), 2) . '%</td>
            </tr>
        </tbody>
    </table>
</div>';

// ---------------- B1 (MAPATO YA USHARIKA KWA MITAA) ----------------
$html .= '<div class="table-container">
        <table border="1" cellpadding="5" cellspacing="0">
            <thead> 
                <tr>
                    <th class="section-color" style="width:3%">B1</th>
                    <th colspan="6" class="text-left header-row-bg">MAPATO YA USHARIKA KWA MITAA</th>
                </tr>
                <tr>
                    <th>Na.</th>
                    <th>Mtaa</th>
                    <th>Halisi ' . DateTime::createFromFormat("Y-m-d", PREVIOUS_PERIOD_START)->format("M") . '-' . DateTime::createFromFormat("Y-m-d", PREVIOUS_PERIOD_END)->format("M") . ' ' . substr(PREVIOUS_YEAR, -2) . '</th>
                    <th>Halisi ' . DateTime::createFromFormat("Y-m-d", CURRENT_PERIOD_START)->format("M") . '-' . DateTime::createFromFormat("Y-m-d", CURRENT_PERIOD_END)->format("M") . ' ' . substr(CURRENT_YEAR, -2) . '</th>
                    <th>Bajeti ' . DateTime::createFromFormat("Y-m-d", CURRENT_PERIOD_START)->format("M") . '-' . DateTime::createFromFormat("Y-m-d", CURRENT_PERIOD_END)->format("M") . ' ' . substr(CURRENT_YEAR, -2) . '</th>
                    <th>Tofauti ya Bajeti</th>
                    <th>Halisi vs Bajeti (%)</th>
                </tr>
            </thead>
            <tbody>';

// per-account totals will still be shown; accumulate into section totals and grand totals already done earlier
foreach ($revenueStats as $account_name => $subParishes) {
    $counter = 1;
    $totalPrev = 0;
    $totalCurr = 0;
    $totalBud  = 0;

    // Account name row spanning all columns
    $html .= '<tr>
        <td colspan="7" class="text-left section-color"><strong>' . strtoupper($account_name) . '</strong></td>
    </tr>';

    foreach ($subParishes as $sub_name => $values) {
        $prev = (float)$values['PREV'];
        $curr = (float)$values['CURR'];
        $bud  = (float)$values['BUD'];
        $diff = $bud - $curr;
        $perc = $bud > 0 ? round(calculatePercentage($curr, $bud), 2) . '%' : '-';

        $html .= '<tr>
            <td class="text-center">' . $counter . '</td>
            <td class="text-left">' . strtoupper($sub_name) . '</td>
            <td class="text-right">' . number_format($prev, 0) . '</td>
            <td class="text-right">' . number_format($curr, 0) . '</td>
            <td class="text-right">' . number_format($bud, 0) . '</td>
            <td class="text-right">' . number_format($diff, 0) . '</td>
            <td class="text-right">' . $perc . '</td>
        </tr>';

        $totalPrev += $prev;
        $totalCurr += $curr;
        $totalBud  += $bud;
        $counter++;
    }

    // Add total row for this account
    $totalDiff = $totalBud - $totalCurr;
    $totalPerc = $totalBud > 0 ? round(calculatePercentage($totalCurr, $totalBud), 2) . '%' : '-';

    $html .= '<tr class="total-row">
        <td colspan="2" class="text-right"><strong>JUMLA</strong></td>
        <td class="text-right"><strong>' . number_format($totalPrev, 0) . '</strong></td>
        <td class="text-right"><strong>' . number_format($totalCurr, 0) . '</strong></td>
        <td class="text-right"><strong>' . number_format($totalBud, 0) . '</strong></td>
        <td class="text-right"><strong>' . number_format($totalDiff, 0) . '</strong></td>
        <td class="text-right"><strong>' . $totalPerc . '</strong></td>
    </tr>';
}

// Finalize B1 table
$html .= '</tbody></table></div>';

// ---------------- B2 (MAPATO MENGINEYO) ----------------
$html .= '<div class="table-container">
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th class="section-color" style="width:3%">B2</th>
                <th colspan="6" class="text-left header-row-bg">MAPATO MENGINEYO</th>
            </tr>
            <tr>
                <th>Na.</th>
                <th>Jina la Mapato</th>
                <th>Halisi ' . DateTime::createFromFormat("Y-m-d", PREVIOUS_PERIOD_START)->format("M") . '-' . DateTime::createFromFormat("Y-m-d", PREVIOUS_PERIOD_END)->format("M") . ' ' . substr(PREVIOUS_YEAR, -2) . '</th>
                <th>Halisi ' . DateTime::createFromFormat("Y-m-d", CURRENT_PERIOD_START)->format("M") . '-' . DateTime::createFromFormat("Y-m-d", CURRENT_PERIOD_END)->format("M") . ' ' . substr(CURRENT_YEAR, -2) . '</th>
                <th>Bajeti ' . DateTime::createFromFormat("Y-m-d", CURRENT_PERIOD_START)->format("M") . '-' . DateTime::createFromFormat("Y-m-d", CURRENT_PERIOD_END)->format("M") . ' ' . substr(CURRENT_YEAR, -2) . '</th>
                <th>Tofauti ya Bajeti</th>
                <th>Halisi vs Bajeti (%)</th>
            </tr>
        </thead>
        <tbody>';

$counter = 1;
$totalPrev = 0;
$totalCurr = 0;
$totalBud  = 0;

foreach ($otherRevenueStats as $stream_name => $values) {
    $prev = (float)$values['PREV'];
    $curr = (float)$values['CURR'];
    $bud  = (float)$values['BUD'];
    $diff = $curr -$bud;
    $perc = $bud > 0 ? round(calculatePercentage($curr, $bud), 2) . '%' : '-';

    $html .= '<tr>
        <td class="text-center">' . $counter . '</td>
        <td class="text-left">' . strtoupper($stream_name) . '</td>
        <td class="text-right">' . number_format($prev, 0) . '</td>
        <td class="text-right">' . number_format($curr, 0) . '</td>
        <td class="text-right">' . number_format($bud, 0) . '</td>
        <td class="text-right">' . number_format($diff, 0) . '</td>
        <td class="text-right">' . $perc . '</td>
    </tr>';

    $totalPrev += $prev;
    $totalCurr += $curr;
    $totalBud  += $bud;
    $counter++;
}

// Add total row for B2
$totalDiff = $totalBud - $totalCurr;
$totalPerc = $totalBud > 0 ? round(calculatePercentage($totalCurr, $totalBud), 2) . '%' : '-';

$html .= '<tr class="total-row">
    <td colspan="2" class="text-right"><strong>JUMLA</strong></td>
    <td class="text-right"><strong>' . number_format($totalPrev, 0) . '</strong></td>
    <td class="text-right"><strong>' . number_format($totalCurr, 0) . '</strong></td>
    <td class="text-right"><strong>' . number_format($totalBud, 0) . '</strong></td>
    <td class="text-right"><strong>' . number_format($totalDiff, 0) . '</strong></td>
    <td class="text-right"><strong>' . $totalPerc . '</strong></td>
</tr>';

$html .= '</tbody></table></div>';

// ---------------- Section C header ----------------
$html .= '<div class="table-container">
    <table border="1" cellpadding="5" cellspacing="0">
        <thead> 
            <tr>
                <th class="section-color" style="width:3%">C</th>
                <th colspan="6" class="text-left header-row-bg">MAPATO YA MITAA, JUMUIYA, IDARA NA VIKUNDI</th>
            </tr>
        </thead>
    </table>
</div>';

// Helper function to render each C table (returns HTML)
function renderRevenueTable($title, $section_code, $items, $type_label) {
    $html = '<div class="table-container">
        <table border="1" cellpadding="5" cellspacing="0">
            <thead>
                <tr>
                    <th class="section-color" style="width:3%">' . $section_code . '</th>
                    <th colspan="6" class="text-left header-row-bg">' . $title . '</th>
                </tr>
                <tr>
                    <th>Na.</th>
                    <th>' . $type_label . '</th>
                    <th>Halisi ' . DateTime::createFromFormat("Y-m-d", PREVIOUS_PERIOD_START)->format("M") . '-' . DateTime::createFromFormat("Y-m-d", PREVIOUS_PERIOD_END)->format("M") . ' ' . substr(PREVIOUS_YEAR, -2) . '</th>
                    <th>Halisi ' . DateTime::createFromFormat("Y-m-d", CURRENT_PERIOD_START)->format("M") . '-' . DateTime::createFromFormat("Y-m-d", CURRENT_PERIOD_END)->format("M") . ' ' . substr(CURRENT_YEAR, -2) . '</th>
                    <th>Bajeti ' . DateTime::createFromFormat("Y-m-d", CURRENT_PERIOD_START)->format("M") . '-' . DateTime::createFromFormat("Y-m-d", CURRENT_PERIOD_END)->format("M") . ' ' . substr(CURRENT_YEAR, -2) . '</th>
                    <th>Tofauti ya Bajeti</th>
                    <th>Halisi vs Bajeti (%)</th>
                </tr>
            </thead>
            <tbody>';

    $counter = 1;
    $totalPrev = 0;
    $totalCurr = 0;
    $totalBud  = 0;

    foreach ($items as $name => $values) {
        $prev = (float)$values['PREV'];
        $curr = (float)$values['CURR'];
        $bud  = (float)$values['BUD'];
        $diff =  $curr - $bud;
        $perc = $bud > 0 ? round(calculatePercentage($curr, $bud), 2) . '%' : '-';

        $html .= '<tr>
            <td class="text-center">' . $counter . '</td>
            <td class="text-left">' . strtoupper($name) . '</td>
            <td class="text-right">' . number_format($prev, 0) . '</td>
            <td class="text-right">' . number_format($curr, 0) . '</td>
            <td class="text-right">' . number_format($bud, 0) . '</td>
            <td class="text-right">' . number_format($diff, 0) . '</td>
            <td class="text-right">' . $perc . '</td>
        </tr>';

        $totalPrev += $prev;
        $totalCurr += $curr;
        $totalBud  += $bud;
        $counter++;
    }

    // Add total row
    $totalDiff = $totalCurr - $totalBud;
    $totalPerc = $totalBud > 0 ? round(calculatePercentage($totalCurr, $totalBud), 2) . '%' : '-';

    $html .= '<tr class="total-row">
        <td colspan="2" class="text-right"><strong>JUMLA</strong></td>
        <td class="text-right"><strong>' . number_format($totalPrev, 0) . '</strong></td>
        <td class="text-right"><strong>' . number_format($totalCurr, 0) . '</strong></td>
        <td class="text-right"><strong>' . number_format($totalBud, 0) . '</strong></td>
        <td class="text-right"><strong>' . number_format($totalDiff, 0) . '</strong></td>
        <td class="text-right"><strong>' . $totalPerc . '</strong></td>
    </tr>';

    $html .= '</tbody></table></div>';
    return $html;
}

// Render C1: MITAA
$html .= renderRevenueTable('MAPATO YA MITAA', 'C1', $otherOperationLevelsRevenueStats['MITAA'] ?? [], 'Mtaa');

// Render C2: JUMUIYA
$html .= renderRevenueTable('MAPATO YA JUMUIYA', 'C2', $otherOperationLevelsRevenueStats['JUMUIYA'] ?? [], 'Jumuiya');

// Render C3: IDARA NA VIKUNDI
$html .= renderRevenueTable('MAPATO YA IDARA NA VIKUNDI', 'C3', $otherOperationLevelsRevenueStats['IDARA_NA_VIKUNDI'] ?? [], 'Kikundi');

// ---------------- GRAND TOTAL ROW ----------------
// Use the grand totals computed earlier
$grandDiff = $grandCurr -$grandBud;
$grandPerc = $grandBud > 0 ? round(calculatePercentage($grandCurr, $grandBud), 2) . '%' : '-';

$html .= '<div class="table-container">
    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th class="grand-total" colspan="7" style="text-align:left">JUMLA YA MAPATO YOTE</th>
            </tr>
        </thead>
        <tbody>
            <tr class="grand-total">
                <td colspan="2" class="text-right"><strong>JUMLA KUU</strong></td>
                <td class="text-right"><strong>' . number_format($grandPrev, 0) . '</strong></td>
                <td class="text-right"><strong>' . number_format($grandCurr, 0) . '</strong></td>
                <td class="text-right"><strong>' . number_format($grandBud, 0) . '</strong></td>
                <td class="text-right"><strong>' . number_format($grandDiff, 0) . '</strong></td>
                <td class="text-right"><strong>' . $grandPerc . '</strong></td>
            </tr>
        </tbody>
    </table>
</div>';

date_default_timezone_set('Africa/Nairobi');
$printedOn = date('d M Y, H:i');
$html .= '
<div class="footer">
    <p>Kanisa Langu - SEWMR Technologies | Printed on ' . $printedOn . '</p>
</div>
</body>
</html>';

// Load HTML into Dompdf
$dompdf->loadHtml($html);

// Set paper size and orientation
$dompdf->setPaper('A4', 'portrait');

// Render the HTML as PDF
$dompdf->render();


// Construct the filename with sub-parish name and date range
$filename = CURRENT_YEAR." ". $quarter." Quarterly Income Statement.pdf";
$dompdf->stream($filename, array("Attachment" => false));

?>
