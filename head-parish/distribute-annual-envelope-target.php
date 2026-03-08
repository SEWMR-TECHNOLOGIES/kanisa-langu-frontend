<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
check_session('head_parish_admin_id', '../head-parish/sign-in');
?>
<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Distribute Envelope Target for Head Parish - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Distribute Envelope Target for Sub Parishes</h5>

            <form id="distributeEnvelopeTargetForm" autocomplete="off">
              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="sub_parish_id" class="form-label">Select Sub Parish</label>
                    <select class="form-control" id="sub_parish_id" name="sub_parish_id">
                      <!-- Options will be loaded dynamically using JavaScript -->
                    </select>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="from_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="from_date" name="from_date" value="2025-01-01">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="2025-12-31">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="percentage" class="form-label">Percentage</label>
                    <input type="number" class="form-control" id="percentage" name="percentage" placeholder="Enter percentage (0-100)" min="0" max="100">
                  </div>
                </div>
              </div>
              <button type="submit" class="btn btn-primary">Distribute Envelope Target</button>
              <div id="responseMessage"></div>
            </form>

          </div>
        </div>
      </div>
    </div>
  </div>

  <?php require_once('components/footer_files.php') ?>

  <script>
   $('select').select2({
    width: '100%'
  });
    // Load sub-parishes dynamically
    function loadSubParishes() {
      $.ajax({
        type: 'GET',
        url: '../api/data/head_parish_sub_parishes?limit=all', 
        dataType: 'json',
        success: function (response) {
          let options = '<option value="">Select Sub Parish</option>';
          $.each(response.data, function (index, subParish) {
            options += '<option value="' + subParish.sub_parish_id + '">' + subParish.sub_parish_name + '</option>';
          });
          $('#sub_parish_id').html(options); 
        },
        error: function (xhr, status, error) {
          console.log('Error loading sub-parishes:', error);
        }
      });
    }

    // Submit the form to distribute the envelope target to sub-parishes
    $(document).ready(function () {
      loadSubParishes();  // Load sub-parishes on page load

      $('#distributeEnvelopeTargetForm').on('submit', function (event) {
        event.preventDefault();
        $('#responseMessage').html('<div class="response-message"><i class="fas fa-spinner fa-spin icon"></i> Distributing Envelope Target...</div>');

        var distributionData = {
          sub_parish_id: $('#sub_parish_id').val(),
          percentage: $('#percentage').val(),
          from_date: $('#from_date').val(),
          end_date: $('#end_date').val()
        };

        $.ajax({
          type: 'POST',
          url: '../api/records/distribute_annual_head_parish_envelope_target.php', // Endpoint to distribute the envelope target
          data: distributionData,
          dataType: 'json',
          success: function (response) {
            let messageHtml = '';
            if (response.success) {
              messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i>' + response.message + '</div>';
              setTimeout(function () { location.reload(); }, 2000);
            } else {
              messageHtml = '<div class="response-message error"><i class="fas fa-times-circle icon"></i>' + response.message + '</div>';
            }
            $('#responseMessage').html(messageHtml);
          },
          error: function () {
            $('#responseMessage').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i>An error occurred.</div>');
          }
        });
      });
    });
  </script>

</body>
</html>
