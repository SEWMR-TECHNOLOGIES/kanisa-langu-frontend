<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $target = isset($_GET['target']) ? $_GET['target'] : 'head-parish';
    $revenueGroupId = isset($_GET['revenue_group_id']) ? intval($_GET['revenue_group_id']) : null;

    if ($revenueGroupId === null) {
        echo json_encode(["success" => false, "message" => "Revenue group ID is required"]);
        exit();
    }

    // Build SQL based on target
    switch ($target) {
        case 'sub-parish':
            $sql = "SELECT r.revenue_stream_id, r.revenue_stream_name 
                    FROM sub_parish_revenue_groups_map m
                    JOIN head_parish_revenue_streams r ON m.revenue_stream_id = r.revenue_stream_id
                    WHERE m.revenue_group_id = ?";
            break;

        case 'community':
            $sql = "SELECT r.revenue_stream_id, r.revenue_stream_name 
                    FROM community_revenue_groups_map m
                    JOIN head_parish_revenue_streams r ON m.revenue_stream_id = r.revenue_stream_id
                    WHERE m.revenue_group_id = ?";
            break;

        case 'group':
            $sql = "SELECT r.revenue_stream_id, r.revenue_stream_name 
                    FROM group_revenue_groups_map m
                    JOIN head_parish_revenue_streams r ON m.revenue_stream_id = r.revenue_stream_id
                    WHERE m.revenue_group_id = ?";
            break;

        case 'head-parish':
        default:
            $sql = "SELECT r.revenue_stream_id, r.revenue_stream_name 
                    FROM head_parish_revenue_groups_map m
                    JOIN head_parish_revenue_streams r ON m.revenue_stream_id = r.revenue_stream_id
                    WHERE m.revenue_group_id = ?";
            break;
    }

    // Prepare and execute
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $revenueGroupId);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $revenueStreams = [];

        while ($row = $result->fetch_assoc()) {
            $revenueStreams[] = $row;
        }

        echo json_encode([
            "success" => true,
            "data" => $revenueStreams
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to fetch revenue streams: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
