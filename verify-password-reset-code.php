<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('app/components/header_files.php'); 
    render_header('Password Reset - Kanisa Langu');
  ?>
  <style>
    .response-message { font-size: 18px; }
    .info { color: #007bff; }
    .success { color: #28a745; }
    .error { color: #dc3545; }
    .icon { margin-right: 10px; }
  </style>
</head>

<body>
  <!-- Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <div class="position-relative overflow-hidden radial-gradient min-vh-100 d-flex align-items-center justify-content-center">
      <div class="d-flex align-items-center justify-content-center w-100">
        <div class="row justify-content-center w-100">
          <div class="col-md-8 col-lg-6 col-xxl-3">
            <div class="card mb-0">
              <div class="card-body">
                <a href="./index.html" class="text-nowrap logo-img text-center d-block py-3 w-100">
                  <img src="assets/images/logos/dark-logo.svg" width="180" alt="">
                </a>
                <p class="text-center"> Connect, Worship, Engage</p>
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
      // Function to verify reset code from URL
      function verifyResetCode() {
        var urlParams = new URLSearchParams(window.location.search);
        var resetCodeUrl = urlParams.get('code');
        if (!resetCodeUrl) {
          $('#responseMessage').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i>No reset code provided. Please check your email for the correct link.</div>');
          return;
        }

        $.ajax({
          type: 'POST',
          url: '/api/verify_password_reset_code.php', 
          data: {
            code: encodeURIComponent(resetCodeUrl)
          },
          dataType: 'json',
          beforeSend: function() {
            $('#responseMessage').html('<div class="response-message info"><i class="fas fa-spinner fa-spin icon"></i>Verifying your reset code...</div>');
          },
          success: function (response) {
            if (response.success) {
              if (response.verified) {
                $('#responseMessage').html('<div class="response-message success"><i class="fas fa-check-circle icon"></i>Reset code verified. Redirecting to reset password page...</div>');
                setTimeout(function () {
                  // Navigate to the reset password page with reset code in the query string
                  window.location.href = '/set-new-admin-password.php';
                }, 2000);
              } else {
                $('#responseMessage').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i>' + response.message + '</div>');
              }
            } else {
              $('#responseMessage').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i>' + response.message + '</div>');
            }
          },
          error: function (xhr, status, error) {
            $('#responseMessage').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i>An error occurred: ' + error + '</div>');
          }
        });
      }

      // Call the function to verify the reset code on page load
      verifyResetCode();
    });
  </script>
</body>

</html>
