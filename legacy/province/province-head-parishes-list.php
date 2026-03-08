<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
// Ensure session for diocese_admin_id exists
check_session('province_admin_id', '../province/sign-in'); 
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
                <input type="text" id="searchParish" class="form-control" placeholder="Search Parish by Name or Other Fields">
              </div>
              <div class="col-md-4 text-end">
                <button class="btn btn-primary" id="searchBtn">Search</button>
              </div>
            </div>

            <!-- Loading GIF -->
            <div id="loading">
              <img src="../assets/images/gifs/loading.gif" alt="Loading..." height="100">
            </div>

            <!-- Parish Cards -->
            <div class="row" id="parishList">
              <!-- Cards will be populated here using AJAX -->
            </div>

            <!-- Pagination -->
            <nav id="paginationNav" aria-label="Parish pagination">
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
        const fetchParishes = (page = 1, query = '') => {
            $('#loading').show(); // Show loading GIF
            $.ajax({
                type: 'GET',
                url: `../api/data/province_head_parishes?page=${page}&query=${query}`, // Adjust API URL if necessary
                dataType: 'json',
                success: function (response) {
                    const parishes = response.data;
                    const parishList = $('#parishList');
                    parishList.empty();
                    parishes.forEach(parish => {
                        const phoneLink = parish.head_parish_phone ? `<i class="fas fa-phone" style="margin-right: 0.5rem;"></i> ${parish.head_parish_phone}` : '<a href="#"><i class="fas fa-phone"></i> Add Phone</a>';
                        const emailLink = parish.head_parish_email ? `<i class="fas fa-envelope" style="margin-right: 0.5rem;"></i> ${parish.head_parish_email}` : '<a href="#"><i class="fas fa-envelope"></i> Add Email</a>';
                        const addressLink = parish.head_parish_address ? `<i class="fas fa-map-marker-alt" style="margin-right: 0.5rem;"></i> ${parish.head_parish_address}` : '<a href="#"><i class="fas fa-map-marker-alt"></i> Add Address</a>';
                        const provinceName = parish.province_name ? parish.province_name : 'Unknown Province';

                        parishList.append(`
                            <div class="col-lg-4 mb-4">
                                <div class="card">
                                    <div class="card-header">
                                        ${parish.head_parish_name}
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title">${provinceName}</h5>
                                        <p class="card-text">${phoneLink}</p>
                                        <p class="card-text">${emailLink}</p>
                                        <p class="card-text">${addressLink}</p>
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
                    console.log('Error fetching parishes:', error);
                    $('#loading').hide(); // Hide loading GIF even on error
                }
            });
        };

        // Load parishes initially
        fetchParishes();

        // Search functionality
        $('#searchBtn').on('click', function () {
            const query = $('#searchParish').val();
            fetchParishes(1, query);
        });

        // Attach onchange event to search input
        $('#searchParish').on('input', function () {
            const query = $(this).val();
            fetchParishes(1, query);
        });

        // Pagination click event
        $(document).on('click', '#paginationNav .page-link', function (e) {
            e.preventDefault();
            const page = $(this).text();
            fetchParishes(page);
        });
    });
</script>
</body>
</html>
