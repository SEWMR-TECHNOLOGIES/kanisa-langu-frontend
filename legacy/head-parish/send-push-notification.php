<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
check_session('head_parish_admin_id', '../head-parish/sign-in');
?>
<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Send Notification - Kanisa Langu');
  ?>
</head>

<body>
  <!-- Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <!-- Sidebar -->
    <?php require_once('components/sidebar.php') ?>
    <!-- Main wrapper -->
    <div class="body-wrapper">
      <!-- HEADER -->
      <?php require_once('components/header.php') ?>
      <div class="container-fluid">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Send Push Notification to Kanisa Langu App</h5>
            <p class="text-muted mb-4">
              Use this form to send push notifications to users of the Kanisa Langu app. Common uses include:
              <ul class="mt-2 mb-4">
                <li>✓ Sharing church announcements</li>
                <li>✓ Sending event reminders</li>
                <li>✓ Notifying users about changes in service times</li>
                <li>✓ Sending emergency or urgent alerts</li>
                <li>✓ Welcoming new users to the app</li>
              </ul>
            </p>

            <form id="notificationForm" autocomplete="off">
              <input type="hidden" id="headParishId" name="head_parish_id" value="<?php echo htmlspecialchars($_SESSION['head_parish_id']); ?>">

              <div class="mb-3">
                <label for="target" class="form-label">FCM Token or Topic</label>
                <input type="text" class="form-control" id="target" name="target" placeholder="Enter FCM Token or Topic (default: news)">
                <small class="text-muted">Leave blank to send to default topic "news"</small>
              </div>

              <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" placeholder="Notification Title" required>
              </div>

              <div class="mb-3">
                <label for="message" class="form-label">Message</label>
                <textarea class="form-control" id="message" name="message" rows="4" placeholder="Notification Message" required></textarea>
              </div>

              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="is_topic" name="is_topic" checked>
                <label class="form-check-label" for="is_topic">
                  Send to Topic (default checked)
                </label>
              </div>

              <div class="mb-3">
                <button type="submit" class="btn btn-primary">Send Notification</button>
              </div>

              <div id="responseMessage"></div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php require_once('components/footer_files.php') ?>

  <script>
  $(document).ready(function () {
    $('#notificationForm').on('submit', function (event) {
      event.preventDefault();

      $('#responseMessage').html('<div class="response-message"><i class="fas fa-spinner fa-spin icon"></i> Sending Notification...</div>');

      var formData = {
        target: $('#target').val() || 'news', // default 'news' if empty
        title: $('#title').val(),
        message: $('#message').val(),
        is_topic: $('#is_topic').is(':checked') ? 'on' : ''
      };

      $.ajax({
        type: 'POST',
        url: '../api/records/send_push_notification.php',
        data: formData,
        dataType: 'json',
        success: function (response) {
          let messageHtml;
          if (response.success) {
            messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i> ' + response.message + '</div>';
            setTimeout(() => {
              $('#notificationForm')[0].reset();
              $('#responseMessage').html('');
            }, 3000);
          } else {
            messageHtml = '<div class="response-message error"><i class="fas fa-times-circle icon"></i> ' + (response.error || 'Failed to send notification') + '</div>';
          }
          $('#responseMessage').html(messageHtml);
        },
        error: function (xhr, status, error) {
          $('#responseMessage').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i> An error occurred: ' + error + '</div>');
        }
      });
    });
  });
  </script>

</body>

</html>
