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
    render_header('Record Harambee Contribution - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Upload Harambee Targets</h5>
            <!-- Guide for Users -->
            <div class="alert alert-info">
              <h6><strong>Excel File Format:</strong></h6>
              <div class="row">
                <!-- Individual Target Type -->
                <div class="col-md-6 mb-3">
                  <h6><strong>For Individual Target Type:</strong></h6>
                  <ul class="mb-0">
                    <li><strong>Column A:</strong> S/N</li>
                    <li><strong>Column B:</strong> Church Member Name</li>
                    <li><strong>Column C:</strong> Phone /Envelope Number</li>
                    <li><strong>Column D:</strong> Target Amount</li>
                  </ul>
                </div>
                <!-- Group Target Type -->
                <div class="col-md-6 mb-3">
                  <h6><strong>For Group Target Type:</strong></h6>
                  <ul class="mb-0">
                    <li><strong>Column A:</strong> S/N</li>
                    <li><strong>Column B:</strong> Group Name (e.g., Mr and Mrs John Doe)</li>
                    <li><strong>Column C:</strong> Phone / Envelope Number of Member 1</li>
                    <li><strong>Column D:</strong> Phone / Envelope Number of Member 2</li>
                    <li><strong>Column E:</strong> Group Target Amount</li>
                  </ul>
                </div>
              </div>
              <p class="mt-3">
                 <strong>Note:</strong>Please ensure the Excel file follows the correct format based on the selected Target Type, and contains <u>only one header row</u> at the top. All data should begin from the second row.
             </p>
            <p class="mt-2">
                <strong>Important:</strong> If a phone number is used instead of an envelope number, it must start with <code>255</code> followed by exactly <code>9 digits</code> (e.g., <code>255712345678</code>).
              </p>
            </div>


            <!-- Harambee Contribution Form -->
            <form id="uploadHarambeeTargetsDataForm" autocomplete="off" enctype="multipart/form-data">
              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="targetTypeSelectionIndividual" class="form-label">Select Type</label>
                    <select class="form-select" id="targetTypeSelectionIndividual" name="target">
                      <option value="">Select Type</option>
                      <option value="head-parish">Head Parish</option>
                      <option value="sub-parish">Sub Parish</option>
                      <option value="community">Community</option>
                      <option value="groups">Groups</option>
                    </select>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="harambeeIdIndividual" class="form-label">Harambee</label>
                    <select class="form-select" id="harambeeIdIndividual" name="harambee_id">
                      <option value="">Select Harambee</option>
                      <!-- Options populated by AJAX -->
                    </select>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="targetType" class="form-label">Select Target Type</label>
                    <select class="form-select" id="targetType" name="target_type">
                      <option value="individual">Individual</option>
                      <option value="group">Group</option>
                    </select>
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
            <div class="mb-3 d-flex flex-column flex-md-row">
              <button type="submit" class="btn btn-primary mb-2 mb-md-0 w-100 w-md-auto">Upload Harambee Targets</button>
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
  $('select').select2({
    width: '100%'
  });

  $(document).ready(function () {

    $('#targetTypeSelectionIndividual').change(function () {
      const selectedType = $(this).val();
      loadHarambee('harambeeIdIndividual', `../api/data/head_parish_harambee?limit=all&target=${selectedType}`);
    });

    function loadHarambee(targetId, url) {
      $.ajax({
        type: 'GET',
        url: url,
        dataType: 'json',
        success: function (response) {
          let options = '<option value="">Select Harambee</option>';
          $.each(response.data, function (index, harambee) {
            options += `<option value="${harambee.harambee_id}">${harambee.description} - ${harambee.from_date} - ${harambee.to_date} - TZS ${harambee.amount}</option>`;
          });
          $('#' + targetId).html(options);
        },
        error: function (xhr, status, error) {
          console.log('Error loading harambee:', error);
        }
      });
    }

    $('#uploadHarambeeTargetsDataForm').submit(function (event) {
      event.preventDefault();
      $('#responseMessage').html('<div class="response-message"><i class="fas fa-spinner fa-spin icon"></i> Uploading Targets...</div>');
      
      let formData = new FormData(this);
      let localTime = getLocalTimestamp();
      formData.append('local_timestamp', localTime);

      $.ajax({
        type: 'POST',
        url: '../api/records/upload_harambee_targets.php', 
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (response) {
          let messageHtml = '';
          if (response.success) {
            $('#uploadHarambeeTargetsDataForm')[0].reset();
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
