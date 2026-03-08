<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Pagination parameters
    $limit = isset($_GET['limit']) ? ($_GET['limit'] === 'all' ? PHP_INT_MAX : intval($_GET['limit'])) : 10;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $offset = ($page - 1) * $limit;

    // Search query if present
    $searchQuery = isset($_GET['query']) ? $conn->real_escape_string($_GET['query']) : '';

    // Fetch the head_parish_id from the session
    if (isset($_SESSION['head_parish_id'])) {
        $head_parish_id = (int)$_SESSION['head_parish_id'];
    } else {
        echo json_encode(["success" => false, "message" => "Head parish ID is not available in the session"]);
        exit();
    }

    // Base query to fetch debits
    $sql = "SELECT debit_id, description, amount, date_debited, return_before_date, purpose, is_paid
            FROM head_parish_debits
            WHERE head_parish_id = ?";

    // Apply search filter if provided
    if (!empty($searchQuery)) {
        $sql .= " AND (description LIKE '%$searchQuery%' OR purpose LIKE '%$searchQuery%')";
    }

    // **Order by date debited**
    $sql .= " ORDER BY date_debited DESC";
    
    // Add pagination
    $sql .= " LIMIT $limit OFFSET $offset";

    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $head_parish_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $debits = [];
        while ($row = $result->fetch_assoc()) {
            // Format the date as Jan 01, 2025
            $formattedDate = date("M d, Y", strtotime($row['date_debited']));
            
            // Format the return date as Jan 01, 2025
            $formattedReturnDate = date("M d, Y", strtotime($row['return_before_date']));
            
            // Format the amount with commas
            $formattedAmount = number_format($row['amount'], 0);

            // Add the formatted data to the array
            $debits[] = [
                'debit_id' => $row['debit_id'],
                'description' => $row['description'],
                'amount' => $formattedAmount,
                'date_debited' => $formattedDate,
                'return_before_date' => $formattedReturnDate,
                'purpose' => $row['purpose'],
                'status' => $row['is_paid'] == 1 ? 'Paid' : 'Unpaid'
            ];
        }

        // Get total records for pagination
        $countSql = "SELECT COUNT(*) as total FROM head_parish_debits WHERE head_parish_id = ?";
        if (!empty($searchQuery)) {
            $countSql .= " AND (description LIKE '%$searchQuery%' OR purpose LIKE '%$searchQuery%')";
        }

        $countStmt = $conn->prepare($countSql);
        $countStmt->bind_param("i", $head_parish_id);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $totalRecords = $countResult->fetch_assoc()['total'];

        $totalPages = ceil($totalRecords / $limit);

        echo json_encode([
            "success" => true,
            "data" => $debits,
            "total_pages" => $totalPages,
            "current_page" => $page
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to fetch debits: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();


?>
