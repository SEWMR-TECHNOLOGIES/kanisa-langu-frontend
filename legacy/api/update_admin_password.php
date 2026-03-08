<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Function to validate password strength
function isValidPassword($password) {
    if (strlen($password) < 8) {
        return false; // Minimum length check
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return false; // At least one uppercase letter
    }
    if (!preg_match('/[a-z]/', $password)) {
        return false; // At least one lowercase letter
    }
    if (!preg_match('/[0-9]/', $password)) {
        return false; // At least one number
    }
    if (!preg_match('/[\W_]/', $password)) { // \W matches any "non-word" character
        return false; // At least one special character
    }
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // Validate input
    if (empty($newPassword) || empty($confirmPassword)) {
        echo json_encode(["success" => false, "message" => "Both new password and confirmation password are required"]);
        exit();
    }

    if ($newPassword !== $confirmPassword) {
        echo json_encode(["success" => false, "message" => "Passwords do not match"]);
        exit();
    }

    if (!isValidPassword($newPassword)) { 
        echo json_encode(["success" => false, "message" => "Password does not meet the strength requirements. It must be at least 8 characters long, contain at least one uppercase letter, one lowercase letter, one number, and one special character."]);
        exit();
    }

    $adminId = $_SESSION['reset_admin_id'] ?? 0;
    $adminType = $_SESSION['reset_admin_type'] ?? '';

    if (!$adminId || !$adminType) {
        echo json_encode(["success" => false, "message" => "Invalid session data"]);
        exit();
    }

    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

    // Update password based on admin type
    switch ($adminType) {
        case 'diocese_admin':
            $sql = "UPDATE diocese_admins SET diocese_admin_password = ? WHERE diocese_admin_id = ?";
            $redirectUrl = '/diocese/sign-in';
            break;
        case 'province_admin':
            $sql = "UPDATE province_admins SET province_admin_password = ? WHERE province_admin_id = ?";
            $redirectUrl = '/province/sign-in';
            break;
        case 'head_parish_admin':
            $sql = "UPDATE head_parish_admins SET head_parish_admin_password = ? WHERE head_parish_admin_id = ?";
            $redirectUrl = '/head-parish/sign-in';
            break;
        case 'sub_parish_admin':  // New case for sub parish admins
            $sql = "UPDATE sub_parish_admins SET sub_parish_admin_password = ? WHERE sub_parish_admin_id = ?";
            $redirectUrl = '/sub-parish/sign-in';
            break;
        case 'community_admin':  // New case for community admins
            $sql = "UPDATE community_admins SET community_admin_password = ? WHERE community_admin_id = ?";
            $redirectUrl = '/community/sign-in';
            break;
        case 'group_admin':  // New case for group admins
            $sql = "UPDATE group_admins SET group_admin_password = ? WHERE group_admin_id = ?";
            $redirectUrl = '/group/sign-in';
            break;
        default:
            echo json_encode(["success" => false, "message" => "Unknown admin type"]);
            exit();
    }

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        echo json_encode(["success" => false, "message" => "SQL preparation failed: " . $conn->error]);
        exit();
    }

    $stmt->bind_param("si", $hashedPassword, $adminId);
    if ($stmt->execute()) {
        // Mark reset code as used
        $sql = "UPDATE admin_password_reset_codes SET used = TRUE WHERE admin_id = ? AND admin_type = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            echo json_encode(["success" => false, "message" => "SQL preparation failed: " . $conn->error]);
            exit();
        }
        $stmt->bind_param("is", $adminId, $adminType);
        $stmt->execute();
        
        // Update admin login record
        $sql = "UPDATE admin_login_records SET first_login = FALSE WHERE admin_id = ? AND admin_type = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            echo json_encode(["success" => false, "message" => "SQL preparation failed: " . $conn->error]);
            exit();
        }
        $stmt->bind_param("is", $adminId, $adminType);
        $stmt->execute();
        
        // Clear session variables
        unset($_SESSION['reset_admin_id']);
        unset($_SESSION['reset_admin_type']);

        // Respond with success and redirect URL
        echo json_encode([
            "success" => true,
            "message" => "Password updated successfully",
            "redirect_url" => $redirectUrl
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update password: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
