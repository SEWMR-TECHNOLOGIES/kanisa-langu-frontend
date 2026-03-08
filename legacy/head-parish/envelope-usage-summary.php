<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
check_session('head_parish_admin_id', '../head-parish/sign-in');
?>
<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Download Envelope Usage Summary - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Download Envelope Usage Summary</h5>

            <form id="downloadEnvelopeUsageForm" method="GET" action="/reports/download_envelope_usage_report.php" target="_blank" autocomplete="off">
              <div class="row g-2 align-items-end">
                <div class="col-12 col-md-6 col-lg-4">
                  <label for="report_date" class="form-label">Select Date</label>
                  <input type="date" class="form-control" id="report_date" name="report_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="col-12 col-md-6 col-lg-2">
                  <button type="submit" class="btn btn-primary w-100">Download</button>
                </div>
              </div>
              <div id="responseMessage" class="mt-2"></div>
            </form>

          </div>
        </div>
      </div>
    </div>
  </div>

  <?php require_once('components/footer_files.php') ?>

  <script>
    $(document).ready(function () {
      $('#downloadEnvelopeUsageForm').on('submit', function (e) {
        const reportDate = $('#report_date').val();
        if (!reportDate) {
          e.preventDefault();
          $('#responseMessage').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i> Please select a date.</div>');
        }
      });
    });
  </script>

</body>
</html>
