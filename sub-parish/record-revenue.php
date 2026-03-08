<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');
?>
<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Record Revenue - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Record Revenue</h5>
            
            <ul class="nav nav-tabs" id="revenueTabs" role="tablist">
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
              <li class="nav-item" role="presentation">
                <a class="nav-link" id="other-tab" data-bs-toggle="tab" href="#other" role="tab">Other Head Parish Revenues</a>
              </li>
            </ul>

            <div class="tab-content mt-3" id="revenueTabContent">
              <!-- Head Parish Revenue Form -->
              <div class="tab-pane fade show active" id="head-parish" role="tabpanel">
                <form id="headParishForm">
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="serviceNumber" class="form-label">Service Number</label>
                        <select class="form-select" id="serviceNumber" name="service_number">
                          <option value="">Select Service Number</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="revenueStreamIdHP" class="form-label">Revenue Stream</label>
                        <select class="form-select" id="revenueStreamIdHP" name="revenue_stream_id">
                          <option value="">Select Revenue Stream</option>
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
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="revenueAmount" class="form-label">Amount</label>
                        <input type="number" class="form-control" id="revenueAmount" name="revenue_amount" placeholder="Amount">
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="paymentMethod" class="form-label">Payment Method</label>
                         <?php render_payment_method_dropdown('paymentMethodHeadParish'); ?>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="revenueDateHP" class="form-label">Revenue Date</label>
                        <input type="date" class="form-control" id="revenueDateHP" name="revenue_date" max="<?php echo date('Y-m-d'); ?>">
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-12">
                      <div class="mb-3">
                        <label for="descriptionHeadParish" class="form-label">Description</label>
                        <textarea class="form-control" id="descriptionHeadParish" name="description" rows="2" placeholder="Enter description..."></textarea>
                      </div>
                    </div>
                  </div>
                  <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Record Revenue</button>
                  </div>
                </form>
              </div>

              <!-- Sub Parish Revenue Form -->
              <div class="tab-pane fade" id="sub-parish" role="tabpanel">
                <form id="subParishForm">
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="subParishId" class="form-label">Sub Parish</label>
                        <select class="form-select" id="subParishId" name="sub_parish_id">
                          <option value="">Select Sub Parish</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="revenueStreamIdSP" class="form-label">Revenue Stream</label>
                        <select class="form-select" id="revenueStreamIdSP" name="revenue_stream_id">
                          <option value="">Select Revenue Stream</option>
                        </select>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="revenueAmountSub" class="form-label">Amount</label>
                        <input type="number" class="form-control" id="revenueAmountSub" name="revenue_amount" placeholder="Amount">
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="paymentMethodSub" class="form-label">Payment Method</label>
                        <?php render_payment_method_dropdown('paymentMethodSubParish'); ?>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="revenueDateSP" class="form-label">Revenue Date</label>
                        <input type="date" class="form-control" id="revenueDateSP" name="revenue_date" max="<?php echo date('Y-m-d'); ?>">
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="descriptionSubParish" class="form-label">Description</label>
                        <textarea class="form-control" id="descriptionSubParish" name="description" rows="1" placeholder="Enter description..."></textarea>
                      </div>
                    </div>
                  </div>
                  
                  <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Record Revenue</button>
                  </div>
                </form>
              </div>

              <!-- Community Revenue Form -->
              <div class="tab-pane fade" id="community" role="tabpanel">
                <form id="communityForm">
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="subParishIdCom" class="form-label">Sub Parish</label>
                        <select class="form-select" id="subParishIdCom" name="sub_parish_id">
                          <option value="">Select Sub Parish</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="communityId" class="form-label">Community</label>
                        <select class="form-select" id="communityId" name="community_id">
                          <option value="">Select Community</option>
                        </select>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="revenueStreamIdCM" class="form-label">Revenue Stream</label>
                        <select class="form-select" id="revenueStreamIdCM" name="revenue_stream_id">
                          <option value="">Select Revenue Stream</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="revenueAmountComm" class="form-label">Amount</label>
                        <input type="number" class="form-control" id="revenueAmountComm" name="revenue_amount" placeholder="Amount">
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="revenueDateCom" class="form-label">Revenue Date</label>
                        <input type="date" class="form-control" id="revenueDateCom" name="revenue_date" max="<?php echo date('Y-m-d'); ?>">
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="paymentMethodCom" class="form-label">Payment Method</label>
                        <?php render_payment_method_dropdown('paymentMethodCommunity'); ?>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-lg-12">
                      <div class="mb-3">
                        <label for="descriptionCommunity" class="form-label">Description</label>
                        <textarea class="form-control" id="descriptionCommunity" name="description" rows="2" placeholder="Enter description..."></textarea>
                      </div>
                    </div>
                  </div>
                  
                  <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Record Revenue</button>
                  </div>
                </form>
              </div>

              <!-- Group Revenue Form -->
              <div class="tab-pane fade" id="group" role="tabpanel">
                <form id="groupForm">
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="groupId" class="form-label">Group</label>
                        <select class="form-select" id="groupId" name="group_id">
                          <option value="">Select Group</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="revenueStreamIdGP" class="form-label">Revenue Stream</label>
                        <select class="form-select" id="revenueStreamIdGP" name="revenue_stream_id">
                          <option value="">Select Revenue Stream</option>
                        </select>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="revenueAmountGP" class="form-label">Amount</label>
                        <input type="number" class="form-control" id="revenueAmountGP" name="revenue_amount" placeholder="Amount">
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="paymentMethodGroup" class="form-label">Payment Method</label>
                        <?php render_payment_method_dropdown('paymentMethodGroup'); ?>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="revenueDateGP" class="form-label">Revenue Date</label>
                        <input type="date" class="form-control" id="revenueDateGP" name="revenue_date" max="<?php echo date('Y-m-d'); ?>">
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="descriptionGroup" class="form-label">Description</label>
                        <textarea class="form-control" id="descriptionGroup" name="description" rows="1" placeholder="Enter description..."></textarea>
                      </div>
                    </div>
                  </div>

                  
                  <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Record Revenue</button>
                  </div>
                </form>
              </div>

            <!-- Other Head Parish Revenue Form -->
              <div class="tab-pane fade show" id="other" role="tabpanel">
                <form id="otherForm">
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="serviceNumberOther" class="form-label">Service Number</label>
                        <select class="form-select" id="serviceNumberOther" name="service_number">
                          <option value="">Select Service Number</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="revenueStreamIdOther" class="form-label">Revenue Stream</label>
                        <select class="form-select" id="revenueStreamIdOther" name="revenue_stream_id">
                          <option value="">Select Revenue Stream</option>
                        </select>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="revenueAmount" class="form-label">Amount</label>
                        <input type="number" class="form-control" id="revenueAmount" name="revenue_amount" placeholder="Amount">
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="paymentMethod" class="form-label">Payment Method</label>
                         <?php render_payment_method_dropdown('paymentMethodOther'); ?>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="revenueDateHP" class="form-label">Revenue Date</label>
                        <input type="date" class="form-control" id="revenueDateHP" name="revenue_date" max="<?php echo date('Y-m-d'); ?>">
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="descriptionHeadParish" class="form-label">Description</label>
                        <textarea class="form-control" id="descriptionHeadParish" name="description" rows="1" placeholder="Enter description..."></textarea>
                      </div>
                    </div>
                  </div>
                  
                  <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Record Revenue</button>
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
    // Function to submit the form dynamically
    function submitForm(formId, target) {
      const formData = $(formId).serialize() + '&target=' + target;

      $.ajax({
        type: 'POST',
        url: '../api/records/record_revenue',
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

    $('#otherForm').on('submit', function (event) {
      event.preventDefault();
      submitForm('#otherForm', 'other');
    });
    // Load service numbers
    function loadServiceNumbers() {
      $.ajax({
        type: 'GET',
        url: '../api/data/head_parish_services',
        dataType: 'json',
        success: function (response) {
          let options = '<option value="">Select Service Number</option>';
          $.each(response.data, function (index, service) {
            options += '<option value="' + service.service_id + '">' + service.service + '</option>';
          });
          $('#serviceNumber').html(options);
          $('#serviceNumberOther').html(options);
        },
        error: function (xhr, status, error) {
          console.log('Error loading service numbers:', error);
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
          $('#subParishIdHP').html(options); 
          $('#subParishId').html(options);
          $('#subParishIdCom').html(options); 
        },
        error: function (xhr, status, error) {
          console.log('Error loading sub-parishes:', error);
        }
      });
    }

    // Load revenue streams based on context
    function loadRevenueStreams(target) {
      $.ajax({
        type: 'GET',
        url: '../api/data/head_parish_revenue_streams', 
        data: { target: target },
        dataType: 'json',
        success: function (response) {
          let options = '<option value="">Select Revenue Stream</option>';
          $.each(response.data, function (index, stream) {
            options += '<option value="' + stream.revenue_stream_id + '">' + stream.revenue_stream_name + '</option>';
          });
          if (target === 'head_parish') {
            $('#revenueStreamIdHP').html(options);
          } else if (target === 'sub_parish') {
            $('#revenueStreamIdSP').html(options);
          } else if (target === 'community') {
            $('#revenueStreamIdCM').html(options);
          } else if (target === 'group') {
            $('#revenueStreamIdGP').html(options);
          }
          else if (target === 'other') {
            $('#revenueStreamIdOther').html(options);
          }
        },
        error: function (xhr, status, error) {
          console.log('Error loading revenue streams:', error);
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

    // Load groups
    function loadGroups() {
      $.ajax({
        type: 'GET',
        url: '../api/data/head_parish_groups?limit=all', // Adjust with your API URL for groups
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

    // Load initial data
    loadServiceNumbers();
    loadSubParishes();
    loadGroups();

    // Load revenue streams when service number is selected
    $('#serviceNumber').on('change', function () {
      loadRevenueStreams('head_parish');
      loadRevenueStreams('other');
    });

    // Load revenue streams when service number is selected
    $('#serviceNumberOther').on('change', function () {
      loadRevenueStreams('other');
    });
    
    // Load revenue streams for sub-parish when a sub-parish is selected
    $('#subParishId').on('change', function () {
      loadRevenueStreams('sub_parish');
    });

    // Load revenue streams for sub-parish when a sub-parish is selected
    $('#subParishIdCom').on('change', function () {
      loadRevenueStreams('sub_parish');
      loadCommunities($(this).val()); 
    });
    
    // Load revenue streams for community when community is selected
    $('#communityId').on('change', function () {
      loadRevenueStreams('community');
    });

    // Load revenue streams for group when a group is selected
    $('#groupId').on('change', function () {
      loadRevenueStreams('group');
    });
  });
</script>

</body>

</html>
