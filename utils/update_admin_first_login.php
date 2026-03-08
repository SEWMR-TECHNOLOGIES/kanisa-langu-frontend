<?php
require_once('../config/db_connection.php');

/**
 * Update the first_login flag to false for the given admin.
 *
 * @param string $adminType The role type of the admin ('kanisalangu_admin', 'diocese_admin', 'province_admin', 'head_parish_admin').
 * @param int $adminId The ID of the admin.
 * @return void
 */
function updateAdminFirstLogin($adminType, $adminId) {
    global $conn; // Assume $conn is your MySQLi connection object

    // Validate role type
    $validRoles = ['kanisalangu_admin', 'diocese_admin', 'province_admin', 'head_parish_admin'];
    if (!in_array($adminType, $validRoles)) {
        die('Invalid admin type');
    }

    // Prepare SQL query to update first_login flag
    $sql = "UPDATE admin_login_records SET first_login = FALSE WHERE admin_id = ? AND admin_type = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    $stmt->bind_param('is', $adminId, $adminType);

    if (!$stmt->execute()) {
        die('Execute failed: ' . htmlspecialchars($stmt->error));
    }

    $stmt->close();
}
?>
