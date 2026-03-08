<?php  
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
check_session('head_parish_admin_id', '../head-parish/sign-in');
?>
<!doctype html>
<html lang="en">
<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Manage Church Member Exclusion Reasons - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Manage Church Member Exclusion Reasons</h5>

            <div class="alert alert-info mb-4">
              These reasons will exclude a church member from church operations such as Harambee, envelopes, and other daily church activities.
            </div>

            <!-- Search Form -->
            <div class="row mb-4">
              <div class="col-md-8">
                <input type="text" id="searchReasons" class="form-control" placeholder="Search exclusion reasons">
              </div>
              <div class="col-md-4 text-end">
                <button class="btn btn-primary" id="searchBtn">Search</button>
              </div>
            </div>

            <!-- Response message -->
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
                    <th>Reason</th>
                    <th>Created At</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="reasonList">
                  <!-- Rows loaded here -->
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <nav id="paginationNav" aria-label="Exclusion Reason pagination">
              <ul class="pagination justify-content-center"></ul>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteConfirmLabel">Confirm Delete</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to delete this exclusion reason?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" id="confirmDeleteBtn" class="btn btn-danger">Delete</button>
        </div>
      </div>
    </div>
  </div>

  <?php require_once('components/footer_files.php') ?>

  <script>
    $(document).ready(function () {
      let currentPage = 1;
      let currentQuery = '';
      let reasonToDelete = null;
      const limit = 5;
      let responseTimeout = null;

      function showResponseMessage(message, isSuccess = true) {
        let messageHtml = '';
        if (isSuccess) {
            messageHtml = `<div class="response-message success"> ${message}</div>`;
        } else {
            messageHtml = `<div class="response-message error"> ${message}</div>`;
        }
        $('#responseMessage').html(messageHtml);
        clearTimeout(responseTimeout);
        responseTimeout = setTimeout(() => {
            $('#responseMessage').html('');
        }, 2000);
      }

      function fetchReasons(page = 1, query = '') {
        currentPage = page;
        currentQuery = query;

        $('#loading').show();
        $.ajax({
          type: 'GET',
          url: `../api/data/church_member_exclusion_reasons.php?limit=${limit}&page=${page}&query=${encodeURIComponent(query)}`,
          dataType: 'json',
          success: function(response) {
            $('#loading').hide();
            if (!response.success) {
              showResponseMessage('Failed to load reasons: ' + response.message, false);
              return;
            }
            const reasons = response.data;
            const tbody = $('#reasonList');
            tbody.empty();

            if (reasons.length === 0) {
              tbody.append('<tr><td colspan="4" class="text-center">No exclusion reasons found.</td></tr>');
            } else {
              let rowIndex = (page - 1) * limit + 1;
              reasons.forEach(reason => {
                tbody.append(`
                  <tr data-id="${reason.exclusion_reason_id}">
                    <td>${rowIndex++}</td>
                    <td>${reason.reason}</td>
                    <td>${new Date(reason.created_at).toLocaleString('en-GB')}</td>
                    <td>
                      <button class="btn btn-danger btn-sm delete-btn" data-id="${reason.exclusion_reason_id}" data-reason="${reason.reason}">
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
            for(let i = 1; i <= response.total_pages; i++) {
              pagination.append(`
                <li class="page-item ${i === page ? 'active' : ''}">
                  <a class="page-link" href="#">${i}</a>
                </li>
              `);
            }
          },
          error: function() {
            $('#loading').hide();
            showResponseMessage('Error loading exclusion reasons.', false);
          }
        });
      }

      // Initial load
      fetchReasons();

      // Search
      $('#searchBtn').on('click', function() {
        fetchReasons(1, $('#searchReasons').val());
      });
      $('#searchReasons').on('input', function() {
        fetchReasons(1, $(this).val());
      });

      // Pagination click
      $(document).on('click', '#paginationNav .page-link', function(e) {
        e.preventDefault();
        const page = parseInt($(this).text());
        fetchReasons(page, currentQuery);
      });

      // Delete button click
      $(document).on('click', '.delete-btn', function() {
        reasonToDelete = {
          id: $(this).data('id'),
          reason: $(this).data('reason')
        };
        $('#deleteConfirmModal .modal-body').text(`Are you sure you want to delete the exclusion reason: "${reasonToDelete.reason}"?`);
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
        deleteModal.show();
      });

      // Confirm delete
      $('#confirmDeleteBtn').on('click', function() {
        if (!reasonToDelete) return;

        $.ajax({
          type: 'POST',
          url: '../api/delete/delete_church_member_exclusion_reason.php',
          data: { reason_id: reasonToDelete.id },
          dataType: 'json',
          success: function(response) {
            const deleteModalEl = document.getElementById('deleteConfirmModal');
            const deleteModal = bootstrap.Modal.getInstance(deleteModalEl);
            deleteModal.hide();

            if (response.success) {
              showResponseMessage('<i class="fas fa-check-circle me-2"></i> ' + response.message, true);
              fetchReasons(currentPage, currentQuery);
            } else {
              showResponseMessage('<i class="fas fa-times-circle me-2"></i> Delete failed: ' + response.message, false);
            }
          },
          error: function() {
            showResponseMessage('<i class="fas fa-times-circle me-2"></i> Error deleting exclusion reason.', false);
          }
        });
      });

    });
  </script>
</body>
</html>
