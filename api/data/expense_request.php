<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $expenseId = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $target = isset($_GET['target']) ? $_GET['target'] : 'head-parish';

    if (!isset($_SESSION['head_parish_id'])) {
        echo json_encode(["success" => false, "message" => "Head parish ID is not available in the session"]);
        exit();
    }

    $session_head_parish_id = (int)$_SESSION['head_parish_id'];

    // Initialize optional variables
    $sub_parish_id = null;
    $community_id = null;
    $group_id = null;

    $data = getExpenseIdsFromRequestId($conn, $expenseId, $target);

    if (isset($data['error'])) {
        echo json_encode(["success" => false, "message" => $data['error']]);
        exit();
    }

    switch ($target) {
        case 'head-parish':
            $head_parish_id = $data['head_parish_id'];
            $expense_name_id = $data['expense_name_id'];
            $date = $data['request_date'];
            break;
        case 'sub-parish':
            $sub_parish_id = $data['sub_parish_id'];
            $expense_name_id = $data['expense_name_id'];
            $date = $data['request_date'];
            $head_parish_id = $session_head_parish_id;
            break;
        case 'community':
            $sub_parish_id = $data['sub_parish_id'];
            $community_id = $data['community_id'];
            $expense_name_id = $data['expense_name_id'];
            $date = $data['request_date'];
            $head_parish_id = $session_head_parish_id;
            break;
        case 'group':
            $group_id = $data['group_id'];
            $expense_name_id = $data['expense_name_id'];
            $date = $data['request_date'];
            $head_parish_id = $session_head_parish_id;
            break;
        default:
            echo json_encode(["success" => false, "message" => "Invalid target type"]);
            exit();
    }

    $summaryData = getBudgetAndExpenseSummaryByDate(
        $conn,
        $date,
        $head_parish_id,
        $expense_name_id,
        $target,
        $sub_parish_id,
        $community_id,
        $group_id
    );

    switch ($target) {
        case 'sub-parish':
            $sql = "SELECT
                        spr.request_id,
                        spr.request_amount,
                        spr.request_datetime,
                        spr.request_description,
                        spr.pastor_approval,
                        spr.pastor_approval_datetime,
                        spr.chairperson_approval,
                        spr.chairperson_approval_datetime,
                        sp.sub_parish_name,
                        eg.expense_group_name,
                        en.expense_name
                    FROM sub_parish_expense_requests spr
                    JOIN sub_parishes sp ON spr.sub_parish_id = sp.sub_parish_id
                    JOIN sub_parish_expense_groups eg ON spr.expense_group_id = eg.expense_group_id
                    JOIN sub_parish_expense_names en ON spr.expense_name_id = en.expense_name_id
                    WHERE spr.request_id = ? AND spr.head_parish_id = ?";
            break;

        case 'community':
            $sql = "SELECT
                        cr.request_id,
                        cr.request_amount,
                        cr.request_datetime,
                        cr.request_description,
                        cr.pastor_approval,
                        cr.pastor_approval_datetime,
                        cr.chairperson_approval,
                        cr.chairperson_approval_datetime,
                        c.community_name,
                        eg.expense_group_name,
                        en.expense_name
                    FROM community_expense_requests cr
                    JOIN communities c ON cr.community_id = c.community_id
                    JOIN community_expense_groups eg ON cr.expense_group_id = eg.expense_group_id
                    JOIN community_expense_names en ON cr.expense_name_id = en.expense_name_id
                    WHERE cr.request_id = ? AND cr.head_parish_id = ?";
            break;

        case 'group':
            $sql = "SELECT
                        gr.request_id,
                        gr.request_amount,
                        gr.request_datetime,
                        gr.request_description,
                        gr.pastor_approval,
                        gr.pastor_approval_datetime,
                        gr.chairperson_approval,
                        gr.chairperson_approval_datetime,
                        g.group_name,
                        eg.expense_group_name,
                        en.expense_name
                    FROM group_expense_requests gr
                    JOIN groups g ON gr.group_id = g.group_id
                    JOIN group_expense_groups eg ON gr.expense_group_id = eg.expense_group_id
                    JOIN group_expense_names en ON gr.expense_name_id = en.expense_name_id
                    WHERE gr.request_id = ? AND gr.head_parish_id = ?";
            break;

        case 'head-parish':
        default:
            $sql = "SELECT
                        hpr.request_id,
                        hpr.request_amount,
                        hpr.request_datetime,
                        hpr.request_description,
                        hpr.pastor_approval,
                        hpr.pastor_approval_datetime,
                        hpr.chairperson_approval,
                        hpr.chairperson_approval_datetime,
                        hp.head_parish_name,
                        eg.expense_group_name,
                        en.expense_name
                    FROM head_parish_expense_requests hpr
                    JOIN head_parishes hp ON hpr.head_parish_id = hp.head_parish_id
                    JOIN head_parish_expense_groups eg ON hpr.expense_group_id = eg.expense_group_id
                    JOIN head_parish_expense_names en ON hpr.expense_name_id = en.expense_name_id
                    WHERE hpr.request_id = ? AND hpr.head_parish_id = ?";
            break;
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $expenseId, $head_parish_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $expenseData = $result->fetch_assoc();

            // Format date fields
            if (!empty($expenseData['request_datetime'])) {
                $dateTime = new DateTime($expenseData['request_datetime']);
                $expenseData['request_datetime'] = $dateTime->format('d M Y, H:i');
            }

            if (!empty($expenseData['chairperson_approval_datetime'])) {
                $dateTime = new DateTime($expenseData['chairperson_approval_datetime']);
                $expenseData['chairperson_approval_datetime'] = $dateTime->format('d M Y, H:i');
            }

            if (!empty($expenseData['pastor_approval_datetime'])) {
                $dateTime = new DateTime($expenseData['pastor_approval_datetime']);
                $expenseData['pastor_approval_datetime'] = $dateTime->format('d M Y, H:i');
            }

            $expenseData['request_amount'] = number_format($expenseData['request_amount'], 0);

            echo json_encode([
                "success" => true,
                "data" => $expenseData,
                "summary" => $summaryData
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "No expense found for the given ID."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Failed to fetch expense details: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
