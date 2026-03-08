<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Community Admin Login - Kanisa Langu');
  ?>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
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
                  <img src="/assets/images/logos/dark-logo.svg" width="180" alt="Logo">
                </a>
                <p class="text-center"> Connect, Worship, Engage</p>
                <form id="loginForm" autocomplete="off">
                  <div class="mb-3">
                    <label for="username" class="form-label">Email / Phone</label>
                    <input type="text" class="form-control" id="username" placeholder="Enter your Email / Phone">
                  </div>
                  <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" placeholder="Enter your password">
                  </div>
                  <div class="mb-4">
                    <div class="g-recaptcha" data-sitekey="6LdvI0kqAAAAAAu_ZbZ_Cp3P7fOFLbKrSjUoNGTh"></div>
                  </div>
                  <div class="d-flex align-items-center justify-content-between mb-4">
                    <a class="text-primary fw-bold" href="/">Forgot Password?</a>
                  </div>
                  <button type="submit" class="btn btn-primary w-100 py-8 fs-4 mb-4 rounded-2">Sign In</button>
                </form>
                <div id="responseMessage" class="text-center mt-3"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php require_once('components/footer_files.php') ?>

<script>
  $(document).ready(function () {
    $('#loginForm').on('submit', function (event) {
      event.preventDefault(); // Prevent the default form submission

      var username = $('#username').val();
      var password = $('#password').val();
      var recaptchaResponse = grecaptcha.getResponse(); 

      if (recaptchaResponse.length === 0) {
        $('#responseMessage').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i>Please complete the reCAPTCHA.</div>');
        return;
      }

      // Get the current local time and format it as YYYY-MM-DD HH:MM:SS
      var now = new Date();
      var year = now.getFullYear();
      var month = ('0' + (now.getMonth() + 1)).slice(-2); 
      var day = ('0' + now.getDate()).slice(-2);
      var hours = ('0' + now.getHours()).slice(-2);
      var minutes = ('0' + now.getMinutes()).slice(-2);
      var seconds = ('0' + now.getSeconds()).slice(-2);

      var localTime = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;

      $.ajax({
        type: 'POST',
        url: '/api/community_admin_signin.php',
        data: {
          email_phone: username,
          password: password,
          client_time: localTime,
          'g-recaptcha-response': recaptchaResponse 
        },
        dataType: 'json',
        success: function (response) {
          var messageHtml;
          if (response.success) {
            if (response.data.first_time_login) {
              messageHtml = '<div class="response-message info"><i class="fas fa-info-circle icon"></i>' + 
                            'It is your first time logging in. Please check your email for a password reset link to set up a new password.</div>';
            } else {
              messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i>' + 
                            response.message + '</div>';
              setTimeout(function () {
                window.location.href = './'; 
              }, 2000);
            }
          } else {
            messageHtml = '<div class="response-message error"><i class="fas fa-times-circle icon"></i>' + response.message + '</div>';
            grecaptcha.reset(); 
          }
          $('#responseMessage').html(messageHtml);
        },
        error: function (xhr, status, error) {
          $('#responseMessage').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i>An error occurred: ' + error + '</div>');
          grecaptcha.reset(); 
        }
      });
    });
  });
</script>
</body>

</html>
