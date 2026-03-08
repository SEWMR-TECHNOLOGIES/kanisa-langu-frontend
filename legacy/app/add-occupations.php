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
    render_header('Add Occupation - Kanisa Langu');
  ?>
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <?php require_once('components/sidebar.php') ?>
    <div class="body-wrapper">
      <?php require_once('components/header.php') ?>
      <div class="container-fluid">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Add New Occupation</h5>
            <form id="occupationForm">
              <div class="mb-3">
                <label for="occupationName" class="form-label">Occupation Name</label>
                <input type="text" class="form-control" id="occupationName" name="occupation_name" placeholder="Enter Occupation Name">
              </div>
              <div class="mb-3">
                <label for="occupationDescription" class="form-label">Description</label>
                <textarea class="form-control" id="occupationDescription" name="description" placeholder="Enter Description"></textarea>
              </div>
              <div class="mb-3">
                <button type="submit" class="btn btn-primary">Register Occupation</button>
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
      $('#occupationForm').on('submit', function (event) {
        event.preventDefault(); 

        var occupation_name = $('#occupationName').val();
        var description = $('#occupationDescription').val();

        $.ajax({
          type: 'POST',
          url: '/api/registration/register_occupation',
          data: {
            occupation_name: occupation_name,
            description: description
          }, 
          dataType: 'json',
          success: function (response) {
            var messageHtml;
            if (response.success) {
              messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i>' + response.message + '</div>';
              setTimeout(function () {
                window.location.href = '/app/occupations'; 
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
