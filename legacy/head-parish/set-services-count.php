<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
check_session('head_parish_admin_id', '../head-parish/sign-in');

// Fetch the current services_count for the head parish
$head_parish_id = $_SESSION['head_parish_id'];
$current_service_count = 0;

if ($head_parish_id) {
    $stmt = $conn->prepare("SELECT services_count FROM head_parishes WHERE head_parish_id = ?");
    $stmt->bind_param("i", $head_parish_id);
    $stmt->execute();
    $stmt->bind_result($current_service_count);
    $stmt->fetch();
    $stmt->close();
}

?>
<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Set Services Count - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Set Services Count</h5>

            <!-- Display current service count if greater than 0 -->
            <?php if ($current_service_count > 0): ?>
              <div class="alert alert-info">
                <strong>Current Service Count:</strong> <?php echo htmlspecialchars($current_service_count); ?>. Setting this will update it.
              </div>
            <?php endif; ?>

            <!-- Form to update services count -->
            <form id="serviceCountForm">
              <input type="hidden" id="headParishId" name="head_parish_id" value="<?php echo htmlspecialchars($head_parish_id); ?>">

              <div class="mb-3">
                <label for="servicesCount" class="form-label">New Services Count</label>
                <input type="number" class="form-control" id="servicesCount" name="services_count" min="0" placeholder="Enter new service count">
              </div>

              <div class="mb-3">
                <button type="submit" class="btn btn-primary">Update Services Count</button>
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
      $('#serviceCountForm').on('submit', function (event) {
        event.preventDefault(); // Prevent the default form submission

        var serviceData = {
          services_count: $('#servicesCount').val(),
          head_parish_id: $('#headParishId').val()
        };

        $.ajax({
          type: 'POST',
          url: '../api/registration/set_head_parish_services_count',
          data: serviceData,
          dataType: 'json',
          success: function (response) {
            var messageHtml;
            if (response.success) {
              messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i>' + response.message + '</div>';
              setTimeout(function () {
                location.reload(); // Reload the page to reflect the updated count
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
