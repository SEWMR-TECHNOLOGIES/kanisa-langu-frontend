<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');
check_session('head_parish_admin_id', '../head-parish/sign-in');

// Calculate current week's Sunday (default date)
$currentDate = date('Y-m-d');
$currentWeekSunday = date('Y-m-d', strtotime('last sunday', strtotime($currentDate)));

// If today is Sunday, set the date to today (so it doesn't go back a week)
if (date('l', strtotime($currentDate)) === 'Sunday') {
    $currentWeekSunday = $currentDate;
}
?>
<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Upload Envelope Data - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Upload Envelope Data</h5>

            <!-- Guide for Users -->
            <div class="alert alert-info">
              <h6><strong>Excel File Format:</strong></h6>
              <ul>
                <li><strong>Column A:</strong> Envelope Number</li>
                <li><strong>Column B:</strong> Amount Paid</li>
                <li><strong>Column C (optional):</strong> Payment Method (Valid values: Cash, Card, Mobile Payment, Bank Transfer)</li>
              </ul>
              <p><strong>Note:</strong> If the Payment Method column is left blank, it will be considered "Cash" by default. Please ensure the Excel file follows this order for successful data upload.</p>
            </div>

            <!-- Upload Envelope Data Form -->
            <form id="uploadEnvelopeDataForm" autocomplete="off" enctype="multipart/form-data">
              <div class="row">
                <!-- Date Field -->
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="uploadDate" class="form-label">Date</label>
                    <input type="date" class="form-control" id="uploadDate" name="date" 
                           max="<?php echo date('Y-m-d'); ?>" 
                           placeholder="Select Date"
                           value="<?php echo $currentWeekSunday; ?>"> <!-- Set the default date to current week's Sunday -->
                  </div>
                </div>
                
                <!-- File Upload -->
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="excelFile" class="form-label">Excel File</label>
                    <input type="file" class="form-control" id="excelFile" name="harambee_data" 
                           accept=".xls, .xlsx" required>
                  </div>
                </div>
              </div>
              
              <!-- Submit Button -->
              <div class="mb-3">
                <button type="submit" class="btn btn-primary">Upload Data</button>
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
    // Handle form submission
    $('#uploadEnvelopeDataForm').submit(function (event) {
      event.preventDefault();

      $('#responseMessage').html('<div class="loading">Uploading...</div>');

      // Create FormData object
      let formData = new FormData(this);
      
      // Get the local timestamp
      let localTime = getLocalTimestamp();
      
      // Append the local timestamp to the formData object
      formData.append('local_timestamp', localTime);
      
      // AJAX request to upload the file and data
      $.ajax({
        type: 'POST',
        url: '../api/records/upload_envelope_data.php', 
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (response) {
          let messageHtml = '';
          if (response.success) {
            $('#uploadEnvelopeDataForm')[0].reset();
            messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i>' + response.message + '</div>';
            if (response.download_url) {
              messageHtml += `<br><a href="${response.download_url}" class="btn btn-secondary" download>Download Missing Data</a>`;
            }
            $('#responseMessage').html(messageHtml);
          } else {
            messageHtml = '<div class="response-message error"><i class="fas fa-times-circle icon"></i>' + response.message + '</div>';
          }
          $('#responseMessage').html(messageHtml);
        },
        error: function (xhr, status, error) {
          const errorMessage = xhr.responseJSON?.message || `An error occurred.${error}`;
          $('#responseMessage').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i>' + errorMessage + '</div>');
        }
      });
    });
  });
</script>
</body>

</html>
