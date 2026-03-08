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
    render_header('Manage Revenue Streams - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Manage Revenue Streams</h5>

            <!-- Search Form -->
            <div class="row mb-4">
              <div class="col-md-8">
                <input type="text" id="searchRevenue" class="form-control" placeholder="Search Revenue Streams by Name">
              </div>
              <div class="col-md-4 text-end">
                <button class="btn btn-primary" id="searchBtn">Search</button>
              </div>
            </div>

            <!-- Loading GIF -->
            <div id="loading">
              <img src="../assets/images/gifs/loading.gif" alt="Loading..." height="100">
            </div>

            <!-- Revenue Streams Table -->
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Revenue Stream Name</th>
                    <th>Account Name</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="revenueList">
                  <!-- Table rows will be populated here using AJAX -->
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <nav id="paginationNav" aria-label="Revenue Stream pagination">
              <ul class="pagination justify-content-center">
                <!-- Pagination buttons will be generated dynamically -->
              </ul>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php require_once('components/footer_files.php') ?>
  <script>
    $(document).ready(function () {
        const fetchRevenueStreams = (page = 1, query = '') => {
            $('#loading').show(); // Show loading GIF
            $.ajax({
                type: 'GET',
                url: `../api/data/head_parish_revenue_streams?page=${page}&query=${query}`, 
                dataType: 'json',
                success: function (response) {
                    const revenueStreams = response.data;
                    const revenueList = $('#revenueList');
                    revenueList.empty();
                    let rowIndex = (page - 1) * 5 + 1;
                    revenueStreams.forEach(stream => {
                        revenueList.append(`
                            <tr>
                                <td>${rowIndex++}</td>
                                <td>${stream.revenue_stream_name}</td>
                                <td>${stream.account_name}</td>
                                <td>
                                    <a href="#" class="btn btn-secondary btn-sm"><i class="fas fa-edit"></i> Edit</a>
                                    <a href="#" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i> Delete</a>
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
                    console.log('Error fetching revenue streams:', error);
                    $('#loading').hide(); // Hide loading GIF even on error
                }
            });
        };

        // Load revenue streams initially
        fetchRevenueStreams();

        // Search functionality
        $('#searchBtn').on('click', function () {
            const query = $('#searchRevenue').val();
            fetchRevenueStreams(1, query);
        });

        // Attach onchange event to search input
        $('#searchRevenue').on('input', function () {
            const query = $(this).val();
            fetchRevenueStreams(1, query);
        });

        // Pagination click event
        $(document).on('click', '#paginationNav .page-link', function (e) {
            e.preventDefault();
            const page = $(this).text();
            fetchRevenueStreams(page);
        });
    });
  </script>
</body>
</html>
