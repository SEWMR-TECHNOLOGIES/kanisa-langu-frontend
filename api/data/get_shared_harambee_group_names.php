<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch required parameters
    $harambee_id = isset($_GET['harambee_id']) ? intval($_GET['harambee_id']) : null;
    $target = isset($_GET['target']) ? $_GET['target'] : 'head-parish';

    // Validate harambee_id
    if (!$harambee_id) {
        echo json_encode(["success" => false, "message" => "Harambee ID is required"]);
        exit();
    }

    // Fetch the head_parish_id from the session
    if (isset($_SESSION['head_parish_id'])) {
        $head_parish_id = (int)$_SESSION['head_parish_id'];
    } else {
        echo json_encode(["success" => false, "message" => "Head parish ID is not available in the session"]);
        exit();
    }

    // Determine the correct table based on the target
    $tableMap = [
        'head-parish' => 'hp_group_harambee_target_information',
        'sub-parish' => 'sp_group_harambee_target_information',
        'community' => 'com_group_harambee_target_information',
        'groups' => 'gp_group_harambee_target_information'
    ];

    if (!array_key_exists($target, $tableMap)) {
        echo json_encode(["success" => false, "message" => "Invalid target specified"]);
        exit();
    }

    $tableName = $tableMap[$target];

    // Prepare the query
    $sql = "SELECT id, group_name
            FROM $tableName
            WHERE harambee_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $harambee_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $groupData = [];
        while ($row = $result->fetch_assoc()) {
            $groupData[] = [
                "id" => $row['id'],
                "group_name" => $row['group_name']
            ];
        }

        echo json_encode([
            "success" => true,
            "data" => $groupData
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to fetch group data: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
