<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
check_session('head_parish_admin_id', '../head-parish/sign-in');
?>
<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Church Members Accounts - Kanisa Langu');
  ?>
  <style>
    #loading {
      display: none;
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
            <h5 class="card-title fw-semibold mb-4">Church Members Accounts</h5>

            <!-- Search Form -->
            <div class="row mb-4">
              <div class="col-md-8">
                <input type="text" id="searchMember" class="form-control" placeholder="Search Members by Name or Envelope Number">
              </div>
              <div class="col-md-4 text-end">
                <button class="btn btn-primary" id="searchBtn">Search</button>
              </div>
            </div>

            <!-- Loading GIF -->
            <div id="loading">
              <img src="../assets/images/gifs/loading.gif" alt="Loading..." height="100">
            </div>

            <!-- Church Members Table -->
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Full Name</th>
                    <th>Phone Number</th>
                    <th>Sub Parish</th>
                    <th>Type</th>
                    <th>Envelope No</th>
                    <th>Date Created</th>
                  </tr>
                </thead>
                <tbody id="memberList">
                  <!-- Table rows will be populated here using AJAX -->
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <nav id="paginationNav" aria-label="Member pagination">
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
    const fetchMembers = (page = 1, query = '') => {
        $('#loading').show(); // Show loading GIF
        $.ajax({
            type: 'GET',
            url: `../api/data/church_members_accounts?page=${page}&query=${query}`,
            dataType: 'json',
            success: function (response) {
                const members = response.data;
                const memberList = $('#memberList');
                memberList.empty();
                let rowIndex = (page - 1) * 10 + 1;
                members.forEach(member => {
                    const fullName = `${member.first_name} ${member.middle_name || ''} ${member.last_name}`.trim();
                    memberList.append(`
                        <tr>
                            <td>${rowIndex++}</td>
                            <td>${fullName}</td>
                            <td>${member.phone || ''}</td>
                            <td>${member.sub_parish_name || ''}</td>
                            <td>${member.type}</td>
                            <td>${member.envelope_number || ''}</td>
                            <td>${member.account_created_at}</td>
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
                console.log('Error fetching members:', error);
                $('#loading').hide(); // Hide loading GIF even on error
            }
        });
    };

    // Load members initially
    fetchMembers();

    // Search functionality
    $('#searchBtn').on('click', function () {
        const query = $('#searchMember').val();
        fetchMembers(1, query);
    });
    
    // Attach onchange event to search input
    $('#searchMember').on('input', function () {
        const query = $(this).val();
        fetchMembers(1, query);
    });

    // Pagination click event
    $(document).on('click', '#paginationNav .page-link', function (e) {
        e.preventDefault();
        const page = $(this).text();
        const query = $('#searchMember').val();
        fetchMembers(page, query);
    });
});
</script>
</body>

</html>
