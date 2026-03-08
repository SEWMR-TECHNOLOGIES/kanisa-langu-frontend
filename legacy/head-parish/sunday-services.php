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
    render_header('Manage Sunday Services - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Manage Sunday Services</h5>

            <!-- Search Form -->
            <div class="row mb-4">
              <div class="col-md-8">
                <input type="text" id="searchService" class="form-control" placeholder="Search Services by Scripture or Color">
              </div>
              <div class="col-md-4 text-end">
                <button class="btn btn-primary" id="searchBtn">Search</button>
              </div>
            </div>

            <!-- Loading GIF -->
            <div id="loading">
              <img src="../assets/images/gifs/loading.gif" alt="Loading..." height="100">
            </div>

            <!-- Sunday Services Table -->
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Service Date</th>
                    <th>Scripture</th>
                    <th>Color</th>
                    <th class="text-end">Actions</th>
                  </tr>
                </thead>
                <tbody id="serviceList">
                  <!-- Table rows will be populated here using AJAX -->
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <nav id="paginationNav" aria-label="Service pagination">
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
    const fetchServices = (page = 1, query = '') => {
        $('#loading').show(); // Show loading GIF
        $.ajax({
            type: 'GET',
            url: `../api/data/sunday_services?page=${page}&query=${query}`, 
            dataType: 'json',
            success: function (response) {
                const services = response.data;
                const serviceList = $('#serviceList');
                serviceList.empty();
                let rowIndex = (page - 1) * 10 + 1;
                services.forEach(service => {
                    const serviceDate = new Date(service.service_date).toLocaleDateString();
                    const color = service.color_name;
                    const linkUrl = `/head-parish/sunday-service-details.php?head_parish_id=${service.head_parish_id}&service_date=${service.service_date}`;
                    serviceList.append(`
                        <tr data-service-id="${service.service_id}">
                            <td>${rowIndex++}</td>
                            <td>${serviceDate}</td>
                            <td>${service.base_scripture_text}</td>
                            <td>${color}</td>
                            <td class="text-end">
                                <a href="${linkUrl}" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> View</a>
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
                console.log('Error fetching services:', error);
                $('#loading').hide(); // Hide loading GIF even on error
            }
        });
    };

    // Load services initially
    fetchServices();

    // Search functionality
    $('#searchBtn').on('click', function () {
        const query = $('#searchService').val();
        fetchServices(1, query);
    });

    // Attach onchange event to search input
    $('#searchService').on('input', function () {
        const query = $(this).val();
        fetchServices(1, query);
    });

    // Pagination click event
    $(document).on('click', '.page-link', function (e) {
        e.preventDefault();
        const page = $(this).text();
        fetchServices(page);
    });
});
</script>
</body>
</html>
