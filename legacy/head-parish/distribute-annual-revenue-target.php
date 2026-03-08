<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');
check_session('head_parish_admin_id', '../head-parish/sign-in');
?>
<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Distribute Annual Revenue Targets - Kanisa Langu');
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
              <h5 class="card-title fw-semibold mb-4">Distribute Annual Revenue Targets</h5>

              <ul class="nav nav-tabs" id="expenseGroupsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                  <a class="nav-link active" id="head-parish-tab" data-bs-toggle="tab" href="#head-parish" role="tab">Head Parish</a>
                </li>
              </ul>

              <div class="tab-content mt-3" id="harambeeTabContent">
                <!-- Head Parish Expense Group Form -->
                <div class="tab-pane fade show active" id="head-parish" role="tabpanel">
                  <form id="headParishForm" autocomplete="off">
                    <input type="hidden" name="target" value="head_parish">
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="accountIdHP" class="form-label">Target Bank Account</label>
                          <select class="form-select" id="accountIdHP" name="account_id">
                            <option value="">Select Bank Account</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="revenueTargetHP" class="form-label">Annual Revenue Target</label>
                          <input type="number" class="form-control" id="revenueTargetHP" name="revenue_target_amount" placeholder="Revenue Target" min="0" readonly>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="startDateHP" class="form-label">Start Date</label>
                          <?php $startDate = date('Y') . '-01-01'; ?>
                          <input type="date" class="form-control" id="startDateHP" name="start_date" value="<?php echo $startDate; ?>" readonly>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="endDateHP" class="form-label">End Date</label>
                          <?php $endDate = date('Y') . '-12-31'; ?>
                          <input type="date" class="form-control" id="endDateHP" name="end_date" value="<?php echo $endDate; ?>" readonly>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="subParishIdHP" class="form-label">Sub Parish</label>
                          <select class="form-select" id="subParishIdHP" name="sub_parish_id">
                            <option value="">Select Sub Parish</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="amountHP" class="form-label">Amount</label>
                          <input type="number" class="form-control" id="amountHP" name="amount" placeholder="Amount">
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="percentageHP" class="form-label">Set By Percentage (Optional)</label>
                          <input type="number" class="form-control" id="percentageHP" name="percentage" placeholder="Percentage (0 - 100)" min="1" max="100">
                        </div>
                      </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Distribute Revenue Target</button>
                  </form>
                </div>
              </div>
              <div id="responseMessage"></div>
            </div>
          </div>
        </div>
      </div>
  </div>
</body>



 
 <?php require_once('components/footer_files.php') ?>
  
<script>
  $('select').select2({
    width: '100%'
  });

  $(document).ready(function () {
    // Function to submit the form dynamically
    function submitForm(formId, target) {
      const formData = $(formId).serialize() + '&target=' + target;
        $('#responseMessage').html('<div class="response-message"><i class="fas fa-spinner fa-spin icon"></i> Distributing Revenue...</div>');
      $.ajax({
        type: 'POST',
        url: '../api/records/distribute_annual_revenue_target',
        data: formData,
        dataType: 'json',
        success: function (response) {
          let messageHtml = '';
          if (response.success) {
            messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i>' + response.message + '</div>';
            setTimeout(function () {
              location.reload(); 
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
    }

    // Attach form submit handlers
    $('#headParishForm').on('submit', function (event) {
      event.preventDefault();
      submitForm('#headParishForm', 'head-parish');
    });


    // Load sub-parishes
    function loadSubParishes() {
      $.ajax({
        type: 'GET',
        url: '../api/data/head_parish_sub_parishes?limit=all', 
        dataType: 'json',
        success: function (response) {
          let options = '<option value="">Select Sub Parish</option>';
          $.each(response.data, function (index, subParish) {
            options += '<option value="' + subParish.sub_parish_id + '">' + subParish.sub_parish_name + '</option>';
          });
          $('#subParishIdHP').html(options); 
        },
        error: function (xhr, status, error) {
          console.log('Error loading sub-parishes:', error);
        }
      });
    }

    // Target IDs for loading bank accounts
    const targetIds = ['accountIdHP', 'accountIdSP', 'accountIdCOM', 'accountIdGP'];
    
    // Function to load bank accounts
    function loadBankAccounts(targetIds, url) {
      $.ajax({
        type: 'GET',
        url: url,
        dataType: 'json',
        success: function (response) {
          var options = '<option value="">Select Account</option>';
          $.each(response.data, function (index, account) {
            options += '<option value="' + account.account_id + '">' + account.account_name + '</option>';
          });
          
          // Update the dropdowns for all target IDs
          targetIds.forEach(targetId => {
            $('#' + targetId).html(options);
          });
        },
        error: function (xhr, status, error) {
          console.log('Error loading accounts:', error);
        }
      });
    }
    
// Function to load revenue target when bank account is selected
    $('#accountIdHP').on('change', function () {
        let accountId = $(this).val();
        let startDate = $('#startDateHP').val();
        let endDate = $('#endDateHP').val();

        if (accountId) {
            $.ajax({
                type: 'GET',
                url: `../api/data/get_revenue_target?account_id=${accountId}&start_date=${startDate}&end_date=${endDate}`,
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        $('#revenueTargetHP').val(response.data.revenue_target);
                    } else {
                        $('#revenueTargetHP').val('');
                        alert('Revenue target not found.');
                    }
                },
                error: function () {
                    $('#revenueTargetHP').val('');
                    alert('Error fetching revenue target.');
                }
            });
        } else {
            $('#revenueTargetHP').val('');
        }
    });
    // Load bank accounts for all targets
    loadBankAccounts(targetIds, '../api/data/head_parish_bank_accounts?limit=all');

    loadSubParishes();

  });
</script>

</body>

</html>
