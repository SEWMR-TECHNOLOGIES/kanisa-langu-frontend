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
    render_header('Manage Harambee - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Manage Harambee</h5>

            <!-- Search Form -->
            <div class="row mb-4">
              <div class="col-md-8">
                <input type="text" id="searchHarambee" class="form-control" placeholder="Search Harambee">
              </div>
              <div class="col-md-4 text-end">
                <button class="btn btn-primary" id="searchBtn">Search</button>
              </div>
            </div>

            <!-- Tab navigation for different Harambee targets -->
            <ul class="nav nav-tabs" id="harambeeTabs" role="tablist">
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
                        <th>Description</th>
                        <th>From Date</th>
                        <th>To Date</th>
                        <th>Amount</th>
                        <th>Account Name</th>
                        <th class="text-end">Actions</th>
                      </tr>
                    </thead>
                    <tbody id="headParishList"></tbody>
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
                        <th>Description</th>
                        <th>From Date</th>
                        <th>To Date</th>
                        <th>Amount</th>
                        <th>Sub Parish Name</th>
                        <th>Account Name</th>
                        <th class="text-end">Actions</th>
                      </tr>
                    </thead>
                    <tbody id="subParishList"></tbody>
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
                        <th>Description</th>
                        <th>From Date</th>
                        <th>To Date</th>
                        <th>Amount</th>
                        <th>Community Name</th>
                        <th>Sub Parish Name</th>
                        <th>Account Name</th>
                        <th class="text-end">Actions</th>
                      </tr>
                    </thead>
                    <tbody id="communityList"></tbody>
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
                        <th>Description</th>
                        <th>From Date</th>
                        <th>To Date</th>
                        <th>Amount</th>
                        <th>Group Name</th>
                        <th>Account Name</th>
                        <th class="text-end">Actions</th>
                      </tr>
                    </thead>
                    <tbody id="groupList"></tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- Loading GIF -->
            <div id="loading">
              <img src="../assets/images/gifs/loading.gif" alt="Loading..." height="100">
            </div>

            <!-- Pagination -->
            <nav id="paginationNav" aria-label="Harambee pagination">
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
    let activeTab = 'head-parish';  // Initialize with the first tab ID
    let currentPage = 1; // Current page for pagination

    // Function to fetch Harambee data
    const fetchHarambee = (target, page = 1, query = '') => {
        $('#loading').show();
        let url = `../api/data/head_parish_harambee?target=${target}&page=${page}&query=${query}`;
        $.ajax({
            type: 'GET',
            url: url,
            dataType: 'json',
            success: function (response) {
                let harambeeList = '';
                let listId = '';

                switch (target) {
                    case 'head-parish':
                        listId = '#headParishList';
                        harambeeList = response.data.map((harambee, index) => `
                            <tr>
                                <td>${(page - 1) * 5 + index + 1}</td>
                                <td>${harambee.description}</td>
                                <td>${harambee.from_date}</td>
                                <td>${harambee.to_date}</td>
                                <td>TZS ${harambee.amount}</td>
                                <td>${harambee.account_name}</td>
                                <td class="text-end">
                                    <a href="#" class="btn btn-secondary btn-sm"><i class="fas fa-edit"></i> Edit</a>
                                    <a href="#" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i> Delete</a>
                                </td>
                            </tr>
                        `).join('');
                        break;
                    case 'sub-parish':
                        listId = '#subParishList';
                        harambeeList = response.data.map((harambee, index) => `
                            <tr>
                                <td>${(page - 1) * 5 + index + 1}</td>
                                <td>${harambee.description}</td>
                                <td>${harambee.from_date}</td>
                                <td>${harambee.to_date}</td>
                                <td>TZS ${harambee.amount}</td>
                                <td>${harambee.sub_parish_name}</td>
                                <td>${harambee.account_name}</td>
                                <td class="text-end">
                                    <a href="#" class="btn btn-secondary btn-sm"><i class="fas fa-edit"></i> Edit</a>
                                    <a href="#" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i> Delete</a>
                                </td>
                            </tr>
                        `).join('');
                        break;
                    case 'community':
                        listId = '#communityList';
                        harambeeList = response.data.map((harambee, index) => `
                            <tr>
                                <td>${(page - 1) * 5 + index + 1}</td>
                                <td>${harambee.description}</td>
                                <td>${harambee.from_date}</td>
                                <td>${harambee.to_date}</td>
                                <td>TZS ${harambee.amount}</td>
                                <td>${harambee.community_name}</td>
                                <td>${harambee.sub_parish_name}</td>
                                <td>${harambee.account_name}</td>
                                <td class="text-end">
                                    <a href="#" class="btn btn-secondary btn-sm"><i class="fas fa-edit"></i> Edit</a>
                                    <a href="#" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i> Delete</a>
                                </td>
                            </tr>
                        `).join('');
                        break;
                    case 'group':
                        listId = '#groupList';
                        harambeeList = response.data.map((harambee, index) => `
                            <tr>
                                <td>${(page - 1) * 5 + index + 1}</td>
                                <td>${harambee.description}</td>
                                <td>${harambee.from_date}</td>
                                <td>${harambee.to_date}</td>
                                <td>TZS ${harambee.amount}</td>
                                <td>${harambee.group_name}</td>
                                <td>${harambee.account_name}</td>
                                <td class="text-end">
                                    <a href="#" class="btn btn-secondary btn-sm"><i class="fas fa-edit"></i> Edit</a>
                                    <a href="#" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i> Delete</a>
                                </td>
                            </tr>
                        `).join('');
                        break;
                }

                $(listId).html(harambeeList);
                $('#paginationNav .pagination').empty();
                for (let i = 1; i <= response.total_pages; i++) {
                    $('#paginationNav .pagination').append(`
                        <li class="page-item ${i === page ? 'active' : ''}">
                            <a class="page-link" href="#">${i}</a>
                        </li>
                    `);
                }

                $('#loading').hide(); // Hide loading GIF
            },
            error: function (xhr, status, error) {
                console.log('Error fetching Harambee data:', error);
                $('#loading').hide(); // Hide loading GIF even on error
            }
        });
    };

    // Load head parish Harambee data by default
    fetchHarambee('head-parish');

    // Tab click event to load different Harambee data based on the selected tab
    $('#harambeeTabs button').on('click', function () {
        activeTab = $(this).data('bs-target').replace('#', '');  // Use data-bs-target attribute
        currentPage = 1; // Reset to the first page on tab change
        fetchHarambee(activeTab);
    });

    // Search functionality
    $('#searchBtn').on('click', function () {
        const query = $('#searchHarambee').val();
        currentPage = 1; // Reset to the first page on search
        fetchHarambee(activeTab, currentPage, query);
    });

    // Attach onchange event to search input
    $('#searchHarambee').on('input', function () {
        const query = $(this).val();
        currentPage = 1; // Reset to the first page on search
        fetchHarambee(activeTab, currentPage, query);
    });

    // Pagination click event
    $(document).on('click', '#paginationNav .page-link', function (e) {
        e.preventDefault();
        currentPage = parseInt($(this).text()); // Get the clicked page number
        fetchHarambee(activeTab, currentPage); // Fetch data for that page
    });
});

  </script>
</body>

</html>
