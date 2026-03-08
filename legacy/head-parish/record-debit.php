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
    render_header('Record Debit - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Record Debit</h5>

            <!-- Debit Form -->
            <form id="debitForm" autocomplete="off">
              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <input type="text" class="form-control" id="description" name="description" placeholder="Enter description">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="amount" class="form-label">Amount (TZS)</label>
                    <input type="number" step="0.01" class="form-control" id="amount" name="amount" placeholder="Enter amount">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="dateDebited" class="form-label">Date Debited</label>
                    <input type="date" class="form-control" id="dateDebited" name="date_debited" max="<?php echo date('Y-m-d'); ?>">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="returnBeforeDate" class="form-label">Return Before Date</label>
                    <input type="date" class="form-control" id="returnBeforeDate" name="return_before_date">
                  </div>
                </div>
              </div>
              <div class="mb-3">
                <label for="purpose" class="form-label">Purpose</label>
                <textarea class="form-control" id="purpose" name="purpose" rows="2" placeholder="Enter purpose..."></textarea>
              </div>
              <div class="mb-3">
                <button type="submit" class="btn btn-primary">Record Debit</button>
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
    $('#debitForm').submit(function (event) {
      event.preventDefault();
      $('#responseMessage').html('<div class="response-message"><i class="fas fa-spinner fa-spin icon"></i> Recording Debit</div>');
      
      let formData = $(this).serialize();
      
      $.ajax({
        type: 'POST',
        url: '../api/records/record_head_parish_debits.php',
        data: formData,
        dataType: 'json',
        success: function (response) {
          let messageHtml = '';
          if (response.success) {
            messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i>' + response.message + '</div>';
            setTimeout(function () {
              $('#debitForm')[0].reset();
              $('#responseMessage').html('');
            }, 2000);
          } else {
            messageHtml = '<div class="response-message error"><i class="fas fa-times-circle icon"></i>' + response.message + '</div>';
          }
          $('#responseMessage').html(messageHtml);
        },
        error: function (xhr, status, error) {
          const errorMessage = xhr.responseJSON?.message || `An error occurred: ${error}`;
          $('#responseMessage').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i>' + errorMessage + '</div>');
        }
      });
    });
  });
</script>
</body>
</html>
