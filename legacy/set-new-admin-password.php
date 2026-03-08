<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
check_session('reset_admin_id', '/');
 ?>
<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('app/components/header_files.php'); 
    render_header('Set New Password - Kanisa Langu');
  ?>
</head>

<body>
  <!-- Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <div
      class="position-relative overflow-hidden radial-gradient min-vh-100 d-flex align-items-center justify-content-center">
      <div class="d-flex align-items-center justify-content-center w-100">
        <div class="row justify-content-center w-100">
          <div class="col-md-8 col-lg-6 col-xxl-3">
            <div class="card mb-0">
              <div class="card-body">
                <a href="/" class="text-nowrap logo-img text-center d-block py-3 w-100">
                  <img src="assets/images/logos/dark-logo.svg" width="180" alt="">
                </a>
                <p class="text-center"> Connect, Worship, Engage</p>
                <form id="setPasswordForm" autocomplete="off">
                  <div class="mb-4">
                    <label for="newPassword" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="newPassword" placeholder="Enter new password">
                  </div>
                  <div class="mb-4">
                    <label for="confirmPassword" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirmPassword" placeholder="Confirm new password">
                  </div>
                  <button type="submit" class="btn btn-primary w-100 py-8 fs-4 mb-4 rounded-2">Set Password</button>
                </form>
                <div id="responseMessage" class="text-center mt-3"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php require_once('app/components/footer_files.php') ?>
  
  <script>
    $(document).ready(function () {
      $('#setPasswordForm').on('submit', function (event) {
        event.preventDefault(); // Prevent the default form submission

        var newPassword = $('#newPassword').val();
        var confirmPassword = $('#confirmPassword').val();

        // Validate password fields
        if (newPassword !== confirmPassword) {
          $('#responseMessage').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i>Passwords do not match</div>');
          return;
        }

        $.ajax({
          type: 'POST',
          url: '/api/update_admin_password.php',
          data: {
            new_password: newPassword,
            confirm_password: confirmPassword
          },
          dataType: 'json',
          success: function (response) {
            var messageHtml;
            if (response.success) {
              messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i>' + response.message + '</div>';
              setTimeout(function () {
                window.location.href = response.redirect_url; 
              }, 2000);
            } else {
              messageHtml = '<div class="response-message error"><i class="fas fa-times-circle icon"></i>' + response.message + '</div>';
            }
            $('#responseMessage').html(messageHtml);
          },
          error: function (xhr, status, error) {
            $('#responseMessage').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i>An error occurred: ' + error + '</div>');
          }
        });
      });
    });
  </script>
</body>

</html>
