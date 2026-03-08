<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
check_session('head_parish_admin_id', '../head-parish/sign-in');
?>
<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Add Community - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Add New Community</h5>
            <form id="communityForm">
              <input type="hidden" id="headParishId" name="head_parish_id" value="<?php echo htmlspecialchars($_SESSION['head_parish_id']); ?>">

              <div class="mb-3">
                <label for="subParishId" class="form-label">Sub Parish</label>
                <select class="form-select" id="subParishId" name="sub_parish_id">
                  <option value="">Select Sub Parish</option>
                  <!-- Options will be populated by AJAX -->
                </select>
              </div>

              <div class="mb-3">
                <label for="communityName" class="form-label">Community Name</label>
                <input type="text" class="form-control" id="communityName" name="community_name" placeholder="Enter Community Name">
              </div>

              <div class="mb-3">
                <label for="description" class="form-label">Description (optional)</label>
                <textarea class="form-control" id="description" name="description" placeholder="Enter Description"></textarea>
              </div>

              <button type="submit" class="btn btn-primary">Register Community</button>
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
      $('#subParishId').select2({
        width: '100%'
      });
      // Load sub_parishes into select
      $.ajax({
        type: 'GET',
        url: '../api/data/head_parish_sub_parishes?limit=all',
        data: { head_parish_id: $('#headParishId').val() },
        dataType: 'json',
        success: function (response) {
          var options = '<option value="">Select Sub Parish</option>';
          $.each(response.data, function (index, sub_parish) {
            options += '<option value="' + sub_parish.sub_parish_id + '">' + sub_parish.sub_parish_name + '</option>';
          });
          $('#subParishId').html(options);
        },
        error: function (xhr, status, error) {
          console.log('Error loading sub parishes:', error);
        }
      });

      // Handle form submission
      $('#communityForm').on('submit', function (event) {
        event.preventDefault();

        var communityData = {
          community_name: $('#communityName').val(),
          sub_parish_id: $('#subParishId').val(),
          head_parish_id: $('#headParishId').val(),
          description: $('#description').val()
        };

        $.ajax({
          type: 'POST',
          url: '../api/registration/register_community',
          data: communityData,
          dataType: 'json',
          success: function (response) {
            var messageHtml;
            if (response.success) {
              messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i>' + response.message + '</div>';
              setTimeout(function () {
                window.location.href = './communities';
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
