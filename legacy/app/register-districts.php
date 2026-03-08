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
    render_header('Add District - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Add New District</h5>
            <form id="districtForm">
              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="regionId" class="form-label">Region</label>
                    <select class="form-select" id="regionId" name="region_id" placeholder="Select Region">
                      <option value="">Select Region</option>
                      <!-- Options will be populated by AJAX -->
                    </select>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="districtName" class="form-label">District Name</label>
                    <input type="text" class="form-control" id="districtName" name="district_name" placeholder="Enter District Name">
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <button type="submit" class="btn btn-primary">Register District</button>
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
      // Initialize select2 for select inputs
      $('#regionId').select2({
        width: '100%'
      });

      // Load regions into select2
      $.ajax({
        type: 'GET',
        url: '/api/data/regions?limit=all', 
        dataType: 'json',
        success: function (response) {
          var options = '<option value="">Select Region</option>';
          $.each(response.data, function (index, region) {
            options += '<option value="' + region.id + '">' + region.name + '</option>';
          });
          $('#regionId').html(options);
        },
        error: function (xhr, status, error) {
          console.log('Error loading regions:', error);
        }
      });

      // Handle form submission
      $('#districtForm').on('submit', function (event) {
        event.preventDefault(); // Prevent the default form submission

        var districtData = {
          district_name: $('#districtName').val(),
          region_id: $('#regionId').val()
        };

        $.ajax({
          type: 'POST',
          url: '/api/registration/register_district',
          data: districtData,
          dataType: 'json',
          success: function (response) {
            var messageHtml;
            if (response.success) {
              messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i>' + response.message + '</div>';
              setTimeout(function () {
                window.location.href = '/app/manage-districts'; 
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
