<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Add Revenue Stream - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Add New Revenue Stream</h5>
            <form id="revenueStreamForm">
              <input type="hidden" id="referenceId" name="reference_id" value="<?php echo htmlspecialchars($_SESSION['head_parish_id']); ?>">
              
              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="revenueStreamName" class="form-label">Revenue Stream Name</label>
                    <input type="text" class="form-control" id="revenueStreamName" name="revenue_stream_name" placeholder="Enter Revenue Stream Name">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="accountId" class="form-label">Account</label>
                    <select class="form-select" id="accountId" name="account_id">
                      <option value="">Select Account</option>
                      <!-- Options will be populated by AJAX -->
                    </select>
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <button type="submit" class="btn btn-primary">Register Revenue Stream</button>
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
      $('#accountId').select2({
        width: '100%'
      });

      // Load accounts into select2
      $.ajax({
        type: 'GET',
        url: '../api/data/head_parish_bank_accounts?limit=all', 
        dataType: 'json',
        success: function (response) {
          var options = '<option value="">Select Account</option>';
          $.each(response.data, function (index, account) {
            options += '<option value="' + account.account_id + '">' + account.account_name + '</option>';
          });
          $('#accountId').html(options);
        },
        error: function (xhr, status, error) {
          console.log('Error loading accounts:', error);
        }
      });

      // Handle form submission
      $('#revenueStreamForm').on('submit', function (event) {
        event.preventDefault(); // Prevent the default form submission

        var revenueStreamData = {
          revenue_stream_name: $('#revenueStreamName').val(),
          account_id: $('#accountId').val(),
          reference_id: $('#referenceId').val(),
          target: 'head_parish' 
        };

        $.ajax({
          type: 'POST',
          url: '../api/registration/register_revenue_stream', 
          data: revenueStreamData,
          dataType: 'json',
          success: function (response) {
            var messageHtml;
            if (response.success) {
              messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i>' + response.message + '</div>';
              setTimeout(function () {
                window.location.href = './revenue-streams'; 
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
