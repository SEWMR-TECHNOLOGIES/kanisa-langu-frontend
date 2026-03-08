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
    render_header('Exclude Church Member - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Exclude Church Member</h5>

            <!-- Alert/info about exclusion -->
            <div class="alert alert-info">
              <strong>Note:</strong> Excluding a member means they will not be able to participate in church operations such as harambee, envelopes, and other daily church activities.
            </div>

            <!-- Exclusion Form -->
            <form id="memberExclusionForm" autocomplete="off">
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

    // Load members with full details
    function loadMembers() {
      $.ajax({
        type: 'GET',
        url: '../api/data/church_members?limit=all',
        dataType: 'json',
        success: function (response) {
          let options = '<option value="">Select Member</option>';
          $.each(response.data, function (index, member) {
            const fullName = `${member.title ? member.title + '. ' : ''}${member.first_name}${member.middle_name ? ' ' + member.middle_name : ''} ${member.last_name}`;
            const phone = member.phone ? ` | Phone: ${member.phone}` : '';
            const envelope = member.envelope_number ? ` | Envelope: ${member.envelope_number}` : '';
            const type = member.type ? ` | Type: ${member.type}` : '';
            options += `<option value="${member.id}">${fullName}${phone}${envelope}${type}</option>`;
          });
          $('#memberId').html(options).trigger('change');
        },
        error: function () {
          console.error('Error loading members');
        }
      });
    }

    // Load exclusion reasons
    function loadExclusionReasons() {
      $.ajax({
        type: 'GET',
        url: '../api/data/church_member_exclusion_reasons', 
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

    // Submit exclusion form
    $('#memberExclusionForm').submit(function (e) {
      e.preventDefault();
      const submitBtn = $(this).find('button[type="submit"]');
      submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');

      $('#responseMessage').html('<div class="response-message"><i class="fas fa-spinner fa-spin icon"></i> Sending Request...</div>');

      const formData = $(this).serialize();

      $.ajax({
        type: 'POST',
        url: '../api/records/exclude_church_member.php', 
        data: formData,
        dataType: 'json',
        success: function (response) {
          let msgHtml = '';
          if (response.success) {
              msgHtml = `<div class="response-message success"><i class="fas fa-check-circle icon"></i> ${response.message}</div>`;
              $('#memberExclusionForm')[0].reset();
              $('#memberId, #exclusionReason').val('').trigger('change');
          } else {
              msgHtml = `<div class="response-message error"><i class="fas fa-times-circle icon"></i> ${response.message}</div>`;
          }
          $('#responseMessage').html(msgHtml);
          setTimeout(() => { $('#responseMessage').fadeOut(300, function() { $(this).html('').show(); }); }, 3000);
        },
        error: function (xhr) {
          const errMsg = xhr.responseJSON?.message || 'An error occurred. Please try again.';
          $('#responseMessage').html(`<div class="response-message error"><i class="fas fa-times-circle icon"></i> ${errMsg}</div>`);
          setTimeout(() => { $('#responseMessage').fadeOut(300, function() { $(this).html('').show(); }); }, 3000);
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
