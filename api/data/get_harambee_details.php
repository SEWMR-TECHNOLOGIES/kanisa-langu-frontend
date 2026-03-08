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
    $target = isset($_GET['target']) ? $_GET['target'] : null;

    // Validate input
    if (!$harambee_id || !$target) {
        echo json_encode(["success" => false, "message" => "Missing required parameters: harambee_id or target"]);
        exit();
    }

    // Call the function to get Harambee details
    $harambeeDetails = get_harambee_details($conn, $harambee_id, $target);

    if ($harambeeDetails === false) {
        echo json_encode(["success" => false, "message" => "Invalid target or unable to fetch details"]);
        exit();
    }

    // Extract details
    $description = htmlspecialchars($harambeeDetails['description']);
    $from_date = date('d M Y', strtotime($harambeeDetails['from_date']));
    $to_date = date('d M Y', strtotime($harambeeDetails['to_date']));
    $amount = 'TZS ' . number_format($harambeeDetails['amount'], 0);

    // Encrypt harambee_id for the download link
    $encrypted_harambee_id = encryptData($harambee_id);

    if ($target == 'head_parsih') {
        $target = 'head-parish';
    }
    if ($target == 'sub_parsih') {
        $target = 'sub-parish';
    }
    
    // Generate the Bootstrap div with the Harambee information
    $responseDiv = '
        <div class="alert alert-info">
            Harambee ya ' . $description . ' kutoka ' . $from_date . ' mpaka ' . $to_date . '. Lengo: ' . $amount . '
        </div>
        <div class="text-right mt-3">
            <a target="_blank" href="/reports/head_parish_harambee_report.php?harambee=' . urlencode($encrypted_harambee_id) . '&target=' . urlencode($target) . '" class="btn btn-success">
                <i class="fas fa-file-pdf"></i> Download Report
            </a>
        </div>';

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