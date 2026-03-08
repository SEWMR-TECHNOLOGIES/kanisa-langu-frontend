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
    render_header('Record Revenue - Kanisa Langu');
  ?>
  <style>
      .nav-tabs .nav-link {
        position: relative;
        padding-right: 2.2rem;
      }
    
      .nav-tabs .tab-badge {
        position: absolute;
        top: 0.2rem;
        right: 0.35rem;
        transform: translate(50%, -50%);
        font-size: 0.70rem;
        line-height: 1;
        min-width: 1.35rem;
        text-align: center;
      }
    </style>

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
            <h5 class="card-title fw-semibold mb-4">Record Revenue</h5>
            <div class="alert alert-info mb-4" role="alert">
                To post all recorded revenues to the bank, just click the <strong>Post to Bank</strong> button below the form. 
                <br>No need to select any fields or make changes in the form.
            </div>
            
            <ul class="nav nav-tabs" id="revenueTabs" role="tablist">
              <li class="nav-item" role="presentation">
                <a class="nav-link active" id="head-parish-tab" data-bs-toggle="tab" href="#head-parish" role="tab">
                  Head Parish <span class="badge rounded-pill bg-danger tab-badge d-none" id="badge-head_parish" data-bs-toggle="tooltip" data-bs-placement="top" title="Unposted revenues"></span>
                </a>
              </li>
            
              <li class="nav-item" role="presentation">
                <a class="nav-link" id="sub-parish-tab" data-bs-toggle="tab" href="#sub-parish" role="tab">
                  Sub Parish <span class="badge rounded-pill bg-danger tab-badge d-none" id="badge-sub_parish" data-bs-toggle="tooltip" data-bs-placement="top" title="Unposted revenues"></span>
                </a>
              </li>
            
              <li class="nav-item" role="presentation">
                <a class="nav-link" id="community-tab" data-bs-toggle="tab" href="#community" role="tab">
                  Community <span class="badge rounded-pill bg-danger tab-badge d-none" id="badge-community" data-bs-toggle="tooltip" data-bs-placement="top" title="Unposted revenues"></span>
                </a>
              </li>
            
              <li class="nav-item" role="presentation">
                <a class="nav-link" id="group-tab" data-bs-toggle="tab" href="#group" role="tab">
                  Group <span class="badge rounded-pill bg-danger tab-badge d-none" id="badge-group" data-bs-toggle="tooltip" data-bs-placement="top" title="Unposted revenues"></span>
                </a>
              </li>
            
              <li class="nav-item" role="presentation">
                <a class="nav-link" id="other-tab" data-bs-toggle="tab" href="#other" role="tab">
                  Other Head Parish Revenues <span class="badge rounded-pill bg-danger tab-badge d-none" id="badge-other" data-bs-toggle="tooltip" data-bs-placement="top" title="Unposted revenues"></span>
                </a>
              </li>
            </ul>


            <div class="tab-content mt-3" id="revenueTabContent">
              <div class="tab-pane fade show active" id="head-parish" role="tabpanel">
                <form id="headParishForm">
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="serviceNumber" class="form-label">Service Number</label>
                        <select class="form-select" id="serviceNumber" name="service_number">
                          <option value="">Select Service Number</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="revenueStreamIdHP" class="form-label">Revenue Stream</label>
                        <select class="form-select" id="revenueStreamIdHP" name="revenue_stream_id">
                          <option value="">Select Revenue Stream</option>
                        </select>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="subParishIdHP" class="form-label">Sub Parish</label>
                        <select class="form-select" id="subParishIdHP" name="sub_parish_id">
                          <option value="">Select Sub Parish</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="revenueAmountHP" class="form-label">Amount</label>
                        <input type="text" class="form-control revenueAmountInput" id="revenueAmountHP" name="revenue_amount" placeholder="Amount" inputmode="numeric" autocomplete="off">
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <?php render_payment_method_dropdown('paymentMethodHeadParish'); ?>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="revenueDateHP" class="form-label">Revenue Date</label>
                        <input type="date" class="form-control" id="revenueDateHP" name="revenue_date" max="<?php echo date('Y-m-d'); ?>">
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-12">
                      <div class="mb-3">
                        <label for="descriptionHeadParish" class="form-label">Description</label>
                        <textarea class="form-control" id="descriptionHeadParish" name="description" rows="2" placeholder="Enter description..."></textarea>
                      </div>
                    </div>
                  </div>
                  <div class="mb-3">
                    <div class="d-flex gap-2 mt-3">
                      <button type="submit" class="btn btn-primary">Record Revenue</button>
                      <button type="button" class="btn btn-success postToBankBtn" data-target="head_parish">Post Head Parish Revenues to Bank</button>
                    </div>
                  </div>
                </form>
              </div>

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
                        <label for="revenueStreamIdSP" class="form-label">Revenue Stream</label>
                        <select class="form-select" id="revenueStreamIdSP" name="revenue_stream_id">
                          <option value="">Select Revenue Stream</option>
                        </select>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="revenueAmountSub" class="form-label">Amount</label>
                        <input type="text" class="form-control revenueAmountInput" id="revenueAmountSub" name="revenue_amount" placeholder="Amount" inputmode="numeric" autocomplete="off">
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <?php render_payment_method_dropdown('paymentMethodSubParish'); ?>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="revenueDateSP" class="form-label">Revenue Date</label>
                        <input type="date" class="form-control" id="revenueDateSP" name="revenue_date" max="<?php echo date('Y-m-d'); ?>">
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="descriptionSubParish" class="form-label">Description</label>
                        <textarea class="form-control" id="descriptionSubParish" name="description" rows="1" placeholder="Enter description..."></textarea>
                      </div>
                    </div>
                  </div>
                  
                  <div class="mb-3">
                    <div class="d-flex gap-2 mt-3">
                      <button type="submit" class="btn btn-primary">Record Revenue</button>
                      <button type="button" class="btn btn-success postToBankBtn" data-target="sub_parish">Post Sub Parish Revenues to Bank</button>
                    </div>
                  </div>
                </form>
              </div>

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
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="revenueStreamIdCM" class="form-label">Revenue Stream</label>
                        <select class="form-select" id="revenueStreamIdCM" name="revenue_stream_id">
                          <option value="">Select Revenue Stream</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="revenueAmountComm" class="form-label">Amount</label>
                        <input type="text" class="form-control revenueAmountInput" id="revenueAmountComm" name="revenue_amount" placeholder="Amount" inputmode="numeric" autocomplete="off">
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="revenueDateCom" class="form-label">Revenue Date</label>
                        <input type="date" class="form-control" id="revenueDateCom" name="revenue_date" max="<?php echo date('Y-m-d'); ?>">
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <?php render_payment_method_dropdown('paymentMethodCommunity'); ?>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-lg-12">
                      <div class="mb-3">
                        <label for="descriptionCommunity" class="form-label">Description</label>
                        <textarea class="form-control" id="descriptionCommunity" name="description" rows="2" placeholder="Enter description..."></textarea>
                      </div>
                    </div>
                  </div>
                  
                  <div class="mb-3">
                    <div class="d-flex gap-2 mt-3">
                      <button type="submit" class="btn btn-primary">Record Revenue</button>
                      <button type="button" class="btn btn-success postToBankBtn" data-target="community">Post Community Revenues to Bank</button>
                    </div>
                  </div>
                </form>
              </div>

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
                        <label for="revenueStreamIdGP" class="form-label">Revenue Stream</label>
                        <select class="form-select" id="revenueStreamIdGP" name="revenue_stream_id">
                          <option value="">Select Revenue Stream</option>
                        </select>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="revenueAmountGP" class="form-label">Amount</label>
                        <input type="text" class="form-control revenueAmountInput" id="revenueAmountGP" name="revenue_amount" placeholder="Amount" inputmode="numeric" autocomplete="off">
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <?php render_payment_method_dropdown('paymentMethodGroup'); ?>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="revenueDateGP" class="form-label">Revenue Date</label>
                        <input type="date" class="form-control" id="revenueDateGP" name="revenue_date" max="<?php echo date('Y-m-d'); ?>">
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="descriptionGroup" class="form-label">Description</label>
                        <textarea class="form-control" id="descriptionGroup" name="description" rows="1" placeholder="Enter description..."></textarea>
                      </div>
                    </div>
                  </div>

                  <div class="mb-3">
                    <div class="d-flex gap-2 mt-3">
                      <button type="submit" class="btn btn-primary">Record Revenue</button>
                      <button type="button" class="btn btn-success postToBankBtn" data-target="group">Post Groups Revenues to Bank</button>
                    </div>
                  </div>
                </form>
              </div>

              <div class="tab-pane fade" id="other" role="tabpanel">
                <form id="otherForm">
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="serviceNumberOther" class="form-label">Service Number</label>
                        <select class="form-select" id="serviceNumberOther" name="service_number">
                          <option value="">Select Service Number</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="revenueStreamIdOther" class="form-label">Revenue Stream</label>
                        <select class="form-select" id="revenueStreamIdOther" name="revenue_stream_id">
                          <option value="">Select Revenue Stream</option>
                        </select>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="revenueAmountOther" class="form-label">Amount</label>
                        <input type="text" class="form-control revenueAmountInput" id="revenueAmountOther" name="revenue_amount" placeholder="Amount" inputmode="numeric" autocomplete="off">
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <?php render_payment_method_dropdown('paymentMethodOther'); ?>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="revenueDateOther" class="form-label">Revenue Date</label>
                        <input type="date" class="form-control" id="revenueDateOther" name="revenue_date" max="<?php echo date('Y-m-d'); ?>">
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="descriptionOther" class="form-label">Description</label>
                        <textarea class="form-control" id="descriptionOther" name="description" rows="1" placeholder="Enter description..."></textarea>
                      </div>
                    </div>
                  </div>
                  
                  <div class="mb-3">
                    <div class="d-flex gap-2 mt-3">
                      <button type="submit" class="btn btn-primary">Record Revenue</button>
                      <button type="button" class="btn btn-success postToBankBtn" data-target="other">Post Other Head Parish Revenues to Bank</button>
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
 
    <div class="modal fade" id="postToBankModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-warning">
            <h5 class="modal-title">Confirm Post to Bank</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p>This action <strong>cannot be reversed</strong>. Review the revenues below before posting:</p>
            <div id="previewTableWrapper" class="table-responsive">
              <table class="table table-bordered" id="previewTable">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Revenue Name</th>
                    <th>Amount</th>
                    <th>Sub Parish</th>
                    <th>Community</th>
                    <th>Group</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger" id="confirmPostToBank">Yes, Post to Bank</button>
          </div>
        </div>
      </div>
    </div>


 <?php require_once('components/footer_files.php') ?>
  
<script>

    const badgeSelectorByTarget = {
      head_parish: '#badge-head_parish',
      sub_parish:  '#badge-sub_parish',
      community:   '#badge-community',
      group:       '#badge-group',
      other:       '#badge-other'
    };
    
    function setTabBadge(target, count) {
      const sel = badgeSelectorByTarget[target];
      const $badge = $(sel);
      if (!$badge.length) return;
    
      const n = parseInt(count, 10) || 0;
    
      if (n > 0) {
        const msg = 'Unposted revenues: ' + n;
    
        $badge.text(n).removeClass('d-none')
          .attr('title', msg)
          .attr('data-bs-original-title', msg);
    
        const el = $badge[0];
        const tip = bootstrap.Tooltip.getInstance(el);
        if (tip) tip.setContent({ '.tooltip-inner': msg });
    
      } else {
        $badge.text('').addClass('d-none')
          .attr('title', 'Unposted revenues')
          .attr('data-bs-original-title', 'Unposted revenues');
      }
    }

    
    function fetchUnpostedCount(target) {
      return $.ajax({
        type: 'POST',
        url: '../api/data/revenues_to_post_to_bank',
        data: { target: target },
        dataType: 'json'
      }).then(
        function (resp) {
          if (resp && resp.success && Array.isArray(resp.data)) return resp.data.length;
          return 0;
        },
        function () {
          return 0;
        }
      );
    }
    
    function refreshUnpostedBadges() {
      $.each(badgeSelectorByTarget, function (target) {
        fetchUnpostedCount(target).then(function (count) {
          setTabBadge(target, count);
        });
      });
    }
    
    refreshUnpostedBadges();

  $('select').select2({
    width: '100%'
  });

  $(document).ready(function () {

    // ============================
    // Amount formatting (same as Harambee)
    // ============================
    function formatWithCommas(number) {
      return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    function removeCommas(value) {
      return (value || '').toString().replace(/,/g, '');
    }

    // allow only digits, format as user types (for all revenue amount fields)
    $(document).on('input', '.revenueAmountInput', function () {
      const raw = removeCommas($(this).val()).replace(/[^\d]/g, '');
      if (!isNaN(raw) && raw !== '') {
        $(this).val(formatWithCommas(raw));
      } else {
        $(this).val('');
      }
    });

    // before submitting any revenue form, remove commas so backend gets clean number
    function cleanAmountBeforeSubmit(formSelector) {
      const $form = $(formSelector);
      $form.find('.revenueAmountInput').each(function () {
        $(this).val(removeCommas($(this).val()));
      });
    }

    // optional: format amounts in preview modal nicely
    function formatNumberForDisplay(n) {
      const raw = removeCommas(n);
      const num = parseFloat(raw);
      if (isNaN(num)) return n;
      return num.toLocaleString();
    }

    function resetRevenueSelect(selector) {
      $(selector).html('<option value="">Select Revenue Stream</option>').trigger('change.select2');
    }

    function loadRevenueStreamsForContext(context) {
      const data = { limit: 'all', target: context.target };

      if (context.target === 'sub-parish') {
        data.sub_parish_id = context.sub_parish_id || 0;
      } else if (context.target === 'community') {
        data.sub_parish_id = context.sub_parish_id || 0;
        data.community_id = context.community_id || 0;
      } else if (context.target === 'groups') {
        data.group_id = context.group_id || 0;
      }

      return $.ajax({
        type: 'GET',
        url: '../api/data/head_parish_revenue_streams',
        data: data,
        dataType: 'json'
      });
    }

    function applyRevenueStreamsToSelect(targetKey, response) {
      let options = '<option value="">Select Revenue Stream</option>';
      if (response && response.success && Array.isArray(response.data)) {
        $.each(response.data, function (index, stream) {
          options += '<option value="' + stream.revenue_stream_id + '">' + stream.revenue_stream_name + '</option>';
        });
      }

      if (targetKey === 'head_parish') {
        $('#revenueStreamIdHP').html(options).trigger('change.select2');
      } else if (targetKey === 'sub_parish') {
        $('#revenueStreamIdSP').html(options).trigger('change.select2');
      } else if (targetKey === 'community') {
        $('#revenueStreamIdCM').html(options).trigger('change.select2');
      } else if (targetKey === 'group') {
        $('#revenueStreamIdGP').html(options).trigger('change.select2');
      } else if (targetKey === 'other') {
        $('#revenueStreamIdOther').html(options).trigger('change.select2');
      }
    }

    function fetchAndFillRevenueStreamsForActiveTab() {
      const activeTab = $('#revenueTabs .nav-link.active').attr('id');

      if (activeTab === 'head-parish-tab') {
        loadRevenueStreamsForContext({ target: 'head-parish' }).done(function(resp){
          applyRevenueStreamsToSelect('head_parish', resp);
        });
      }

      if (activeTab === 'sub-parish-tab') {
        const sp = $('#subParishId').val();
        if (!sp) {
          resetRevenueSelect('#revenueStreamIdSP');
          return;
        }
        loadRevenueStreamsForContext({ target: 'sub-parish', sub_parish_id: sp }).done(function(resp){
          applyRevenueStreamsToSelect('sub_parish', resp);
        });
      }

      if (activeTab === 'community-tab') {
        const sp = $('#subParishIdCom').val();
        const cm = $('#communityId').val();
        if (!sp || !cm) {
          resetRevenueSelect('#revenueStreamIdCM');
          return;
        }
        loadRevenueStreamsForContext({ target: 'community', sub_parish_id: sp, community_id: cm }).done(function(resp){
          applyRevenueStreamsToSelect('community', resp);
        });
      }

      if (activeTab === 'group-tab') {
        const gid = $('#groupId').val();
        if (!gid) {
          resetRevenueSelect('#revenueStreamIdGP');
          return;
        }
        loadRevenueStreamsForContext({ target: 'groups', group_id: gid }).done(function(resp){
          applyRevenueStreamsToSelect('group', resp);
        });
      }

      if (activeTab === 'other-tab') {
        loadRevenueStreamsForContext({ target: 'head-parish' }).done(function(resp){
          applyRevenueStreamsToSelect('other', resp);
        });
      }
    }

    $('#revenueTabs a[data-bs-toggle="tab"]').on('shown.bs.tab', function () {
      fetchAndFillRevenueStreamsForActiveTab();
    });

    function submitForm(formId, target) {
      // remove commas before serialize
      cleanAmountBeforeSubmit(formId);

      $('#responseMessage').html('<div class="response-message"><i class="fas fa-spinner fa-spin icon"></i> Recording revenue...</div>');
      const formData = $(formId).serialize() + '&target=' + target;

      $.ajax({
        type: 'POST',
        url: '../api/records/record_revenue',
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

    $('#otherForm').on('submit', function (event) {
      event.preventDefault();
      submitForm('#otherForm', 'other');
    });

    function loadServiceNumbers() {
      $.ajax({
        type: 'GET',
        url: '../api/data/head_parish_services',
        dataType: 'json',
        success: function (response) {
          let options = '<option value="">Select Service Number</option>';
          $.each(response.data, function (index, service) {
            options += '<option value="' + service.service_id + '">' + service.service + '</option>';
          });
          $('#serviceNumber').html(options).trigger('change.select2');
          $('#serviceNumberOther').html(options).trigger('change.select2');
        },
        error: function (xhr, status, error) {
          console.log('Error loading service numbers:', error);
        }
      });
    }

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
          $('#subParishIdHP').html(options).trigger('change.select2'); 
          $('#subParishId').html(options).trigger('change.select2');
          $('#subParishIdCom').html(options).trigger('change.select2'); 
        },
        error: function (xhr, status, error) {
          console.log('Error loading sub-parishes:', error);
        }
      });
    }

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
          $('#communityId').html(options).trigger('change.select2');
        },
        error: function (xhr, status, error) {
          console.log('Error loading communities:', error);
        }
      });
    }

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
          $('#groupId').html(options).trigger('change.select2');
        },
        error: function (xhr, status, error) {
          console.log('Error loading groups:', error);
        }
      });
    }

    loadServiceNumbers();
    loadSubParishes();
    loadGroups();

    loadRevenueStreamsForContext({ target: 'head-parish' }).done(function(resp){
      applyRevenueStreamsToSelect('head_parish', resp);
      applyRevenueStreamsToSelect('other', resp);
    });

    $('#serviceNumber').on('change', function () {
      fetchAndFillRevenueStreamsForActiveTab();
    });

    $('#serviceNumberOther').on('change', function () {
      fetchAndFillRevenueStreamsForActiveTab();
    });

    $('#subParishId').on('change', function () {
      resetRevenueSelect('#revenueStreamIdSP');
      const sp = $(this).val();
      if (!sp) return;
      loadRevenueStreamsForContext({ target: 'sub-parish', sub_parish_id: sp }).done(function(resp){
        applyRevenueStreamsToSelect('sub_parish', resp);
      });
    });

    $('#subParishIdCom').on('change', function () {
      const sp = $(this).val();
      $('#communityId').html('<option value="">Select Community</option>').trigger('change.select2');
      resetRevenueSelect('#revenueStreamIdCM');
      if (!sp) return;
      loadCommunities(sp);
    });

    $('#communityId').on('change', function () {
      resetRevenueSelect('#revenueStreamIdCM');
      const sp = $('#subParishIdCom').val();
      const cm = $(this).val();
      if (!sp || !cm) return;
      loadRevenueStreamsForContext({ target: 'community', sub_parish_id: sp, community_id: cm }).done(function(resp){
        applyRevenueStreamsToSelect('community', resp);
      });
    });

    $('#groupId').on('change', function () {
      resetRevenueSelect('#revenueStreamIdGP');
      const gid = $(this).val();
      if (!gid) return;
      loadRevenueStreamsForContext({ target: 'groups', group_id: gid }).done(function(resp){
        applyRevenueStreamsToSelect('group', resp);
      });
    });
    
    let targetScope = null;

    $(document).on('click', '.postToBankBtn', function () {
        let btn = $(this);
        let originalText = btn.text();
        btn.prop('disabled', true).text('Loading Preview...');
    
        targetScope = btn.data('target');
        $('#previewTable tbody').empty();
    
        $.ajax({
            type: 'POST',
            url: '../api/data/revenues_to_post_to_bank',
            data: { target: targetScope },
            dataType: 'json',
            success: function (response) {
                if (response.success && response.data.length) {
                    let sn = 1;
                    response.data.forEach(item => {
                        let row = `<tr>
                            <td>${sn}</td>
                            <td>${formatDateDMY(item.revenue_date)}</td>
                            <td>${item.revenue_stream_name}</td>
                            <td>${formatNumberForDisplay(item.total_amount)}</td>`;
    
                        if (targetScope === 'sub_parish' || targetScope === 'community') {
                            row += `<td>${item.sub_parish_name || ''}</td>`;
                        }
    
                        if (targetScope === 'community') {
                            row += `<td>${item.community_name || ''}</td>`;
                        } else if (targetScope === 'group') {
                            row += `<td>${item.group_name || ''}</td>`;
                        }
    
                        row += `</tr>`;
                        $('#previewTable tbody').append(row);
                        sn++;
                    });
    
                    let headerHtml = '<tr><th>S/N</th><th>Date</th><th>Revenue Name</th><th>Amount</th>';
                    if (targetScope === 'sub_parish' || targetScope === 'community') headerHtml += '<th>Sub Parish</th>';
                    if (targetScope === 'community') headerHtml += '<th>Community</th>';
                    if (targetScope === 'group') headerHtml += '<th>Group</th>';
                    headerHtml += '</tr>';
                    $('#previewTable thead').html(headerHtml);
                } else {
                    $('#previewTable tbody').html('<tr><td colspan="6" class="text-center">No unposted revenues found</td></tr>');
                }
    
                $('#postToBankModal').modal('show');
            },
            error: function (xhr, status, error) {
                alert('Failed to load preview: ' + error);
            },
            complete: function () {
                btn.prop('disabled', false).text(originalText);
            }
        });
    });


    $('#confirmPostToBank').on('click', function () {
      if (!targetScope) return;
    
      $('#responseMessage').html('<div class="response-message"><i class="fas fa-spinner fa-spin icon"></i> Posting to bank...</div>');
      
      $.ajax({
        type: 'POST',
        url: '../api/records/post_to_bank',
        data: { target: targetScope },
        dataType: 'json',
        success: function (response) {
          let messageHtml = '';
          if (response.success) {
            messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i>' + response.message + '</div>';
            setTimeout(() => location.reload(), 2000);
          } else {
            messageHtml = '<div class="response-message error"><i class="fas fa-times-circle icon"></i>' + response.message + '</div>';
          }
          $('#responseMessage').html(messageHtml);
          $('#postToBankModal').modal('hide');
        },
        error: function (xhr, status, error) {
          $('#responseMessage').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i>An error occurred: ' + error + '</div>');
          $('#postToBankModal').modal('hide');
        }
      });
    });

  });
</script>

</body>
</html>
