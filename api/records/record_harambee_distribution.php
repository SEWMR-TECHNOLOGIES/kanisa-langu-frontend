<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');

// Check if head_parish_id is in session
if (!isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Head Parish ID is missing from session"]);
    exit();
}

$head_parish_id = $_SESSION['head_parish_id'];

// Check the database connection
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $harambee_id = isset($_POST['harambee_id']) ? intval($_POST['harambee_id']) : 0;
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0.00;
    $target = isset($_POST['target']) ? $conn->real_escape_string($_POST['target']) : '';
    $percentage = isset($_POST['percentage']) ? floatval($_POST['percentage']) : 0.00; 

    // Validate basic inputs
    if ($harambee_id <= 0) {
        echo json_encode(["success" => false, "message" => "Please select Harambee"]);
        exit();
    }
    // Check for amount and percentage
    if ($amount <= 0 && $percentage <= 0) {
        echo json_encode(["success" => false, "message" => "At least Amount or Percentage must be set"]);
        exit();
    }
    
    // Calculate amount if percentage is set
    if ($percentage > 0) {
        
        if ($percentage > 100) {
            echo json_encode(["success" => false, "message" => "Percentage must be between 0 and 100"]);
            exit();
        }
        // Fetch harambee details to get the base amount
        $harambee_details = get_harambee_details($conn, $harambee_id, $target);
        if ($harambee_details) {
            $base_amount = $harambee_details['amount'];
            $amount = ($base_amount * $percentage) / 100; 
        } else {
            echo json_encode(["success" => false, "message" => "Harambee details not found"]);
            exit();
        }
    }
    
    if (empty($target)) {
        echo json_encode(["success" => false, "message" => "Distribution target is required"]);
        exit();
    }

    // Begin transaction
    $conn->begin_transaction();
    $stmt = null;

    switch ($target) {
        case 'head_parish':
            $sub_parish_id = isset($_POST['sub_parish_id']) ? intval($_POST['sub_parish_id']) : 0;
            if ($sub_parish_id <= 0) {
                echo json_encode(["success" => false, "message" => "Sub Parish is required"]);
                exit();
            }

            // Check if a record exists
            $stmt = $conn->prepare("SELECT * FROM head_parish_harambee_distribution WHERE harambee_id = ? AND head_parish_id = ? AND sub_parish_id = ?");
            $stmt->bind_param("iii", $harambee_id, $head_parish_id, $sub_parish_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Update the existing record
                $stmt = $conn->prepare("UPDATE head_parish_harambee_distribution SET amount = ? WHERE harambee_id = ? AND head_parish_id = ? AND sub_parish_id = ?");
                $stmt->bind_param("diii", $amount, $harambee_id, $head_parish_id, $sub_parish_id);
            } else {
                // Insert a new record
                $stmt = $conn->prepare("INSERT INTO head_parish_harambee_distribution (harambee_id, head_parish_id, sub_parish_id, amount) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiid", $harambee_id, $head_parish_id, $sub_parish_id, $amount);
            }
            break;

        case 'sub_parish':
            $sub_parish_id = isset($_POST['sub_parish_id']) ? intval($_POST['sub_parish_id']) : 0;
            if ($sub_parish_id <= 0) {
                echo json_encode(["success" => false, "message" => "Sub Parish is required"]);
                exit();
            }

            // Check if a record exists
            $stmt = $conn->prepare("SELECT * FROM sub_parish_harambee_distribution WHERE harambee_id = ? AND head_parish_id = ? AND sub_parish_id = ?");
            $stmt->bind_param("iii", $harambee_id, $head_parish_id, $sub_parish_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Update the existing record
                $stmt = $conn->prepare("UPDATE sub_parish_harambee_distribution SET amount = ? WHERE harambee_id = ? AND head_parish_id = ? AND sub_parish_id = ?");
                $stmt->bind_param("diii", $amount, $harambee_id, $head_parish_id, $sub_parish_id);
            } else {
                // Insert a new record
                $stmt = $conn->prepare("INSERT INTO sub_parish_harambee_distribution (harambee_id, head_parish_id, sub_parish_id, amount) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiid", $harambee_id, $head_parish_id, $sub_parish_id, $amount);
            }
            break;

        case 'community':
            $sub_parish_id = isset($_POST['sub_parish_id']) ? intval($_POST['sub_parish_id']) : 0;
            $community_id = isset($_POST['community_id']) ? intval($_POST['community_id']) : 0;
            if ($community_id <= 0 || $sub_parish_id <= 0) {
                echo json_encode(["success" => false, "message" => "Community and Sub Parish are required"]);
                exit();
            }

            // Check if a record exists
            $stmt = $conn->prepare("SELECT * FROM community_harambee_distribution WHERE harambee_id = ? AND head_parish_id = ? AND sub_parish_id = ? AND community_id = ?");
            $stmt->bind_param("iiii", $harambee_id, $head_parish_id, $sub_parish_id, $community_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Update the existing record
                $stmt = $conn->prepare("UPDATE community_harambee_distribution SET amount = ? WHERE harambee_id = ? AND head_parish_id = ? AND sub_parish_id = ? AND community_id = ?");
                $stmt->bind_param("diiii", $amount, $harambee_id, $head_parish_id, $sub_parish_id, $community_id);
            } else {
                // Insert a new record
                $stmt = $conn->prepare("INSERT INTO community_harambee_distribution (harambee_id, head_parish_id, sub_parish_id, community_id, amount) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iiidi", $harambee_id, $head_parish_id, $sub_parish_id, $community_id, $amount);
            }
            break;

        case 'group':
            $group_id = isset($_POST['group_id']) ? intval($_POST['group_id']) : 0;
            if ($group_id <= 0) {
                echo json_encode(["success" => false, "message" => "Group is required"]);
                exit();
            }

            // Check if a record exists
            $stmt = $conn->prepare("SELECT * FROM group_harambee_distribution WHERE harambee_id = ? AND head_parish_id = ? AND group_id = ?");
            $stmt->bind_param("iii", $harambee_id, $head_parish_id, $group_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Update the existing record
                $stmt = $conn->prepare("UPDATE group_harambee_distribution SET amount = ? WHERE harambee_id = ? AND head_parish_id = ? AND group_id = ?");
                $stmt->bind_param("diii", $amount, $harambee_id, $head_parish_id, $group_id);
            } else {
                // Insert a new record
                $stmt = $conn->prepare("INSERT INTO group_harambee_distribution (harambee_id, head_parish_id, group_id, amount) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiid", $harambee_id, $head_parish_id, $group_id, $amount);
            }
            break;

        default:
            echo json_encode(["success" => false, "message" => "Invalid distribution target"]);
            exit();
    }

    // Execute the statement
    if ($stmt->execute()) {
        $conn->commit(); // Commit transaction
        echo json_encode(["success" => true, "message" => "Harambee distribution recorded successfully"]);
    } else {
        $conn->rollback(); // Rollback transaction on failure
        echo json_encode(["success" => false, "message" => "Failed to record distribution: " . $stmt->error]);
    }

    // Close the statement
    $stmt->close();
} else {
    // If the request method is not POST
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

// Close the database connection
$conn->close();
?>
