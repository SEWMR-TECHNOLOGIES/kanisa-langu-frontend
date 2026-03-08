<?php
header('Content-Type: application/json');
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/libraries/vendor/autoload.php'); // Dompdf

use Dompdf\Dompdf;

date_default_timezone_set('Africa/Nairobi');

// Ensure admin is logged in
if (!isset($_SESSION['head_parish_admin_id']) || !isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Admin not logged in"]);
    exit();
}

$admin_id = $_SESSION['head_parish_admin_id'];
$head_parish_id = $_SESSION['head_parish_id'];

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit();
}

// Sanitize POST inputs
$account_id = isset($_POST['account_id']) ? intval($_POST['account_id']) : null;
$management_level = isset($_POST['management_level']) ? strtolower(trim($_POST['management_level'])) : null;
$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-01');
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : date('Y-m-t');

$sub_parish_id = isset($_POST['sub_parish_id']) ? intval($_POST['sub_parish_id']) : null;
$community_id = isset($_POST['community_id']) ? intval($_POST['community_id']) : null;
$group_id = isset($_POST['group_id']) ? intval($_POST['group_id']) : null;

// Validate required fields
if (!$account_id || !$management_level || !$head_parish_id) {
    echo json_encode(["success" => false, "message" => "Required parameters missing"]);
    exit();
}

// Validate management level
if (!in_array($management_level, ['head-parish','sub-parish','community','group'])) {
    echo json_encode(["success" => false, "message" => "Invalid management level"]);
    exit();
}

// Level-specific validation
switch ($management_level) {
    case 'head-parish':
        $sub_parish_id = $community_id = $group_id = null;
        break;
    case 'sub-parish':
        if (empty($sub_parish_id)) {
            echo json_encode(["success" => false, "message" => "Sub Parish ID is required"]);
            exit();
        }
        $community_id = $group_id = null;
        break;
    case 'community':
        if (empty($sub_parish_id) || empty($community_id)) {
            echo json_encode(["success" => false, "message" => "Sub Parish ID and Community ID are required"]);
            exit();
        }
        $group_id = null;
        break;
    case 'group':
        if (empty($group_id)) {
            echo json_encode(["success" => false, "message" => "Group ID is required"]);
            exit();
        }
        break;
}

// Get display names
$account_name = getHeadParishBankAccount($conn, $account_id)['account_name'] ?? 'Unknown Account';

$level_names = getManagementLevelNames(
    $conn,
    $management_level,
    $head_parish_id,
    $sub_parish_id,
    $community_id,
    $group_id
);

// Pick correct display name based on management level
switch (strtolower($management_level)) {
    case 'head-parish':
        $management_level_name = $level_names['head_parish_name'] ?? 'HEAD PARISH';
        $level_key = 'USHARIKA';
        break;
    case 'sub-parish':
        $management_level_name = $level_names['sub_parish_name'] ?? 'SUB PARISH';
         $level_key = 'MITAA';
        break;
    case 'community':
        $management_level_name = $level_names['community_name'] ?? 'COMMUNITY';
         $level_key = 'JUMUIYA';
        break;
    case 'group':
        $management_level_name = $level_names['group_name'] ?? 'GROUP';
        $level_key = 'VIKUNDI';
        break;
    default:
        $management_level_name = 'UNKNOWN';
        break;
}

// Call function to fetch transactions
$data = getTransactionHistory(
    $conn,
    $account_id,
    $management_level,
    $start_date,
    $end_date,
    $head_parish_id,
    $sub_parish_id,
    $community_id,
    $group_id
);

// Calculate running balance
$running = $data['opening_balance'] ?? 0;

// Add opening balance row
$transactions = $data['transactions'] ?? [];
array_unshift($transactions, [
    'transaction_id' => null,
    'type' => 'opening',
    'description' => 'Opening Balance',
    'amount' => null,
    'txn_date' => $start_date,
    'balance' => $running,
    'reference_number' => '---'
]);

// Compute running balance and reference numbers
foreach ($transactions as $i => &$txn) {
    if ($txn['type'] === 'revenue') {
        $running += $txn['amount'];
    } elseif ($txn['type'] === 'expense') {
        $running -= $txn['amount'];
    }
    $txn['balance'] = $running;

    if ($txn['transaction_id']) {
        $txn_date_formatted = date('Ymd', strtotime($txn['txn_date']));
        $txn['reference_number'] = "HP{$head_parish_id}-TX{$txn['transaction_id']}-{$txn_date_formatted}";
    }
}
unset($txn);
$parish_info = getParishInfo($conn, $head_parish_id);
// Build HTML exactly as in your original view (kept structure/CSS intact)
$account_name_esc = htmlspecialchars(strtoupper($account_name));
$management_level_name_esc = htmlspecialchars(strtoupper($management_level_name));
$level_key_esc = htmlspecialchars($level_key);
$start_date_disp = date('d/m/Y', strtotime($start_date));
$end_date_disp = date('d/m/Y', strtotime($end_date));
$timestamp = date('l, F j, Y g:i A');


$html = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/png" href="/assets/images/logos/favicon.png" />
    <title>Harambee Community Report By Class</title>
    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }
        .date{
            color: #4187f8;
        }
        body {
            font-family: Helvetica, Arial, sans-serif; 
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
            font-size:12px;
        }
        td {
            font-size: 12px;
            border: 1px solid #000;
        }
        .empty-cell {
            background-color: #fff;
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
        .footer {
            position: fixed;
            bottom: 0px;
            left: 0;
            right: 0;
            height: 30px;
            text-align: center;
            font-size: 12px;
            color: #888;
        }
        .footer .page-number:before {
            content: "Page " counter(page);
        }

    /* amount styles */
    .amount-revenue { color: #4ed47dff; font-weight: 700; text-align: right; }
    .amount-expense { color: #c93232ff; font-weight: 700; text-align: right; }
    td.amount-empty { color: rgba(255,255,255,0.25); text-align: center; }

    /* reference column */
    td.ref { font-family: monospace; font-size: 12px; color: #0b1117ff; text-align: center; }

</style>
</head>
<body>
    <div class="container">
        <div class="header" style="text-align: center; margin-bottom: 20px;">
            <h1 style="font-size: 12px; color: #3498db; margin: 0;">K.K.K.T ' . $parish_info['diocese_name'] . '</h1>
            <h1 style="font-size: 12px; color: #2c3e50; margin: 5px 0;">' . $parish_info['province_name'] . ' | ' . $parish_info['head_parish_name'] . '</h1>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr style="background-color:#fff;font-weight:bold;text-align:center;">
                        <td colspan="2">
                            TAARIFA YA FEDHA: MAPATO NA MATUMIZI KUTOKA ' . $start_date_disp . ' HADI ' . $end_date_disp . ' <br>Printed on: ' . $timestamp . '
                        </td>
                    </tr>
                </thead>
            </table>
        </div>

    <div class="table-container" style="margin-top: 20px;">
        
         <table>
            <thead>
                <tr>
                    <th colspan="3" style="text-align:left">JINA LA AKAUNTI</th>
                    <th colspan="4" style="text-align:left">'.$account_name_esc.'</th>
                </tr>
                <tr>
                    <th colspan="2">NGAZI YA</th>
                    <th colspan="2">'.$level_key_esc.'</th>
                    <th>JINA</th>
                    <th colspan="2">'.$management_level_name_esc.'</th>
                </tr>
                <tr>
                    <th rowspan="2">No.</th>
                    <th rowspan="2">Tarehe</th>
                    <th rowspan="2">Maelezo</th>
                    <th rowspan="2">Kumbu.</th>
                    <th colspan="2">Kiasi cha fedha kilicho</th>
                    <th rowspan="2">Salio</th>
                </tr>
                <tr>
                    <th>Pokelewa</th>
                    <th>Tolewa</th>
                </tr>
            </thead>

            <tbody>';
// populate rows
$count = 1;
foreach ($transactions as $txn) {
    $txn_date = date('d/m/Y', strtotime($txn['txn_date']));
    $description = htmlspecialchars($txn['description']);
    $ref = htmlspecialchars($txn['reference_number']);
    $revenue = $txn['type'] === 'revenue' ? number_format($txn['amount'],2) : '-';
    $expense = $txn['type'] === 'expense' ? number_format($txn['amount'],2) : '-';
    $balance = number_format($txn['balance'],2);

    $row_class = $txn['type'] === 'opening' ? 'opening' : '';
    $html .= '<tr class="'.$row_class.'">
        <td style="text-align:center">'.$count++.'</td>
        <td>'.$txn_date.'</td>
        <td style="text-align:left;">'.$description.'</td>
        <td class="ref">'.$ref.'</td>
        <td class="'.($txn['type']==='revenue' ? 'amount-revenue' : 'amount-empty').'">'.$revenue.'</td>
        <td class="'.($txn['type']==='expense' ? 'amount-expense' : 'amount-empty').'">'.$expense.'</td>
        <td style="text-align:right">'.$balance.'</td>
    </tr>';
}

$html .= '</tbody></table>
    </div>';
    
$html .= '</div>

        <div class="footer">
            <p><span class="page-number"></span> | Printed on '.$timestamp.' | Kanisa Langu - SEWMR Technologies</p>
        </div>
    </div>
</body>
</html>';

// Instantiate Dompdf and set options
$dompdf = new Dompdf();
$options = $dompdf->getOptions();
$options->setFontCache('fonts');
$options->set('isRemoteEnabled', true);
$dompdf->setOptions($options);

// Load HTML
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Get PDF content
$pdf_content = $dompdf->output();

// Encode to base64
$pdf_base64 = base64_encode($pdf_content);

// Return JSON with base64 so JS can download
echo json_encode([
    "success" => true,
    "filename" => 'transaction_report_' . $head_parish_id . '_' . date('Ymd_His') . '.pdf',
    "pdf_base64" => $pdf_base64
]);
exit();

?>
