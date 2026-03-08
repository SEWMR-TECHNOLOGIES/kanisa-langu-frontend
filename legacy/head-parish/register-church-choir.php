<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
check_session('head_parish_admin_id', '../head-parish/sign-in');
?>
<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Add Church Choir - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Add New Church Choir</h5>
            <form id="churchChoirForm">
              <input type="hidden" id="headParishId" name="head_parish_id" value="<?php echo htmlspecialchars($_SESSION['head_parish_id']); ?>">

              <div class="row">
                <div class="col-lg-12">
                  <div class="mb-3">
                    <label for="choirName" class="form-label">Choir Name</label>
                    <input type="text" class="form-control" id="choirName" name="choir_name" placeholder="Enter Choir Name">
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <button type="submit" class="btn btn-primary">Register Church Choir</button>
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
      $('#churchChoirForm').on('submit', function (event) {
        event.preventDefault();

        // Show loading spinner when submitting the form
        $('#responseMessage').html('<div class="response-message"><i class="fas fa-spinner fa-spin icon"></i> Sending Request...</div>');

        var choirData = {
          choir_name: $('#choirName').val(),
          head_parish_id: $('#headParishId').val()
        };

        $.ajax({
          type: 'POST',
          url: '../api/registration/register_church_choir', 
          data: choirData,
          dataType: 'json',
          success: function (response) {
            var messageHtml;
            if (response.success) {
              messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i>' + response.message + '</div>';
              setTimeout(function () {
                window.location.href = './register-church-choir'; 
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
