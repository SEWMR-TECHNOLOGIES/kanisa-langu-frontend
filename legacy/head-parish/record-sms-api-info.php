<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');
check_session('head_parish_admin_id', '../head-parish/sign-in');
?>
<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Add SMS API Info - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Add New SMS API Info</h5>
            
            <!-- Information Row for SMS API -->
            <div class="alert alert-info" role="alert">
              <strong>Note:</strong> Please provide your <strong>API Token</strong> and <strong>Sender ID</strong>. Contact our support team for help: 
              <a href="mailto:info@kanisalangu.sewmrtechnologies.com">info@kanisalangu.sewmrtechnologies.com</a>.
            </div>

            <!-- Head Parish API Info Form -->
            <form id="headParishForm">
              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="apiTokenHP" class="form-label">API Token</label>
                    <input type="text" class="form-control" id="apiTokenHP" name="api_token" placeholder="Enter API Token" required>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="senderIdHP" class="form-label">Sender ID</label>
                    <input type="text" class="form-control" id="senderIdHP" name="sender_id" placeholder="Enter Sender ID" required>
                  </div>
                </div>
              </div>
              <div class="mb-3">
                <button type="submit" class="btn btn-primary">Record API Info</button>
              </div>
            </form>

            <div id="responseMessage"></div>
          </div>
        </div>
      </div>
    </div>
  </div>    

  <?php require_once('components/footer_files.php') ?>
  
<script>
  $(document).ready(function () {
    // Submit Head Parish Form Only
    $('#headParishForm').submit(function (e) {
      e.preventDefault();
      const formData = $(this).serialize() + '&target=head_parish';

      $.ajax({
        type: 'POST',
        url: '../api/records/record_sms_api_info',
        data: formData,
        dataType: 'json',
        success: function (response) {
          let messageHtml = '';
          if (response.success) {
            messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i>' + response.message + '</div>';
            setTimeout(function () {
              location.reload();
            }, 2000);
          } else {
            messageHtml = '<div class="response-message error"><i class="fas fa-times-circle icon"></i>' + response.message + '</div>';
          }
          $('#responseMessage').html(messageHtml);
        },
        error: function (xhr, status, error) {
          const errorMessage = xhr.responseJSON?.message || 'An error occurred.';
          $('#responseMessage').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i>' + errorMessage + '</div>');
        }
      });
    });
  });
</script>

</body>
</html>
