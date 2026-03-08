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
    render_header('Record Asset Revenue - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Record Asset Revenue</h5>

            <!-- Asset Revenue Form -->
            <form id="assetRevenueForm" autocomplete="off">
              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="assetId" class="form-label">Select Asset</label>
                    <select class="form-select" id="assetId" name="asset_id">
                      <option value="">Select Asset</option>
                    </select>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="revenueDate" class="form-label">Revenue Date</label>
                    <input type="date" class="form-control" id="revenueDate" name="revenue_date" 
                           max="<?php echo date('Y-m-d'); ?>" 
                           value="<?php echo date('Y-m-d'); ?>">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="revenueAmount" class="form-label">Revenue Amount (TZS)</label>
                    <input type="number" step="0.01" class="form-control" id="revenueAmount" name="revenue_amount" placeholder="Enter revenue amount">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="description" class="form-label">Description (Optional)</label>
                    <textarea class="form-control" id="description" name="description" rows="1" placeholder="Enter additional details..."></textarea>
                  </div>
                </div>
              </div>
              <div class="mb-3">
                <button type="submit" class="btn btn-primary">Record Asset Revenue</button>
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

  $('select').select2({
    width: '100%'
  });
  
  $(document).ready(function () {
    
    // Load revenue-generating assets dynamically
    function loadAssets() {
      $.ajax({
        type: 'GET',
        url: '../api/data/head_parish_assets.php?limit=all', 
        dataType: 'json',
        success: function (response) {
          let options = '<option value="">Select Asset</option>';
          $.each(response.data, function (index, asset) {
            options += `<option value="${asset.asset_id}">${asset.asset_name} (${asset.generates_revenue ? 'Revenue Generating' : 'Non-Revenue'})</option>`;
          });
          $('#assetId').html(options);
        },
        error: function (xhr, status, error) {
          console.log('Error loading assets:', error);
        }
      });
    }

    loadAssets();

    // Submit asset revenue form
    $('#assetRevenueForm').submit(function (event) {
      event.preventDefault();
      $('#responseMessage').html('<div class="loading">Loading...</div>');
      
      let formData = $(this).serialize();

      // Form submission logic
      $.ajax({
        type: 'POST',
        url: '../api/records/record_asset_revenue.php',
        data: formData,
        dataType: 'json',
        success: function (response) {
          let messageHtml = '';
          if (response.success) {
            messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i>' + response.message + '</div>';
            setTimeout(function () {
              $('#assetId').val('').trigger('change');
              $('#revenueAmount').val('');
              $('#description').val('');
              $('#revenueDate').val('<?php echo date('Y-m-d'); ?>');
              $('#responseMessage').html('');
              loadAssets();
            }, 2000);
          } else {
            messageHtml = '<div class="response-message error"><i class="fas fa-times-circle icon"></i>' + response.message + '</div>';
          }
          $('#responseMessage').html(messageHtml);
        },
        error: function (xhr, status, error) {
          const errorMessage = xhr.responseJSON?.message || `An error occurred.${error}`;
          $('#responseMessage').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i>' + errorMessage + '</div>');
        }
      });
    });
  });
</script>
</body>

</html>
