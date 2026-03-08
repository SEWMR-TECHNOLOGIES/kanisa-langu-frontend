<?php
header('Content-Type: application/json');
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

date_default_timezone_set('Africa/Nairobi');

// ✅ CHANGE THIS if your column name is different (e.g. payment_mode, payment_type, method, etc.)
$PAYMENT_METHOD_COLUMN = 'payment_method';

// Ensure admin logged in
if (!isset($_SESSION['head_parish_admin_id']) || !isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Admin not logged in"]);
    exit();
}

$admin_id = $_SESSION['head_parish_admin_id'];
$head_parish_id = $_SESSION['head_parish_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit();
}

$target = isset($_POST['target']) ? strtolower(trim($_POST['target'])) : null;

if (!$target || !in_array($target, ['head_parish','other','sub_parish','community','group'], true)) {
    echo json_encode(["success" => false, "message" => "Invalid target"]);
    exit();
}

// Map target → table and management level
$tableMap = [
    'head_parish' => ['table' => 'head_parish_revenues', 'level' => 'head-parish'],
    'other'       => ['table' => 'other_head_parish_revenues', 'level' => 'head-parish'],
    'sub_parish'  => ['table' => 'sub_parish_revenues', 'level' => 'sub-parish'],
    'community'   => ['table' => 'community_revenues', 'level' => 'community'],
    'group'       => ['table' => 'group_revenues', 'level' => 'group'],
];

$table = $tableMap[$target]['table'];
$management_level = $tableMap[$target]['level'];

// ✅ Build GROUP BY depending on target (NOW includes payment method so Cash & Bank Transfer won't be summed together)
switch ($target) {
    case 'head_parish':
    case 'other':
        $groupBy = "r.revenue_stream_id, r.revenue_date, r.`$PAYMENT_METHOD_COLUMN`";
        break;

    case 'sub_parish':
        $groupBy = "r.revenue_stream_id, r.revenue_date, r.sub_parish_id, r.`$PAYMENT_METHOD_COLUMN`";
        break;

    case 'community':
        $groupBy = "r.revenue_stream_id, r.revenue_date, r.sub_parish_id, r.community_id, r.`$PAYMENT_METHOD_COLUMN`";
        break;

    case 'group':
        $groupBy = "r.revenue_stream_id, r.revenue_date, r.group_id, r.`$PAYMENT_METHOD_COLUMN`";
        break;
}

$conn->begin_transaction();

try {
    // Build optional select columns per target
    $extraSelectCols = "";
    if ($target === 'sub_parish') {
        $extraSelectCols = "r.sub_parish_id,";
    } elseif ($target === 'community') {
        $extraSelectCols = "r.sub_parish_id, r.community_id,";
    } elseif ($target === 'group') {
        $extraSelectCols = "r.group_id,";
    }

    // ✅ Fetch grouped revenues, now grouped by payment method too
    $sql = "
        SELECT
            GROUP_CONCAT(r.revenue_id) AS revenue_ids,
            r.revenue_stream_id,
            s.revenue_stream_name,
            s.account_id,
            r.revenue_date,
            COALESCE(r.`$PAYMENT_METHOD_COLUMN`, 'Unknown') AS payment_method,
            $extraSelectCols
            SUM(r.revenue_amount) AS total_amount
        FROM $table r
        JOIN head_parish_revenue_streams s ON r.revenue_stream_id = s.revenue_stream_id
        WHERE r.head_parish_id = ? AND r.is_posted_to_bank = FALSE
        GROUP BY $groupBy
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $head_parish_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $conn->rollback();
        echo json_encode(["success" => false, "message" => "No unposted revenues found"]);
        exit();
    }

    $created_at = date('Y-m-d H:i:s');
    $insertedCount = 0;
    $allIdsToUpdate = [];

    while ($row = $result->fetch_assoc()) {
        $account_id = (int)$row['account_id'];
        $amount     = (float)$row['total_amount'];
        $txn_date   = $row['revenue_date'];

        $sub_id   = isset($row['sub_parish_id']) ? (int)$row['sub_parish_id'] : null;
        $comm_id  = isset($row['community_id']) ? (int)$row['community_id'] : null;
        $group_id = isset($row['group_id']) ? (int)$row['group_id'] : null;

        $payment_method = $row['payment_method'] ?? 'Unknown';

        // ✅ Keep method visible in the transaction description (no DB schema change needed)
        $description = $row['revenue_stream_name'] . " ({$payment_method})";

        $revenue_ids = array_filter(array_map('trim', explode(',', (string)$row['revenue_ids'])));
        $allIdsToUpdate = array_merge($allIdsToUpdate, $revenue_ids);

        $sql2 = "INSERT INTO transactions
            (account_id, head_parish_id, sub_parish_id, community_id, group_id,
             management_level, type, description, amount, txn_date, recorded_by, created_at)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";

        $stmt2 = $conn->prepare($sql2);
        if (!$stmt2) {
            throw new Exception("Prepare insert failed: " . $conn->error);
        }

        $type = "revenue";

        // Note: bind types:
        // i=account_id, i=head_parish_id, i=sub_id, i=comm_id, i=group_id,
        // s=management_level, s=type, s=description, d=amount, s=txn_date, s=recorded_by, s=created_at
        $stmt2->bind_param(
            "iiiissssdsss",
            $account_id,
            $head_parish_id,
            $sub_id,
            $comm_id,
            $group_id,
            $management_level,
            $type,
            $description,
            $amount,
            $txn_date,
            $admin_id,
            $created_at
        );

        if ($stmt2->execute()) {
            $insertedCount++;
        } else {
            throw new Exception("Insert transaction failed: " . $stmt2->error);
        }

        $stmt2->close();
    }

    $stmt->close();

    // ✅ Update only the revenues we just grouped
    $allIdsToUpdate = array_values(array_unique(array_map('intval', $allIdsToUpdate)));

    if (count($allIdsToUpdate) === 0) {
        throw new Exception("No revenue IDs collected for update.");
    }

    $idsString = implode(',', $allIdsToUpdate);
    $updateSql = "UPDATE $table SET is_posted_to_bank = TRUE WHERE revenue_id IN ($idsString)";

    if (!$conn->query($updateSql)) {
        throw new Exception("Update posted flag failed: " . $conn->error);
    }

    $conn->commit();

    echo json_encode([
        "success" => true,
        "message" => "$insertedCount transaction(s) posted to bank successfully (payment methods separated)."
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}

$conn->close();
