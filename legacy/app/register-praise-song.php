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
    render_header('Add Praise Song - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Add New Praise Song</h5>
            <form id="praiseSongForm">

              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="songNumber" class="form-label">Song Number</label>
                    <input type="number" class="form-control" id="songNumber" name="song_number" placeholder="Enter Song Number">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="songName" class="form-label">Song Name</label>
                    <input type="text" class="form-control" id="songName" name="song_name" placeholder="Enter Song Name">
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="pageNumber" class="form-label">Page Number</label>
                    <input type="number" class="form-control" id="pageNumber" name="page_number" placeholder="Enter Page Number">
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <button type="submit" class="btn btn-primary">Register Praise Song</button>
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
      $('#praiseSongForm').on('submit', function (event) {
        event.preventDefault();

        // Show loading spinner when submitting the form
        $('#responseMessage').html('<div class="response-message"><i class="fas fa-spinner fa-spin icon"></i> Sending Request...</div>');

        var songData = {
          song_number: $('#songNumber').val(),
          song_name: $('#songName').val(),
          page_number: $('#pageNumber').val()
        };

        $.ajax({
          type: 'POST',
          url: '../api/registration/register_praise_song', 
          data: songData,
          dataType: 'json',
          success: function (response) {
            var messageHtml;
            if (response.success) {
              messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i>' + response.message + '</div>';
              setTimeout(function () {
                window.location.href = './register-praise-song'; 
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
