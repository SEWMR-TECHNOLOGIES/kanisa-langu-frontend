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
    render_header('Manage Communities - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Manage Communities</h5>

            <!-- Search Form -->
            <div class="row mb-4">
              <div class="col-md-8">
                <input type="text" id="searchCommunity" class="form-control" placeholder="Search Communities by Name or Sub Parish">
              </div>
              <div class="col-md-4 text-end">
                <button class="btn btn-primary" id="searchBtn">Search</button>
              </div>
            </div>

            <!-- Loading GIF -->
            <div id="loading">
              <img src="../assets/images/gifs/loading.gif" alt="Loading..." height="100">
            </div>

            <!-- Communities Table -->
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Community Name</th>
                    <th>Sub Parish</th>
                    <th>Description</th>
                    <th class="text-end">Actions</th>
                  </tr>
                </thead>
                <tbody id="communityList">
                  <!-- Table rows will be populated here using AJAX -->
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <nav id="paginationNav" aria-label="Community pagination">
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
        const fetchCommunities = (page = 1, query = '', sub_parish_id = null) => {
            $('#loading').show(); // Show loading GIF
            $.ajax({
                type: 'GET',
                url: `../api/data/head_parish_communities?page=${page}&query=${query}`, 
                dataType: 'json',
                success: function (response) {
                    const communities = response.data;
                    const communityList = $('#communityList');
                    communityList.empty();
                    let rowIndex = (page - 1) * 5 + 1;
                    communities.forEach(community => {
                        communityList.append(`
                            <tr>
                                <td>${rowIndex++}</td>
                                <td>${community.community_name}</td>
                                <td>${community.sub_parish_name}</td>
                                <td>${community.description}</td>
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
                    console.log('Error fetching communities:', error);
                    $('#loading').hide(); // Hide loading GIF even on error
                }
            });
        };

        // Load communities initially
        fetchCommunities();

        // Search functionality
        $('#searchBtn').on('click', function () {
            const query = $('#searchCommunity').val();
            fetchCommunities(1, query);
        });

        // Attach onchange event to search input
        $('#searchCommunity').on('input', function () {
            const query = $(this).val();
            fetchCommunities(1, query);
        });

        // Pagination click event
        $(document).on('click', '#paginationNav .page-link', function (e) {
            e.preventDefault();
            const page = $(this).text();
            fetchCommunities(page);
        });
    });
  </script>
</body>

</html>
