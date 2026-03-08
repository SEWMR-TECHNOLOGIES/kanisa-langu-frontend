<?php
header('Content-Type: application/json');
session_start();

require_once('../config/db_connection.php');
require_once('../utils/mail_functions.php');
require_once('../utils/encryption_functions.php');
require_once('../utils/check_admin_first_login.php');
require_once('../utils/save_admin_reset_codes.php');
require_once('../utils/generate_reset_codes.php');

$admin_type = 'province_admin';

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_phone = isset($_POST['email_phone']) ? $conn->real_escape_string(trim($_POST['email_phone'])) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $client_time = isset($_POST['client_time']) ? $_POST['client_time'] : '';
    $recaptcha_response = isset($_POST['recaptcha_response']) ? $_POST['recaptcha_response'] : ''; // Updated to match JavaScript

    if (empty($email_phone) || empty($password) || empty($recaptcha_response)) {
        echo json_encode(["success" => false, "message" => "Email/Phone, password, and reCAPTCHA are required"]);
        exit();
    }
    
    // Verify reCAPTCHA
    $secret_key = '6LdvI0kqAAAAACHJD8__-_3k4R32lZowmTYyQz3N'; // Replace with your actual secret key
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

    // Query to join the province_admins and provinces tables
    $sql = "
        SELECT pa.province_admin_id, pa.province_admin_fullname, pa.province_admin_email, pa.province_admin_phone, 
               pa.province_admin_password, pa.province_admin_role, p.province_id, p.province_name
        FROM province_admins pa
        JOIN provinces p ON pa.province_id = p.province_id
        WHERE pa.province_admin_email = ? OR pa.province_admin_phone = ?
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email_phone, $email_phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['province_admin_password'])) {
            // Store details in the session
            $_SESSION['province_admin_id'] = $user['province_admin_id'];
            $_SESSION['province_admin_email'] = $user['province_admin_email'];
            $_SESSION['province_admin_fullname'] = $user['province_admin_fullname'];
            $_SESSION['province_admin_phone'] = $user['province_admin_phone'];
            $_SESSION['province_admin_role'] = $user['province_admin_role'];
            $_SESSION['province_id'] = $user['province_id'];
            $_SESSION['province_name'] = $user['province_name'];

            // Check if it's the first time logging in
            $firstTimeLogin = isAdminFirstLogin($admin_type, $user['province_admin_id']);
            $response = [
                "success" => true,
                "message" => "Login successful",
                "data" => [
                    "province_admin_id" => $user['province_admin_id'],
                    "province_admin_role" => $user['province_admin_role'],
                    "province_name" => $user['province_name'],
                    "first_time_login" => $firstTimeLogin
                ]
            ];

            if ($firstTimeLogin) {
                // Generate and save reset code
                $resetCode = generateResetCode();
                $encryptedCode = encryptData($resetCode);
                saveAdminResetCode($user['province_admin_id'], $resetCode, $admin_type, $client_time);

                // Send reset email
                $resetLink = "https://kanisalangu.sewmrtechnologies.com/verify-password-reset-code.php?code=" . urlencode($encryptedCode);
                $emailContent = "<p>Click <a href=\"$resetLink\">here</a> to reset your password.</p>";
                $emailSent = sendEmail($user['province_admin_email'], "Password Reset Code", $emailContent);

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
