<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
// Call the function on any page that requires superadmin authentication
check_session('head_parish_admin_id', '../head-parish/sign-in');
?>
<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Add Bank Account - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Add New Bank Account</h5>
            <form id="bankAccountForm">
              <input type="hidden" id="referenceId" name="reference_id" value="<?php echo htmlspecialchars($_SESSION['head_parish_id']); ?>">

              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="accountName" class="form-label">Account Name</label>
                    <input type="text" class="form-control" id="accountName" name="account_name" placeholder="Enter Account Name">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="accountNumber" class="form-label">Account Number</label>
                    <input type="text" class="form-control" id="accountNumber" name="account_number" placeholder="Enter Account Number">
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="bankId" class="form-label">Bank</label>
                    <select class="form-select" id="bankId" name="bank_id">
                      <option value="">Select Bank</option>
                      <!-- Options will be populated by AJAX -->
                    </select>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="balance" class="form-label">Current Balance</label>
                    <input type="text" class="form-control" id="balance" name="balance" placeholder="Enter Current Balance (optional)">
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <button type="submit" class="btn btn-primary">Register Bank Account</button>
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
      $('#bankId').select2({
        width: '100%'
      });

      // Load banks into select2
      $.ajax({
        type: 'GET',
        url: '../api/data/banks?limit=all', 
        dataType: 'json',
        success: function (response) {
          var options = '<option value="">Select Bank</option>';
          $.each(response.data, function (index, bank) {
            options += '<option value="' + bank.bank_id + '">' + bank.bank_name + '</option>';
          });
          $('#bankId').html(options);
        },
        error: function (xhr, status, error) {
          console.log('Error loading banks:', error);
        }
      });

      // Handle form submission
      $('#bankAccountForm').on('submit', function (event) {
        event.preventDefault(); // Prevent the default form submission

        var bankAccountData = {
          account_name: $('#accountName').val(),
          account_number: $('#accountNumber').val(),
          bank_id: $('#bankId').val(),
          reference_id: $('#referenceId').val(),
          balance: $('#balance').val(), 
          target: 'head_parish'
        };

        $.ajax({
          type: 'POST',
          url: '../api/registration/register_bank_account',
          data: bankAccountData,
          dataType: 'json',
          success: function (response) {
            var messageHtml;
            if (response.success) {
              messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i>' + response.message + '</div>';
              setTimeout(function () {
                window.location.href = './bank-accounts'; // Redirect to the bank accounts listing page
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
