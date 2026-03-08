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
    render_header('Church Member Exclusion Reasons - Kanisa Langu');
  ?>
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6"
    data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">

    <?php require_once('components/sidebar.php') ?>

    <div class="body-wrapper">
      <?php require_once('components/header.php') ?>
      <div class="container-fluid">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title fw-semibold mb-3">Church Member Exclusion Reasons</h5>

            <div class="alert alert-info mb-4">
              These reasons will exclude a church member from church operations such as Harambee, envelopes, and other daily church activities.
            </div>

            <form id="exclusionReasonForm">
              <div class="mb-3">
                <label for="reason" class="form-label">Exclusion Reason</label>
                <input type="text" class="form-control" id="reason" name="reason" placeholder="Enter exclusion reason">
              </div>

              <div class="mb-3">
                <button type="submit" class="btn btn-primary">Record Reason</button>
              </div>
            </form>

            <div id="responseMessage" class="mt-3"></div>

          </div>
        </div>
      </div>
    </div>
  </div>

  <?php require_once('components/footer_files.php') ?>

  <script>
    $(document).ready(function () {
      $('#exclusionReasonForm').on('submit', function (e) {
        e.preventDefault();
        const formData = $(this).serialize();
        $('#responseMessage').html('<div class="response-message"><i class="fas fa-spinner fa-spin icon"></i> Recording reason...</div>');

        $.ajax({
          type: 'POST',
          url: '/api/records/record_church_member_exclusion_reason.php',
          data: formData,
          dataType: 'json',
          success: function (response) {
            let messageHtml = '';
            if (response.success) {
              messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i> ' + response.message + '</div>';
              $('#exclusionReasonForm')[0].reset();
              setTimeout(function () {
                $('#responseMessage').html('');
              }, 3000);
            } else {
              messageHtml = '<div class="response-message error"><i class="fas fa-times-circle icon"></i> ' + response.message + '</div>';
            }
            $('#responseMessage').html(messageHtml);
          },
          error: function () {
            $('#responseMessage').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i> Something went wrong. Please try again.</div>');
          }
        });
      });
    });
  </script>
</body>

</html>
