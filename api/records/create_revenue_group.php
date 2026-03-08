<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

// Ensure head_parish_id is in session
if (!isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Head Parish ID is missing from session"]);
    exit();
}

$head_parish_id = $_SESSION['head_parish_id'];

// Check database connection
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target = isset($_POST['target']) ? $conn->real_escape_string($_POST['target']) : '';
    $account_id = isset($_POST['account_id']) ? intval($_POST['account_id']) : null;
    $sub_parish_id = isset($_POST['sub_parish_id']) ? intval($_POST['sub_parish_id']) : null;
    $community_id = isset($_POST['community_id']) ? intval($_POST['community_id']) : null;
    $group_id = isset($_POST['group_id']) ? intval($_POST['group_id']) : null;
    $revenue_group_name = isset($_POST['revenue_group_name']) ? $conn->real_escape_string($_POST['revenue_group_name']) : '';
    
    // Validate the target input
    if (empty($target) || !in_array($target, ['head-parish', 'sub-parish', 'community', 'group'])) {
        echo json_encode(["success" => false, "message" => "Invalid target specified"]);
        exit();
    }

    // Validate the target input
    if (empty($revenue_group_name)) {
        echo json_encode(["success" => false, "message" => "revenue Group name cannot be blank!"]);
        exit();
    }
    
    // Validate the target input
    if (empty($account_id)) {
        echo json_encode(["success" => false, "message" => "Please select a valid bank account"]);
        exit();
    }
    

    // Initialize variable for the table name, identifier column, and duplicate check query
    $table = '';
    $identifier_column = '';
    $duplicate_check_query = '';

    // Determine the target table and generate the next identifier using switch
    switch ($target) {
        case 'head-parish':
            $table = 'head_parish_revenue_groups';

            // Duplicate check
            $duplicate_check_query = "SELECT COUNT(*) FROM $table WHERE head_parish_id = ? AND revenue_group_name = ?";
            break;

        case 'sub-parish':
            if ($sub_parish_id <= 0) {
                echo json_encode(["success" => false, "message" => "Sub Parish ID is required for sub_parish target"]);
                exit();
            }

            $table = 'sub_parish_revenue_groups';

            // Duplicate check
            $duplicate_check_query = "SELECT COUNT(*) FROM $table WHERE head_parish_id = ? AND sub_parish_id = ? AND revenue_group_name = ?";
            break;

        case 'community':
            if ($community_id <= 0 || $sub_parish_id <= 0) {
                echo json_encode(["success" => false, "message" => "Community and Sub Parish IDs are required for community target"]);
                exit();
            }

            $table = 'community_revenue_groups';

            // Duplicate check
            $duplicate_check_query = "SELECT COUNT(*) FROM $table WHERE head_parish_id = ? AND sub_parish_id = ? AND community_id = ? AND revenue_group_name = ?";
            break;

        case 'group':
            if ($group_id <= 0) {
                echo json_encode(["success" => false, "message" => "Group ID is required for group target"]);
                exit();
            }

            $table = 'group_revenue_groups';

            // Duplicate check
            $duplicate_check_query = "SELECT COUNT(*) FROM $table WHERE head_parish_id = ? AND group_id = ? AND revenue_group_name = ?";
            break;
    }

    // Prepare and execute the duplicate check query
    $stmt = $conn->prepare($duplicate_check_query);
    if ($target === 'head-parish') {
        $stmt->bind_param("is", $head_parish_id, $revenue_group_name);
    } elseif ($target === 'sub-parish') {
        $stmt->bind_param("iis", $head_parish_id, $sub_parish_id, $revenue_group_name);
    } elseif ($target === 'community') {
        $stmt->bind_param("iiis", $head_parish_id, $sub_parish_id, $community_id, $revenue_group_name);
    } elseif ($target === 'group') {
        $stmt->bind_param("iis", $head_parish_id, $group_id, $revenue_group_name);
    }
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    // Check for existing revenue group name
    if ($count > 0) {
        echo json_encode(["success" => false, "message" => "revenue group name already exists for this context"]);
        exit();
    }

    // Switch statement for handling different targets
    switch ($target) {
        case 'head-parish':
            // Validate required fields
            if (empty($revenue_group_name) || is_null($account_id)) {
                echo json_encode(["success" => false, "message" => "revenue group name and account ID are required for head parish"]);
                exit();
            }

            $table = 'head_parish_revenue_groups';
            // Check for duplicate revenue group name
            $duplicate_check_query = "SELECT COUNT(*) AS count FROM $table WHERE head_parish_id = ? AND revenue_group_name = ?";
            $stmt = $conn->prepare($duplicate_check_query);
            $stmt->bind_param("is", $head_parish_id, $revenue_group_name);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            if ($result['count'] > 0) {
                echo json_encode(["success" => false, "message" => "Duplicate revenue group name found for head parish"]);
                exit();
            }

            // Insert the new head parish revenue group
            $insert_query = "INSERT INTO $table (head_parish_id, revenue_group_name, account_id) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("isi", $head_parish_id, $revenue_group_name, $account_id);
            break;

        case 'sub-parish':
            // Validate required fields
            if ($sub_parish_id <= 0 || empty($revenue_group_name) || is_null($account_id)) {
                echo json_encode(["success" => false, "message" => "Sub Parish ID, revenue group name, and account ID are required for sub parish"]);
                exit();
            }

            $table = 'sub_parish_revenue_groups';

            // Check for duplicate revenue group name
            $duplicate_check_query = "SELECT COUNT(*) AS count FROM $table WHERE head_parish_id = ? AND sub_parish_id = ? AND revenue_group_name = ?";
            $stmt = $conn->prepare($duplicate_check_query);
            $stmt->bind_param("iis", $head_parish_id, $sub_parish_id, $revenue_group_name);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            if ($result['count'] > 0) {
                echo json_encode(["success" => false, "message" => "Duplicate revenue group name found for sub parish"]);
                exit();
            }
            
            // Insert the new sub parish revenue group
            $insert_query = "INSERT INTO $table (head_parish_id, sub_parish_id, revenue_group_name, account_id) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("iisi", $head_parish_id, $sub_parish_id, $revenue_group_name, $account_id);
            break;

        case 'community':
            // Validate required fields
            if ($community_id <= 0 || $sub_parish_id <= 0 || empty($revenue_group_name) || is_null($account_id)) {
                echo json_encode(["success" => false, "message" => "Community ID, Sub Parish ID, revenue group name, and account ID are required for community"]);
                exit();
            }

            $table = 'community_revenue_groups';

            // Check for duplicate revenue group name
            $duplicate_check_query = "SELECT COUNT(*) AS count FROM $table WHERE head_parish_id = ? AND sub_parish_id = ? AND community_id = ? AND revenue_group_name = ?";
            $stmt = $conn->prepare($duplicate_check_query);
            $stmt->bind_param("iiis", $head_parish_id, $sub_parish_id, $community_id, $revenue_group_name);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            if ($result['count'] > 0) {
                echo json_encode(["success" => false, "message" => "Duplicate revenue group name found for community"]);
                exit();
            }

            // Insert the new community revenue group
            $insert_query = "INSERT INTO $table (head_parish_id, sub_parish_id, community_id, revenue_group_name, account_id) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("iiisi", $head_parish_id, $sub_parish_id, $community_id, $revenue_group_name, $account_id);
            break;

        case 'group':
            // Validate required fields
            if ($group_id <= 0 || empty($revenue_group_name) || is_null($account_id)) {
                echo json_encode(["success" => false, "message" => "Group ID, revenue group name, and account ID are required for group"]);
                exit();
            }

            $table = 'group_revenue_groups';

            // Check for duplicate revenue group name
            $duplicate_check_query = "SELECT COUNT(*) AS count FROM $table WHERE head_parish_id = ? AND group_id = ? AND revenue_group_name = ?";
            $stmt = $conn->prepare($duplicate_check_query);
            $stmt->bind_param("iis", $head_parish_id, $group_id, $revenue_group_name);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            if ($result['count'] > 0) {
                echo json_encode(["success" => false, "message" => "Duplicate revenue group name found for group"]);
                exit();
            }

            // Insert the new group revenue group
            $insert_query = "INSERT INTO $table (head_parish_id, group_id, revenue_group_name, account_id) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("iisi", $head_parish_id, $group_id, $revenue_group_name, $account_id);
            break;
    }


    // Execute the insertion
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "revenue group recorded successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to record revenue group: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

// Close the database connection
$conn->close();
?>
