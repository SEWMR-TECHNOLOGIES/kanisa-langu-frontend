<?php
header('Content-Type: application/json');

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch the diocese_id from the request
    if (isset($_GET['diocese_id'])) {
        $diocese_id = (int)$_GET['diocese_id'];
    } else {
        echo json_encode(["success" => false, "message" => "Diocese ID is not provided"]);
        exit();
    }

    // Query to fetch provinces with diocese, region, and district names based on diocese_id
    $sql = "SELECT 
                p.province_id, p.province_name, p.province_address, p.province_email, p.province_phone, 
                d.diocese_name, 
                r.region_name, 
                dis.district_name
            FROM provinces p
            LEFT JOIN dioceses d ON p.diocese_id = d.diocese_id
            LEFT JOIN regions r ON p.region_id = r.region_id
            LEFT JOIN districts dis ON p.district_id = dis.district_id
            WHERE p.diocese_id = ?";

    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $diocese_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $provinces = [];
        while ($row = $result->fetch_assoc()) {
            $provinces[] = $row;
        }

        echo json_encode([
            "success" => true,
            "data" => $provinces
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to fetch provinces: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
