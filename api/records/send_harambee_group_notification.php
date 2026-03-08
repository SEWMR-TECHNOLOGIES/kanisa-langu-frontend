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

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Check if JSON input is valid
if ($input === null) {
    echo json_encode(["success" => false, "message" => "Invalid JSON input"]);
    exit();
}

// Extract data from the decoded JSON
$harambee_group_id = isset($input['harambee_group_id']) ? $conn->real_escape_string($input['harambee_group_id']) : null;
$harambee_id = isset($input['harambee_id']) ? $conn->real_escape_string($input['harambee_id']) : null; // Extract harambeeId
$target = isset($input['target']) ? $conn->real_escape_string($input['target']) : null;
$from_date = isset($input['start_date']) ? $conn->real_escape_string($input['start_date']) : null;
$to_date = isset($input['end_date']) ? $conn->real_escape_string($input['end_date']) : null;

// Validate harambee_group_id
if (empty($harambee_group_id)) {
    echo json_encode(["success" => false, "message" => "Harambee group ID is required."]);
    exit();
}

// Validate harambee_id
if (empty($harambee_id)) {
    echo json_encode(["success" => false, "message" => "Harambee ID is required."]);
    exit();
}

// Validate target
if (empty($target)) {
    echo json_encode(["success" => false, "message" => "Target is required."]);
    exit();
}

// Validate from_date
if (empty($from_date)) {
    echo json_encode(["success" => false, "message" => "From date is required."]);
    exit();
}

// Validate to_date only if it's provided
if (!empty($to_date)) {
    $to_date_timestamp = strtotime($to_date);
    if ($to_date_timestamp === false) {
        echo json_encode(["success" => false, "message" => "To date is invalid."]);
        exit();
    }
} else {
    $to_date = date("Y-m-d");
}

// Check if from_date is a valid date
$from_date_timestamp = strtotime($from_date);
if ($from_date_timestamp === false) {
    echo json_encode(["success" => false, "message" => "From date is invalid."]);
    exit();
}

// Determine which contribution table to use
$contribution_table = '';
switch ($target) {
    case 'head-parish':
        $contribution_table = 'head_parish_harambee_contribution';
        break;
    case 'sub-parish':
        $contribution_table = 'sub_parish_harambee_contribution';
        break;
    case 'community':
        $contribution_table = 'community_harambee_contribution';
        break;
    case 'groups':
        $contribution_table = 'groups_harambee_contribution';
        break;
    default:
        echo json_encode(["success" => false, "message" => "Invalid target specified."]);
        exit();
}

// Fetch the member IDs for the specified group and date range
$harambeeGroupMemberIds = getHarambeeGroupMemberIds($conn, $target, $harambee_group_id);

if ($harambeeGroupMemberIds) {
    error_log("Fetched Member IDs for Harambee Group ID $harambee_group_id: " . implode(', ', $harambeeGroupMemberIds));
    $totalHarambeeGroupContributions = geTotalHarambeeGroupContribution($conn, $harambeeGroupMemberIds, $harambee_id, $target, $from_date);

    // Process each member ID
    foreach ($harambeeGroupMemberIds as $member_id) {
        // Query the contribution details for the member within the date range and sum amounts for each date
        $query = "
            SELECT contribution_date, SUM(amount) AS amount 
            FROM $contribution_table 
            WHERE member_id = $member_id AND harambee_id = $harambee_id 
              AND contribution_date BETWEEN '$from_date' AND '$to_date'
            GROUP BY contribution_date
        ";
        
        // Array to store member contributions (amount and contribution_date)
        $memberContributions = [];

        $result = $conn->query($query);

        if ($result && $result->num_rows > 0) {
            // Store the contribution details for the member (amount and contribution_date)
            while ($row = $result->fetch_assoc()) {
                if (isset($row['amount']) && isset($row['contribution_date'])) {
                    // Store the amount and contribution date in the array
                    $memberContributions[] = [
                        'amount' => $row['amount'],
                        'contribution_date' => $row['contribution_date'],
                    ];

                    // Log the contribution data for debugging
                    error_log("Stored Contribution - Amount: {$row['amount']}, Contribution Date: {$row['contribution_date']}");
                }
            }
        }
        // else {
        //     // error_log("No contributions found for Member ID: $member_id in the specified date range.");
        // }
        
        // Retrieve group details
        $harambeeGroupDetails = getHarambeeGroupDetails($conn, $harambee_id, $member_id, $target);
        $group_name = $harambeeGroupDetails['harambee_group_name'];
        $group_target = $harambeeGroupDetails['harambee_group_target'];
        $harambee_description = $harambeeGroupDetails['harambee_description'];
        
        // Now process all the contributions stored in the array
        foreach ($memberContributions as $contribution) {
            $amount = $contribution['amount'];
            $contribution_date = $contribution['contribution_date'];
        
            // Get contributing member details
            $contributing_member_result = getMemberDetails($conn, $member_id);
            if ($contributing_member_result) {
                $contributing_member = $contributing_member_result->fetch_assoc();

                // Log successful data retrieval for better tracing
                // error_log("Data retrieved - Member ID: $member_id, Amount: $amount, Contribution Date: $contribution_date");

                // Log the number of members to notify
                // error_log("Notification will be sent to " . count($harambeeGroupMemberIds) . " members");

                // Now process each harambee group member
                foreach ($harambeeGroupMemberIds as $harambee_group_member_id) {
                    // Fetch the harambee group member details
                    $harambee_group_member_result = getMemberDetails($conn, $harambee_group_member_id);
                    if ($harambee_group_member_result) {
                        $harambee_group_member = $harambee_group_member_result->fetch_assoc();


                        // Example: Pass this data to the notification function
                        notifyHarambeeGroupMembersBySMS(
                            $conn,
                            $group_target,
                            $totalHarambeeGroupContributions,
                            $group_name,
                            $harambee_description,
                            $harambee_group_member,
                            $contributing_member,
                            $amount,
                            $contribution_date,
                            $target,
                            $harambee_id
                        );
                    }
                }
            } else {
                error_log("Failed to fetch details for contributing member ID: $member_id");
            }
        }
    }

    echo json_encode(["success" => true, "message" => "Members notified successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "No members found for the specified group and date range."]);
}

$conn->close();
?>
