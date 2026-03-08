<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
check_session('kanisalangu_admin_id', '../app/sign-in');

// Fetch last login information
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

$admin_id = $_SESSION['kanisalangu_admin_id'];
$sql = "SELECT login_time FROM kanisalangu_admin_logins 
        WHERE kanisalangu_admin_id = ? 
        ORDER BY login_time DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$last_login = $result->fetch_assoc();
$last_login_time = $last_login ? $last_login['login_time'] : 'No login record found';

$stmt->close();
$conn->close();

// Format the last login time
if ($last_login_time !== 'No login record found') {
    $datetime = new DateTime($last_login_time);
    $last_login_time = $datetime->format('l, d M Y H:i');
}

// Format current server time
$server_time = new DateTime();
$server_time_formatted = $server_time->format('l, d M Y H:i');
?>

<!doctype html>
<html lang="en">
<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Admin Dashboard - Kanisa Langu');
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
            <h5 class="card-title">Welcome, <?php echo htmlspecialchars($_SESSION['kanisalangu_admin_username']); ?>!</h5>
            <p class="card-text">You last logged in on <span><?php echo $last_login_time; ?></span></p>
            <p class="card-text">Current server time: <span id="serverTime"><?php echo $server_time_formatted; ?></span></p>
          </div>
        </div>
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
