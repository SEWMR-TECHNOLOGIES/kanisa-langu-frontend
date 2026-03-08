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
    render_header('Distribute Harambee - Kanisa Langu');
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
              <h5 class="card-title fw-semibold mb-4">Distribute Harambee</h5>

              <ul class="nav nav-tabs" id="harambeeTabs" role="tablist">
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
                <!-- Head Parish Harambee Form -->
                <div class="tab-pane fade show active" id="head-parish" role="tabpanel">
                  <form id="headParishForm" autocomplete="off">
                    <input type="hidden" name="target" value="head_parish">
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
                    </div>
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="subParishIdHP" class="form-label">Sub Parish</label>
                          <select class="form-select" id="subParishIdHP" name="sub_parish_id">
                            <option value="">Select Sub Parish</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="amountHP" class="form-label">Amount to Contribute</label>
                          <input type="number" class="form-control" id="amountHP" name="amount" placeholder="Amount">
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="percentageHP" class="form-label">Set By Percentage (Optional)</label>
                          <input type="number" class="form-control" id="percentageHP" name="percentage" placeholder="Percentage (0 - 100)" min="1" max="100">
                        </div>
                      </div>
                    </div>
                    <div id="harambeeDetailsHP" class="harambee-details mb-2"></div>
                    <button type="submit" class="btn btn-primary">Distribute Harambee</button>
                  </form>
                </div>

                <!-- Sub Parish Harambee Form -->
                <div class="tab-pane fade" id="sub-parish" role="tabpanel">
                  <form id="subParishForm" autocomplete="off">
                    <input type="hidden" name="target" value="sub_parish">
                    <div class="row">
                      <div class="col-lg-12">
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
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="subParishId" class="form-label">Sub Parish</label>
                          <select class="form-select" id="subParishId" name="sub_parish_id">
                            <option value="">Select Sub Parish</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="amountSP" class="form-label">Amount to Contribute</label>
                          <input type="number" class="form-control" id="amountSP" name="amount" placeholder="Amount">
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="percentageSP" class="form-label">Set By Percentage (Optional)</label>
                          <input type="number" class="form-control" id="percentageSP" name="percentage" placeholder="Percentage (0 - 100)" min="1" max="100">
                        </div>
                      </div>
                    </div>
                    <div id="harambeeDetailsSP" class="harambee-details mb-2"></div>
                    <button type="submit" class="btn btn-primary">Distribute Harambee</button>
                  </form>
                </div>

                <!-- Community Harambee Form -->
                <div class="tab-pane fade" id="community" role="tabpanel">
                  <form id="communityForm" autocomplete="off">
                    <input type="hidden" name="target" value="community">
                    <div class="row">
                      <div class="col-lg-12">
                        <div class="mb-3">
                          <label for="harambeeIdCOM" class="form-label">Harambee</label>
                          <select class="form-select" id="harambeeIdCOM" name="harambee_id">
                            <option value="">Select Harambee</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                    </div>
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
                          <label for="amountComm" class="form-label">Amount to Contribute</label>
                          <input type="number" class="form-control" id="amountComm" name="amount" placeholder="Amount">
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="percentageComm" class="form-label">Set By Percentage (Optional)</label>
                          <input type="number" class="form-control" id="percentageComm" name="percentage" placeholder="Percentage (0 - 100)" min="1" max="100">
                        </div>
                      </div>
                    </div>
                    <div id="harambeeDetailsCOM" class="harambee-details mb-2"></div>
                    <button type="submit" class="btn btn-primary">Distribute Harambee</button>
                  </form>
                </div>

                <!-- Group Harambee Form -->
                <div class="tab-pane fade" id="group" role="tabpanel">
                  <form id="groupForm" autocomplete="off">
                    <input type="hidden" name="target" value="group">
                    <div class="row">
                      <div class="col-lg-12">
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
                          <label for="amountGroup" class="form-label">Amount to Contribute</label>
                          <input type="number" class="form-control" id="amountGroup" name="amount" placeholder="Amount">
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="percentageGroup" class="form-label">Set By Percentage (Optional)</label>
                          <input type="number" class="form-control" id="percentageGroup" name="percentage" placeholder="Percentage (0 - 100)" min="1" max="100">
                        </div>
                      </div>
                    </div>
                    <div id="harambeeDetailsGP" class="harambee-details mb-2"></div>
                    <button type="submit" class="btn btn-primary">Distribute Harambee</button>
                  </form>
                </div>
              </div>
              <div id="responseMessage"></div>
            </div>
          </div>
        </div>
      </div>
  </div>
</body>



 
 <?php require_once('components/footer_files.php') ?>
  
<script>
  $('select').select2({
    width: '100%'
  });

  $(document).ready(function () {
    // Function to submit the form dynamically
    function submitForm(formId, target) {
      const formData = $(formId).serialize() + '&target=' + target;

      $.ajax({
        type: 'POST',
        url: '../api/records/record_harambee_distribution',
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
          $('#responseMessage').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i>An error occurred: ' + error + '</div>');
        }
      });
    }

    // Attach form submit handlers
    $('#headParishForm').on('submit', function (event) {
      event.preventDefault();
      submitForm('#headParishForm', 'head_parish');
    });

    $('#subParishForm').on('submit', function (event) {
      event.preventDefault();
      submitForm('#subParishForm', 'sub_parish');
    });

    $('#communityForm').on('submit', function (event) {
      event.preventDefault();
      submitForm('#communityForm', 'community');
    });

    $('#groupForm').on('submit', function (event) {
      event.preventDefault();
      submitForm('#groupForm', 'group');
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
          $('#subParishId').html(options);
          $('#subParishIdHP').html(options); 
          $('#subParishIdCom').html(options); 
        },
        error: function (xhr, status, error) {
          console.log('Error loading sub-parishes:', error);
        }
      });
    }

  // Load bank accounts
  loadHarambee('harambeeIdHP', '../api/data/head_parish_harambee?limit=all&target=head-parish');

  // Load Harambee for sub parish community
  loadHarambee('harambeeIdSP', '../api/data/head_parish_harambee?limit=all&target=sub-parish');

  // Load Harambee for head parish
  loadHarambee('harambeeIdCOM', '../api/data/head_parish_harambee?limit=all&target=community');

  // Load Harambee for groups
  loadHarambee('harambeeIdGP', '../api/data/head_parish_harambee?limit=all&target=group');

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


    function loadHarambeeDetails(targetId, harambeeId, responseDivId) {
      if (!harambeeId || !responseDivId) {
        console.error('Harambee ID and response div ID must be provided.');
        return; 
      }
    
      // Optionally show a loading indicator
      $(responseDivId).html('<div class="loading">Loading...</div>');
    
      $.ajax({
        type: 'GET',
        url: `../api/data/get_harambee_details?harambee_id=${harambeeId}&target=${targetId}`,
        dataType: 'json',
        success: function (response) {
          if (response.success) {
            $(responseDivId).html(response.html);
          } else {
            $(responseDivId).html('<div class="alert alert-danger">Unable to retrieve harambee details.</div>');
          }
        },
        error: function (xhr) {
          console.error('Error loading harambee details:', xhr);
          $(responseDivId).html(`<div class="alert alert-danger">An error occurred: ${xhr.status} - ${xhr.statusText}</div>`);
        }
      });
    }



    $('#harambeeIdHP').change(function () {
      const harambeeId = $(this).val();
      const target = 'head_parish'; 
      if (harambeeId) {
        loadHarambeeDetails(target, harambeeId, '#harambeeDetailsHP'); 
      } else {
        $('#harambeeDetailsHP').empty();
      }
    });

    $('#harambeeIdSP').change(function () {
      const harambeeId = $(this).val();
      const target = 'sub_parish'; 
      if (harambeeId) {
        loadHarambeeDetails(target, harambeeId, '#harambeeDetailsSP'); 
      } else {
        $('#harambeeDetailsHP').empty();
      }
    });
    
        $('#harambeeIdCOM').change(function () {
      const harambeeId = $(this).val();
      const target = 'community'; 
      if (harambeeId) {
        loadHarambeeDetails(target, harambeeId, '#harambeeDetailsGP'); 
      } else {
        $('#harambeeDetailsHP').empty();
      }
    });
    
        $('#harambeeIdGP').change(function () {
      const harambeeId = $(this).val();
      const target = 'groups'; 
      if (harambeeId) {
        loadHarambeeDetails(target, harambeeId, '#harambeeDetailsGP'); 
      } else {
        $('#harambeeDetailsHP').empty();
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
</script>

</body>

</html>
