<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
check_session('head_parish_admin_id', '../head-parish/sign-in');
?>
<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Add Church Leader - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Add New Church Leader</h5>
            <form id="churchLeaderForm">
              <input type="hidden" id="headParishId" name="head_parish_id" value="<?php echo htmlspecialchars($_SESSION['head_parish_id']); ?>">

              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="titleId" class="form-label">Title (optional)</label>
                    <select class="form-select" id="titleId" name="title_id">
                      <option value="">Select Title</option>
                      <!-- Options will be populated by AJAX -->
                    </select>
                  </div>
                </div>

                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="firstName" class="form-label">First Name</label>
                    <input type="text" class="form-control" id="firstName" name="first_name" placeholder="Enter First Name">
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="middleName" class="form-label">Middle Name (optional)</label>
                    <input type="text" class="form-control" id="middleName" name="middle_name" placeholder="Enter Middle Name">
                  </div>
                </div>

                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="lastName" class="form-label">Last Name</label>
                    <input type="text" class="form-control" id="lastName" name="last_name" placeholder="Enter Last Name">
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="gender" class="form-label">Gender</label>
                    <select class="form-select" id="gender" name="gender">
                      <option value="">Select Gender</option>
                      <option value="Male">Male</option>
                      <option value="Female">Female</option>
                    </select>
                  </div>
                </div>

                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="type" class="form-label">Type</label>
                    <select class="form-select" id="type" name="type">
                      <option value="">Select Type</option>
                      <option value="Mgeni">Mgeni</option>
                      <option value="Mwenyeji">Mwenyeji</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="roleId" class="form-label">Role</label>
                    <select class="form-select" id="roleId" name="role_id">
                      <option value="">Select Role</option>
                      <!-- Options will be populated by AJAX -->
                    </select>
                  </div>
                </div>

                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="appointmentDate" class="form-label">Appointment Date</label>
                    <input type="date" class="form-control" id="appointmentDate" name="appointment_date" value="<?php echo date('Y-m-d'); ?>">
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="endDate" class="form-label">End Date (optional)</label>
                    <input type="date" class="form-control" id="endDate" name="end_date">
                  </div>
                </div>

                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                      <option value="Active">Active</option>
                      <option value="Inactive">Inactive</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <button type="submit" class="btn btn-primary">Register Church Leader</button>
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
    
      // Load titles into the select
      $.ajax({
        type: 'GET',
        url: '../api/data/titles?limit=all', // Update with your API endpoint for titles
        dataType: 'json',
        success: function (response) {
          var options = '<option value="">Select Title</option>';
          $.each(response.data, function (index, title) {
            options += '<option value="' + title.id + '">' + title.name + '</option>';
          });
          $('#titleId').html(options).trigger('change'); // Trigger change to update Select2
        },
        error: function (xhr, status, error) {
          console.log('Error loading titles:', error);
        }
      });
    
      // Load roles into the select
      $.ajax({
        type: 'GET',
        url: '../api/data/church_roles?limit=all', // Update with your API endpoint for roles
        dataType: 'json',
        success: function (response) {
          var options = '<option value="">Select Role</option>';
          $.each(response.data, function (index, role) {
            options += '<option value="' + role.role_id + '">' + role.role_name + '</option>';
          });
          $('#roleId').html(options).trigger('change'); // Trigger change to update Select2
        },
        error: function (xhr, status, error) {
          console.log('Error loading roles:', error);
        }
      });
    
      // Handle form submission
      $('#churchLeaderForm').on('submit', function (event) {
        event.preventDefault();
    
        // Show loading spinner when submitting the form
        $('#responseMessage').html('<div class="response-message"><i class="fas fa-spinner fa-spin icon"></i> Sending Request...</div>');
    
        var leaderData = {
          title_id: $('#titleId').val(),
          first_name: $('#firstName').val(),
          middle_name: $('#middleName').val(),
          last_name: $('#lastName').val(),
          gender: $('#gender').val(),
          type: $('#type').val(),
          role_id: $('#roleId').val(),
          appointment_date: $('#appointmentDate').val(),
          end_date: $('#endDate').val(),
          status: $('#status').val(),
          head_parish_id: $('#headParishId').val()
        };
    
        $.ajax({
          type: 'POST',
          url: '../api/registration/register_church_leader', 
          data: leaderData,
          dataType: 'json',
          success: function (response) {
            var messageHtml;
            if (response.success) {
              messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i>' + response.message + '</div>';
              setTimeout(function () {
                window.location.href = './register-church-leader'; 
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
