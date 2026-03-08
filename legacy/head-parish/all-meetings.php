<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
// Ensure the user is authenticated as superadmin
check_session('head_parish_admin_id', '../head-parish/sign-in');
?>
<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Church Meetings - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Church Meetings</h5>

            <!-- Search Form -->
            <div class="row mb-4">
              <div class="col-md-8">
                <input type="text" id="searchMeeting" class="form-control" placeholder="Search Meetings by Title or Description">
              </div>
              <div class="col-md-4 text-end">
                <button class="btn btn-primary" id="searchBtn">Search</button>
              </div>
            </div>

            <!-- Loading GIF -->
            <div id="loading">
              <img src="../assets/images/gifs/loading.gif" alt="Loading..." height="100">
            </div>

            <!-- Meetings Table -->
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Meeting Title</th>
                    <th>Meeting Description</th>
                    <th>Meeting Date</th>
                    <th>Meeting Place</th>
                    <th>Meeting Time</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="meetingList">
                  <!-- Table rows will be populated here using AJAX -->
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <nav id="paginationNav" aria-label="Meeting pagination">
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
    const fetchMeetings = (page = 1, query = '') => {
        $('#loading').show(); // Show loading GIF
        $.ajax({
            type: 'GET',
            url: `../api/data/meetings.php?page=${page}&query=${query}`,
            dataType: 'json',
            success: function (response) {
                const meetings = response.data;
                const meetingList = $('#meetingList');
                meetingList.empty();
                let rowIndex = (page - 1) * 10 + 1;
                meetings.forEach(meeting => {
                    meetingList.append(`
                        <tr>
                            <td>${rowIndex++}</td>
                            <td>${meeting.meeting_title}</td>
                            <td>${meeting.meeting_description}</td>
                            <td>${meeting.meeting_date}</td>
                            <td>${meeting.meeting_place}</td>
                            <td>${meeting.meeting_time}</td>
                            <td>
                                <a href="/reports/meeting.php?meeting_id=${meeting.meeting_id}" class="btn btn-success btn-sm" target="_blank">Download PDF</a>
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
                console.log('Error fetching meetings:', error);
                $('#loading').hide(); // Hide loading GIF even on error
            }
        });
    };

    // Load meetings initially
    fetchMeetings();

    // Search functionality
    $('#searchBtn').on('click', function () {
        const query = $('#searchMeeting').val();
        fetchMeetings(1, query);
    });
    
    // Attach onchange event to search input
    $('#searchMeeting').on('input', function () {
        const query = $(this).val();
        fetchMeetings(1, query);
    });

    // Pagination click event
    $(document).on('click', '#paginationNav .page-link', function (e) {
        e.preventDefault();
        const page = $(this).text();
        const query = $('#searchMeeting').val();
        fetchMeetings(page, query);
    });
});
</script>
</body>

</html>
