<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
check_session('head_parish_admin_id', '../head-parish/sign-in');

// Fetch last login information
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

$admin_id = $_SESSION['head_parish_admin_id'];
$admin_type = 'head_parish_admin';

$sql = "SELECT login_time 
        FROM admin_login_records 
        WHERE admin_id = ? AND admin_type = ? 
        ORDER BY login_time DESC 
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $admin_id, $admin_type);
$stmt->execute();
$result = $stmt->get_result();

$last_login = $result->fetch_assoc();
$last_login_time = $last_login ? $last_login['login_time'] : 'No login record found';

$stmt->close();
$conn->close();

// Format the last login time
if ($last_login_time !== 'No login record found') {
    $datetime = new DateTime($last_login_time, new DateTimeZone('Africa/Nairobi'));
    $last_login_time = $datetime->format('l, d M Y H:i');
}

// Format current server time
$server_time = new DateTime('now', new DateTimeZone('Africa/Nairobi'));
$server_time_formatted = $server_time->format('l, d M Y H:i');
?>


<!doctype html>
<html lang="en">
<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Head Parish Admin Dashboard - Kanisa Langu'); // Updated title
  ?>
</head>
<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <?php require_once('components/sidebar.php') ?>
    <div class="body-wrapper">
      <?php require_once('components/header.php') ?>
      <div class="container-fluid">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Welcome, <?php echo htmlspecialchars($_SESSION['head_parish_admin_fullname']); ?>!</h5> <!-- Updated session variable -->
            <p class="card-text">You last logged in on: <span><?php echo $last_login_time; ?></span></p>
            <p class="card-text">Current server time: <span id="serverTime"><?php echo $server_time_formatted; ?></span></p>
            
            <hr>
            
            <h6>Your Information:</h6>
            <ul class="list-group">
              <li class="list-group-item"><strong>Full Name:</strong> <?php echo htmlspecialchars($_SESSION['head_parish_admin_fullname']); ?></li> <!-- Updated session variable -->
              <li class="list-group-item"><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['head_parish_admin_email']); ?></li> <!-- Updated session variable -->
              <li class="list-group-item"><strong>Phone:</strong> <?php echo htmlspecialchars($_SESSION['head_parish_admin_phone']); ?></li> <!-- Updated session variable -->
              <li class="list-group-item"><strong>Parish:</strong> <?php echo htmlspecialchars($_SESSION['head_parish_name']); ?></li> <!-- Updated session variable -->
              <li class="list-group-item"><strong>Role:</strong> <?php echo htmlspecialchars($_SESSION['head_parish_admin_role']); ?></li> <!-- Updated session variable -->
            </ul>
          </div>
        </div>
        <?php require_once('components/sms-statistics.php'); ?>
      </div>
    </div>
  </div>
  <?php require_once('components/footer_files.php') ?>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', (event) => {
      // Display client time
      const clientTime = new Date();
      const options = {
        weekday: 'long',
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
      };
      const formattedClientTime = clientTime.toLocaleDateString('en-GB', options).replace(/,\s/, ' ').slice(0, -3);
      const serverTimeElement = document.getElementById('serverTime');
      serverTimeElement.textContent = formattedClientTime;
    });
  </script>
</body>
</html>
