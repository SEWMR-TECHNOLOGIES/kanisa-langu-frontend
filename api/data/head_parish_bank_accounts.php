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
    $limit = isset($_GET['limit']) ? ($_GET['limit'] == 'all' ? PHP_INT_MAX : intval($_GET['limit'])) : 5;
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

    // Base query to fetch bank accounts
    $sql = "SELECT
                b.account_id,
                b.account_name, 
                bn.bank_name, 
                b.account_number, 
                b.balance
            FROM head_parish_bank_accounts b
            LEFT JOIN banks bn ON b.bank_id = bn.bank_id
            WHERE b.head_parish_id = ?";

    // Apply search filter if provided
    if (!empty($searchQuery)) {
        $sql .= " AND (b.account_name LIKE '%$searchQuery%' 
                        OR bn.bank_name LIKE '%$searchQuery%' 
                        OR b.account_number LIKE '%$searchQuery%' 
                        OR b.balance LIKE '%$searchQuery%')";
    }

    // Add pagination
    $sql .= " LIMIT $limit OFFSET $offset";
    
    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $head_parish_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $accounts = [];
        while ($row = $result->fetch_assoc()) {
            // Format the balance to 2 decimal places
            $row['balance'] = number_format((float)$row['balance'], 2);
            $accounts[] = $row;
        }

        // Get total records for pagination
        $countSql = "SELECT COUNT(*) as total FROM head_parish_bank_accounts b 
                     LEFT JOIN banks bn ON b.bank_id = bn.bank_id
                     WHERE b.head_parish_id = ?";
        if (!empty($searchQuery)) {
            $countSql .= " AND (b.account_name LIKE '%$searchQuery%' 
                                OR bn.bank_name LIKE '%$searchQuery%' 
                                OR b.account_number LIKE '%$searchQuery%' 
                                OR b.balance LIKE '%$searchQuery%')";
        }
        $countStmt = $conn->prepare($countSql);
        $countStmt->bind_param("i", $head_parish_id);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $totalRecords = $countResult->fetch_assoc()['total'];

        $totalPages = ceil($totalRecords / $limit);

        echo json_encode([
            "success" => true,
            "data" => $accounts,
            "total_pages" => $totalPages,
            "current_page" => $page
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to fetch bank accounts: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
