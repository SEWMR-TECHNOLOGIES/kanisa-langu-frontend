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
    render_header('Manage Debits - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Manage Debits</h5>

            <!-- Search Form -->
            <div class="row mb-4">
              <div class="col-md-8">
                <input type="text" id="searchDebit" class="form-control" placeholder="Search Debits by description, purpose">
              </div>
              <div class="col-md-4 text-end">
                <button class="btn btn-primary" id="searchBtn">Search</button>
              </div>
            </div>

            <!-- Loading GIF -->
            <div id="loading">
              <img src="../assets/images/gifs/loading.gif" alt="Loading..." height="100">
            </div>

            <!-- Debits Table -->
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Date Debited</th>
                    <th>Return Before</th>
                    <th>Purpose</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                  </tr>
                </thead>
                <tbody id="debitList">
                  <!-- Table rows will be populated here using AJAX -->
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <nav id="paginationNav" aria-label="Debit pagination">
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
  
  <?php require_once('components/footer_files.php') ?>
<script>
$(document).ready(function () {
    const fetchDebits = (page = 1, query = '') => {
        $('#loading').show(); // Show loading GIF
        $.ajax({
            type: 'GET',
            url: `../api/data/debits.php?page=${page}&query=${query}`, 
            dataType: 'json',
            success: function (response) {
                const debits = response.data;
                const debitList = $('#debitList');
                debitList.empty();
                let rowIndex = (page - 1) * 10 + 1;
                debits.forEach(debit => {
                    // Assign text color based on the status
                    const statusClass = debit.status === 'Unpaid' ? 'text-danger' : 'text-success'; // Red text for Unpaid, Green for Paid
            
                    debitList.append(`
                        <tr data-debit-id="${debit.id}">
                            <td>${rowIndex++}</td>
                            <td>${debit.description}</td>
                            <td>TZS ${debit.amount}</td>
                            <td>${debit.date_debited}</td>
                            <td>${debit.return_before_date}</td>
                            <td>${debit.purpose}</td>
                            <td class="status ${statusClass}">${debit.status}</td> <!-- Apply text color to status -->
                            <td class="text-end">
                                <button class="btn btn-secondary btn-sm edit-btn"><i class="fas fa-edit"></i> Edit</button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i> Delete</button>
                            </td>
                        </tr>
                    `);
                });


                // Handle pagination
                $('#paginationNav .pagination').empty();
                for (let i = 1; i <= response.total_pages; i++) {
                    $('#paginationNav .pagination').append(`
                        <li class="page-item ${i == page ? 'active' : ''}">
                            <a class="page-link" href="#">${i}</a>
                        </li>
                    `);
                }

                $('#loading').hide(); // Hide loading GIF
            },
            error: function (xhr, status, error) {
                console.log('Error fetching debits:', error);
                $('#loading').hide(); // Hide loading GIF even on error
            }
        });
    };

    // Load debits initially
    fetchDebits();

    // Search functionality
    $('#searchBtn').on('click', function () {
        const query = $('#searchDebit').val();
        fetchDebits(1, query);
    });

    // Attach onchange event to search input
    $('#searchDebit').on('input', function () {
        const query = $(this).val();
        fetchDebits(1, query);
    });

    // Pagination click event
    $(document).on('click', '#paginationNav .page-link', function (e) {
        e.preventDefault();
        const page = $(this).text();
        const query = $('#searchDebit').val();
        fetchDebits(page, query);
    });

});
</script>
</body>

</html>
