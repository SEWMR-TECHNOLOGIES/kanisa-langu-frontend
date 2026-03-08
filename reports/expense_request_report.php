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
$options->set('isHtml5ParserEnabled', true);
$options->setChroot([$_SERVER['DOCUMENT_ROOT'] . '/assets/fonts/']);
$dompdf->setOptions($options);

$imageData = base64_encode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/assets/images/logos/kkkt-logo.jpg'));
$src = 'data:image/jpeg;base64,' . $imageData;

if (!isset($_SESSION['head_parish_id'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit('Unauthorized');
}

$head_parish_id = $_SESSION['head_parish_id'];
$parish_info = getParishInfo($conn, $head_parish_id);
$request_id = isset($_GET['request_id']) ? (int)$_GET['request_id'] : 0;
$target = isset($_GET['target']) ? trim($_GET['target']) : '';

$valid_targets = ['head-parish', 'sub-parish', 'community', 'group'];
if ($request_id <= 0 || !in_array($target, $valid_targets)) {
    header("HTTP/1.1 400 Bad Request");
    exit('Invalid request parameters.');
}

// Map tables dynamically based on target
switch ($target) {
    case 'head-parish':
        $req_table = 'head_parish_expense_requests';
        $group_table = 'head_parish_expense_groups';
        $name_table = 'head_parish_expense_names';
        $items_table = 'head_parish_expense_request_items';
        $budget_table = 'head_parish_expense_budgets';
        break;

    case 'sub-parish':
        $req_table = 'sub_parish_expense_requests';
        $group_table = 'sub_parish_expense_groups';
        $name_table = 'sub_parish_expense_names';
        $items_table = 'sub_parish_expense_request_items';
        $budget_table = 'sub_parish_expense_budgets';
        break;

    case 'community':
        $req_table = 'community_expense_requests';
        $group_table = 'community_expense_groups';
        $name_table = 'community_expense_names';
        $items_table = 'community_expense_request_items';
        $budget_table = 'community_expense_budgets';
        break;

    case 'group':
        $req_table = 'group_expense_requests';
        $group_table = 'group_expense_groups';
        $name_table = 'group_expense_names';
        $items_table = 'group_expense_request_items';
        $budget_table = 'group_expense_budgets';
        break;

    default:
        header("HTTP/1.1 400 Bad Request");
        exit('Target not supported.');
}

// Fetch request with related group and expense name
$sql = "SELECT r.*, eg.expense_group_name, en.expense_name
        FROM $req_table r
        JOIN $group_table eg ON r.expense_group_id = eg.expense_group_id
        JOIN $name_table en ON r.expense_name_id = en.expense_name_id
        WHERE r.request_id = ? AND r.head_parish_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $request_id, $head_parish_id);
$stmt->execute();
$request = $stmt->get_result()->fetch_assoc();

if (!$request) {
    header("HTTP/1.1 404 Not Found");
    exit('Expense request not found.');
}

$request_year = date('Y', strtotime($request['request_datetime']));

// Fetch budget (optional, not used for summary now)
$sql_budget = "SELECT budgeted_amount 
               FROM $budget_table
               WHERE head_parish_id = ? AND expense_group_id = ? AND expense_name_id = ?
                 AND YEAR(start_date) <= ? AND YEAR(end_date) >= ?
               LIMIT 1";
$stmt_budget = $conn->prepare($sql_budget);
$stmt_budget->bind_param("iiiii", $head_parish_id, $request['expense_group_id'], $request['expense_name_id'], $request_year, $request_year);
$stmt_budget->execute();
$budget_res = $stmt_budget->get_result()->fetch_assoc();
$budget_amount = $budget_res['budgeted_amount'] ?? 0;

// Fetch items
$sql_items = "SELECT i.*, u.unit 
              FROM $items_table i
              LEFT JOIN unit_of_measure u ON i.measure_id = u.measure_id
              WHERE i.request_id = ?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $request_id);
$stmt_items->execute();
$items_res = $stmt_items->get_result();

$items = [];
$total_spent = 0;
while ($row = $items_res->fetch_assoc()) {
    $item_total = ($row['unit_cost'] !== null && $row['quantity'] !== null) ? $row['unit_cost'] * $row['quantity'] : null;
    if ($item_total) {
        $total_spent += $item_total;
    }
    $items[] = $row;
}

$signaturesData = getSignaturePaths($conn, $head_parish_id);

function toBase64Img($filePath) {
    if ($filePath && file_exists($filePath)) {
        $imageData = base64_encode(file_get_contents($filePath));
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeType = match (strtolower($ext)) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            default => 'application/octet-stream',
        };
        return "data:$mimeType;base64,$imageData";
    }
    return null;
}

$signaturesBase64 = [];
foreach (['accountant', 'pastor', 'admin'] as $role) {
    $signaturesBase64[$role] = [
        'name' => $signaturesData[$role]['name'] ?? ucfirst($role),
        'image' => toBase64Img($signaturesData[$role]['path'] ?? '')
    ];
}

// Assume request amount is stored in 'request_amount' field; adjust if different
$requested_amount = $request['request_amount'] ?? 0;
$balance = $requested_amount - $total_spent;

$html = '
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Expense Request Report</title>
<style>
  /* Keep your original CSS intact */
  body {
    font-family: "Segoe UI", sans-serif;
    color: #222;
    background: #fff;
    padding: 40px;
    max-width: 900px;
    margin: auto;
    font-size: 14px;
  }
  .header {
      text-align: center;
      margin-bottom: 10px;
  }
  .header img {
      max-width: 150px;
      margin-bottom: 10px;
  }
  h1,h2,h3 { margin:0; padding:0; }
  .header { text-align:center; margin-bottom:20px; color:#2f3b52; }
  .header h1 { font-size:16px; text-transform:uppercase; font-weight:600; }
  .header h2 { font-size:15px; font-weight:500; margin-top:2px; }
  .header h3 { font-size:18px; font-weight:700; margin-top:5px; }
  .logo { margin:12px auto 0; width:80px; height:80px; }
  .title-block { text-align:center; margin:30px 0 20px; color:#2c3e50; }
  .title-block h2 { font-size:17px; margin:4px 0; font-weight:500; }
  .title-block strong { font-weight:700; }
  .meta-row {
    display:flex;
    justify-content:space-between;
    border-top:1px solid #ccc;
    border-bottom:1px solid #ccc;
    padding:8px 0;
    margin-bottom:20px;
    font-size:13px;
  }
  .meta-row div { flex:1; }
  .meta-row div:not(:last-child) { margin-right:20px; }

  table {
    width:100%;
    border-collapse:collapse;
    margin-bottom:25px;
  }
  table thead { background:#f0f4f8; }
  table th, table td {
    border:1px solid #ccc;
    padding:6px 8px;
    text-align:left;
  }
  table th {
    font-weight:600;
    font-size:13px;
    color:#2f3b52;
  }
  table td { font-size:13px; }
  .muted { color:#888; font-style:italic; }
  .total-row td { font-weight:bold; background:#fdf5e6; }
  .summary {
    border: none;
    padding: 10px 15px;
    margin-bottom: 30px;
    display: flex;
    justify-content: space-around;
    font-size: 13px;
    background: #f9fafb;
  }
  .summary div { text-align: center; }
  .summary div span { display: block; font-weight: 600; font-size: 14px; margin-bottom: 3px; color: #34495e; }
  .signature-table {
    width: 100%;
    margin-top: 40px;
    font-size: 13px;
    border-collapse: collapse;
    border: none;
  }
  .signature-table td {
    width: 33%;
    text-align: center;
    vertical-align: bottom;
    padding: 0 10px;
    border: none;
  }
  .sign-name { font-weight: 600; margin-bottom: 2px; }
  .sign-role { font-size: 12px; color: #555; margin-bottom: 2px; }
  .sign-line { border-top: 1px solid #222; height: 1px; }
  .sign-date { font-size: 11px; color: #666; margin-bottom: 12px; }
</style>
</head>
<body>
    <div class="container">
        <div class="header" style="text-align: center; margin-bottom: 20px;">
            <h1 style="font-size: 12px; color: #3498db; margin: 0;">K.K.K.T ' . $parish_info['diocese_name'] . '</h1>
            <h1 style="font-size: 12px; color: #2c3e50; margin: 5px 0;">' . $parish_info['province_name'] . ' | ' . $parish_info['head_parish_name'] . '</h1>
            <h2 style="font-size: 12px; color: #2c3e50; margin: 10px 0;text-transform:uppercase;">MCHANGANUO WA OMBI LA MATUMIZI ' . strtoupper(htmlspecialchars($request['expense_group_name'])) . ' KASMA YA ' . htmlspecialchars($request['expense_name']) . ' TAREHE  <span class="date">' . date('j F, Y g:i A', strtotime($request['request_datetime'])) . '</span></h2>
        </div>
    </div>

<table>
  <thead>
    <tr>
      <th>#</th>
      <th>Item Name</th>
      <th>Unit Cost</th>
      <th>Quantity</th>
      <th>Total</th>
      <th>Spent On</th>
    </tr>
  </thead>
  <tbody>';

$count = 1;
foreach ($items as $item) {
    $hasAmount = ($item['unit_cost'] !== null && $item['quantity'] !== null);
    $unit_cost = $hasAmount ? number_format($item['unit_cost'], 2) : '';
    $quantity = $hasAmount ? number_format($item['quantity'], 0) : '';
    $total_cost = $hasAmount ? number_format($item['unit_cost'] * $item['quantity'], 2) : '';
    $unit = htmlspecialchars($item['unit'] ?? '');
    $spent_on = !empty($item['spent_on']) ? htmlspecialchars(date('d F Y', strtotime($item['spent_on']))) : '—';

    // Combine quantity and unit, e.g. "4 KG"
    $qty_unit = $hasAmount ? ($quantity . ' ' . $unit) : $unit;

    if ($hasAmount) {
        $html .= '
    <tr>
      <td>' . $count . '</td>
      <td>' . htmlspecialchars($item['item_name']) . '</td>
      <td>' . $unit_cost . '</td>
      <td>' . $qty_unit . '</td>
      <td>' . $total_cost . '</td>
      <td>' . $spent_on . '</td>
    </tr>';
    } else {
        $html .= '
    <tr>
      <td>' . $count . '</td>
      <td>' . htmlspecialchars($item['item_name']) . '</td>
      <td class="muted" colspan="3">No amount, descriptive item</td>
      <td class="muted">—</td>
    </tr>';
    }
    $count++;
}

$html .= '
    <tr class="total-row">
      <td colspan="4" style="text-align: right;">Total</td>
      <td colspan="2">' . number_format($total_spent, 2) . ' TZS</td>
    </tr>
  </tbody>
</table>


  <table style="width:100%; border-collapse: collapse; margin-bottom: 30px; font-size: 13px;">
    <tr>
      <td style="text-align:center; font-weight:600; color:#34495e;">Requested Amount</td>
      <td style="text-align:center; font-weight:600; color:#34495e;">Total Spent</td>
      <td style="text-align:center; font-weight:600; color:#34495e;">Balance</td>
    </tr>
    <tr>
      <td style="text-align:center;">' . number_format($requested_amount, 2) . ' TZS</td>
      <td style="text-align:center;">' . number_format($total_spent, 2) . ' TZS</td>
      <td style="text-align:center;">' . number_format($balance, 2) . ' TZS</td>
    </tr>
  </table>';
  
// Format timestamps or show "—" if null
$accDate = date('F j, Y g:i A', strtotime($request['request_datetime']));
$pastorDate = !empty($request['pastor_approval_datetime']) 
    ? date('F j, Y g:i A', strtotime($request['pastor_approval_datetime'])) : '—';
$chairpersonDate = !empty($request['chairperson_approval_datetime']) 
    ? date('F j, Y g:i A', strtotime($request['chairperson_approval_datetime'])) : '—';

$html .= '
  <table class="signature-table">
    <tr>
      <td>
        
      </td>

      <td>
        <div class="sign-name">' . htmlspecialchars($signaturesBase64['accountant']['name'] ?? 'Accountant') . '</div>
        <div class="sign-role">Accountant</div>
        <div class="sign-date">' . $accDate . '</div>';
if (!empty($signaturesBase64['accountant']['image'])) {
    $html .= '<img src="' . $signaturesBase64['accountant']['image'] . '" style="max-height:40px; margin-bottom:5px;" />';
}
$html .= '
        <div class="sign-line"></div>
      </td>

      <td>
        
      </td>
    </tr>
  </table>';

$html .= '
</body>
</html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Clean the expense name for filename use (remove spaces, special chars)
$clean_expense_name = preg_replace('/[^A-Za-z0-9\-]/', '_', $request['expense_name']);

// Build a descriptive filename
$filename = $clean_expense_name . ' - request_' . $request_id . ' breakdown report.pdf';

// Stream PDF inline with this filename
$dompdf->stream($filename, ['Attachment' => false]);

exit;
?>