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
    $expense_group_name = isset($_POST['expense_group_name']) ? $conn->real_escape_string($_POST['expense_group_name']) : '';
    
    // Validate the target input
    if (empty($target) || !in_array($target, ['head-parish', 'sub-parish', 'community', 'group'])) {
        echo json_encode(["success" => false, "message" => "Invalid target specified"]);
        exit();
    }

    // Validate the target input
    if (empty($expense_group_name)) {
        echo json_encode(["success" => false, "message" => "Expense Group name cannot be blank!"]);
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
            $table = 'head_parish_expense_groups';
            $identifier_column = 'expense_group_identifier';

            // Duplicate check
            $duplicate_check_query = "SELECT COUNT(*) FROM $table WHERE head_parish_id = ? AND expense_group_name = ?";
            break;

        case 'sub-parish':
            if ($sub_parish_id <= 0) {
                echo json_encode(["success" => false, "message" => "Sub Parish ID is required for sub_parish target"]);
                exit();
            }

            $table = 'sub_parish_expense_groups';
            $identifier_column = 'expense_group_identifier';

            // Duplicate check
            $duplicate_check_query = "SELECT COUNT(*) FROM $table WHERE head_parish_id = ? AND sub_parish_id = ? AND expense_group_name = ?";
            break;

        case 'community':
            if ($community_id <= 0 || $sub_parish_id <= 0) {
                echo json_encode(["success" => false, "message" => "Community and Sub Parish IDs are required for community target"]);
                exit();
            }

            $table = 'community_expense_groups';
            $identifier_column = 'expense_group_identifier';

            // Duplicate check
            $duplicate_check_query = "SELECT COUNT(*) FROM $table WHERE head_parish_id = ? AND sub_parish_id = ? AND community_id = ? AND expense_group_name = ?";
            break;

        case 'group':
            if ($group_id <= 0) {
                echo json_encode(["success" => false, "message" => "Group ID is required for group target"]);
                exit();
            }

            $table = 'group_expense_groups';
            $identifier_column = 'expense_group_identifier';

            // Duplicate check
            $duplicate_check_query = "SELECT COUNT(*) FROM $table WHERE head_parish_id = ? AND group_id = ? AND expense_group_name = ?";
            break;
    }

    // Prepare and execute the duplicate check query
    $stmt = $conn->prepare($duplicate_check_query);
    if ($target === 'head-parish') {
        $stmt->bind_param("is", $head_parish_id, $expense_group_name);
    } elseif ($target === 'sub-parish') {
        $stmt->bind_param("iis", $head_parish_id, $sub_parish_id, $expense_group_name);
    } elseif ($target === 'community') {
        $stmt->bind_param("iiis", $head_parish_id, $sub_parish_id, $community_id, $expense_group_name);
    } elseif ($target === 'group') {
        $stmt->bind_param("iis", $head_parish_id, $group_id, $expense_group_name);
    }
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    // Check for existing expense group name
    if ($count > 0) {
        echo json_encode(["success" => false, "message" => "Expense group name already exists for this context"]);
        exit();
    }

    // Switch statement for handling different targets
    switch ($target) {
        case 'head-parish':
            // Validate required fields
            if (empty($expense_group_name) || is_null($account_id)) {
                echo json_encode(["success" => false, "message" => "Expense group name and account ID are required for head parish"]);
                exit();
            }

            $table = 'head_parish_expense_groups';
            $identifier_column = 'expense_group_identifier';
            // Check for duplicate expense group name
            $duplicate_check_query = "SELECT COUNT(*) AS count FROM $table WHERE head_parish_id = ? AND expense_group_name = ?";
            $stmt = $conn->prepare($duplicate_check_query);
            $stmt->bind_param("is", $head_parish_id, $expense_group_name);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            if ($result['count'] > 0) {
                echo json_encode(["success" => false, "message" => "Duplicate expense group name found for head parish"]);
                exit();
            }
            
            // Get the highest identifier and calculate the next letter
            $query = "SELECT MAX($identifier_column) AS max_identifier FROM $table WHERE head_parish_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $head_parish_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            // Check if there is a result and if it's not null
            $current_identifier = $result['max_identifier'] ?? null;
            
            // Set next_identifier based on the current identifier
            if ($current_identifier === null) {
                $next_identifier = 'A'; // Start from 'A' if no records exist
            } else {
                $next_identifier = chr(ord($current_identifier) + 1);
            }

            // Insert the new head parish expense group
            $insert_query = "INSERT INTO $table (head_parish_id, expense_group_name, $identifier_column, account_id) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("issi", $head_parish_id, $expense_group_name, $next_identifier, $account_id);
            break;

        case 'sub-parish':
            // Validate required fields
            if ($sub_parish_id <= 0 || empty($expense_group_name) || is_null($account_id)) {
                echo json_encode(["success" => false, "message" => "Sub Parish ID, expense group name, and account ID are required for sub parish"]);
                exit();
            }

            $table = 'sub_parish_expense_groups';
            $identifier_column = 'expense_group_identifier';

            // Check for duplicate expense group name
            $duplicate_check_query = "SELECT COUNT(*) AS count FROM $table WHERE head_parish_id = ? AND sub_parish_id = ? AND expense_group_name = ?";
            $stmt = $conn->prepare($duplicate_check_query);
            $stmt->bind_param("iis", $head_parish_id, $sub_parish_id, $expense_group_name);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            if ($result['count'] > 0) {
                echo json_encode(["success" => false, "message" => "Duplicate expense group name found for sub parish"]);
                exit();
            }
            
            // Get the highest identifier and calculate the next letter
            $query = "SELECT MAX($identifier_column) AS max_identifier FROM $table WHERE head_parish_id = ? AND sub_parish_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $head_parish_id, $sub_parish_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            // Check if there is a result and if it's not null
            $current_identifier = $result['max_identifier'] ?? null;
            
            // Set next_identifier based on the current identifier
            if ($current_identifier === null) {
                $next_identifier = 'A'; // Start from 'A' if no records exist
            } else {
                $next_identifier = chr(ord($current_identifier) + 1);
            }

            // Insert the new sub parish expense group
            $insert_query = "INSERT INTO $table (head_parish_id, sub_parish_id, expense_group_name, $identifier_column, account_id) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("iissi", $head_parish_id, $sub_parish_id, $expense_group_name, $next_identifier, $account_id);
            break;

        case 'community':
            // Validate required fields
            if ($community_id <= 0 || $sub_parish_id <= 0 || empty($expense_group_name) || is_null($account_id)) {
                echo json_encode(["success" => false, "message" => "Community ID, Sub Parish ID, expense group name, and account ID are required for community"]);
                exit();
            }

            $table = 'community_expense_groups';
            $identifier_column = 'expense_group_identifier';

            // Check for duplicate expense group name
            $duplicate_check_query = "SELECT COUNT(*) AS count FROM $table WHERE head_parish_id = ? AND sub_parish_id = ? AND community_id = ? AND expense_group_name = ?";
            $stmt = $conn->prepare($duplicate_check_query);
            $stmt->bind_param("iiis", $head_parish_id, $sub_parish_id, $community_id, $expense_group_name);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            if ($result['count'] > 0) {
                echo json_encode(["success" => false, "message" => "Duplicate expense group name found for community"]);
                exit();
            }
            
            // Get the highest identifier and calculate the next letter
            $query = "SELECT MAX($identifier_column) AS max_identifier FROM $table WHERE head_parish_id = ? AND sub_parish_id = ? AND community_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iii", $head_parish_id, $sub_parish_id, $community_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            // Check if there is a result and if it's not null
            $current_identifier = $result['max_identifier'] ?? null;
            
            // Set next_identifier based on the current identifier
            if ($current_identifier === null) {
                $next_identifier = 'A'; // Start from 'A' if no records exist
            } else {
                $next_identifier = chr(ord($current_identifier) + 1);
            }

            // Insert the new community expense group
            $insert_query = "INSERT INTO $table (head_parish_id, sub_parish_id, community_id, expense_group_name, $identifier_column, account_id) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("iiissi", $head_parish_id, $sub_parish_id, $community_id, $expense_group_name, $next_identifier, $account_id);
            break;

        case 'group':
            // Validate required fields
            if ($group_id <= 0 || empty($expense_group_name) || is_null($account_id)) {
                echo json_encode(["success" => false, "message" => "Group ID, expense group name, and account ID are required for group"]);
                exit();
            }

            $table = 'group_expense_groups';
            $identifier_column = 'expense_group_identifier';

            // Check for duplicate expense group name
            $duplicate_check_query = "SELECT COUNT(*) AS count FROM $table WHERE head_parish_id = ? AND group_id = ? AND expense_group_name = ?";
            $stmt = $conn->prepare($duplicate_check_query);
            $stmt->bind_param("iis", $head_parish_id, $group_id, $expense_group_name);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            if ($result['count'] > 0) {
                echo json_encode(["success" => false, "message" => "Duplicate expense group name found for group"]);
                exit();
            }
            
            // Get the highest identifier and calculate the next letter
            $query = "SELECT MAX($identifier_column) AS max_identifier FROM $table WHERE head_parish_id = ? AND group_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $head_parish_id, $group_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
                        
            // Check if there is a result and if it's not null
            $current_identifier = $result['max_identifier'] ?? null;
            
            // Set next_identifier based on the current identifier
            if ($current_identifier === null) {
                $next_identifier = 'A'; // Start from 'A' if no records exist
            } else {
                $next_identifier = chr(ord($current_identifier) + 1);
            }

            // Insert the new group expense group
            $insert_query = "INSERT INTO $table (head_parish_id, group_id, expense_group_name, $identifier_column, account_id) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("iissi", $head_parish_id, $group_id, $expense_group_name, $next_identifier, $account_id);
            break;
    }


    // Execute the insertion
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Expense group recorded successfully", "expense_group_identifier" => $next_identifier]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to record expense group: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

// Close the database connection
$conn->close();
?>
