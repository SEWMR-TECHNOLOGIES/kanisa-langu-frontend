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
    render_header('Church Events - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Church Events</h5>

            <div class="row mb-4">
              <div class="col-md-8">
                <input type="text" id="searchEvent" class="form-control" placeholder="Search Events by Title or Description">
              </div>
              <div class="col-md-4 text-end">
                <button class="btn btn-primary" id="searchBtn">Search</button>
              </div>
            </div>

            <div id="loading">
              <img src="../assets/images/gifs/loading.gif" alt="Loading..." height="100">
            </div>

            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Event Title</th>
                    <th>Description</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Location</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="eventList">
                  <!-- Event rows will be dynamically loaded -->
                </tbody>
              </table>
            </div>

            <nav id="paginationNav" aria-label="Event pagination">
              <div class="overflow-auto scroll-sidebar" data-simplebar="">
                <ul class="pagination justify-content-start"></ul>
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
    const fetchEvents = (page = 1, query = '') => {
        $('#loading').show();
        $.ajax({
            type: 'GET',
            url: `../api/data/church_events.php?page=${page}&query=${query}`,
            dataType: 'json',
            success: function (response) {
                const events = response.data;
                const eventList = $('#eventList');
                eventList.empty();
                let rowIndex = (page - 1) * 10 + 1;

                events.forEach(event => {
                    eventList.append(`
                        <tr>
                            <td>${rowIndex++}</td>
                            <td>${event.title}</td>
                            <td>${event.description}</td>
                            <td>${event.event_date}</td>
                            <td>${event.start_time} - ${event.end_time}</td>
                            <td>${event.location}</td>
                            <td class="d-flex gap-2">
                                <button class="btn btn-sm btn-warning edit-btn" data-id="${event.id}" data-title="${event.title}" data-bs-toggle="tooltip" title="Edit Event">
                                    <i class="ti ti-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-btn" data-id="${event.id}" data-title="${event.title}" data-bs-toggle="tooltip" title="Delete Event">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `);
                });

                $('#paginationNav .pagination').empty();
                for (let i = 1; i <= response.total_pages; i++) {
                    $('#paginationNav .pagination').append(`
                        <li class="page-item ${i == page ? 'active' : ''}">
                            <a class="page-link" href="#">${i}</a>
                        </li>
                    `);
                }

                $('#loading').hide();
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
            },
            error: function () {
                $('#loading').hide();
            }
        });
    };

    fetchEvents();

    $('#searchBtn').on('click', function () {
        const query = $('#searchEvent').val();
        fetchEvents(1, query);
    });

    $('#searchEvent').on('input', function () {
        const query = $(this).val();
        fetchEvents(1, query);
    });

    $(document).on('click', '#paginationNav .page-link', function (e) {
        e.preventDefault();
        const page = $(this).text();
        const query = $('#searchEvent').val();
        fetchEvents(page, query);
    });

});
</script>
</body>
</html>
