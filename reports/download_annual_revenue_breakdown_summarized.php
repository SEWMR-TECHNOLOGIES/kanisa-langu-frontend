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
$adultBenchmark = $attendanceBenchmark["adult_reading"];
$childBenchmark = $attendanceBenchmark["child_reading"];

// Calculate the percentage of previous year attendance compared to benchmark
$previousYearAdultPercentage = ($adultBenchmark > 0) ? round(($previousYearAdultAttendance / $adultBenchmark) * 100, 0) : 0;
$previousYearChildrenPercentage = ($childBenchmark > 0) ? round(($previousYearChildrenAttendance / $childBenchmark) * 100, 0) : 0;

// Calculate the percentage of current year attendance compared to benchmark
$currentYearAdultPercentage = ($adultBenchmark > 0) ? round(($currentYearAdultAttendance / $adultBenchmark) * 100, 0) : 0;
$currentYearChildrenPercentage = ($childBenchmark > 0) ? round(($currentYearChildrenAttendance / $childBenchmark) * 100, 0) : 0;

// Calculate the difference between current year attendance and benchmark
$currentYearAdultDifference = $currentYearAdultAttendance - $adultBenchmark;
$currentYearChildrenDifference = $currentYearChildrenAttendance - $childBenchmark;

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

$currentYearRevenueTotalB1 = calculateTotalAmount($currentYearData, 'total_revenue_collected');

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
    </style>
</head>
<body>
    <div class="container">
        <div class="header" style="text-align: center; margin-bottom: 5px;">
            <h1 style="font-size: 10px; color: #3498db; margin: 0;">K.K.K.T ' . $parish_info['diocese_name'] .' | ' . $parish_info['province_name'] . ' | ' . $parish_info['head_parish_name'] . '</h1>
            <h1 style="font-size: 10px; color: #2c3e50; margin: 2px 0;"> TAARIFA YA MAPATO, MATUMIZI NA MALI ZA USHARIKA KWA MWAKA '.'<span class="date">' .CURRENT_YEAR . '</span></h1>
        </div>
    </div>

<div class="table-container">
        <table border="1" cellpadding="5" cellspacing="0">
            <thead> 
                <tr>
                    <th class="section-color" style="width:3%">A</th>
                    <th colspan="9" class="text-left header-row-bg">MAHUDHURIO</th>
                </tr>
                <tr>
                    <th rowspan="2">Na.</th>
                    <th rowspan="2" style="width:20%">Maelezo</th>
                    <th colspan="2" style="width:20%">'.PREVIOUS_YEAR.'</th>
                    <th colspan="2" style="width:20%">'.CURRENT_YEAR.'</th>
                    <th colspan="2" style="width:20%">MAFANIKIO</th>
                    <th style="width:6%">UWIANO</th>
                    <th style="width:11%" rowspan="2">TOFAUTI ' .CURRENT_YEAR.'</th>
                </tr>
                <tr>
                    <th style="width:10%">BAJETI</th>
                    <th style="width:10%">HALISI</th>
                    <th style="width:10%">BAJETI</th>
                    <th style="width:10%">HALISI</th>
                    <th style="width:10%">'.PREVIOUS_YEAR.'</th>
                    <th style="width:10%">'.CURRENT_YEAR.'</th>
                    <th style="width:6%">'.CURRENT_YEAR.'</th>
                </tr>
            </thead>
            <tbody>
            <tr class="text-center">
                <td class="text-center">1</td>
                <td class="text-left">Watu wazima</td>
                <td class="column-bg-color">'.$adultBenchmark.'</td>
                <td class="column-bg-color">'.$previousYearAdultAttendance.'</td>
                <td>'.$adultBenchmark.'</td>
                <td>'.$currentYearAdultAttendance.'</td>
                <td class="column-bg-color">'.$previousYearAdultPercentage.'%</td>
                <td>'.$currentYearAdultPercentage.'%</td>
                <td>-</td>
                <td class="text-center" style="color:'.($currentYearAdultDifference < 0 ? 'red' : 'black').'">
                    '.($currentYearAdultDifference).'
                </td>
            </tr>
            <tr class="text-center">
                <td class="text-center">2</td>
                <td class="text-left">Watoto</td>
                <td class="column-bg-color">'.$childBenchmark.'</td>
                <td class="column-bg-color">'.$previousYearChildrenAttendance.'</td>
                <td>'.$childBenchmark.'</td>
                <td>'.$currentYearChildrenAttendance.'</td>
                <td class="column-bg-color">'.$previousYearChildrenPercentage.'%</td>
                <td>'.$currentYearChildrenPercentage.'%</td>
                 <td>-</td>
                <td class="text-center" style="color:'.($currentYearChildrenDifference < 0 ? 'red' : 'black').'">
                    '.($currentYearChildrenDifference).'
                </td>
            </tr>';

$html .= '</tbody>
        </table>
    </div>';

$html .= '<div class="table-container">
        <table border="1" cellpadding="5" cellspacing="0">
            <thead> 
                <tr>
                    <th class="section-color" style="width:3%">B1</th>
                    <th colspan="9" class="text-left header-row-bg">MAPATO YA USHARIKA</th>
                </tr>
            </thead>
            <tbody>';

// Loop through current year groups and find corresponding previous year data

$grandTotalPrevBudget = 0;
$grandTotalPrevRevenue = 0;
$grandTotalCurrBudget  = 0;
$grandTotalCurrRevenue = 0;

// Group revenue groups by account_name
$groupedByAccount = [];
foreach ($currentYearMap as $groupId => $group) {
    $accountName = $group['account_name'] ?? 'N/A';
    $groupedByAccount[$accountName][] = $group;
}

$counter = 1;
// Initialize grand totals for all sections
$grandTotalPrevBudget = 0;
$grandTotalPrevRevenue = 0;
$grandTotalCurrBudget = 0;
$grandTotalCurrRevenue = 0;

// Initialize overall totals for Section B
$sectionBPrevBudget = 0;
$sectionBPrevRevenue = 0;
$sectionBCurrBudget = 0;
$sectionBCurrRevenue = 0;

// Loop through each account group
foreach ($groupedByAccount as $accountName => $groups) {
    // Initialize account totals inside the loop
    $accountPrevBudget = 0;
    $accountPrevRevenue = 0;
    $accountCurrBudget = 0;
    $accountCurrRevenue = 0;

    // Loop through revenue groups under this account
    foreach ($groups as $group) {
        $groupId = $group['revenue_group_id'];
        $prev = $previousYearMap[$groupId] ?? ['total_budget' => 0, 'total_revenue_collected' => 0];

        // Extract values
        $prevBudget = $prev['total_budget'];
        $prevRevenue = $prev['total_revenue_collected'];
        $currBudget = $group['total_budget'];
        $currRevenue = $group['total_revenue_collected'];

        // Calculate percentages
        $prevSuccessRate = ($prevBudget > 0) ? ($prevRevenue / $prevBudget) * 100 : 0;
        $currSuccessRate = ($currBudget > 0) ? ($currRevenue / $currBudget) * 100 : 0;
        $successRatioB1 = ($currentYearRevenueTotalB1 > 0) ? ($currRevenue / $currentYearRevenueTotalB1) * 100 : 0; 
        // Difference in amount between budget and revenue for current year
        $budgetRevenueDiff = $currRevenue - $currBudget;

        // Sum up totals for this account
        $accountPrevBudget += $prevBudget;
        $accountPrevRevenue += $prevRevenue;
        $accountCurrBudget += $currBudget;
        $accountCurrRevenue += $currRevenue;

        // Append row
        $html .= '<tr class="text-center">
                    <td class="text-center">' . $counter++ . '</td>
                    <td class="text-left" style="width:20%">' . htmlspecialchars($group['revenue_group_name']) . '</td>
                    <td style="width:10%" class="column-bg-color">' . number_format($prevBudget, 0) . '</td>
                    <td style="width:10%" class="column-bg-color">' . number_format($prevRevenue, 0) . '</td>
                    <td style="width:10%">' . number_format($currBudget, 0) . '</td>
                    <td style="width:10%">' . number_format($currRevenue, 0) . '</td>
                    <td style="width:10%" class="column-bg-color">' . number_format($prevSuccessRate, 0) . '%</td>
                    <td style="width:10%">' . number_format($currSuccessRate, 0) . '%</td>
                    <td style="width:6%">' . number_format($successRatioB1, 0) . '%</td>
                    <td style="color:' . ($budgetRevenueDiff < 0 ? 'red' : 'black') . '">
                        ' . number_format(($budgetRevenueDiff), 0) . '
                    </td>
                </tr>';
    }

    // Calculate account success rates
    $accountPrevSuccessRate = ($accountPrevBudget > 0) ? ($accountPrevRevenue / $accountPrevBudget) * 100 : 0;
    $accountCurrSuccessRate = ($accountCurrBudget > 0) ? ($accountCurrRevenue / $accountCurrBudget) * 100 : 0;
    $successRatioAccountB1 = ($currentYearRevenueTotalB1 > 0) ? ($accountCurrRevenue / $currentYearRevenueTotalB1) * 100 : 0; 
    $accountBudgetRevenueDiff = $accountCurrRevenue - $accountCurrBudget;

    // Append subtotal row for the account
    $html .= '<tr class="subtotal-row text-center">
                <td colspan="2" class="text-left"><strong>Jumla - ' . htmlspecialchars($accountName) . '</strong></td>
                <td class="column-bg-color"><strong>' . number_format($accountPrevBudget, 0) . '</strong></td>
                <td class="column-bg-color"><strong>' . number_format($accountPrevRevenue, 0) . '</strong></td>
                <td><strong>' . number_format($accountCurrBudget, 0) . '</strong></td>
                <td><strong>' . number_format($accountCurrRevenue, 0) . '</strong></td>
                <td class="column-bg-color"><strong>' . number_format($accountPrevSuccessRate, 0) . '%</strong></td>
                <td><strong>' . number_format($accountCurrSuccessRate, 0) . '%</strong></td>
                <td style="width:6%"><strong>' . number_format($successRatioAccountB1, 0) . '%</strong></td>
                <td style="color:' . ($accountBudgetRevenueDiff < 0 ? 'red' : 'black') . '">
                    <strong>' . number_format(($accountBudgetRevenueDiff), 0) . '</strong>
                </td>
            </tr>';

    // Add to Section B totals
    $sectionBPrevBudget += $accountPrevBudget;
    $sectionBPrevRevenue += $accountPrevRevenue;
    $sectionBCurrBudget += $accountCurrBudget;
    $sectionBCurrRevenue += $accountCurrRevenue;

    // Add to Grand Totals
    $grandTotalPrevBudget += $accountPrevBudget;
    $grandTotalPrevRevenue += $accountPrevRevenue;
    $grandTotalCurrBudget += $accountCurrBudget;
    $grandTotalCurrRevenue += $accountCurrRevenue;
}

// Calculate Section B success rates
$sectionBPrevSuccessRate = ($sectionBPrevBudget > 0) ? ($sectionBPrevRevenue / $sectionBPrevBudget) * 100 : 0;
$sectionBCurrSuccessRate = ($sectionBCurrBudget > 0) ? ($sectionBCurrRevenue / $sectionBCurrBudget) * 100 : 0;
$successRatioAllAccountB1 = ($currentYearRevenueTotalB1 > 0) ? ($sectionBCurrRevenue / $currentYearRevenueTotalB1) * 100 : 0; 
$sectionBBudgetRevenueDiff = $sectionBCurrRevenue - $sectionBCurrBudget;

// Append grand total row for Section B
$html .= '<tr class="total-row text-center">
            <td colspan="2" class="text-left"><strong>Jumla Kuu</strong></td>
            <td class="column-bg-color"><strong>' . number_format($sectionBPrevBudget, 0) . '</strong></td>
            <td class="column-bg-color"><strong>' . number_format($sectionBPrevRevenue, 0) . '</strong></td>
            <td><strong>' . number_format($sectionBCurrBudget, 0) . '</strong></td>
            <td><strong>' . number_format($sectionBCurrRevenue, 0) . '</strong></td>
            <td class="column-bg-color"><strong>' . number_format($sectionBPrevSuccessRate, 0) . '%</strong></td>
            <td><strong>' . number_format($sectionBCurrSuccessRate, 0) . '%</strong></td>
            <td style="width:6%"><strong>' . number_format($successRatioAllAccountB1, 0) . '%</strong></td>
            <td style="color:' . ($sectionBBudgetRevenueDiff < 0 ? 'red' : 'black') . '">
                <strong>' . number_format(($sectionBBudgetRevenueDiff), 0) . '</strong>
            </td>
        </tr>';

// Finalize table
$html .= '</tbody></table></div>';



// Fetch revenue group financials for both years
$previousYearData = getRevenueDataByHeadParish($head_parish_id, PREVIOUS_YEAR, $conn);
$currentYearData = getRevenueDataByHeadParish($head_parish_id, CURRENT_YEAR, $conn);
$currentYearRevenueTotalB2 = calculateTotalAmount($currentYearData, 'total_revenue_collected');
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
                    <th colspan="9" class="text-left header-row-bg">MAPATO YA VIKUNDI, JUMUIYA NA MITAA</th>
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
    $successRatioB2 = ($currentYearRevenueTotalB2 > 0) ? ($currRevenue / $currentYearRevenueTotalB2) * 100 : 0;
    // Calculate difference in budget vs revenue for current year
    $budgetRevenueDiff = $currRevenue - $currBudget;

    // Sum up totals
    $totalPrevBudget += $prevBudget;
    $totalPrevRevenue += $prevRevenue;
    $totalCurrBudget += $currBudget;
    $totalCurrRevenue += $currRevenue;

    // Append row to HTML table
    $html .= '<tr class="text-center">
                <td class="text-center">' . $counter++ . '</td>
                <td class="text-left" style="width:20%">' . htmlspecialchars($current['category_name']) . '</td>
                <td style="width:10%" class="column-bg-color">' . number_format($prevBudget, 0) . '</td>
                <td style="width:10%" class="column-bg-color">' . number_format($prevRevenue, 0) . '</td>
                <td style="width:10%">' . number_format($currBudget, 0) . '</td>
                <td style="width:10%">' . number_format($currRevenue, 0) . '</td>
                <td style="width:10%" class="column-bg-color">' . number_format($prevSuccessRate, 0) . '%</td>
                <td style="width:10%">' . number_format($currSuccessRate, 0) . '%</td>
                <td style="width:6%">' . number_format($successRatioB2, 0) . '%</td>
                <td style="color:' . ($budgetRevenueDiff < 0 ? 'red' : 'black') . '">
                    ' . number_format(($budgetRevenueDiff), 0) . '
                </td>
            </tr>';
}

// Calculate total success rates
$totalPrevSuccessRate = ($totalPrevBudget > 0) ? ($totalPrevRevenue / $totalPrevBudget) * 100 : 0;
$totalCurrSuccessRate = ($totalCurrBudget > 0) ? ($totalCurrRevenue / $totalCurrBudget) * 100 : 0;
$totatSuccessRatioB2 = ($currentYearRevenueTotalB2 > 0) ? ($totalCurrRevenue / $currentYearRevenueTotalB2) * 100 : 0;

$grandTotalRevenues = $currentYearRevenueTotalB1 + $currentYearRevenueTotalB2;
// Calculate overall budget vs revenue difference
$totalBudgetRevenueDiff = $totalCurrRevenue - $totalCurrBudget;

$grandTotalPrevBudget += $totalPrevBudget;
$grandTotalPrevRevenue += $totalPrevRevenue;
$grandTotalCurrBudget += $totalCurrBudget;
$grandTotalCurrRevenue += $totalCurrRevenue;

// Calculate total success rates
$grandTotalPrevSuccessRate = ($grandTotalPrevBudget > 0) ? ($grandTotalPrevRevenue / $grandTotalPrevBudget) * 100 : 0;
$grandTotalCurrSuccessRate = ($grandTotalCurrBudget > 0) ? ($grandTotalCurrRevenue / $grandTotalCurrBudget) * 100 : 0;
$grandTotalSuccessRatio = ($grandTotalRevenues > 0) ? ($grandTotalCurrRevenue / $grandTotalRevenues) * 100 : 0;

// Calculate overall budget vs revenue difference
$grandTotalBudgetRevenueDiff = $grandTotalCurrRevenue - $grandTotalCurrBudget;

// Append total row
$html .= '<tr class="total-row text-center">
            <td colspan="2" class="text-left"><strong>Jumla</strong></td>
            <td class="column-bg-color"><strong>' . number_format($totalPrevBudget, 0) . '</strong></td>
            <td class="column-bg-color"><strong>' . number_format($totalPrevRevenue, 0) . '</strong></td>
            <td><strong>' . number_format($totalCurrBudget, 0) . '</strong></td>
            <td><strong>' . number_format($totalCurrRevenue, 0) . '</strong></td>
            <td class="column-bg-color"><strong>' . number_format($totalPrevSuccessRate, 0) . '%</strong></td>
            <td><strong>' . number_format($totalCurrSuccessRate, 0) . '%</strong></td>
            <td><strong>' . number_format($totatSuccessRatioB2, 0) . '%</strong></td>
            <td style="color:' . ($totalBudgetRevenueDiff < 0 ? 'red' : 'black') . '">
                <strong>' . number_format(($totalBudgetRevenueDiff), 0) . '</strong>
            </td>
        </tr>';

// Append total row
$html .= '<tr class="total-row text-center">
            <td colspan="2" class="text-left"><strong>Jumla Kuu ya Mapato</strong></td>
            <td class="column-bg-color"><strong>' . number_format($grandTotalPrevBudget, 0) . '</strong></td>
            <td class="column-bg-color"><strong>' . number_format($grandTotalPrevRevenue, 0) . '</strong></td>
            <td><strong>' . number_format($grandTotalCurrBudget, 0) . '</strong></td>
            <td><strong>' . number_format($grandTotalCurrRevenue, 0) . '</strong></td>
            <td class="column-bg-color"><strong>' . number_format($grandTotalPrevSuccessRate, 0) . '%</strong></td>
            <td><strong>' . number_format($grandTotalCurrSuccessRate, 0) . '%</strong></td>
            <td><strong>' . number_format($grandTotalSuccessRatio, 0) . '%</strong></td>
            <td style="color:' . ($grandTotalBudgetRevenueDiff < 0 ? 'red' : 'black') . '">
                <strong>' . number_format(($grandTotalBudgetRevenueDiff), 0) . '</strong>
            </td>
        </tr>';
$html .= '</tbody>
        </table>
    </div>';



// Fetch expense group financials for both years
$previousYearData = getExpenseDataByHeadParish($head_parish_id, PREVIOUS_YEAR, $conn);
$currentYearData = getExpenseDataByHeadParish($head_parish_id, CURRENT_YEAR, $conn);
$totalApprovedExpenses = calculateTotalAmount($currentYearData, 'total_approved_expenses');

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
                <th class="section-color" style="width:3%;">C</th>
                <th colspan="9" class="text-left header-row-bg" style="background-color:#FCF3E5;">MATUMIZI</th>
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
    $expenseRatio = ($totalApprovedExpenses > 0) ? ($currExpenses / $totalApprovedExpenses) * 100 : 0;

    // Difference in amount between budget and actual expenses for current year
    $budgetExpenseDiff = $currBudget - $currExpenses;

    // Sum up totals
    $totalPrevBudget += $prevBudget;
    $totalPrevExpenses += $prevExpenses;
    $totalCurrBudget += $currBudget;
    $totalCurrExpenses += $currExpenses;

    // Append row to table
    $html .= '<tr class="text-center">
                <td class="text-center">' . $counter++ . '</td>
                <td class="text-left" style="width:20%">' . htmlspecialchars($current['category_name']) . '</td>
                <td style="width:10%" class="column-bg-color">' . number_format($prevBudget, 0) . '</td>
                <td style="width:10%" class="column-bg-color">' . number_format($prevExpenses, 0) . '</td>
                <td style="width:10%">' . number_format($currBudget, 0) . '</td>
                <td style="width:10%">' . number_format($currExpenses, 0) . '</td>
                <td style="width:10%" class="column-bg-color">' . number_format($prevSuccessRate, 0) . '%</td>
                <td style="width:10%">' . number_format($currSuccessRate, 0) . '%</td>
                <td style="width:6%">' . number_format($expenseRatio, 0) . '%</td>
                <td style="color:' . ($budgetExpenseDiff < 0 ? 'red' : 'black') . '; width:11%">
                    ' . number_format(($budgetExpenseDiff), 0) . '
                </td>
            </tr>';
}

// Calculate total success rates
$totalPrevSuccessRate = ($totalPrevBudget > 0) ? ($totalPrevExpenses / $totalPrevBudget) * 100 : 0;
$totalCurrSuccessRate = ($totalCurrBudget > 0) ? ($totalCurrExpenses / $totalCurrBudget) * 100 : 0;
$grandTotalExpenseRatio = ($totalApprovedExpenses > 0) ? ($totalCurrExpenses / $totalApprovedExpenses) * 100 : 0;
// Calculate total budget vs expenses difference
$totalBudgetExpenseDiff = $totalCurrBudget - $totalCurrExpenses;

// Append total row
$html .= '<tr class="total-row text-center">
            <td colspan="2" class="text-left"><strong>Jumla</strong></td>
            <td class="column-bg-color"><strong>' . number_format($totalPrevBudget, 0) . '</strong></td>
            <td class="column-bg-color"><strong>' . number_format($totalPrevExpenses, 0) . '</strong></td>
            <td><strong>' . number_format($totalCurrBudget, 0) . '</strong></td>
            <td><strong>' . number_format($totalCurrExpenses, 0) . '</strong></td>
            <td class="column-bg-color"><strong>' . number_format($totalPrevSuccessRate, 0) . '%</strong></td>
            <td><strong>' . number_format($totalCurrSuccessRate, 0) . '%</strong></td>
            <td><strong>' . number_format($grandTotalExpenseRatio, 0) . '%</strong></td>
            <td style="color:' . ($totalBudgetExpenseDiff < 0 ? 'red' : 'black') . '">
                <strong>' . number_format(($totalBudgetExpenseDiff), 0) . '</strong>
            </td>
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
                <th colspan="9" class="text-left" style="width:97%">MALI ZA USHARIKA</th>
            </tr>
            <tr>
                <th style="width:3%">Na.</th>
                <th style="width:20%">Mali</th>
                <th style="width:11%">Hali</th>
                <th style="width:11%">Kuzalisha</th>
                <th style="width:11%">Kiasi</th>
                <th colspan="5">Maoni / Mapendekezo</th>
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
        $revenueDisplay = $revenue != '0.00' ? number_format($revenue, 0) : '-';
        $generatesRevenueDisplay = 'Ndio';

        // Add to total revenue if valid
        $totalRevenue += ($revenue != '0.00') ? $revenue : 0;
    } else {
        $revenueDisplay = 'Huduma';
    }

    // Append row to the table
    $html .= '<tr class="text-center">
                <td class="text-center">' . $counter++ . '</td>
                <td class="text-left">' . $assetName . '</td>
                <td>' . $status . '</td>
                <td>' . $generatesRevenueDisplay . '</td>
                <td>' . $revenueDisplay . '</td>
                <td colspan="5" class="text-left">' . $description . '</td>
            </tr>';
}

// Append total row
$html .= '<tr class="total-row">
            <td colspan="4"><strong>Jumla ya Mapato ya Mali</strong></td>
            <td class="text-center"><strong>' . number_format($totalRevenue, 0) . '</strong></td>
            <td colspan="5"></td>
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
$filename = CURRENT_YEAR." Annual Revenue Breakdown Statement.pdf";
$dompdf->stream($filename, array("Attachment" => false));

?>
