<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php'); 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/libraries/vendor/autoload.php');
// Set timezone to Nairobi and format current datetime
date_default_timezone_set('Africa/Nairobi');
$printedOn = date('j F Y h:i A'); // e.g. 21 July 2025 12:54 PM

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
$grouped_request_id = isset($_GET['grouped_request_id']) ? (int)$_GET['grouped_request_id'] : 0;
$target = isset($_GET['target']) ? trim($_GET['target']) : '';

$valid_targets = ['head-parish', 'sub-parish', 'community', 'group'];
if ($grouped_request_id <= 0 || !in_array($target, $valid_targets)) {
    header("HTTP/1.1 400 Bad Request");
    exit('Invalid request parameters.');
}

// Map to get request table per target
$requestTables = [
    'head-parish' => 'head_parish_expense_requests',
    'sub-parish'  => 'sub_parish_expense_requests',
    'community'   => 'community_expense_requests',
    'group'       => 'group_expense_requests',
];

// Get the correct expense requests table
if (!isset($requestTables[$target])) {
    header("HTTP/1.1 400 Bad Request");
    exit('Unsupported target.');
}

$details = getGroupedRequestDetails($conn, $grouped_request_id, $target);

if ($details) {
    $description = $details['description'];
    $submission_datetime = $details['submission_datetime'];
    $recorded_by_name = $details['recorded_by_name'];
} else {
    $description = 'N/A';
    $submission_datetime = 'N/A';
    $recorded_by_name = 'N/A';
}

$submissionTimestamp = strtotime($submission_datetime);
$nowTimestamp = time();

$submissionFormatted = strtoupper(date('d/m/Y h:i A', $submissionTimestamp));
$descriptionUpper = strtoupper($description);
$recordedByUpper = strtoupper($recorded_by_name);

$submittedPhrase = ($nowTimestamp > $submissionTimestamp) 
    ? 'YALIYOWASILISHWA NA' 
    : 'YAMEWASILISHWA NA';

$title = 'MAOMBI YA MATUMIZI KUHUSU ' . $descriptionUpper;

$year = (int)date('Y', $submissionTimestamp);
$month = (int)date('m', $submissionTimestamp);

switch (true) {
    case $month >= 1 && $month <= 3:
        $quarterText = "ROBO YA KWANZA YA MWAKA $year (JANUARI 01 - MACHI 31)";
        break;
    case $month >= 4 && $month <= 6:
        $quarterText = "ROBO YA PILI YA MWAKA $year (APRILI 01 - JUNI 30)";
        break;
    case $month >= 7 && $month <= 9:
        $quarterText = "ROBO YA TATU YA MWAKA $year (JULAI 01 - SEPTEMBA 30)";
        break;
    case $month >= 10 && $month <= 12:
        $quarterText = "ROBO YA NNE YA MWAKA $year (OKTOBA 01 - DESEMBA 31)";
        break;
    default:
        $quarterText = "ROBO ISIYOJULIKANA";
}


$req_table = $requestTables[$target];
$show_signature = true;
// Prepare and execute query to fetch all expense requests for this grouped_request_id
$sql = "SELECT request_id, request_amount, request_status, pastor_approval, pastor_approval_datetime, chairperson_approval, chairperson_approval_datetime FROM $req_table WHERE grouped_request_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $grouped_request_id);
$stmt->execute();
$result = $stmt->get_result();


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
    // if the role wasn't returned, default to nulls
    $data = $signaturesData[$role] ?? ['name' => null, 'path' => null];

    $signaturesBase64[$role] = [
        'name'  => $data['name'],                     
        'image' => toBase64Img($data['path'] ?? null) 
    ];
}

$html = '
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Expense Request Report</title>
<style>
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
  .muted {
    color:#888;
    font-style:italic;
  }
  .total-row td {
    font-weight:bold;
    background:#fdf5e6;
  }

  .status {
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 4px;
    color: white;
    display: inline-block;
    font-size: 13px;
    text-transform: uppercase;
  }
  .status-pending {
    background-color: #f39c12; /* orange */
  }
  .status-approved {
    background-color: #27ae60; /* green */
  }
  .status-rejected {
    background-color: #c0392b; /* red */
  }
  .total-row td {
    font-weight: 700;
    background: #fdf5e6;
    text-align: right;
  }
  .total-row td:first-child {
    text-align: center;
  }

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
  .sign-name {
    font-weight: 600;
    margin-bottom: 2px;
  }
  .sign-role {
    font-size: 12px;
    color: #555;
    margin-bottom: 2px;
  }
  .sign-line {
    border-top: 1px solid #222;
    height: 1px;
  }
  .sign-date {
    font-size: 11px;
    color: #666;
    margin-bottom: 12px;
}

</style>
</head>
<body>
    <div class="container">
        <div class="header" style="text-align: center; margin-bottom: 20px;">
            <h1 style="font-size: 12px; color: #3498db; margin: 0;">K.K.K.T ' . $parish_info['diocese_name'] . '</h1>
            <h1 style="font-size: 12px; color: #2c3e50; margin: 5px 0;">' . $parish_info['province_name'] . ' | ' . $parish_info['head_parish_name'] . '</h1>
            <h2 style="font-size: 12px; color: #2c3e50; margin: 10px 0; text-transform: uppercase;">
                ' . $title . '<br>
                <span style="font-size: 11px;">
                    ' . $submittedPhrase . ' ' . $recordedByUpper . ' TAREHE ' . $submissionFormatted . '
                </span>
            </h2>
                     <h2 style="font-size: 12px; color: #2c3e50; margin: 10px 0; text-transform: uppercase;">
                ' . $quarterText . '
            </h2>
        </div>
    </div>

<table>
  <thead>
    <tr>
      <th>S/N</th>
      <th>Expense Name</th>
      <th>Requested Amount</th>
      <th>Quarterly Balance</th>
      <th>Annual Balance</th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody>
';

$count = 1;
$totalRequested = 0;
$totalQuarterlyBalance = 0;
$totalAnnualBalance = 0;

if ($result->num_rows === 0) {
    $html .= '<tr><td colspan="6" style="text-align:center;">No approved expense requests found for grouped request ID ' . htmlspecialchars($grouped_request_id) . '.</td></tr>';
} else {
    while ($row = $result->fetch_assoc()) {
        $request = $row;
        $request_id = $row['request_id'];
        $expenseStatistics = getExpenseStatsByRequest($conn, $request_id, $target);
        if (!$expenseStatistics) {
            continue; // skip if no stats
        }
        if ($row['request_status'] == 'Pending') {
            $show_signature = false;
        }

        $expenseStatistics['request_amount'] = $row['request_amount'];
        $expenseStatistics['request_status'] = $row['request_status'];
        $expenseStatistics['pastor_approval'] = $row['pastor_approval'];
        $expenseStatistics['pastor_approval_datetime'] = $row['pastor_approval_datetime'];
        $expenseStatistics['chairperson_approval'] = $row['chairperson_approval'];
        $expenseStatistics['chairperson_approval_datetime'] = $row['chairperson_approval_datetime'];

        $totalRequested += $expenseStatistics['request_amount'];
        $totalQuarterlyBalance += $expenseStatistics['current_quarter_balance'];
        $totalAnnualBalance += $expenseStatistics['annual_balance'];

        // Prepare status label class
        $status = strtolower($expenseStatistics['request_status']);
        $statusClass = match($status) {
            'approved' => 'status-approved',
            'pending' => 'status-pending',
            'rejected' => 'status-rejected',
            default => 'status-pending',
        };

        $html .= '<tr>
          <td style="text-align:center;">' . $count++ . '</td>
          <td>' . htmlspecialchars($expenseStatistics['expense_name']) . '</td>
          <td>' . number_format($expenseStatistics['request_amount'], 2) . '</td>
          <td>' . number_format($expenseStatistics['current_quarter_balance'], 2) . '</td>
          <td>' . number_format($expenseStatistics['annual_balance'], 2) . '</td>
          <td><span class="status ' . $statusClass . '">' . ucfirst($expenseStatistics['request_status']) . '</span></td>
        </tr>';
    }

    // Grand total row
    $html .= '<tr class="total-row">
      <td colspan="2" style="text-align:center;">Total</td>
      <td>' . number_format($totalRequested, 2) . '</td>
      <td>' . number_format($totalQuarterlyBalance, 2) . '</td>
      <td>' . number_format($totalAnnualBalance, 2) . '</td>
      <td></td>
    </tr>';
}

$html .= '
  </tbody>
</table>';
  
// Format timestamps or show "—" if null
$accDate = date('F j, Y g:i A', strtotime($submission_datetime));
$pastorDate = !empty($request['pastor_approval_datetime']) 
    ? date('F j, Y g:i A', strtotime($request['pastor_approval_datetime'])) : '—';
$chairpersonDate = !empty($request['chairperson_approval_datetime']) 
    ? date('F j, Y g:i A', strtotime($request['chairperson_approval_datetime'])) : '—';

$html .= '
  <table class="signature-table">
    <tr>
      <td>
        <div class="sign-name">' . htmlspecialchars($signaturesBase64['accountant']['name'] ?? 'Accountant') . '</div>
        <div class="sign-role">Accountant</div>
        <div class="sign-date">' . $accDate . '</div>';
if (!empty($signaturesBase64['accountant']['image']) && $show_signature) {
    $html .= '<img src="' . $signaturesBase64['accountant']['image'] . '" style="max-height:40px; margin-bottom:5px;" />';
}
$html .= '
        <div class="sign-line"></div>
      </td>

      <td>
        <div class="sign-name">' . htmlspecialchars($signaturesBase64['admin']['name'] ?? 'Chairperson') . '</div>
        <div class="sign-role">Chairperson</div>
        <div class="sign-date">' . $chairpersonDate . '</div>';
if (!empty($signaturesBase64['admin']['image']) && $show_signature) {
    $html .= '<img src="' . $signaturesBase64['admin']['image'] . '" style="max-height:40px; margin-bottom:5px;" />';
}
$html .= '
        <div class="sign-line"></div>
      </td>

      <td>
        <div class="sign-name">' . htmlspecialchars($signaturesBase64['pastor']['name'] ?? 'Pastor') . '</div>
        <div class="sign-role">Pastor</div>
        <div class="sign-date">' . $pastorDate . '</div>';
if (!empty($signaturesBase64['pastor']['image']) && $show_signature) {
    $html .= '<img src="' . $signaturesBase64['pastor']['image'] . '" style="max-height:40px; margin-bottom:5px;" />';
}
$html .= '
        <div class="sign-line"></div>
      </td>
    </tr>
  </table>';

$html .= '
<div style="text-align:center; margin-top:30px; font-size:12px; color:#555;">
    Printed on ' . $printedOn . ' | Kanisa Langu - SEWMR Technologies
</div>';

$html .= '
</body>
</html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Clean the expense name for filename use (remove spaces, special chars)
$clean_grouped_expense_name = preg_replace('/[^A-Za-z0-9\-]/', '_', $description);

// Build a descriptive filename
$filename = $clean_grouped_expense_name . ' - request_' . $grouped_request_id . '.pdf';

// Stream PDF inline with this filename
$dompdf->stream($filename, ['Attachment' => false]);

exit;
?>