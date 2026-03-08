<?php 
// Prefill date values for the first and last day of the current year
$first_day_of_year = date('Y-01-01');
$last_day_of_year = date('Y-12-31');
?>
<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Set Envelope Target - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Set /Update Envelope Target</h5>

            <!-- Envelope Target Form -->
            <form id="envelopeTargetForm" autocomplete="off">
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
                    <label for="targetAmount" class="form-label">Target Amount</label>
                    <input type="number" class="form-control" id="targetAmount" name="target" placeholder="Target Amount" min="0">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="fromDate" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="fromDate" name="from_date" value="<?php echo $first_day_of_year; ?>">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="toDate" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="toDate" name="end_date" value="<?php echo $last_day_of_year; ?>">
                  </div>
                </div>
              </div>
              
              <div id="memberInfo"></div>
              <div class="mb-3">
                <button type="submit" class="btn btn-primary">Set Envelope Target</button>
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

    // Load member's target when a member is selected
    $('#memberId').change(function () {
        const member_id = $(this).val();

        if (member_id) {
            $.ajax({
                type: 'GET',
                url: `../api/data/get_envelope_target_amount?member_id=${member_id}`,
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        $('#memberInfo').html(response.html); 
                    } else {
                        $('#memberInfo').html(`<div class="alert alert-danger">${response.message}</div>`);
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
    });

    // Submit envelope target form
    $('#envelopeTargetForm').submit(function (event) {
      event.preventDefault();
      $('#responseMessage').html('<div class="loading">Loading...</div>');
      
      let formData = $(this).serialize();

      // Form submission logic
      $.ajax({
        type: 'POST',
        url: '../api/records/set_envelope_target',
        data: formData,
        dataType: 'json',
        success: function (response) {
          let messageHtml = '';
          if (response.success) {
            messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i>' + response.message + '</div>';
            setTimeout(function () {
              $('#targetAmount').val('').trigger('input');
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
