<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
// Call the function on any page that requires superadmin authentication
check_session('sub_parish_admin_id', '../sub-parish/sign-in');


// Ensure session is started before accessing session variables
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/sidebar_functions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');

$target = 'sub-parish';
// Set a fallback role if session variable is not set
$role = isset($_SESSION['sub_parish_admin_role']) ? $_SESSION['sub_parish_admin_role'] : 'default';

renderSidebar($target, $role);
?>
