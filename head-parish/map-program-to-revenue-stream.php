<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');
check_session('head_parish_admin_id', '../head-parish/sign-in');
?>
<!doctype html>
<html lang="en">
<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Map Program to Revenue Stream - Kanisa Langu');
  ?>
</head>
<body>
<div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
     data-sidebar-position="fixed" data-header-position="fixed">
  <?php require_once('components/sidebar.php') ?>
  <div class="body-wrapper">
    <?php require_once('components/header.php') ?>
    <div class="container-fluid">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title fw-semibold mb-4">Map Program to Revenue Stream</h5>
          <form id="programMapForm" autocomplete="off">
            <div class="mb-3">
              <label for="program" class="form-label">Program</label>
              <select class="form-select" id="program" name="program">
                <option value="">Select Program</option>
                <option value="harambee">Harambee</option>
                <option value="bahasha">Bahasha</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="revenue_stream_id" class="form-label">Revenue Stream</label>
              <select class="form-select" id="revenue_stream_id" name="revenue_stream_id">
                <option value="">Select Revenue Stream</option>
              </select>
            </div>
            <button type="submit" class="btn btn-primary">Save Mapping</button>
          </form>
          <div id="responseMessage" class="mt-3"></div>
        </div>
      </div>
    </div>
  </div>
</div>
</body>

<?php require_once('components/footer_files.php') ?>

<script>
  $('select').select2({ width: '100%' });
  $(document).ready(function () {
    $.ajax({
      type: 'GET',
      url: '../api/data/head_parish_revenue_streams?limit=all',
      dataType: 'json',
      success: function (response) {
        let options = '<option value="">Select Revenue Stream</option>';
        $.each(response.data, function (index, stream) {
          options += '<option value="' + stream.revenue_stream_id + '">' + stream.revenue_stream_name + '</option>';
        });
        $('#revenue_stream_id').html(options);
      },
      error: function () {
        let messageHtml = '<div class="response-message error"><i class="fas fa-times-circle icon"></i>Failed to load revenue streams</div>';
        $('#responseMessage').html(messageHtml);
      }
    });

    $('#programMapForm').on('submit', function (e) {
      e.preventDefault();
      const formData = $(this).serialize();
      $('#responseMessage').html('<div class="response-message"><i class="fas fa-spinner fa-spin icon"></i> Mapping...</div>');
      $.ajax({
        type: 'POST',
        url: '../api/records/map_program_to_revenue.php',
        data: formData,
        dataType: 'json',
        success: function (response) {
          let messageHtml = '';
          if (response.success) {
            messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i>' + response.message + '</div>';
            setTimeout(function () { location.reload(); }, 1500);
          } else {
            messageHtml = '<div class="response-message error"><i class="fas fa-times-circle icon"></i>' + response.message + '</div>';
          }
          $('#responseMessage').html(messageHtml);
        },
        error: function (xhr, status, error) {
          let messageHtml = '<div class="response-message error"><i class="fas fa-times-circle icon"></i>An error occurred: ' + error + '</div>';
          $('#responseMessage').html(messageHtml);
        }
      });
    });
  });
</script>

</html>
