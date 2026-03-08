<?php
header('Content-Type: application/json');

// Database connection
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

// Check if the database connection is successful
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Check if the request method is GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Retrieve book type and size from request
    $bookType = isset($_GET['book_type']) ? $conn->real_escape_string($_GET['book_type']) : '';
    $size = isset($_GET['size']) ? $conn->real_escape_string($_GET['size']) : '';

    // Validate inputs
    if (empty($bookType) || empty($size)) {
        echo json_encode(["success" => false, "message" => "Missing required parameters: book_type or size"]);
        exit();
    }

    // Query to fetch the number of pages for the specified book type and size
    $sql = "SELECT pages FROM church_books WHERE book_type = '$bookType' AND size = '$size' LIMIT 1";

    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $totalPages = $row['pages'];

        // Generate a range of pages
        $pageOptions = range(1, $totalPages);

        echo json_encode([
            "success" => true,
            "book_type" => $bookType,
            "size" => $size,
            "total_pages" => $totalPages,
            "pages" => $pageOptions
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Book not found for the given type and size"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

// Close the database connection
$conn->close();
?>
