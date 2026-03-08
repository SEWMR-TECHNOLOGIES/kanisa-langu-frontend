<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Manage Church Members - Kanisa Langu');
  ?>
  <style>
    #loading {
      display: none; /* Hidden by default */
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      z-index: 9999;
    }
    td.text-end {
      white-space: nowrap; /* Keeps the buttons on one line */
    }
  </style>
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
    <?php require_once('components/sidebar.php') ?>
    <div class="body-wrapper">
      <?php require_once('components/header.php') ?>
      <div class="container-fluid">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Manage Church Members</h5>

            <!-- Search Form -->
            <div class="row mb-4">
              <div class="col-md-8">
                <input type="text" id="searchMember" class="form-control" placeholder="Search Members by Name or Email">
              </div>
              <div class="col-md-4 text-end">
                <button class="btn btn-primary" id="searchBtn">Search</button>
              </div>
            </div>

            <!-- Loading GIF -->
            <div id="loading">
              <img src="../assets/images/gifs/loading.gif" alt="Loading..." height="100">
            </div>

            <!-- Church Members Table -->
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Full Name</th>
                    <th>D.O.B</th>
                    <th>Phone Number</th>
                    <th>Occupation</th>
                    <th>Sub Parish</th>
                    <th>Community</th>
                    <th>Type</th>
                    <th>Env. No</th>
                    <th class="text-end">Actions</th>
                  </tr>
                </thead>
                <tbody id="memberList">
                  <!-- Table rows will be populated here using AJAX -->
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <nav id="paginationNav" aria-label="Member pagination">
                <div class="overflow-auto scroll-sidebar" data-simplebar="">
                    <ul class="pagination justify-content-start">
                        <!-- Pagination buttons will be generated dynamically -->
                    </ul>
                </div>
            </nav>

          </div>
        </div>
      </div>
    </div>
  </div>
  
  
<!-- Edit Member Modal -->
<div class="modal fade" id="editMemberModal" tabindex="-1" aria-labelledby="editMemberModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editMemberModalLabel">Edit Church Member</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Message Display -->
                <div id="messageContainer" class="mb-3" style="display: none;">
                    <div id="message" class="alert" role="alert"></div>
                </div>

                <form id="editMemberForm">
                    <input type="hidden" id="memberId" name="memberId" />

                    <div class="row">
                        <!-- Column 1 -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="firstName" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="firstName" name="first_name" required />
                            </div>
                            <div class="mb-3">
                                <label for="middleName" class="form-label">Middle Name</label>
                                <input type="text" class="form-control" id="middleName" name="middle_name" />
                            </div>
                            <div class="mb-3">
                                <label for="lastName" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="lastName" name="last_name" required />
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone" required />
                            </div>
                        </div>

                        <!-- Column 2 -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="type" class="form-label">Type</label>
                                <input type="text" class="form-control bg-light text-muted border" id="type" name="type" readonly />
                            </div>
                            <div class="mb-3">
                                <label for="envelopeNumber" class="form-label">Envelope No</label>
                                <input type="text" class="form-control bg-light text-muted border" id="envelopeNumber" name="envelopeNumber" readonly />
                            </div>
                            <div class="mb-3">
                                <label for="subParish" class="form-label">Sub Parish</label>
                                <input type="text" class="form-control bg-light text-muted border" id="subParish" name="subParish" readonly />
                            </div>
                            <div class="mb-3">
                                <label for="community" class="form-label">Community</label>
                                <input type="text" class="form-control bg-light text-muted border" id="community" name="community" readonly />
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>



  <?php require_once('components/footer_files.php') ?>
<script>
$(document).ready(function () {
    const fetchMembers = (page = 1, query = '') => {
        $('#loading').show(); // Show loading GIF
        $.ajax({
            type: 'GET',
            url: `../api/data/church_members?page=${page}&query=${query}`, 
            dataType: 'json',
            success: function (response) {
                const members = response.data;
                const memberList = $('#memberList');
                memberList.empty();
                let rowIndex = (page - 1) * 10 + 1;
                members.forEach(member => {
                    // Construct full name
                    const fullName = (member.title ? member.title + '. ' : '') + 
                                     member.first_name + 
                                     (member.middle_name ? ' ' + member.middle_name : '') + 
                                     ' ' + member.last_name;

                    memberList.append(`
                        <tr data-member-id="${member.id}"> <!-- Add the member ID here -->
                            <td>${rowIndex++}</td>
                            <td>${fullName.trim()}</td>
                            <td>${member.date_of_birth}</td>
                            <td>${member.phone || ''}</td>
                            <td>${member.occupation_name || ''}</td>
                            <td>${member.sub_parish_name || ''}</td>
                            <td>${member.community_name || ''}</td>
                            <td>${member.type}</td>
                            <td>${member.envelope_number || ''}</td>
                            <td class="text-end">
                                <button class="btn btn-secondary btn-sm edit-btn"><i class="fas fa-edit"></i> Edit</button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i> Delete</button>
                            </td>
                        </tr>
                    `);
                });

                // Handle pagination
                $('#paginationNav .pagination').empty();
                for (let i = 1; i <= response.total_pages; i++) {
                    $('#paginationNav .pagination').append(`
                        <li class="page-item ${i == page ? 'active' : ''}">
                            <a class="page-link" href="#">${i}</a>
                        </li>
                    `);
                }

                $('#loading').hide(); // Hide loading GIF
            },
            error: function (xhr, status, error) {
                console.log('Error fetching members:', error);
                $('#loading').hide(); // Hide loading GIF even on error
            }
        });
    };

    // Load members initially
    fetchMembers();

    // Search functionality
    $('#searchBtn').on('click', function () {
        const query = $('#searchMember').val();
        fetchMembers(1, query);
    });

    // Attach onchange event to search input
    $('#searchMember').on('input', function () {
        const query = $(this).val();
        fetchMembers(1, query);
    });

    // Pagination click event
    $(document).on('click', '#paginationNav .page-link', function (e) {
        e.preventDefault();
        const page = $(this).text();
        fetchMembers(page);
    });
    
    // When Edit button is clicked (without using href)
    $(document).on('click', '.edit-btn', function () {
        const memberId = $(this).closest('tr').data('member-id'); // Get the member ID from data attribute
        $('#memberId').val(memberId); // Set the member ID in the hidden field
    
        // Fetch member data via AJAX from the new API
        $.ajax({
            type: 'GET',
            url: `../api/data/church_member.php?id=${memberId}`, // Updated API endpoint
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    const member = response.data;
    
                    // Check and modify phone number (replace '255' with '0')
                    let phone = member.phone;
                    if (phone && phone.startsWith('255')) {
                        phone = '0' + phone.substring(3); // Remove '255' and add '0'
                    }
    
                    // Populate the form fields with the fetched data
                    $('#firstName').val(member.first_name);
                    $('#middleName').val(member.middle_name);
                    $('#lastName').val(member.last_name);
                    $('#phone').val(phone); // Use modified phone number
                    $('#subParish').val(member.sub_parish_name);
                    $('#community').val(member.community_name ? 'JUMUIYA YA ' + member.community_name : '');
                    $('#type').val(member.type);
                    $('#envelopeNumber').val(member.envelope_number);
    
                    // Enable editing of certain fields
                    $('#firstName, #middleName, #lastName, #phone').prop('readonly', false);
                    $('#subParish, #community, #type, #envelopeNumber').prop('readonly', true);
    
                    // Open the modal programmatically without using href
                    $('#editMemberModal').modal('show');
                } else {
                    alert(response.message || "Error fetching member details.");
                }
            },
            error: function (xhr, status, error) {
                console.log('Error fetching member data:', error);
            }
        });
    });


    // Handle form submission
    $('#editMemberForm').on('submit', function (event) {
        event.preventDefault();
    
        // Get form data
        const formData = $(this).serialize();
    
        // Send updated data via AJAX
        $.ajax({
            type: 'POST',
            url: '../api/registration/update_church_member.php', // API endpoint for updating the member
            data: formData,
            success: function (response) {
                if (response.success) {
                    // Show success message
                    const messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i>' + response.message + '</div>';
                    $('#message').html(messageHtml);
                    $('#messageContainer').show(); // Display the message container
                
                
                    // After 2 seconds, hide the message and reload the members
                    setTimeout(function () {
                        $('#messageContainer').hide(); // Hide the message container
                        // Close the modal and refresh the member list
                        $('#editMemberModal').modal('hide');
                        fetchMembers(); // Reload the member list
                    }, 3000); // 2 seconds
                } else {
                    // Show error message
                    const messageHtml = '<div class="response-message error"><i class="fas fa-times-circle icon"></i>' + response.message + '</div>';
                    $('#message').html(messageHtml);
                    $('#messageContainer').show(); // Display the message container
                }
            },
            error: function (xhr, status, error) {
                console.log('Error updating member:', error);
    
                // Show error message in case of AJAX failure
                const errorMessage = 'There was an error updating the member. Please try again.';
                const messageHtml = '<div class="response-message error"><i class="fas fa-times-circle icon"></i>' + errorMessage + '</div>';
                $('#message').html(messageHtml);
                $('#messageContainer').show(); // Display the message container
            }
        });
    });

});
</script>
</body>

</html>
