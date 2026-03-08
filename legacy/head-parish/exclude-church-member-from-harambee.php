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
    render_header('Exclude Church Member from Harambee - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Exclude Church Member from Harambee</h5>

            <!-- Exclusion Form -->
            <form id="harambeeExclusionForm" autocomplete="off">
              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="targetTypeSelection" class="form-label">Select Type</label>
                    <select class="form-select" id="targetTypeSelection" name="target" required>
                      <option value="">Select Type</option>
                      <option value="head-parish">Head Parish</option>
                      <option value="sub-parish">Sub Parish</option>
                      <option value="community">Community</option>
                      <option value="group">Group</option>
                    </select>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="harambeeId" class="form-label">Harambee</label>
                    <select class="form-select" id="harambeeId" name="harambee_id" required>
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
                    <select class="form-select" id="memberId" name="member_id" required>
                      <option value="">Select Member</option>
                      <!-- Options populated by AJAX -->
                    </select>
                    <div class="form-text mt-1">
                      Not registered? 
                      <a href="/head-parish/register-church-member?returnUrl=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">Click here to register</a>
                    </div>
                  </div>
                </div>

                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="exclusionReason" class="form-label">Exclusion Reason</label>
                    <select class="form-select" id="exclusionReason" name="exclusion_reason" required>
                      <option value="">Select Reason</option>
                      <!-- Populated via AJAX -->
                    </select>
                  </div>
                </div>
              </div>

              <div id="memberInfo"></div>

              <div class="mb-3 d-flex flex-column flex-md-row">
                <button type="submit" class="btn btn-danger mb-2 mb-md-0 w-100 w-md-auto">Exclude Member</button>
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
  $('select').select2({ width: '100%' });

  $(document).ready(function () {

    // Load members
    function loadMembers() {
      $.ajax({
        type: 'GET',
        url: '../api/data/church_members?limit=all',
        dataType: 'json',
        success: function (response) {
          let options = '<option value="">Select Member</option>';
          $.each(response.data, function (index, member) {
            const fullName = `${member.title ? member.title + '. ' : ''}${member.first_name}${member.middle_name ? ' ' + member.middle_name : ''} ${member.last_name}`;
            const phone = member.phone ? ` - ${member.phone}` : '';
            const envelopeNumber = member.envelope_number ? ` - ${member.envelope_number}` : '';
            const memberType = member.type ? ` - ${member.type}` : '';
            options += `<option value="${member.id}">${fullName}${phone}${envelopeNumber}${memberType}</option>`;
          });
          $('#memberId').html(options).trigger('change');
        },
        error: function () {
          console.error('Error loading members');
        }
      });
    }

    // Load harambee list based on target type
    $('#targetTypeSelection').change(function () {
      const selectedType = $(this).val();
      if (!selectedType) {
        $('#harambeeId').html('<option value="">Select Harambee</option>').trigger('change');
        return;
      }
      $.ajax({
        type: 'GET',
        url: `../api/data/head_parish_harambee?limit=all&target=${encodeURIComponent(selectedType)}`,
        dataType: 'json',
        success: function (response) {
          let options = '<option value="">Select Harambee</option>';
          $.each(response.data, function (index, harambee) {
            options += `<option value="${harambee.harambee_id}">${harambee.description} - ${harambee.from_date} to ${harambee.to_date} - TZS ${harambee.amount}</option>`;
          });
          $('#harambeeId').html(options).trigger('change');
        },
        error: function () {
          console.error('Error loading harambee list');
        }
      });
    });

    // Load exclusion reasons
    function loadExclusionReasons() {
      $.ajax({
        type: 'GET',
        url: '../api/data/harambee_exclusion_reasons', 
        dataType: 'json',
        success: function (response) {
          let options = '<option value="">Select Reason</option>';
          $.each(response.data, function (index, reason) {
            options += `<option value="${reason.exclusion_reason_id}">${reason.reason}</option>`;
          });
          $('#exclusionReason').html(options).trigger('change');
        },
        error: function () {
          console.error('Error loading exclusion reasons');
        }
      });
    }

    loadMembers();
    loadExclusionReasons();

    // Display member info (optional, if needed)
    $('#memberId, #harambeeId, #targetTypeSelection').change(function () {
      const harambee_id = $('#harambeeId').val();
      const member_id = $('#memberId').val();
      const target = $('#targetTypeSelection').val();

      if (harambee_id && member_id && target) {
        $('#memberInfo').html('<div class="response-message"><i class="fas fa-spinner fa-spin icon"></i> Loading member info...</div>');
        $.ajax({
          type: 'GET',
          url: `../api/data/get_member_harambee_status?harambee_id=${encodeURIComponent(harambee_id)}&member_id=${encodeURIComponent(member_id)}&target=${encodeURIComponent(target)}`,
          dataType: 'json',
          success: function (response) {
            if (response.success) {
              $('#memberInfo').html(response.html);
            } else {
              $('#memberInfo').html('<div class="alert alert-danger">Unable to retrieve member information.</div>');
            }
          },
          error: function () {
            $('#memberInfo').html('<div class="alert alert-danger">Error fetching member information.</div>');
          }
        });
      } else {
        $('#memberInfo').empty();
      }
    });

    // Submit exclusion form
    $('#harambeeExclusionForm').submit(function (e) {
      e.preventDefault();

      const submitBtn = $(this).find('button[type="submit"]');
      submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');

      $('#responseMessage').html('<div class="response-message"><i class="fas fa-spinner fa-spin icon"></i> Sending Request...</div>');

      const formData = $(this).serialize();

      $.ajax({
        type: 'POST',
        url: '../api/records/exclude_church_member_from_harambee', 
        data: formData,
        dataType: 'json',
        success: function (response) {
        let msgHtml = '';
        if (response.success) {
            msgHtml = `<div class="response-message success"><i class="fas fa-check-circle icon"></i> ${response.message}</div>`;
            $('#harambeeExclusionForm')[0].reset();
            $('#targetTypeSelection, #harambeeId, #memberId, #exclusionReason').val('').trigger('change');
            $('#memberInfo').empty();
        } else {
            msgHtml = `<div class="response-message error"><i class="fas fa-times-circle icon"></i> ${response.message}</div>`;
        }
        $('#responseMessage').html(msgHtml);

        // Clear message after 3 seconds
        setTimeout(() => {
            $('#responseMessage').fadeOut(300, function() { $(this).html('').show(); });
        }, 3000);
        },
        error: function (xhr) {
        const errMsg = xhr.responseJSON?.message || 'An error occurred. Please try again.';
        $('#responseMessage').html(`<div class="response-message error"><i class="fas fa-times-circle icon"></i> ${errMsg}</div>`);

        // Clear message after 3 seconds
        setTimeout(() => {
            $('#responseMessage').fadeOut(300, function() { $(this).html('').show(); });
        }, 3000);
        },

        complete: function () {
          submitBtn.prop('disabled', false).html('Exclude Member');
        }
      });
    });
  });
</script>
</body>

</html>
