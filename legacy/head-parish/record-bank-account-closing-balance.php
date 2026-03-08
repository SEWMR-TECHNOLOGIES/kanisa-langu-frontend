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
    render_header('Record Closing Balances - Kanisa Langu');
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
              <h5 class="card-title fw-semibold mb-4">Record Bank Account Closing Balances</h5>
            
            <ul class="nav nav-tabs" id="revenueTabs" role="tablist">
              <li class="nav-item" role="presentation">
                <a class="nav-link active" id="head-parish-tab" data-bs-toggle="tab" href="#head-parish" role="tab">Head Parish</a>
              </li>
              <li class="nav-item" role="presentation">
                <a class="nav-link" id="sub-parish-tab" data-bs-toggle="tab" href="#sub-parish" role="tab">Sub Parish</a>
              </li>
              <li class="nav-item" role="presentation">
                <a class="nav-link" id="community-tab" data-bs-toggle="tab" href="#community" role="tab">Community</a>
              </li>
              <li class="nav-item" role="presentation">
                <a class="nav-link" id="group-tab" data-bs-toggle="tab" href="#group" role="tab">Group</a>
              </li>
            </ul>

            <div class="tab-content mt-3" id="revenueTabContent">
              <!-- Head Parish Form -->
              <div class="tab-pane fade show active" id="head-parish" role="tabpanel">
                <form id="headParishForm">
                    <div class="row">
                      <div class="col-lg-4">
                        <div class="mb-3">
                          <label for="accountId" class="form-label">Bank Account</label>
                          <select class="form-select" id="accountId" name="account_id">
                            <option value="">Select Bank Account</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                      <div class="col-lg-4">
                        <div class="mb-3">
                          <label for="closingBalance" class="form-label">Closing Balance</label>
                          <input type="number" class="form-control" id="closingBalance" name="closing_balance" placeholder="Enter Closing Balance" min="0.00" step="0.01">
                        </div>
                      </div>
                      <div class="col-lg-4">
                        <div class="mb-3">
                          <label for="closingDate" class="form-label">Closing Date</label>
                          <input type="date" class="form-control" id="closingDate" name="closing_balance_date" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                      </div>
                    </div>
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary">Record Closing Balance</button>
                    </div>
                </form>
              </div>

              <!-- Sub Parish Form -->
              <div class="tab-pane fade" id="sub-parish" role="tabpanel">
                <form id="subParishForm">
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="subParishId" class="form-label">Sub Parish</label>
                        <select class="form-select" id="subParishId" name="sub_parish_id">
                          <option value="">Select Sub Parish</option>
                        </select>
                      </div>
                    </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="accountIdSub" class="form-label">Bank Account</label>
                          <select class="form-select" id="accountIdSub" name="account_id">
                            <option value="">Select Bank Account</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                  </div>

                  <div class="row">
                    <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="closingBalanceSub" class="form-label">Closing Balance</label>
                          <input type="number" class="form-control" id="closingBalanceSub" name="closing_balance" placeholder="Enter Closing Balance" min="0.00" step="0.01">
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="closingDateSub" class="form-label">Closing Date</label>
                          <input type="date" class="form-control" id="closingDateSub" name="closing_balance_date" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                      </div>
                  </div>
                  
                  <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Record Closing Balance</button>
                  </div>
                </form>
              </div>

              <!-- Community Revenue Form -->
              <div class="tab-pane fade" id="community" role="tabpanel">
                <form id="communityForm">
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="subParishIdCom" class="form-label">Sub Parish</label>
                        <select class="form-select" id="subParishIdCom" name="sub_parish_id">
                          <option value="">Select Sub Parish</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="communityId" class="form-label">Community</label>
                        <select class="form-select" id="communityId" name="community_id">
                          <option value="">Select Community</option>
                        </select>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                      <div class="col-lg-4">
                        <div class="mb-3">
                          <label for="accountIdCom" class="form-label">Bank Account</label>
                          <select class="form-select" id="accountIdCom" name="account_id">
                            <option value="">Select Bank Account</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                      <div class="col-lg-4">
                        <div class="mb-3">
                          <label for="closingBalanceCom" class="form-label">Closing Balance</label>
                          <input type="number" class="form-control" id="closingBalanceCom" name="closing_balance" placeholder="Enter Closing Balance" min="0.00" step="0.01">
                        </div>
                      </div>
                      <div class="col-lg-4">
                        <div class="mb-3">
                          <label for="closingDateCom" class="form-label">Closing Date</label>
                          <input type="date" class="form-control" id="closingDateCom" name="closing_balance_date" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                      </div>
                    </div>
                  
                  <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Record Closing Balance</button>
                  </div>
                </form>
              </div>

              <!-- Group Revenue Form -->
              <div class="tab-pane fade" id="group" role="tabpanel">
                <form id="groupForm">
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="groupId" class="form-label">Group</label>
                        <select class="form-select" id="groupId" name="group_id">
                          <option value="">Select Group</option>
                        </select>
                      </div>
                    </div>
                     <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="accountIdGp" class="form-label">Bank Account</label>
                          <select class="form-select" id="accountIdGp" name="account_id">
                            <option value="">Select Bank Account</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                  </div>

                   <div class="row">
                    <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="closingBalanceGp" class="form-label">Closing Balance</label>
                          <input type="number" class="form-control" id="closingBalanceGp" name="closing_balance" placeholder="Enter Closing Balance" min="0.00" step="0.01">
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="closingDateGp" class="form-label">Closing Date</label>
                          <input type="date" class="form-control" id="closingDateGp" name="closing_balance_date" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                      </div>
                  </div>
                  <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Record Revenue</button>
                  </div>
                </form>
              </div>
              <div id="responseMessage"></div>
            </div>
          </div>
        </div>
      </div>
  </div>

  <?php require_once('components/footer_files.php') ?>
  
<script>
  $(document).ready(function () {
     $('select').select2({
    width: '100%'
  });
  
  });

  $(document).ready(function () {
    // Function to submit the form dynamically
    function submitForm(formId, target) {
      const formData = $(formId).serialize() + '&target=' + target;
      $('#responseMessage').html('<div class="response-message"><i class="fas fa-spinner fa-spin icon"></i> Recording Closing Balance...</div>');
      $.ajax({
        type: 'POST',
        url: '../api/records/record_bank_account_closing_balance',
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

    $('#subParishForm').on('submit', function (event) {
      event.preventDefault();
      submitForm('#subParishForm', 'sub-parish');
    });

    $('#communityForm').on('submit', function (event) {
      event.preventDefault();
      submitForm('#communityForm', 'community');
    });

    $('#groupForm').on('submit', function (event) {
      event.preventDefault();
      submitForm('#groupForm', 'group');
    });

    function loadBankAccounts() {
      $.ajax({
        type: 'GET',
        url: '../api/data/head_parish_bank_accounts?limit=all',
        dataType: 'json',
        success: function (response) {
          let options = '<option value="">Select Bank Account</option>';
          $.each(response.data, function (index, account) {
            options += '<option value="' + account.account_id + '">' + account.account_name + '</option>';
          });
          $('#accountId').html(options);
          $('#accountIdSub').html(options);
          $('#accountIdCom').html(options);
          $('#accountIdGp').html(options);
        },
        error: function () {
          console.log('Error loading accounts.');
        }
      });
    }
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
          $('#subParishId').html(options);
          $('#subParishIdCom').html(options); 
        },
        error: function (xhr, status, error) {
          console.log('Error loading sub-parishes:', error);
        }
      });
    }
    
     // Load communities based on selected sub-parish
    function loadCommunities(subParishId) {
      $.ajax({
        type: 'GET',
        url: '../api/data/head_parish_communities?limit=all', 
        data: { sub_parish_id: subParishId },
        dataType: 'json',
        success: function (response) {
          let options = '<option value="">Select Community</option>';
          $.each(response.data, function (index, community) {
            options += '<option value="' + community.community_id + '">' + community.community_name + '</option>';
          });
          $('#communityId').html(options);
        },
        error: function (xhr, status, error) {
          console.log('Error loading communities:', error);
        }
      });
    }

    // Load groups
    function loadGroups() {
      $.ajax({
        type: 'GET',
        url: '../api/data/head_parish_groups?limit=all', // Adjust with your API URL for groups
        dataType: 'json',
        success: function (response) {
          let options = '<option value="">Select Group</option>';
          $.each(response.data, function (index, group) {
            options += '<option value="' + group.group_id + '">' + group.group_name + '</option>';
          });
          $('#groupId').html(options);
        },
        error: function (xhr, status, error) {
          console.log('Error loading groups:', error);
        }
      });
    }
    
    // Load initial data
    loadSubParishes();
    loadGroups();
    loadBankAccounts();
    
    $('#subParishIdCom').on('change', function () {
      loadCommunities($(this).val()); 
    });
  });
</script>

</body>
</html>
