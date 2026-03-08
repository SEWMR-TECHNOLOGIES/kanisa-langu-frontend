<?php
header('Content-Type: application/json');
session_start(); 

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

// Check database connection
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Handle GET request
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

    // Base query to fetch payment gateway wallets
    $sql = "SELECT
                w.wallet_id,
                w.merchant_code,
                w.client_id,
                w.client_secret,
                b.account_name, 
                b.account_number
            FROM head_parish_payment_gateway_wallets w
            LEFT JOIN head_parish_bank_accounts b ON w.account_id = b.account_id
            WHERE w.head_parish_id = ?";

    // Apply search filter if provided
    if (!empty($searchQuery)) {
        $sql .= " AND (w.merchant_code LIKE '%$searchQuery%' 
                        OR w.client_id LIKE '%$searchQuery%' 
                        OR b.account_name LIKE '%$searchQuery%' 
                        OR b.account_number LIKE '%$searchQuery%')";
    }

    // Add pagination
    $sql .= " LIMIT $limit OFFSET $offset";

    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $head_parish_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $wallets = [];
        while ($row = $result->fetch_assoc()) {
            // Mask sensitive data like client_secret (optional)
            $row['client_secret'] = str_repeat('*', strlen($row['client_secret']) - 4) . substr($row['client_secret'], -4);
            $wallets[] = $row;
        }

        // Get total records for pagination
        $countSql = "SELECT COUNT(*) as total FROM head_parish_payment_gateway_wallets w
                     LEFT JOIN head_parish_bank_accounts b ON w.account_id = b.account_id
                     WHERE w.head_parish_id = ?";
        if (!empty($searchQuery)) {
            $countSql .= " AND (w.merchant_code LIKE '%$searchQuery%' 
                                OR w.client_id LIKE '%$searchQuery%' 
                                OR b.account_name LIKE '%$searchQuery%' 
                                OR b.account_number LIKE '%$searchQuery%')";
        }
        $countStmt = $conn->prepare($countSql);
        $countStmt->bind_param("i", $head_parish_id);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $totalRecords = $countResult->fetch_assoc()['total'];

        $totalPages = ceil($totalRecords / $limit);

        // Return JSON response with data, total pages, and current page
        echo json_encode([
            "success" => true,
            "data" => $wallets,
            "total_pages" => $totalPages,
            "current_page" => $page
        ]);
    } else {
        // Return error response if query execution fails
        echo json_encode(["success" => false, "message" => "Failed to fetch payment gateway wallets: " . $stmt->error]);
    }

    // Close the statement
    $stmt->close();
} else {
    // If request method is not GET
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

// Close the database connection
$conn->close();
?>
