<?php
header('Content-Type: application/json');
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');

// Check if the database connection is successful
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Ensure it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and validate required parameters
    $harambee_id = isset($_POST['harambee_id']) ? intval($_POST['harambee_id']) : null;
    $member_id = isset($_POST['member_id']) ? intval($_POST['member_id']) : null;
    $target = isset($_POST['target']) ? $_POST['target'] : null;

    if (!$harambee_id || !$member_id || !$target) {
        echo json_encode(["success" => false, "message" => "Missing required parameters."]);
        exit();
    }

    // Determine the target table based on the 'target' parameter
    $target_table = '';
    switch ($target) {
        case 'head-parish':
            $target_table = 'head_parish_harambee_contribution';
            break;
        case 'sub-parish':
            $target_table = 'sub_parish_harambee_contribution';
            break;
        case 'community':
            $target_table = 'community_harambee_contribution';
            break;
        case 'groups':
            $target_table = 'groups_harambee_contribution';
            break;
        default:
            echo json_encode(["success" => false, "message" => "Invalid target type provided."]);
            exit();
    }

    // Fetch member's target and contributions
    $memberDetails = getMemberTargetAndContributions($conn, $harambee_id, $member_id, $target);

    if ($memberDetails === false) {
        echo json_encode(["success" => false, "message" => "Unable to fetch member details."]);
        exit();
    }

    // Extract and calculate values
    $target_amount = $memberDetails['target_amount'];
    $total_contribution = 0;
    $current_balance = $target_amount;

    // Fetch contributions
    $stmt = $conn->prepare("SELECT amount FROM $target_table WHERE member_id = ? AND harambee_id = ?");
    $stmt->bind_param("ii", $member_id, $harambee_id);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $total_contribution += $row['amount']; // Sum the contributions
        }
    }
    $stmt->close();

    // Calculate the balance
    $current_balance = $target_amount - $total_contribution;

    // Return the JSON response with target amount, total contribution, and balance
    echo json_encode([
        "success" => true,
        "target_amount" => $target_amount,
        "total_contribution" => $total_contribution,
        "balance" => $current_balance
    ]);

} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}

$conn->close();
