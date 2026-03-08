<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Add Church Member - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Add New Church Member</h5>
            <form id="churchMemberForm">
              <input type="hidden" id="headParishId" name="head_parish_id" value="<?php echo htmlspecialchars($_SESSION['head_parish_id']); ?>">

              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="titleId" class="form-label">Title (optional)</label>
                    <select class="form-select" id="titleId" name="title_id">
                      <option value="">Select Title</option>
                      <!-- Options will be populated by AJAX -->
                    </select>
                  </div>
                </div>

                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="firstName" class="form-label">First Name</label>
                    <input type="text" class="form-control" id="firstName" name="first_name" placeholder="Enter First Name">
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="middleName" class="form-label">Middle Name (optional)</label>
                    <input type="text" class="form-control" id="middleName" name="middle_name" placeholder="Enter Middle Name">
                  </div>
                </div>

                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="lastName" class="form-label">Last Name</label>
                    <input type="text" class="form-control" id="lastName" name="last_name" placeholder="Enter Last Name">
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="dob" class="form-label">Date of Birth</label>
                    <input type="date" class="form-control" id="dob" name="date_of_birth" value="<?php echo date('Y-m-d', strtotime('1970-01-01')); ?>">
                  </div>
                </div>

                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="gender" class="form-label">Gender</label>
                    <select class="form-select" id="gender" name="gender">
                      <option value="">Select Gender</option>
                      <option value="Male">Male</option>
                      <option value="Female">Female</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="type" class="form-label">Type</label>
                    <select class="form-select" id="type" name="type">
                      <option value="">Select Type</option>
                      <option value="Mgeni">Mgeni</option>
                      <option value="Mwenyeji">Mwenyeji</option>
                    </select>
                  </div>
                </div>

                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="subParishId" class="form-label">Sub Parish</label>
                    <select class="form-select" id="subParishId" name="sub_parish_id">
                      <option value="">Select Sub Parish</option>
                      <!-- Options will be populated by AJAX -->
                    </select>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="communityId" class="form-label">Community</label>
                    <select class="form-select" id="communityId" name="community_id">
                      <option value="">Select Community</option>
                      <!-- Options will be populated by AJAX -->
                    </select>
                  </div>
                </div>

                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="occupationId" class="form-label">Occupation (Optional)</label>
                    <select class="form-select" id="occupationId" name="occupation_id">
                      <option value="">Select Occupation</option>
                      <!-- Options will be populated by AJAX -->
                    </select>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="phone" class="form-label">Phone (optional)</label>
                    <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter Phone (255XXXXXXXXX)">
                  </div>
                </div>

                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="email" class="form-label">Email (optional)</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email">
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="envelopeNumber" class="form-label">Envelope Number (optional)</label>
                    <input type="text" class="form-control" id="envelopeNumber" name="envelope_number" placeholder="Enter Envelope Number (e.g., Y26)">
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <button type="submit" class="btn btn-primary">Register Member</button>
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

  // Load titles into the select
  $.ajax({
    type: 'GET',
    url: '../api/data/titles?limit=all', // Update with your API endpoint for titles
    dataType: 'json',
    success: function (response) {
      var options = '<option value="">Select Title</option>';
      $.each(response.data, function (index, title) {
        options += '<option value="' + title.id + '">' + title.name + '</option>';
      });
      $('#titleId').html(options).trigger('change'); // Trigger change to update Select2
    },
    error: function (xhr, status, error) {
      console.log('Error loading titles:', error);
    }
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

  // Load occupations into the select
  $.ajax({
    type: 'GET',
    url: '../api/data/occupations?limit=all', // Update with your API endpoint for occupations
    dataType: 'json',
    success: function (response) {
      var options = '<option value="">Select Occupation</option>';
      $.each(response.data, function (index, occupation) {
        options += '<option value="' + occupation.id + '">' + occupation.name + '</option>';
      });
      $('#occupationId').html(options).trigger('change'); // Trigger change to update Select2
    },
    error: function (xhr, status, error) {
      console.log('Error loading occupations:', error);
    }
  });

  // Handle form submission
  $('#churchMemberForm').on('submit', function (event) {
    event.preventDefault();

    var memberData = {
      title_id: $('#titleId').val(),
      first_name: $('#firstName').val(),
      middle_name: $('#middleName').val(),
      last_name: $('#lastName').val(),
      date_of_birth: $('#dob').val(),
      gender: $('#gender').val(),
      type: $('#type').val(),
      sub_parish_id: $('#subParishId').val(),
      community_id: $('#communityId').val(),
      occupation_id: $('#occupationId').val(),
      phone: $('#phone').val(),
      email: $('#email').val(),
      envelope_number: $('#envelopeNumber').val(), // Include envelope number
      head_parish_id: $('#headParishId').val()
    };

    $.ajax({
      type: 'POST',
      url: '../api/registration/register_church_member',
      data: memberData,
      dataType: 'json',
      success: function (response) {
        var messageHtml;
        if (response.success) {
          messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i>' + response.message + '</div>';
          setTimeout(function () {
            window.location.href = './church-members'; 
          }, 2000);
        } else {
          messageHtml = '<div class="response-message error"><i class="fas fa-times-circle icon"></i>' + response.message + '</div>';
        }
        $('#responseMessage').html(messageHtml);
      },
      error: function (xhr, status, error) {
        $('#responseMessage').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i>An error occurred: ' + error + '</div>');
      }
    });
  });
});
</script>

</body>

</html>
