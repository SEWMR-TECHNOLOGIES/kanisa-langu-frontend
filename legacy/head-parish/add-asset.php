<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
// Ensure only head parish admins can access this page
check_session('head_parish_admin_id', '../head-parish/sign-in');
?>
<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Add Asset - Kanisa Langu');
  ?>
</head>

<body>
  <!-- Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    
    <!-- Sidebar -->
    <?php require_once('components/sidebar.php') ?>

    <!-- Main wrapper -->
    <div class="body-wrapper">
      
      <!-- HEADER -->
      <?php require_once('components/header.php') ?>

      <div class="container-fluid">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Add New Asset</h5>
            
            <form id="assetForm">

              <div class="mb-3">
                <label for="assetName" class="form-label">Asset Name</label>
                <input type="text" class="form-control" id="assetName" name="asset_name" placeholder="Enter Asset Name">
              </div>

              <div class="mb-3">
                <label for="generatesRevenue" class="form-label">Generates Revenue?</label>
                <select class="form-select" id="generatesRevenue" name="generates_revenue">
                  <option value="0">No</option>
                  <option value="1">Yes</option>
                </select>
              </div>

              <div class="mb-3">
                <button type="submit" class="btn btn-primary">Register Asset</button>
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
      $('#generatesRevenue').select2({
        width: '100%'
      });
    
      // Handle form submission
      $('#assetForm').on('submit', function (event) {
        event.preventDefault(); // Prevent default form submission
    
        var formData = $(this).serialize(); // Serialize form data (URL-encoded format)
        $('#responseMessage').html('<div class="response-message"><i class="fas fa-spinner fa-spin icon"></i> Adding Asset...</div>');
        
        $.ajax({
          type: 'POST',
          url: '../api/registration/add_asset',
          data: formData, // Send as form data, not JSON
          success: function (response) {
            var messageHtml;
            if (response.success) {
              messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i> ' + response.message + '</div>';
            } else {
              messageHtml = '<div class="response-message error"><i class="fas fa-times-circle icon"></i> ' + response.message + '</div>';
            }
            $('#responseMessage').html(messageHtml);
    
            // Clear the message and reset form after 3 seconds
            setTimeout(function () {
              $('#responseMessage').html(''); // Clear the message
              $('#assetForm')[0].reset(); // Reset the form
            }, 3000); // Adjust timeout as needed (3000ms = 3 seconds)
          },
          error: function (xhr, status, error) {
            $('#responseMessage').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i> An error occurred: ' + error + '</div>');
    
            // Clear the message and reset form after 3 seconds
            setTimeout(function () {
              $('#responseMessage').html(''); // Clear the message
              $('#assetForm')[0].reset(); // Reset the form
            }, 3000); // Adjust timeout as needed (3000ms = 3 seconds)
          }
        });
      });
    });

  </script>

</body>
</html>
