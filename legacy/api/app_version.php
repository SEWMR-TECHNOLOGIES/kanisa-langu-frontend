<?php
header('Content-Type: application/json');

// Include the database connection file
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

// Get platform parameter from query string, default to 'android'
$valid_platforms = ['android', 'ios'];
$platform = isset($_GET['platform']) && in_array(strtolower($_GET['platform']), $valid_platforms) 
            ? strtolower($_GET['platform']) 
            : 'android';

// Prepare statement with platform filter
$sql = "SELECT version_name, version_code FROM app_versions WHERE platform = ? ORDER BY id DESC LIMIT 1";

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "s", $platform);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($result) {
        $version = mysqli_fetch_assoc($result);
        if ($version) {
            echo json_encode($version);
        } else {
            echo json_encode(['error' => 'No version information found for platform: ' . $platform]);
        }
        mysqli_free_result($result);
    } else {
        echo json_encode(['error' => 'Query failed: ' . mysqli_error($conn)]);
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['error' => 'Failed to prepare statement: ' . mysqli_error($conn)]);
}

mysqli_close($conn);

?>