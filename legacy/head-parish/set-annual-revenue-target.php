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
    render_header('Set Annual Revenue Targets - Kanisa Langu');
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
              <h5 class="card-title fw-semibold mb-4">Set Annual Revenue Targets</h5>

              <ul class="nav nav-tabs" id="expenseGroupsTabs" role="tablist">
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
                          <label for="revenueTargetHP" class="form-label">Revenue Target Amount</label>
                          <input type="number" class="form-control" id="revenueTargetHP" name="revenue_target_amount" placeholder="Revenue Target" min="0">
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
                    <button type="submit" class="btn btn-primary">Save Revenue Target</button>
                  </form>
                </div>

                <!-- Sub Parish Expense Group Form -->
                <div class="tab-pane fade" id="sub-parish" role="tabpanel">
                  <form id="subParishForm" autocomplete="off">
                    <input type="hidden" name="target" value="sub_parish">
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="accountIdSP" class="form-label">Target Bank Account</label>
                          <select class="form-select" id="accountIdSP" name="account_id">
                            <option value="">Select Bank Account</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="revenueTargetSP" class="form-label">Revenue Target Amount</label>
                          <input type="number" class="form-control" id="revenueTargetSP" name="revenue_target_amount" placeholder="Revenue Target" min="0">
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="startDateSP" class="form-label">Start Date</label>
                          <?php $startDate = date('Y') . '-01-01'; ?>
                          <input type="date" class="form-control" id="startDateSP" name="start_date" value="<?php echo $startDate; ?>" readonly>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="endDateSP" class="form-label">End Date</label>
                          <?php $endDate = date('Y') . '-12-31'; ?>
                          <input type="date" class="form-control" id="endDateSP" name="end_date" value="<?php echo $endDate; ?>" readonly>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="subParishId" class="form-label">Sub Parish</label>
                          <select class="form-select" id="subParishId" name="sub_parish_id">
                            <option value="">Select Sub Parish</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Revenue Target</button>
                  </form>
                </div>

                <!-- Community Expense Group Form -->
                <div class="tab-pane fade" id="community" role="tabpanel">
                  <form id="communityForm" autocomplete="off">
                    <input type="hidden" name="target" value="community">
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="revenueTargetCOM" class="form-label">Revenue Target Amount</label>
                          <input type="number" class="form-control" id="revenueTargetCOM" name="revenue_target_amount" placeholder="Revenue Target" min="0">
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="accountIdCOM" class="form-label">Target Bank Account</label>
                          <select class="form-select" id="accountIdCOM" name="account_id">
                            <option value="">Select Bank Account</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="subParishIdCom" class="form-label">Sub Parish</label>
                          <select class="form-select" id="subParishIdCom" name="sub_parish_id">
                            <option value="">Select Sub Parish</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="communityId" class="form-label">Community</label>
                          <select class="form-select" id="communityId" name="community_id">
                            <option value="">Select Community</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="startDateCom" class="form-label">Start Date</label>
                          <?php $startDate = date('Y') . '-01-01'; ?>
                          <input type="date" class="form-control" id="startDateCom" name="start_date" value="<?php echo $startDate; ?>" readonly>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="endDateCom" class="form-label">End Date</label>
                          <?php $endDate = date('Y') . '-12-31'; ?>
                          <input type="date" class="form-control" id="endDateCom" name="end_date" value="<?php echo $endDate; ?>" readonly>
                        </div>
                      </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Revenue Target</button>
                  </form>
                </div>

                <!-- Group Expense Group Form -->
                <div class="tab-pane fade" id="group" role="tabpanel">
                  <form id="groupForm" autocomplete="off">
                    <input type="hidden" name="target" value="group">
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="revenueTargetGP" class="form-label">Revenue Target Amount</label>
                          <input type="number" class="form-control" id="revenueTargetGP" name="revenue_target_amount" placeholder="Revenue Target" min="0">
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="accountIdGP" class="form-label">Target Bank Account</label>
                          <select class="form-select" id="accountIdGP" name="account_id">
                            <option value="">Select Bank Account</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="startDateGP" class="form-label">Start Date</label>
                          <?php $startDate = date('Y') . '-01-01'; ?>
                          <input type="date" class="form-control" id="startDateGP" name="start_date" value="<?php echo $startDate; ?>" readonly>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="endDateGP" class="form-label">End Date</label>
                          <?php $endDate = date('Y') . '-12-31'; ?>
                          <input type="date" class="form-control" id="endDateGP" name="end_date" value="<?php echo $endDate; ?>" readonly>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="groupId" class="form-label">Group</label>
                          <select class="form-select" id="groupId" name="group_id">
                            <option value="">Select Group</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Revenue Target</button>
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

      $.ajax({
        type: 'POST',
        url: '../api/records/set_annual_revenue_target',
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
          $('#subParishIdHP').html(options); 
          $('#subParishIdCom').html(options); 
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
    
    // Load bank accounts for all targets
    loadBankAccounts(targetIds, '../api/data/head_parish_bank_accounts?limit=all');

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
        url: '../api/data/head_parish_groups?limit=all', 
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

    loadSubParishes();
    loadGroups();

    // Load revenue streams for sub-parish when a sub-parish is selected
    $('#subParishIdCom').on('change', function () {
      loadCommunities($(this).val()); 
    });
  });
</script>

</body>

</html>
