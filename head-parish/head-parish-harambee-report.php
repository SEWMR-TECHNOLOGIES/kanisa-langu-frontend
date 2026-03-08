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
    render_header('Head Parish Harambee Report - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Head Parish Harambee Report</h5>

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
            </form>
            <div id="responseMessage" class="mt-3"></div>
          </div>
        </div>
        <div id="harambeeInfo"></div>
      </div>
    </div>
  </div>

  <?php require_once('components/footer_files.php') ?>

<script>
  $('select').select2({
    width: '100%'
  });
  
$(document).ready(function () {
        // Load harambee function
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
    
    // Load harambees when the type changes
    $('#targetTypeSelectionIndividual').change(function () {
        const selectedType = $(this).val();
        loadHarambee('harambeeIdIndividual', `../api/data/head_parish_harambee?limit=all&target=${selectedType}`);
    });

    // Load member's target and contributions when a member is selected
    $('#harambeeIdIndividual').change(function () {
        const harambee_id = $('#harambeeIdIndividual').val();
        const target = $('#targetTypeSelectionIndividual').val();
        
        // Check if all selections are made before making the API call
        if (harambee_id && target) {
            // Optionally show a loading indicator
            $('#harambeeInfo').html('<div class="loading">Loading...</div>');
            $.ajax({
                type: 'GET',
                url: `../api/data/get_harambee_details.php?harambee_id=${harambee_id}&target=${target}`,
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        $('#harambeeInfo').html(response.html); 
                    } else {
                        const errorMessage = response.message || 'Unable to retrieve harambee information.';
                        $('#harambeeInfo').html(`<div class="alert alert-danger">${errorMessage}</div>`);
                    }
                },
                error: function (xhr, status, error) {
                    let errorMessage = 'An error occurred while fetching member information.';

                    if (xhr.status === 404) {
                        errorMessage = 'Harambee information not found.';
                    } else if (xhr.status === 500) {
                        errorMessage = 'Server error. Please try again later.';
                    } else if (xhr.status === 0) {
                        errorMessage = 'Network error. Please check your internet connection.';
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    $('#harambeeInfo').html(`<div class="alert alert-danger">${errorMessage}</div>`);
                }
            });
        } else {
            console.log('Missing required parameters: harambee_id, member_id, or target');
            $('#harambeeInfo').empty(); 
        }
    });

    // Load members initially
    loadMembers();
});

</script>
</body>

</html>
