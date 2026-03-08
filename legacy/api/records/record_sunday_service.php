<?php
header('Content-Type: application/json');
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

// Ensure the head_parish_id is set in the session
if (!isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Head Parish ID is missing from session"]);
    exit();
}

$head_parish_id = $_SESSION['head_parish_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_date = isset($_POST['service_date']) ? $conn->real_escape_string($_POST['service_date']) : null;

    if (empty($service_date)) {
        echo json_encode(["success" => false, "message" => "Service date is required"]);
        exit();
    }

    // Extract POST data
    $fields = [
        'service_color_id' => isset($_POST['service_color']) ? intval($_POST['service_color']) : null,
        'large_liturgy_page_number' => isset($_POST['large_liturgy_page_number']) ? intval($_POST['large_liturgy_page_number']) : null,
        'small_liturgy_page_number' => isset($_POST['small_liturgy_page_number']) ? intval($_POST['small_liturgy_page_number']) : null,
        'large_antiphony_page_number' => isset($_POST['large_antiphony_page_number']) ? intval($_POST['large_antiphony_page_number']) : null,
        'small_antiphony_page_number' => isset($_POST['small_antiphony_page_number']) ? intval($_POST['small_antiphony_page_number']) : null,
        'large_praise_page_number' => isset($_POST['large_praise_page_number']) ? intval($_POST['large_praise_page_number']) : null,
        'small_praise_page_number' => isset($_POST['small_praise_page_number']) ? intval($_POST['small_praise_page_number']) : null,
        'base_scripture_text' => isset($_POST['base_scripture_text']) ? $conn->real_escape_string($_POST['base_scripture_text']) : null,
    ];

    // Check if a record exists for the given date
    $check_sql = "SELECT service_id as id FROM sunday_services WHERE service_date = ? AND head_parish_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $service_date, $head_parish_id);
    $check_stmt->execute();
    $check_stmt->bind_result($service_id);
    $record_exists = $check_stmt->fetch();
    $check_stmt->close();

    if ($record_exists) {
        // Update record
        $update_fields = [];
        $params = [];
        $types = '';

        foreach ($fields as $key => $value) {
            if (!empty($value)) {
                $update_fields[] = "$key = ?";
                $params[] = $value;
                $types .= is_int($value) ? 'i' : 's';
            }
        }

        if (!empty($update_fields)) {
            $update_sql = "UPDATE sunday_services SET " . implode(", ", $update_fields) . " WHERE service_id = ?";
            $types .= 'i';
            $params[] = $service_id;

            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param($types, ...$params);

            if ($update_stmt->execute()) {
                echo json_encode(["success" => true, "message" => "Sunday service updated successfully"]);
            } else {
                echo json_encode(["success" => false, "message" => "Failed to update Sunday service"]);
            }
            $update_stmt->close();
        } else {
            echo json_encode(["success" => false, "message" => "No valid fields to update"]);
        }
    } else {
        // Validate required fields for insertion
        $required_fields = [
            'service_color_id' => 'Service color',
            'large_liturgy_page_number' => 'Large liturgy page number',
            'small_liturgy_page_number' => 'Small liturgy page number',
            'large_antiphony_page_number' => 'Large antiphony page number',
            'small_antiphony_page_number' => 'Small antiphony page number',
            'large_praise_page_number' => 'Large praise page number',
            'small_praise_page_number' => 'Small praise page number',
            'base_scripture_text' => 'Base scripture text',
        ];

        foreach ($required_fields as $key => $label) {
            if (empty($fields[$key])) {
                echo json_encode(["success" => false, "message" => "$label is required for insertion"]);
                exit();
            }
        }

        // Insert new record
        $insert_sql = "INSERT INTO sunday_services (
            service_date, head_parish_id, service_color_id, large_liturgy_page_number, 
            small_liturgy_page_number, large_antiphony_page_number, small_antiphony_page_number,
            large_praise_page_number, small_praise_page_number, base_scripture_text
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param(
            "siiiiiiiis",
            $service_date,
            $head_parish_id,
            $fields['service_color_id'],
            $fields['large_liturgy_page_number'],
            $fields['small_liturgy_page_number'],
            $fields['large_antiphony_page_number'],
            $fields['small_antiphony_page_number'],
            $fields['large_praise_page_number'],
            $fields['small_praise_page_number'],
            $fields['base_scripture_text']
        );

        if ($insert_stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Sunday service added successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to add Sunday service"]);
        }

        $insert_stmt->close();
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
