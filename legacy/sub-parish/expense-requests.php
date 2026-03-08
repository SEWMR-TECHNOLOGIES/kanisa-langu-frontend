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
                        <th>Accountant Approval</th>
                        <th>Chairperson Approval</th>
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
                        <th>Accountant Approval</th>
                        <th>Chairperson Approval</th>
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
                        <th>Accountant Approval</th>
                        <th>Chairperson Approval</th>
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
                        <th>Accountant Approval</th>
                        <th>Chairperson Approval</th>
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
              <ul class="pagination justify-content-center">
                <!-- Pagination buttons will be generated dynamically -->
              </ul>
            </nav>

          </div>
        </div>
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

  <?php require_once('components/footer_files.php') ?>

  <script>
$(document).ready(function () {
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
                                <td style="color: ${expense.accountant_approval ? 'green' : (expense.accountant_approval == false ? 'red' : 'gray')}">
                                    ${expense.accountant_approval ? 'Approved' : (expense.accountant_approval == false ? 'Rejected' : 'Pending')}
                                    ${expense.accountant_approval_datetime ? `<br>(${expense.accountant_approval_datetime})` : ''}
                                    ${expense.accountant_rejection_remarks && expense.accountant_approval == false ? `<br>Remarks: ${expense.accountant_rejection_remarks}` : ''}
                                </td>
                                <td style="color: ${expense.chairperson_approval ? 'green' : (expense.chairperson_approval == false ? 'red' : 'gray')}">
                                    ${expense.chairperson_approval ? 'Approved' : (expense.chairperson_approval == false ? 'Rejected' : 'Pending')}
                                    ${expense.chairperson_approval_datetime ? `<br>(${expense.chairperson_approval_datetime})` : ''}
                                    ${expense.chairperson_rejection_remarks && expense.chairperson_approval == false ? `<br>Remarks: ${expense.chairperson_rejection_remarks}` : ''}
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary preview-btn" data-bs-toggle="modal" data-bs-target="#containerModal" data-id="${expense.request_id}" data-target="${target}">Preview</button>
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
                                <td style="color: ${expense.accountant_approval ? 'green' : (expense.accountant_approval == false ? 'red' : 'gray')}">
                                    ${expense.accountant_approval ? 'Approved' : (expense.accountant_approval == false ? 'Rejected' : 'Pending')}
                                    ${expense.accountant_approval_datetime ? `<br>(${expense.accountant_approval_datetime})` : ''}
                                    ${expense.accountant_rejection_remarks && expense.accountant_approval == false ? `<br>Remarks: ${expense.accountant_rejection_remarks}` : ''}
                                </td>
                                <td style="color: ${expense.chairperson_approval ? 'green' : (expense.chairperson_approval == false ? 'red' : 'gray')}">
                                    ${expense.chairperson_approval ? 'Approved' : (expense.chairperson_approval == false ? 'Rejected' : 'Pending')}
                                    ${expense.chairperson_approval_datetime ? `<br>(${expense.chairperson_approval_datetime})` : ''}
                                    ${expense.chairperson_rejection_remarks && expense.chairperson_approval == false ? `<br>Remarks: ${expense.chairperson_rejection_remarks}` : ''}
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary preview-btn" data-bs-toggle="modal" data-bs-target="#containerModal" data-id="${expense.request_id}" data-target="${target}">Preview</button>
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
                                <td style="color: ${expense.accountant_approval ? 'green' : (expense.accountant_approval == false ? 'red' : 'gray')}">
                                    ${expense.accountant_approval ? 'Approved' : (expense.accountant_approval == false ? 'Rejected' : 'Pending')}
                                    ${expense.accountant_approval_datetime ? `<br>(${expense.accountant_approval_datetime})` : ''}
                                    ${expense.accountant_rejection_remarks && expense.accountant_approval == false ? `<br>Remarks: ${expense.accountant_rejection_remarks}` : ''}
                                </td>
                                <td style="color: ${expense.chairperson_approval ? 'green' : (expense.chairperson_approval == false ? 'red' : 'gray')}">
                                    ${expense.chairperson_approval ? 'Approved' : (expense.chairperson_approval == false ? 'Rejected' : 'Pending')}
                                    ${expense.chairperson_approval_datetime ? `<br>(${expense.chairperson_approval_datetime})` : ''}
                                    ${expense.chairperson_rejection_remarks && expense.chairperson_approval == false ? `<br>Remarks: ${expense.chairperson_rejection_remarks}` : ''}
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary preview-btn" data-bs-toggle="modal" data-bs-target="#containerModal" data-id="${expense.request_id}" data-target="${target}">Preview</button>
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
                                <td style="color: ${expense.accountant_approval ? 'green' : (expense.accountant_approval == false ? 'red' : 'gray')}">
                                    ${expense.accountant_approval ? 'Approved' : (expense.accountant_approval == false ? 'Rejected' : 'Pending')}
                                    ${expense.accountant_approval_datetime ? `<br>(${expense.accountant_approval_datetime})` : ''}
                                    ${expense.accountant_rejection_remarks && expense.accountant_approval == false ? `<br>Remarks: ${expense.accountant_rejection_remarks}` : ''}
                                </td>
                                <td style="color: ${expense.chairperson_approval ? 'green' : (expense.chairperson_approval == false ? 'red' : 'gray')}">
                                    ${expense.chairperson_approval ? 'Approved' : (expense.chairperson_approval == false ? 'Rejected' : 'Pending')}
                                    ${expense.chairperson_approval_datetime ? `<br>(${expense.chairperson_approval_datetime})` : ''}
                                    ${expense.chairperson_rejection_remarks && expense.chairperson_approval == false ? `<br>Remarks: ${expense.chairperson_rejection_remarks}` : ''}
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary preview-btn" data-bs-toggle="modal" data-bs-target="#containerModal" data-id="${expense.request_id}" data-target="${target}">Preview</button>
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
                        <li class="page-item ${i === page ? 'active' : ''}">
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
        currentPage = parseInt($(this).text()); 
        fetchExpenses(activeTab, currentPage); 
    });
    
});

// Event listener for the "Preview" button click
$(document).on('click', '.preview-btn', function () {
    // Extract expense ID and active tab from button data attributes
    const expenseId = $(this).data('id');
    const activeTab = $(this).data('target');

    // Construct the API URL for fetching expense details
    const url = `../api/data/expense_request?id=${expenseId}&target=${activeTab}`;
    console.log("Fetching expense details from:", url); // Log URL for debugging

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
                                        <strong>Accountant Approval:</strong>
                                        <span class="${data.accountant_approval == true ? 'text-success' : (data.accountant_approval == false ? 'text-danger' : 'text-warning')}">
                                            <i class="${data.accountant_approval == true ? 'fas fa-check-circle' : (data.accountant_approval == false ? 'fas fa-times-circle' : 'fas fa-clock')}"></i>
                                            ${data.accountant_approval == true ? ' Approved' : (data.accountant_approval == false ? ' Rejected' : ' Pending')}
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
