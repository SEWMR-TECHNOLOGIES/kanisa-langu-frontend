<?php 
// This page aims at generating an income statement report
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

$parish_info = getParishInfo($conn, $head_parish_id);

// Sanitize and validate the year input
$year = filter_var($_GET['year'], FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 2024, 'max_range' => date('Y')]
]);

if (!$year) {
    header("Location: /error.php?message=" . urlencode("Invalid year. Please select a valid year."));
    exit();
}

// Define constants for the current and previous year
define('CURRENT_YEAR', $year);
define('PREVIOUS_YEAR', $year - 1);

// Fetch attendance data for the current and previous year
$currentYearAttendance = getAverageAttendance(CURRENT_YEAR, $head_parish_id, $conn);
$previousYearAttendance = getAverageAttendance(PREVIOUS_YEAR, $head_parish_id, $conn);

// Store attendance in separate variables
$currentYearAdultAttendance = $currentYearAttendance['average_adult_attendance'];
$currentYearChildrenAttendance = $currentYearAttendance['average_children_attendance'];

$previousYearAdultAttendance = $previousYearAttendance['average_adult_attendance'];
$previousYearChildrenAttendance = $previousYearAttendance['average_children_attendance'];

// Fetch the attendance benchmark
$attendanceBenchmark = getAttendanceBenchmark($head_parish_id, $conn);


// Calculate the percentage of previous year attendance compared to benchmark
$previousYearAdultPercentage = ($attendanceBenchmark > 0) ? round(($previousYearAdultAttendance / $attendanceBenchmark) * 100, 2) : 0;
$previousYearChildrenPercentage = ($attendanceBenchmark > 0) ? round(($previousYearChildrenAttendance / $attendanceBenchmark) * 100, 2) : 0;

// Calculate the percentage of current year attendance compared to benchmark
$currentYearAdultPercentage = ($attendanceBenchmark > 0) ? round(($currentYearAdultAttendance / $attendanceBenchmark) * 100, 2) : 0;
$currentYearChildrenPercentage = ($attendanceBenchmark > 0) ? round(($currentYearChildrenAttendance / $attendanceBenchmark) * 100, 2) : 0;

// Calculate the difference between current year attendance and benchmark
$currentYearAdultDifference = $currentYearAdultAttendance - $attendanceBenchmark;
$currentYearChildrenDifference = $currentYearChildrenAttendance - $attendanceBenchmark;



// Fetch revenue group financials for both years
$previousYearData = getHeadParishRevenueGroupFinancials($head_parish_id, PREVIOUS_YEAR, $conn);
$currentYearData = getHeadParishRevenueGroupFinancials($head_parish_id, CURRENT_YEAR, $conn);

// Store data in an associative array by revenue group ID for easy lookup
$previousYearMap = [];
foreach ($previousYearData as $group) {
    $previousYearMap[$group['revenue_group_id']] = $group;
}

$currentYearMap = [];
foreach ($currentYearData as $group) {
    $currentYearMap[$group['revenue_group_id']] = $group;
}


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
            font-family: Arial; 
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
        
        
        .section-color{
            background-color:yellow;
        }
        
    </style>
</head>
<body>
    <div class="container">
        <div class="header" style="text-align: center; margin-bottom: 5px;">
            <h1 style="font-size: 10px; color: #3498db; margin: 0;">K.K.K.T ' . $parish_info['diocese_name'] .' | ' . $parish_info['province_name'] . ' | ' . $parish_info['head_parish_name'] . '</h1>
            <h1 style="font-size: 10px; color: #2c3e50; margin: 2px 0;"> TAARIFA YA MAPATO NA MATUMIZI KWA MWAKA '.'<span class="date">' .CURRENT_YEAR . '</span></h1>
        </div>
    </div>

<div class="table-container">
        <table border="1" cellpadding="5" cellspacing="0">
            <thead> 
                <tr>
                    <th class="section-color" style="width:3%">A</th>
                    <th colspan="8" class="text-left">MAHUDHURIO</th>
                </tr>
                <tr>
                    <th rowspan="2">Na.</th>
                    <th rowspan="2" style="width:20%">Maelezo</th>
                    <th colspan="2" style="width:22%">'.PREVIOUS_YEAR.'</th>
                    <th colspan="2" style="width:22%">'.CURRENT_YEAR.'</th>
                    <th colspan="2" style="width:22%">MAFANIKIO</th>
                    <th rowspan="2" style="width:11%">TOFAUTI ' .CURRENT_YEAR.'</th>
                </tr>
                <tr>
                    <th style="width:11%">BAJETI</th>
                    <th style="width:11%">HALISI</th>
                    <th style="width:11%">BAJETI</th>
                    <th style="width:11%">HALISI</th>
                    <th style="width:11%">'.PREVIOUS_YEAR.'</th>
                    <th style="width:11%">'.CURRENT_YEAR.'</th>
                </tr>
            </thead>
            <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>Watu wazima</td>
                <td>'.$attendanceBenchmark.'</td>
                <td>'.$previousYearAdultAttendance.'</td>
                <td>'.$attendanceBenchmark.'</td>
                <td>'.$currentYearAdultAttendance.'</td>
                <td>'.$previousYearAdultPercentage.'%</td>
                <td>'.$currentYearAdultPercentage.'%</td>
                <td>'.$currentYearAdultDifference.'</td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td>Watoto</td>
                <td>'.$attendanceBenchmark.'</td>
                <td>'.$previousYearChildrenAttendance.'</td>
                <td>'.$attendanceBenchmark.'</td>
                <td>'.$currentYearChildrenAttendance.'</td>
                <td>'.$previousYearChildrenPercentage.'%</td>
                <td>'.$currentYearChildrenPercentage.'%</td>
                <td>'.$currentYearChildrenDifference.'</td>
            </tr>';

$html .= '</tbody>
        </table>
    </div>';

$html .= '<div class="table-container">
        <table border="1" cellpadding="5" cellspacing="0">
            <thead> 
                <tr>
                    <th class="section-color" style="width:3%">B1</th>
                    <th colspan="8" class="text-left">MAPATO YA USHARIKA</th>
                </tr>
            </thead>
            <tbody>';

// Loop through current year groups and find corresponding previous year data
$counter = 1;
// Initialize totals
$totalPrevBudget = 0;
$totalPrevRevenue = 0;
$totalCurrBudget = 0;
$totalCurrRevenue = 0;


$grandTotalPrevBudget = 0;
$grandTotalPrevRevenue = 0;
$grandTotalCurrBudget  = 0;
$grandTotalCurrRevenue = 0;

// Loop through current year groups and find corresponding previous year data
foreach ($currentYearMap as $groupId => $current) {
    $prev = $previousYearMap[$groupId] ?? ['total_budget' => 0, 'total_revenue_collected' => 0];

    // Extract values
    $prevBudget = $prev['total_budget'];
    $prevRevenue = $prev['total_revenue_collected'];
    $currBudget = $current['total_budget'];
    $currRevenue = $current['total_revenue_collected'];

    // Calculate percentages
    $prevSuccessRate = ($prevBudget > 0) ? ($prevRevenue / $prevBudget) * 100 : 0;
    $currSuccessRate = ($currBudget > 0) ? ($currRevenue / $currBudget) * 100 : 0;

    // Difference in amount between budget and revenue for current year
    $budgetRevenueDiff = $currRevenue - $currBudget;

    // Sum up totals
    $totalPrevBudget += $prevBudget;
    $totalPrevRevenue += $prevRevenue;
    $totalCurrBudget += $currBudget;
    $totalCurrRevenue += $currRevenue;

    // Append row
    $html .= '<tr>
                <td>' . $counter++ . '</td>
                <td style="width:20%">' . htmlspecialchars($current['revenue_group_name']) . '</td>
                <td style="width:11%">' . number_format($prevBudget, 2) . '</td>
                <td style="width:11%">' . number_format($prevRevenue, 2) . '</td>
                <td style="width:11%">' . number_format($currBudget, 2) . '</td>
                <td style="width:11%">' . number_format($currRevenue, 2) . '</td>
                <td style="width:11%">' . number_format($prevSuccessRate, 2) . '%</td>
                <td style="width:11%">' . number_format($currSuccessRate, 2) . '%</td>
                <td>' . number_format($budgetRevenueDiff, 2) . '</td>
            </tr>';
}

// Calculate total success rates
$totalPrevSuccessRate = ($totalPrevBudget > 0) ? ($totalPrevRevenue / $totalPrevBudget) * 100 : 0;
$totalCurrSuccessRate = ($totalCurrBudget > 0) ? ($totalCurrRevenue / $totalCurrBudget) * 100 : 0;

// Calculate overall budget vs revenue difference
$totalBudgetRevenueDiff = $totalCurrRevenue - $totalCurrBudget;

$grandTotalPrevBudget += $totalPrevBudget;
$grandTotalPrevRevenue += $totalPrevRevenue;
$grandTotalCurrBudget += $totalCurrBudget;
$grandTotalCurrRevenue += $totalCurrRevenue;

// Append total row
$html .= '<tr class="total-row">
            <td colspan="2"><strong>Jumla</strong></td>
            <td><strong>' . number_format($totalPrevBudget, 2) . '</strong></td>
            <td><strong>' . number_format($totalPrevRevenue, 2) . '</strong></td>
            <td><strong>' . number_format($totalCurrBudget, 2) . '</strong></td>
            <td><strong>' . number_format($totalCurrRevenue, 2) . '</strong></td>
            <td><strong>' . number_format($totalPrevSuccessRate, 2) . '%</strong></td>
            <td><strong>' . number_format($totalCurrSuccessRate, 2) . '%</strong></td>
            <td><strong>' . number_format($totalBudgetRevenueDiff, 2) . '</strong></td>
        </tr>';

$html .= '</tbody>
        </table>
    </div>';


// Fetch revenue group financials for both years
$previousYearData = getRevenueDataByHeadParish($head_parish_id, PREVIOUS_YEAR, $conn);
$currentYearData = getRevenueDataByHeadParish($head_parish_id, CURRENT_YEAR, $conn);

// Store data in an associative array by category ID for easy lookup
$previousYearMap = [];
foreach ($previousYearData as $group) {
    $previousYearMap[$group['category_id']] = $group;
}

$currentYearMap = [];
foreach ($currentYearData as $group) {
    $currentYearMap[$group['category_id']] = $group;
}
// Begin HTML output for the table
$html .= '<div class="table-container">
        <table border="1" cellpadding="5" cellspacing="0">
            <thead> 
                <tr>
                    <th class="section-color" style="width:3%">B2</th>
                    <th colspan="8" class="text-left">MAPATO YA VIKUNDI, JUMUIYA NA MITAA</th>
                </tr>
            </thead>
            <tbody>';

$counter = 1;

// Initialize totals
$totalPrevBudget = 0;
$totalPrevRevenue = 0;
$totalCurrBudget = 0;
$totalCurrRevenue = 0;

// Loop through current year groups and find corresponding previous year data
foreach ($currentYearMap as $groupId => $current) {
    $prev = $previousYearMap[$groupId] ?? ['total_budget' => 0, 'total_revenue_collected' => 0];

    // Extract values
    $prevBudget = $prev['total_budget'];
    $prevRevenue = $prev['total_revenue_collected'];
    $currBudget = $current['total_budget'];
    $currRevenue = $current['total_revenue_collected'];

    // Calculate success rates (percentage)
    $prevSuccessRate = ($prevBudget > 0) ? ($prevRevenue / $prevBudget) * 100 : 0;
    $currSuccessRate = ($currBudget > 0) ? ($currRevenue / $currBudget) * 100 : 0;

    // Calculate difference in budget vs revenue for current year
    $budgetRevenueDiff = $currRevenue - $currBudget;

    // Sum up totals
    $totalPrevBudget += $prevBudget;
    $totalPrevRevenue += $prevRevenue;
    $totalCurrBudget += $currBudget;
    $totalCurrRevenue += $currRevenue;

    // Append row to HTML table
    $html .= '<tr>
                <td>' . $counter++ . '</td>
                <td style="width:20%">' . htmlspecialchars($current['category_name']) . '</td>
                <td style="width:11%">' . number_format($prevBudget, 2) . '</td>
                <td style="width:11%">' . number_format($prevRevenue, 2) . '</td>
                <td style="width:11%">' . number_format($currBudget, 2) . '</td>
                <td style="width:11%">' . number_format($currRevenue, 2) . '</td>
                <td style="width:11%">' . number_format($prevSuccessRate, 2) . '%</td>
                <td style="width:11%">' . number_format($currSuccessRate, 2) . '%</td>
                <td>' . number_format($budgetRevenueDiff, 2) . '</td>
            </tr>';
}

// Calculate total success rates
$totalPrevSuccessRate = ($totalPrevBudget > 0) ? ($totalPrevRevenue / $totalPrevBudget) * 100 : 0;
$totalCurrSuccessRate = ($totalCurrBudget > 0) ? ($totalCurrRevenue / $totalCurrBudget) * 100 : 0;

// Calculate overall budget vs revenue difference
$totalBudgetRevenueDiff = $totalCurrRevenue - $totalCurrBudget;

$grandTotalPrevBudget += $totalPrevBudget;
$grandTotalPrevRevenue += $totalPrevRevenue;
$grandTotalCurrBudget += $totalCurrBudget;
$grandTotalCurrRevenue += $totalCurrRevenue;

// Calculate total success rates
$grandTotalPrevSuccessRate = ($grandTotalPrevBudget > 0) ? ($grandTotalPrevRevenue / $grandTotalPrevBudget) * 100 : 0;
$grandTotalCurrSuccessRate = ($grandTotalCurrBudget > 0) ? ($grandTotalCurrRevenue / $grandTotalCurrBudget) * 100 : 0;

// Calculate overall budget vs revenue difference
$grandTotalBudgetRevenueDiff = $grandTotalCurrRevenue - $grandTotalCurrBudget;

// Append total row
$html .= '<tr class="total-row">
            <td colspan="2"><strong>Jumla</strong></td>
            <td><strong>' . number_format($totalPrevBudget, 2) . '</strong></td>
            <td><strong>' . number_format($totalPrevRevenue, 2) . '</strong></td>
            <td><strong>' . number_format($totalCurrBudget, 2) . '</strong></td>
            <td><strong>' . number_format($totalCurrRevenue, 2) . '</strong></td>
            <td><strong>' . number_format($totalPrevSuccessRate, 2) . '%</strong></td>
            <td><strong>' . number_format($totalCurrSuccessRate, 2) . '%</strong></td>
            <td><strong>' . number_format($totalBudgetRevenueDiff, 2) . '</strong></td>
        </tr>';

// Append total row
$html .= '<tr class="total-row">
            <td colspan="2"><strong>Jumla Kuu</strong></td>
            <td><strong>' . number_format($grandTotalPrevBudget, 2) . '</strong></td>
            <td><strong>' . number_format($grandTotalPrevRevenue, 2) . '</strong></td>
            <td><strong>' . number_format($grandTotalCurrBudget, 2) . '</strong></td>
            <td><strong>' . number_format($grandTotalCurrRevenue, 2) . '</strong></td>
            <td><strong>' . number_format($grandTotalPrevSuccessRate, 2) . '%</strong></td>
            <td><strong>' . number_format($grandTotalCurrSuccessRate, 2) . '%</strong></td>
            <td><strong>' . number_format($grandTotalBudgetRevenueDiff, 2) . '</strong></td>
        </tr>';
$html .= '</tbody>
        </table>
    </div>';



// Fetch expense group financials for both years
$previousYearData = getExpenseDataByHeadParish($head_parish_id, PREVIOUS_YEAR, $conn);
$currentYearData = getExpenseDataByHeadParish($head_parish_id, CURRENT_YEAR, $conn);

// Store data in an associative array by expense group ID for easy lookup
$previousYearMap = [];
foreach ($previousYearData as $group) {
    $previousYearMap[$group['category_id']] = $group;
}

$currentYearMap = [];
foreach ($currentYearData as $group) {
    $currentYearMap[$group['category_id']] = $group;
}

$html .= '<div class="table-container">
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th class="section-color" style="width:3%">C</th>
                <th colspan="8" class="text-left">MATUMIZI</th>
            </tr>
        </thead>
        <tbody>';

$counter = 1;
// Initialize totals
$totalPrevBudget = 0;
$totalPrevExpenses = 0;
$totalCurrBudget = 0;
$totalCurrExpenses = 0;

// Loop through current year groups and find corresponding previous year data
foreach ($currentYearMap as $groupId => $current) {
    // Get previous year data for the same group
    $prev = $previousYearMap[$groupId] ?? ['total_budget' => 0, 'total_approved_expenses' => 0];

    // Extract values
    $prevBudget = $prev['total_budget'];
    $prevExpenses = $prev['total_approved_expenses'];
    $currBudget = $current['total_budget'];
    $currExpenses = $current['total_approved_expenses'];

    // Calculate success rates (percentage of approved expenses over budget)
    $prevSuccessRate = ($prevBudget > 0) ? ($prevExpenses / $prevBudget) * 100 : 0;
    $currSuccessRate = ($currBudget > 0) ? ($currExpenses / $currBudget) * 100 : 0;

    // Difference in amount between budget and actual expenses for current year
    $budgetExpenseDiff = $currExpenses - $currBudget;

    // Sum up totals
    $totalPrevBudget += $prevBudget;
    $totalPrevExpenses += $prevExpenses;
    $totalCurrBudget += $currBudget;
    $totalCurrExpenses += $currExpenses;

    // Append row to table
    $html .= '<tr>
                <td>' . $counter++ . '</td>
                <td style="width:20%">' . htmlspecialchars($current['category_name']) . '</td>
                <td style="width:11%">' . number_format($prevBudget, 2) . '</td>
                <td style="width:11%">' . number_format($prevExpenses, 2) . '</td>
                <td style="width:11%">' . number_format($currBudget, 2) . '</td>
                <td style="width:11%">' . number_format($currExpenses, 2) . '</td>
                <td style="width:11%">' . number_format($prevSuccessRate, 2) . '%</td>
                <td style="width:11%">' . number_format($currSuccessRate, 2) . '%</td>
                <td style="width:11%">' . number_format($budgetExpenseDiff, 2) . '</td>
            </tr>';
}

// Calculate total success rates
$totalPrevSuccessRate = ($totalPrevBudget > 0) ? ($totalPrevExpenses / $totalPrevBudget) * 100 : 0;
$totalCurrSuccessRate = ($totalCurrBudget > 0) ? ($totalCurrExpenses / $totalCurrBudget) * 100 : 0;

// Calculate total budget vs expenses difference
$totalBudgetExpenseDiff = $totalCurrExpenses - $totalCurrBudget;

// Append total row
$html .= '<tr class="total-row">
            <td colspan="2"><strong>Jumla</strong></td>
            <td><strong>' . number_format($totalPrevBudget, 2) . '</strong></td>
            <td><strong>' . number_format($totalPrevExpenses, 2) . '</strong></td>
            <td><strong>' . number_format($totalCurrBudget, 2) . '</strong></td>
            <td><strong>' . number_format($totalCurrExpenses, 2) . '</strong></td>
            <td><strong>' . number_format($totalPrevSuccessRate, 2) . '%</strong></td>
            <td><strong>' . number_format($totalCurrSuccessRate, 2) . '%</strong></td>
            <td><strong>' . number_format($totalBudgetExpenseDiff, 2) . '</strong></td>
        </tr>';

$html .= '</tbody>
        </table>
    </div>';


// Fetch assets data for CURRENT_YEAR
$assetsData = getHeadParishAssets($head_parish_id, CURRENT_YEAR, $conn);

$html .= '<div class="table-container">
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th class="section-color" style="width:3%">D</th>
                <th colspan="8" class="text-left" style="width:97%">MALI ZA USHARIKA</th>
            </tr>
            <tr>
                <th style="width:3%">Na.</th>
                <th style="width:20%">Mali</th>
                <th style="width:11%">Hali</th>
                <th style="width:11%">Kuzalisha</th>
                <th style="width:11%">Kiasi</th>
                <th colspan="4">Maoni / Mapendekezo</th>
            </tr>
        </thead>
        <tbody>';

// Loop through the assets data for the current year and generate table rows
$counter = 1;
// Initialize total revenue
$totalRevenue = 0;

// Loop through assets data
foreach ($assetsData as $asset) {
    // Extract asset details
    $assetName = htmlspecialchars($asset['asset_name']);
    $status = htmlspecialchars($asset['status']);
    $generatesRevenue = $asset['generates_revenue'];
    $revenue = $asset['generated_revenue'];
    $description = htmlspecialchars($asset['status_description']);
    $generatesRevenueDisplay = 'Hapana';

    // Determine revenue column content and sum total revenue
    if ($generatesRevenue === 'Yes') {
        $revenueDisplay = $revenue != '0.00' ? number_format($revenue, 2) : '-';
        $generatesRevenueDisplay = 'Ndio';

        // Add to total revenue if valid
        $totalRevenue += ($revenue != '0.00') ? $revenue : 0;
    } else {
        $revenueDisplay = 'Huduma';
    }

    // Append row to the table
    $html .= '<tr>
                <td>' . $counter++ . '</td>
                <td>' . $assetName . '</td>
                <td>' . $status . '</td>
                <td>' . $generatesRevenueDisplay . '</td>
                <td>' . $revenueDisplay . '</td>
                <td colspan="4">' . $description . '</td>
            </tr>';
}

// Append total row
$html .= '<tr class="total-row">
            <td colspan="4"><strong>Jumla ya Mapato</strong></td>
            <td><strong>' . number_format($totalRevenue, 2) . '</strong></td>
            <td colspan="4"></td>
        </tr>';

$html .= '</tbody>
        </table>
    </div>';


        
$html .= '
</body>
</html>';

// Load HTML into Dompdf
$dompdf->loadHtml($html);

// Set paper size and orientation
$dompdf->setPaper('A4', 'landscape');

// Render the HTML as PDF
$dompdf->render();


// Construct the filename with sub-parish name and date range
$filename = "Income Statement.pdf";
$dompdf->stream($filename, array("Attachment" => false));

?>
