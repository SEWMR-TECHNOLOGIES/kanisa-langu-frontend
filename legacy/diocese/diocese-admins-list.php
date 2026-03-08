<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
// Call the function on any page that requires superadmin authentication
check_superadmin_session();
?>
<!doctype html>
<html lang="en">
<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Manage Diocese Admins - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Manage Diocese Admins</h5>
            <!-- Search Form -->
            <div class="row mb-4">
              <div class="col-md-8">
                <input type="text" id="searchAdmin" class="form-control" placeholder="Search Admin by Name or Diocese Name">
              </div>
              <div class="col-md-4 text-end">
                <button class="btn btn-primary" id="searchBtn">Search</button>
              </div>
            </div>

            <!-- Loading GIF -->
            <div id="loading">
              <img src="../assets/images/gifs/loading.gif" alt="Loading..." height="100">
            </div>

            <!-- Admins Table -->
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Admin Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Diocese</th>
                    <th>Role</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="adminList">
                  <!-- Table rows will be populated here using AJAX -->
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <nav id="paginationNav" aria-label="Admin pagination">
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
        const fetchAdmins = (page = 1, query = '') => {
            $('#loading').show(); // Show loading GIF
            $.ajax({
                type: 'GET',
                url: `../api/data/diocese_admins?page=${page}&query=${query}`, // API endpoint for fetching admins
                dataType: 'json',
                success: function (response) {
                    const admins = response.data;
                    const adminList = $('#adminList');
                    adminList.empty();
                    let rowIndex = (page - 1) * 5 + 1;
                    admins.forEach(admin => {
                        const phoneLink = admin.admin_phone ? admin.admin_phone : '<a href="#"><i class="fas fa-phone"></i> Add Phone</a>';
                        const emailLink = admin.admin_email ? admin.admin_email : '<a href="#"><i class="fas fa-envelope"></i> Add Email</a>';
                        const dioceseName = admin.diocese_name ? admin.diocese_name : 'Unknown Diocese';
                        const role = admin.role ? admin.role : 'Unknown Role';
                        adminList.append(`
                            <tr>
                                <td>${rowIndex++}</td>
                                <td>${admin.admin_name}</td>
                                <td>${emailLink}</td>
                                <td>${phoneLink}</td>
                                <td>${dioceseName}</td>
                                <td>${role}</td>
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
                    console.log('Error fetching admins:', error);
                    $('#loading').hide(); // Hide loading GIF even on error
                }
            });
        };

        // Load admins initially
        fetchAdmins();

        // Search functionality
        $('#searchBtn').on('click', function () {
            const query = $('#searchAdmin').val();
            fetchAdmins(1, query);
        });

        // Attach onchange event to search input
        $('#searchAdmin').on('input', function () {
            const query = $(this).val();
            fetchAdmins(1, query);
        });

        // Pagination click event
        $(document).on('click', '#paginationNav .page-link', function (e) {
            e.preventDefault();
            const page = $(this).text();
            fetchAdmins(page);
        });
    });
</script>
</body>
</html>
