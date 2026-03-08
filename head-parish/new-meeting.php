<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
check_session('head_parish_admin_id', '../head-parish/sign-in');
?>

<!doctype html>
<html lang="en">
<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('New Meeting');
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
            <h5 class="card-title fw-semibold mb-4">Meetings</h5>

           <!-- Tabs Navigation -->
            <ul class="nav nav-tabs" id="serviceTabs" role="tablist">
              <li class="nav-item" role="presentation">
                <a class="nav-link active" id="new-meeting-tab" data-bs-toggle="tab" href="#new-meeting" role="tab">New Meeting</a>
              </li>
              <li class="nav-item" role="presentation">
                <a class="nav-link" id="meeting-agenda-tab" data-bs-toggle="tab" href="#meeting-agenda" role="tab">Meeting Agenda</a>
              </li>
              <li class="nav-item" role="presentation">
                <a class="nav-link" id="additional-notes-tab" data-bs-toggle="tab" href="#additional-notes" role="tab">Additional Notes</a>
              </li>
              <li class="nav-item" role="presentation">
                <a class="nav-link" id="meeting-minutes-tab" data-bs-toggle="tab" href="#meeting-minutes" role="tab">Meeting Minutes</a>
              </li>
            </ul>
            
            <!-- Tab Content -->
            <div class="tab-content mt-3" id="serviceTabContent">
            
              <!-- New Meeting Tab -->
              <div class="tab-pane fade show active" id="new-meeting" role="tabpanel">
                <form id="newMeetingForm" autocomplete="off">
                  <div class="row">
                    <!-- Meeting Title -->
                    <div class="col-lg-6 col-md-6 mb-3">
                      <label for="meetingTitle" class="form-label">Meeting Title</label>
                      <input type="text" class="form-control" id="meetingTitle" name="meeting_title" placeholder="Enter meeting title">
                    </div>
                    <!-- Meeting Date -->
                    <div class="col-lg-6 col-md-6 mb-3">
                      <label for="meetingDate" class="form-label">Meeting Date</label>
                      <input type="date" class="form-control" id="meetingDate" name="meeting_date">
                    </div>
                  </div>
            
                  <div class="row">
                    <!-- Meeting Time -->
                    <div class="col-lg-6 col-md-6 mb-3">
                      <label for="meetingTime" class="form-label">Meeting Time</label>
                      <input type="time" class="form-control" id="meetingTime" name="meeting_time">
                    </div>
                    <!-- Meeting Place -->
                    <div class="col-lg-6 col-md-6 mb-3">
                      <label for="meetingPlace" class="form-label">Meeting Place</label>
                      <input type="text" class="form-control" id="meetingPlace" name="meeting_place" placeholder="Enter meeting place">
                    </div>
                  </div>
            
                  <div class="row">
                    <!-- Meeting Description -->
                    <div class="col-lg-12 col-md-12 mb-3">
                      <label for="meetingDescription" class="form-label">Meeting Description</label>
                      <textarea class="form-control" id="meetingDescription" name="meeting_description" rows="3" placeholder="Enter meeting description"></textarea>
                    </div>
                  </div>
            
                  <div class="row">
                    <!-- Submit Button -->
                    <div class="col-lg-6 col-md-6 mb-3">
                      <button type="submit" class="btn btn-primary w-100">Create Meeting</button>
                    </div>
                  </div>
                </form>
              </div>
            
              <!-- Meeting Agenda Tab -->
              <div class="tab-pane fade" id="meeting-agenda" role="tabpanel">
                <form id="meetingAgendaForm" autocomplete="off">
                  <div class="row">
                    <!-- Select Meeting -->
                    <div class="col-lg-12 col-md-12 mb-3">
                      <label for="selectMeetingAgenda" class="form-label">Select Meeting</label>
                      <select class="form-select" id="selectMeetingAgenda" name="meeting_id">
                        <option value="">Select Meeting</option>
                        <!-- Meetings loaded dynamically -->
                      </select>
                    </div>
                  </div>
            
                  <div class="row">
                    <!-- Agenda Title -->
                    <div class="col-lg-6 col-md-6 mb-3">
                      <label for="agendaTitle" class="form-label">Agenda Title</label>
                      <input type="text" class="form-control" id="agendaTitle" name="title" placeholder="Enter agenda title">
                    </div>
                    
                    <!-- Participants -->
                    <div class="col-lg-6 col-md-6 mb-3">
                      <label for="participants" class="form-label">Participants</label>
                      <input type="text" class="form-control" id="participants" name="participants" placeholder="Enter participants">
                    </div>
                  </div>
            
                  <div class="row">
                    <!-- From Time -->
                    <div class="col-lg-6 col-md-6 mb-3">
                      <label for="fromTime" class="form-label">From Time</label>
                      <input type="time" class="form-control" id="fromTime" name="from_time">
                    </div>
                    <!-- To Time -->
                    <div class="col-lg-6 col-md-6 mb-3">
                      <label for="toTime" class="form-label">To Time</label>
                      <input type="time" class="form-control" id="toTime" name="to_time">
                    </div>
                  </div>
            
                  <div class="row">
                  <!-- Agenda Description -->
                    <div class="col-lg-12 col-md-12 mb-3">
                      <label for="agendaDescription" class="form-label">Agenda Description</label>
                      <textarea class="form-control" id="agendaDescription" name="description" rows="3" placeholder="Enter agenda description"></textarea>
                    </div>
                  </div>
            
                  <div class="row">
                    <!-- Submit Button -->
                    <div class="col-lg-6 col-md-6 mb-3">
                      <button type="submit" class="btn btn-primary w-100">Save Agenda</button>
                    </div>
                  </div>
                </form>
              </div>
            
              <!-- Additional Notes Tab -->
              <div class="tab-pane fade" id="additional-notes" role="tabpanel">
                <form id="additionalNotesForm" autocomplete="off">
                  <div class="row">
                    <!-- Select Meeting -->
                    <div class="col-lg-12 col-md-12 mb-3">
                      <label for="selectMeetingNotes" class="form-label">Select Meeting</label>
                      <select class="form-select" id="selectMeetingNotes" name="meeting_id">
                        <option value="">Select Meeting</option>
                        <!-- Meetings loaded dynamically -->
                      </select>
                    </div>
                  </div>
            
                  <div class="row">
                    <!-- Notes Text -->
                    <div class="col-lg-12 col-md-12 mb-3">
                      <label for="notesText" class="form-label">Additional Notes</label>
                      <textarea class="form-control" id="notesText" name="note_text" rows="3" placeholder="Enter additional notes"></textarea>
                    </div>
                  </div>
            
                  <div class="row">
                    <!-- Submit Button -->
                    <div class="col-lg-6 col-md-6 mb-3">
                      <button type="submit" class="btn btn-primary w-100">Save Notes</button>
                    </div>
                  </div>
                </form>
              </div>
            
              <!-- Meeting Minutes Tab -->
              <div class="tab-pane fade" id="meeting-minutes" role="tabpanel">
                <form id="meetingMinutesForm" autocomplete="off">
                  <div class="row">
                    <!-- Select Meeting -->
                    <div class="col-lg-12 col-md-12 mb-3">
                      <label for="selectMeetingMinutes" class="form-label">Select Meeting</label>
                      <select class="form-select" id="selectMeetingMinutes" name="meeting_id">
                        <option value="">Select Meeting</option>
                        <!-- Meetings loaded dynamically -->
                      </select>
                    </div>
                  </div>
            
                  <div class="row">
                    <!-- Minutes Text -->
                    <div class="col-lg-12 col-md-12 mb-3">
                      <label for="minutesText" class="form-label">Meeting Minutes</label>
                      <textarea class="form-control" id="minutesText" name="minutes_text" rows="3" placeholder="Enter meeting minutes"></textarea>
                    </div>
                  </div>
            
                  <div class="row">
                    <!-- Submit Button -->
                    <div class="col-lg-6 col-md-6 mb-3">
                      <button type="submit" class="btn btn-primary w-100">Save Minutes</button>
                    </div>
                  </div>
                </form>
              </div>
            
            </div>
             <div id="responseMessage"></div>
            </div> <!-- End tab-content -->
           
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
    function submitForm(formId, url) {
        const formData = $(formId).serialize();
    
        // Show "Sending Request" message before sending the request
        $('#responseMessage').html('<div class="response-message mb-2"><i class="fas fa-spinner fa-spin icon"></i> Sending Request...</div>');
    
        $.ajax({
            type: 'POST',
            url: url,
            data: formData,
            dataType: 'json',
            success: function (response) {
                let messageHtml = '';
                if (response.success) {
                    messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i>' + response.message + '</div>';
                    // Call the function to load Sunday services after success
                    loadMeetings();
    
                    // Display the success message
                    $('#responseMessage').html(messageHtml);
    
                    // Hide the success message after 2 seconds and reset the form
                    setTimeout(function () {
                        $('#responseMessage').empty();  // Clear the success message
                        $(formId)[0].reset();  // Reset the form
                        // Re-initialize custom form elements (like select or textarea)
                        $(formId).find('select, textarea').trigger('change');  // If needed
                    }, 2000);
                } else {
                    messageHtml = '<div class="response-message error"><i class="fas fa-times-circle icon"></i>' + response.message + '</div>';
                    $('#responseMessage').html(messageHtml);
                    // Hide the error message after 5 seconds
                    setTimeout(function () {
                        $('#responseMessage').empty();
                    }, 5000);
                }
            },
            error: function (xhr, status, error) {
                let errorMessage = '<div class="response-message error"><i class="fas fa-times-circle icon"></i>An error occurred: ' + error + '</div>';
                $('#responseMessage').html(errorMessage);
                // Hide the error message after 5 seconds
                setTimeout(function () {
                    $('#responseMessage').empty();
                }, 5000);
            }
        });
    }

    const forms = [
      { id: '#newMeetingForm', url: '../api/records/record_meeting' },
      { id: '#meetingAgendaForm', url: '../api/records/set_meeting_agenda' },
      { id: '#additionalNotesForm', url: '../api/records/add_meeting_notes' },
      { id: '#meetingMinutesForm', url: '../api/records/record_meeting_minutes' }
    ];

    
    // Attach event listeners to each form
    forms.forEach(function (form) {
      $(form.id).on('submit', function (event) {
        event.preventDefault();
        submitForm(form.id, form.url);
      });
    });

function loadMeetings() {
  $.ajax({
    type: 'GET',
    url: '../api/data/meetings?limit=all',  
    dataType: 'json',
    success: function (response) {
      if (response.success) {
        let options = '<option value="">Select Meeting</option>';
        $.each(response.data, function (index, meeting) {
          options += `<option value="${meeting.meeting_id}">${meeting.meeting_date} - ${meeting.meeting_title}</option>`;
        });
        $('select[name="meeting_id"]').html(options); // Populate the select options
      } else {
        console.error('Error loading Meetings:', response.message);
      }
    },
    error: function (xhr, status, error) {
      console.error('AJAX error loading Meetings:', error);
    }
  });
}


    // Call the function to load Sunday services on page load
    loadMeetings();

  });
  
</script>

</body>

</html>
