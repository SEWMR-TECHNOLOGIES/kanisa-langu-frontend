<?php
header('Content-Type: application/json');

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $bookId = isset($_GET['book_id']) ? (int) $_GET['book_id'] : 0;

    if ($bookId <= 0) {
        echo json_encode(["success" => false, "message" => "Invalid book ID"]);
        exit();
    }

    // Fetch the number of chapters for the specified book
    $sql = "SELECT number_of_chapters FROM bible WHERE book_id = $bookId";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $numberOfChapters = $row['number_of_chapters'];

        // Generate a range of chapters
        $chapterRange = range(1, $numberOfChapters);

        echo json_encode([
            "success" => true,
            "book_id" => $bookId,
            "chapter_range" => $chapterRange // Returning the range of chapters
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "No chapters found for the specified book"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
