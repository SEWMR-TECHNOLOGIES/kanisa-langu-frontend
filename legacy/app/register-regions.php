<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
// Check if the admin session exists
check_session('kanisalangu_admin_id', '../app/sign-in');
?>
<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Add Region - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Add New Region</h5>
            <form id="regionForm">
              <div class="mb-3">
                <label for="regionName" class="form-label">Region Name</label>
                <input type="text" class="form-control" id="regionName" name="region_name" placeholder="Enter Region Name">
              </div>
              <div class="mb-3">
                <button type="submit" class="btn btn-primary">Register Region</button>
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
      // Handle form submission
      $('#regionForm').on('submit', function (event) {
        event.preventDefault(); // Prevent the default form submission

        var region_name = $('#regionName').val();

        $.ajax({
          type: 'POST',
          url: '/api/registration/register_region',
          data: {
            region_name: region_name
          }, 
          dataType: 'json',
          success: function (response) {
            var messageHtml;
            if (response.success) {
              messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i>' + response.message + '</div>';
              setTimeout(function () {
                window.location.href = '/app/manage-regions'; 
              }, 2000); // Timeout duration in milliseconds
            } else {
              messageHtml = '<div class="response-message error"><i class="fas fa-times-circle icon"></i>' + response.message + '</div>';
            }
            $('#responseMessage').html(messageHtml);
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
