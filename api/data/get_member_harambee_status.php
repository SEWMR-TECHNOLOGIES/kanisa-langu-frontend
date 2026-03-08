<?php
header('Content-Type: application/json');
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');

// Check if the database connection is successful
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Ensure it's a GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Retrieve the required parameters
    $harambee_id = isset($_GET['harambee_id']) ? intval($_GET['harambee_id']) : null;
    $member_id = isset($_GET['member_id']) ? intval($_GET['member_id']) : null;
    $target = isset($_GET['target']) ? $_GET['target'] : null;

    // Validate input
    if (!$harambee_id || !$member_id || !$target) {
        echo json_encode(["success" => false, "message" => "Missing required parameters: harambee_id, member_id, or target"]);
        exit();
    }

    // Call the function to get member target and contributions
    $memberDetails = getMemberTargetAndContributions($conn, $harambee_id, $member_id, $target);

    if ($memberDetails === false) {
        echo json_encode(["success" => false, "message" => "Invalid target or unable to fetch details"]);
        exit();
    }

    // Extract target and contribution
    $target_amount = $memberDetails['target_amount'];
    $total_contribution = $memberDetails['total_contribution'];

    // Calculate balance based on the provided logic
    if ($target_amount == 0 && $total_contribution > 0) {
        // If the target is 0 and the contribution is more than 0, balance is set to 0
        $balance = 0;
    } else {
        // Calculate the balance as target - contribution
        $balance = $target_amount - $total_contribution;
    }

    // Apply number formatting to TZS
    $formatted_target = 'TZS ' . number_format($target_amount, 0);
    $formatted_contribution = 'TZS ' . number_format($total_contribution, 0);
    $formatted_balance = 'TZS ' . number_format(abs($balance), 0);
    $balance_text = ($balance < 0) ? 'Extra' : 'Balance';
    // Generate a modern Bootstrap div for the member's information in one paragraph
    $responseDiv = '
        <div class="alert alert-info">
            <p>
                <strong>Member Target:</strong> <span class="text-primary">' . htmlspecialchars($formatted_target) . '</span> | 
                <strong>Total Contribution:</strong> <span class="text-success">' . htmlspecialchars($formatted_contribution) . '</span> | 
                <strong>'.$balance_text.':</strong> <span class="text-danger">' . htmlspecialchars($formatted_balance) . '</span>
            </p>
        </div>
    ';


    // Return the HTML response
    echo json_encode([
        "success" => true,
        "html" => $responseDiv
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
