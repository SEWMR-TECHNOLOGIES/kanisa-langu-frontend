<?php
header('Content-Type: application/json');
session_start();

require_once('../config/db_connection.php');

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? $conn->real_escape_string(trim($_POST['username'])) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $client_time = isset($_POST['client_time']) ? $_POST['client_time'] : '';
    $recaptcha_response = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';

    if (empty($username) || empty($password) || empty($recaptcha_response)) {
        echo json_encode(["success" => false, "message" => "Username, password, and reCAPTCHA are required"]);
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

    $sql = "SELECT kanisalangu_admin_id, kanisalangu_admin_username, kanisalangu_admin_password, kanisalangu_admin_role FROM kanisalangu_admins WHERE kanisalangu_admin_username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['kanisalangu_admin_password'])) {
            $_SESSION['kanisalangu_admin_id'] = $user['kanisalangu_admin_id'];
            $_SESSION['kanisalangu_admin_username'] = $user['kanisalangu_admin_username'];

            // Log the login event
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            $log_sql = "INSERT INTO kanisalangu_admin_logins (kanisalangu_admin_id, login_time, ip_address, user_agent) VALUES (?, ?, ?, ?)";
            $log_stmt = $conn->prepare($log_sql);
            $log_stmt->bind_param("isss", $user['kanisalangu_admin_id'], $client_time, $ip_address, $user_agent);
            $log_stmt->execute();

            echo json_encode([
                "success" => true,
                "message" => "Login successful",
                "data" => [
                    "kanisalangu_admin_id" => $user['kanisalangu_admin_id'],
                    "kanisalangu_admin_role" => $user['kanisalangu_admin_role']
                ]
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "Invalid username or password"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Invalid username or password"]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
