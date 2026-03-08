<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
check_session('head_parish_admin_id', '../head-parish/sign-in');
?>
<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Upload Church Member Data - Kanisa Langu');
  ?>
</head>

<body>
  <!-- Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <!-- Sidebar -->
    <?php require_once('components/sidebar.php') ?>
    <!-- Main wrapper -->
    <div class="body-wrapper">
      <!-- HEADER -->
      <?php require_once('components/header.php') ?>
      <div class="container-fluid">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Upload Church Member Data</h5>
            <!-- Guide for Users -->
            <div class="alert alert-info">
              <h6><strong>Excel File Format:</strong></h6>
              <div class="row">
                <div class="col-md-12 mb-3">
                  <ul class="mb-0">
                    <li><strong>Column A:</strong> S/N</li>
                    <li><strong>Column B:</strong> Full Name</li>
                    <li><strong>Column C:</strong> Envelope Number</li>
                    <li><strong>Column D:</strong> Phone Number</li>
                  </ul>
                </div>
              </div>
              <p class="mt-3">
                <strong>Note:</strong> Please ensure the Excel file has <u>only one header row</u> at the top. All member data should begin from the second row.
              </p>
            </div>

            <form id="churchMemberForm" autocomplete="off" enctype="multipart/form-data">
              <input type="hidden" id="headParishId" name="head_parish_id" value="<?php echo htmlspecialchars($_SESSION['head_parish_id']); ?>">


              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="subParishId" class="form-label">Sub Parish</label>
                    <select class="form-select" id="subParishId" name="sub_parish_id">
                      <option value="">Select Sub Parish</option>
                      <!-- Options will be populated by AJAX -->
                    </select>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="communityId" class="form-label">Community</label>
                    <select class="form-select" id="communityId" name="community_id">
                      <option value="">Select Community</option>
                      <!-- Options will be populated by AJAX -->
                    </select>
                  </div>
                </div>
              </div>

              <div class="row">
                <!-- File Upload -->
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="excelFile" class="form-label">Excel File</label>
                    <input type="file" class="form-control" id="excelFile" name="member_data" 
                           accept=".xls, .xlsx" required>
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <button type="submit" class="btn btn-primary">Upload Members Details</button>
              </div>
              <div id="responseMessage"></div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php require_once('components/footer_files.php') ?>
  <script>
$(document).ready(function () {
  // Initialize Select2 for all select inputs
  $('select').select2({
    width: '100%'
  });
  
    // Get returnUrl from query string
    const urlParams = new URLSearchParams(window.location.search);
    const returnUrl = urlParams.get('returnUrl');

  // Load sub parishes into the select
  $.ajax({
    type: 'GET',
    url: '../api/data/head_parish_sub_parishes?limit=all',
    data: { head_parish_id: $('#headParishId').val() },
    dataType: 'json',
    success: function (response) {
      var options = '<option value="">Select Sub Parish</option>';
      $.each(response.data, function (index, sub_parish) {
        options += '<option value="' + sub_parish.sub_parish_id + '">' + sub_parish.sub_parish_name + '</option>';
      });
      $('#subParishId').html(options).trigger('change'); // Trigger change to update Select2
    },
    error: function (xhr, status, error) {
      console.log('Error loading sub parishes:', error);
    }
  });

  // Load communities into the select based on selected sub parish
  $('#subParishId').change(function () {
    var subParishId = $(this).val();
    $.ajax({
      type: 'GET',
      url: '../api/data/head_parish_communities?limit=all', 
      data: { sub_parish_id: subParishId },
      dataType: 'json',
      success: function (response) {
        var options = '<option value="">Select Community</option>';
        $.each(response.data, function (index, community) {
          options += '<option value="' + community.community_id + '">' + community.community_name + '</option>';
        });
        $('#communityId').html(options).trigger('change'); // Trigger change to update Select2
      },
      error: function (xhr, status, error) {
        console.log('Error loading communities:', error);
      }
    });
  });



  // Handle form submission
  $('#churchMemberForm').on('submit', function (event) {
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
        url: '../api/records/upload_church_members_data.php', 
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (response) {
          let messageHtml = '';
          if (response.success) {
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
