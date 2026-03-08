<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
// Ensure the user is authenticated as admin
check_session('kanisalangu_admin_id', '../app/sign-in');
?>
<!doctype html>
<html lang="en">
<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Manage Occupations - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Manage Occupations</h5>
            <!-- Search Form -->
            <div class="row mb-4">
              <div class="col-md-8">
                <input type="text" id="searchOccupation" class="form-control" placeholder="Search Occupation by Name">
              </div>
              <div class="col-md-4 text-end">
                <button class="btn btn-primary" id="searchBtn">Search</button>
              </div>
            </div>

            <!-- Loading GIF -->
            <div id="loading">
              <img src="../assets/images/gifs/loading.gif" alt="Loading..." height="100">
            </div>

            <!-- Occupations Table -->
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Occupation Name</th>
                    <th>Description</th>
                    <th class="text-end">Actions</th>
                  </tr>
                </thead>
                <tbody id="occupationList">
                  <!-- Table rows will be populated here using AJAX -->
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <nav id="paginationNav" aria-label="Occupation pagination">
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
        const fetchOccupations = (page = 1, query = '') => {
            $('#loading').show(); // Show loading GIF
            $.ajax({
                type: 'GET',
                url: `/api/data/occupations.php?page=${page}&query=${query}`,
                dataType: 'json',
                success: function (response) {
                    const occupations = response.data;
                    const occupationList = $('#occupationList');
                    occupationList.empty();
                    let rowIndex = (page - 1) * 10 + 1;
                    occupations.forEach(occupation => {
                        occupationList.append(`
                            <tr>
                                <td>${rowIndex++}</td>
                                <td>${occupation.name}</td>
                                <td>${occupation.description}</td>
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
                    console.log('Error fetching occupations:', error);
                    $('#loading').hide(); // Hide loading GIF even on error
                }
            });
        };

        // Load occupations initially
        fetchOccupations();

        // Search functionality
        $('#searchBtn').on('click', function () {
            const query = $('#searchOccupation').val();
            fetchOccupations(1, query);
        });

        // Attach onchange event to search input
        $('#searchOccupation').on('input', function () {
            const query = $(this).val();
            fetchOccupations(1, query);
        });

        // Pagination click event
        $(document).on('click', '#paginationNav .page-link', function (e) {
            e.preventDefault();
            const page = $(this).text();
            fetchOccupations(page);
        });
    });
  </script>
</body>
</html>
