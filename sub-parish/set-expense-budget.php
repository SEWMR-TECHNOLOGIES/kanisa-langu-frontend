<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Set Expense Budgets - Kanisa Langu');
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
              <h5 class="card-title fw-semibold mb-4">Create Expense Budgets</h5>

              <ul class="nav nav-tabs" id="expenseBudgetsTabs" role="tablist">
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

              <div class="tab-content mt-3" id="expenseBudgetTabContent">
                <!-- Head Parish Expense Budget Form -->
                <div class="tab-pane fade show active" id="head-parish" role="tabpanel">
                  <form id="headParishForm" autocomplete="off">
                    <input type="hidden" name="target" value="head_parish">
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="expenseGroupHP" class="form-label">Expense Group</label>
                          <select class="form-select" id="expenseGroupHP" name="expense_group_id">
                            <option value="">Select Expense Group</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="expenseNameHP" class="form-label">Expense Name</label>
                          <select class="form-select" id="expenseNameHP" name="expense_name_id">
                            <option value="">Select Expense Name</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="budgetedAmountHP" class="form-label">Budgeted Amount</label>
                          <input type="number" class="form-control" id="budgetedAmountHP" name="budgeted_amount" placeholder="Budgeted Amount" min="0.01" step="0.01">
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="startDateHP" class="form-label">Start Date</label>
                          <?php $startDate = date('Y') . '-01-01'; ?>
                          <input type="date" class="form-control" id="startDateHP" name="start_date" value="<?php echo $startDate; ?>" readonly>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="endDateHP" class="form-label">End Date</label>
                          <?php $endDate = date('Y') . '-12-31'; ?>
                          <input type="date" class="form-control" id="endDateHP" name="end_date" value="<?php echo $endDate; ?>" readonly>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="budgetDescriptionHP" class="form-label">Description</label>
                          <input type="text" class="form-control" id="budgetDescriptionHP" name="budget_description" placeholder="Budget Description">
                        </div>
                      </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                  </form>
                </div>

                <!-- Sub Parish Expense Budget Form -->
                <div class="tab-pane fade" id="sub-parish" role="tabpanel">
                  <form id="subParishForm" autocomplete="off">
                    <input type="hidden" name="target" value="sub_parish">
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
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="expenseGroupSP" class="form-label">Expense Group</label>
                          <select class="form-select" id="expenseGroupSP" name="expense_group_id">
                            <option value="">Select Expense Group</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="expenseNameSP" class="form-label">Expense Name</label>
                          <select class="form-select" id="expenseNameSP" name="expense_name_id">
                            <option value="">Select Expense Name</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="budgetedAmountSP" class="form-label">Budgeted Amount</label>
                          <input type="number" class="form-control" id="budgetedAmountSP" name="budgeted_amount" placeholder="Budgeted Amount" min="0.01" step="0.01">
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="startDateSP" class="form-label">Start Date</label>
                          <input type="date" class="form-control" id="startDateSP" name="start_date" value="<?php echo $startDate; ?>" readonly>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="endDateSP" class="form-label">End Date</label>
                          <input type="date" class="form-control" id="endDateSP" name="end_date" value="<?php echo $endDate; ?>" readonly>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="budgetDescriptionSP" class="form-label">Description</label>
                          <input type="text" class="form-control" id="budgetDescriptionSP" name="budget_description" placeholder="Budget Description">
                        </div>
                      </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                  </form>
                </div>

                <!-- Community Expense Budget Form -->
                <div class="tab-pane fade" id="community" role="tabpanel">
                  <form id="communityForm" autocomplete="off">
                    <input type="hidden" name="target" value="community">
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
                          <label for="expenseGroupCom" class="form-label">Expense Group</label>
                          <select class="form-select" id="expenseGroupCom" name="expense_group_id">
                            <option value="">Select Expense Group</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="expenseNameCom" class="form-label">Expense Name</label>
                          <select class="form-select" id="expenseNameCom" name="expense_name_id">
                            <option value="">Select Expense Name</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="budgetedAmountCom" class="form-label">Budgeted Amount</label>
                          <input type="number" class="form-control" id="budgetedAmountCom" name="budgeted_amount" placeholder="Budgeted Amount" min="0.01" step="0.01">
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="startDateCom" class="form-label">Start Date</label>
                          <input type="date" class="form-control" id="startDateCom" name="start_date" value="<?php echo $startDate; ?>" readonly>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="endDateCom" class="form-label">End Date</label>
                          <input type="date" class="form-control" id="endDateCom" name="end_date" value="<?php echo $endDate; ?>" readonly>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="budgetDescriptionCom" class="form-label">Description</label>
                          <input type="text" class="form-control" id="budgetDescriptionCom" name="budget_description" placeholder="Budget Description">
                        </div>
                      </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                  </form>
                </div>

                <!-- Group Expense Budget Form -->
                <div class="tab-pane fade" id="group" role="tabpanel">
                  <form id="groupForm" autocomplete="off">
                    <input type="hidden" name="target" value="group">
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
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="expenseGroupG" class="form-label">Expense Group</label>
                          <select class="form-select" id="expenseGroupG" name="expense_group_id">
                            <option value="">Select Expense Group</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="expenseNameG" class="form-label">Expense Name</label>
                          <select class="form-select" id="expenseNameG" name="expense_name_id">
                            <option value="">Select Expense Name</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="budgetedAmountG" class="form-label">Budgeted Amount</label>
                          <input type="number" class="form-control" id="budgetedAmountG" name="budgeted_amount" placeholder="Budgeted Amount" min="0.01" step="0.01">
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="startDateG" class="form-label">Start Date</label>
                          <input type="date" class="form-control" id="startDateG" name="start_date" value="<?php echo $startDate; ?>" readonly>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="endDateG" class="form-label">End Date</label>
                          <input type="date" class="form-control" id="endDateG" name="end_date" value="<?php echo $endDate; ?>" readonly>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="budgetDescriptionG" class="form-label">Description</label>
                          <input type="text" class="form-control" id="budgetDescriptionG" name="budget_description" placeholder="Budget Description">
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

 <?php require_once('components/footer_files.php') ?>

</body>
<script>
  $(document).ready(function () {
    // Initialize Select2 for all select elements
    $('select').select2({
      width: '100%'
    });

    // Function to submit the form dynamically
    function submitForm(formId, target) {
      const formData = $(formId).serialize() + '&target=' + target;

      $.ajax({
        type: 'POST',
        url: '../api/records/set_expense_budget',
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
          $('#subParishId, #subParishIdHP, #subParishIdCom').html(options); // Populate all relevant selects
        },
        error: function (xhr, status, error) {
          console.log('Error loading sub-parishes:', error);
        }
      });
    }

    // Load expense groups based on the form context
    function loadExpenseGroups(targetId, url, params = {}) {
      $.ajax({
        type: 'GET',
        url: url,
        data: params,
        dataType: 'json',
        success: function (response) {
          let options = '<option value="">Select Expense Group</option>';
          $.each(response.data, function (index, group) {
            options += '<option value="' + group.expense_group_id + '">' + group.expense_group_name + '</option>';
          });
          $(targetId).html(options);
        },
        error: function (xhr, status, error) {
          console.log('Error loading expense groups:', error);
        }
      });
    }

    // Load expense groups for Head Parish on page load
    loadExpenseGroups('#expenseGroupHP', '../api/data/expense_groups', { target: 'head-parish', limit: 'all' });

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

    // Event handlers for loading expense groups and communities
    $('#subParishId').on('change', function () {
      const subParishId = $(this).val();
      loadExpenseGroups('#expenseGroupSP', '../api/data/expense_groups', { target: 'sub-parish', sub_parish_id: subParishId, limit: 'all' });
    });

    $('#groupId').on('change', function () {
      const groupId = $(this).val();
      loadExpenseGroups('#expenseGroupG', '../api/data/expense_groups', { target: 'group', group_id: groupId, limit: 'all' });
    });

    $('#subParishIdCom').on('change', function () {
      loadCommunities($(this).val());
    });

    $('#subParishIdCom, #communityId').on('change', function () {
      const subParishId = $('#subParishIdCom').val();
      const communityId = $('#communityId').val();

      // Check if both Sub Parish and Community are selected
      if (subParishId && communityId) {
        loadExpenseGroups('#expenseGroupCom', '../api/data/expense_groups', {
          target: 'community',
          sub_parish_id: subParishId,
          community_id: communityId,
          limit: 'all'
        });
      }
    });

    // Load expense names based on selected expense group
    function loadExpenseNames(expenseGroupId, target, elementId) {
      $.ajax({
        type: 'GET',
        url: '../api/data/expense_names',
        data: { target: target, expense_group_id: expenseGroupId },
        dataType: 'json',
        success: function (response) {
          let options = '<option value="">Select Expense Name</option>';
          $.each(response.data, function (index, expense) {
            options += '<option value="' + expense.expense_name_id + '">' + expense.expense_name + '</option>';
          });
          $(elementId).html(options);  // Populate the relevant dropdown
        },
        error: function (xhr, status, error) {
          console.log('Error loading expense names:', error);
        }
      });
    }

    // Attach change event listeners to each expense group dropdown
    $('#expenseGroupHP').on('change', function () {
      const expenseGroupId = $(this).val();
      if (expenseGroupId) {
        loadExpenseNames(expenseGroupId, 'head-parish', '#expenseNameHP');
      }
    });

    $('#expenseGroupSP').on('change', function () {
      const expenseGroupId = $(this).val();
      if (expenseGroupId) {
        loadExpenseNames(expenseGroupId, 'sub-parish', '#expenseNameSP');
      }
    });

    $('#expenseGroupCom').on('change', function () {
      const expenseGroupId = $(this).val();
      if (expenseGroupId) {
        loadExpenseNames(expenseGroupId, 'community', '#expenseNameCom');
      }
    });

    $('#expenseGroupG').on('change', function () {
      const expenseGroupId = $(this).val();
      if (expenseGroupId) {
        loadExpenseNames(expenseGroupId, 'group', '#expenseNameG');
      }
    });

    // Initial data loading
    loadSubParishes();
    loadGroups();
  });
</script>
</html>
