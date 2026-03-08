<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
check_session('head_parish_admin_id', '../head-parish/sign-in');
?>
<!doctype html>
<html lang="en">
<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('New Church Event');
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
            <h5 class="card-title fw-semibold mb-4">Create Church Event</h5>

            <form id="churchEventForm" autocomplete="off">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="title" class="form-label">Event Title</label>
                  <input type="text" class="form-control" id="title" name="title" placeholder="Enter event title">
                </div>

                <div class="col-md-6 mb-3">
                  <label for="event_date" class="form-label">Start Date</label>
                  <input type="date" class="form-control" id="event_date" name="event_date">
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="end_date" class="form-label">End Date (optional)</label>
                  <input type="date" class="form-control" id="end_date" name="end_date">
                </div>

                <div class="col-md-6 mb-3">
                  <label for="location" class="form-label">Location</label>
                  <input type="text" class="form-control" id="location" name="location" placeholder="Event location">
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="start_time" class="form-label">Start Time</label>
                  <input type="time" class="form-control" id="start_time" name="start_time">
                </div>

                <div class="col-md-6 mb-3">
                  <label for="end_time" class="form-label">End Time</label>
                  <input type="time" class="form-control" id="end_time" name="end_time">
                </div>
              </div>

              <div class="row">
                <div class="col-md-12 mb-3">
                  <label for="description" class="form-label">Event Description</label>
                  <textarea class="form-control" id="description" name="description" rows="3" placeholder="Describe the event"></textarea>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12 mb-3">
                  <label for="target_audience" class="form-label">Target Audience</label>
                  <input type="text" class="form-control" id="target_audience" name="target_audience" placeholder="e.g., Youth, Women, All Members">
                </div>
              </div>

              <div class="row">
                <div class="col-md-12 mb-3">
                  <label for="notes" class="form-label">Notes (optional)</label>
                  <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Any additional notes"></textarea>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <button type="submit" class="btn btn-success w-100">Save Event</button>
                </div>
              </div>
            </form>

            <div id="responseMessage" class="mt-3"></div>
          </div>
        </div>
      </div>
    </div>
</div>

<?php require_once('components/footer_files.php') ?>

<script>
  $(document).ready(function () {
    $('#churchEventForm').on('submit', function (e) {
      e.preventDefault();
      const formData = $(this).serialize();

      $('#responseMessage').html('<div class="response-message mb-2"><i class="fas fa-spinner fa-spin icon"></i> Sending Request...</div>');

      $.ajax({
        type: 'POST',
        url: '../api/records/record_church_event.php',
        data: formData,
        dataType: 'json',
        success: function (response) {
          let messageHtml = '';
          if (response.success) {
            messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i> ' + response.message + '</div>';
            $('#responseMessage').html(messageHtml);
            setTimeout(() => {
              $('#responseMessage').empty();
              $('#churchEventForm')[0].reset();
            }, 2000);
          } else {
            messageHtml = '<div class="response-message error"><i class="fas fa-times-circle icon"></i> ' + response.message + '</div>';
            $('#responseMessage').html(messageHtml);
            setTimeout(() => $('#responseMessage').empty(), 5000);
          }
        },
        error: function (xhr, status, error) {
          const errorMessage = '<div class="response-message error"><i class="fas fa-times-circle icon"></i> An error occurred: ' + error + '</div>';
          $('#responseMessage').html(errorMessage);
          setTimeout(() => $('#responseMessage').empty(), 5000);
        }
      });
    });
  });
</script>

</body>
</html>
