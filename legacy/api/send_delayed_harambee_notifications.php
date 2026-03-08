<?php
// Adjust the paths to the correct locations
require_once(__DIR__ . '/../config/db_connection.php');  // Goes up one level to access 'config' folder
require_once(__DIR__ . '/../utils/helpers.php');  // Goes up one level to access 'utils' folder

// Check the database connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    exit();
}

// Fetch records that were created in the last 10 minutes and have not been processed
$sql = "SELECT * FROM delayed_harambee_notifications 
        WHERE created_at >= NOW() - INTERVAL 10 MINUTE AND is_processed = 0";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $harambee_id = $row['harambee_id'];
        $member_id = $row['member_id'];
        $target = $row['target'];
        $contribution_date = $row['contribution_date'];
        $amount = $row['amount'];
        $contributing_member_name = $row['contributing_member_name']; 
        $mrAndMrsName = $row['mr_and_mrs_name'];
        $isMrAndMrs = $row['is_mr_and_mrs'];

        // Get member details
        $member = getMemberDetails($conn, $member_id)->fetch_assoc(); 

        // Retrieve group details (if any) for this contribution
        $harambeeGroupDetails = getHarambeeGroupDetails($conn, $harambee_id, $member_id, $target);

        if ($harambeeGroupDetails) {
            $harambee_group_id = $harambeeGroupDetails['harambee_group_id'];
            $group_name = $harambeeGroupDetails['harambee_group_name'];
            $group_target = $harambeeGroupDetails['harambee_group_target'];
            $harambee_description = $harambeeGroupDetails['harambee_description'];
            $date_created = $harambeeGroupDetails['date_created'];
            $contribution_start_date = $date_created;

            // Retrieve the member IDs for the group
            $harambeeGroupMemberIds = getHarambeeGroupMemberIds($conn, $target, $harambee_group_id);

            if ($harambeeGroupMemberIds) {
                // Calculate the total contributions for the group
                $totalHarambeeGroupContributions = geTotalHarambeeGroupContribution($conn, $harambeeGroupMemberIds, $harambee_id, $target, $contribution_start_date);

                $allNotificationsSent = true;  // Flag to check if all notifications are sent

                // Notify each member by SMS, including their contribution amount
                foreach ($harambeeGroupMemberIds as $harambee_group_member_id) {
                    // Fetch member details based on the member ID
                    $result = getMemberDetails($conn, $harambee_group_member_id);

                    if ($result && $harambee_group_member = $result->fetch_assoc()) {
                        // Call the SMS notification function
                        $smsSent = notifyHarambeeGroupMembersBySMS(
                            $conn,
                            $group_target,
                            $totalHarambeeGroupContributions,
                            $group_name,
                            $harambee_description,
                            $harambee_group_member,  
                            $member,  
                            $amount,
                            $contribution_date,
                            $target,
                            $harambee_id,
                            $isMrAndMrs,
                            $mrAndMrsName
                        );
                    }
                }

                // If all notifications were sent successfully, update is_processed to 1
                if ($allNotificationsSent) {
                    // Mark the record as processed after sending SMS to all members
                    $updateStmt = $conn->prepare("UPDATE delayed_harambee_notifications SET is_processed = 1 WHERE id = ?");
                    $updateStmt->bind_param("i", $row['id']);
                    $updateStmt->execute();
                    $updateStmt->close();
                }
            }
        }
    }
}

$conn->close();
?>
