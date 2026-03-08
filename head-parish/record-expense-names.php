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
    render_header('Record Expense Names - Kanisa Langu');
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
              <h5 class="card-title fw-semibold mb-4">Create Expense Names</h5>

              <ul class="nav nav-tabs" id="expenseNamesTabs" role="tablist">
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

              <div class="tab-content mt-3" id="expenseNamesTabContent">
                <!-- Head Parish Expense Name Form -->
                <div class="tab-pane fade show active" id="head-parish" role="tabpanel">
                  <form id="headParishForm" autocomplete="off">
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="expenseGroupHeadParish" class="form-label">Expense Group</label>
                          <select class="form-select" id="expenseGroupHeadParish" name="expense_group_id">
                            <option value="">Select Expense Group</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="expenseNameHeadParish" class="form-label">Expense Name</label>
                          <input type="text" class="form-control" id="expenseNameHeadParish" name="expense_name" placeholder="Expense Name">
                        </div>
                      </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                  </form>
                </div>

                <!-- Sub Parish Expense Name Form -->
                <div class="tab-pane fade" id="sub-parish" role="tabpanel">
                  <form id="subParishForm" autocomplete="off">
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="expenseGroupSubParish" class="form-label">Expense Group</label>
                          <select class="form-select" id="expenseGroupSubParish" name="expense_group_id">
                            <option value="">Select Expense Group</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="expenseNameSubParish" class="form-label">Expense Name</label>
                          <input type="text" class="form-control" id="expenseNameSubParish" name="expense_name" placeholder="Expense Name">
                        </div>
                      </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                  </form>
                </div>

                <!-- Community Expense Name Form -->
                <div class="tab-pane fade" id="community" role="tabpanel">
                  <form id="communityForm" autocomplete="off">
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="expenseGroupCommunity" class="form-label">Expense Group</label>
                          <select class="form-select" id="expenseGroupCommunity" name="expense_group_id">
                            <option value="">Select Expense Group</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="expenseNameCommunity" class="form-label">Expense Name</label>
                          <input type="text" class="form-control" id="expenseNameCommunity" name="expense_name" placeholder="Expense Name">
                        </div>
                      </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                  </form>
                </div>

                <!-- Group Expense Name Form -->
                <div class="tab-pane fade" id="group" role="tabpanel">
                  <form id="groupForm" autocomplete="off">
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="expenseGroupGroup" class="form-label">Expense Group</label>
                          <select class="form-select" id="expenseGroupGroup" name="expense_group_id">
                            <option value="">Select Expense Group</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="expenseNameGroup" class="form-label">Expense Name</label>
                          <input type="text" class="form-control" id="expenseNameGroup" name="expense_name" placeholder="Expense Name">
                        </div>
                      </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
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
        url: '../api/records/record_expense_name',
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

    // Function to load expense groups with a target parameter
    function loadExpenseGroups(target) {
        $.ajax({
            type: 'GET',
            url: '../api/data/expense_groups?limit=all&target=' + target,
            dataType: 'json',
            success: function (response) {
                let options = '<option value="">Select Expense Group</option>';
                $.each(response.data, function (index, group) {
                    // Append the additional name based on the target
                    let displayName = group.expense_group_name;
                    if (target === 'sub-parish') {
                        displayName += ' - ' + group.sub_parish_name; // Add sub_parish_name
                    } else if (target === 'community') {
                        displayName += ' - ' + group.community_name; // Add community_name
                    } else if (target === 'group') {
                        displayName += ' - ' + group.group_name; // Add group_name
                    }
    
                    options += '<option value="' + group.expense_group_id + '">' + displayName + '</option>';
                });
    
                // Populate the select elements for each form
                if (target === 'head-parish') {
                    $('#expenseGroupHeadParish').html(options);
                } else if (target === 'sub-parish') {
                    $('#expenseGroupSubParish').html(options);
                } else if (target === 'community') {
                    $('#expenseGroupCommunity').html(options);
                } else if (target === 'group') {
                    $('#expenseGroupGroup').html(options);
                }
            },
            error: function (xhr, status, error) {
                console.log('Error loading expense groups for ' + target + ':', error);
            }
        });
    }


     // Load expense groups for each category
    loadExpenseGroups('head-parish');
    loadExpenseGroups('sub-parish');
    loadExpenseGroups('community');
    loadExpenseGroups('group');
  });
</script>
</html>
