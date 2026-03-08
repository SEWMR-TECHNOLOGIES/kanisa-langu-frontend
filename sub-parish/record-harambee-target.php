<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');
?>
<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Record Harambee Targets - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Record Harambee Targets</h5>
            
            <ul class="nav nav-tabs" id="targetTabs" role="tablist">
              <li class="nav-item" role="presentation">
                <a class="nav-link active" id="individual-tab" data-bs-toggle="tab" href="#individual" role="tab">Individual Target</a>
              </li>
              <li class="nav-item" role="presentation">
                <a class="nav-link" id="group-tab" data-bs-toggle="tab" href="#group" role="tab">Group Target</a>
              </li>
            </ul>

            <div class="tab-content mt-3" id="targetTabContent">
              <!-- Individual Target Form -->
              <div class="tab-pane fade show active" id="individual" role="tabpanel">
                <form id="individualForm" autocomplete="off">
                  <input type="hidden" id="targetType" name="target_type" value="individual">
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="memberId" class="form-label">Select Member</label>
                        <select class="form-select" id="memberId" name="member_id">
                          <option value="">Select Member</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="subParishId" class="form-label">Sub Parish (Optional)</label>
                        <select class="form-select" id="subParishId" name="sub_parish_id">
                          <option value="">Select Sub Parish</option>
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="communityId" class="form-label">Community (Optional)</label>
                        <select class="form-select" id="communityId" name="community_id">
                          <option value="">Select Community</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="targetAmount" class="form-label">Target Amount</label>
                        <input type="number" class="form-control" id="targetAmount" name="target" placeholder="Target Amount" min="0">
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="targetTypeSelectionIndividual" class="form-label">Select Type</label>
                        <select class="form-select" id="targetTypeSelectionIndividual" name="target_table">
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
                          <label for="harambeeResponsibility" class="form-label">Harambee Committee Responsibility (Optional)</label>
                          <?php render_harambee_responsibility_dropdown('harambeeResponsibility', 'member_harambee_responsibility'); ?>
                        </div>                        
                      </div>
                  </div>
                  <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Record Individual Target</button>
                  </div>
                </form>
              </div>

              <!-- Group Target Form -->
              <div class="tab-pane fade" id="group" role="tabpanel">
                <form id="groupForm" autocomplete="off">
                  <input type="hidden" id="targetType" name="target_type" value="group">
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="firstMemberId" class="form-label">First Member</label>
                        <select class="form-select" id="firstMemberId" name="first_member_id">
                          <option value="">Select First Member</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="secondMemberId" class="form-label">Second Member</label>
                        <select class="form-select" id="secondMemberId" name="second_member_id">
                          <option value="">Select Second Member</option>
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="groupName" class="form-label">Group Name</label>
                        <input type="text" class="form-control" id="groupName" name="group_name" placeholder="Group Name">
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="groupTargetAmount" class="form-label">Target Amount</label>
                        <input type="number" class="form-control" id="groupTargetAmount" name="target" placeholder="Target Amount" min="0">
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="firstHarambeeResponsibility" class="form-label">First Member Committee Responsibility (Optional)</label>
                        <?php render_harambee_responsibility_dropdown('firstHarambeeResponsibility', 'first_member_harambee_responsibility'); ?>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="secondHarambeeResponsibility" class="form-label">Second Member Committee Responsibility (Optional)</label>
                        <?php render_harambee_responsibility_dropdown('secondHarambeeResponsibility', 'second_member_harambee_responsibility'); ?>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="targetTypeSelectionGroup" class="form-label">Select Type</label>
                        <select class="form-select" id="targetTypeSelectionGroup" name="target_table">
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
                          <label for="harambeeIdGroup" class="form-label">Harambee</label>
                          <select class="form-select" id="harambeeIdGroup" name="harambee_id">
                            <option value="">Select Harambee</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                  </div>
                  <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Record Group Target</button>
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
      
    // Load members dynamically
    function loadMembers() {
      $.ajax({
        type: 'GET',
        url: '../api/data/church_members?limit=all', 
        dataType: 'json',
        success: function (response) {
          let options = '<option value="">Select Member</option>';
          $.each(response.data, function (index, member) {
            // Construct full name using template literals
            const fullName = `${member.title ? member.title + '. ' : ''}${member.first_name}${member.middle_name ? ' ' + member.middle_name : ''} ${member.last_name}`;
            
            // Construct phone and envelope number string
            const phone = member.phone ? ` - ${member.phone}` : '';
            const envelopeNumber = member.envelope_number ? ` - ${member.envelope_number}` : '';
    
            // Combine name, phone, and envelope number in the option text
            options += `<option value="${member.id}">${fullName}${phone}${envelopeNumber}</option>`;
          });
          $('#memberId, #firstMemberId, #secondMemberId').html(options);
        },
        error: function (xhr, status, error) {
          console.log('Error loading members:', error);
        }
      });
    }


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

$('#targetTypeSelectionIndividual').change(function () {
  const selectedType = $(this).val();
  loadHarambee('harambeeIdIndividual', `../api/data/head_parish_harambee?limit=all&target=${selectedType}`);
});

$('#targetTypeSelectionGroup').change(function () {
  const selectedType = $(this).val();
  loadHarambee('harambeeIdGroup', `../api/data/head_parish_harambee?limit=all&target=${selectedType}`);
});

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
    loadMembers();
    loadSubParishes();

    // Event listener for sub-parish change to load communities
    $('#subParishId').change(function () {
      const subParishId = $(this).val();
      if (subParishId) {
        loadCommunities(subParishId);
      } else {
        $('#communityId').html('<option value="">Select Community</option>');
      }
    });

    // Form submission logic
    function submitForm(formId) {
      const formData = $(formId).serialize();

      $.ajax({
        type: 'POST',
        url: '../api/records/record_harambee_target', // Update this to your actual API endpoint
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
          const errorMessage = xhr.responseJSON?.message || 'An error occurred.';
          $('#responseMessage').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i>' + errorMessage + '</div>');
        }
      });
    }

    $('#individualForm').submit(function (e) {
      e.preventDefault();
      submitForm('#individualForm');
    });

    $('#groupForm').submit(function (e) {
      e.preventDefault();
      submitForm('#groupForm');
    });
  });
</script>

</body>
</html>
