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
    render_header('Send Harambee Contribution SMS - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Send Harambee Contribution SMS</h5>

            <!-- Harambee Contribution Form -->
            <form id="harambeeContributionForm" autocomplete="off">
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
                    <label for="memberId" class="form-label">Select Member</label>
                    <select class="form-select" id="memberId" name="member_id">
                      <option value="">Select Member</option>
                    </select>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="contributionDate" class="form-label">Contribution Date</label>
                    <input type="date" class="form-control" id="contributionDate" name="contribution_date" 
                           max="<?php echo date('Y-m-d'); ?>" 
                           value="<?php echo date('Y-m-d'); ?>">
                  </div>
                </div>
              </div>
              <div class="row">
              <div id="memberInfo"></div>
            <div id="responseMessage" class="mt-3 mb-2"></div>
            <div class="mb-3 d-flex flex-column flex-md-row">
              <button type="submit" class="btn btn-primary mb-2 mb-md-0 w-100 w-md-auto">Send Contribution SMS</button>
            </div>
            </form>
          </div>
        </div>
      </div>
        <?php require_once('components/sms-statistics.php'); ?>
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
            const phone = member.phone ? ` - ${member.phone}` : '';
            const envelopeNumber = member.envelope_number ? ` - ${member.envelope_number}` : '';
            options += `<option value="${member.id}">${fullName}${phone}${envelopeNumber}</option>`;
          });
          $('#memberId').html(options);
        },
        error: function (xhr, status, error) {
          console.log('Error loading members:', error);
        }
      });
    }

    $('#targetTypeSelectionIndividual').change(function () {
      const selectedType = $(this).val();
      loadHarambee('harambeeIdIndividual', `../api/data/head_parish_harambee?limit=all&target=${selectedType}`);
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
          $('#' + targetId).html(options);
        },
        error: function (xhr, status, error) {
          console.log('Error loading harambee:', error);
        }
      });
    }
    
    loadMembers();

    // Load member's target and contributions when a member is selected
    let debounceTimer;
    $('#memberId, #harambeeIdIndividual, #targetTypeSelectionIndividual').change(function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const harambee_id = $('#harambeeIdIndividual').val();
            const member_id = $('#memberId').val();
            const target = $('#targetTypeSelectionIndividual').val();
    
            if (harambee_id && member_id && target) {
                 $('#memberInfo').html('<div class="response-message"><i class="fas fa-spinner fa-spin icon"></i> Sending Request...</div>');
                $.ajax({
                    type: 'GET',
                    url: `../api/data/get_member_harambee_status?harambee_id=${harambee_id}&member_id=${member_id}&target=${target}`,
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            $('#memberInfo').html(response.html);
                        } else {
                            $('#memberInfo').html('<div class="alert alert-danger">Unable to retrieve member information.</div>');
                        }
                    },
                    error: function (xhr, status, error) {
                        console.log('Error loading member information:', error);
                        $('#memberInfo').html('<div class="alert alert-danger">An error occurred while fetching member information.</div>');
                    }
                });
            } else {
                $('#memberInfo').empty();
            }
        }, 300); // Adjust delay as needed
    });



    // Submit harambee contribution form
    $('#harambeeContributionForm').submit(function (event) {
      event.preventDefault();
      // Optionally show a loading indicator
      $('#responseMessage').html('<div class="response-message"><i class="fas fa-spinner fa-spin icon"></i> Sending SMS...</div>');
      let formData = $(this).serialize();

      let localTime = getLocalTimestamp();
      formData += `&local_timestamp=${encodeURIComponent(localTime)}`;

      // Form submission logic
      $.ajax({
        type: 'POST',
        url: '../api/records/send_harambee_contribution_sms',
        data: formData,
        dataType: 'json',
        success: function (response) {
          let messageHtml = '';
          if (response.success) {
            messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i>' + response.message + '</div>';
            setTimeout(function () {
              $('#amount').val('').trigger('input');
              $('#memberId').val('').trigger('change');
              $('#responseMessage').html('');
              loadMembers(); 
            }, 2000);
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
