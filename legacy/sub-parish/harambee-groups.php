<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Harambee Groups - Kanisa Langu');
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
              <h5 class="card-title fw-semibold mb-4">Harambee Groups</h5>

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
                      <div class="col-lg-12">
                        <div class="mb-3">
                          <label for="harambeeIdHP" class="form-label">Harambee</label>
                          <select class="form-select" id="harambeeIdHP" name="harambee_id">
                            <option value="">Select Harambee</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                      <div class="col-lg-12">
                        <div class="mb-3">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                  <thead>
                                    <tr>
                                      <th>#</th>
                                      <th>Group Name</th>
                                      <th>Date Created</th>
                                      <th>Target Amount (TZS)</th>
                                      <th>Members</th>
                                      <th class="text-end">Actions</th>
                                    </tr>
                                  </thead>
                                  <tbody id="headParishGroupsTableBody">
                                    <!-- Groups will be populated here -->
                                  </tbody>
                                </table>
                            </div>
                        </div>
                      </div>
                    </div>
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
                      <div class="col-lg-12">
                        <div class="mb-3">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                  <thead>
                                    <tr>
                                      <th>#</th>
                                      <th>Group Name</th>
                                      <th>Date Created</th>
                                      <th>Target Amount (TZS)</th>
                                      <th>Members</th>
                                      <th class="text-end">Actions</th>
                                    </tr>
                                  </thead>
                                  <tbody id="subParishGroupsTableBody">
                                    <!-- Groups will be populated here -->
                                  </tbody>
                                </table>
                            </div>
                        </div>
                      </div>
                    </div>
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
                      <div class="col-lg-12">
                        <div class="mb-3">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                  <thead>
                                    <tr>
                                      <th>#</th>
                                      <th>Group Name</th>
                                      <th>Date Created</th>
                                      <th>Target Amount (TZS)</th>
                                      <th>Members</th>
                                      <th class="text-end">Actions</th>
                                    </tr>
                                  </thead>
                                  <tbody id="communityGroupsTableBody">
                                    <!-- Groups will be populated here -->
                                  </tbody>
                                </table>
                            </div>
                        </div>
                      </div>
                    </div>
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
                      <div class="col-lg-12">
                        <div class="mb-3">
                            <div class="table-responsive">
                            <table class="table table table-hover">
                              <thead>
                                <tr>
                                  <th>#</th>
                                  <th>Group Name</th>
                                  <th>Date Created</th>
                                  <th>Target Amount (TZS)</th>
                                  <th>Members</th>
                                  <th class="text-end">Actions</th>
                                </tr>
                              </thead>
                              <tbody id="groupGroupsTableBody">
                                <!-- Groups will be populated here -->
                              </tbody>
                            </table>
                        </div>
                        </div>
                      </div>
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

<!-- Modal HTML for displaying group details -->
<div class="modal fade" id="containerModal" tabindex="-1" aria-labelledby="containerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="containerModalLabel">Harambee Group Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="container-items">
                    <!-- Group details will be dynamically inserted here -->
                </div>
                
                <div id="responseMessageModal" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Confirmation Modal for Group Deletion -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this group?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Custom Confirmation Modal for Member Removal -->
<div class="modal fade" id="confirmRemoveMemberModal" tabindex="-1" aria-labelledby="confirmRemoveMemberModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmRemoveMemberModalLabel">Confirm Member Removal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to remove this member?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRemoveMemberBtn">Remove</button>
            </div>
        </div>
    </div>
</div>
 

<!-- Modal HTML for Contribution Notification -->
<div class="modal fade" id="contributionNotificationModal" tabindex="-1" aria-labelledby="contributionNotificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contributionNotificationModalLabel">Send Contribution Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="contributionNotificationForm">
                    <div class="mb-3">
                        <label for="startDate" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="startDate">
                    </div>
                    <div class="mb-3">
                        <label for="endDate" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="endDate" >
                    </div>
                    <div id="notificationResponseMessageModal" class="mt-3"></div>
                    <button type="submit" class="btn btn-primary">Send Notification</button>
                </form>
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
    // Attach form submit handlers
    $('#headParishForm').on('submit', function (event) {
      event.preventDefault();
    });

    $('#subParishForm').on('submit', function (event) {
      event.preventDefault();
    });

    $('#communityForm').on('submit', function (event) {
      event.preventDefault();
    });

    $('#groupForm').on('submit', function (event) {
      event.preventDefault();
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

    // Function to load harambee groups and populate the table
    function loadHarambeeGroups(targetId, target, harambeeId) {
      const url = '../api/data/harambee_groups.php?target=' + target + '&harambee_id=' + harambeeId;
    
      $.ajax({
        type: 'GET',
        url: url,
        dataType: 'json',
        success: function (response) {
          let rows = ''; // Initialize rows for the table
          if (response.success) {
            $.each(response.groups, function (index, harambee_group) {
             // Get the current client date and time (timestamp)
            const clientTimestamp = getLocalTimestamp();
            
            // Create the URL for downloading the group details with timestamp
            const downloadUrl = `../reports/harambee_group_statement.php?target=${target}&harambee_group_id=${harambee_group.harambee_group_id}&harambee_id=${harambeeId}&timestamp=${encodeURIComponent(clientTimestamp)}`;
            
    
              rows += `<tr>
                          <td>${index + 1}</td>
                          <td>${harambee_group.harambee_group_name}</td>
                          <td>${harambee_group.date_created}</td>
                          <td>${harambee_group.harambee_group_target}</td>
                          <td>${harambee_group.members_count}</td>
                          <td class="text-end">
                            <button class="btn btn-info btn-sm preview-btn" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#containerModal" 
                                    data-id="${harambee_group.harambee_group_id}" 
                                    data-harambee-id="${harambeeId}" 
                                    data-target="${target}">
                                More Details
                            </button>
                            <a href="${downloadUrl}" class="btn btn-success btn-sm" target="_blank">
                                Download Statement
                            </a>
                          </td>
                        </tr>`;
            });
          } else {
            console.log('Error:', response.message);
          }
          // Populate the relevant table body
          $('#' + targetId).html(rows);
        },
        error: function (xhr, status, error) {
          console.log('Error loading harambee:', error);
        }
      });
    }


    // Attach change event for Head Parish Harambee ID
      $('#harambeeIdHP').change(function () {
        const targetId = 'headParishGroupsTableBody'; // Target select ID for Head Parish
        const target = 'head-parish'; // Target value for Head Parish
        const harambeeId = $(this).val(); // Get selected Harambee ID
        if (harambeeId) { // Check if harambeeId is not empty
          loadHarambeeGroups(targetId, target, harambeeId);
        } else {
          $('#' + targetId).html('');
        }
      });
    
      // Attach change event for Sub Parish Harambee ID
      $('#harambeeIdSP').change(function () {
        const targetId = 'subParishGroupsTableBody'; // Target select ID for Sub Parish
        const target = 'sub-parish'; // Target value for Sub Parish
        const harambeeId = $(this).val(); // Get selected Harambee ID
        if (harambeeId) { // Check if harambeeId is not empty
          loadHarambeeGroups(targetId, target, harambeeId);
        } else {
          $('#' + targetId).html('');
        }
      });
    
      // Attach change event for Community Harambee ID
      $('#harambeeIdCOM').change(function () {
        const targetId = 'communityGroupsTableBody'; // Target select ID for Community
        const target = 'community'; // Target value for Community
        const harambeeId = $(this).val(); // Get selected Harambee ID
        if (harambeeId) { // Check if harambeeId is not empty
          loadHarambeeGroups(targetId, target, harambeeId);
        } else {
          $('#' + targetId).html('');
        }
      });
    
      // Attach change event for Group Harambee ID
      $('#harambeeIdGP').change(function () {
        const targetId = 'groupGroupsTableBody'; // Target select ID for Group
        const target = 'group'; // Target value for Group
        const harambeeId = $(this).val(); // Get selected Harambee ID
        if (harambeeId) { // Check if harambeeId is not empty
          loadHarambeeGroups(targetId, target, harambeeId);
        } else {
          $('#' + targetId).html('');
        }
      });


  
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

    // Event listener for the "More Details" button click
    $(document).on('click', '.preview-btn', function () {
        // Extract harambee_group_id, harambee_id, and target from button data attributes
        const groupId = $(this).data('id');
        const harambeeId = $(this).data('harambee-id');
        const target = $(this).data('target');
    
        // Construct the API URL for fetching group details
        const url = `../api/data/harambee_groups.php?harambee_group_id=${groupId}&harambee_id=${harambeeId}&target=${target}`;
        console.log("Fetching group details from:", url); // Log URL for debugging
    
        // Clear previous messages
        $('#responseMessage').html('');
    
        // Fetch group details via AJAX
        $.ajax({
            type: 'GET',
            url: url,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    const groupData = response.groups[0]; // Assuming we get the first group details
    
                    // Initialize HTML structure for the details using Bootstrap
                    let detailsHtml = `
                        <div class="container my-4">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="bg-light border rounded p-4 shadow-sm mb-4">
                                        <h4 class="text-primary">Group Overview</h4>
                                        <div class="mb-3"><strong>Group Name:</strong> <span>${groupData.harambee_group_name}</span></div>
                                        <div class="mb-3"><strong>Date Created:</strong> <span>${groupData.date_created}</span></div>
                                        <div class="mb-3"><strong>Target Amount:</strong> <span>TZS ${groupData.harambee_group_target}</span></div>
                                        <div class="mb-3"><strong>Description:</strong> <span>${groupData.description || 'N/A'}</span></div>
                                        <div class="mb-3"><strong>Total Members:</strong> <span>${groupData.members_count || 0}</span></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                        <div class="bg-light border rounded p-4 shadow-sm mb-4">
                                        <h4 class="text-primary">Members</h4>
                                        <div class="overflow-auto scroll-sidebar" data-simplebar="" style="max-height: 300px;">
                                        <ul class="list-group">`;
    
                    // Loop through the members and add them to the list with a check icon and remove icon
                    groupData.members.forEach(member => {
                        detailsHtml += `
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <strong>${member.full_name}</strong> - ${member.phone || 'N/A'}
                                </span>
                                <i class="fas fa-trash-alt text-danger remove-member" data-member-id="${member.member_id}" data-group-id="${groupId}" data-target="${target}" style="cursor: pointer;"></i>
                            </li>`;
                    });
    
                    detailsHtml += `
                                        </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 text-end">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button class="btn btn-primary" id="sendNotificationBtn" data-group-id="${groupId}" data-target="${target}" data-harambee-id="${harambeeId}">Send Contribution Notification</button>
                                    <button class="btn btn-danger" id="deleteGroupBtn" data-group-id="${groupId}" data-target="${target}">Delete Group</button>
                                </div>
                            </div>
                        </div>`;
    
                    // Populate the modal with the fetched details
                    $('#container-items').html(detailsHtml);
                } else {
                    $('#container-items').html(`<p class="text-danger">${response.message}</p>`);
                }
            },
            error: function () {
                $('#container-items').html('<p class="text-danger">Failed to load group details.</p>');
            }
        });
    });

    
    
    // Function to remove member via AJAX
    function removeMember(groupId, memberId, target) {
        const url = '../api/data/delete_harambee_group_member.php';
    
        // Create the data object to send in the body
        const data = {
            harambee_group_id: groupId,
            member_id: memberId,
            target: target
        };
    
        $.ajax({
            type: 'POST',
            url: url,
            contentType: 'application/json', // Set content type to JSON
            data: JSON.stringify(data), // Convert the data object to JSON
            success: function (response) {
                if (response.success) {
                    // Remove the member's list item from the UI
                    $(`.remove-member[data-member-id="${memberId}"]`).closest('li').remove();
                    $('#responseMessageModal').html('<p class="text-success">Member removed successfully</p>'); // Show success message
                } else {
                    $('#responseMessageModal').html(`<p class="text-danger">Failed to remove member: ${response.message}</p>`); // Show error message
                }
            },
            error: function () {
                $('#responseMessageModal').html('<p class="text-danger">Error removing member</p>'); // Show error message
            }
        });
    }

    // Event listener for the "Remove" icon click
    $(document).on('click', '.remove-member', function () {
        const memberId = $(this).data('member-id');
        const groupId = $(this).data('group-id');
        const target = $(this).data('target');
    
        // Show the custom confirmation modal for member removal
        $('#confirmRemoveMemberModal').modal('show');
    
        // Store the memberId, groupId, and target in data attributes for later use
        $('#confirmRemoveMemberBtn').data('member-id', memberId);
        $('#confirmRemoveMemberBtn').data('group-id', groupId);
        $('#confirmRemoveMemberBtn').data('target', target);
    });
    
    // Event listener for the confirmation button in the member removal modal
    $(document).on('click', '#confirmRemoveMemberBtn', function () {
        const memberId = $(this).data('member-id');
        const groupId = $(this).data('group-id');
        const target = $(this).data('target');
    
        // Call the remove function
        removeMember(groupId, memberId, target);
    
        // Close the modal after the removal action
        $('#confirmRemoveMemberModal').modal('hide');
    });
    
    // Event listener for the "Delete Group" button click
    $(document).on('click', '#deleteGroupBtn', function () {
        const groupId = $(this).data('group-id');
        const target = $(this).data('target');
    
        // Show the custom confirmation modal for group deletion
        $('#confirmDeleteModal').modal('show');
    
        // Store the groupId and target in data attributes for later use
        $('#confirmDeleteBtn').data('group-id', groupId);
        $('#confirmDeleteBtn').data('target', target);
    });
    
    // Event listener for the confirmation button in the custom modal
    $(document).on('click', '#confirmDeleteBtn', function () {
        const groupId = $(this).data('group-id');
        const target = $(this).data('target');
    
        // Call delete function
        deleteGroup(groupId, target);
    
        // Close the modal after the deletion action
        $('#confirmDeleteModal').modal('hide');
    });

    // Event listener for "Send Contribution Notification" button click
    $(document).on('click', '#sendNotificationBtn', function () {
        // Extract data attributes from the button
        const groupId = $(this).data('group-id');
        const harambeeId = $(this).data('harambee-id'); // Get the harambee_id
        const target = $(this).data('target');
        
        // Hide the harambee details modal
        $('#containerModal').modal('hide');
        
        // Open the contribution notification modal
        $('#contributionNotificationModal').modal('show');
        
        // Optionally, store these values in the modal for future reference or prefill the form (if needed)
        $('#contributionNotificationForm').data('group-id', groupId); // Store groupId in the form
        $('#contributionNotificationForm').data('harambee-id', harambeeId); // Store harambeeId in the form
        $('#contributionNotificationForm').data('target', target); // Store target in the form
    });
    
    // Event listener for form submission inside the notification modal
    $('#contributionNotificationForm').on('submit', function (event) {
        event.preventDefault(); // Prevent form submission from refreshing the page
    
        // Show a "Sending..." message before sending the request
        $('#notificationResponseMessageModal').html('<p class="text-info">Sending notification...</p>');
    
        // Retrieve the stored groupId, harambeeId, and target from the form data
        const groupId = $(this).data('group-id');
        const harambeeId = $(this).data('harambee-id'); // Retrieve harambeeId from the form
        const target = $(this).data('target');
        
        // Get the date range values from the form inputs
        const startDate = $('#startDate').val();
        const endDate = $('#endDate').val();
        
        // Prepare data to send in the API request
        const data = {
            harambee_group_id: groupId,
            harambee_id: harambeeId, // Include harambeeId in the data
            target: target,
            start_date: startDate,
            end_date: endDate
        };
        
        // API URL for sending the contribution notification
        const url = '../api/records/send_harambee_group_notification.php';
        
        // Send the data to the server via AJAX
        $.ajax({
            type: 'POST',
            url: url,
            contentType: 'application/json',
            data: JSON.stringify(data), // Convert the data object to JSON
            success: function (response) {
                if (response.success) {
                    // Show success message
                    $('#notificationResponseMessageModal').html('<p class="text-success">Notification sent successfully!</p>');
                    
                    // Set a timeout to close the modal after 3 seconds
                    setTimeout(function () {
                        $('#contributionNotificationModal').modal('hide');
                        $('#notificationResponseMessageModal').html('');
                    }, 3000);  // 3000ms = 3 seconds
                    
                } else {
                    // Show error message
                    $('#notificationResponseMessageModal').html(`<p class="text-danger">Failed to send notification: ${response.message}</p>`);
                }
            },
            error: function () {
                // Show error message on failure
                $('#notificationResponseMessageModal').html('<p class="text-danger">Error sending notification</p>');
            }
        });
    });


    // Function to delete group via AJAX
    function deleteGroup(groupId, target) {
        const url = '../api/data/delete_harambee_group.php';
    
        // Create the data object to send in the body
        const data = {
            harambee_group_id: groupId,
            target: target
        };
    
        $.ajax({
            type: 'POST',
            url: url,
            contentType: 'application/json', // Set content type to JSON
            data: JSON.stringify(data), // Convert the data object to JSON
            success: function (response) {
                if (response.success) {
                    $('#responseMessageModal').html('<p class="text-success">Group deleted successfully</p>'); // Show success message
                    // Optionally, you can redirect or refresh the page
                    location.reload(); // Refresh the page to update the UI
                } else {
                    $('#responseMessageModal').html(`<p class="text-danger">Failed to delete group: ${response.message}</p>`); // Show error message
                }
            },
            error: function () {
                $('#responseMessageModal').html('<p class="text-danger">Error deleting group</p>'); // Show error message
            }
        });
    }


</script>

</body>

</html>
