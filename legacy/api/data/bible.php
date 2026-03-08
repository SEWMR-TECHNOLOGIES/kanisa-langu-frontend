<?php
header('Content-Type: application/json');

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Query to get all Bible books
    $sql = "SELECT book_id, book_name_en, book_name_sw FROM bible";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $books = [];
        while ($row = $result->fetch_assoc()) {
            $books[] = [
                'book_id' => $row['book_id'],
                'book_name_sw' => $row['book_name_sw']
            ];
        }

        echo json_encode([
            "success" => true,
            "data" => $books // Returning the list of Bible books
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "No books found"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
