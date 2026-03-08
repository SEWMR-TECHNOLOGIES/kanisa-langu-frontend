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
    render_header('Create Harambee Group - Kanisa Langu');
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
              <h5 class="card-title fw-semibold mb-4">Create Harambee Group</h5>

              <ul class="nav nav-tabs" id="harambeeGroupTabs" role="tablist">
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

              <div class="tab-content mt-3" id="harambeeTabContent">
                <!-- Head Parish Harambee Group Form -->
                <div class="tab-pane fade show active" id="head-parish" role="tabpanel">
                  <form id="headParishForm" autocomplete="off">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                              <label for="harambeeIdHP" class="form-label">Harambee</label>
                              <select class="form-select" id="harambeeIdHP" name="harambee_id">
                                <option value="">Select Harambee</option>
                                <!-- Options populated by AJAX -->
                              </select>
                            </div>
                        </div>
                        <div class="col-lg-6">
                          <div class="mb-3">
                            <label for="subParishIdHP" class="form-label">Sub Parish</label>
                            <select class="form-select" id="subParishIdHP" name="sub_parish_id">
                              <option value="">Select Sub Parish</option>
                            </select>
                          </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="groupNameHP" class="form-label">Group Name</label>
                                <input type="text" class="form-control" id="groupNameHP" name="group_name" placeholder="Harambee Group Name">
                            </div>
                        </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="groupTargetHP" class="form-label">Group Target</label>
                          <input type="number" class="form-control" id="groupTargetHP" name="group_target" placeholder="Group Target" step="0.01" min="0">
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-12">
                        <div class="mb-3">
                          <label for="groupDescriptionHP" class="form-label">Group Description</label>
                          <textarea class="form-control" id="groupDescriptionHP" name="group_description" placeholder="Harambee Group Description (Optional)" rows="2"></textarea>
                        </div>
                      </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Harambee Group</button>
                  </form>
                </div>

                <!-- Sub Parish Harambee Group Form -->
                <div class="tab-pane fade" id="sub-parish" role="tabpanel">
                  <form id="subParishForm" autocomplete="off">
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="subParishIdSP" class="form-label">Sub Parish</label>
                          <select class="form-select" id="subParishIdSP" name="sub_parish_id">
                            <option value="">Select Sub Parish</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="harambeeIdSP" class="form-label">Harambee</label>
                          <select class="form-select" id="harambeeIdSP" name="harambee_id">
                            <option value="">Select Harambee</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="groupNameSP" class="form-label">Group Name</label>
                          <input type="text" class="form-control" id="groupNameSP" name="group_name" placeholder="Harambee Group Name">
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="groupTargetSP" class="form-label">Group Target</label>
                          <input type="number" class="form-control" id="groupTargetSP" name="group_target" placeholder="Group Target" step="0.01" min="0">
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-12">
                        <div class="mb-3">
                          <label for="groupDescriptionSP" class="form-label">Group Description</label>
                          <textarea class="form-control" id="groupDescriptionSP" name="group_description" placeholder="Harambee Group Description (Optional)" rows="2"></textarea>
                        </div>
                      </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Harambee Group</button>
                  </form>
                </div>

                <!-- Community Harambee Group Form -->
                <div class="tab-pane fade" id="community" role="tabpanel">
                  <form id="communityForm" autocomplete="off">
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="subParishIdCom" class="form-label">Sub Parish</label>
                          <select class="form-select" id="subParishIdCom" name="sub_parish_id">
                            <option value="">Select Sub Parish</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="communityId" class="form-label">Community</label>
                          <select class="form-select" id="communityId" name="community_id">
                            <option value="">Select Community</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="harambeeIdCOM" class="form-label">Harambee</label>
                          <select class="form-select" id="harambeeIdCOM" name="harambee_id">
                            <option value="">Select Harambee</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="groupNameCOM" class="form-label">Group Name</label>
                          <input type="text" class="form-control" id="groupNameCOM" name="group_name" placeholder="Harambee Group Name">
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="groupTargetCOM" class="form-label">Group Target</label>
                          <input type="number" class="form-control" id="groupTargetCOM" name="group_target" placeholder="Group Target" step="0.01" min="0">
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="groupDescriptionCOM" class="form-label">Group Description</label>
                          <textarea class="form-control" id="groupDescriptionCOM" name="group_description" placeholder="Harambee Group Description (Optional)" rows="1"></textarea>
                        </div>
                      </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Harambee Group</button>
                  </form>
                </div>

                <!-- Group Harambee Group Form -->
                <div class="tab-pane fade" id="group" role="tabpanel">
                  <form id="groupForm" autocomplete="off">
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="groupId" class="form-label">Group</label>
                          <select class="form-select" id="groupId" name="group_id">
                            <option value="">Select Group</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="harambeeIdGP" class="form-label">Harambee</label>
                          <select class="form-select" id="harambeeIdGP" name="harambee_id">
                            <option value="">Select Harambee</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="groupNameGP" class="form-label">Group Name</label>
                          <input type="text" class="form-control" id="groupNameGP" name="group_name" placeholder="Harambee Group Name">
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="groupTargetGP" class="form-label">Group Target</label>
                          <input type="number" class="form-control" id="groupTargetGP" name="group_target" placeholder="Group Target" step="0.01" min="0">
                        </div>
                      </div>
                     </div>
                     <div class="row">
                      <div class="col-lg-12">
                        <div class="mb-3">
                          <label for="groupDescriptionGP" class="form-label">Group Description</label>
                          <textarea class="form-control" id="groupDescriptionGP" name="group_description" placeholder="Harambee Group Description (Optional)" rows="2"></textarea>
                        </div>
                      </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Harambee Group</button>
                  </form>
                </div>
              </div>
              <div id="responseMessage"></div>
            </div>
          </div>
        </div>
      </div>
  </div>
</body>



 
 <?php require_once('components/footer_files.php') ?>
  
<script>
  $('select').select2({
    width: '100%'
  });

  $(document).ready(function () {
    // Function to submit the form dynamically
    function submitForm(formId, target) {
      const formData = $(formId).serialize() + '&target=' + target;

      $.ajax({
        type: 'POST',
        url: '../api/records/create_harambee_group',
        data: formData,
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
    $('#headParishForm').on('submit', function (event) {
      event.preventDefault();
      submitForm('#headParishForm', 'head-parish');
    });

    $('#subParishForm').on('submit', function (event) {
      event.preventDefault();
      submitForm('#subParishForm', 'sub-parish');
    });

    $('#communityForm').on('submit', function (event) {
      event.preventDefault();
      submitForm('#communityForm', 'community');
    });

    $('#groupForm').on('submit', function (event) {
      event.preventDefault();
      submitForm('#groupForm', 'group');
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
          $('#subParishIdSP').html(options);
          $('#subParishIdHP').html(options); 
          $('#subParishIdCom').html(options); 
        },
        error: function (xhr, status, error) {
          console.log('Error loading sub-parishes:', error);
        }
      });
    }

  // Function to load harambee
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
        // Populate the relevant select element
        $('#' + targetId).html(options);
      },
      error: function (xhr, status, error) {
        console.log('Error loading harambee:', error);
      }
    });
  }

     // Load harambee for Head Parish
    loadHarambee('harambeeIdHP', `../api/data/head_parish_harambee?limit=all&target=head-parish`);
    
      // Load harambee for Sub Parish
      $('#subParishIdSP').change(function () {
        const sub_parish_id = $(this).val();
        if (sub_parish_id) {
          loadHarambee('harambeeIdSP', `../api/data/head_parish_harambee?limit=all&target=sub-parish&sub_parish_id=${sub_parish_id}`);
        }
      });
      
      // Load harambee for Community
      $('#communityId').change(function () {
        const community_id = $(this).val();
        if (community_id) {
          loadHarambee('harambeeIdCOM', `../api/data/head_parish_harambee?limit=all&target=community&community_id=${community_id}`);
        }
      });
      
      // Load harambee for Groups
      $('#groupId').change(function () {
        const group_id = $(this).val();
        if (group_id) {
          loadHarambee('harambeeIdGP', `../api/data/head_parish_harambee?limit=all&target=group&group_id=${group_id}}`);
        }
      });

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
        url: '../api/data/head_parish_groups?limit=all', 
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

    loadSubParishes();
    loadGroups();

    // Load revenue streams for sub-parish when a sub-parish is selected
    $('#subParishIdCom').on('change', function () {
      loadCommunities($(this).val()); 
    });
  });
</script>

</body>

</html>
