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
    render_header('Record Harambee Letter Status - Kanisa Langu');
  ?>
</head>
<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
    
    <?php require_once('components/sidebar.php') ?>
    
    <div class="body-wrapper">
      <?php require_once('components/header.php') ?>
      <div class="container-fluid">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Record Harambee Letter Status</h5>

            <form id="harambeeLetterForm" autocomplete="off">
              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="memberId" class="form-label">Select Member</label>
                    <select class="form-select" id="memberId" name="member_id">
                      <option value="">Select Member</option>
                    </select>
                    <div class="form-text mt-1">
                      Not registered? 
                      <a href="/head-parish/register-church-member?returnUrl=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">Click here to register</a>
                    </div>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="form-check mt-4 pt-2">
                    <input class="form-check-input" type="checkbox" value="1" id="receivedLetter" name="received_letter">
                    <label class="form-check-label" for="receivedLetter">
                      Member has received a Harambee letter
                    </label>
                  </div>
                </div>
              </div>

              <div class="mb-3 mt-3">
                <button type="submit" class="btn btn-primary">Record Status</button>
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

    // Load members into select
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

    loadMembers();

    // Form submission
    $('#harambeeLetterForm').submit(function (event) {
      event.preventDefault();
      $('#responseMessage').html('<div class="loading">Loading...</div>');

      let formData = $(this).serialize();
      let localTime = getLocalTimestamp();
      formData += `&local_timestamp=${encodeURIComponent(localTime)}`;

      $.ajax({
        type: 'POST',
        url: '../api/records/record_harambee_letter_status',
        data: formData,
        dataType: 'json',
        success: function (response) {
          let messageHtml = '';
          const wasChecked = $('#receivedLetter').is(':checked'); // Track checkbox status
        
          if (response.success) {
            messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i>' + response.message + '</div>';
            
            // Reset the form but keep the checkbox if it was checked
            $('#harambeeLetterForm')[0].reset();
            $('#memberId').val('').trigger('change');
            if (wasChecked) {
              $('#receivedLetter').prop('checked', true);
            }
          } else {
            messageHtml = '<div class="response-message error"><i class="fas fa-times-circle icon"></i>' + response.message + '</div>';
          }
        
          $('#responseMessage').html(messageHtml);
        
          // Clear the message after 2 seconds
          setTimeout(() => {
            $('#responseMessage').fadeOut('slow', function () {
              $(this).html('').show();
            });
          }, 2000);
        },
        
        error: function (xhr, status, error) {
          const errorMessage = xhr.responseJSON?.message || `An error occurred.${error}`;
          $('#responseMessage').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i>' + errorMessage + '</div>');
        }
      });
    });

    // Helper for timestamp
    function getLocalTimestamp() {
      const now = new Date();
      return now.toISOString();
    }

  });
</script>
</body>
</html>
