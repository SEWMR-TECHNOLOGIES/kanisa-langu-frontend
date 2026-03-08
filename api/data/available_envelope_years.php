<?php
header('Content-Type: application/json');

// Start the session and check for required configuration
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

// Check if head_parish_id is in session
if (!isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Head Parish ID is missing from session"]);
    exit();
}

$head_parish_id = $_SESSION['head_parish_id'];

// Check the database connection
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Function to fetch unique years from envelope_targets table for a given head_parish_id
function getUniqueYearsFromEnvelopeTargets($conn, $head_parish_id) {
    $years = [];

    // SQL query to fetch unique years from from_date and end_date filtered by head_parish_id
    $sql = "SELECT DISTINCT YEAR(from_date) AS year FROM envelope_targets 
            WHERE head_parish_id = ?
            UNION 
            SELECT DISTINCT YEAR(end_date) AS year FROM envelope_targets 
            WHERE head_parish_id = ?
            ORDER BY year ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $head_parish_id, $head_parish_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $years[] = $row['year'];
        }
    } else {
        return ["success" => false, "message" => "No years found."];
    }

    return ["success" => true, "years" => $years];
}

// Call the function and return the result as JSON
$response = getUniqueYearsFromEnvelopeTargets($conn, $head_parish_id);
echo json_encode($response);

$conn->close();
?>
