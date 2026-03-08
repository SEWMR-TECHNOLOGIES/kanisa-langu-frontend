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
    render_header('Revenue Group Report - Kanisa Langu');
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
              <h5 class="card-title fw-semibold mb-4">Revenue Group Report</h5>

              <ul class="nav nav-tabs" id="revenueBudgetsTabs" role="tablist">
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

              <div class="tab-content mt-3" id="revenueBudgetTabContent">
                <!-- Head Parish Revenue Form -->
                <div class="tab-pane fade show active" id="head-parish" role="tabpanel">
                  <form id="headParishForm" autocomplete="off">
                    <input type="hidden" name="target" value="head-parish">
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="revenueGroupHP" class="form-label">Revenue Group <span class="text-danger">*</span></label>
                          <select class="form-select" id="revenueGroupHP" name="revenue_group_id" required>
                            <option value="">Select Revenue Group</option>
                          </select>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="revenueNameHP" class="form-label">Revenue Name</label>
                          <select class="form-select" id="revenueNameHP" name="revenue_name_id">
                            <option value="">Select Revenue Name</option>
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
                        <button type="button" class="btn btn-primary mx-2" onclick="downloadRevenueGroupReport('headParishForm','#responseMessage')">Download Revenue Group Report</button>
                        <button type="button" class="btn btn-success mx-2" onclick="downloadRevenueNameReport('headParishForm','#responseMessage')">Download Revenue Name Report</button>
                      </div>
                    </div>
                  </form>
                </div>

                <!-- Sub Parish Revenue Form -->
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
                          <label for="revenueGroupSP" class="form-label">Revenue Group <span class="text-danger">*</span></label>
                          <select class="form-select" id="revenueGroupSP" name="revenue_group_id" required>
                            <option value="">Select Revenue Group</option>
                          </select>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="revenueNameSP" class="form-label">Revenue Name</label>
                          <select class="form-select" id="revenueNameSP" name="revenue_name_id">
                            <option value="">Select Revenue Name</option>
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
                        <button type="button" class="btn btn-primary mx-2" onclick="downloadRevenueGroupReport('subParishForm','#responseMessage')">Download Revenue Group Report</button>
                        <button type="button" class="btn btn-success mx-2" onclick="downloadRevenueNameReport('subParishForm','#responseMessage')">Download Revenue Name Report</button>
                      </div>
                    </div>
                  </form>
                </div>

                <!-- Community Revenue Form -->
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
                          <label for="revenueGroupCom" class="form-label">Revenue Group <span class="text-danger">*</span></label>
                          <select class="form-select" id="revenueGroupCom" name="revenue_group_id" required>
                            <option value="">Select Revenue Group</option>
                          </select>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="revenueNameCom" class="form-label">Revenue Name</label>
                          <select class="form-select" id="revenueNameCom" name="revenue_name_id">
                            <option value="">Select Revenue Name</option>
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
                        <button type="button" class="btn btn-primary mx-2" onclick="downloadRevenueGroupReport('communityForm','#responseMessage')">Download Revenue Group Report</button>
                        <button type="button" class="btn btn-success mx-2" onclick="downloadRevenueNameReport('communityForm','#responseMessage')">Download Revenue Name Report</button>
                      </div>
                    </div>
                  </form>
                </div>

                <!-- Group Revenue Form -->
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
                          <label for="revenueGroupG" class="form-label">Revenue Group <span class="text-danger">*</span></label>
                          <select class="form-select" id="revenueGroupG" name="revenue_group_id" required>
                            <option value="">Select Revenue Group</option>
                          </select>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="revenueNameG" class="form-label">Revenue Name</label>
                          <select class="form-select" id="revenueNameG" name="revenue_name_id">
                            <option value="">Select Revenue Name</option>
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
                        <button type="button" class="btn btn-primary mx-2" onclick="downloadRevenueGroupReport('groupForm','#responseMessage')">Download Revenue Group Report</button>
                        <button type="button" class="btn btn-success mx-2" onclick="downloadRevenueNameReport('groupForm','#responseMessage')">Download Revenue Name Report</button>
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

    // Load revenue groups
    function loadRevenueGroups(targetId, url, params = {}) {
      $.ajax({
        type: 'GET',
        url: url,
        data: params,
        dataType: 'json',
        success: function (response) {
          let options = '<option value="">Select Revenue Group</option>';
          $.each(response.data, function (index, group) {
            options += `<option value="${group.revenue_group_id}">${group.revenue_group_name}</option>`;
          });
          $(targetId).html(options);
        },
        error: function (xhr, status, error) {
          console.log('Error loading revenue groups:', error);
        }
      });
    }

    // Load revenue groups for Head Parish on page load
    loadRevenueGroups('#revenueGroupHP', '../api/data/revenue_groups', { target: 'head-parish', limit: 'all' });

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
      loadRevenueGroups('#revenueGroupSP', '../api/data/revenue_groups', { target: 'sub-parish', sub_parish_id: subParishId, limit: 'all' });
    });

    $('#groupId').on('change', function () {
      const groupId = $(this).val();
      loadRevenueGroups('#revenueGroupG', '../api/data/revenue_groups', { target: 'group', group_id: groupId, limit: 'all' });
    });

    $('#subParishIdCom').on('change', function () {
      loadCommunities($(this).val());
    });

    $('#subParishIdCom, #communityId').on('change', function () {
      const subParishId = $('#subParishIdCom').val();
      const communityId = $('#communityId').val();

      if (subParishId && communityId) {
        loadRevenueGroups('#revenueGroupCom', '../api/data/revenue_groups', {
          target: 'community',
          sub_parish_id: subParishId,
          community_id: communityId,
          limit: 'all'
        });
      }
    });

    // Load revenue names
    function loadRevenueNames(revenueGroupId, target, elementId) {
      $.ajax({
        type: 'GET',
        url: '../api/data/revenue_streams',
        data: { target: target, revenue_group_id: revenueGroupId },
        dataType: 'json',
        success: function (response) {
          let options = '<option value="">Select Revenue Name</option>';
          $.each(response.data, function (index, revenue) {
            options += `<option value="${revenue.revenue_stream_id}">${revenue.revenue_stream_name}</option>`;
          });
          $(elementId).html(options);
        },
        error: function (xhr, status, error) {
          console.log('Error loading revenue names:', error);
        }
      });
    }

    $('#revenueGroupHP').on('change', function () {
      const id = $(this).val();
      if (id) loadRevenueNames(id, 'head-parish', '#revenueNameHP');
    });
    $('#revenueGroupSP').on('change', function () {
      const id = $(this).val();
      if (id) loadRevenueNames(id, 'sub-parish', '#revenueNameSP');
    });
    $('#revenueGroupCom').on('change', function () {
      const id = $(this).val();
      if (id) loadRevenueNames(id, 'community', '#revenueNameCom');
    });
    $('#revenueGroupG').on('change', function () {
      const id = $(this).val();
      if (id) loadRevenueNames(id, 'group', '#revenueNameG');
    });

    // Initial data load
    loadSubParishes();
    loadGroups();
  });

  // ---- REPORT DOWNLOAD FUNCTIONS ----
  function downloadRevenueGroupReport(formId, responseTarget) {
    const form = document.getElementById(formId);
    const data = new FormData(form);

    const start_date = data.get('start_date');
    const end_date = data.get('end_date');
    const revenue_group_id = data.get('revenue_group_id');
    const target = data.get('target');

    if (!start_date || !end_date || !revenue_group_id) {
      showMessage('Please fill Start Date, End Date, and Revenue Group.', 'error', responseTarget);
      return;
    }

    let params = new URLSearchParams({
      start_date,
      end_date,
      revenue_group_id,
      management_level: target
    });

    if (target === 'sub-parish') params.append('sub_parish_id', data.get('sub_parish_id'));
    if (target === 'community') {
      params.append('sub_parish_id', data.get('sub_parish_id'));
      params.append('community_id', data.get('community_id'));
    }
    if (target === 'group') params.append('group_id', data.get('group_id'));

    window.location.href = '/reports/revenue_group_report.php?' + params.toString();
  }

  function downloadRevenueNameReport(formId, responseTarget) {
    const form = document.getElementById(formId);
    const data = new FormData(form);

    const start_date = data.get('start_date');
    const end_date = data.get('end_date');
    const revenue_stream_id = data.get('revenue_name_id');
    const revenue_group_id = data.get('revenue_group_id');
    const target = data.get('target');

    if (!start_date || !end_date || !revenue_stream_id) {
      showMessage('Please fill Start Date, End Date, and Revenue Name.', 'error', responseTarget);
      return;
    }

    let params = new URLSearchParams({
      start_date,
      end_date,
      revenue_stream_id,
      revenue_group_id,
      management_level: target
    });

    if (target === 'sub_parish') params.append('sub_parish_id', data.get('sub_parish_id'));
    if (target === 'community') {
      params.append('sub_parish_id', data.get('sub_parish_id'));
      params.append('community_id', data.get('community_id'));
    }
    if (target === 'group') params.append('group_id', data.get('group_id'));

    window.location.href = '/reports/revenue_stream_report.php?' + params.toString();
  }
</script>
</html>

