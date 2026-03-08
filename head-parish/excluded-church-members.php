<?php  
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
check_session('head_parish_admin_id', '../head-parish/sign-in');
?>
<!doctype html>
<html lang="en">
<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Manage Excluded Members - Kanisa Langu');
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
<div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6"
     data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
  <?php require_once('components/sidebar.php') ?>
  <div class="body-wrapper">
    <?php require_once('components/header.php') ?>
    <div class="container-fluid">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title fw-semibold mb-4">Manage Excluded Church Members</h5>

          <!-- Search Form -->
          <div class="row mb-4">
            <div class="col-md-8">
              <input type="text" id="searchExclusions" class="form-control" placeholder="Search by name, envelope, phone, or reason">
            </div>
            <div class="col-md-4 text-end">
              <button class="btn btn-primary" id="searchBtn">Search</button>
            </div>
          </div>

          <!-- Response Message -->
          <div id="responseMessage" class="mt-3"></div>

          <!-- Loading -->
          <div id="loading">
            <img src="../assets/images/gifs/loading.gif" alt="Loading..." height="100">
          </div>

          <!-- Table -->
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
              <tr>
                <th>#</th>
                <th>Member</th>
                <th>Envelope / Phone</th>
                <th>Reason</th>
                <th>Excluded On</th>
                <th>Actions</th>
              </tr>
              </thead>
              <tbody id="excludedList"></tbody>
            </table>
          </div>

          <!-- Pagination -->
          <nav id="paginationNav" aria-label="Pagination">
            <ul class="pagination justify-content-center"></ul>
          </nav>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteLabel">Confirm Deletion</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">Are you sure you want to remove this member's exclusion?</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="confirmDeleteBtn" class="btn btn-danger">Delete</button>
      </div>
    </div>
  </div>
</div>

<?php require_once('components/footer_files.php'); ?>

<script>
  $(document).ready(function () {
    let currentPage = 1;
    let currentQuery = '';
    const limit = 5;
    let excludeToDelete = null;
    let responseTimeout = null;

    function showResponseMessage(message, isSuccess = true) {
      const msg = `<div class="response-message ${isSuccess ? 'success' : 'error'}">${message}</div>`;
      $('#responseMessage').html(msg);
      clearTimeout(responseTimeout);
      responseTimeout = setTimeout(() => $('#responseMessage').html(''), 3000);
    }

    function fetchExclusions(page = 1, query = '') {
        currentPage = page;
        currentQuery = query;
        $('#loading').show();

        const url = `../api/data/excluded_church_members.php?limit=${limit}&page=${page}&query=${encodeURIComponent(query)}`;

        $.get(url, function (response) {
            $('#loading').hide();
        if (!response.success) {
          showResponseMessage(response.message, false);
          return;
        }

        const tbody = $('#excludedList');
        tbody.empty();

        if (response.data.length === 0) {
          tbody.append('<tr><td colspan="6" class="text-center">No excluded members found.</td></tr>');
        } else {
          let index = (page - 1) * limit + 1;
           response.data.forEach(item => {
            tbody.append(`
            <tr data-id="${item.exclusion_id}">
                <td>${index++}</td>
                <td>${item.member.full_name}</td>
                <td>Envelope: ${item.member.envelope_number || 'N/A'}<br>Phone: ${item.member.phone || 'N/A'}</td>
                <td>${item.reason}</td>
                <td>${item.excluded_datetime}</td>
                <td>
                <button class="btn btn-danger btn-sm delete-btn" data-id="${item.exclusion_id}" data-name="${item.member.full_name}">
                    <i class="fas fa-trash-alt"></i> Delete
                </button>
                </td>
            </tr>
            `);
          });
        }

        // Pagination
        const pagination = $('#paginationNav .pagination');
        pagination.empty();
        for (let i = 1; i <= response.total_pages; i++) {
          pagination.append(`<li class="page-item ${i === page ? 'active' : ''}">
            <a class="page-link" href="#">${i}</a>
          </li>`);
        }
      }, 'json');
    }

    fetchExclusions();

    $('#searchBtn').click(() => fetchExclusions(1, $('#searchExclusions').val()));

    $('#searchExclusions').on('input', () => {
      fetchExclusions(1, $('#searchExclusions').val());
    });

    $(document).on('click', '#paginationNav .page-link', function (e) {
      e.preventDefault();
      const page = parseInt($(this).text());
      fetchExclusions(page, currentQuery);
    });

    $(document).on('click', '.delete-btn', function () {
      excludeToDelete = {
        id: $(this).data('id'),
        name: $(this).data('name')
      };
      $('#deleteConfirmModal .modal-body').text(`Are you sure you want to remove exclusion for "${excludeToDelete.name}"?`);
      const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
      modal.show();
    });

    $('#confirmDeleteBtn').click(function () {
      if (!excludeToDelete) return;

      $.post('../api/delete/delete_excluded_member.php', { exclusion_id: excludeToDelete.id }, function (response) {
        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
        modal.hide();

        if (response.success) {
          showResponseMessage('<i class="fas fa-check-circle me-2"></i> ' + response.message);
          fetchExclusions(currentPage, currentQuery);
        } else {
          showResponseMessage('<i class="fas fa-times-circle me-2"></i> ' + response.message, false);
        }
      }, 'json').fail(() => {
        showResponseMessage('<i class="fas fa-times-circle me-2"></i> Failed to delete member.', false);
      });
    });
  });
</script>
</body>
</html>
