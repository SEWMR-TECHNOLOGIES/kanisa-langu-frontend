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
    render_header('Record Attendance - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Record Attendance</h5>
            
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
            </ul>

            <div class="tab-content mt-3" id="revenueTabContent">
              <!-- Head Parish Attendance Form -->
                <div class="tab-pane fade show active" id="head-parish" role="tabpanel">
                  <form id="headParishForm">
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="eventTitle" class="form-label">Event Title</label>
                          <input type="text" class="form-control" id="eventTitle" name="event_title" placeholder="Enter event title">
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="attendanceDate" class="form-label">Attendance Date</label>
                          <input type="date" class="form-control" id="attendanceDate" name="attendance_date" max="<?php echo date('Y-m-d'); ?>">
                        </div>
                      </div>
                    </div>
                
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="serviceNumber" class="form-label">Service Number</label>
                          <select class="form-select" id="serviceNumber" name="service_number">
                            <option value="">Select Service Number</option>
                            <!-- Options should be dynamically populated -->
                          </select>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="subParishIdHP" class="form-label">Sub Parish</label>
                          <select class="form-select" id="subParishIdHP" name="sub_parish_id">
                            <option value="">Select Sub Parish</option>
                            <!-- Options should be dynamically populated -->
                          </select>
                        </div>
                      </div>
                    </div>
                
                    <div class="row">
                      <div class="col-lg-4">
                        <div class="mb-3">
                          <label for="maleAttendanceHP" class="form-label">Male Attendance</label>
                          <input type="number" class="form-control" id="maleAttendanceHP" name="male_attendance" value="0" min="0">
                        </div>
                      </div>
                      <div class="col-lg-4">
                        <div class="mb-3">
                          <label for="femaleAttendanceHP" class="form-label">Female Attendance</label>
                          <input type="number" class="form-control" id="femaleAttendanceHP" name="female_attendance" value="0" min="0">
                        </div>
                      </div>
                      <div class="col-lg-4">
                        <div class="mb-3">
                          <label for="childrenAttendanceHP" class="form-label">Children Attendance</label>
                          <input type="number" class="form-control" id="childrenAttendanceHP" name="children_attendance" value="0" min="0">
                        </div>
                      </div>
                    </div>
                    <div class="mb-3">
                      <button type="submit" class="btn btn-primary">Record Attendance</button>
                    </div>
                  </form>
                </div>


             <!-- Sub Parish Attendance Form -->
            <div class="tab-pane fade" id="sub-parish" role="tabpanel">
              <form id="subParishForm">
                <div class="row">
                  <div class="col-lg-4">
                    <div class="mb-3">
                      <label for="eventTitleSub" class="form-label">Event Title</label>
                      <input type="text" class="form-control" id="eventTitleSub" name="event_title" placeholder="Enter event title">
                    </div>
                  </div>
                  <div class="col-lg-4">
                    <div class="mb-3">
                      <label for="attendanceDateSub" class="form-label">Attendance Date</label>
                      <input type="date" class="form-control" id="attendanceDateSub" name="attendance_date" max="<?php echo date('Y-m-d'); ?>">
                    </div>
                  </div>
                  <div class="col-lg-4">
                    <div class="mb-3">
                      <label for="subParishId" class="form-label">Sub Parish</label>
                      <select class="form-select" id="subParishId" name="sub_parish_id">
                        <option value="">Select Sub Parish</option>
                        <!-- Options should be dynamically populated -->
                      </select>
                    </div>
                  </div>
                </div>

            
                <div class="row">
                  <div class="col-lg-4">
                    <div class="mb-3">
                      <label for="maleAttendanceSub" class="form-label">Male Attendance</label>
                      <input type="number" class="form-control" id="maleAttendanceSub" name="male_attendance" value="0" min="0">
                    </div>
                  </div>
                  <div class="col-lg-4">
                    <div class="mb-3">
                      <label for="femaleAttendanceSub" class="form-label">Female Attendance</label>
                      <input type="number" class="form-control" id="femaleAttendanceSub" name="female_attendance" value="0" min="0">
                    </div>
                  </div>
                  <div class="col-lg-4">
                    <div class="mb-3">
                      <label for="childrenAttendanceSub" class="form-label">Children Attendance</label>
                      <input type="number" class="form-control" id="childrenAttendanceSub" name="children_attendance" value="0" min="0">
                    </div>
                  </div>
                </div>
            
                <div class="mb-3">
                  <button type="submit" class="btn btn-primary">Record Attendance</button>
                </div>
              </form>
            </div>

             <!-- Community Attendance Form -->
            <div class="tab-pane fade" id="community" role="tabpanel">
              <form id="communityForm">
                <div class="row">
                  <div class="col-lg-6">
                    <div class="mb-3">
                      <label for="eventTitleCom" class="form-label">Event Title</label>
                      <input type="text" class="form-control" id="eventTitleCom" name="event_title" placeholder="Enter event title">
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="mb-3">
                      <label for="attendanceDateCom" class="form-label">Attendance Date</label>
                      <input type="date" class="form-control" id="attendanceDateCom" name="attendance_date" max="<?php echo date('Y-m-d'); ?>">
                    </div>
                  </div>
                </div>
            
                <div class="row">
                  <div class="col-lg-6">
                    <div class="mb-3">
                      <label for="subParishIdCom" class="form-label">Sub Parish</label>
                      <select class="form-select" id="subParishIdCom" name="sub_parish_id">
                        <option value="">Select Sub Parish</option>
                        <!-- Options should be dynamically populated -->
                      </select>
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="mb-3">
                      <label for="communityId" class="form-label">Community</label>
                      <select class="form-select" id="communityId" name="community_id">
                        <option value="">Select Community</option>
                        <!-- Options should be dynamically populated -->
                      </select>
                    </div>
                  </div>
                </div>
            
                <div class="row">
                  <div class="col-lg-4">
                    <div class="mb-3">
                      <label for="maleAttendanceCom" class="form-label">Male Attendance</label>
                      <input type="number" class="form-control" id="maleAttendanceCom" name="male_attendance" value="0" min="0">
                    </div>
                  </div>
                  <div class="col-lg-4">
                    <div class="mb-3">
                      <label for="femaleAttendanceCom" class="form-label">Female Attendance</label>
                      <input type="number" class="form-control" id="femaleAttendanceCom" name="female_attendance" value="0" min="0">
                    </div>
                  </div>
                  <div class="col-lg-4">
                    <div class="mb-3">
                      <label for="childrenAttendanceCom" class="form-label">Children Attendance</label>
                      <input type="number" class="form-control" id="childrenAttendanceCom" name="children_attendance" value="0" min="0">
                    </div>
                  </div>
                </div>
            
                <div class="mb-3">
                  <button type="submit" class="btn btn-primary">Record Attendance</button>
                </div>
              </form>
            </div>


              <!-- Group Attendance Form -->
            <div class="tab-pane fade" id="group" role="tabpanel">
              <form id="groupForm">
                <div class="row">
                  <div class="col-lg-4">
                    <div class="mb-3">
                      <label for="eventTitleGroup" class="form-label">Event Title</label>
                      <input type="text" class="form-control" id="eventTitleGroup" name="event_title" placeholder="Enter event title">
                    </div>
                  </div>
                  <div class="col-lg-4">
                    <div class="mb-3">
                      <label for="attendanceDateGroup" class="form-label">Attendance Date</label>
                      <input type="date" class="form-control" id="attendanceDateGroup" name="attendance_date" max="<?php echo date('Y-m-d'); ?>">
                    </div>
                  </div>
                    <div class="col-lg-4">
                    <div class="mb-3">
                      <label for="groupId" class="form-label">Group</label>
                      <select class="form-select" id="groupId" name="group_id">
                        <option value="">Select Group</option>
                        <!-- Options should be dynamically populated -->
                      </select>
                    </div>
                  </div>
                </div>
            
                <div class="row">
                  <div class="col-lg-4">
                    <div class="mb-3">
                      <label for="maleAttendanceGroup" class="form-label">Male Attendance</label>
                      <input type="number" class="form-control" id="maleAttendanceGroup" name="male_attendance" value="0" min="0">
                    </div>
                  </div>
                  <div class="col-lg-4">
                    <div class="mb-3">
                      <label for="femaleAttendanceGroup" class="form-label">Female Attendance</label>
                      <input type="number" class="form-control" id="femaleAttendanceGroup" name="female_attendance" value="0" min="0">
                    </div>
                  </div>
                  <div class="col-lg-4">
                    <div class="mb-3">
                      <label for="childrenAttendanceGroup" class="form-label">Children Attendance</label>
                      <input type="number" class="form-control" id="childrenAttendanceGroup" name="children_attendance" value="0" min="0">
                    </div>
                  </div>
                </div>
            
                <div class="mb-3">
                  <button type="submit" class="btn btn-primary">Record Attendance</button>
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
      $('#responseMessage').html('<div class="loading">Recording attendance...</div>');
      $.ajax({
        type: 'POST',
        url: '../api/records/record_attendance',
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

    // Load revenue streams for sub-parish when a sub-parish is selected
    $('#subParishIdCom').on('change', function () {
      loadCommunities($(this).val()); 
    });
  });
</script>

</body>

</html>
