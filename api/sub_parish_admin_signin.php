<?php
header('Content-Type: application/json');
session_start();

require_once('../config/db_connection.php');
require_once('../utils/mail_functions.php');
require_once('../utils/encryption_functions.php');
require_once('../utils/check_admin_first_login.php');
require_once('../utils/save_admin_reset_codes.php');
require_once('../utils/generate_reset_codes.php');

$admin_type = 'sub_parish_admin';

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

    // Query to join sub_parish_admins, sub_parishes, and head_parishes tables
    $sql = "
        SELECT spa.sub_parish_admin_id, spa.sub_parish_admin_fullname, spa.sub_parish_admin_email, 
               spa.sub_parish_admin_phone, spa.sub_parish_admin_password, spa.sub_parish_admin_role, 
               sp.sub_parish_id, sp.sub_parish_name, hp.head_parish_id, hp.head_parish_name
        FROM sub_parish_admins spa
        JOIN sub_parishes sp ON spa.sub_parish_id = sp.sub_parish_id
        JOIN head_parishes hp ON sp.head_parish_id = hp.head_parish_id
        WHERE spa.sub_parish_admin_email = ? OR spa.sub_parish_admin_phone = ?
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email_phone, $email_phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['sub_parish_admin_password'])) {
            // Store details in the session
            $_SESSION['sub_parish_admin_id'] = $user['sub_parish_admin_id'];
            $_SESSION['sub_parish_admin_email'] = $user['sub_parish_admin_email'];
            $_SESSION['sub_parish_admin_fullname'] = $user['sub_parish_admin_fullname'];
            $_SESSION['sub_parish_admin_phone'] = $user['sub_parish_admin_phone'];
            $_SESSION['sub_parish_admin_role'] = $user['sub_parish_admin_role'];
            $_SESSION['sub_parish_id'] = $user['sub_parish_id'];
            $_SESSION['sub_parish_name'] = $user['sub_parish_name'];
            $_SESSION['head_parish_id'] = $user['head_parish_id']; // Store head parish ID in session
            $_SESSION['head_parish_name'] = $user['head_parish_name'];

            // Check if it's the first time logging in
            $firstTimeLogin = isAdminFirstLogin($admin_type, $user['sub_parish_admin_id']);
            $response = [
                "success" => true,
                "message" => "Login successful",
                "data" => [
                    "sub_parish_admin_id" => $user['sub_parish_admin_id'],
                    "sub_parish_admin_role" => $user['sub_parish_admin_role'],
                    "sub_parish_name" => $user['sub_parish_name'],
                    "head_parish_id" => $user['head_parish_id'], // Include head parish ID in the response
                    "first_time_login" => $firstTimeLogin
                ]
            ];

            if ($firstTimeLogin) {
                // Generate and save reset code
                $resetCode = generateResetCode();
                $encryptedCode = encryptData($resetCode);
                saveAdminResetCode($user['sub_parish_admin_id'], $resetCode, $admin_type, $client_time);

                // Send reset email
                $resetLink = "https://kanisalangu.sewmrtechnologies.com/verify-password-reset-code.php?code=" . urlencode($encryptedCode);
                $emailContent = "<p>Click <a href=\"$resetLink\">here</a> to reset your password.</p>";
                $emailSent = sendEmail($user['sub_parish_admin_email'], "Password Reset Code", $emailContent);

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
