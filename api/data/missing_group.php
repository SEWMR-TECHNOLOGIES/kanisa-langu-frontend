<?php
header('Content-Type: application/json');

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Query to fetch and sum contributions per member per date
    $sql = "SELECT contribution_date, member_id, SUM(amount) AS total_amount
            FROM head_parish_harambee_contribution 
            WHERE member_id IN (333, 334) 
            AND harambee_id = 1
            GROUP BY contribution_date, member_id
            ORDER BY contribution_date ASC";

    $result = $conn->query($sql);

    if ($result) {
        $contributions = [];
        $totalSum = ["member_333" => 0, "member_334" => 0];

        // Organize results by date
        while ($row = $result->fetch_assoc()) {
            $date = $row['contribution_date'];
            $memberId = $row['member_id'];
            $amount = (float) $row['total_amount'];

            // Ensure the date key exists
            if (!isset($contributions[$date])) {
                $contributions[$date] = [
                    "member_333" => "0.00",
                    "member_334" => "0.00"
                ];
            }

            // Assign values based on member ID and sum them
            if ($memberId == 333) {
                $contributions[$date]["member_333"] = number_format($amount, 2, '.', '');
                $totalSum["member_333"] += $amount;
            } elseif ($memberId == 334) {
                $contributions[$date]["member_334"] = number_format($amount, 2, '.', '');
                $totalSum["member_334"] += $amount;
            }
        }

        // Format the total sums
        $totalSum["member_333"] = number_format($totalSum["member_333"], 2, '.', '');
        $totalSum["member_334"] = number_format($totalSum["member_334"], 2, '.', '');

        echo json_encode([
            "success" => true,
            "data" => $contributions,
            "total_sum" => $totalSum
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to fetch data: " . $conn->error]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
