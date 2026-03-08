<?php
header('Content-Type: application/json');
session_start();

require_once('../config/db_connection.php');
require_once('../utils/mail_functions.php');
require_once('../utils/encryption_functions.php');
require_once('../utils/check_admin_first_login.php');
require_once('../utils/save_admin_reset_codes.php');
require_once('../utils/generate_reset_codes.php');

$admin_type = 'community_admin';

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_phone = isset($_POST['email_phone']) ? $conn->real_escape_string(trim($_POST['email_phone'])) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $client_time = isset($_POST['client_time']) ? $_POST['client_time'] : '';
    $recaptcha_response = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';

    if (empty($email_phone) || empty($password) || empty($recaptcha_response)) {
        echo json_encode(["success" => false, "message" => "Email/Phone, password, and reCAPTCHA are required"]);
        exit();
    }

    // Verify reCAPTCHA
    $secret_key = '6LdvI0kqAAAAACHJD8__-_3k4R32lZowmTYyQz3N'; 
    $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptcha_response = file_get_contents($recaptcha_url . '?secret=' . $secret_key . '&response=' . $recaptcha_response);
    $recaptcha_data = json_decode($recaptcha_response);

    if (!$recaptcha_data->success) {
        echo json_encode(["success" => false, "message" => "reCAPTCHA verification failed"]);
        exit();
    }

    // Validate email or phone format
    if (filter_var($email_phone, FILTER_VALIDATE_EMAIL) === false && !preg_match('/^\+?[0-9]*$/', $email_phone)) {
        echo json_encode(["success" => false, "message" => "Invalid Email/Phone format"]);
        exit();
    }

    // Query to join community_admins, communities, sub_parishes, and head_parishes tables
    $sql = "
        SELECT ca.community_admin_id, ca.community_admin_fullname, ca.community_admin_email, 
               ca.community_admin_phone, ca.community_admin_password, ca.community_admin_role, 
               c.community_id, c.community_name, sp.sub_parish_id, sp.sub_parish_name, 
               hp.head_parish_id, hp.head_parish_name
        FROM community_admins ca
        JOIN communities c ON ca.community_id = c.community_id
        JOIN sub_parishes sp ON ca.sub_parish_id = sp.sub_parish_id
        JOIN head_parishes hp ON sp.head_parish_id = hp.head_parish_id
        WHERE ca.community_admin_email = ? OR ca.community_admin_phone = ?
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email_phone, $email_phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['community_admin_password'])) {
            // Store details in the session
            $_SESSION['community_admin_id'] = $user['community_admin_id'];
            $_SESSION['community_admin_email'] = $user['community_admin_email'];
            $_SESSION['community_admin_fullname'] = $user['community_admin_fullname'];
            $_SESSION['community_admin_phone'] = $user['community_admin_phone'];
            $_SESSION['community_admin_role'] = $user['community_admin_role'];
            $_SESSION['community_id'] = $user['community_id'];
            $_SESSION['community_name'] = $user['community_name'];
            $_SESSION['sub_parish_id'] = $user['sub_parish_id'];
            $_SESSION['sub_parish_name'] = $user['sub_parish_name']; 
            $_SESSION['head_parish_id'] = $user['head_parish_id'];
            $_SESSION['head_parish_name'] = $user['head_parish_name']; 

            // Check if it's the first time logging in
            $firstTimeLogin = isAdminFirstLogin($admin_type, $user['community_admin_id']);
            $response = [
                "success" => true,
                "message" => "Login successful",
                "data" => [
                    "community_admin_id" => $user['community_admin_id'],
                    "community_admin_role" => $user['community_admin_role'],
                    "community_name" => $user['community_name'],
                    "sub_parish_name" => $user['sub_parish_name'], // Include sub parish name in response
                    "head_parish_name" => $user['head_parish_name'], // Include head parish name in response
                    "first_time_login" => $firstTimeLogin,
                    "head_parish_id" => $user['head_parish_id'] // Include head parish ID
                ]
            ];

            if ($firstTimeLogin) {
                // Generate and save reset code
                $resetCode = generateResetCode();
                $encryptedCode = encryptData($resetCode);
                saveAdminResetCode($user['community_admin_id'], $resetCode, $admin_type, $client_time);

                // Send reset email
                $resetLink = "https://kanisalangu.sewmrtechnologies.com/verify-password-reset-code.php?code=" . urlencode($encryptedCode);
                $emailContent = "<p>Click <a href=\"$resetLink\">here</a> to reset your password.</p>";
                $emailSent = sendEmail($user['community_admin_email'], "Password Reset Code", $emailContent);

                if (!$emailSent) {
                    $response["success"] = false;
                    $response["message"] = "Failed to send reset email.";
                }
            }

            echo json_encode($response);
        } else {
            echo json_encode(["success" => false, "message" => "Invalid Email/Phone or password"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Invalid Email/Phone or password"]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
