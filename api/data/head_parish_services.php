<?php
header('Content-Type: application/json');
session_start();

// Include the database connection
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

// Check for a database connection error
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Check if the request is a GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // Check if the head parish ID is set in the session
    if (isset($_SESSION['head_parish_id'])) {
        $head_parish_id = (int)$_SESSION['head_parish_id'];
    } else {
        echo json_encode(["success" => false, "message" => "Head parish ID is not available in the session"]);
        exit();
    }

    // Fetch the services_count for the head parish
    $sql = "SELECT services_count FROM head_parishes WHERE head_parish_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $head_parish_id);

    if ($stmt->execute()) {
        $stmt->bind_result($services_count);
        $stmt->fetch();
        $stmt->close();

        // Check if the services_count is valid
        if ($services_count > 0) {
            // Fetch the service times from the head_parish_services table
            $serviceSql = "SELECT service, start_time FROM head_parish_services WHERE head_parish_id = ?";
            $serviceStmt = $conn->prepare($serviceSql);
            $serviceStmt->bind_param("i", $head_parish_id);
            $serviceStmt->execute();
            $result = $serviceStmt->get_result();
            
            $serviceTimes = [];
            while ($row = $result->fetch_assoc()) {
                // Format the time to AM/PM
                $formattedTime = date("h:i A", strtotime($row['start_time']));
                $serviceTimes[$row['service']] = $formattedTime;
            }
            $serviceStmt->close();

            // Generate an array of services based on the services_count
            $services = [];
            for ($i = 1; $i <= $services_count; $i++) {
                $services[] = [
                    "service_id" => $i,
                    "service" => $i,
                    "time" => isset($serviceTimes[$i]) ? $serviceTimes[$i] : null
                ];
            }

            // Return the services_count and generated services list
            echo json_encode([
                "success" => true,
                "services_count" => $services_count,
                "data" => $services
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "No services found for the given head parish"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Failed to fetch services count: " . $stmt->error]);
    }

} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
