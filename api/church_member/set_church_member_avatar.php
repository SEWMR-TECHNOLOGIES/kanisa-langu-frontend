<?php
header('Content-Type: application/json');
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');

// Check database connection
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Ensure the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate the member_id
    $member_id = isset($_POST['member_id']) ? intval($_POST['member_id']) : 0;
    if ($member_id <= 0) {
        echo json_encode(["success" => false, "message" => "Invalid member ID."]);
        exit();
    }

    // Validate the uploaded file
    if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] != UPLOAD_ERR_OK) {
        echo json_encode(["success" => false, "message" => "No avatar uploaded or upload error occurred."]);
        exit();
    }

    $avatar = $_FILES['avatar'];
    
    // Extract the file extension using pathinfo()
    $extension = pathinfo($avatar['name'], PATHINFO_EXTENSION);

    // Accept all image types by checking the file's MIME type
    $allowed_types = [
        'jpeg', 'png', 'gif', 'bmp', 'webp', 'tiff', 'jpg'
    ];
    
    $max_size = 1024000 * 5; // 1 MB in bytes

    // Check file type
    if (!in_array($extension, $allowed_types)) {
        echo json_encode(["success" => false, "message" => "Invalid file type. Only images are allowed."]);
        exit();
    }

    // Check file size
    if ($avatar['size'] > $max_size) {
        echo json_encode(["success" => false, "message" => "File size exceeds the 5 MB limit."]);
        exit();
    }

    // Define upload directory and new file name
    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/avatars/';
    $new_filename = uniqid("avatar_") . '.' . $extension;

    // Ensure upload directory exists
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Move the uploaded file
    if (!move_uploaded_file($avatar['tmp_name'], $upload_dir . $new_filename)) {
        echo json_encode(["success" => false, "message" => "Failed to save uploaded avatar."]);
        exit();
    }

    // Check if the user already has an avatar
    $stmt = $conn->prepare("SELECT avatar_url FROM church_members_accounts WHERE member_id = ?");
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing_avatar = $result->fetch_assoc()['avatar_url'];
    $stmt->close();

    // Delete the existing avatar file if it exists
    if ($existing_avatar) {
        $existing_avatar_path = $upload_dir . $existing_avatar;
        if (file_exists($existing_avatar_path)) {
            unlink($existing_avatar_path);
        }
    }

    // Update the avatar URL in the database
    $stmt = $conn->prepare("UPDATE church_members_accounts SET avatar_url = ? WHERE member_id = ?");
    $stmt->bind_param("si", $new_filename, $member_id);

    if ($stmt->execute()) {
        // Fetch the updated user details
        $user_details = getMemberDetails($conn, $member_id)->fetch_assoc();

        // Append the full avatar URL to the response
        $user_details['avatar_url'] = $user_details['avatar_url']
            ? "https://kanisalangu.sewmrtechnologies.com/uploads/avatars/" . $new_filename
            : null;

        echo json_encode(["success" => true, "message" => "Avatar updated successfully.", "user_details" => $user_details]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update avatar in the database."]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}

$conn->close();
?>
