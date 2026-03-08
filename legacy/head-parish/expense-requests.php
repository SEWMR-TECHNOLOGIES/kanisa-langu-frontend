<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
// Ensure the user is authenticated as head parish admin
check_session('head_parish_admin_id', '../head-parish/sign-in');
?>
<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Expense Requests - Kanisa Langu');
  ?>
  <style>
    #loading {
      display: none; /* Hidden by default */
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      z-index: 9999;
    }
    td.text-end {
      white-space: nowrap; /* Keeps the buttons on one line */
    }
  </style>
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
    <?php require_once('components/sidebar.php') ?>
    <div class="body-wrapper">
      <?php require_once('components/header.php') ?>
      <div class="container-fluid">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Expense Requests</h5>
            
            <!-- Search Form -->
            <div class="row mb-4">
              <div class="col-md-8">
                <input type="text" id="searchExpense" class="form-control" placeholder="Search Expense Request">
              </div>
              <div class="col-md-4 text-end">
                <button class="btn btn-primary" id="searchBtn">Search</button>
              </div>
            </div>

            <!-- Tab navigation for different expense request levels -->
            <ul class="nav nav-tabs" id="expenseTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="head-parish-tab" data-bs-toggle="tab" data-bs-target="#head-parish" role="tab" aria-controls="head-parish" aria-selected="true">Head Parish</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="sub-parish-tab" data-bs-toggle="tab" data-bs-target="#sub-parish" role="tab" aria-controls="sub-parish" aria-selected="false">Sub Parish</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="community-tab" data-bs-toggle="tab" data-bs-target="#community" role="tab" aria-controls="community" aria-selected="false">Community</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="group-tab" data-bs-toggle="tab" data-bs-target="#group" role="tab" aria-controls="group" aria-selected="false">Group</button>
                </li>
            </ul>

            <!-- Tab content -->
            <div class="tab-content mt-4">
              <!-- Head Parish Tab -->
              <div class="tab-pane fade show active" id="head-parish" role="tabpanel" aria-labelledby="head-parish-tab">
                <div class="table-responsive">
                  <table class="table table-hover">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Expense Group</th>
                        <th>Expense Name</th>
                        <th>Amount</th>
                        <th>Request Date</th>
                        <th>Chairperson Approval</th>
                        <th>Pastor Approval</th>
                        <th class="text-end">Actions</th>
                        
                      </tr>
                    </thead>
                    <tbody id="headParishExpenseList"></tbody>
                  </table>
                </div>
              </div>

              <!-- Sub Parish Tab -->
              <div class="tab-pane fade" id="sub-parish" role="tabpanel" aria-labelledby="sub-parish-tab">
                <div class="table-responsive">
                  <table class="table table-hover">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Sub Parish</th>
                        <th>Expense Group</th>
                        <th>Expense Name</th>
                        <th>Amount</th>
                        <th>Request Date</th>
                        <th>Chairperson Approval</th>
                        <th>Pastor Approval</th>
                        <th class="text-end">Actions</th>
                      </tr>
                    </thead>
                    <tbody id="subParishExpenseList"></tbody>
                  </table>
                </div>
              </div>

              <!-- Community Tab -->
              <div class="tab-pane fade" id="community" role="tabpanel" aria-labelledby="community-tab">
                <div class="table-responsive">
                  <table class="table table-hover">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Community Name</th>
                        <th>Expense Group</th>
                        <th>Expense Name</th>
                        <th>Amount</th>
                        <th>Request Date</th>
                        <th>Chairperson Approval</th>
                        <th>Pastor Approval</th>
                        <th class="text-end">Actions</th>
                      </tr>
                    </thead>
                    <tbody id="communityExpenseList"></tbody>
                  </table>
                </div>
              </div>

              <!-- Group Tab -->
              <div class="tab-pane fade" id="group" role="tabpanel" aria-labelledby="group-tab">
                <div class="table-responsive">
                  <table class="table table-hover">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Group Name</th>
                        <th>Expense Group</th>
                        <th>Expense Name</th>
                        <th>Amount</th>
                        <th>Request Date</th>
                        <th>Chairperson Approval</th>
                        <th>Pastor Approval</th>
                        <th class="text-end">Actions</th>
                      </tr>
                    </thead>
                    <tbody id="groupExpenseList"></tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- Loading GIF -->
            <div id="loading">
              <img src="../assets/images/gifs/loading.gif" alt="Loading..." height="100">
            </div>

            <!-- Pagination -->
            <nav id="paginationNav" aria-label="Expense pagination">
                <div class="overflow-auto scroll-sidebar" data-simplebar="">
                    <ul class="pagination justify-content-start">
                        <!-- Pagination buttons will be generated dynamically -->
                    </ul>
                </div>
            </nav>

          </div>
        </div>
      </div>
    </div>
  </div>

<!-- Respond Modal -->
<div class="modal fade" id="respondModal" tabindex="-1" aria-labelledby="respondModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="respondForm">
        <div class="modal-header">
          <h5 class="modal-title" id="respondModalLabel">Respond to Expense Request</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="respondRequestId" name="request_id">
          <input type="hidden" id="respondTarget" name="target">

          <div class="mb-3">
            <label><strong>Response:</strong></label><br>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="approval" id="approveOption" value="approve" checked>
              <label class="form-check-label" for="approveOption">Approve</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="approval" id="rejectOption" value="reject">
              <label class="form-check-label" for="rejectOption">Reject</label>
            </div>
          </div>
        <div class="mb-3" id="adjustedAmountDiv">
          <label for="adjustedAmount" class="form-label"><strong>Approved Amount (TZS)</strong></label>
          <input type="number" min="0" class="form-control" id="adjustedAmount" name="approved_amount" placeholder="Enter approved amount">
        </div>
          <div class="mb-3" id="rejectionReasonDiv" style="display:none;">
            <label for="rejectionReason" class="form-label"><strong>Rejection Reason</strong></label>
            <textarea class="form-control" id="rejectionReason" name="rejection_reason" rows="3" placeholder="Enter reason for rejection"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Submit Response</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
        <div id="responseMessage" class="mx-3 mb-3"></div>
      </form>
    </div>
  </div>
</div>


<!-- Modal HTML for displaying expense details -->
<div class="modal fade" id="containerModal" tabindex="-1" aria-labelledby="containerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="containerModalLabel">Expense Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="container-items">
                    <!-- Expense details will be dynamically inserted here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Itemize Expenses Modal -->
<div class="modal fade" id="itemizeModal" tabindex="-1" aria-labelledby="itemizeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="itemizeForm" class="p-3">
        <div class="modal-header">
          <h5 class="modal-title" id="itemizeModalLabel">Itemize Expense Items</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <input type="hidden" id="itemizeRequestId" name="request_id" />
        <input type="hidden" id="itemizeTarget" name="target" />

        <div id="message" class="mb-3"></div>

        <div class="row g-3 align-items-end">
          <div class="col-md-6">
            <label for="itemName" class="form-label">Item Name <span class="text-danger">*</span></label>
            <input type="text" id="itemName" name="item_name" class="form-control" required placeholder="Enter item name" />
          </div>

          <div class="col-md-3">
            <label for="spentOn" class="form-label">Spent On (Optional)</label>
            <input type="date" id="spentOn" name="spent_on" class="form-control" />
          </div>
        </div>

        <div class="row g-3 align-items-end mt-1">
          <div class="col-md-3">
            <label for="unitCost" class="form-label">Unit Cost (TZS)</label>
            <input type="number" id="unitCost" name="unit_cost" class="form-control" min="0" step="0.01" placeholder="0.00" />
          </div>

          <div class="col-md-3">
            <label for="quantity" class="form-label">Quantity</label>
            <input type="number" id="quantity" name="quantity" class="form-control" min="0" step="0.01" placeholder="0" />
          </div>

          <div class="col-md-3">
            <label for="measureId" class="form-label">Unit of Measure</label>
            <select id="measureId" name="measure_id" class="form-select">
              <option value="">Select unit</option>
              <!-- to be populated via API -->
            </select>
          </div>

          <div class="col-md-3">
            <label for="totalCost" class="form-label">Total Cost</label>
            <input type="number" id="totalCost" name="total_cost" class="form-control bg-light" readonly placeholder="Auto" />
          </div>
        </div>

        <div class="mt-3 d-flex justify-content-between">
          <button type="button" id="addItemBtn" class="btn btn-success">Add Item</button>
          <button type="button" id="clearItemsBtn" class="btn btn-outline-danger">Clear All Items</button>
        </div>

        <hr />

        <h6>Itemized Expense Items</h6>
        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
          <table class="table table-sm table-bordered">
            <thead>
              <tr>
                <th>#</th>
                <th>Item Name</th>
                <th>Unit Cost</th>
                <th>Qty</th>
                <th>Unit</th>
                <th>Total</th>
                <th>Date</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="itemizedItemsList">
              <!-- Items will be appended here -->
            </tbody>
          </table>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="saveItemsBtn">Save Items</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>



  <?php require_once('components/footer_files.php') ?>

  <script>
$(document).ready(function () {
    function showMessage(type, message) {
      $('#message').html(`<div class="response-message ${type}"><i class="fas fa-${type === 'success' ? 'check-circle' : 'times-circle'} icon"></i> ${message}</div>`);
      setTimeout(() => $('#message').html(''), 3000);
    }
    
    function escapeHtml(text) {
      return $('<div>').text(text).html();
    }
    
    function calculateTotalCost() {
      const cost = parseFloat($('#unitCost').val()) || 0;
      const qty = parseFloat($('#quantity').val()) || 0;
      $('#totalCost').val((cost * qty).toFixed(2));
    }
    
    function renderItemizedItems(items) {
      const $tbody = $('#itemizedItemsList');
      $tbody.empty();
      if (!items.length) {
        $tbody.append('<tr><td colspan="8" class="text-center text-muted">No items added yet.</td></tr>');
        return;
      }
    
      items.forEach((item, idx) => {
        $tbody.append(`
          <tr>
            <td>${idx + 1}</td>
            <td>${escapeHtml(item.item_name)}</td>
            <td>${Number(item.unit_cost).toLocaleString()}</td>
            <td>${item.quantity}</td>
            <td>${item.unit_text || '-'}</td>
            <td>${Number(item.total_cost).toLocaleString()}</td>
            <td>${item.spent_on || '-'}</td>
            <td><button type="button" class="btn btn-sm btn-danger remove-item-btn" data-index="${idx}">Remove</button></td>
          </tr>
        `);
      });
    }
    
    function loadUnitOfMeasures() {
      $.get('/api/data/unit_of_measures.php', function (response) {
        const $select = $('#measureId');
        $select.empty().append('<option value="">Select unit</option>');
        if (response.success && Array.isArray(response.data)) {
          response.data.forEach(unit => {
            $select.append(`<option value="${unit.measure_id}">${unit.unit} - ${unit.meaning}</option>`);
          });
          
          setTimeout(() => {
            $select.select2({ width: '100%', dropdownParent: $('#itemizeModal') });
          }, 100);
        } else {
          showMessage('error', response.message || 'Failed to load unit of measures');
        }
      }).fail(() => {
        showMessage('error', 'Error loading unit of measures');
      });
    }
    
    $(document).on('click', '.itemize-btn', function () {
      const requestId = $(this).data('request-id');
      const target = $(this).data('target');
    
      $('#itemizeRequestId').val(requestId);
      $('#itemizeTarget').val(target);
      $('#message').html('');
      loadUnitOfMeasures();
    
      const storageKey = `itemized_items_${requestId}`;
      let items = [];
      try {
        items = JSON.parse(localStorage.getItem(storageKey)) || [];
      } catch {
        items = [];
      }
      renderItemizedItems(items);
    
      $('#itemName, #unitCost, #quantity, #totalCost, #spentOn, #measureId').val('');
    
      const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('itemizeModal'));
      modal.show();
    });
    
    $('#unitCost, #quantity').on('input', calculateTotalCost);
    
    $('#addItemBtn').click(function (e) {
      e.preventDefault();
    
      const requestId = $('#itemizeRequestId').val();
      const storageKey = `itemized_items_${requestId}`;
    
      const itemName = $('#itemName').val().trim();
      const unitCost = parseFloat($('#unitCost').val()) || 0;
      const quantity = parseFloat($('#quantity').val()) || 0;
      const measureId = $('#measureId').val();
      const unitText = $('#measureId option:selected').text();
      const totalCost = parseFloat($('#totalCost').val()) || 0;
      const spentOn = $('#spentOn').val();
    
      if (!itemName) {
        showMessage('error', 'Item name is required.');
        return;
      }
    
      const newItem = {
        item_name: itemName,
        unit_cost: unitCost,
        quantity: quantity,
        measure_id: measureId,
        unit_text: unitText,
        total_cost: totalCost,
        spent_on: spentOn
      };
    
      let items = [];
      try {
        items = JSON.parse(localStorage.getItem(storageKey)) || [];
      } catch {
        items = [];
      }
    
      items.push(newItem);
      localStorage.setItem(storageKey, JSON.stringify(items));
    
      renderItemizedItems(items);
      $('#itemName, #unitCost, #quantity, #totalCost, #spentOn, #measureId').val('');
      showMessage('success', 'Item added successfully.');
    });
    
    $(document).on('click', '.remove-item-btn', function () {
      const idx = $(this).data('index');
      const requestId = $('#itemizeRequestId').val();
      const storageKey = `itemized_items_${requestId}`;
    
      let items = [];
      try {
        items = JSON.parse(localStorage.getItem(storageKey)) || [];
      } catch {
        items = [];
      }
    
      if (idx >= 0 && idx < items.length) {
        items.splice(idx, 1);
        localStorage.setItem(storageKey, JSON.stringify(items));
        renderItemizedItems(items);
        showMessage('error', 'Item removed.');
      }
    });
    
    $('#clearItemsBtn').click(function () {
      const requestId = $('#itemizeRequestId').val();
      const storageKey = `itemized_items_${requestId}`;
      localStorage.removeItem(storageKey);
      renderItemizedItems([]);
      showMessage('error', 'All items cleared.');
    });
    
    $('#saveItemsBtn').click(function () {
      const requestId = $('#itemizeRequestId').val();
      const target = $('#itemizeTarget').val();
      const storageKey = `itemized_items_${requestId}`;
    
      let items = [];
      try {
        items = JSON.parse(localStorage.getItem(storageKey)) || [];
      } catch {
        items = [];
      }
    
      if (!items.length) {
        showMessage('error', 'No items to save.');
        return;
      }
    
      const btn = $(this);
      btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
      $('#message').html('<div class="response-message"><i class="fas fa-spinner fa-spin icon"></i> Sending Request...</div>');
    
      $.ajax({
        type: 'POST',
        url: '/api/records/record_approved_expense_request_items.php',
        contentType: 'application/json',
        dataType: 'json',
        data: JSON.stringify({
          request_id: requestId,
          target: target,
          items: items
        }),
        success(response) {
          if (response.success) {
            showMessage('success', response.message || 'Items saved successfully.');
            localStorage.removeItem(storageKey);
            renderItemizedItems([]);
          } else {
            showMessage('error', response.message || 'Save failed.');
          }
        },
        error(xhr) {
          const err = xhr.responseJSON?.message || 'Failed to save items to the server.';
          showMessage('error', err);
        },
        complete() {
          btn.prop('disabled', false).html('Save Items');
        }
      });
    });

  
    let activeTab = 'head-parish';
    let currentPage = 1;

    const fetchExpenses = (target, page = 1, query = '') => {
        $('#loading').show();
        let url = `../api/data/expense_requests?target=${target}&page=${page}&query=${query}`;
        $.ajax({
            type: 'GET',
            url: url,
            dataType: 'json',
            success: function (response) {
                let expenseList = '';
                let listId = '';
                const userRole = response.target_role;
                function parseAmount(formattedAmount) {
                    // Remove commas, trim whitespace, convert to float
                    return parseFloat(formattedAmount.replace(/,/g, '').trim());
                }
                // Determine the target and create the corresponding expense list
                switch (target) {
                    case 'head-parish':
                        listId = '#headParishExpenseList';
                        expenseList = response.data.map((expense, index) => `
                            <tr>
                                <td>${(page - 1) * 5 + index + 1}</td>
                                <td>${expense.expense_group_name}</td>
                                <td>${expense.expense_name}</td>
                                <td>TZS ${expense.request_amount}</td>
                                <td>${expense.request_datetime}</td>
                                <td style="color: ${expense.chairperson_approval ? 'green' : (expense.chairperson_approval == false ? 'red' : 'gray')}">
                                    ${expense.chairperson_approval ? 'Approved' : (expense.chairperson_approval == false ? 'Rejected' : 'Pending')}
                                    ${expense.chairperson_approval_datetime ? `<br>(${expense.chairperson_approval_datetime})` : ''}
                                    ${expense.chairperson_rejection_remarks && expense.chairperson_approval == false ? `<br>Remarks: ${expense.chairperson_rejection_remarks}` : ''}
                                </td>
                                <td style="color: ${expense.pastor_approval ? 'green' : (expense.pastor_approval == false ? 'red' : 'gray')}">
                                    ${expense.pastor_approval ? 'Approved' : (expense.pastor_approval == false ? 'Rejected' : 'Pending')}
                                    ${expense.pastor_approval_datetime ? `<br>(${expense.pastor_approval_datetime})` : ''}
                                    ${expense.accountant_rejection_remarks && expense.pastor_approval == false ? `<br>Remarks: ${expense.accountant_rejection_remarks}` : ''}
                                </td>
                                <td class="text-nowrap">
                                    <div class="d-flex align-items-center gap-2">
                                        <button class="btn btn-sm btn-outline-primary preview-btn"
                                            data-bs-toggle="modal" title="Preview"
                                            data-bs-target="#containerModal"
                                            data-id="${expense.request_id}"
                                            data-target="${target}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                
                                        ${(userRole === 'chairperson' || userRole === 'pastor') ? `
                                            <button class="btn btn-sm btn-outline-success respond-btn"
                                                data-bs-toggle="tooltip" title="Respond"
                                                data-id="${expense.request_id}"
                                                data-target="${target}"
                                                data-amount="${parseAmount(expense.request_amount)}">
                                                <i class="fas fa-reply"></i>
                                            </button>` : ''}
                                        
                                        ${(expense.pastor_approval && expense.chairperson_approval) ? `
                                        <button class="btn btn-sm btn-outline-warning itemize-btn"
                                            data-bs-toggle="modal" title="Itemize Expenses"
                                            data-bs-target="#itemizeModal"
                                            data-request-id="${expense.request_id}"
                                            data-target="${target}">
                                            <i class="fas fa-list"></i>
                                        </button>` : ''}
                                        
                                        ${(expense.pastor_approval && expense.chairperson_approval) ? `
                                          <a href="/reports/expense_request_report.php?request_id=${expense.request_id}&target=${target}" 
                                             target="_blank" 
                                             class="btn btn-sm btn-outline-danger ms-1" 
                                             title="Download PDF Report"
                                             role="button">
                                             <i class="fas fa-file-pdf"></i>
                                          </a>
                                        ` : ''}
                                    </div>
                                </td>

                            </tr>
                        `).join('');
                        break;

                    case 'sub-parish':
                        listId = '#subParishExpenseList';
                        expenseList = response.data.map((expense, index) => `
                            <tr>
                                <td>${(page - 1) * 5 + index + 1}</td>
                                <td>${expense.sub_parish_name}</td>
                                <td>${expense.expense_group_name}</td>
                                <td>${expense.expense_name}</td>
                                <td>TZS ${expense.request_amount}</td>
                                <td>${expense.request_datetime}</td>
                                <td style="color: ${expense.chairperson_approval ? 'green' : (expense.chairperson_approval == false ? 'red' : 'gray')}">
                                    ${expense.chairperson_approval ? 'Approved' : (expense.chairperson_approval == false ? 'Rejected' : 'Pending')}
                                    ${expense.chairperson_approval_datetime ? `<br>(${expense.chairperson_approval_datetime})` : ''}
                                    ${expense.chairperson_rejection_remarks && expense.chairperson_approval == false ? `<br>Remarks: ${expense.chairperson_rejection_remarks}` : ''}
                                </td>
                                <td style="color: ${expense.pastor_approval ? 'green' : (expense.pastor_approval == false ? 'red' : 'gray')}">
                                    ${expense.pastor_approval ? 'Approved' : (expense.pastor_approval == false ? 'Rejected' : 'Pending')}
                                    ${expense.pastor_approval_datetime ? `<br>(${expense.pastor_approval_datetime})` : ''}
                                    ${expense.accountant_rejection_remarks && expense.pastor_approval == false ? `<br>Remarks: ${expense.accountant_rejection_remarks}` : ''}
                                </td>
                                 <td class="text-nowrap">
                                    <div class="d-flex align-items-center gap-2">
                                        <button class="btn btn-sm btn-outline-primary preview-btn"
                                            data-bs-toggle="modal" title="Preview"
                                            data-bs-target="#containerModal"
                                            data-id="${expense.request_id}"
                                            data-target="${target}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                
                                        ${(userRole === 'chairperson' || userRole === 'pastor') ? `
                                            <button class="btn btn-sm btn-outline-success respond-btn"
                                                data-bs-toggle="tooltip" title="Respond"
                                                data-id="${expense.request_id}"
                                                data-target="${target}"
                                                data-amount="${parseAmount(expense.request_amount)}">
                                                <i class="fas fa-reply"></i>
                                            </button>` : ''}
                                        
                                        ${(expense.pastor_approval && expense.chairperson_approval) ? `
                                        <button class="btn btn-sm btn-outline-warning itemize-btn"
                                            data-bs-toggle="modal" title="Itemize Expenses"
                                            data-bs-target="#itemizeModal"
                                            data-request-id="${expense.request_id}"
                                            data-target="${target}">
                                            <i class="fas fa-list"></i>
                                        </button>` : ''}
                                        
                                        ${(expense.pastor_approval && expense.chairperson_approval) ? `
                                          <a href="/reports/expense_request_report.php?request_id=${expense.request_id}&target=${target}" 
                                             target="_blank" 
                                             class="btn btn-sm btn-outline-danger ms-1" 
                                             title="Download PDF Report"
                                             role="button">
                                             <i class="fas fa-file-pdf"></i>
                                          </a>
                                        ` : ''}
                                    </div>
                                </td>
                            </tr>
                        `).join('');
                        break;

                    case 'community':
                        listId = '#communityExpenseList';
                        expenseList = response.data.map((expense, index) => `
                            <tr>
                                <td>${(page - 1) * 5 + index + 1}</td>
                                <td>${expense.community_name}</td>
                                <td>${expense.expense_group_name}</td>
                                <td>${expense.expense_name}</td>
                                <td>TZS ${expense.request_amount}</td>
                                <td>${expense.request_datetime}</td>
                                <td style="color: ${expense.chairperson_approval ? 'green' : (expense.chairperson_approval == false ? 'red' : 'gray')}">
                                    ${expense.chairperson_approval ? 'Approved' : (expense.chairperson_approval == false ? 'Rejected' : 'Pending')}
                                    ${expense.chairperson_approval_datetime ? `<br>(${expense.chairperson_approval_datetime})` : ''}
                                    ${expense.chairperson_rejection_remarks && expense.chairperson_approval == false ? `<br>Remarks: ${expense.chairperson_rejection_remarks}` : ''}
                                </td>
                                <td style="color: ${expense.pastor_approval ? 'green' : (expense.pastor_approval == false ? 'red' : 'gray')}">
                                    ${expense.pastor_approval ? 'Approved' : (expense.pastor_approval == false ? 'Rejected' : 'Pending')}
                                    ${expense.pastor_approval_datetime ? `<br>(${expense.pastor_approval_datetime})` : ''}
                                    ${expense.accountant_rejection_remarks && expense.pastor_approval == false ? `<br>Remarks: ${expense.accountant_rejection_remarks}` : ''}
                                </td>
                                <td class="text-nowrap">
                                    <div class="d-flex align-items-center gap-2">
                                        <button class="btn btn-sm btn-outline-primary preview-btn"
                                            data-bs-toggle="modal" title="Preview"
                                            data-bs-target="#containerModal"
                                            data-id="${expense.request_id}"
                                            data-target="${target}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                
                                        ${(userRole === 'chairperson' || userRole === 'pastor') ? `
                                            <button class="btn btn-sm btn-outline-success respond-btn"
                                                data-bs-toggle="tooltip" title="Respond"
                                                data-id="${expense.request_id}"
                                                data-target="${target}"
                                                data-amount="${parseAmount(expense.request_amount)}">
                                                <i class="fas fa-reply"></i>
                                            </button>` : ''}
                                            
                                        ${(expense.pastor_approval && expense.chairperson_approval) ? `
                                        <button class="btn btn-sm btn-outline-warning itemize-btn"
                                            data-bs-toggle="modal" title="Itemize Expenses"
                                            data-bs-target="#itemizeModal"
                                            data-request-id="${expense.request_id}"
                                            data-target="${target}">
                                            <i class="fas fa-list"></i>
                                        </button>` : ''}
                                        
                                        ${(expense.pastor_approval && expense.chairperson_approval) ? `
                                          <a href="/reports/expense_request_report.php?request_id=${expense.request_id}&target=${target}" 
                                             target="_blank" 
                                             class="btn btn-sm btn-outline-danger ms-1" 
                                             title="Download PDF Report"
                                             role="button">
                                             <i class="fas fa-file-pdf"></i>
                                          </a>
                                        ` : ''}
                                    </div>
                                </td>
                            </tr>
                        `).join('');
                        break;

                    case 'group':
                        listId = '#groupExpenseList';
                        expenseList = response.data.map((expense, index) => `
                            <tr>
                                <td>${(page - 1) * 5 + index + 1}</td>
                                <td>${expense.group_name}</td>
                                <td>${expense.expense_group_name}</td>
                                <td>${expense.expense_name}</td>
                                <td>TZS ${expense.request_amount}</td>
                                <td>${expense.request_datetime}</td>
                                <td style="color: ${expense.chairperson_approval ? 'green' : (expense.chairperson_approval == false ? 'red' : 'gray')}">
                                    ${expense.chairperson_approval ? 'Approved' : (expense.chairperson_approval == false ? 'Rejected' : 'Pending')}
                                    ${expense.chairperson_approval_datetime ? `<br>(${expense.chairperson_approval_datetime})` : ''}
                                    ${expense.chairperson_rejection_remarks && expense.chairperson_approval == false ? `<br>Remarks: ${expense.chairperson_rejection_remarks}` : ''}
                                </td>
                                <td style="color: ${expense.pastor_approval ? 'green' : (expense.pastor_approval == false ? 'red' : 'gray')}">
                                    ${expense.pastor_approval ? 'Approved' : (expense.pastor_approval == false ? 'Rejected' : 'Pending')}
                                    ${expense.pastor_approval_datetime ? `<br>(${expense.pastor_approval_datetime})` : ''}
                                    ${expense.accountant_rejection_remarks && expense.pastor_approval == false ? `<br>Remarks: ${expense.accountant_rejection_remarks}` : ''}
                                </td>
                                <td class="text-nowrap">
                                    <div class="d-flex align-items-center gap-2">
                                        <button class="btn btn-sm btn-outline-primary preview-btn"
                                            data-bs-toggle="modal" title="Preview"
                                            data-bs-target="#containerModal"
                                            data-id="${expense.request_id}"
                                            data-target="${target}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                
                                        ${(userRole === 'chairperson' || userRole === 'pastor') ? `
                                            <button class="btn btn-sm btn-outline-success respond-btn"
                                                data-bs-toggle="tooltip" title="Respond"
                                                data-id="${expense.request_id}"
                                                data-target="${target}"
                                                data-amount="${parseAmount(expense.request_amount)}">
                                                <i class="fas fa-reply"></i>
                                            </button>` : ''}
                                            
                                        ${(expense.pastor_approval && expense.chairperson_approval) ? `
                                        <button class="btn btn-sm btn-outline-warning itemize-btn"
                                            data-bs-toggle="modal" title="Itemize Expenses"
                                            data-bs-target="#itemizeModal"
                                            data-request-id="${expense.request_id}"
                                            data-target="${target}">
                                            <i class="fas fa-list"></i>
                                        </button>` : ''}
                                        
                                        ${(expense.pastor_approval && expense.chairperson_approval) ? `
                                          <a href="/reports/expense_request_report.php?request_id=${expense.request_id}&target=${target}" 
                                             target="_blank" 
                                             class="btn btn-sm btn-outline-danger ms-1" 
                                             title="Download PDF Report"
                                             role="button">
                                             <i class="fas fa-file-pdf"></i>
                                          </a>
                                        ` : ''}
                                    </div>
                                </td>
                            </tr>
                        `).join('');
                        break;

                    default:
                        break;
                }

                // Populate the appropriate expense list
                $(listId).html(expenseList);

                // Generate pagination based on total pages
                $('#paginationNav .pagination').empty();
                for (let i = 1; i <= response.total_pages; i++) {
                    $('#paginationNav .pagination').append(`
                        <li class="page-item ${i == page ? 'active' : ''}">
                            <a class="page-link" href="#">${i}</a>
                        </li>
                    `);
                }

                $('#loading').hide();
            },
            error: function (xhr, status, error) {
                console.log('Error fetching expense data:', error);
                $('#loading').hide();
            }
        });
    };


    fetchExpenses(activeTab);

    $('#expenseTabs button').on('click', function () {
        activeTab = $(this).data('bs-target').replace('#', '');
        currentPage = 1;
        fetchExpenses(activeTab);
    });

 
    // Search functionality
    $('#searchBtn').on('click', function () {
        const query = $('#searchExpense').val();
        currentPage = 1; // Reset to the first page on search
        fetchExpenses(activeTab, currentPage, query);
    });

    // Attach onchange event to search input
    $('#searchExpense').on('input', function () {
        const query = $(this).val();
        currentPage = 1; // Reset to the first page on search
        fetchExpenses(activeTab, currentPage, query);
    });

    // Pagination click event
    $(document).on('click', '#paginationNav .page-link', function (e) {
        e.preventDefault();
        currentPage = Number($(this).text());
        const query = $('#searchExpense').val();
        fetchExpenses(activeTab, currentPage, query); 
    });

    
});

// Show Respond modal on Respond button click
$(document).on('click', '.respond-btn', function () {
    const requestId = $(this).data('id');
    const target = $(this).data('target');
    const requestedAmount = $(this).data('amount'); 
    $('#respondRequestId').val(requestId);
    $('#respondTarget').val(target);

    $('#respondForm')[0].reset();
    $('#rejectionReasonDiv').hide();
    $('#adjustedAmountDiv').show(); 
    $('#responseMessage').html('');

    // Optional: auto-fill approved amount with original requested amount
    $('#adjustedAmount').val(requestedAmount || '');

    $('#containerModal').modal('hide');
    $('#respondModal').modal('show');
});

// Toggle rejection reason and approved amount visibility
$(document).on('change', 'input[name="approval"]', function () {
    const isReject = $(this).val() === 'reject';
    $('#rejectionReasonDiv').toggle(isReject);
    $('#adjustedAmountDiv').toggle(!isReject);
});

// Handle form submission with message feedback
$(document).on('submit', '#respondForm', function (e) {
    e.preventDefault();
    $('#responseMessage').html('<div class="response-message mb-2"><i class="fas fa-spinner fa-spin icon"></i> Sending Request...</div>');

    const requestId = $('#respondRequestId').val();
    const target = $('#respondTarget').val();
    const approval = $('input[name="approval"]:checked').val();
    const rejectionReason = $('#rejectionReason').val().trim();
    const approvedAmount = parseFloat($('#adjustedAmount').val()) || null;

    if (approval === 'reject' && rejectionReason === '') {
        $('#responseMessage').html(
            '<div class="response-message error"><i class="fas fa-times-circle icon"></i> Please provide a rejection reason.</div>'
        );
        return;
    }

    if (approval === 'approve' && (!approvedAmount || approvedAmount <= 0)) {
        $('#responseMessage').html(
            '<div class="response-message error"><i class="fas fa-times-circle icon"></i> Please enter a valid approved amount.</div>'
        );
        return;
    }

    const payload = {
        request_id: requestId,
        target: target,
        approval: approval === 'approve',
        rejection_reason: rejectionReason,
        approved_amount: approval === 'approve' ? approvedAmount : null
    };

    $.ajax({
        type: 'POST',
        url: '../api/data/respond_to_expense_request',
        data: JSON.stringify(payload),
        contentType: 'application/json',
        success: function (response) {
            if (response.success) {
                $('#responseMessage').html(
                    '<div class="response-message success"><i class="fas fa-check-circle icon"></i> ' + response.message + '</div>'
                );

                setTimeout(() => {
                    $('#responseMessage').html('');
                    $('#respondModal').modal('hide');
                    fetchExpenses(target, currentPage);
                }, 2000);
            } else {
                $('#responseMessage').html(
                    '<div class="response-message error"><i class="fas fa-times-circle icon"></i> ' + (response.message || 'An error occurred') + '</div>'
                );
            }
        },
        error: function () {
            $('#responseMessage').html(
                '<div class="response-message error"><i class="fas fa-times-circle icon"></i> Error submitting response.</div>'
            );
        }
    });
});


// Event listener for the "Preview" button click
$(document).on('click', '.preview-btn', function () {
    // Extract expense ID and active tab from button data attributes
    const expenseId = $(this).data('id');
    const activeTab = $(this).data('target');

    // Construct the API URL for fetching expense details
    const url = `../api/data/expense_request?id=${expenseId}&target=${activeTab}`;

    // Fetch expense details via AJAX
    $.ajax({
        type: 'GET',
        url: url,
        dataType: 'json',
        success: function (response) {
            // Check if the response is successful
            if (response.success) {
                const data = response.data; // Extract data from the response

                // Initialize HTML structure for the details using Bootstrap
                let detailsHtml = `
                    <div class="container my-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="bg-light border rounded p-4 shadow-sm mb-4">
                                    <h4 class="text-primary">Request Overview</h4>
                                    <div class="mb-3"><strong>Description:</strong> <span>${data.request_description}</span></div>
                                    <div class="mb-3"><strong>Expense Group:</strong> <span>${data.expense_group_name}</span></div>
                                    <div class="mb-3"><strong>Expense Name:</strong> <span>${data.expense_name || 'N/A'}</span></div>
                                    <div class="mb-3"><strong>Amount:</strong> <span>TZS ${data.request_amount}</span></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-light border rounded p-4 shadow-sm mb-4">
                                    <h4 class="text-primary">Approval Status</h4>
                                    <div class="mb-3">
                                        <strong>Pastor Approval:</strong>
                                        <span class="${data.pastor_approval == true ? 'text-success' : (data.pastor_approval == false ? 'text-danger' : 'text-warning')}">
                                            <i class="${data.pastor_approval == true ? 'fas fa-check-circle' : (data.pastor_approval == false ? 'fas fa-times-circle' : 'fas fa-clock')}"></i>
                                            ${data.pastor_approval == true ? ' Approved' : (data.pastor_approval == false ? ' Rejected' : ' Pending')}
                                        </span>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Chairperson Approval:</strong>
                                        <span class="${data.chairperson_approval == true ? 'text-success' : (data.chairperson_approval == false ? 'text-danger' : 'text-warning')}">
                                            <i class="${data.chairperson_approval == true ? 'fas fa-check-circle' : (data.chairperson_approval == false ? 'fas fa-times-circle' : 'fas fa-clock')}"></i>
                                            ${data.chairperson_approval == true ? ' Approved' : (data.chairperson_approval == false ? ' Rejected' : ' Pending')}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="bg-light border rounded p-4 shadow-sm">
                                    <h4 class="text-primary">Additional Information</h4>
                `;

                // Add additional fields based on the active tab
                switch (activeTab) {
                    case 'sub-parish':
                        detailsHtml += `<div class="mb-3"><strong>Sub-Parish:</strong> <span>${data.sub_parish_name || 'N/A'}</span></div>`;
                        break;
                    case 'community':
                        detailsHtml += `<div class="mb-3"><strong>Community:</strong> <span>${data.community_name || 'N/A'}</span></div>`;
                        break;
                    case 'group':
                        detailsHtml += `<div class="mb-3"><strong>Group:</strong> <span>${data.group_name || 'N/A'}</span></div>`;
                        break;
                    case 'head-parish':
                        detailsHtml += `<div class="mb-3"><strong>Head Parish:</strong> <span>${data.head_parish_name || 'N/A'}</span></div>`;
                        break;
                    default:
                        break;
                }

                // Close the HTML structure for the details
                detailsHtml += `</div></div></div>`;

                // Append summary data if available
                const d = response.summary;
                if (d) {
                    detailsHtml += `
                        <div class="bg-white border rounded p-4 shadow-sm mt-4">
                            <h4 class="text-primary">Summary Information</h4>
                            <p class="card-text mb-3"><strong>Year:</strong> ${d.year} &nbsp; | &nbsp; <strong>Quarter:</strong> ${d.quarter}</p>
                
                            <div class="row mb-3 text-white">
                                <div class="col-md-4 mb-2 mb-md-0">
                                    <div class="p-3 bg-primary rounded">
                                        <strong>Annual Budget</strong>
                                        <div class="fs-5">${Number(d.annual_budget).toLocaleString()}</div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2 mb-md-0">
                                    <div class="p-3 bg-danger rounded">
                                        <strong>Annual Expense</strong>
                                        <div class="fs-5">${Number(d.annual_expense).toLocaleString()}</div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2 mb-md-0">
                                    <div class="p-3 bg-success rounded">
                                        <strong>Annual Balance</strong>
                                        <div class="fs-5">${Number(d.annual_balance).toLocaleString()}</div>
                                    </div>
                                </div>
                            </div>
                
                            <div class="row text-white">
                                <div class="col-md-4 mb-2 mb-md-0">
                                    <div class="p-3 bg-primary rounded">
                                        <strong>Quarter Budget</strong>
                                        <div class="fs-5">${Number(d.quarter_budget).toLocaleString()}</div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2 mb-md-0">
                                    <div class="p-3 bg-danger rounded">
                                        <strong>Quarter Expense</strong>
                                        <div class="fs-5">${Number(d.quarter_expense).toLocaleString()}</div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2 mb-md-0">
                                    <div class="p-3 bg-success rounded">
                                        <strong>Quarter Balance</strong>
                                        <div class="fs-5">${Number(d.quarter_balance).toLocaleString()}</div>
                                    </div>
                                </div>
                            </div>
                                <div class="row" style="margin-top: 15px;">
                                  <div class="col-md-12">
                                    <div style="border: 1px solid #f0ad4e; padding: 8px 12px; border-radius: 5px; color: #856404; font-weight: 600; font-size: 0.95rem; width: 100%;">
                                      Pending Requests Amount for This Quarter: <span style="color: #d48806;">${d.quarter_pending.toLocaleString()}</span>
                                    </div>
                                  </div>
                                </div>
                        </div>
                    `;
                }
                // Populate the modal with the fetched details
                $('#container-items').html(detailsHtml);
            } else {
                // Handle API response failure
                $('#container-items').html(`<p class="text-danger">${response.message}</p>`);
            }
        },
        error: function () {
            // Handle AJAX request failure
            $('#container-items').html('<p class="text-danger">Failed to load expense details.</p>');
        }
    });
});

  </script>
</body>

</html>
