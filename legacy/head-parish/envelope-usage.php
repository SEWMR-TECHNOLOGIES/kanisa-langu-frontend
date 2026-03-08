<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
check_session('head_parish_admin_id', '../head-parish/sign-in');
?>
<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Envelope Usage Report - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Envelope Usage Report</h5>

            <!-- Information Row -->
            <div class="alert alert-info" role="alert">
              <strong>Note:</strong> The <strong>Benchmark</strong> is the standard measure of how real attendance compares to it. If not set, the default benchmark is 1000.
            </div>

            <form id="envelopeUsageForm">
              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="usageDate" class="form-label">Select Date</label>
                    <input type="date" class="form-control" id="usageDate" name="usage_date">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="benchmark" class="form-label">Benchmark (Optional)</label>
                    <input type="number" class="form-control" id="benchmark" name="benchmark" placeholder="Enter Benchmark (default: 1000)">
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <button type="submit" class="btn btn-primary">Generate Report</button>
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
      $('#envelopeUsageForm').on('submit', function (event) {
        event.preventDefault();
        $('#responseMessage').html('<div class="response-message"><i class="fas fa-spinner fa-spin icon"></i> Generating Report...</div>');
        
        var usageData = {
          usage_date: $('#usageDate').val(),
          benchmark: $('#benchmark').val() || 1000
        };

        $.ajax({
          type: 'POST',
          url: '../api/data/envelope_usage.php', 
          data: usageData,
          dataType: 'json',
          success: function (response) {
            if (response.success) {
              $('#responseMessage').html('<div class="response-message success"><i class="fas fa-check-circle icon"></i> Report generated successfully. Redirecting...</div>');
              setTimeout(function () {
                window.open(response.report_url, '_blank');
              }, 3000);
            } else {
              $('#responseMessage').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i>' + response.message + '</div>');
            }
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