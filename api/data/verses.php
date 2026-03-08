<?php
header('Content-Type: application/json');

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $bookId = isset($_GET['book_id']) ? (int) $_GET['book_id'] : 0;
    $chapter = isset($_GET['chapter']) ? (int) $_GET['chapter'] : 0;

    if ($bookId <= 0 || $chapter <= 0) {
        echo json_encode(["success" => false, "message" => "Invalid book ID or chapter number"]);
        exit();
    }

    // Fetch the number of verses for the specified chapter
    $sql = "SELECT number_of_verses FROM chapter_verses_count WHERE book_id = $bookId AND chapter = $chapter";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $numberOfVerses = $row['number_of_verses'];

        // Generate a range of verses
        $verseRange = range(1, $numberOfVerses);

        echo json_encode([
            "success" => true,
            "book_id" => $bookId,
            "chapter" => $chapter,
            "verse_range" => $verseRange // Returning the range of verses
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "No verses found for the specified chapter"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
