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
    render_header('Record Harambee Contribution - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Record Harambee Contribution</h5>

            <!-- Harambee Contribution Form -->
            <form id="harambeeContributionForm" autocomplete="off">
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
                  <div class="mb-3">
                    <label for="amount" class="form-label">Contribution Amount</label>
                   <input type="text" class="form-control" id="amount" name="amount" placeholder="Amount">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="contributionDate" class="form-label">Contribution Date</label>
                    <input type="date" class="form-control" id="contributionDate" name="contribution_date" 
                           max="<?php echo date('Y-m-d'); ?>" 
                           value="<?php echo date('Y-m-d'); ?>">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="paymentMethod" class="form-label">Payment Method</label>
                    <?php render_payment_method_dropdown('paymentMethodHeadParish'); ?>
                  </div>
                </div>
              </div>
              <div id="memberInfo"></div>
            <div class="mb-3 d-flex flex-column flex-md-row">
              <button type="submit" class="btn btn-primary mb-2 mb-md-0 w-100 w-md-auto">Record Harambee Contribution</button>
              <?php 
              if(isset($_SESSION['head_parish_admin_id']) && $_SESSION['head_parish_admin_role'] !== 'clerk'){
                  echo '<button type="button" id="showSummaryButton" class="btn btn-info mb-2 mb-md-0 w-100 w-md-auto ms-md-2">Show Harambee Summary</button>';
              } else {
                    echo '<a href="/head-parish/harambee-record-statistics" class="btn btn-secondary mb-2 mb-md-0 w-100 w-md-auto ms-md-2">View Recorded Contributions</a>';
                }
              ?>
            </div>
            </form>
            <div id="responseMessage" class="mt-3"></div>
          </div>
        </div>
        <?php require_once('components/sms-statistics.php'); ?>
      </div>
    </div>
  </div>

<!-- Modal HTML for displaying Harambee Summary -->
<div class="modal fade" id="harambeeSummaryModal" tabindex="-1" aria-labelledby="harambeeSummaryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="harambeeSummaryContent">
                    <!-- Summary content will be dynamically populated here -->
                </div>
                <div id="responseMessageModal" class="mt-3"></div>
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

    // Format number with commas
    function formatWithCommas(number) {
      return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
    
    // Remove all commas
    function removeCommas(value) {
      return value.replace(/,/g, '');
    }
    
    // Live format as user types
    $('#amount').on('input', function () {
      const raw = removeCommas($(this).val());
      if (!isNaN(raw) && raw !== '') {
        $(this).val(formatWithCommas(raw));
      } else {
        $(this).val('');
      }
    });

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
            const memberType = member.type ? ` - ${member.type}` : '';
            options += `<option value="${member.id}">${fullName}${phone}${envelopeNumber}${memberType}</option>`;
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
      const cleanedAmount = removeCommas($('#amount').val());
      $('#amount').val(cleanedAmount);
        // Disable the submit button to prevent multiple clicks
      const submitButton = $(this).find('button[type="submit"]');
      submitButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Recording...');
      
      // Optionally show a loading indicator
      $('#responseMessage').html('<div class="response-message"><i class="fas fa-spinner fa-spin icon"></i> Sending Request...</div>');
      let formData = $(this).serialize();

      let localTime = getLocalTimestamp();
      formData += `&local_timestamp=${encodeURIComponent(localTime)}`;

      // Form submission logic
      $.ajax({
        type: 'POST',
        url: '../api/records/record_harambee_contribution',
        data: formData,
        dataType: 'json',
        success: function (response) {
          let messageHtml = '';
          if (response.success) {
            messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i>' + response.message + '</div>';
            setTimeout(function () {
            //   $('#harambeeContributionForm')[0].reset(); 
            //   $('#targetTypeSelectionIndividual').val('').trigger('change');
            //   $('#harambeeIdIndividual').val('').trigger('change');
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
        },
        complete: function () {
          // Re-enable the submit button and reset the text
          submitButton.prop('disabled', false).html('Record Harambee Contribution');
        }
      });
    });
    
    
$('#showSummaryButton').click(function () {
  // Get selected values
  const targetType = $('#targetTypeSelectionIndividual').val();
  const harambeeId = $('#harambeeIdIndividual').val();
  const contributionDate = $('#contributionDate').val();

  // Check if the required fields (Type, Harambee, and Contribution Date) are filled
  if (!targetType || !harambeeId || !contributionDate) {
    const errorMessage = "Please fill in all the required fields (Type, Harambee, and Contribution Date) to see the summary.";
    $('#responseMessage').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i>' + errorMessage + '</div>');
    return;
  }

  // Clear previous response message
  $('#responseMessage').html('');

  // Show the "Loading Summary" message
  $('#responseMessage').html('<div class="response-message" style="color: blue;"><i class="fas fa-spinner fa-spin"></i> Loading Summary...</div>');

  // Construct the URL with query parameters
  const url = `../api/data/daily_harambee_summary.php?target=${encodeURIComponent(targetType)}&harambee_id=${encodeURIComponent(harambeeId)}&contribution_date=${encodeURIComponent(contributionDate)}`;

  // Send data to the API to fetch the summary using GET
  $.ajax({
    type: 'GET',
    url: url, // Direct URL with query parameters
    dataType: 'json', // Expecting JSON response
    success: function (response) {
      // Log the full response for debugging
      console.log("API Response:", response);
      
      // Clear the loading message
      $('#responseMessage').html('');

      // Check if the response contains data
      if (response.success) {
        const data = response.data;
        let modalContent = '';

        // Display the date at the top of the modal
        modalContent += `<h4 class="text-center mb-4 text-primary font-weight-bold">Harambee Contribution Summary on ${contributionDate}</h4>`;

        // Initialize variables to track grand total
        let grandTotalCash = 0, grandTotalBankTransfer = 0, grandTotalMobilePayment = 0, grandTotalCard = 0, grandTotalMembers = 0;

        // Function to format numbers as ###,###
        function formatNumber(number) {
          return number.toLocaleString();
        }

        // Iterate through each sub parish and community
        data.forEach(function(subParish) {
          let subTotalCash = 0, subTotalBankTransfer = 0, subTotalMobilePayment = 0, subTotalCard = 0, subTotalMembers = 0;

          // Apply Bootstrap classes for sub-parish styling
          modalContent += `
            <h5 class="text-center font-weight-bold text-white bg-primary py-2 rounded-top">${subParish.sub_parish_name}</h5>
            <table class="table table-bordered shadow-sm">
              <thead class="thead-dark">
                <tr>
                  <th>Community Name</th>
                  <th>Cash</th>
                  <th>Bank Transfer</th>
                  <th>Mobile Payment</th>
                  <th>Card</th>
                  <th>Member Count</th>
                </tr>
              </thead>
              <tbody>
          `;

          subParish.communities.forEach(function(community) {
            modalContent += `
              <tr>
                <td>${community.community_name}</td>
                <td class="text-right">${formatNumber(community.Cash)}</td>
                <td class="text-right">${formatNumber(community['Bank Transfer'])}</td>
                <td class="text-right">${formatNumber(community['Mobile Payment'])}</td>
                <td class="text-right">${formatNumber(community.Card)}</td>
                <td class="text-center">${community.member_count}</td>
              </tr>
            `;

            // Add to sub-total for this sub-parish
            subTotalCash += community.Cash;
            subTotalBankTransfer += community['Bank Transfer'];
            subTotalMobilePayment += community['Mobile Payment'];
            subTotalCard += community.Card;
            subTotalMembers += community.member_count;

            // Add to grand total
            grandTotalCash += community.Cash;
            grandTotalBankTransfer += community['Bank Transfer'];
            grandTotalMobilePayment += community['Mobile Payment'];
            grandTotalCard += community.Card;
            grandTotalMembers += community.member_count;
          });

          // Add sub-total for the sub-parish
          modalContent += `
            <tr class="font-weight-bold thead-dark">
              <td class="text-center">Sub-Total</td>
              <td class="text-right">${formatNumber(subTotalCash)}</td>
              <td class="text-right">${formatNumber(subTotalBankTransfer)}</td>
              <td class="text-right">${formatNumber(subTotalMobilePayment)}</td>
              <td class="text-right">${formatNumber(subTotalCard)}</td>
              <td class="text-center">${subTotalMembers}</td>
            </tr>
          `;

          modalContent += '</tbody></table>';
        });

        // Add grand total at the end
        modalContent += `
          <h5 class="text-center font-weight-bold text-white bg-success py-2 rounded-top">Grand Total</h5>
          <table class="table table-bordered table-striped shadow-sm">
            <thead class="thead-dark">
              <tr><th>Total</th><th>Cash</th><th>Bank Transfer</th><th>Mobile Payment</th><th>Card</th><th>Member Count</th></tr>
            </thead>
            <tbody>
              <tr class="font-weight-bold">
                <td class="text-center">All Sub-Parishes</td>
                <td class="text-right">${formatNumber(grandTotalCash)}</td>
                <td class="text-right">${formatNumber(grandTotalBankTransfer)}</td>
                <td class="text-right">${formatNumber(grandTotalMobilePayment)}</td>
                <td class="text-right">${formatNumber(grandTotalCard)}</td>
                <td class="text-center">${grandTotalMembers}</td>
              </tr>
            </tbody>
          </table>
        `;

        // Populate the modal content
        $('#harambeeSummaryContent').html(modalContent);

        // Show the modal with smooth animation
        $('#harambeeSummaryModal').modal('show');
      } else {
        $('#responseMessage').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i>No summary available.</div>');
      }
    },
    error: function (xhr, status, error) {
      // Log the error response for debugging
      console.log("Error Response:", xhr.responseJSON || error);

      const errorMessage = xhr.responseJSON?.message || `An error occurred: ${error}`;
      $('#responseMessage').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i>' + errorMessage + '</div>');
    }
  });
});




  });
</script>
</body>

</html>
