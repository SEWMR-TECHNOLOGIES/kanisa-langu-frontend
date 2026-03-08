<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
// Call the function on any page that requires superadmin authentication
check_session('kanisalangu_admin_id', '../app/sign-in');
?>
<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Add Diocese - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Add New Diocese</h5>
            <form id="dioceseForm">
              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="dioceseName" class="form-label">Diocese Name</label>
                    <input type="text" class="form-control" id="dioceseName" name="diocese_name" placeholder="Enter Diocese Name">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="dioceseEmail" class="form-label">Diocese Email</label>
                    <input type="email" class="form-control" id="dioceseEmail" name="diocese_email" placeholder="Enter Diocese Email">
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="diocesePhone" class="form-label">Diocese Phone</label>
                    <input type="text" class="form-control" id="diocesePhone" name="diocese_phone" placeholder="Enter Diocese Phone">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="dioceseAddress" class="form-label">Diocese Address</label>
                    <input type="text" class="form-control" id="dioceseAddress" name="diocese_address" placeholder="Enter Diocese Address">
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="regionId" class="form-label">Region</label>
                    <select class="form-select" id="regionId" name="region_id">
                      <option value="">Select Region</option>
                      <!-- Options will be populated by AJAX -->
                    </select>
                  </div>                      
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="districtId" class="form-label">District</label>
                    <select class="form-select" id="districtId" name="district_id">
                      <option value="">Select District</option>
                      <!-- Options will be populated by AJAX -->
                    </select>
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <button type="submit" class="btn btn-primary">Register Diocese</button>
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
      // Initialize select2
      $('#regionId').select2({
        placeholder: 'Select Region',
        width: '100%' // Make sure Select2 takes full width
      });

      $('#districtId').select2({
        placeholder: 'Select District',
        width: '100%' // Make sure Select2 takes full width
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

      // Load districts when a region is selected
      $('#regionId').on('change', function () {
        var regionId = $(this).val();
        $.ajax({
          type: 'GET',
          url: '/api/data/districts?limit=all',
          data: { region_id: regionId },
          dataType: 'json',
          success: function (response) {
            var options = '<option value="">Select District</option>';
            $.each(response.data, function (index, district) {
              options += '<option value="' + district.id + '">' + district.name + '</option>';
            });
            $('#districtId').html(options);
          },
          error: function (xhr, status, error) {
            console.log('Error loading districts:', error);
          }
        });
      });

      // Handle form submission
      $('#dioceseForm').on('submit', function (event) {
        event.preventDefault(); // Prevent the default form submission

        var dioceseData = {
          diocese_name: $('#dioceseName').val(),
          region_id: $('#regionId').val(),
          district_id: $('#districtId').val(),
          diocese_address: $('#dioceseAddress').val(),
          diocese_email: $('#dioceseEmail').val(),
          diocese_phone: $('#diocesePhone').val()
        };

        $.ajax({
          type: 'POST',
          url: '/api/registration/register_diocese',
          data: dioceseData, 
          dataType: 'json',
          success: function (response) {
            var messageHtml;
            if (response.success) {
              messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i>' + response.message + '</div>';
              setTimeout(function () {
                window.location.href = '/app/manage-dioceses'; 
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
