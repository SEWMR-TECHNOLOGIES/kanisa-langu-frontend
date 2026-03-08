<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
check_session('head_parish_admin_id', '../head-parish/sign-in');
?>

<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Letter Status Report - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Download Letter Status Report</h5>

            <form id="letterStatusForm">
              <div class="row">
                <div class="col-lg-6 mb-3">
                  <label for="statusFilter" class="form-label">Select Status</label>
                  <select class="form-select" id="statusFilter" name="status" required>
                    <option value="">-- Select One --</option>
                    <option value="received">Received</option>
                    <option value="not_received">Not Received</option>
                  </select>
                </div>
              </div>

              <div class="mb-3">
                <button type="submit" class="btn btn-primary">Generate Report</button>
                
                <!-- Download report must be here -->
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
    $('#letterStatusForm').submit(function (event) {
      event.preventDefault();

      $('#responseMessage').html('<div class="loading"><i class="fas fa-spinner fa-spin"></i> Generating report...</div>');

      // Create a FormData object from the form
      var formData = new FormData(this);

      $.ajax({
        type: 'POST',
        url: '../reports/harambee_letters_status_report.php',  
        data: formData,
        processData: false,  // Prevent jQuery from automatically processing the data
        contentType: false,  // Prevent jQuery from automatically setting the content type
        dataType: 'json',    // Expect a JSON response
        success: function (response) {
          let messageHtml = '';
          if (response.success) {
            messageHtml = `<div class="response-message success"><i class="fas fa-check-circle icon"></i> ${response.message}</div>`;
            if (response.download_url) {
              messageHtml += `<br><a href="${response.download_url}" class="btn btn-success mt-2" download>Download Report</a>`;
            }
          } else {
            messageHtml = `<div class="response-message error"><i class="fas fa-times-circle icon"></i> ${response.message}</div>`;
          }
          $('#responseMessage').html(messageHtml);
        },
        error: function (xhr, status, error) {
          const errorMessage = xhr.responseJSON?.message || `An error occurred: ${error}`;
          $('#responseMessage').html(`<div class="response-message error"><i class="fas fa-times-circle icon"></i> ${errorMessage}</div>`);
        }
      });
    });
  });
</script>


</body>

</html>