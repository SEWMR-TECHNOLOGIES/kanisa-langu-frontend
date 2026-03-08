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
    render_header('Add Province - Kanisa Langu');
  ?>
</head>

<body>
  <!-- Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <!-- Sidebar -->
    <?php require_once('components/sidebar.php') ?>
    <!-- Main Wrapper -->
    <div class="body-wrapper">
      <!-- HEADER -->
      <?php require_once('components/header.php') ?>
      <div class="container-fluid">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Add New Province</h5>
            <form id="provinceForm">
              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="provinceName" class="form-label">Province Name</label>
                    <input type="text" class="form-control" id="provinceName" name="province_name" placeholder="Enter Province Name">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="provinceEmail" class="form-label">Province Email</label>
                    <input type="email" class="form-control" id="provinceEmail" name="province_email" placeholder="Enter Province Email">
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="provincePhone" class="form-label">Province Phone</label>
                    <input type="text" class="form-control" id="provincePhone" name="province_phone" placeholder="Enter Province Phone">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="provinceAddress" class="form-label">Province Address</label>
                    <input type="text" class="form-control" id="provinceAddress" name="province_address" placeholder="Enter Province Address">
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="dioceseId" class="form-label">Diocese</label>
                    <select class="form-select" id="dioceseId" name="diocese_id">
                      <option value="">Select Diocese</option>
                      <!-- Options will be populated by AJAX -->
                    </select>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="regionId" class="form-label">Region</label>
                    <select class="form-select" id="regionId" name="region_id">
                      <option value="">Select Region</option>
                      <!-- Options will be populated by AJAX -->
                    </select>
                  </div>
                </div>
              </div>

              <div class="row">
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

              <div class="mb-3 text-start">
                <button type="submit" class="btn btn-primary">Register Province</button>
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
      $('#dioceseId, #regionId, #districtId').select2({
        width: '100%'
      });

      // Load dioceses into select2
      $.ajax({
        type: 'GET',
        url: '/api/data/dioceses?limit=all', // Update with the correct API endpoint
        dataType: 'json',
        success: function (response) {
          var options = '<option value="">Select Diocese</option>';
          $.each(response.data, function (index, diocese) {
            options += '<option value="' + diocese.diocese_id + '">' + diocese.diocese_name + '</option>';
          });
          $('#dioceseId').html(options);
        },
        error: function (xhr, status, error) {
          console.log('Error loading dioceses:', error);
        }
      });

      // Load regions into select2
      $.ajax({
        type: 'GET',
        url: '../api/data/regions?limit=all', // Update with the correct API endpoint
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
          url: '../api/data/districts?limit=all',
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
      $('#provinceForm').on('submit', function (event) {
        event.preventDefault(); // Prevent the default form submission

        var provinceData = {
          province_name: $('#provinceName').val(),
          diocese_id: $('#dioceseId').val(),
          region_id: $('#regionId').val(),
          district_id: $('#districtId').val(),
          province_address: $('#provinceAddress').val(),
          province_email: $('#provinceEmail').val(),
          province_phone: $('#provincePhone').val()
        };

        $.ajax({
          type: 'POST',
          url: '../api/registration/register_province',
          data: provinceData,
          dataType: 'json',
          success: function (response) {
            var messageHtml;
            if (response.success) {
              messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i>' + response.message + '</div>';
              setTimeout(function () {
                window.location.href = './manage-provinces'; 
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
