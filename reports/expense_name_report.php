<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/libraries/vendor/autoload.php'); // Dompdf

use Dompdf\Dompdf;

date_default_timezone_set('Africa/Nairobi');

// Auth
if (!isset($_SESSION['head_parish_admin_id']) || !isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Admin not logged in"]);
    exit();
}

$admin_id = $_SESSION['head_parish_admin_id'];
$head_parish_id = $_SESSION['head_parish_id'];

// Only GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit();
}

// Inputs
$account_id = isset($_GET['account_id']) ? intval($_GET['account_id']) : null;
$management_level = isset($_GET['management_level']) ? strtolower(trim($_GET['management_level'])) : null;
$expense_group_id = isset($_GET['expense_group_id']) ? intval($_GET['expense_group_id']) : null;
$expense_name_id  = isset($_GET['expense_name_id']) ? intval($_GET['expense_name_id']) : null; // NEW
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

$sub_parish_id = isset($_GET['sub_parish_id']) ? intval($_GET['sub_parish_id']) : null;
$community_id = isset($_GET['community_id']) ? intval($_GET['community_id']) : null;
$group_id = isset($_GET['group_id']) ? intval($_GET['group_id']) : null;

// Validate
if (!$management_level || !$head_parish_id || !$expense_group_id || !$expense_name_id) {
    echo json_encode(["success" => false, "message" => "Required parameters missing"]);
    exit();
}

if (!in_array($management_level, ['head-parish','sub-parish','community','group'])) {
    echo json_encode(["success" => false, "message" => "Invalid management level"]);
    exit();
}

// Level-specific checks
switch ($management_level) {
    case 'head-parish':
        $sub_parish_id = $community_id = $group_id = null;
        break;
    case 'sub-parish':
        if (empty($sub_parish_id)) { echo json_encode(["success"=>false,"message"=>"Sub Parish ID is required"]); exit(); }
        $community_id = $group_id = null;
        break;
    case 'community':
        if (empty($sub_parish_id) || empty($community_id)) { echo json_encode(["success"=>false,"message"=>"Sub Parish ID and Community ID are required"]); exit(); }
        $group_id = null;
        break;
    case 'group':
        if (empty($group_id)) { echo json_encode(["success"=>false,"message"=>"Group ID is required"]); exit(); }
        break;
}

// display names
$account_name_esc = '';
if ($account_id) {
    $acct = getHeadParishBankAccount($conn, $account_id);
    $account_name_esc = htmlspecialchars(strtoupper($acct['account_name'] ?? 'UNKNOWN ACCOUNT'));
}
$level_names = getManagementLevelNames($conn, $management_level, $head_parish_id, $sub_parish_id, $community_id, $group_id);

switch ($management_level) {
    case 'head-parish':
        $management_level_name = $level_names['head_parish_name'] ?? 'HEAD PARISH';
        $level_key = 'USHARIKA';
        $display_name = $management_level_name;
        break;
    case 'sub-parish':
        $management_level_name = $level_names['sub_parish_name'] ?? 'SUB PARISH';
        $level_key = 'MITAA';
        $display_name = 'MTAA WA '.$management_level_name;
        break;
    case 'community':
        $management_level_name = $level_names['community_name'] ?? 'COMMUNITY';
        $level_key = 'JUMUIYA';
        $display_name = 'JUMUIYA YA '.$management_level_name;
        break;
    case 'group':
        $management_level_name = $level_names['group_name'] ?? 'GROUP';
        $level_key = 'VIKUNDI';
        $display_name = 'KIKUNDI CHA '.$management_level_name;
        break;
    default:
        $management_level_name = 'UNKNOWN';
        $level_key = '';
        $display_name = $management_level_name;
        break;
}

$management_level_name_esc = htmlspecialchars(strtoupper($management_level_name));
$level_key_esc = htmlspecialchars($level_key);
$start_date_disp = date('d/m/Y', strtotime($start_date));
$end_date_disp = date('d/m/Y', strtotime($end_date));
$timestamp = date('l, F j, Y g:i A');

// Get expense group summary
$expense_summary = getExpenseNameSummary(
    $conn,
    $management_level,
    $head_parish_id,
    $sub_parish_id,
    $community_id,
    $group_id,
    $expense_group_id,
    $expense_name_id,
    $start_date,
    $end_date
);

if ($expense_summary === null || $expense_summary === false) {
    echo json_encode(["success" => false, "message" => "No expense data found"]);
    exit();
}
// Encode the response as JSON and log it
// error_log("getExpenseNameSummary response: " . json_encode($expense_summary, JSON_PRETTY_PRINT));

$group_name       = $expense_summary['expense_group_name'] ?? 'Unknown';
$expense_name     = strtoupper($expense_summary['expense_name']) ?? 'Unknown';
$exp_name_id      = $expense_summary['expense_name_id'] ?? 0;
$annual_budget    = (float)($expense_summary['annual_budget'] ?? 0);
$exp_budget       = (float)($expense_summary['budgeted_amount'] ?? 0);
$exp_prev         = (float)($expense_summary['total_expenses_before_date'] ?? 0);
$exp_now          = (float)($expense_summary['total_expenses'] ?? 0);
$exp_total        = $exp_prev + $exp_now;
$exp_balance      = $exp_budget - $exp_total;
$expenses         = is_array($expense_summary['expenses'] ?? null) ? $expense_summary['expenses'] : [];

// parish header
$parish_info = getParishInfo($conn, $head_parish_id);
if (!is_array($parish_info)) { $parish_info = ['diocese_name'=>'','province_name'=>'','head_parish_name'=>'']; }

$html = '';
$html .= '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Expense Name Report</title>
<style>
    *{margin:0;padding:0;box-sizing:border-box;}
    body { font-family: Helvetica, Arial, sans-serif; margin:25px; color:#333; background:#fff; }
    .container { width:90%; margin:20px auto; padding:20px; background:#fff; box-shadow:0 4px 8px rgba(0,0,0,0.1); }
    .header { text-align:center; margin-bottom:10px; }
    h1{font-size:14px;color:#3498db;margin-bottom:5px;}
    h3{font-size:12px;}
    table{width:100%;border-collapse:collapse;}
    th,td{border:1px solid #000;padding:6px;font-size:12px;}
    th{background-color:#fff;color:#000;}
    .text-right{text-align:right;}
    .text-center{text-align:center;}
    .muted{color:#888;font-size:12px;}
    .label { font-weight:700; background:#f0f0f0; padding:4px 6px; display:inline-block; border-radius:3px;}
    .amount-neg { color:#c93232; font-weight:700; }
    .table-container{
        width:100%;
    }
    .table-separator {
            width: 99.5%;               
            background-color: #000;  
            height: 2px;            
            margin: 0 auto;          
        }
</style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>K.K.K.T '.htmlspecialchars($parish_info['diocese_name']).'</h1>
      <h1 style="color:#2c3e50;">'.htmlspecialchars($parish_info['province_name']).' | '.htmlspecialchars($parish_info['head_parish_name']).'</h1>
      <h3 style="color:#2c3e50;">
         TAARIFA YA '.htmlspecialchars($group_name).' KASMA YA '.htmlspecialchars($expense_name).' KUTOKA '.$start_date_disp.' HADI '.$end_date_disp.''.($management_level !== 'head-parish' ? ' '.$display_name : '').
      '</h3>
    </div>


    <!-- Table A: Summary -->
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th style="width:5%;" class="text-center">A:</th>
            <th colspan="5" style="width:96%;text-align:center; font-weight:700;">MUHTASARI WA BAJETI NA MATUMIZI</th>
          </tr>
          <tr>
            <th style="width:4%;" rowspan="3"></th>
            <th style="width:30%; text-align:center;" rowspan="2">BAJETI YA MWAKA</th>
            <th style="width:48%; text-align:center;" colspan="3">MATUMIZI</th>
            <th style="width:17%; text-align:center;" rowspan="2">SALIO</th>
          </tr>
          <tr>
            <th style="width:16%; text-align:center;">AWALI</th>
            <th style="width:16%; text-align:center;">SASA</th>
            <th style="width:16%; text-align:center;">JUMLA</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="text-center">'.number_format($exp_budget,0).'</td>
            <td class="text-center">'.number_format($exp_prev,0).'</td>
            <td class="text-center">'.number_format($exp_now,0).'</td>
            <td class="text-center">'.number_format($exp_total,0).'</td>
            <td class="text-center">'.number_format($exp_balance,0).'</td>
          </tr>
        </tbody>
      </table>
    </div>

  <!-- Table B: Expense breakdown -->
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th class="text-center" style="width:5%;">B:</th>
            <th colspan="7" style="text-align:center; font-weight:700;width:95%;">MCHANGANUO WA MATUMIZI</th>
          </tr>
          <tr>
            <th style="width:5%;text-align:center;" rowspan="2">NA.</th>
            <th style="width:10%;text-align:center;" rowspan="2">TAREHE</th>
            <th style="width:20%;text-align:center;" rowspan="2">MAELEZO</th>
            <th style="width:14%;text-align:center;" rowspan="2">BAJETI</th>
            <th style="width:36%;text-align:center;" colspan="3">MATUMIZI</th>
            <th style="width:15%;text-align:center;" rowspan="2">SALIO</th>
          </tr>
          <tr>
            <th style="text-align:center;">AWALI</th>
            <th style="text-align:center;">SASA</th>
            <th style="text-align:center;">JUMLA</th>
          </tr>
        </thead>
        <tbody>';
    
    $count = 1;
    $running_balance = $exp_budget - $exp_prev; // starting balance
    
    foreach ($expenses as $exp) {
        $date = date('d-m-Y', strtotime($exp['date'] ?? ''));
        $amount_now = (float)($exp['amount'] ?? 0);
        $total = $exp_prev + $amount_now; // total for this row
        $running_balance -= $amount_now;   // update running balance
        $expense_description = $exp['description'];
    
        $html .= '
        <tr>
            <td class="small" style="text-align:center">'.$count++.'</td>
            <td style="text-align:left; padding-left:8px;">'.$date.'</td>
            <td style="text-align:left; padding-left:8px;">'.htmlspecialchars($expense_description).'</td>
            <td class="text-right">'.number_format($exp_budget,0).'</td>
            <td class="text-right">'.number_format($exp_prev,0).'</td>
            <td class="text-right">'.number_format($amount_now,0).'</td>
            <td class="text-right">'.number_format($total,0).'</td>
            <td class="text-right '.($running_balance < 0 ? 'amount-neg' : '').'">'.number_format($running_balance,0).'</td>
        </tr>';
    
        // update previous total for next row
        $exp_prev += $amount_now;
    }
    
    $html .= '
        </tbody>
      </table>
    </div>


    <div style="height:12px;"></div>
  </div>

  <div class="footer muted" style="text-align:center; margin-top:8px;">
    <span class="page-number"></span>  Printed on '.$timestamp.' | Kanisa Langu - SEWMR Technologies
  </div>
</body>
</html>';

$dompdf = new Dompdf();
$options = $dompdf->getOptions();
$options->setFontCache('fonts');
$options->set('isRemoteEnabled', true);
$dompdf->setOptions($options);

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = preg_replace('/\s+/', '_', strtoupper($display_name)) . "_EXPENSE_NAME_REPORT_" . date('Ymd_His') . ".pdf";
$dompdf->stream($filename, ["Attachment" => false]);
exit();
?>
