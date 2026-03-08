<?php
// test_transactions.php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');
date_default_timezone_set('Africa/Nairobi');

// Fetch GET parameters
$account_id       = isset($_GET['account_id']) ? intval($_GET['account_id']) : null;
$management_level = isset($_GET['management_level']) ? trim($_GET['management_level']) : null;
$head_parish_id   = isset($_GET['head_parish_id']) ? intval($_GET['head_parish_id']) : null;
$sub_parish_id    = isset($_GET['sub_parish_id']) ? intval($_GET['sub_parish_id']) : null;
$community_id     = isset($_GET['community_id']) ? intval($_GET['community_id']) : null;
$group_id         = isset($_GET['group_id']) ? intval($_GET['group_id']) : null;
$start_date       = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date         = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Validate required fields
if (!$account_id || !$management_level || !$head_parish_id) {
    die("Missing required parameters: account_id, management_level, head_parish_id");
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
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>TAARIFA YA FEDHA</title>
<style>
    /* Basic resets */
    *, *::before, *::after { box-sizing: border-box; }
    html, body { height: 100%; margin: 0; font-family: "Segoe UI", Roboto, Arial, sans-serif; background: radial-gradient(circle at 10% 10%, #0f1724 0%, #0b1220 40%, #071018 100%); color: #e6eef8; -webkit-font-smoothing: antialiased; }
    .wrap { max-width: 1180px; margin: 28px auto; padding: 26px; border-radius: 16px; background: linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.02)); box-shadow: 0 8px 30px rgba(2,6,23,0.7), inset 0 1px 0 rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.04); }

    header.page-head { display: block; margin-bottom: 18px; position: relative; padding-bottom: 10px; }
    .title { font-size: 20px; letter-spacing: 0.6px; font-weight: 700; color: #f0f7ff; margin: 0 0 6px 0; text-transform: uppercase; text-align: center; }
    .subtitle { font-size: 13px; color: #bcd0e8; margin: 0; text-align: center;}

    /* accent bar */
    .accent { position: absolute; left: 0; right: 0; top: -14px; height: 8px; border-radius: 10px; background: linear-gradient(90deg, #00d4ff, #0066ff, #7d5cff); box-shadow: 0 6px 16px rgba(29,78,255,0.15); }

    /* table container */
    .table-scroller { overflow-x: auto; padding-top: 6px; -webkit-overflow-scrolling: touch; }

    /* stylish table using classic table layout no flex no grid */
    table.ledger {
        width: 100%;
        border-collapse: separate; /* create gap between rows */
        border-spacing: 0 10px; /* vertical gap */
        table-layout: auto;
    }

    /* header row */
    thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: linear-gradient(180deg, rgba(10,20,40,0.9), rgba(10,20,40,0.85));
        color: #dff4ff;
        font-weight: 700;
        padding: 12px 14px;
        font-size: 13px;
        text-align: center;
        border-bottom: 1px solid rgba(255,255,255,0.03);
        backdrop-filter: blur(4px);
    }

    thead tr.top-meta th {
        background: transparent;
        font-weight: 600;
        color: #cfe8ff;
        padding: 10px 12px;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.8px;
    }

    tbody tr {
        transition: transform 220ms ease, box-shadow 220ms ease;
    }

    /* row card illusion */
    tbody tr td {
        background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
        padding: 12px 14px;
        border-top: 1px solid rgba(255,255,255,0.02);
        border-bottom: 1px solid rgba(0,0,0,0.18);
        color: #d8e9ff;
        font-size: 13px;
    }

    /* create rounded row look by rounding first and last cell */
    tbody tr td:first-child { border-top-left-radius: 10px; border-bottom-left-radius: 10px; text-align: left; width: 6%; }
    tbody tr td:last-child { border-top-right-radius: 10px; border-bottom-right-radius: 10px; width: 12%; text-align: right; }

    /* special row for opening balance */
    tr.opening td {
        background: linear-gradient(90deg, rgba(255,255,255,0.03), rgba(220,230,255,0.01));
        color: #fffcec;
        font-weight: 700;
        box-shadow: 0 6px 18px rgba(0,0,0,0.45);
    }

    /* zebra slight */
    tbody tr:nth-child(even) td { filter: brightness(0.99); }

    /* hover lift */
    tbody tr:hover td { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(2,6,23,0.6); }

    /* amount styles */
    .amount-revenue { color: #8ef0b0; font-weight: 700; text-align: right; }
    .amount-expense { color: #ffb3b3; font-weight: 700; text-align: right; }
    td.amount-empty { color: rgba(255,255,255,0.25); text-align: center; }

    /* small labels */
    .meta-cell { font-size: 12px; color: #bcd0e8; text-transform: uppercase; letter-spacing: 0.6px; }

    /* reference column */
    td.ref { font-family: monospace; font-size: 12px; color: #cde6ff; text-align: center; }

    /* responsive tweaks */
    @media (max-width: 820px) {
        .wrap { padding: 14px; border-radius: 12px; }
        thead th { font-size: 12px; padding: 10px 8px; }
        tbody tr td { padding: 10px 8px; font-size: 12px; }
        .title { font-size: 16px; }
    }

    /* print friendly */
    @media print {
        body { background: #fff; color: #000; }
        .wrap { box-shadow: none; border: none; background: transparent; }
        thead th { position: static; background: #eee; color: #000; -webkit-print-color-adjust: exact; }
        tbody tr td { background: #fff; color: #000; box-shadow: none; }
    }
</style>
</head>
<body>
<div class="wrap">
    <div class="accent" aria-hidden="true"></div>
    <header class="page-head">
        <h1 class="title">TAARIFA YA FEDHA MAPATO NA MATUMIZI</h1>
        <p class="subtitle">Taarifa hii inaonesha mapato na matumizi ya fedha katika kipindi kilichochaguliwa.</p>
    </header>

    <div class="table-scroller">
        <table class="ledger" role="table" aria-label="transaction ledger">
            <thead>
                <tr class="top-meta">
                    <th colspan="3" style="text-align:left">JINA LA AKAUNTI</th>
                    <th colspan="4" style="text-align:left"><?php echo strtoupper($account_name); ?></th>
                </tr>
                <tr class="top-meta">
                    <th colspan="2">NGAZI YA</th>
                    <th colspan="2"><?php echo $level_key; ?></th>
                    <th colspan="2">JINA</th>
                    <th colspan="2"><?php echo strtoupper($management_level_name); ?></th>
                </tr>
                <tr class="top-meta">
                    <th colspan="2">Kuanzia</th>
                    <th><?php echo date('d/m/Y', strtotime($start_date)); ?></th>
                    <th colspan="2">Mpaka</th>
                    <th><?php echo date('d/m/Y', strtotime($end_date)); ?></th>
                </tr>

                <tr>
                    <th rowspan="2">No.</th>
                    <th rowspan="2">Tarehe</th>
                    <th rowspan="2">Maelezo</th>
                    <th rowspan="2">Kumbu.</th>
                    <th colspan="2">Kiasi cha fedha</th>
                    <th rowspan="2">Salio</th>
                </tr>
                <tr>
                    <th>Pokelewa</th>
                    <th>Tolewa</th>
                </tr>
            </thead>

            <tbody>
                <?php $count = 1; foreach ($transactions as $txn): ?>
                <tr class="<?php echo $txn['type'] === 'opening' ? 'opening' : ''; ?>">
                    <td><?php echo $count++; ?></td>
                    <td><?php echo date('d/m/Y', strtotime($txn['txn_date'])); ?></td>
                    <td style="text-align:left;"><?php echo htmlspecialchars($txn['description']); ?></td>
                    <td class="ref"><?php echo htmlspecialchars($txn['reference_number']); ?></td>
                    <td class="<?php echo $txn['type'] === 'revenue' ? 'amount-revenue' : 'amount-empty'; ?>">
                        <?php echo $txn['type'] === 'revenue' ? number_format($txn['amount'],2) : '-'; ?>
                    </td>
                    <td class="<?php echo $txn['type'] === 'expense' ? 'amount-expense' : 'amount-empty'; ?>">
                        <?php echo $txn['type'] === 'expense' ? number_format($txn['amount'],2) : '-'; ?>
                    </td>
                    <td><?php echo number_format($txn['balance'],2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
