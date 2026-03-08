<?php
header('Content-Type: application/json');
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

// Ensure the head_parish_id is set in session
if (!isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Head Parish ID is missing from session"]);
    exit();
}

$head_parish_id = $_SESSION['head_parish_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
    $book_id = isset($_POST['book_id']) ? intval($_POST['book_id']) : 0;
    $chapter = isset($_POST['chapter']) ? intval($_POST['chapter']) : 0;
    $starting_verse_number = isset($_POST['starting_verse_number']) ? intval($_POST['starting_verse_number']) : 0;
    $ending_verse_number = isset($_POST['ending_verse_number']) && $_POST['ending_verse_number'] !== '' ? intval($_POST['ending_verse_number']) : null;

    // Validate required fields
    if ($service_id <= 0 || $book_id <= 0 || $chapter <= 0 || $starting_verse_number <= 0) {
        echo json_encode(["success" => false, "message" => "Service, Book, Chapter, and Starting Verse are required"]);
        exit();
    }

    // Check if ending verse is provided (not null and not empty) and validate
    if ($ending_verse_number !== null) {
        // Ending verse must be greater than starting verse
        if ($ending_verse_number < $starting_verse_number) {
            echo json_encode(["success" => false, "message" => "Ending Verse must be greater than Starting Verse"]);
            exit();
        }
        // Starting verse cannot be the same as ending verse
        if ($ending_verse_number == $starting_verse_number) {
            echo json_encode(["success" => false, "message" => "Starting Verse and Ending Verse cannot be the same"]);
            exit();
        }
    }

    // Prepare the SQL query
    $sql = "INSERT INTO service_scriptures (service_id, book_id, chapter, starting_verse_number, ending_verse_number) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiii", $service_id, $book_id, $chapter, $starting_verse_number, $ending_verse_number);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Scripture added to service successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to add scripture to service"]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
