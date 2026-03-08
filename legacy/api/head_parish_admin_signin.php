<?php
header('Content-Type: application/json');
ini_set('session.cookie_domain', '.kanisalangu.sewmrtechnologies.com');
ini_set('session.cookie_secure', '1'); 
ini_set('session.cookie_httponly', '1');
session_start();


require_once('../config/db_connection.php');
require_once('../utils/mail_functions.php');
require_once('../utils/encryption_functions.php');
require_once('../utils/check_admin_first_login.php');
require_once('../utils/save_admin_reset_codes.php');
require_once('../utils/generate_reset_codes.php');

$admin_type = 'head_parish_admin';

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

    // Query to join head_parish_admins and head_parishes tables
    $sql = "
        SELECT hpa.head_parish_admin_id, hpa.head_parish_admin_fullname, hpa.head_parish_admin_email, 
               hpa.head_parish_admin_phone, hpa.head_parish_admin_password, hpa.head_parish_admin_role, 
               hp.head_parish_id, hp.head_parish_name
        FROM head_parish_admins hpa
        JOIN head_parishes hp ON hpa.head_parish_id = hp.head_parish_id
        WHERE hpa.head_parish_admin_email = ? OR hpa.head_parish_admin_phone = ?
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email_phone, $email_phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['head_parish_admin_password'])) {
            // Store details in the session
            $_SESSION['head_parish_admin_id'] = $user['head_parish_admin_id'];
            $_SESSION['head_parish_admin_email'] = $user['head_parish_admin_email'];
            $_SESSION['head_parish_admin_fullname'] = $user['head_parish_admin_fullname'];
            $_SESSION['head_parish_admin_phone'] = $user['head_parish_admin_phone'];
            $_SESSION['head_parish_admin_role'] = $user['head_parish_admin_role'];
            $_SESSION['head_parish_id'] = $user['head_parish_id'];
            $_SESSION['head_parish_name'] = $user['head_parish_name'];

            // Check if it's the first time logging in
            $firstTimeLogin = isAdminFirstLogin($admin_type, $user['head_parish_admin_id']);
            $response = [
                "success" => true,
                "message" => "Login successful",
                "data" => [
                    "head_parish_admin_id" => $user['head_parish_admin_id'],
                    "head_parish_admin_role" => $user['head_parish_admin_role'],
                    "head_parish_name" => $user['head_parish_name'],
                    "first_time_login" => $firstTimeLogin
                ]
            ];

            if ($firstTimeLogin) {
                // Generate and save reset code
                $resetCode = generateResetCode();
                $encryptedCode = encryptData($resetCode);
                saveAdminResetCode($user['head_parish_admin_id'], $resetCode, $admin_type, $client_time);

                // Send reset email
                $resetLink = "https://kanisalangu.sewmrtechnologies.com/verify-password-reset-code.php?code=" . urlencode($encryptedCode);
                $emailContent = "<p>Click <a href=\"$resetLink\">here</a> to reset your password.</p>";
                $emailSent = sendEmail($user['head_parish_admin_email'], "Password Reset Code", $emailContent);

                if (!$emailSent) {
                    $response["success"] = false;
                    $response["message"] = "Failed to send reset email.";
                }
            } else {
                // Only log login time if not first login
                date_default_timezone_set('Africa/Nairobi');
                $now = date('Y-m-d H:i:s');
            
                $insertLogin = $conn->prepare("
                    INSERT INTO admin_login_records (admin_id, admin_type, login_time, first_login)
                    VALUES (?, ?, ?, FALSE)
                ");
                $insertLogin->bind_param("iss", $user['head_parish_admin_id'], $admin_type, $now);
                $insertLogin->execute();
                $insertLogin->close();
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
