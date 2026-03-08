<?php
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/libraries/vendor/autoload.php');

// Assuming getExpenseGroupDetails is defined in another file or included earlier
// You can call it directly here.

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

// Initialize variables from GET parameters
$account_id = isset($_GET['account_id']) ? $_GET['account_id'] : null;
$target = isset($_GET['target']) ? $_GET['target'] : null;
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$sub_parish_id = isset($_GET['sub_parish_id']) ? $_GET['sub_parish_id'] : null;
$community_id = isset($_GET['community_id']) ? $_GET['community_id'] : null;
$group_id = isset($_GET['group_id']) ? $_GET['group_id'] : null;
$report_order = isset($_GET['report_order']) ? $_GET['report_order'] : 'default';
$order_column = isset($_GET['order_column']) ? $_GET['order_column'] : 'expense_identifier';
$client_datetime = isset($_GET['client_datetime']) ? $_GET['client_datetime'] : date('Y-m-d H:i:s');

// Format the date and time
$date = new DateTime($client_datetime);
$formatted_datetime = $date->format('d/m/Y h:i:s A');

// Define valid order columns
$valid_order_columns = ['expense_budget', 'total_expense_amount', 'annual_budget_balance', 'expenditure_percentage','expense_identifier'];

// Validate order_column
if (!in_array($order_column, $valid_order_columns)) {
    header("Location: /error.php?message=" . urlencode("Invalid order column provided."));
    exit;
}

// Validate account_id
if ($account_id) {
    try {
        // Attempt to decrypt the Bank Account ID
        $account_id = decryptData($account_id);

        // Further validation if necessary
        if (empty($account_id) || !preg_match('/^[a-zA-Z0-9]+$/', $account_id)) {
            header("Location: /error.php?message=" . urlencode("Invalid Bank Account ID."));
            exit;
        }
    } catch (Exception $e) {
        // Handle decryption failure
        header("Location: /error.php?message=" . urlencode("Error decrypting Bank Account ID: " . $e->getMessage()));
        exit;
    }
} else {
    // Handle case where Bank Account ID is not provided
    header("Location: /error.php?message=" . urlencode("Bank Account ID is required."));
    exit;
}

// Validate target
$valid_targets = ['head-parish', 'sub-parish', 'community', 'group'];
if (!in_array($target, $valid_targets)) {
    header("Location: /error.php?message=" . urlencode("Invalid target type provided."));
    exit;
}

// Validate report_order
$valid_report_orders = ['default', 'ascending', 'descending']; // Example valid report orders
if (!in_array($report_order, $valid_report_orders)) {
    header("Location: /error.php?message=" . urlencode("Invalid report order provided."));
    exit;
}

// Validate year
define('DEFAULT_YEAR', 2024);
if (!is_numeric($year) || $year < DEFAULT_YEAR || $year > date('Y')) {
    header("Location: /error.php?message=" . urlencode("Please select a valid year between " . DEFAULT_YEAR . " and the current year."));
    exit;
}

// Validation for sub-parish target
if ($target == 'sub-parish') {
    if (!$sub_parish_id) {
        header("Location: /error.php?message=" . urlencode("Sub Parish ID is required for sub-parish target."));
        exit;
    }
    try {
        // Attempt to decrypt the sub_parish ID
        $sub_parish_id = decryptData($sub_parish_id);

        // Further validation if necessary
        if (empty($sub_parish_id) || !preg_match('/^[a-zA-Z0-9]+$/', $sub_parish_id)) {
            header("Location: /error.php?message=" . urlencode("Invalid sub parish ID."));
            exit;
        }
    } catch (Exception $e) {
        // Handle decryption failure
        header("Location: /error.php?message=" . urlencode("Error decrypting sub parish ID: " . $e->getMessage()));
        exit;
    }
}

// Validation for community target
if ($target == 'community') {
    if (!$sub_parish_id || !$community_id) {
        header("Location: /error.php?message=" . urlencode("Both Sub Parish ID and Community ID are required for community target."));
        exit;
    }
    try {
        // Attempt to decrypt the sub_parish ID
        $sub_parish_id = decryptData($sub_parish_id);

        // Further validation if necessary
        if (empty($sub_parish_id) || !preg_match('/^[a-zA-Z0-9]+$/', $sub_parish_id)) {
            header("Location: /error.php?message=" . urlencode("Invalid sub parish ID."));
            exit;
        }

        // Attempt to decrypt the community ID
        $community_id = decryptData($community_id);

        // Further validation if necessary
        if (empty($community_id) || !preg_match('/^[a-zA-Z0-9]+$/', $community_id)) {
            header("Location: /error.php?message=" . urlencode("Invalid community ID."));
            exit;
        }
    } catch (Exception $e) {
        // Handle decryption failure
        header("Location: /error.php?message=" . urlencode("Error decrypting IDs: " . $e->getMessage()));
        exit;
    }
}

// Validation for group target
if ($target == 'group') {
    if (!$group_id) {
        header("Location: /error.php?message=" . urlencode("Group ID is required for group target."));
        exit;
    }
    try {
        // Attempt to decrypt the group ID
        $group_id = decryptData($group_id);

        // Further validation if necessary
        if (empty($group_id) || !preg_match('/^[a-zA-Z0-9]+$/', $group_id)) {
            header("Location: /error.php?message=" . urlencode("Invalid group ID."));
            exit;
        }
    } catch (Exception $e) {
        // Handle decryption failure
        header("Location: /error.php?message=" . urlencode("Error decrypting group ID: " . $e->getMessage()));
        exit;
    }
}

// Check if the head_parish_id is in session
if (!isset($_SESSION['head_parish_id'])) {
    header("Location: /error.php?message=" . urlencode("Unauthorized"));
    exit();
}

$head_parish_id = $_SESSION['head_parish_id'];
$parish_info = getParishInfo($conn, $head_parish_id);
$operational_level_name = strtoupper(getOperationalLevelName($conn, $head_parish_id, $sub_parish_id, $community_id, $group_id, $target));
$target_bank_account_name = getBankAccountName($conn, $account_id);
$default_target = 'head-parish';
$head_parish_name = strtoupper(getOperationalLevelName($conn, $head_parish_id, $sub_parish_id, $community_id, $group_id, $default_target));
$operational_level_text = '';
$operational_level = '';

switch ($target) {
    case 'head-parish':
        // For the 'head-parish' target, assign the operational level text
        $operational_level_text = 'HEAD PARISH NAME';
        $operational_level = 'HEAD PARISH';
        break;

    case 'sub-parish':
        // For the 'sub-parish' target, assign the operational level text
        $operational_level_text = 'SUB PARISH NAME';
        $operational_level = 'SUB PARISH';
        break;

    case 'community':
        // For the 'community' target, assign the operational level text
        $operational_level_text = 'COMMUNITY NAME';
        $operational_level = 'COMMUNITY';
        break;

    case 'group':
        // For the 'group' target, assign the operational level text
        $operational_level_text = 'GROUP NAME'; 
        $operational_level = 'GROUP';
        break;

    default:
        // For invalid target types, return an empty string or an error message
        $operational_level_text = 'Invalid target';
        break;
}




if ($target_bank_account_name == null) {
    $target_bank_account_name = 'INVALID BANK ACCOUNT';
}


// Call the function to get the expense group details
$expense_groups = getExpenseGroupDetails($conn, $head_parish_id, $account_id, $year, $target, $sub_parish_id, $community_id, $group_id);


    $html = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>On Going Budget Position (OGO)</title>
        <style>
            *{
                margin:0;
                padding:0;
                box-sizing:border-box;
            }
            body {
                margin-top: 20px;
                padding: 0;
                color: #333;
                font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            }
    
            /* Container styling */
            .container {
                width: 90%;
                margin: 20px auto;
                padding: 20px;
                background-color: #ffffff;
                border-radius: 10px;
            }
    
            /* Table styling */
            table {
                width: 100%; /* Full width table */
                border-collapse: collapse;
            }
    
            th, td {
                padding: 10px; /* Smaller padding */
                text-align: left;
                border: 1px solid #000; /* Light gray border */
            }
    
            .table-separator {
                width: 99.5%;               
                background-color: #000;  
                height: 2px;            
                margin: 0 auto;          
            }
        
            /* Table header */
            thead th {
                font-weight: 600; /* Medium weight for headers */
                font-size: 13px; /* Slightly smaller header font */
                color: #555; /* Darker gray */
            }
    
            tbody td {
                font-size: 10px; /* Smaller font size */
            }

            .footer {
                text-align: center;
                margin-top: 40px;
                font-size: 12px;
                color: #888;
            }
        
            .heading {
                text-align: center;
                font-size: 20px; /* Moderate heading size */
                font-weight: 700;
                margin-bottom: 15px;
                color: #2c3e50; /* Darker heading color */
            }
    
            .table-heading {
                font-weight: bold;
                font-size:14px;
                color: #444;
                background-color:#243a4a;
                text-transform:uppercase;
                color:#fff;
                border:none;
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
            .table td strong {
                font-weight: bold;
            }
            
            .table-container{
                width:100%;
            }
            .all-bold{
                font-weight:bold;
            }
            .no-border-top{
                border-top: none;
            }
            .positive-percentage {
                color: green;
                font-weight: bold;
            }
            
            .neutral-percentage {
                color: #d79e1e;
                font-weight: bold;
            }
            
            .final-row{
                font-size:8px;
            }
            .highlight-column {
                background-color: gray;
            }
        </style>
    </head>
    <body>
    
    <div class="container">
        <h1 class="heading">THE ONGOING BUDGET POSITION FOR ' . $head_parish_name . ' LUTHERAN CHURCH FOR THE YEAR ' . $year . '</h1>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th colspan="4" class="table-heading text-center">Budget Summary</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>MANAGEMENT LEVEL:</strong></td>
                        <td>' . $operational_level . '</td>
                        <td><strong>' . $operational_level_text . ':</strong></td>
                        <td>' . $operational_level_name . '</td>
                    </tr>
                    <tr>
                        <td><strong>TARGET ACCOUNT:</strong></td>
                        <td>' . $target_bank_account_name . '</td>
                        <td><strong>Abbreviations:</strong></td>
                        <td><strong>BUD</strong> for Budget, <strong>BAL</strong> for Balance, <strong>EXP</strong> for Expense</td>
                    </tr>
                </tbody>
            </table>
        </div>

    ';
    
    $revenue_details = getBankAccountRevenueDetails($conn, $head_parish_id, $year, $target, $sub_parish_id, $community_id, $group_id);
    // echo json_encode($revenue_details, JSON_PRETTY_PRINT);

    $html .= '
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th colspan="' . (count($revenue_details) + 1) . '" class="table-heading text-center">OUR BOOKS BALANCES AS PER ' . $formatted_datetime . '</th>
                    </tr>
                    <tr>
                        <th>CRITERIA</th>';
        $total_account_balance = 0;
        foreach ($revenue_details as $account) {
            $html .= '<th class="text-center">' . $account['account_name'] . '</th>';
        }
        
        $html .= '
                    </tr>
                </thead>
                <tbody>';
        
        // Rows for bank account balance
        $rows = ['ACCOUNT BALANCE' => 'account_balance'];
        
        foreach ($rows as $label => $field) {
            $html .= '<tr><td>' . $label . '</td>';
            foreach ($revenue_details as $account) {
                $value = $account[$field];
                $html .= '<td class="text-center">' . number_format($value, 2) . '</td>';
                $total_account_balance += $value;
            }
            $html .= '</tr>';
        }
        
        $html .= '
                </tbody>
            </table>
        </div>
        <div class="table-container">
            <table class="table">
                <tbody>
                    <tr class="all-bold no-border-top">
                        <td class="text-center no-border-top" style="width:50%;font-size:12px;font-weight:bold;">CUMMULATIVE BANK ACCOUNTS BALANCE</td>
                        <td class="text-center no-border-top" style="width:50%;font-size:12px;font-weight:bold;">' . number_format($total_account_balance).'</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th colspan="' . (count($revenue_details) + 1) . '" style="text-align:center;" class="no-border-top table-heading">ANNUAL BUDGET OVERVIEW</th>
                    </tr>
                    <tr>
                        <th>CRITERIA</th>';
        
        foreach ($revenue_details as $account) {
            $html .= '<th class="text-center">' . $account['account_name'] . '</th>';
        }
        
        $html .= '
                    </tr>
                </thead>
                <tbody>';
        
        $grand_totals = ['BUDGET' => 0, 'REVENUE' => 0, 'DEFICIT' => 0];
        $rows = [
            'BUDGET' => 'annual_revenue_target',
            'REVENUE' => 'annual_revenue',
            'DEFICIT' => 'balance'
        ];
        
        foreach ($rows as $label => $field) {
            $row_total = 0;
            $html .= '<tr><td>' . $label . '</td>';
            foreach ($revenue_details as $account) {
                $value = $account[$field];
                $html .= '<td class="text-center">' . number_format($value, 2) . '</td>';
                $row_total += $value;
                $grand_totals[$label] += $value;
            }
            $html .= '</tr>';
        }
        
        $html .= '
                </tbody>
            </table>
        </div>
        <div class="table-container">
            <table class="table">
                <tbody>
                    <tr class="all-bold no-border-top">
                        <td class="text-center no-border-top">TOTAL BUDGET</td>
                        <td class="text-center no-border-top">' . number_format($grand_totals['BUDGET'], 2) . ' (' . 
                            '<span class="' . (calculatePercentage($grand_totals['BUDGET'], $grand_totals['BUDGET']) > 100 ? 'positive-percentage' : 'neutral-percentage') . '">' . 
                            number_format(calculatePercentage($grand_totals['BUDGET'], $grand_totals['BUDGET']), 0) . '%</span>)</td>
                        <td class="text-center no-border-top">TOTAL REVENUE</td>
                        <td class="text-center no-border-top">' . number_format($grand_totals['REVENUE'], 2) . ' (' . 
                            '<span class="' . (calculatePercentage($grand_totals['REVENUE'], $grand_totals['BUDGET']) > 100 ? 'positive-percentage' : 'neutral-percentage') . '">' . 
                            number_format(calculatePercentage($grand_totals['REVENUE'], $grand_totals['BUDGET']), 0) . '%</span>)</td>
                        <td class="text-center no-border-top">BUDGET DEFICIT</td>
                        <td class="text-center no-border-top">' . number_format($grand_totals['DEFICIT'], 2) . ' (' . 
                            '<span class="' . (calculatePercentage($grand_totals['DEFICIT'], $grand_totals['BUDGET']) > 100 ? 'positive-percentage' : 'neutral-percentage') . '">' . 
                            number_format(calculatePercentage($grand_totals['DEFICIT'], $grand_totals['BUDGET']), 0) . '%</span>)</td>
                    </tr>
                </tbody>
            </table>
        </div>';
        
// Iterate over each expense group and calculate totals for each expense
if ($expense_groups) {
    $final_expense_report = [];

    foreach ($expense_groups as $expense_group) {
        $expense_group_name = $expense_group['expense_group'];
        $expense_code = $expense_group['expense_code'];
        $expense_budgets = $expense_group['expense_budgets'];
    
        // Initialize an array for this expense group
        $expense_group_report = [
            "expense_group" => $expense_group_name,
            "expense_code" => $expense_code,
            "expenses" => []
        ];
    
        // Iterate through the budgets and calculate the approved expenses per quarter
        foreach ($expense_budgets as $budget) {
            $expense_name = $budget['expense_name'];
            $expense_id = $budget['expense_id'];
            $expense_identifier = $budget['expense_identifier'];
            $expense_budget = $budget['expense_budget'];
    
            // Get the approved expenses for this budget
            $quarter_totals = getApprovedExpenseByQuarter($conn, $head_parish_id, $sub_parish_id, $community_id, $group_id, $year, $expense_id, $target);
    
            // Calculate total expense amount
            $total_expense_amount = array_sum($quarter_totals);
            
            $annual_budget_balance = $expense_budget - $total_expense_amount;
    
            $expenditure_percentage = calculatePercentage($annual_budget_balance, $expense_budget);
            // Add the expense details along with the totals for each quarter
            $expense_group_report['expenses'][] = [
                "expense_name" => $expense_name,
                "expense_identifier" => $expense_identifier,
                "expense_budget" => $expense_budget,
                "approved_expenses" => $quarter_totals,
                "total_expense_amount" => $total_expense_amount,
                "annual_budget_balance" => $annual_budget_balance,
                "expenditure_percentage" => $expenditure_percentage
            ];
        }
    
        // Sort the expenses within each expense group based on `order_column` and `report_order`
        if ($report_order === 'default') {
            usort($expense_group_report['expenses'], function($a, $b) use ($order_column) {
                return strcmp($a[$order_column], $b[$order_column]);
            });
        } elseif ($report_order === 'ascending') {
            usort($expense_group_report['expenses'], function($a, $b) use ($order_column) {
                return $a[$order_column] <=> $b[$order_column];
            });
        } elseif ($report_order === 'descending') {
            usort($expense_group_report['expenses'], function($a, $b) use ($order_column) {
                return $b[$order_column] <=> $a[$order_column];
            });
        }
    
        // Add the expense group report to the final report
        $final_expense_report[] = $expense_group_report;
    }

$html .='

        
        <div class="table-container">
            <table class="table">';
        
        foreach ($final_expense_report as $expense_group) {
            $expense_group_name = $expense_group['expense_group'];
            $expense_code = $expense_group['expense_code'];
        
            $html .= '
                <thead>
                    <tr>
                        <th colspan="18" class="table-heading text-center">' . $expense_code . '. ' . $expense_group_name . '</th>
                    </tr>
                    <tr>
                        <th rowspan="2" class="text-center ogo-header">ACC</th>
                        <th colspan="3" class="text-center ogo-header">Q1 (JAN - MAR)</th>
                        <th colspan="3" class="text-center ogo-header">Q2 (APR - JUN)</th>
                        <th colspan="3" class="text-center ogo-header">Q3 (JUL - SEPT)</th>
                        <th colspan="3" class="text-center ogo-header">Q4 (OCT - DEC)</th>
                        <th colspan="4" class="text-center ogo-header">ANNUAL TOTAL</th>
                        <th rowspan="2" class="text-center ogo-header">ACC</th>
                    </tr>
                    <tr>
                        <th class="text-center ogo-columns">BUD</th><th class="text-center ogo-columns">EXP</th><th class="text-center ogo-columns">BAL</th>
                        <th class="text-center ogo-columns">BUD</th><th class="text-center ogo-columns">EXP</th><th class="text-center ogo-columns">BAL</th>
                        <th class="text-center ogo-columns">BUD</th><th class="text-center ogo-columns">EXP</th><th class="text-center ogo-columns">BAL</th>
                        <th class="text-center ogo-columns">BUD</th><th class="text-center ogo-columns">EXP</th><th class="text-center ogo-columns">BAL</th>
                        <th class="text-center ogo-columns">BUD</th><th class="text-center ogo-columns">EXP</th><th class="text-center ogo-columns">BAL</th><th class="text-center ogo-columns">% OF BAL.</th>

                    </tr>
                </thead>
                <tbody>';
        
            $grand_total_budget = 0;
            $grand_total_expense = 0;
            $grand_total_balance = 0;
        
            $grand_totals_per_quarter = [
                'Q1' => ['budget' => 0, 'expense' => 0, 'balance' => 0],
                'Q2' => ['budget' => 0, 'expense' => 0, 'balance' => 0],
                'Q3' => ['budget' => 0, 'expense' => 0, 'balance' => 0],
                'Q4' => ['budget' => 0, 'expense' => 0, 'balance' => 0]
            ];
        
            foreach ($expense_group['expenses'] as $expense) {
                $expense_identifier = $expense['expense_identifier'];
                $expense_budget = $expense['expense_budget'];
                $quarterly_budget = ($expense_budget / 4);
                $expense_name = $expense['expense_name'];
            
                // Highlight the column if $order_column is 'expense_identifier'
                $highlight_style = ($order_column === 'expense_identifier') ? 'background-color:#dceef2;' : '';
            
                $html .= '<tr><td class="text-center" style="' . $highlight_style . '"><a href="#" title="' . $expense_name . '" style="text-decoration:none;color:black;font-weight:bold;">' . $expense_identifier . '</a></td>';
            
                $annual_total_expense = 0;
            
                foreach (['Q1', 'Q2', 'Q3', 'Q4'] as $quarter) {
                    $approved_expense = isset($expense['approved_expenses'][$quarter]) ? $expense['approved_expenses'][$quarter] : 0;
                    $balance = $quarterly_budget - $approved_expense;
            
                    $grand_totals_per_quarter[$quarter]['budget'] += $quarterly_budget;
                    $grand_totals_per_quarter[$quarter]['expense'] += $approved_expense;
                    $grand_totals_per_quarter[$quarter]['balance'] += $balance;
            
                    $annual_total_expense += $approved_expense;
            
                    $html .= '<td class="text-right">' . number_format($quarterly_budget, 0) . '</td>';
                    $html .= '<td class="text-right">' . number_format($approved_expense, 0) . '</td>';
                    $html .= '<td class="text-right">' . number_format($balance, 0) . '</td>';
                }
            
                $annual_budget_balance = $expense_budget - $annual_total_expense;
                $expenditure_percentage = calculatePercentage($annual_budget_balance, $expense_budget);
            
                // Apply highlight style conditionally for each column
                $highlight_style = ($order_column === 'expense_budget') ? 'background-color:#dceef2;' : '';
                $html .= '<td class="text-right" style="' . $highlight_style . '">' . number_format($expense_budget, 0) . '</td>';
            
                $highlight_style = ($order_column === 'total_expense_amount') ? 'background-color:#dceef2;' : '';
                $html .= '<td class="text-right" style="' . $highlight_style . '">' . number_format($annual_total_expense, 0) . '</td>';
            
                $highlight_style = ($order_column === 'annual_budget_balance') ? 'background-color:#dceef2;' : '';
                $html .= '<td class="text-right" style="' . $highlight_style . '">' . number_format($annual_budget_balance, 0) . '</td>';
            
                $highlight_style = ($order_column === 'expenditure_percentage') ? 'background-color:#dceef2;' : '';
                $html .= '<td class="text-center" style="' . $highlight_style . '">' . number_format($expenditure_percentage, 0) . '</td>';
            
                // Reuse highlight style for the second instance of 'expense_identifier'
                $highlight_style = ($order_column === 'expense_identifier') ? 'background-color:#dceef2;' : '';
                $html .= '<td class="text-center" style="' . $highlight_style . '"><a href="#" title="' . $expense_name . '" style="text-decoration:none;color:black;font-weight:bold;">' . $expense_identifier . '</a></td>';
            }

        
            $html .= '<tr style="font-weight:bold;"><td class="text-center">TOTAL</td>';
            foreach (['Q1', 'Q2', 'Q3', 'Q4'] as $quarter) {
                $html .= '<td class="text-right final-row">' . number_format($grand_totals_per_quarter[$quarter]['budget'], 0) . '</td>';
                $html .= '<td class="text-right final-row">' . number_format($grand_totals_per_quarter[$quarter]['expense'], 0) . '</td>';
                $html .= '<td class="text-right final-row">' . number_format($grand_totals_per_quarter[$quarter]['balance'], 0) . '</td>';
        
                $grand_total_budget += $grand_totals_per_quarter[$quarter]['budget'];
                $grand_total_expense += $grand_totals_per_quarter[$quarter]['expense'];
                $grand_total_balance += $grand_totals_per_quarter[$quarter]['balance'];
            }
            $html .= '<td class="text-right final-row">' . number_format($grand_total_budget, 0) . '</td>';
            $html .= '<td class="text-right final-row">' . number_format($grand_total_expense, 0) . '</td>';
            $html .= '<td class="text-right final-row">' . number_format($grand_total_balance, 0) . '</td>';
            $html .= '<td class="text-center">' . number_format(($grand_total_balance / $grand_total_budget) * 100, 0) . '</td>';
            $html .= '<td class="text-center">TOTAL</td></tr>';
        
            $html .= '</tbody>';
        }
        $html .= '</table>
        </div>';
        
        
    
    
    } else {
    // Print a message in a table when no data is available
        $html .= '
        <div class="table-container">
            <table border="1" cellpadding="5" cellspacing="0" style="width:100%;">
                <thead>
                    <tr>
                        <th colspan="2" style="text-align:center;">No Expense Groups Found</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="2" style="text-align:center;">No data available for the selected criteria.</td>
                    </tr>
                </tbody>
            </table>
        </div>';
}

        $html .= '
        <div class="footer">
            <p>Kanisa Langu - SEWMR Technologies</p>
        </div>
    </div>
</body>
</html>';

// echo $html;

// Load HTML into Dompdf
$dompdf->loadHtml($html);

// Set paper size and orientation
$dompdf->setPaper('A4', 'landscape');

// Render the HTML as PDF
$dompdf->render();

// Stream the PDF with a dynamic filename
$filename = "ONGOING BUDGET POSITION $client_datetime.pdf";
$dompdf->stream($filename, array("Attachment" => false));
?>
