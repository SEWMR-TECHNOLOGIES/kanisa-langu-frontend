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
    render_header('Add Diocese Admin - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Add New Diocese Admin</h5>
            <form id="adminForm">
              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="adminFullname" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="adminFullname" name="diocese_admin_fullname" placeholder="Enter Full Name">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="adminEmail" class="form-label">Email</label>
                    <input type="email" class="form-control" id="adminEmail" name="diocese_admin_email" placeholder="Enter Email">
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="adminPhone" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="adminPhone" name="diocese_admin_phone" placeholder="Enter Phone Number">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="dioceseId" class="form-label">Diocese</label>
                    <select class="form-select" id="dioceseId" name="diocese_id">
                      <option value="">Select Diocese</option>
                      <!-- Options will be populated by AJAX -->
                    </select>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="adminRole" class="form-label">Admin Role</label>
                    <select class="form-select" id="adminRole" name="diocese_admin_role">
                      <option value="">Select Role</option>
                      <option value="admin">Admin</option>
                      <option value="bishop">Bishop</option>
                      <option value="secretary">Secretary</option>
                      <option value="chairperson">Chairperson</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="mb-3 text-start">
                <button type="submit" class="btn btn-primary">Register Admin</button>
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
      $('#dioceseId, #adminRole').select2({
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

      // Handle form submission
      $('#adminForm').on('submit', function (event) {
        event.preventDefault(); // Prevent the default form submission

        var adminData = {
          diocese_admin_fullname: $('#adminFullname').val(),
          diocese_admin_email: $('#adminEmail').val(),
          diocese_admin_phone: $('#adminPhone').val(),
          diocese_id: $('#dioceseId').val(),
          diocese_admin_role: $('#adminRole').val()
        };

        $.ajax({
          type: 'POST',
          url: '/api/registration/create_diocese_admin', 
          data: adminData,
          dataType: 'json',
          success: function (response) {
            var messageHtml;
            if (response.success) {
              messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i>' + response.message + '</div>';
              setTimeout(function () {
                window.location.href = './diocese-admins-list'; 
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
