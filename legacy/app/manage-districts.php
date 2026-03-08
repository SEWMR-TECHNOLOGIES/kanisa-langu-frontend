<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
// Ensure the user is authenticated as superadmin
check_session('kanisalangu_admin_id', '../app/sign-in');
?>
<!doctype html>
<html lang="en">
<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Manage Districts - Kanisa Langu');
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
    
    /* Additional styling for the actions column */
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
            <h5 class="card-title fw-semibold mb-4">Manage Districts</h5>
            <!-- Search Form -->
            <div class="row mb-4">
              <div class="col-md-8">
                <input type="text" id="searchDistrict" class="form-control" placeholder="Search District by Name">
              </div>
              <div class="col-md-4 text-end">
                <button class="btn btn-primary" id="searchBtn">Search</button>
              </div>
            </div>

            <!-- Loading GIF -->
            <div id="loading">
              <img src="../assets/images/gifs/loading.gif" alt="Loading..." height="100">
            </div>

            <!-- Districts Table -->
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>District Name</th>
                    <th>Region</th>
                    <th class="text-end">
                  </tr>
                </thead>
                <tbody id="districtList">
                  <!-- Table rows will be populated here using AJAX -->
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <nav id="paginationNav" aria-label="District pagination">
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
        const fetchDistricts = (page = 1, query = '') => {
            $('#loading').show(); // Show loading GIF
            $.ajax({
                type: 'GET',
                url: `/api/data/districts.php?page=${page}&query=${query}`,
                dataType: 'json',
                success: function (response) {
                    const districts = response.data;
                    const districtList = $('#districtList');
                    districtList.empty();
                    let rowIndex = (page - 1) * 10 + 1;
                    districts.forEach(district => {
                        districtList.append(`
                            <tr>
                                <td>${rowIndex++}</td>
                                <td>${district.name}</td>
                                <td>${district.region_name}</td>
                                 <td class="text-end">
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
                    console.log('Error fetching districts:', error);
                    $('#loading').hide(); // Hide loading GIF even on error
                }
            });
        };

        // Load districts initially
        fetchDistricts();

        // Search functionality
        $('#searchBtn').on('click', function () {
            const query = $('#searchDistrict').val();
            fetchDistricts(1, query);
        });

        // Attach onchange event to search input
        $('#searchDistrict').on('input', function () {
            const query = $(this).val();
            fetchDistricts(1, query);
        });

        // Pagination click event
        $(document).on('click', '#paginationNav .page-link', function (e) {
            e.preventDefault();
            const page = $(this).text();
            fetchDistricts(page);
        });
    });
  </script>
</body>
</html>

