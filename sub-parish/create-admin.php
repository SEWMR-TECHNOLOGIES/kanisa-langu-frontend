<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');
?>
<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Create Admin - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Create Admins</h5>
            
            <ul class="nav nav-tabs" id="revenueTabs" role="tablist">
              <li class="nav-item" role="presentation">
                <a class="nav-link active" id="head-parish-tab" data-bs-toggle="tab" href="#head-parish" role="tab">Head Parish</a>
              </li>
              <li class="nav-item" role="presentation">
                <a class="nav-link" id="sub-parish-tab" data-bs-toggle="tab" href="#sub-parish" role="tab">Sub Parish</a>
              </li>
              <li class="nav-item" role="presentation">
                <a class="nav-link" id="community-tab" data-bs-toggle="tab" href="#community" role="tab">Community</a>
              </li>
              <li class="nav-item" role="presentation">
                <a class="nav-link" id="group-tab" data-bs-toggle="tab" href="#group" role="tab">Group</a>
              </li>
            </ul>

            <div class="tab-content mt-3" id="revenueTabContent">
              <!-- Head Parish Admin Form -->
              <div class="tab-pane fade show active" id="head-parish" role="tabpanel">
                <form id="headParishAdminForm" enctype="multipart/form-data">
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="adminFullNameHP" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="adminFullNameHP" name="admin_fullname" placeholder="Enter full name">
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="adminEmailHP" class="form-label">Email</label>
                        <input type="email" class="form-control" id="adminEmailHP" name="admin_email" placeholder="Enter email">
                      </div>
                    </div>
                  </div>
    
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="adminPhoneHP" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="adminPhoneHP" name="admin_phone" placeholder="Enter phone number">
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="adminRoleHP" class="form-label">Role</label>
                        <select class="form-select" id="adminRoleHP" name="admin_role">
                          <option value="">Select Role</option>
                          <option value="secretary">Secretary</option>
                          <option value="accountant">Accountant</option>
                          <option value="chairperson">Chairperson</option>
                        </select>
                      </div>
                    </div>
                  </div>
    
                  <div class="row">
                    <div class="col-lg-12">
                      <div class="mb-3">
                        <label for="signaturePathHP" class="form-label">Signature Upload</label>
                        <input type="file" class="form-control" id="signaturePathHP" name="signature_path" accept="image/*">
                      </div>
                    </div>
                  </div>
                  
                  <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Create Admin</button>
                  </div>
                </form>
              </div>

              <!-- Sub Parish Admin Form -->
              <div class="tab-pane fade" id="sub-parish" role="tabpanel">
                <form id="subParishAdminForm" enctype="multipart/form-data">
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="adminFullNameSP" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="adminFullNameSP" name="admin_fullname" placeholder="Enter full name">
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="adminEmailSP" class="form-label">Email</label>
                        <input type="email" class="form-control" id="adminEmailSP" name="admin_email" placeholder="Enter email">
                      </div>
                    </div>
                  </div>
    
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="adminPhoneSP" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="adminPhoneSP" name="admin_phone" placeholder="Enter phone number">
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="adminRoleSP" class="form-label">Role</label>
                        <select class="form-select" id="adminRoleSP" name="admin_role">
                          <option value="">Select Role</option>
                          <option value="secretary">Secretary</option>
                          <option value="accountant">Accountant</option>
                          <option value="chairperson">Chairperson</option>
                        </select>
                      </div>
                    </div>
                  </div>
    
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="subParishId" class="form-label">Sub Parish</label>
                        <select class="form-select" id="subParishId" name="sub_parish_id">
                          <option value="">Select Sub Parish</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="signaturePathSP" class="form-label">Signature Upload</label>
                        <input type="file" class="form-control" id="signaturePathSP" name="signature_path" accept="image/*">
                      </div>
                    </div>
                  </div>
                  
                  <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Create Admin</button>
                  </div>
                </form>
              </div>

              <!-- Community Admin Form -->
              <div class="tab-pane fade" id="community" role="tabpanel">
                <form id="communityAdminForm" enctype="multipart/form-data">
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="adminFullNameCom" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="adminFullNameCom" name="admin_fullname" placeholder="Enter full name">
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="adminEmailCom" class="form-label">Email</label>
                        <input type="email" class="form-control" id="adminEmailCom" name="admin_email" placeholder="Enter email">
                      </div>
                    </div>
                  </div>
            
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="adminPhoneCom" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="adminPhoneCom" name="admin_phone" placeholder="Enter phone number">
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="adminRoleCom" class="form-label">Role</label>
                        <select class="form-select" id="adminRoleCom" name="admin_role">
                          <option value="">Select Role</option>
                          <option value="secretary">Secretary</option>
                          <option value="accountant">Accountant</option>
                          <option value="chairperson">Chairperson</option>
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="subParishIdCom" class="form-label">Sub Parish</label>
                        <select class="form-select" id="subParishIdCom" name="sub_parish_id">
                          <option value="">Select Sub Parish</option>
                          <!-- Populate this dropdown with sub parishes -->
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="communityId" class="form-label">Community</label>
                        <select class="form-select" id="communityId" name="community_id">
                          <option value="">Select Community</option>
                          <!-- Populate this dropdown based on selected sub parish -->
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-12">
                      <div class="mb-3">
                        <label for="signaturePathCom" class="form-label">Signature Upload</label>
                        <input type="file" class="form-control" id="signaturePathCom" name="signature_path" accept="image/*">
                      </div>
                    </div>
                  </div>
                  
                  <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Create Admin</button>
                  </div>
                </form>
              </div>

              <!-- Group Admin Form -->
              <div class="tab-pane fade" id="group" role="tabpanel">
                <form id="groupAdminForm" enctype="multipart/form-data">
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="adminFullNameG" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="adminFullNameG" name="admin_fullname" placeholder="Enter full name">
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="adminEmailG" class="form-label">Email</label>
                        <input type="email" class="form-control" id="adminEmailG" name="admin_email" placeholder="Enter email">
                      </div>
                    </div>
                  </div>
            
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="adminPhoneG" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="adminPhoneG" name="admin_phone" placeholder="Enter phone number">
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="adminRoleG" class="form-label">Role</label>
                        <select class="form-select" id="adminRoleG" name="admin_role">
                          <option value="">Select Role</option>
                          <option value="secretary">Secretary</option>
                          <option value="accountant">Accountant</option>
                          <option value="chairperson">Chairperson</option>
                        </select>
                      </div>
                    </div>
                  </div>
            
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="groupId" class="form-label">Sub Parish</label>
                        <select class="form-select" id="groupId" name="group_id">
                          <option value="">Select Group</option>
                          <!-- Populate this dropdown with sub parishes -->
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="signaturePathG" class="form-label">Signature Upload</label>
                        <input type="file" class="form-control" id="signaturePathG" name="signature_path" accept="image/*">
                      </div>
                    </div>
                  </div>
                  
                  <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Create Admin</button>
                  </div>
                </form>
              </div>

            </div>
            <div id="responseMessage"></div>
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
      
    // Function to submit the form dynamically
    function submitForm(formId, target) {
      const formData = new FormData($(formId)[0]); // Create FormData object from the form
      formData.append('target', target); // Append the target to FormData
    
      $.ajax({
        type: 'POST',
        url: '../api/registration/create_admin',
        data: formData,
        processData: false, // Important: Prevent jQuery from automatically transforming the data into a query string
        contentType: false,  // Important: Tell jQuery not to set any content type
        dataType: 'json',
        success: function (response) {
          let messageHtml = '';
          if (response.success) {
            messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i>' + response.message + '</div>';
            setTimeout(function () {
              location.reload(); 
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
    }

    // Attach form submit handlers
    $('#headParishAdminForm').on('submit', function (event) {
      event.preventDefault();
      submitForm('#headParishAdminForm', 'head-parish');
    });

    $('#subParishAdminForm').on('submit', function (event) {
      event.preventDefault();
      submitForm('#subParishAdminForm', 'sub-parish');
    });

    $('#communityAdminForm').on('submit', function (event) {
      event.preventDefault();
      submitForm('#communityAdminForm', 'community');
    });

    $('#groupAdminForm').on('submit', function (event) {
      event.preventDefault();
      submitForm('#groupAdminForm', 'group');
    });

    // Load sub-parishes
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
          $('#subParishId').html(options);
          $('#subParishIdCom').html(options); 
        },
        error: function (xhr, status, error) {
          console.log('Error loading sub-parishes:', error);
        }
      });
    }

    // Load communities based on selected sub-parish
    function loadCommunities(subParishId) {
      $.ajax({
        type: 'GET',
        url: '../api/data/head_parish_communities?limit=all', 
        data: { sub_parish_id: subParishId },
        dataType: 'json',
        success: function (response) {
          let options = '<option value="">Select Community</option>';
          $.each(response.data, function (index, community) {
            options += '<option value="' + community.community_id + '">' + community.community_name + '</option>';
          });
          $('#communityId').html(options);
        },
        error: function (xhr, status, error) {
          console.log('Error loading communities:', error);
        }
      });
    }

    // Load groups
    function loadGroups() {
      $.ajax({
        type: 'GET',
        url: '../api/data/head_parish_groups?limit=all', // Adjust with your API URL for groups
        dataType: 'json',
        success: function (response) {
          let options = '<option value="">Select Group</option>';
          $.each(response.data, function (index, group) {
            options += '<option value="' + group.group_id + '">' + group.group_name + '</option>';
          });
          $('#groupId').html(options);
        },
        error: function (xhr, status, error) {
          console.log('Error loading groups:', error);
        }
      });
    }

    loadSubParishes(); // Load sub-parishes on page load
    loadGroups();


    // Load revenue streams for sub-parish when a sub-parish is selected
    $('#subParishIdCom').on('change', function () {
      loadCommunities($(this).val()); 
    });
    
  });
</script>

</body>

</html>
