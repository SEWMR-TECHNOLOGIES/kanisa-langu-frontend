<?php
header('Content-Type: application/json');
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

// Set timezone once
date_default_timezone_set('Africa/Nairobi');
$now = new DateTime(); // Automatically uses 'Africa/Nairobi'

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Cannot access database. Please contact support."
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Unsupported request method. Use GET to fetch church events."
    ]);
    exit();
}

if (empty($_GET['headParishId']) || !is_numeric($_GET['headParishId'])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Valid headParishId parameter is required to fetch events."
    ]);
    exit();
}

$headParishId = (int)$_GET['headParishId'];

$sql = "
    SELECT 
        ce.id,
        ce.title,
        ce.description,
        ce.event_date,
        ce.end_date,
        ce.start_time,
        ce.end_time,
        ce.location,
        ce.created_by,
        ce.head_parish_id,
        ce.created_at,
        ce.updated_at,
        ce.target_audience,
        ce.notes,
        hpa.head_parish_admin_fullname AS created_by_name
    FROM church_events ce
    JOIN head_parish_admins hpa ON hpa.head_parish_admin_id = ce.created_by
    WHERE ce.head_parish_id = ?
    ORDER BY ce.id DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $headParishId);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    $events = [];

    while ($row = $result->fetch_assoc()) {
        $eventDateStr = $row['event_date'];
        $startTimeStr = $row['start_time'];
        $endDateStr = $row['end_date'];
    
        $startDateTime = DateTime::createFromFormat('Y-m-d H:i:s', "$eventDateStr $startTimeStr");
    
        if (!empty($endDateStr)) {
            $endTimeStr = $row['end_time'] ?: '23:59:59';
            $endDateTime = DateTime::createFromFormat('Y-m-d H:i:s', "$endDateStr $endTimeStr");
        } else {
            $endDateTime = DateTime::createFromFormat('Y-m-d H:i:s', "$eventDateStr 23:59:59");
        }
    
        $isActive = ($now >= $startDateTime) && ($now <= $endDateTime);
    
        $row['is_active'] = $isActive;
        $events[] = $row;
    }


    echo json_encode([
        "success" => true,
        "data" => $events
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Unable to retrieve events at this time. Please try again later."
    ]);
}

$stmt->close();
$conn->close();
?>
