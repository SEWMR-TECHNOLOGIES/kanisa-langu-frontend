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
    render_header('Add New Harambee - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Add New Harambee</h5>

            <ul class="nav nav-tabs" id="harambeeTabs" role="tablist">
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
              <!-- Head Parish Harambee Form -->
              <div class="tab-pane fade show active" id="head-parish" role="tabpanel">
                <form id="headParishForm" autocomplete="off">
                  <input type="hidden" name="target" value="head_parish">
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="nameHP" class="form-label">Name</label>
                        <input type="text" class="form-control" id="nameHP" name="name" placeholder="Enter harambee name"></textarea>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="descriptionHP" class="form-label">Description</label>
                        <textarea class="form-control" id="descriptionHP" name="description" rows="1" placeholder="Enter description..."></textarea>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="accountIdHP" class="form-label">Bank Account</label>
                        <select class="form-select" id="accountIdHP" name="account_id">
                          <option value="">Select Bank Account</option>
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
                        <label for="fromDateHP" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="fromDateHP" name="from_date">
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="toDateHP" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="toDateHP" name="to_date">
                      </div>
                    </div>
                  </div>
                  <button type="submit" class="btn btn-primary">Submit Harambee</button>
                </form>
              </div>

              <!-- Sub Parish Harambee Form -->
              <div class="tab-pane fade" id="sub-parish" role="tabpanel">
                <form id="subParishForm" autocomplete="off">
                  <input type="hidden" name="target" value="sub_parish">
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="nameSP" class="form-label">Name</label>
                        <input type="text" class="form-control" id="nameSP" name="name" placeholder="Enter harambee name"></textarea>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="descriptionSP" class="form-label">Description</label>
                        <textarea class="form-control" id="descriptionSP" name="description" rows="1" placeholder="Enter description..."></textarea>
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
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="accountIdSP" class="form-label">Bank Account</label>
                        <select class="form-select" id="accountIdSP" name="account_id">
                          <option value="">Select Bank Account</option>
                          <!-- Options populated by AJAX -->
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="amountSP" class="form-label">Amount</label>
                        <input type="number" class="form-control" id="amountSP" name="amount" placeholder="Amount">
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="fromDateSP" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="fromDateSP" name="from_date">
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="toDateSP" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="toDateSP" name="to_date">
                      </div>
                    </div>
                  </div>
                  <button type="submit" class="btn btn-primary">Submit Harambee</button>
                </form>
              </div>

              <!-- Community Harambee Form -->
              <div class="tab-pane fade" id="community" role="tabpanel">
                <form id="communityForm" autocomplete="off">
                  <input type="hidden" name="target" value="community">
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="nameComm" class="form-label">Name</label>
                        <input type="text" class="form-control" id="nameComm" name="name" placeholder="Enter harambee name"></textarea>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="descriptionComm" class="form-label">Description</label>
                        <textarea class="form-control" id="descriptionComm" name="description" rows="1" placeholder="Enter description..."></textarea>
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
                        <label for="accountIdCOM" class="form-label">Bank Account</label>
                        <select class="form-select" id="accountIdCOM" name="account_id">
                          <option value="">Select Bank Account</option>
                          <!-- Options populated by AJAX -->
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="amountComm" class="form-label">Amount</label>
                        <input type="number" class="form-control" id="amountComm" name="amount" placeholder="Amount">
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="fromDateCom" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="fromDateCom" name="from_date">
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="toDateCom" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="toDateCom" name="to_date">
                      </div>
                    </div>
                  </div>
                  <button type="submit" class="btn btn-primary">Submit Harambee</button>
                </form>
              </div>

              <!-- Group Harambee Form -->
              <div class="tab-pane fade" id="group" role="tabpanel">
                <form id="groupForm" autocomplete="off">
                  <input type="hidden" name="target" value="group">
                  <div class="row">
                    <div class="col-lg-6">
                        <div class="mb-3">
                            <label for="nameGroup" class="form-label">Name</label>
                            <input type="text" class="form-control" id="nameGroup" name="name" placeholder="Enter harambee name"></textarea>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="descriptionGroup" class="form-label">Description</label>
                        <textarea class="form-control" id="descriptionGroup" name="description" rows="1" placeholder="Enter description..."></textarea>
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
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="accountIdGroup" class="form-label">Bank Account</label>
                        <select class="form-select" id="accountIdGroup" name="account_id">
                          <option value="">Select Bank Account</option>
                          <!-- Options populated by AJAX -->
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="amountGroup" class="form-label">Amount</label>
                        <input type="number" class="form-control" id="amountGroup" name="amount" placeholder="Amount">
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="fromDateGroup" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="fromDateGroup" name="from_date">
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="toDateGroup" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="toDateGroup" name="to_date">
                      </div>
                    </div>
                  </div>
                  <button type="submit" class="btn btn-primary">Submit Harambee</button>
                </form>
              </div>
            </div>
            <div id="responseMessage"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

 
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
        url: '../api/records/record_harambee',
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
      submitForm('#headParishForm', 'head_parish');
    });

    $('#subParishForm').on('submit', function (event) {
      event.preventDefault();
      submitForm('#subParishForm', 'sub_parish');
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
          $('#subParishIdCom').html(options); 
        },
        error: function (xhr, status, error) {
          console.log('Error loading sub-parishes:', error);
        }
      });
    }

  // Load bank accounts
  loadBankAccounts();

  // Function to load bank accounts
  function loadBankAccounts() {
    $.ajax({
      type: 'GET',
      url: '../api/data/head_parish_bank_accounts?limit=all',
      dataType: 'json',
      success: function (response) {
        var options = '<option value="">Select Bank Account</option>';
        $.each(response.data, function (index, account) {
          options += '<option value="' + account.account_id + '">' + account.account_name + ' (' + account.account_number + ')</option>';
        });
        // Populate all relevant select elements
        $('#accountIdHP, #accountIdSP, #accountIdCOM, #accountIdGroup').html(options);
      },
      error: function (xhr, status, error) {
        console.log('Error loading bank accounts:', error);
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
