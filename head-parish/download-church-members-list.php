<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
check_session('head_parish_admin_id', '../head-parish/sign-in');
?>
<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Download Church Members List - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Download Church Members List</h5>
            <form id="churchMemberForm">

              <div class="row">
                <div class="col-lg-4">
                  <div class="mb-3">
                    <label for="subParishId" class="form-label">Sub Parish</label>
                    <select class="form-select" id="subParishId" name="sub_parish_id">
                      <option value="">Select Sub Parish</option>
                      <!-- Options will be populated by AJAX -->
                    </select>
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="mb-3">
                    <label for="communityId" class="form-label">Community</label>
                    <select class="form-select" id="communityId" name="community_id">
                      <option value="">Select Community</option>
                      <!-- Options will be populated by AJAX -->
                    </select>
                  </div>
                </div>
                <div class="col-lg-4">
                    <div class="mb-3">
                      <label for="gender" class="form-label">Gender</label>
                      <select class="form-select" id="gender" name="gender">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="">Other / Not Specified</option>
                        <option value="all">All</option>
                      </select>
                    </div>
                </div>
              </div>

              <div class="mb-3">
                <!-- Button for downloading PDF -->
                <button type="button" class="btn btn-danger" id="downloadPDF">Download PDF</button>
                <!-- Button for downloading Excel -->
                <button type="button" class="btn btn-success" id="downloadXLS">Download Excel</button>
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

    // Handle PDF download
    $('#downloadPDF').click(function () {
      var subParishId = $('#subParishId').val();
      var communityId = $('#communityId').val();
      var gender = $('#gender').val(); // <-- Grab gender
    
      if (!subParishId || isNaN(subParishId)) {
        $('#responseMessage').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i> Sub Parish is required.</div>');
        return;
      }
      if (!communityId || isNaN(communityId)) {
        $('#responseMessage').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i> Community is required.</div>');
        return;
      }
    
      const url = `../reports/download_church_members_list_pdf.php?sub_parish_id=${encodeURIComponent(subParishId)}&community_id=${encodeURIComponent(communityId)}&gender=${encodeURIComponent(gender)}`;
      window.open(url, '_blank');
    });


    // Handle Excel download
    $('#downloadXLS').click(function () {
      var subParishId = $('#subParishId').val();
      var communityId = $('#communityId').val();
      var gender = $('#gender').val(); // <-- Grab gender
    
      if (!subParishId || isNaN(subParishId)) {
        $('#responseMessage').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i> Sub Parish is required.</div>');
        return;
      }
      if (!communityId || isNaN(communityId)) {
        $('#responseMessage').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i> Community is required.</div>');
        return;
      }
    
      const url = `../reports/download_church_members_list_xls.php?sub_parish_id=${encodeURIComponent(subParishId)}&community_id=${encodeURIComponent(communityId)}&gender=${encodeURIComponent(gender)}`;
      window.open(url, '_blank');
    });

});
</script>

</body>

</html>
