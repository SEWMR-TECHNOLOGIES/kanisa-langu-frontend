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
    render_header('Manage Provinces - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Manage Provinces</h5>
            <!-- Search Form -->
            <div class="row mb-4">
              <div class="col-md-8">
                <input type="text" id="searchProvince" class="form-control" placeholder="Search Province by Name or Diocese Name">
              </div>
              <div class="col-md-4 text-end">
                <button class="btn btn-primary" id="searchBtn">Search</button>
              </div>
            </div>

            <!-- Loading GIF -->
            <div id="loading">
              <img src="../assets/images/gifs/loading.gif" alt="Loading..." height="100">
            </div>

            <!-- Province Cards -->
            <div class="row" id="provinceList">
              <!-- Cards will be populated here using AJAX -->
            </div>

            <!-- Pagination -->
            <nav id="paginationNav" aria-label="Province pagination">
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
        const fetchProvinces = (page = 1, query = '') => {
            $('#loading').show(); // Show loading GIF
            $.ajax({
                type: 'GET',
                url: `../api/data/provinces?page=${page}&query=${query}`, 
                dataType: 'json',
                success: function (response) {
                    const provinces = response.data;
                    const provinceList = $('#provinceList');
                    provinceList.empty();
                    provinces.forEach(province => {
                        const phoneLink = province.province_phone ? `<i class="fas fa-phone" style="margin-right: 0.5rem;"></i> ${province.province_phone}` : '<a href="#"><i class="fas fa-phone"></i> Add Phone</a>';
                        const emailLink = province.province_email ? `<i class="fas fa-envelope" style="margin-right: 0.5rem;"></i> ${province.province_email}` : '<a href="#"><i class="fas fa-envelope"></i> Add Email</a>';
                        const addressLink = province.province_address ? `<i class="fas fa-map-marker-alt" style="margin-right: 0.5rem;"></i> ${province.province_address}` : '<a href="#"><i class="fas fa-map-marker-alt"></i> Add Address</a>';
                        const dioceseName = province.diocese_name ? province.diocese_name : 'Unknown Diocese';
                        const regionName = province.region_name ? province.region_name : 'Unknown Region';
                        const districtName = province.district_name ? province.district_name : 'Unknown District';

                        provinceList.append(`
                            <div class="col-lg-4 mb-4">
                                <div class="card">
                                    <div class="card-header">
                                        ${province.province_name}
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title">${dioceseName}</h5>
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
                    console.log('Error fetching provinces:', error);
                    $('#loading').hide(); // Hide loading GIF even on error
                }
            });
        };

        // Load provinces initially
        fetchProvinces();

        // Search functionality
        $('#searchBtn').on('click', function () {
            const query = $('#searchProvince').val();
            fetchProvinces(1, query);
        });

        // Attach onchange event to search input
        $('#searchProvince').on('input', function () {
            const query = $(this).val();
            fetchProvinces(1, query);
        });

        // Pagination click event
        $(document).on('click', '#paginationNav .page-link', function (e) {
            e.preventDefault();
            const page = $(this).text();
            fetchProvinces(page);
        });
    });
</script>
</body>
</html>
