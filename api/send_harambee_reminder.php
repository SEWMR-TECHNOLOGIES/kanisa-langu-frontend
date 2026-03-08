<?php

// Load configurations and helpers
require_once(__DIR__ . '/../config/db_connection.php');
require_once(__DIR__ . '/../utils/helpers.php'); 

// Ensure timezone is set to Africa/Nairobi
date_default_timezone_set('Africa/Nairobi');

// Exit early if DB connection fails
if ($conn->connect_error) {
    error_log("DB connection error: " . $conn->connect_error);
    exit("Database connection failed.");
}

// Get current timestamp for 'notified_at'
$timestamp = date('Y-m-d H:i:s');

// Prepare statement to fetch unsent schedules
$sql = "
    SELECT schedule_id, member_id 
    FROM harambee_sms_schedules 
    WHERE notified = FALSE AND phone IS NOT NULL AND phone != '' 
    LIMIT 10
";
$result = $conn->query($sql);

// Process each unsent record
if ($result && $result->num_rows > 0) {

    // Prepare update statement once
    $updateStmt = $conn->prepare("
        UPDATE harambee_sms_schedules 
        SET notified = TRUE, notified_at = ? 
        WHERE schedule_id = ?
    ");

    if (!$updateStmt) {
        error_log("Failed to prepare update statement: " . $conn->error);
        $conn->close();
        exit("Internal error.");
    }

    while ($row = $result->fetch_assoc()) {
        $scheduleId = $row['schedule_id'];
        $memberId = $row['member_id'];

        // Attempt to send SMS
        $smsSent = sendHarambeeReminder($conn, $memberId);

        if ($smsSent) {
            // Update schedule as notified
            $updateStmt->bind_param("si", $timestamp, $scheduleId);
            if (!$updateStmt->execute()) {
                error_log("Failed to update schedule ID $scheduleId: " . $updateStmt->error);
            }
        }
    }

    $updateStmt->close();

} else {
    error_log("No unsent SMS schedules found or query failed: " . $conn->error);
}

// Clean up
$conn->close();

?>
