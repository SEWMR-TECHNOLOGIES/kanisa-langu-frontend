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
    render_header('Record Harambee Classes - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Record Harambee Class</h5>
            
            <form id="classForm">
              <div class="row">
                <div class="col-lg-4">
                  <div class="mb-3">
                    <label for="className" class="form-label">Class Name</label>
                    <select class="form-select" id="className" name="class_name">
                      <option value="">Select Class</option>
                      <?php foreach (range('A', 'Z') as $letter): ?>
                        <option value="<?php echo $letter; ?>"><?php echo $letter; ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="mb-3">
                    <label for="amountMin" class="form-label">Minimum Amount</label>
                    <input type="number" class="form-control" id="amountMin" name="amount_min" placeholder="Enter minimum amount">
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="mb-3">
                    <label for="amountMax" class="form-label">Maximum Amount</label>
                    <input type="number" class="form-control" id="amountMax" name="amount_max" placeholder="Enter maximum amount">
                  </div>
                </div>
              </div>


              <div class="mb-3">
                <button type="submit" class="btn btn-primary">Record Class</button>
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
      $('select').select2({
        width: '100%'
      });

      $('#classForm').on('submit', function (e) {
        e.preventDefault();
        const formData = $(this).serialize();
        $('#responseMessage').html('<div class="response-message"><i class="fas fa-spinner fa-spin icon"></i> Recording class...</div>');

        $.ajax({
          type: 'POST',
          url: '../api/records/record_harambee_classes', 
          data: formData,
          dataType: 'json',
          success: function (response) {
            let messageHtml = '';
            if (response.success) {
              messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i> ' + response.message + '</div>';
              $('#classForm')[0].reset();
              $('#className').val('').trigger('change');
                  setTimeout(function () {
                  $('#responseMessage').html('');
                }, 2000);
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
