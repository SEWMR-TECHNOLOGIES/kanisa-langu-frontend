<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
check_session('head_parish_admin_id', '../head-parish/sign-in');
?>
<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Set Envelope Target for Head Parish - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Set Envelope Target for Head Parish</h5>

            <form id="setEnvelopeTargetForm" autocomplete="off">
              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="from_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="from_date" name="from_date" value="2025-01-01">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="2025-12-31">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="target" class="form-label">Set Envelope Target</label>
                    <input type="number" class="form-control" id="target" name="target" placeholder="Enter Envelope Target (e.g., 1000)" min="0">
                  </div>
                </div>
              </div>
              <button type="submit" class="btn btn-primary">Set Envelope Target</button>
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
      // Submit the form to set the envelope target
      $('#setEnvelopeTargetForm').on('submit', function (event) {
        event.preventDefault();
        $('#responseMessage').html('<div class="response-message"><i class="fas fa-spinner fa-spin icon"></i> Setting Envelope Target...</div>');

        var targetData = {
          target: $('#target').val(),
          from_date: $('#from_date').val(),
          end_date: $('#end_date').val()
        };

        $.ajax({
          type: 'POST',
          url: '../api/records/set_annual_envelope_target.php', // Endpoint to save the envelope target
          data: targetData,
          dataType: 'json',
          success: function (response) {
            let messageHtml = '';
            if (response.success) {
              messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i>' + response.message + '</div>';
              setTimeout(function () { location.reload(); }, 2000);
            } else {
              messageHtml = '<div class="response-message error"><i class="fas fa-times-circle icon"></i>' + response.message + '</div>';
            }
            $('#responseMessage').html(messageHtml);
          },
          error: function () {
            $('#responseMessage').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i>An error occurred.</div>');
          }
        });
      });
    });
  </script>

</body>
</html>
