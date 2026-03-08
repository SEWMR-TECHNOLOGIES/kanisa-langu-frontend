<?php
// Make sure session is already started
if (!isset($_SESSION)) {
    session_start();
}

// Check if the role is set
if (isset($_SESSION['head_parish_admin_role'])) {
    $role = $_SESSION['head_parish_admin_role'];

    // Load the appropriate sidebar based on role
    if (in_array($role, ['admin', 'secretary', 'accountant', 'pastor', 'evangelist'])) {
        include 'admin-sidebar.php';
    } elseif ($role === 'secretary') {
        include 'secretary-sidebar.php';
    } elseif ($role === 'accountant') {
        include 'accountant-sidebar.php';
    } elseif ($role === 'clerk') {
        include 'clerk-sidebar.php';
    } elseif ($role === 'pastor') {
        include 'pastor-sidebar.php';
    } elseif ($role === 'evangelist') {
        include 'evangelist-sidebar.php';
    } else {
        echo '<!-- Unknown role: No sidebar available -->';
    }
} else {
    echo '<!-- No role defined in session -->';
}
?>
