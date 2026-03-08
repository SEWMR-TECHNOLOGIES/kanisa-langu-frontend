<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

if (!isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Missing head parish ID in session"]);
    exit();
}

$head_parish_id = intval($_SESSION['head_parish_id']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit();
}

$program = isset($_POST['program']) ? strtolower(trim($_POST['program'])) : null;
$revenue_stream_id = isset($_POST['revenue_stream_id']) ? intval($_POST['revenue_stream_id']) : null;

$valid_programs = ['harambee', 'bahasha'];

if (!$program || !in_array($program, $valid_programs)) {
    echo json_encode(["success" => false, "message" => "Invalid or missing program"]);
    exit();
}

if (!$revenue_stream_id) {
    echo json_encode(["success" => false, "message" => "Missing revenue stream ID"]);
    exit();
}

// Check if the revenue stream is already mapped to a different program
$conflict_query = "SELECT program FROM program_revenue_map WHERE head_parish_id = ? AND revenue_stream_id = ? AND program != ?";
$conflict_stmt = $conn->prepare($conflict_query);
$conflict_stmt->bind_param("iis", $head_parish_id, $revenue_stream_id, $program);
$conflict_stmt->execute();
$conflict_stmt->store_result();

if ($conflict_stmt->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "This revenue stream is already mapped to another program"]);
    $conflict_stmt->close();
    $conn->close();
    exit();
}
$conflict_stmt->close();

// Check if this program already has a mapping (for update)
$check_query = "SELECT map_id FROM program_revenue_map WHERE head_parish_id = ? AND program = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("is", $head_parish_id, $program);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($map_id);
    $stmt->fetch();
    $stmt->close();

    $update_query = "UPDATE program_revenue_map SET revenue_stream_id = ? WHERE map_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ii", $revenue_stream_id, $map_id);

    if ($update_stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Program mapping updated"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update mapping"]);
    }
    $update_stmt->close();
} else {
    $stmt->close();

    $insert_query = "INSERT INTO program_revenue_map (program, revenue_stream_id, head_parish_id) VALUES (?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("sii", $program, $revenue_stream_id, $head_parish_id);

    if ($insert_stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Program mapped successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to map program"]);
    }
    $insert_stmt->close();
}

$conn->close();
?>
