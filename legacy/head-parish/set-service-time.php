<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
check_session('head_parish_admin_id', '../head-parish/sign-in');
?>
<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Manage Service Times - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Manage Service Times</h5>

            <!-- Information Row -->
            <div class="alert alert-info" role="alert">
              <strong>Note:</strong> Please enter valid service times for each service.
            </div>

            <form id="serviceTimeForm">
              <input type="hidden" id="headParishId" name="head_parish_id" value="<?php echo htmlspecialchars($_SESSION['head_parish_id']); ?>">

              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="service" class="form-label">Select Service</label>
                    <select class="form-select" id="service" name="service">
                      <option value="">Select Service</option>
                      <!-- Options will be populated by AJAX -->
                    </select>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="startTime" class="form-label">Start Time (24-hr Format)</label>
                    <input type="time" class="form-control" id="startTime" name="start_time">
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <button type="submit" class="btn btn-primary">Add / Update Service Time</button>
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
      // Initialize Select2 for all select inputs
      $('select').select2({
        width: '100%'
      });

      // Load services into select
      $.ajax({
        type: 'GET',
        url: '../api/data/head_parish_services', 
        dataType: 'json',
        success: function (response) {
          var options = '<option value="">Select Service</option>';
          $.each(response.data, function (index, service) {
            options += '<option value="' + service.service_id + '">' + service.service + '</option>';
          });
          $('#service').html(options);
        },
        error: function (xhr, status, error) {
          console.log('Error loading services:', error);
        }
      });

      // Handle form submission
      $('#serviceTimeForm').on('submit', function (event) {
        event.preventDefault(); // Prevent the default form submission

        var serviceData = {
          head_parish_id: $('#headParishId').val(),
          service: $('#service').val(),
          start_time: $('#startTime').val(),
        };

        $.ajax({
          type: 'POST',
          url: '../api/registration/set_head_parish_service_time',
          data: serviceData,
          dataType: 'json',
          success: function (response) {
            var messageHtml;
            if (response.success) {
              messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i>' + response.message + '</div>';
              setTimeout(function () {
                window.location.reload();
              }, 2000);
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
