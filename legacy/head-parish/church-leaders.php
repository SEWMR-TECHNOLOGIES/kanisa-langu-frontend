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
    render_header('Manage Church Leaders - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Manage Church Leaders</h5>

            <!-- Search Form -->
            <div class="row mb-4">
              <div class="col-md-8">
                <input type="text" id="searchLeader" class="form-control" placeholder="Search Leaders by Name or Role">
              </div>
              <div class="col-md-4 text-end">
                <button class="btn btn-primary" id="searchBtn">Search</button>
              </div>
            </div>

            <!-- Loading GIF -->
            <div id="loading">
              <img src="../assets/images/gifs/loading.gif" alt="Loading..." height="100">
            </div>

            <!-- Church Leaders Table -->
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Full Name</th>
                    <th>Role</th>
                    <th>Appointment Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                  </tr>
                </thead>
                <tbody id="leaderList">
                  <!-- Table rows will be populated here using AJAX -->
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <nav id="paginationNav" aria-label="Leader pagination">
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
    const fetchLeaders = (page = 1, query = '') => {
        $('#loading').show(); // Show loading GIF
        $.ajax({
            type: 'GET',
            url: `../api/data/church_leaders?page=${page}&query=${query}`, 
            dataType: 'json',
            success: function (response) {
                const leaders = response.data;
                const leaderList = $('#leaderList');
                leaderList.empty();
                let rowIndex = (page - 1) * 10 + 1;
                leaders.forEach(leader => {
                    const fullName = leader.name;
                    leaderList.append(`
                        <tr data-leader-id="${leader.id}"> <!-- Add the leader ID here -->
                            <td>${rowIndex++}</td>
                            <td>${fullName}</td>
                            <td>${leader.role_name}</td>
                            <td>${leader.appointment_date}</td>
                            <td>${leader.end_date || ''}</td>
                            <td>${leader.status}</td>
                            <td class="text-end">
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
                console.log('Error fetching leaders:', error);
                $('#loading').hide(); // Hide loading GIF even on error
            }
        });
    };

    // Load leaders initially
    fetchLeaders();

    // Search functionality
    $('#searchBtn').on('click', function () {
        const query = $('#searchLeader').val();
        fetchLeaders(1, query);
    });

    // Attach onchange event to search input
    $('#searchLeader').on('input', function () {
        const query = $(this).val();
        fetchLeaders(1, query);
    });

    // Pagination click event
    $(document).on('click', '.page-link', function (e) {
        e.preventDefault();
        const page = $(this).text();
        fetchLeaders(page);
    });
    
    // Edit button click event
    $(document).on('click', '.edit-btn', function () {
        const leaderId = $(this).closest('tr').data('leader-id');
        // Fetch leader details for editing
        $.ajax({
            url: `../api/data/church_leaders/${leaderId}`,
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                const leader = response.data;
                $('#leaderId').val(leader.id);
                $('#firstName').val(leader.first_name);
                $('#middleName').val(leader.middle_name);
                $('#lastName').val(leader.last_name);
                $('#role').val(leader.role_name);
                $('#appointmentDate').val(leader.appointment_date);
                $('#endDate').val(leader.end_date);
                $('#status').val(leader.status);
                $('#editLeaderModal').modal('show');
            }
        });
    });
});
</script>
</body>
</html>
