<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');
check_session('head_parish_admin_id', '../head-parish/sign-in');

// defaults for date inputs
$firstDay = date('Y-m-01');
$lastDay = date('Y-m-t');
?>
<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Expense Group Report - Kanisa Langu');
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
              <h5 class="card-title fw-semibold mb-4">Expense Group Report</h5>

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
                <!-- Head Parish Expense Form -->
                <div class="tab-pane fade show active" id="head-parish" role="tabpanel">
                  <form id="headParishForm" autocomplete="off">
                    <input type="hidden" name="target" value="head-parish">
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="expenseGroupHP" class="form-label">Expense Group <span class="text-danger">*</span></label>
                          <select class="form-select" id="expenseGroupHP" name="expense_group_id" required>
                            <option value="">Select Expense Group</option>
                          </select>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="expenseNameHP" class="form-label">Expense Name</label>
                          <select class="form-select" id="expenseNameHP" name="expense_name_id">
                            <option value="">Select Expense Name</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                            <label for="startDateHP" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="startDateHP" name="start_date" value="<?php echo $firstDay; ?>" required>
                          </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                            <label for="endDateHP" class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="endDateHP" name="end_date" value="<?php echo $lastDay; ?>" max="<?php echo date('Y-m-d'); ?>" required>
                          </div>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="col-lg-12">
                        <!-- Head Parish -->
                        <button type="button" class="btn btn-primary mx-2" onclick="downloadExpenseGroupReport('headParishForm','#responseMessage')">Download Expense Group Report</button>
                        <button type="button" class="btn btn-success mx-2" onclick="downloadExpenseNameReport('headParishForm','#responseMessage')">Download Expense Name Report</button>
                      </div>
                    </div>
                  </form>
                </div>

                <!-- Sub Parish Expense Form -->
                <div class="tab-pane fade" id="sub-parish" role="tabpanel">
                  <form id="subParishForm" autocomplete="off">
                    <input type="hidden" name="target" value="sub-parish">
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="subParishId" class="form-label">Sub Parish <span class="text-danger">*</span></label>
                          <select class="form-select" id="subParishId" name="sub_parish_id" required>
                            <option value="">Select Sub Parish</option>
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="expenseGroupSP" class="form-label">Expense Group <span class="text-danger">*</span></label>
                          <select class="form-select" id="expenseGroupSP" name="expense_group_id" required>
                            <option value="">Select Expense Group</option>
                          </select>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="expenseNameSP" class="form-label">Expense Name</label>
                          <select class="form-select" id="expenseNameSP" name="expense_name_id">
                            <option value="">Select Expense Name</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                            <label for="startDateSP" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="startDateSP" name="start_date" value="<?php echo $firstDay; ?>" required>
                          </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                            <label for="endDateSP" class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="endDateSP" name="end_date" value="<?php echo $lastDay; ?>" max="<?php echo date('Y-m-d'); ?>" required>
                          </div>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="col-lg-12">
                        <!-- Sub Parish -->
                        <button type="button" class="btn btn-primary mx-2" onclick="downloadExpenseGroupReport('subParishForm','#responseMessage')">Download Expense Group Report</button>
                        <button type="button" class="btn btn-success mx-2" onclick="downloadExpenseNameReport('subParishForm','#responseMessage')">Download Expense Name Report</button>
                      </div>
                    </div>
                  </form>
                </div>

                <!-- Community Expense Form -->
                <div class="tab-pane fade" id="community" role="tabpanel">
                  <form id="communityForm" autocomplete="off">
                    <input type="hidden" name="target" value="community">
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="subParishIdCom" class="form-label">Sub Parish <span class="text-danger">*</span></label>
                          <select class="form-select" id="subParishIdCom" name="sub_parish_id" required>
                            <option value="">Select Sub Parish</option>
                          </select>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="communityId" class="form-label">Community <span class="text-danger">*</span></label>
                          <select class="form-select" id="communityId" name="community_id" required>
                            <option value="">Select Community</option>
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="expenseGroupCom" class="form-label">Expense Group <span class="text-danger">*</span></label>
                          <select class="form-select" id="expenseGroupCom" name="expense_group_id" required>
                            <option value="">Select Expense Group</option>
                          </select>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="expenseNameCom" class="form-label">Expense Name</label>
                          <select class="form-select" id="expenseNameCom" name="expense_name_id">
                            <option value="">Select Expense Name</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                            <label for="startDateCom" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="startDateCom" name="start_date" value="<?php echo $firstDay; ?>" required>
                          </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                            <label for="endDateCom" class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="endDateCom" name="end_date" value="<?php echo $lastDay; ?>" max="<?php echo date('Y-m-d'); ?>" required>
                          </div>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="col-lg-12">
                        <!-- Community -->
                        <button type="button" class="btn btn-primary mx-2" onclick="downloadExpenseGroupReport('communityForm','#responseMessage')">Download Expense Group Report</button>
                        <button type="button" class="btn btn-success mx-2" onclick="downloadExpenseNameReport('communityForm','#responseMessage')">Download Expense Name Report</button>
                      </div>
                    </div>
                  </form>
                </div>

                <!-- Group Expense Form -->
                <div class="tab-pane fade" id="group" role="tabpanel">
                  <form id="groupForm" autocomplete="off">
                    <input type="hidden" name="target" value="group">
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="groupId" class="form-label">Group <span class="text-danger">*</span></label>
                          <select class="form-select" id="groupId" name="group_id" required>
                            <option value="">Select Group</option>
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="expenseGroupG" class="form-label">Expense Group <span class="text-danger">*</span></label>
                          <select class="form-select" id="expenseGroupG" name="expense_group_id" required>
                            <option value="">Select Expense Group</option>
                          </select>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="expenseNameG" class="form-label">Expense Name</label>
                          <select class="form-select" id="expenseNameG" name="expense_name_id">
                            <option value="">Select Expense Name</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                            <label for="startDateGP" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="startDateGP" name="start_date" value="<?php echo $firstDay; ?>" required>
                          </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                            <label for="endDateGP" class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="endDateGP" name="end_date" value="<?php echo $lastDay; ?>" max="<?php echo date('Y-m-d'); ?>" required>
                          </div>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="col-lg-12">
                        <!-- Group -->
                        <button type="button" class="btn btn-primary mx-2" onclick="downloadExpenseGroupReport('groupForm','#responseMessage')">Download Expense Group Report</button>
                        <button type="button" class="btn btn-success mx-2" onclick="downloadExpenseNameReport('groupForm','#responseMessage')">Download Expense Name Report</button>
                      </div>
                    </div>
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
  // Updated showMessage function to accept a target selector
  function showMessage(message, type, targetSelector) {
    const responseDiv = $(targetSelector);
    let messageHtml = `<div class="response-message ${type}">
        <i class="${type === 'success' ? 'fas fa-check-circle' : 'fas fa-times-circle'} icon"></i>
        ${message}
      </div>`;
    responseDiv.html(messageHtml);

    // Clear the message after 3 seconds
    setTimeout(() => {
      responseDiv.html('');
    }, 3000);
  }

  $(document).ready(function () {
    // Initialize Select2 for all select elements
    $('select').select2({ width: '100%' });

    // Load sub-parishes
    function loadSubParishes() {
      $.ajax({
        type: 'GET',
        url: '../api/data/head_parish_sub_parishes?limit=all',
        dataType: 'json',
        success: function (response) {
          let options = '<option value="">Select Sub Parish</option>';
          $.each(response.data, function (index, subParish) {
            options += `<option value="${subParish.sub_parish_id}">${subParish.sub_parish_name}</option>`;
          });
          $('#subParishId, #subParishIdHP, #subParishIdCom').html(options);
        },
        error: function (xhr, status, error) {
          console.log('Error loading sub-parishes:', error);
        }
      });
    }

    // Load expense groups
    function loadExpenseGroups(targetId, url, params = {}) {
      $.ajax({
        type: 'GET',
        url: url,
        data: params,
        dataType: 'json',
        success: function (response) {
          let options = '<option value="">Select Expense Group</option>';
          $.each(response.data, function (index, group) {
            options += `<option value="${group.expense_group_id}">${group.expense_group_name}</option>`;
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

    // Load communities
    function loadCommunities(subParishId) {
      $.ajax({
        type: 'GET',
        url: '../api/data/head_parish_communities?limit=all',
        data: { sub_parish_id: subParishId },
        dataType: 'json',
        success: function (response) {
          let options = '<option value="">Select Community</option>';
          $.each(response.data, function (index, community) {
            options += `<option value="${community.community_id}">${community.community_name}</option>`;
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
            options += `<option value="${group.group_id}">${group.group_name}</option>`;
          });
          $('#groupId').html(options);
        },
        error: function (xhr, status, error) {
          console.log('Error loading groups:', error);
        }
      });
    }

    // Event handlers
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

      if (subParishId && communityId) {
        loadExpenseGroups('#expenseGroupCom', '../api/data/expense_groups', {
          target: 'community',
          sub_parish_id: subParishId,
          community_id: communityId,
          limit: 'all'
        });
      }
    });

    // Load expense names
    function loadExpenseNames(expenseGroupId, target, elementId) {
      $.ajax({
        type: 'GET',
        url: '../api/data/expense_names',
        data: { target: target, expense_group_id: expenseGroupId },
        dataType: 'json',
        success: function (response) {
          let options = '<option value="">Select Expense Name</option>';
          $.each(response.data, function (index, expense) {
            options += `<option value="${expense.expense_name_id}">${expense.expense_name}</option>`;
          });
          $(elementId).html(options);
        },
        error: function (xhr, status, error) {
          console.log('Error loading expense names:', error);
        }
      });
    }

    $('#expenseGroupHP').on('change', function () {
      const id = $(this).val();
      if (id) loadExpenseNames(id, 'head-parish', '#expenseNameHP');
    });
    $('#expenseGroupSP').on('change', function () {
      const id = $(this).val();
      if (id) loadExpenseNames(id, 'sub-parish', '#expenseNameSP');
    });
    $('#expenseGroupCom').on('change', function () {
      const id = $(this).val();
      if (id) loadExpenseNames(id, 'community', '#expenseNameCom');
    });
    $('#expenseGroupG').on('change', function () {
      const id = $(this).val();
      if (id) loadExpenseNames(id, 'group', '#expenseNameG');
    });

    // Initial data load
    loadSubParishes();
    loadGroups();
  });

  // ---- REPORT DOWNLOAD FUNCTIONS ----
  function downloadExpenseGroupReport(formId, responseTarget) {
    const form = document.getElementById(formId);
    const data = new FormData(form);

    const start_date = data.get('start_date');
    const end_date = data.get('end_date');
    const expense_group_id = data.get('expense_group_id');
    const target = data.get('target');

    if (!start_date || !end_date || !expense_group_id) {
      showMessage('Please fill Start Date, End Date, and Expense Group.', 'error', responseTarget);
      return;
    }

    let params = new URLSearchParams({
      start_date,
      end_date,
      expense_group_id,
      management_level: target
    });

    if (target === 'sub-parish') params.append('sub_parish_id', data.get('sub_parish_id'));
    if (target === 'community') {
      params.append('sub_parish_id', data.get('sub_parish_id'));
      params.append('community_id', data.get('community_id'));
    }
    if (target === 'group') params.append('group_id', data.get('group_id'));

    window.location.href = '/reports/expense_group_report.php?' + params.toString();
  }

  function downloadExpenseNameReport(formId, responseTarget) {
    const form = document.getElementById(formId);
    const data = new FormData(form);

    const start_date = data.get('start_date');
    const end_date = data.get('end_date');
    const expense_name_id = data.get('expense_name_id');
    const expense_group_id = data.get('expense_group_id');
    const target = data.get('target');

    if (!start_date || !end_date || !expense_name_id) {
      showMessage('Please fill Start Date, End Date, and Expense Name.', 'error', responseTarget);
      return;
    }

    let params = new URLSearchParams({
      start_date,
      end_date,
      expense_name_id,
      expense_group_id,
      management_level: target
    });

    if (target === 'sub_parish') params.append('sub_parish_id', data.get('sub_parish_id'));
    if (target === 'community') {
      params.append('sub_parish_id', data.get('sub_parish_id'));
      params.append('community_id', data.get('community_id'));
    }
    if (target === 'group') params.append('group_id', data.get('group_id'));

    window.location.href = '/reports/expense_name_report.php?' + params.toString();
  }
</script>
</html>

