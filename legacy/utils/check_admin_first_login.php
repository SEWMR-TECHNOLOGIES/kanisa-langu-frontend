<?php
require_once('../config/db_connection.php');

/**
 * Check if the given admin is logging in for the first time and insert login record.
 *
 * @param string $adminType The role type of the admin ('kanisalangu_admin', 'diocese_admin', 'diocese_admin', 'province_admin', 'head_parish_admin').
 * @param int $adminId The ID of the admin.
 * @return bool Returns true if it is the admin's first time logging in, false otherwise.
 */
function isAdminFirstLogin($adminType, $adminId) {
    global $conn; // Assume $conn is your MySQLi connection object

    // Validate role type
    $validRoles = ['kanisalangu_admin', 'diocese_admin', 'province_admin', 'head_parish_admin','sub_parish_admin','community_admin','group_admin'];
    if (!in_array($adminType, $validRoles)) {
        return false;
    }

    // Prepare SQL query to check if it is the first login
    $sql = "SELECT first_login FROM admin_login_records WHERE admin_id = ? AND admin_type = ? ORDER BY login_time DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    $stmt->bind_param('is', $adminId, $adminType);

    if (!$stmt->execute()) {
        die('Execute failed: ' . htmlspecialchars($stmt->error));
    }

    $result = $stmt->get_result();
    $record = $result->fetch_assoc();

    // If no record is found, assume it's the first login
    if (!$record) {
        // Insert a new record
        $insertSql = "INSERT INTO admin_login_records (admin_id, admin_type, login_time) VALUES (?, ?, CURRENT_TIMESTAMP)";
        $insertStmt = $conn->prepare($insertSql);
        
        if ($insertStmt === false) {
            die('Insert prepare failed: ' . htmlspecialchars($conn->error));
        }

        $insertStmt->bind_param('is', $adminId, $adminType);

        if (!$insertStmt->execute()) {
            die('Insert execute failed: ' . htmlspecialchars($insertStmt->error));
        }

        $insertStmt->close();
        return true; // It's the first login
    }

    // If a record is found, return whether it's the first login
    return (bool)$record['first_login'];
}
?>
