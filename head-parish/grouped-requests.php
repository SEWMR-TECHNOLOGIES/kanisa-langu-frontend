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
            <h5 class="card-title fw-semibold mb-4">Grouped Expense Requests</h5>
            
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
                <button class="nav-link active" id="grouped-head-parish-tab" data-bs-toggle="tab" data-bs-target="#grouped-head-parish" role="tab" aria-controls="grouped-head-parish" aria-selected="true">Head Parish</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="grouped-sub-parish-tab" data-bs-toggle="tab" data-bs-target="#grouped-sub-parish" role="tab" aria-controls="grouped-sub-parish" aria-selected="false">Sub Parish</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="grouped-community-tab" data-bs-toggle="tab" data-bs-target="#grouped-community" role="tab" aria-controls="grouped-community" aria-selected="false">Community</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="grouped-group-tab" data-bs-toggle="tab" data-bs-target="#grouped-group" role="tab" aria-controls="grouped-group" aria-selected="false">Group</button>
              </li>
            </ul>

            <!-- Grouped Expense Requests Tabs -->
            <div class="tab-content mt-4">
              <!-- Head Parish Grouped -->
              <div class="tab-pane fade show active" id="grouped-head-parish" role="tabpanel" aria-labelledby="grouped-head-parish-tab">
                <div class="table-responsive">
                  <table class="table table-hover">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Description</th>
                        <th>Request Date</th>
                        <th>Account Name</th>
                        <th class="text-end">Actions</th>
                      </tr>
                    </thead>
                    <tbody id="groupedHeadParishList"></tbody>
                  </table>
                </div>
              </div>
            
              <!-- Sub Parish Grouped -->
              <div class="tab-pane fade" id="grouped-sub-parish" role="tabpanel" aria-labelledby="grouped-sub-parish-tab">
                <div class="table-responsive">
                  <table class="table table-hover">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Sub Parish</th>
                        <th>Description</th>
                        <th>Request Date</th>
                        <th>Account Name</th>
                        <th class="text-end">Actions</th>
                      </tr>
                    </thead>
                    <tbody id="groupedSubParishList"></tbody>
                  </table>
                </div>
              </div>
            
              <!-- Community Grouped -->
              <div class="tab-pane fade" id="grouped-community" role="tabpanel" aria-labelledby="grouped-community-tab">
                <div class="table-responsive">
                  <table class="table table-hover">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Community</th>
                        <th>Description</th>
                        <th>Request Date</th>
                        <th>Account Name</th>
                        <th class="text-end">Actions</th>
                      </tr>
                    </thead>
                    <tbody id="groupedCommunityList"></tbody>
                  </table>
                </div>
              </div>
            
              <!-- Group Grouped -->
              <div class="tab-pane fade" id="grouped-group" role="tabpanel" aria-labelledby="grouped-group-tab">
                <div class="table-responsive">
                  <table class="table table-hover">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Group</th>
                        <th>Description</th>
                        <th>Request Date</th>
                        <th>Account Name</th>
                        <th class="text-end">Actions</th>
                      </tr>
                    </thead>
                    <tbody id="groupedGroupList"></tbody>
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

<!-- Modal HTML for displaying grouped-expense details -->
<div class="modal fade" id="containerModal" tabindex="-1" aria-labelledby="containerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="containerModalLabel">Grouped Expense Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div id="container-items"><!-- overview + summary go here --></div>

        <!-- items list -->
        <div class="mt-4">
          <h5>Expense Items</h5>
          <div class="table-responsive">
            <table class="table table-sm table-bordered">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Group</th>
                  <th>Name</th>
                  <th>Amount</th>
                  <th class="text-end">Remove</th>
                </tr>
              </thead>
              <tbody id="groupedItemsList"></tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="mx-3 mb-2" id="responseMessage"></div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>






  <?php require_once('components/footer_files.php') ?>

  <script>
    $(document).ready(function () {
    
      // Show messages in a consistent style with icon and auto-clear
      function showMessage(type, message, targetSelector = '#responseMessage', timeout = 3000) {
        const icons = {
          success: 'check-circle',
          error: 'times-circle',
          info: 'spinner fa-spin'
        };
        const iconClass = icons[type] || 'info';
        $(targetSelector).html(
          `<div class="response-message ${type}">
            <i class="fas fa-${iconClass} icon"></i> ${message}
          </div>`
        );
        if (timeout) {
          setTimeout(() => $(targetSelector).html(''), timeout);
        }
      }
    
      // Load grouped expense items into the modal table
      function loadGroupedItems(groupedId, target) {
        $.getJSON(`../api/data/grouped_expense_request.php?id=${groupedId}&target=${target}`, resp => {
          if (!resp.success) {
            $('#groupedItemsList').html(`<tr><td colspan="5" class="text-danger">${resp.message}</td></tr>`);
            return;
          }
          const rows = resp.data.map((it, i) => `
            <tr>
              <td>${i + 1}</td>
              <td>${it.expense_group_name}</td>
              <td>${it.expense_name}</td>
              <td>TZS ${it.request_amount}</td>
              <td class="text-end">
                <button class="btn btn-sm btn-danger remove-item-btn" data-id="${it.request_id}" data-target="${target}">
                  Remove
                </button>
              </td>
            </tr>
          `).join('');
          $('#groupedItemsList').html(rows);
        });
      }
    
      // Remove item button click handler
      $(document).on('click', '.remove-item-btn', function () {
        const requestId = $(this).data('id');
        const target = $(this).data('target');
    
        showMessage('info', 'Removing...', '#responseMessage', 0); // 0 = don't auto-clear here
    
        $.ajax({
          url: '../api/records/remove_expense_item.php',
          method: 'POST',
          contentType: 'application/json',
          data: JSON.stringify({ request_id: requestId, target }),
          success(response) {
            if (response.success) {
              showMessage('success', response.message, '#responseMessage');
    
              // Reload items after a short delay so user can see success message
              setTimeout(() => {
                $('#responseMessage').html('');
                // Use data attribute on modal to get groupedExpenseId
                const groupedExpenseId = $('#containerModal').data('grouped-expense-id');
                if (groupedExpenseId) {
                  loadGroupedItems(groupedExpenseId, target);
                }
              }, 1500);
    
            } else {
              showMessage('error', response.message || 'An error occurred', '#responseMessage');
            }
          },
          error() {
            showMessage('error', 'Error removing item.', '#responseMessage');
          }
        });
      });
    
      // Variables for current state
      let activeTab = 'head-parish';
      let currentPage = 1;
    
      // Fetch and render grouped expense requests in tables by target
      function fetchGroupedExpenses(target, page = 1, query = '') {
        $('#loading').show();
    
        const url = `../api/data/grouped_expense_requests?target=${target}&page=${page}&query=${query}`;
        $.ajax({
          type: 'GET',
          url: url,
          dataType: 'json',
          success: function (response) {
            let tableBodyId = '';
            switch (target) {
              case 'head-parish': tableBodyId = '#groupedHeadParishList'; break;
              case 'sub-parish': tableBodyId = '#groupedSubParishList'; break;
              case 'community': tableBodyId = '#groupedCommunityList'; break;
              case 'group': tableBodyId = '#groupedGroupList'; break;
            }
    
            const rows = response.data.map((item, index) => `
              <tr>
                <td>${(page - 1) * 5 + index + 1}</td>
                ${target === 'head-parish' ? '' : (item.location_name ? `<td>${item.location_name}</td>` : '')}
                <td>${item.description}</td>
                <td>${item.submission_datetime}</td>
                <td>${item.account_name}</td>
                <td class="text-nowrap text-end">
                  <button class="btn btn-sm btn-outline-primary preview-grouped-btn"
                          data-id="${item.grouped_request_id}"
                          data-target="${target}"
                          title="Preview">
                    <i class="fas fa-eye"></i>
                  </button>
                  <a href="/reports/grouped_expense_report.php?grouped_request_id=${item.grouped_request_id}&target=${target}"
                     target="_blank"
                     class="btn btn-sm btn-outline-danger ms-1"
                     title="Print">
                     <i class="fas fa-print"></i>
                  </a>
                </td>
              </tr>
            `).join('');
    
            $(tableBodyId).html(rows);
    
            // Pagination
            $('#paginationNav .pagination').empty();
            for (let i = 1; i <= response.total_pages; i++) {
              $('#paginationNav .pagination').append(`
                <li class="page-item ${i === page ? 'active' : ''}">
                  <a class="page-link" href="#">${i}</a>
                </li>
              `);
            }
    
            $('#loading').hide();
          },
          error: function (xhr, status, error) {
            console.error('Error loading grouped expenses:', error);
            $('#loading').hide();
          }
        });
      }
    
      // Initial fetch
      fetchGroupedExpenses(activeTab);
    
      // Tab change handler
      $('#expenseTabs button').on('click', function () {
        activeTab = $(this).data('bs-target').replace('#', '');
        currentPage = 1;
        fetchGroupedExpenses(activeTab);
      });
    
      // Search button click
      $('#searchBtn').on('click', function () {
        const query = $('#searchExpense').val();
        currentPage = 1;
        fetchGroupedExpenses(activeTab, currentPage, query);
      });
    
      // Search input on typing
      $('#searchExpense').on('input', function () {
        const query = $(this).val();
        currentPage = 1;
        fetchGroupedExpenses(activeTab, currentPage, query);
      });
    
      // Pagination click handler
      $(document).on('click', '#paginationNav .page-link', function (e) {
        e.preventDefault();
        currentPage = Number($(this).text());
        const query = $('#searchExpense').val();
        fetchGroupedExpenses(activeTab, currentPage, query);
      });
    
     $(document).on('click', '.preview-grouped-btn', function () {
      const groupedExpenseId = $(this).data('id');
      const target = $(this).data('target');
    
      // Save grouped expense ID on the modal if you need it later
      $('#containerModal').data('grouped-expense-id', groupedExpenseId);
    
      // Clear previous contents
      $('#groupedItemsList').html('');
      $('#responseMessage').html('');
    
      // Load grouped expense items only (API call inside this function)
      loadGroupedItems(groupedExpenseId, target);
    
      // Show the modal
      const modalEl = document.getElementById('containerModal');
      const modal = new bootstrap.Modal(modalEl);
      modal.show();
    });


    });
    </script>

</body>

</html>
