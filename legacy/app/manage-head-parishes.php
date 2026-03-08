
<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
// Call the function on any page that requires superadmin authentication
check_session('kanisalangu_admin_id', '../app/sign-in');
 ?>
 <!doctype html>
<html lang="en">
<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Manage Head Parishes - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Manage Head Parishes</h5>
            <!-- Search Form -->
            <div class="row mb-4">
              <div class="col-md-8">
                <input type="text" id="searchHeadParish" class="form-control" placeholder="Search Head Parish by Name, Diocese, or Province">
              </div>
              <div class="col-md-4 text-end">
                <button class="btn btn-primary" id="searchBtn">Search</button>
              </div>
            </div>

            <!-- Loading GIF -->
            <div id="loading">
              <img src="../assets/images/gifs/loading.gif" alt="Loading..." height="100">
            </div>

            <!-- Head Parish Cards -->
            <div class="row" id="headParishList">
              <!-- Cards will be populated here using AJAX -->
            </div>

            <!-- Pagination -->
            <nav id="paginationNav" aria-label="Head Parish pagination">
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
        const fetchHeadParishes = (page = 1, query = '') => {
            $('#loading').show(); // Show loading GIF
            $.ajax({
                type: 'GET',
                url: `../api/data/parishes.php?page=${page}&query=${query}`, 
                dataType: 'json',
                success: function (response) {
                    const headParishes = response.data;
                    const headParishList = $('#headParishList');
                    headParishList.empty();
                    headParishes.forEach(headParish => {
                        const phoneLink = headParish.head_parish_phone ? `<i class="fas fa-phone" style="margin-right: 0.5rem;"></i> ${headParish.head_parish_phone}` : '<a href="#"><i class="fas fa-phone"></i> Add Phone</a>';
                        const emailLink = headParish.head_parish_email ? `<i class="fas fa-envelope" style="margin-right: 0.5rem;"></i> ${headParish.head_parish_email}` : '<a href="#"><i class="fas fa-envelope"></i> Add Email</a>';
                        const addressLink = headParish.head_parish_address ? `<i class="fas fa-map-marker-alt" style="margin-right: 0.5rem;"></i> ${headParish.head_parish_address}` : '<a href="#"><i class="fas fa-map-marker-alt"></i> Add Address</a>';
                        const dioceseName = headParish.diocese_name ? headParish.diocese_name : 'Unknown Diocese';
                        const provinceName = headParish.province_name ? headParish.province_name : 'Unknown Province';
                        const regionName = headParish.region_name ? headParish.region_name : 'Unknown Region';
                        const districtName = headParish.district_name ? headParish.district_name : 'Unknown District';

                        headParishList.append(`
                            <div class="col-lg-4 mb-4">
                                <div class="card">
                                    <div class="card-header">
                                        ${headParish.head_parish_name}
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title">${dioceseName}</h5>
                                        <p class="card-text">${provinceName}</p>
                                        <p class="card-text">${districtName}, ${regionName}</p>
                                        <p class="card-text">${phoneLink}</p>
                                        <p class="card-text">${emailLink}</p>
                                        <p class="card-text">${addressLink}</p>
                                    </div>
                                    <div class="card-footer text-end" style="background-color:#fff!important;">
                                        <a href="#" class="btn btn-secondary btn-sm"><i class="fas fa-edit"></i> Edit</a>
                                        <a href="#" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i> Delete</a>
                                    </div>
                                </div>
                            </div>
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
                    console.log('Error fetching head parishes:', error);
                    $('#loading').hide(); // Hide loading GIF even on error
                }
            });
        };

        // Load head parishes initially
        fetchHeadParishes();

        // Search functionality
        $('#searchBtn').on('click', function () {
            const query = $('#searchHeadParish').val();
            fetchHeadParishes(1, query);
        });

        // Attach onchange event to search input
        $('#searchHeadParish').on('input', function () {
            const query = $(this).val();
            fetchHeadParishes(1, query);
        });

        // Pagination click event
        $(document).on('click', '#paginationNav .page-link', function (e) {
            e.preventDefault();
            const page = $(this).text();
            fetchHeadParishes(page);
        });
    });
  </script>
</body>
</html>
