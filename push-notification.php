<?php
require_once 'utils/FCM.php'; 

$result = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target = $_POST['target'];
    $title = $_POST['title'];
    $message = $_POST['message'];
    $isTopic = isset($_POST['is_topic']);

    if ($isTopic) {
        $response = sendPushNotificationToTopic($target, $title, $message);
    } else {
        $response = sendPushNotificationToFCMToken($target, $title, $message);
    }

    if ($response['success']) {
        $result = '✅ Notification sent successfully!';
    } else {
        $result = '❌ Failed to send notification: ' . htmlspecialchars($response['error']);
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send FCM Notification</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .fade-in { animation: fadeIn 0.5s ease-out forwards; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center px-4">
    <div class="max-w-md w-full bg-white shadow-xl rounded-2xl p-8 fade-in border-t-4 border-blue-500">
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Send Firebase Notification</h2>
        <p class="text-gray-500 mb-6 text-sm">Send to a specific FCM token or a topic.</p>

        <?php if (!empty($result)) : ?>
            <div class="mb-4 p-3 rounded-lg <?php echo strpos($result, '✅') === 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                <?= htmlspecialchars($result) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-4">
            <div>
                <label for="target" class="block text-sm font-medium text-gray-700">FCM Token or Topic</label>
                <input type="text" name="target" id="target" required class="w-full mt-1 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                <input type="text" name="title" id="title" required class="w-full mt-1 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                <textarea name="message" id="message" rows="4" required class="w-full mt-1 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
            </div>
            <div class="flex items-center">
                <input type="checkbox" name="is_topic" id="is_topic" class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                <label for="is_topic" class="ml-2 block text-sm text-gray-700">Send to Topic?</label>
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg shadow transition transform hover:scale-105">
                Send Notification
            </button>
        </form>

        <p class="text-xs text-gray-400 text-center mt-6">&copy; <?= date('Y'); ?> SEWMR Technologies</p>
    </div>
</body>
</html>
