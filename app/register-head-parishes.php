<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
// Call the function on any page that requires superadmin authentication
check_session('kanisalangu_admin_id', '/app/sign-in');
?>
<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Add Head Parish - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Add New Head Parish</h5>
            <form id="headParishForm">
              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="headParishName" class="form-label">Head Parish Name</label>
                    <input type="text" class="form-control" id="headParishName" name="head_parish_name" placeholder="Enter Head Parish Name">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="headParishEmail" class="form-label">Head Parish Email</label>
                    <input type="email" class="form-control" id="headParishEmail" name="head_parish_email" placeholder="Enter Head Parish Email">
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="headParishPhone" class="form-label">Head Parish Phone</label>
                    <input type="text" class="form-control" id="headParishPhone" name="head_parish_phone" placeholder="Enter Head Parish Phone">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="headParishAddress" class="form-label">Head Parish Address</label>
                    <input type="text" class="form-control" id="headParishAddress" name="head_parish_address" placeholder="Enter Head Parish Address">
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
                    <label for="provinceId" class="form-label">Province</label>
                    <select class="form-select" id="provinceId" name="province_id">
                      <option value="">Select Province</option>
                      <!-- Options will be populated by AJAX based on selected diocese -->
                    </select>
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

              <div class="mb-3 text-start">
                <button type="submit" class="btn btn-primary">Register Head Parish</button>
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
      $('#dioceseId, #provinceId, #regionId, #districtId').select2({
        width: '100%'
      });

      // Load dioceses into select2
      $.ajax({
        type: 'GET',
        url: '/api/data/dioceses?limit=all', 
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

      // Load provinces when a diocese is selected
      $('#dioceseId').on('change', function () {
        var dioceseId = $(this).val();
        $.ajax({
          type: 'GET',
          url: '/api/data/get_selected_diocese_provinces.php', 
          data: { diocese_id: dioceseId },
          dataType: 'json',
          success: function (response) {
            var options = '<option value="">Select Province</option>';
            $.each(response.data, function (index, province) {
              options += '<option value="' + province.province_id + '">' + province.province_name + '</option>';
            });
            $('#provinceId').html(options);
          },
          error: function (xhr, status, error) {
            console.log('Error loading provinces:', error);
          }
        });
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
      $('#headParishForm').on('submit', function (event) {
        event.preventDefault(); // Prevent the default form submission

        var headParishData = {
          head_parish_name: $('#headParishName').val(),
          diocese_id: $('#dioceseId').val(),
          province_id: $('#provinceId').val(),
          region_id: $('#regionId').val(),
          district_id: $('#districtId').val(),
          head_parish_address: $('#headParishAddress').val(),
          head_parish_email: $('#headParishEmail').val(),
          head_parish_phone: $('#headParishPhone').val()
        };

        $.ajax({
          type: 'POST',
          url: '/api/registration/register_head_parish.php',
          data: headParishData,
          dataType: 'json',
          success: function (response) {
            var messageHtml;
            if (response.success) {
              messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i>' + response.message + '</div>';
              setTimeout(function () {
                window.location.href = './manage-head-parishes';
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