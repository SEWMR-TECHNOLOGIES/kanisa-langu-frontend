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
    render_header('Add Payment Gateway Wallet - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Add Payment Gateway Wallet</h5>

            <!-- Information Row -->
            <div class="alert alert-info" role="alert">
              <strong>Note:</strong> The <strong>Merchant Code</strong>, <strong>Client ID</strong>, and <strong>Secret Key</strong> are provided by your payment gateway service provider. For more details, please contact our support team at <a href="mailto:info@kanisalangu.sewmrtechnologies.com">info@kanisalangu.sewmrtechnologies.com</a>.
            </div>

            <form id="paymentGatewayWalletForm">
              <input type="hidden" id="referenceId" name="reference_id" value="<?php echo htmlspecialchars($_SESSION['head_parish_id']); ?>">

              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="merchantCode" class="form-label">Merchant Code</label>
                    <input type="text" class="form-control" id="merchantCode" name="merchant_code" placeholder="Enter Merchant Code">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="accountId" class="form-label">Bank Account</label>
                    <select class="form-select" id="accountId" name="account_id">
                      <option value="">Select Bank Account</option>
                      <!-- Options will be populated by AJAX -->
                    </select>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="clientId" class="form-label">Client ID</label>
                    <textarea class="form-control" id="clientId" name="client_id" rows="2" placeholder="Enter Client ID"></textarea>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="clientSecret" class="form-label">Client Secret</label>
                    <textarea class="form-control" id="clientSecret" name="client_secret" rows="2" placeholder="Enter Client Secret"></textarea>
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <button type="submit" class="btn btn-primary">Register Wallet</button>
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

      // Load bank accounts into select2
      $.ajax({
        type: 'GET',
        url: '../api/data/head_parish_bank_accounts?limit=all', 
        dataType: 'json',
        success: function (response) {
          var options = '<option value="">Select Bank Account</option>';
          $.each(response.data, function (index, account) {
            options += '<option value="' + account.account_id + '">' + account.account_name + ' (' + account.account_number + ')</option>';
          });
          $('#accountId').html(options);
        },
        error: function (xhr, status, error) {
          console.log('Error loading bank accounts:', error);
        }
      });

      // Handle form submission
      $('#paymentGatewayWalletForm').on('submit', function (event) {
        event.preventDefault(); // Prevent the default form submission

        var walletData = {
          merchant_code: $('#merchantCode').val(),
          account_id: $('#accountId').val(),
          reference_id: $('#referenceId').val(),
          client_id: $('#clientId').val(),
          client_secret: $('#clientSecret').val(),
          target: 'head_parish'
        };

        $.ajax({
          type: 'POST',
          url: '../api/registration/register_payment_gateway_wallet',
          data: walletData,
          dataType: 'json',
          success: function (response) {
            var messageHtml;
            if (response.success) {
              messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i>' + response.message + '</div>';
              setTimeout(function () {
                window.location.href = './payment-gateway-wallets';
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
