<?php 
// This page aims at generating an income statement report
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/libraries/vendor/autoload.php');

function chooseBasedOnEnvelope($include_envelope, $valueIfYes, $valueIfNo) {
    return $include_envelope === "yes" ? $valueIfYes : $valueIfNo;
}


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

// Extract account_id
$account_id = isset($_GET['account_id']) ? filter_var($_GET['account_id'], FILTER_VALIDATE_INT) : null;

if (!$account_id) {
    header("Location: /error.php?message=" . urlencode("Invalid account ID."));
    exit();
}

// Extract include_envelope parameter
$include_envelope = isset($_GET['include_envelope']) ? filter_var($_GET['include_envelope'], FILTER_SANITIZE_STRING) : 'no';

// Define constants for the current and previous year
define('CURRENT_YEAR', $year);
define('PREVIOUS_YEAR', CURRENT_YEAR - 1);

// Fetch revenue group financials for the targeted account and year
$previousYearData = getHeadParishRevenueBySubParish($head_parish_id, PREVIOUS_YEAR, $account_id, $conn);
$currentYearRevenueData = getHeadParishRevenueBySubParish($head_parish_id, CURRENT_YEAR, $account_id, $conn);
$total_revenues = calculateTotalRevenue($currentYearRevenueData);
$envelopeData = getEnvelopeContributionsBySubParish($head_parish_id, CURRENT_YEAR, $conn);
$bankAccountsData = getAccountWiseFinancialSummary($head_parish_id, CURRENT_YEAR, $conn);
$subParishesRevenuesData = getSubParishYearlyFinancials($head_parish_id, CURRENT_YEAR, $conn);
$overBudgetExpenseData = getOverBudgetExpenseNames($head_parish_id, CURRENT_YEAR, $conn);
$debtsData = getUnpaidDebtsByYear($head_parish_id, CURRENT_YEAR, $conn);
$accountName = getAccountNameById($account_id, $head_parish_id, $conn);
$itemsPerBatch = 3;

// Prepare HTML content
$html = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/png" href="/assets/images/logos/favicon.png" />
    <title>Revenue Breakdown</title>
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
        
        .text-bold{
            font-weight: bold;
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
    </div>';

// Check if there's any data to process
if (empty($currentYearRevenueData) || !array_filter($currentYearRevenueData, fn($rev) => !empty($rev['items']))) {
    // If there's no data, print a table with a "No data available" message
    $html .= '<table border="1" cellpadding="5" cellspacing="0">
                <tr>
                    <th class="section-color" style="width:3%">E</th>
                    <th colspan="7" class="text-left header-row-bg">HEAD PARISH '.$accountName.'  REVENUE BREAKDOWN BY SUB-PARISH</th>
                </tr>
                <tr>
                    <td colspan="8" style="text-align:center;">No data available</td>
                </tr>
            </table>';
} else {
    // Calculate the maximum number of items in any revenue (to handle the dynamic rows)
    $maxItems = max(array_map(fn($rev) => count($rev['items']), $currentYearRevenueData));

    // Start printing the table with the header
    $html .= '<table border="1" cellpadding="5" cellspacing="0">
                <tr>
                    <th class="section-color" style="width:3%">E</th>
                    <th colspan="7" class="text-left header-row-bg">HEAD PARISH '.$accountName.'  REVENUE BREAKDOWN BY SUB-PARISH</th>
                </tr>
            </table>';

    // Print tables in batches of $itemsPerBatch
    for ($start = 0; $start < $maxItems; $start += $itemsPerBatch) {
        $html .= '<table border="1" cellpadding="5" cellspacing="0">';
        $html .= '<tr><th style="width:3%">No.</th><th>REVENUE NAME</th>';

        // Print actual item names as headers
        for ($i = 0; $i < $itemsPerBatch; $i++) {
            $itemIndex = $start + $i;
            $itemName = '';
            foreach ($currentYearRevenueData as $revenue) {
                if (isset($revenue['items'][$itemIndex])) {
                    $itemName = $revenue['items'][$itemIndex]['name'];
                    break; // Get the name from the first available revenue
                }
            }
            $html .= '<th>' . ($itemName ?: '') . '</th>';  // Use item name or leave blank
        }

        $html .= '<th>TOTAL</th><th>%</th></tr>';

        // Initialize an array to accumulate totals for each column (including sub_parish_id)
        $columnTotals = array_fill(0, $itemsPerBatch, ['total' => 0, 'id' => null]);
        $totalRow = 0; // Variable to accumulate the total of the "Total" column

        // Loop through each revenue and print data
        foreach ($currentYearRevenueData as $index => $revenue) {
            $rowTotal = 0; // Initialize total for the current row
            $html .= '<tr class="text-center">';
            $html .= '<td>' . ($index + 1) . '</td>';
            $html .= '<td style="width:20%" class="text-left">' . $revenue['name'] . '</td>';

            // Print item values for the current batch and calculate the total
            for ($i = 0; $i < $itemsPerBatch; $i++) {
                $itemIndex = $start + $i;
                $itemValue = isset($revenue['items'][$itemIndex]) ? $revenue['items'][$itemIndex]['value'] : 0;
                $subParishId = isset($revenue['items'][$itemIndex]) ? $revenue['items'][$itemIndex]['id'] : null;
                $rowTotal += $itemValue; // Add value to the row total
                $columnTotals[$i]['total'] += $itemValue; // Accumulate column total
                if ($columnTotals[$i]['id'] === null) {
                    $columnTotals[$i]['id'] = $subParishId; // Set sub-parish ID for the first row
                }
                $html .= '<td style="width:15.4%">' . (number_format($itemValue, 0) ?: '') . '</td>'; // Print item value or leave blank
            }

            $html .= '<td style="width:15.4%">' . number_format($rowTotal, 0) . '</td>';
            $totalRow += $rowTotal; // Accumulate the total for the "Total" column
            $rowPercentage = ($total_revenues > 0) ? ($rowTotal / $total_revenues) * 100 : 0.00;
            $html .= '<td style="width:15.4%">'.number_format($rowPercentage, 0).' %</td>'; // Placeholder for % column
            $html .= '</tr>';
        }

        // Step 1: Calculate Uwiano for each sub_parish_id and store in an array
        $calculatedUwianoValues = [];
        
        // Assuming you have already calculated the column totals and stored them in $columnTotals
        foreach ($columnTotals as $columnTotal) {
            // Calculate Uwiano for the current column (you need to define this calculation based on your logic)
            $uwianoValue = getHeadParishRevenueTargetAmountBySubParish($head_parish_id, $columnTotal['id'], CURRENT_YEAR, $conn); 
            $calculatedUwianoValues[$columnTotal['id']] = $uwianoValue;
        }
        
        // Step 2: Add the total row for each column
        $html .= '<tr><td colspan="2" class="text-left"><strong>TOTAL</strong></td>';
        foreach ($columnTotals as $columnTotal) {
            $html .= '<td class="text-center text-bold" data-sub-parish-id="' . $columnTotal['id'] . '">' . number_format($columnTotal['total'], 0) . '</td>';
        }
        $html .= '<td class="text-center text-bold">' . number_format($totalRow, 0) . '</td>'; // Total for the "Total" column
        $html .= '<td rowspan="4" style="background-color:#eee"></td></tr>'; // Empty cell for the % column
        
        // Step 3: Add the three rows using the pre-calculated Uwiano values
        $html .= '<tr class="text-center text-bold">';
        $html .= '<td colspan="2" class="text-left">PROPORTION</td>';
        $totalUwiano = 0;
        foreach ($columnTotals as $columnTotal) {
            // Use the pre-calculated Uwiano value
            $uwianoValue = $calculatedUwianoValues[$columnTotal['id']];
            $totalUwiano += $uwianoValue;
            $html .= '<td class="text-center">' . number_format($uwianoValue, 0) . '</td>';
        }
        $html .= '<td>'.number_format($totalUwiano, 0).'</td>';
        $html .= '</tr>';
        
        $html .= '<tr class="text-center text-bold">';
        $html .= '<td colspan="2" class="text-left">DIFFERENCE / DEFICIT</td>';
        $totalUpungufu = 0;
        foreach ($columnTotals as $columnTotal) {
            // Calculate Tofauti as the difference between Uwiano and column total
            $uwianoValue = $calculatedUwianoValues[$columnTotal['id']];
            $tofautiValue = $uwianoValue - $columnTotal['total']; // Tofauti is the difference
            $totalUpungufu += $tofautiValue;
            $html .= '<td class="text-center">' . number_format($tofautiValue, 0) . '</td>';
        }
        $html .= '<td>'.number_format($totalUpungufu, 0).'</td>';
        $html .= '</tr>';
        
        $html .= '<tr class="text-center text-bold">';
        $html .= '<td colspan="2" class="text-left">DEFICIT RATE BY %</td>';
        foreach ($columnTotals as $columnTotal) {
            // Calculate Percentage as (Uwiano / Column Total) * 100
            $uwianoValue = $calculatedUwianoValues[$columnTotal['id']];
            $percentageValue = ($columnTotal['total'] > 0) ? (($uwianoValue - $columnTotal['total']) / $uwianoValue) * 100 : 0; // Avoid division by zero
            $html .= '<td class="text-center">' . number_format($percentageValue, 0) . ' %</td>';
        }
        $finalPercentage = ($totalUwiano > 0) ? ($totalUpungufu / $totalUwiano) * 100 : 0.00;
        $html .= '<td>'.number_format($finalPercentage, 0).'%</td>';
        $html .= '</tr>';
        
        $html .= '</table>';


    }
}

if($include_envelope == 'yes'){

// ENVELOPES
$html .= '<div class="table-container">
    <table border="1" cellpadding="5" cellspacing="0">
        <thead> 
            <tr>
                <th class="section-color" style="width:3%">F</th>
                <th colspan="6" class="text-left header-row-bg">ENVELOPE TARGETS AND CONTRIBUTION BREAKDOWN BY SUB-PARISH</th>
            </tr>
            <tr>
                <th style="width:3%">No.</th>
                <th style="width:20%">SUB PARISH</th>
                <th style="width:15.4%">OVERALL TARGET</th>
                <th style="width:15.4%">TOTAL CONTRIBUTION</th>
                <th style="width:15.4%">BALANCE</th>
                <th style="width:15.4%">ACHIEVEMENT</th>
                <th style="width:15.4%">ANNUAL / WEEKLY AVERAGE</th>
            </tr>
        </thead>
        <tbody>';

            $totalTarget = 0;
            $grandTotalContribution = 0;
            $totalBalance = 0;
            $totalPercentageContribution = 0;
            $totalAnnualAverage = 0;
            $totalWeeklyAverage = 0;
            
            // Loop through the fetched data (assuming $envelopeData contains the data)
            $no = 1;
            foreach ($envelopeData as $data) {
                $overallTarget = $data['total_target'];  
                $totalContribution = $data['total_contribution'];
                $percentageContribution = $overallTarget > 0 ? ($totalContribution / $overallTarget) * 100 : 0; 
                $annualAverage = ($data['total_envelopes'] != 0) ? $totalContribution / $data['total_envelopes'] : 0;
                $weeklyAverage = ($data['total_envelopes'] != 0) ? $totalContribution / (52 * $data['total_envelopes']) : 0;

                $balance =   $data['total_target'] -  $data['total_contribution'];
                $cell_style = ($balance < 0 ) ? "color:red;" : "color: black;";
                
                // Add to grand totals
                $totalTarget += $overallTarget;
                $grandTotalContribution += $totalContribution;
                $totalBalance += $balance;
                $totalAnnualAverage += $annualAverage;
                $totalWeeklyAverage += $weeklyAverage;

                // Fill the table rows with the calculated data
                $html .= '<tr class="text-center">
                    <td>' . $no++ . '</td>
                    <td class="text-left">' . $data['sub_parish_name'] . ' ( '. $data['total_envelopes'] .' Envelopes)</td>
                    <td>' . number_format($overallTarget, 0) . '</td>
                    <td>' . number_format($totalContribution, 0) . '</td>
                    <td style="' . $cell_style . '">' . number_format($balance, 0) . '</td>
                    <td>' . number_format($percentageContribution, 0) . '%</td>
                    <td>' . number_format($annualAverage, 0) . ' / ' . number_format($weeklyAverage, 0) . '</td>
                </tr>';
            }
            
            $totalPercentageContribution = $totalTarget > 0 ? ($grandTotalContribution / $totalTarget) * 100 : 0;
            
            // Add grand total row
            $html .= '<tr class="text-center" style="font-weight: bold;">
                <td colspan="2" class="text-left">GRAND TOTAL</td>
                <td>' . number_format($totalTarget, 0) . '</td>
                <td>' . number_format($grandTotalContribution, 0) . '</td>
                <td>' . number_format($totalBalance, 0) . '</td>
                <td>' . number_format($totalPercentageContribution / $no, 0) . '%</td>
                <td>' . number_format($totalAnnualAverage / $no, 0) . ' / ' . number_format($totalWeeklyAverage / $no, 0) . '</td>
            </tr>';

$html .= '</tbody>
    </table>
</div>';

}

// BANK ACCOUNT BALANCES
$html .= '<div class="table-container">
    <table border="1" cellpadding="5" cellspacing="0">
        <thead> 
            <tr>
                <th class="section-color" style="width:3%">' . chooseBasedOnEnvelope($include_envelope, 'G', 'F') . '</th>
                <th colspan="6" class="text-left header-row-bg">HEAD PARISH REVENUES AND EXPENDITURES BY ACCOUNT</th>
            </tr>
            <tr>
                <th style="width:3%">No.</th>
                <th style="width:20%">ACCOUNT NAME</th>
                <th style="width:15.4%">OPENING BALANCE</th>
                <th style="width:15.4%">ANNUAL REVENUE</th>
                <th style="width:15.4%">TOTAL</th>
                <th style="width:15.4%">TOTAL EXPENSES</th> 
                <th style="width:15.4%">ACCOUNT BALANCE</th>
            </tr>
        </thead>
        <tbody>';

        $grandTotalOpeningBalance = 0;
        $grandTotalRevenue = 0;
        $grandTotalTotal = 0;
        $grandTotalExpenses = 0;
        $grandTotalAccountBalance = 0;
        $no = 1;

        // Loop through the fetched data (assuming $bankAccountsData contains the data)
        foreach ($bankAccountsData as $data) {
            $accountId = $data['account_id'];
            $accountName = $data['account_name'];
            $openingBalance = $data['opening_balance'];
            $totalRevenue = $data['total_revenue_collected'];
            $totalExpense = $data['total_approved_expenses'];

            $total = $openingBalance + $totalRevenue;
            $accountBalance = $total - $totalExpense;

            // Add values to the grand totals
            $grandTotalOpeningBalance += $openingBalance;
            $grandTotalRevenue += $totalRevenue;
            $grandTotalTotal += $total;
            $grandTotalExpenses += $totalExpense;
            $grandTotalAccountBalance += $accountBalance;

            // Fill the table rows with the calculated data
            $html .= '<tr class="text-center">
                <td>' . $no++ . '</td>
                <td class="text-left">' . $accountName . '</td>
                <td>' . number_format($openingBalance, 0) . '</td>
                <td>' . number_format($totalRevenue, 0) . '</td>
                <td>' . number_format($total, 0) . '</td>
                <td>' . number_format($totalExpense, 0) . '</td>
                <td>' . number_format($accountBalance, 0) . '</td>
            </tr>';
        }

        // Grand total row
        $html .= '<tr class="text-center text-bold">
            <td colspan="2"><strong>GRAND TOTAL</strong></td>
            <td>' . number_format($grandTotalOpeningBalance, 0) . '</td>
            <td>' . number_format($grandTotalRevenue, 0) . '</td>
            <td>' . number_format($grandTotalTotal, 0) . '</td>
            <td>' . number_format($grandTotalExpenses, 0) . '</td>
            <td>' . number_format($grandTotalAccountBalance, 0) . '</td>
        </tr>';

$html .= '</tbody>
    </table>
</div>';


// SUB PARISH REVENUES
$html .= '<div class="table-container">
    <table border="1" cellpadding="5" cellspacing="0">
        <thead> 
            <tr>
                <th class="section-color" style="width:3%">' . chooseBasedOnEnvelope($include_envelope, 'H', 'G') . '</th>
                <th colspan="6" class="text-left header-row-bg">SUB-PARISH REVENUE AND EXPENDITURE ACCOUNTS</th>
            </tr>
            <tr>
                <th style="width:3%">No.</th>
                <th style="width:20%">SUB PARISH NAME</th>
                <th style="width:15.4%">OPENING BALANCE</th>
                <th style="width:15.4%">ANNUAL REVENUE</th>
                <th style="width:15.4%">TOTAL</th>
                <th style="width:15.4%">TOTAL EXPENSES</th> 
                <th style="width:15.4%">BALANCE</th>
            </tr>
        </thead>
        <tbody>';

        $grandTotalPreviousYearRevenue = 0;
        $grandTotalCurrentYearRevenue = 0;
        $grandTotalTotal = 0;
        $grandTotalExpenses = 0;
        $grandTotalBalance = 0;
        $no = 1;

        // Loop through the fetched data (assuming $subParishesRevenuesData contains the data)
        foreach ($subParishesRevenuesData as $data) {
            $subParishId = $data['sub_parish_id'];
            $subParishName = $data['sub_parish_name'];
            $previousYearRevenue = $data['previous_year_revenue'];
            $currentYearRevenue = $data['current_year_revenue'];
            $totalApprovedExpenses = $data['total_approved_expenses'];

            // Calculate the total and balance
            $total = $previousYearRevenue + $currentYearRevenue;
            $balance = $total - $totalApprovedExpenses;

            // Add values to the grand totals
            $grandTotalPreviousYearRevenue += $previousYearRevenue;
            $grandTotalCurrentYearRevenue += $currentYearRevenue;
            $grandTotalTotal += $total;
            $grandTotalExpenses += $totalApprovedExpenses;
            $grandTotalBalance += $balance;

            // Fill the table rows with the calculated data
            $html .= '<tr class="text-center">
                <td>' . $no++ . '</td>
                <td class="text-left">' . $subParishName . '</td>
                <td>' . number_format($previousYearRevenue, 0) . '</td>
                <td>' . number_format($currentYearRevenue, 0) . '</td>
                <td>' . number_format($total, 0) . '</td>
                <td>' . number_format($totalApprovedExpenses, 0) . '</td>
                <td>' . number_format($balance, 0) . '</td>
            </tr>';
        }

        // Grand total row
        $html .= '<tr class="text-center text-bold">
            <td colspan="2"><strong>GRAND TOTAL</strong></td>
            <td>' . number_format($grandTotalPreviousYearRevenue, 0) . '</td>
            <td>' . number_format($grandTotalCurrentYearRevenue, 0) . '</td>
            <td>' . number_format($grandTotalTotal, 0) . '</td>
            <td>' . number_format($grandTotalExpenses, 0) . '</td>
            <td>' . number_format($grandTotalBalance, 0) . '</td>
        </tr>';

$html .= '</tbody>
    </table>
</div>';

$html .= '<div class="table-container">
    <table border="1" cellpadding="5" cellspacing="0">
        <thead> 
            <tr>
                <th class="section-color" style="width:3%">' . chooseBasedOnEnvelope($include_envelope, 'I', 'H') . '</th>
                <th colspan="6" class="text-left header-row-bg">OVER BUDGET EXPENSES</th>
            </tr>
            <tr>
                <th style="width:3%">No.</th>
                <th style="width:20%">EXPENSE NAME</th>
                <th style="width:15.4%">EXPENSE BUDGET</th>
                <th style="width:15.4%">TOTAL EXPENSE</th>
                <th style="width:15.4%">BALANCE</th>
                <th style="width:15.4%">ANNUAL BALANCE</th>
                <th style="width:15.4%">OVER BUDGET STATUS</th>
            </tr>
        </thead>
        <tbody>';

        $grandBudget = 0;
        $grandExpense = 0;
        $grandAnnualBalance = 0;
        $no = 1;

        foreach ($overBudgetExpenseData as $row) {
            $expenseName = $row['expense_name'];
            $budgetAmount = $row['budget_amount'];
            $totalExpense = $row['total_expense'];
            $balance = $row['balance'];
            $annualBalance = $row['annual_balance'];
            $grandAnnualBalance += $annualBalance;
            $impact = $row['impact'];

            $grandBudget += $budgetAmount;
            $grandExpense += $totalExpense;
            $percentage = $row['over_budget_percentage'];
            $absPercentage = number_format(abs($percentage), 0);
            $color = $percentage < 0 ? 'green' : ($percentage > 0 ? 'red' : 'black');
            $html .= '<tr class="text-center">
                <td>' . $no++ . '</td>
                <td class="text-left">' . $expenseName . '</td>
                <td>' . number_format($budgetAmount, 0) . '</td>
                <td>' . number_format($totalExpense, 0) . '</td>
                <td>' . number_format($balance, 0) . '</td>
                <td>' . number_format($annualBalance, 0) . '</td>
                <td style="color: ' . $color . ';">' . 
                    ($percentage >= 0 ? number_format($absPercentage, 0) . '% - ' : '') . 
                    $impact . 
                '</td>
            </tr>';

        }

        $html .= '<tr class="text-center">
            <td colspan="2"><strong>GRAND TOTAL</strong></td>
            <td class="text-bold">' . number_format($grandBudget, 0) . '</td>
            <td class="text-bold">' . number_format($grandExpense, 0) . '</td>
            <td class="text-bold">' . number_format($grandBudget - $grandExpense, 0) . '</td>
            <td class="text-bold">' . number_format($grandAnnualBalance, 0) . '</td>
            <td></td>
        </tr>';

$html .= '</tbody>
    </table>
</div>';


// DEBTS
$html .= '<div class="table-container">
    <table border="1" cellpadding="5" cellspacing="0">
        <thead> 
            <tr>
                <th class="section-color" style="width:3%">' . chooseBasedOnEnvelope($include_envelope, 'J', 'I') . '</th>
                <th colspan="5" class="text-left header-row-bg">INTERNAL HEAD PARISH DEBTS</th>
            </tr>
            <tr>
                <th style="width:3%">No.</th>
                <th style="width:35.4%">DESCRIPTION</th>
                <th style="width:15.4%">PURPOSE</th>
                <th style="width:15.4%">AMOUNT</th>
                <th style="width:15.4%">DATE DEBITED</th>
                <th style="width:15.4%">RETURN BEFORE DATE</th> 
            </tr>
        </thead>
        <tbody>';

        $grandTotalAmount = 0;
        $no = 1;

        // Loop through the fetched data (assuming $debtsData contains the data)
        foreach ($debtsData as $data) {
            $debtName = $data['debt_name'];
            $amount = $data['amount'];
            $purpose = $data['purpose'];
            $dateDebited = $data['date_debited'];
            $returnBeforeDate = $data['return_before_date'];
            $dateDebitedFormatted = date("d F Y", strtotime($data['date_debited']));
            $returnBeforeDateFormatted = date("d F Y", strtotime($data['return_before_date']));

            // Add the amount to the grand total
            $grandTotalAmount += $amount;

            // Fill the table rows with the data
            $html .= '<tr class="text-center">
                <td>' . $no++ . '</td>
                <td class="text-left">' . $debtName . '</td>
                <td>' . $purpose . '</td>
                <td>' . number_format($amount, 0) . '</td>
                <td>' . $dateDebitedFormatted . '</td>
                <td>' . $returnBeforeDateFormatted . '</td>
            </tr>';
        }

        // Grand total row
        $html .= '<tr class="text-center">
            <td colspan="3"><strong>GRAND TOTAL</strong></td>
            <td class="text-bold">' . number_format($grandTotalAmount, 0) . '</td>
            <td colspan="2"></td> <!-- Empty cells for Date and Return Before Date -->
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