<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
check_session('head_parish_admin_id', '../head-parish/sign-in');
?>

<!doctype html>
<html lang="en">
<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Sunday Service Details');
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
            <h5 class="card-title fw-semibold mb-4">Sunday Service Details</h5>

            <!-- Tabs Navigation -->
            <ul class="nav nav-tabs" id="serviceTabs" role="tablist">
              <li class="nav-item" role="presentation">
                <a class="nav-link active" id="service-date-tab" data-bs-toggle="tab" href="#service-date" role="tab">Main</a>
              </li>
                <li class="nav-item" role="presentation">
                <a class="nav-link" id="service-times-tab" data-bs-toggle="tab" href="#service-times" role="tab">Service Times</a>
              </li>
              <li class="nav-item" role="presentation">
                <a class="nav-link" id="service-scriptures-tab" data-bs-toggle="tab" href="#service-scriptures" role="tab">Scriptures</a>
              </li>
              <li class="nav-item" role="presentation">
                <a class="nav-link" id="service-songs-tab" data-bs-toggle="tab" href="#service-songs" role="tab">Service Songs</a>
              </li>
              <li class="nav-item" role="presentation">
                <a class="nav-link" id="offerings-tab" data-bs-toggle="tab" href="#offerings" role="tab">Offerings</a>
              </li>
              <li class="nav-item" role="presentation">
                <a class="nav-link" id="service-leader-tab" data-bs-toggle="tab" href="#service-leader" role="tab">Leader</a>
              </li>
              <li class="nav-item" role="presentation">
                <a class="nav-link" id="preacher-tab" data-bs-toggle="tab" href="#preacher" role="tab">Preacher</a>
              </li>
              <li class="nav-item" role="presentation">
                <a class="nav-link" id="service-elder-tab" data-bs-toggle="tab" href="#service-elder" role="tab">Elders</a>
              </li>
              <li class="nav-item" role="presentation">
                <a class="nav-link" id="choirs-tab" data-bs-toggle="tab" href="#choirs" role="tab">Choirs</a>
              </li>

            </ul>

            <!-- Tab Content -->
            <div class="tab-content mt-3" id="serviceTabContent">

            <!-- Service Date Tab -->
            <div class="tab-pane fade show active" id="service-date" role="tabpanel">
              <form id="serviceDateForm" autocomplete="off">
                <div class="row">
                  <!-- Service Date -->
                  <div class="col-lg-6 col-md-6 mb-3">
                    <label for="serviceDate" class="form-label">Service Date</label>
                    <input type="date" class="form-control" id="serviceDate" name="service_date">
                  </div>
            
                  <!-- Service Color -->
                  <div class="col-lg-6 col-md-6 mb-3">
                    <label for="serviceColor" class="form-label">Service Color</label>
                    <select class="form-select" id="serviceColor" name="service_color">
                      <option value="">Select Color</option>
                      <!-- Page options dynamically loaded -->
                    </select>
                  </div>
                </div>
            
                <!-- Large and Small Liturgy Pages -->
                <div class="row">
                  <div class="col-lg-6 col-md-6 mb-3">
                    <label for="smallLiturgyPage" class="form-label">Small Liturgy Page Number</label>
                    <select class="form-select" id="smallLiturgyPage" name="small_liturgy_page_number">
                      <option value="">Select Page</option>
                      <!-- Page options dynamically loaded -->
                    </select>
                  </div>
                  <div class="col-lg-6 col-md-6 mb-3">
                    <label for="largeLiturgyPage" class="form-label">Large Liturgy Page Number</label>
                    <select class="form-select" id="largeLiturgyPage" name="large_liturgy_page_number">
                      <option value="">Select Page</option>
                      <!-- Page options dynamically loaded -->
                    </select>
                  </div>
                </div>
            
                <!-- Large and Small Antiphony Pages -->
                <div class="row">
                  <div class="col-lg-6 col-md-6 mb-3">
                    <label for="smallAntiphonyPage" class="form-label">Small Antiphony Page Number</label>
                    <select class="form-select" id="smallAntiphonyPage" name="small_antiphony_page_number">
                      <option value="">Select Page</option>
                      <!-- Page options dynamically loaded -->
                    </select>
                  </div>
                  <div class="col-lg-6 col-md-6 mb-3">
                    <label for="largeAntiphonyPage" class="form-label">Large Antiphony Page Number</label>
                    <select class="form-select" id="largeAntiphonyPage" name="large_antiphony_page_number">
                      <option value="">Select Page</option>
                      <!-- Page options dynamically loaded -->
                    </select>
                  </div>
                </div>
            
                <!-- Large and Small Praise Books -->
                <div class="row">
                  <div class="col-lg-6 col-md-6 mb-3">
                    <label for="smallPraisePage" class="form-label">Small Praise Books</label>
                    <select class="form-select" id="smallPraisePage" name="small_praise_page_number">
                      <option value="">Select Page</option>
                      <!-- Page options dynamically loaded -->
                    </select>
                  </div>
                <div class="col-lg-6 col-md-6 mb-3">
                    <label for="largePraisePage" class="form-label">Large Praise Books</label>
                    <select class="form-select" id="largePraisePage" name="large_praise_page_number">
                      <option value="">Select Page</option>
                      <!-- Page options dynamically loaded -->
                    </select>
                  </div>
                </div>
                <!-- Base Scripture -->
                <div class="mb-3">
                  <label for="baseScripture" class="form-label">Base Scripture (Neno Kuu)</label>
                  <textarea class="form-control" id="baseScripture" name="base_scripture_text" rows="3" placeholder="Enter base scripture"></textarea>
                </div>
                <!-- Submit Button -->
                <div class="row">
                  <div class="col-lg-6 col-md-6 mb-3">
                    <button type="submit" class="btn btn-primary w-100">Save Service</button>
                  </div>
                </div>
              </form>
            </div>

            <!-- Service Times Tab -->
            <div class="tab-pane fade" id="service-times" role="tabpanel">
              <form id="serviceTimesForm" autocomplete="off">
                <div class="row">
                  <!-- Sunday Service Select -->
                  <div class="col-lg-6 col-md-6 mb-3">
                    <label for="serviceSelectTimes" class="form-label">Select Sunday Service</label>
                    <select class="form-select" id="serviceSelectTimes" name="service_id">
                      <option value="">Select Sunday Service</option>
                      <!-- Sunday services loaded dynamically -->
                    </select>
                  </div>
            
                  <!-- Service Select -->
                  <div class="col-lg-6 col-md-6 mb-3">
                    <label for="serviceNumberSelectTimes" class="form-label">Select Service Number</label>
                    <select class="form-select" id="serviceNumberSelectTimes" name="service_number">
                      <option value="">Select Service Number</option>
                      <!-- Service numbers loaded dynamically -->
                    </select>
                  </div>
                </div>
            
                <div class="row">
                  <!-- Service Time Input -->
                  <div class="col-lg-6 col-md-6 mb-3">
                    <label for="serviceTimeInput" class="form-label">Set Service Time</label>
                    <input type="time" class="form-control" id="serviceTimeInput" name="service_time">
                  </div>
                </div>
            
                <!-- Submit Button in a Row -->
                <div class="row">
                  <div class="col-lg-6 col-md-6 mb-3">
                    <button type="submit" class="btn btn-primary w-100">Set Service Time</button>
                  </div>
                </div>
              </form>
            </div>

            <!-- Service Scriptures Form -->
            <div class="tab-pane fade" id="service-scriptures" role="tabpanel">
              <form id="serviceScripturesForm" autocomplete="off">
                <div class="row">
                  <!-- Sunday Service Select -->
                  <div class="col-lg-6 col-md-6 mb-3">
                    <label for="serviceSelectScripture" class="form-label">Select Sunday Service</label>
                    <select class="form-select" id="serviceSelectScripture" name="service_id">
                      <option value="">Select Sunday Service</option>
                      <!-- Sunday services loaded dynamically -->
                    </select>
                  </div>
                  
                  <!-- Book Select -->
                  <div class="col-lg-6 col-md-6 mb-3">
                    <label for="bookSelect" class="form-label">Select Book</label>
                    <select class="form-select" id="bookSelect" name="book_id">
                      <option value="">Select Book</option>
                      <!-- Bible books loaded dynamically -->
                    </select>
                  </div>
                </div>
            
                <div class="row">
                  <!-- Chapter Number -->
                  <div class="col-lg-6 col-md-6 mb-3">
                    <label for="chapterNumber" class="form-label">Chapter Number</label>
                    <select class="form-select" id="chapterNumber" name="chapter">
                      <option value="">Select Chapter</option>
                      <!-- Chapters loaded dynamically based on selected book -->
                    </select>
                  </div>
            
                  <!-- Starting Verse Number -->
                  <div class="col-lg-6 col-md-6 mb-3">
                    <label for="startingVerse" class="form-label">Starting Verse Number</label>
                    <select class="form-select" id="startingVerse" name="starting_verse_number">
                      <option value="">Select Starting Verse</option>
                      <!-- Starting verses loaded dynamically based on selected chapter -->
                    </select>
                  </div>
                </div>
            
                <div class="row">
                  <!-- Ending Verse Number (Optional) -->
                  <div class="col-lg-6 col-md-6 mb-3">
                    <label for="endingVerse" class="form-label">Ending Verse Number</label>
                    <select class="form-select" id="endingVerse" name="ending_verse_number">
                      <option value="">Select Ending Verse</option>
                      <!-- Ending verses loaded dynamically based on selected chapter -->
                    </select>
                  </div>
                </div>
            
                <!-- Submit Button -->
                <div class="row">
                  <div class="col-lg-6 col-md-6 mb-3">
                    <button type="submit" class="btn btn-primary w-100">Save Scripture</button>
                  </div>
                </div>
              </form>
            </div>


    
                <!-- Inside the Service Songs Tab -->
                <div class="tab-pane fade" id="service-songs" role="tabpanel">
                  <form id="serviceSongsForm" autocomplete="off">
                    <div class="row">
                      <!-- Sunday Service Select -->
                      <div class="col-lg-6 col-md-6 mb-3">
                        <label for="serviceSelect" class="form-label">Select Sunday Service</label>
                        <select class="form-select" id="serviceSelect" name="service_id">
                          <option value="">Select Sunday Service</option>
                          <!-- Sunday services loaded dynamically -->
                        </select>
                      </div>
                
                      <!-- Song Select -->
                      <div class="col-lg-6 col-md-6 mb-3">
                        <label for="songSelect" class="form-label">Select Song</label>
                        <select class="form-select" id="songSelect" name="song_id">
                          <option value="">Select Song</option>
                          <!-- Songs loaded dynamically -->
                        </select>
                      </div>
                    </div>
                
                    <!-- Submit Button in a Row -->
                    <div class="row">
                      <div class="col-lg-6 col-md-6 mb-3">
                        <button type="submit" class="btn btn-primary w-100">Add Song to Service</button>
                      </div>
                    </div>
                  </form>
                </div>

                
                <!-- Inside the Offerings Tab -->
                <div class="tab-pane fade" id="offerings" role="tabpanel">
                  <form id="offeringsForm" autocomplete="off">
                    <div class="row">
                      <!-- Sunday Service Select -->
                      <div class="col-lg-6 col-md-6 mb-3">
                        <label for="serviceSelectOffering" class="form-label">Select Sunday Service</label>
                        <select class="form-select" id="serviceSelectOffering" name="service_id">
                          <option value="">Select Sunday Service</option>
                          <!-- Sunday services loaded dynamically -->
                        </select>
                      </div>
                
                      <!-- Revenue Stream Select -->
                      <div class="col-lg-6 col-md-6 mb-3">
                        <label for="revenueStream" class="form-label">Revenue Stream</label>
                        <select class="form-select" id="revenueStream" name="revenue_stream_id">
                          <option value="">Select Revenue Stream</option>
                          <!-- Revenue streams loaded dynamically -->
                        </select>
                      </div>
                    </div>
                
                    <!-- Submit Button in a Row -->
                    <div class="row">
                      <div class="col-lg-6 col-md-6 mb-3">
                        <button type="submit" class="btn btn-primary w-100">Add Offering</button>
                      </div>
                    </div>
                  </form>
                </div>

                
                <!-- Inside the Choirs Tab -->
                <div class="tab-pane fade" id="choirs" role="tabpanel">
                  <form id="choirsForm" autocomplete="off">
                    <div class="row">
                      <!-- Sunday Service Select -->
                      <div class="col-lg-6 col-md-6 mb-3">
                        <label for="serviceSelectChoir" class="form-label">Select Sunday Service</label>
                        <select class="form-select" id="serviceSelectChoir" name="service_id">
                          <option value="">Select Sunday Service</option>
                          <!-- Sunday services loaded dynamically -->
                        </select>
                      </div>
                
                      <!-- Service Select -->
                      <div class="col-lg-6 col-md-6 mb-3">
                        <label for="serviceNumberSelectChoir" class="form-label">Select Service</label>
                        <select class="form-select" id="serviceNumberSelectChoir" name="service_number">
                          <option value="">Select Service</option>
                          <!-- Sunday services loaded dynamically -->
                        </select>
                      </div>
                    </div>
                
                    <div class="row">
                      <!-- Choir Select -->
                      <div class="col-lg-6 col-md-6 mb-3">
                        <label for="choirSelect" class="form-label">Select Choir</label>
                        <select class="form-select" id="choirSelect" name="choir_id">
                          <option value="">Select Choir</option>
                          <!-- Choirs loaded dynamically -->
                        </select>
                      </div>
                    </div>
                
                    <!-- Submit Button in a Row -->
                    <div class="row">
                      <div class="col-lg-6 col-md-6 mb-3">
                        <button type="submit" class="btn btn-primary w-100">Add Choir to Service</button>
                      </div>
                    </div>
                  </form>
                </div>


                <!-- Service Leader Tab -->
                <div class="tab-pane fade" id="service-leader" role="tabpanel">
                  <form id="serviceLeaderForm" autocomplete="off">
                    <div class="row">
                      <!-- Sunday Service Select -->
                      <div class="col-lg-6 col-md-6 mb-3">
                        <label for="serviceSelectLeader" class="form-label">Select Sunday Service</label>
                        <select class="form-select" id="serviceSelectLeader" name="service_id">
                          <option value="">Select Sunday Service</option>
                          <!-- Sunday services loaded dynamically -->
                        </select>
                      </div>
                
                      <!-- Service Select -->
                      <div class="col-lg-6 col-md-6 mb-3">
                        <label for="serviceNumberSelectLeader" class="form-label">Select Service</label>
                        <select class="form-select" id="serviceNumberSelectLeader" name="service_number">
                          <option value="">Select Service</option>
                          <!-- Sunday services loaded dynamically -->
                        </select>
                      </div>
                    </div>
                
                    <div class="row">
                      <!-- Service Leader Select -->
                      <div class="col-lg-6 col-md-6 mb-3">
                        <label for="serviceLeaderSelect" class="form-label">Select Service Leader</label>
                        <select class="form-select" id="serviceLeaderSelect" name="leader_id">
                          <option value="">Select Leader</option>
                          <!-- Leaders loaded dynamically -->
                        </select>
                      </div>
                    </div>
                
                    <!-- Submit Button in a Row -->
                    <div class="row">
                      <div class="col-lg-6 col-md-6 mb-3">
                        <button type="submit" class="btn btn-primary w-100">Assign Leader</button>
                      </div>
                    </div>
                  </form>
                </div>


                <!-- Preacher Tab -->
                <div class="tab-pane fade" id="preacher" role="tabpanel">
                  <form id="servicePreacherForm" autocomplete="off">
                    <div class="row">
                      <!-- Sunday Service Select -->
                      <div class="col-lg-6 col-md-6 mb-3">
                        <label for="serviceSelectPreacher" class="form-label">Select Sunday Service</label>
                        <select class="form-select" id="serviceSelectPreacher" name="service_id">
                          <option value="">Select Sunday Service</option>
                          <!-- Sunday services loaded dynamically -->
                        </select>
                      </div>
                
                      <!-- Service Select -->
                      <div class="col-lg-6 col-md-6 mb-3">
                        <label for="serviceNumberSelectPreacher" class="form-label">Select Service</label>
                        <select class="form-select" id="serviceNumberSelectPreacher" name="service_number">
                          <option value="">Select Service</option>
                          <!-- Sunday services loaded dynamically -->
                        </select>
                      </div>
                    </div>
                
                    <div class="row">
                      <!-- Preacher Select -->
                      <div class="col-lg-6 col-md-6 mb-3">
                        <label for="servicePreacherSelect" class="form-label">Select Preacher</label>
                        <select class="form-select" id="servicePreacherSelect" name="preacher_id">
                          <option value="">Select Preacher</option>
                          <!-- Preachers loaded dynamically -->
                        </select>
                      </div>
                    </div>
                
                    <!-- Submit Button in a Row -->
                    <div class="row">
                      <div class="col-lg-6 col-md-6 mb-3">
                        <button type="submit" class="btn btn-primary w-100">Assign Preacher</button>
                      </div>
                    </div>
                  </form>
                </div>

                
                <!-- Service Elders Tab -->
                <div class="tab-pane fade" id="service-elder" role="tabpanel">
                  <form id="serviceElderForm" autocomplete="off">
                    <div class="row">
                      <!-- Sunday Service Select -->
                      <div class="col-lg-6 col-md-6 mb-3">
                        <label for="serviceSelectElder" class="form-label">Select Sunday Service</label>
                        <select class="form-select" id="serviceSelectElder" name="service_id">
                          <option value="">Select Sunday Service</option>
                          <!-- Sunday services loaded dynamically -->
                        </select>
                      </div>
                
                      <!-- Service Select -->
                      <div class="col-lg-6 col-md-6 mb-3">
                        <label for="serviceNumberSelectElder" class="form-label">Select Service</label>
                        <select class="form-select" id="serviceNumberSelectElder" name="service_number">
                          <option value="">Select Service</option>
                          <!-- Sunday services loaded dynamically -->
                        </select>
                      </div>
                    </div>
                
                    <div class="row">
                      <!-- Service Elder Select -->
                      <div class="col-lg-6 col-md-6 mb-3">
                        <label for="serviceElderSelect" class="form-label">Select Elder</label>
                        <select class="form-select" id="serviceElderSelect" name="elder_id">
                          <option value="">Select Elder</option>
                          <!-- Leaders loaded dynamically -->
                        </select>
                      </div>
                    </div>
                
                    <!-- Submit Button in a Row -->
                    <div class="row">
                      <div class="col-lg-6 col-md-6 mb-3">
                        <button type="submit" class="btn btn-primary w-100">Assign Elder</button>
                      </div>
                    </div>
                  </form>
                </div>
            </div> <!-- End tab-content -->
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
                loadSundayServices();

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

    // Form IDs and their respective API URLs
    const forms = [
      { id: '#serviceDateForm', url: '../api/records/record_sunday_service' },
      { id: '#serviceScripturesForm', url: '../api/records/set_sunday_service_scriptures' },
      { id: '#serviceSongsForm', url: '../api/records/set_sunday_service_songs' },
      { id: '#offeringsForm', url: '../api/records/set_sunday_service_offerings' },
      { id: '#choirsForm', url: '../api/records/set_sunday_service_choirs' },
      { id: '#serviceLeaderForm', url: '../api/records/set_sunday_service_leader' },
      { id: '#servicePreacherForm', url: '../api/records/set_sunday_service_preacher' },
      { id: '#serviceElderForm', url: '../api/records/set_sunday_service_elders' },
      { id: '#serviceTimesForm', url: '../api/records/set_sunday_service_times' }
    ];
    
    // Attach event listeners to each form
    forms.forEach(function (form) {
      $(form.id).on('submit', function (event) {
        event.preventDefault();
        submitForm(form.id, form.url);
      });
    });

    // Function to load page options dynamically
    function loadPageOptions(bookType, size, selectId) {
      $.ajax({
        type: 'GET',
        url: '../api/data/books', 
        data: { book_type: bookType, size: size },
        dataType: 'json',
        success: function (response) {
          if (response.success) {
            let options = '<option value="">Select Page</option>';
            $.each(response.pages, function (index, page) {
              options += `<option value="${page}">${page}</option>`;
            });
            $(selectId).html(options);
          } else {
            console.error(`Error loading pages for ${bookType} (${size}): ${response.message}`);
          }
        },
        error: function (xhr, status, error) {
          console.error(`AJAX error loading pages for ${bookType} (${size}):`, error);
        }
      });
    }

    // Load Church Colors
    function loadChurchColors() {
      $.ajax({
        type: 'GET',
        url: '../api/data/colors.php', // Adjust path as needed
        dataType: 'json',
        success: function (response) {
          if (response.success) {
            let options = '<option value="">Select Color</option>';
            $.each(response.data, function (index, color) {
              options += `<option value="${color.color_id}" style="background-color: ${color.color_code}; color: #fff;">${color.color_name}</option>`;
            });
            $('#serviceColor').html(options);
          } else {
            console.error('Error loading church colors:', response.message);
          }
        },
        error: function (xhr, status, error) {
          console.error('AJAX error loading church colors:', error);
        }
      });
    }

    // Call the function to load colors
    loadChurchColors();
    

    function loadSundayServices() {
      $.ajax({
        type: 'GET',
        url: '../api/data/sunday_services?limit=all',  
        dataType: 'json',
        success: function (response) {
          if (response.success) {
            let options = '<option value="">Select Sunday Service</option>';
            $.each(response.data, function (index, service) {
              options += `<option value="${service.service_id}">${service.service_date} - ${service.base_scripture_text}</option>`;
            });
            $('select[name="service_id"]').html(options); // Populate the service select in all forms
          } else {
            console.error('Error loading Sunday services:', response.message);
          }
        },
        error: function (xhr, status, error) {
          console.error('AJAX error loading Sunday services:', error);
        }
      });
    }

    // Call the function to load Sunday services on page load
    loadSundayServices();
    

    function loadSundayServicesNumbers() {
      $.ajax({
        type: 'GET',
        url: '../api/data/head_parish_services',  
        dataType: 'json',
        success: function (response) {
          if (response.success) {
            let options = '<option value="">Select Service</option>';
            $.each(response.data, function (index, service) {
               options += '<option value="' + service.service_id + '">' + service.service + '</option>';
            });
            $('select[name="service_number"]').html(options); // Populate the service select in all forms
          } else {
            console.error('Error loading Sunday services:', response.message);
          }
        },
        error: function (xhr, status, error) {
          console.error('AJAX error loading Sunday services:', error);
        }
      });
    }
    // Call the function to load Sunday services numbers on page load
    loadSundayServicesNumbers();
    
    // Load revenue streams based on context
    function loadRevenueStreams() {
      $.ajax({
        type: 'GET',
        url: '../api/data/head_parish_revenue_streams?limit=all', 
        data: { target: 'head-parish' },
        dataType: 'json',
        success: function (response) {
          let options = '<option value="">Select Revenue Stream</option>';
          $.each(response.data, function (index, stream) {
            options += '<option value="' + stream.revenue_stream_id + '">' + stream.revenue_stream_name + '</option>';
          });

            $('#revenueStream').html(options);
        },
        error: function (xhr, status, error) {
          console.log('Error loading revenue streams:', error);
        }
      });
    }
    loadRevenueStreams();
    
    // Load pages for Liturgy, Antiphony, and Praise books
    loadPageOptions('Liturgy', 'Large', '#largeLiturgyPage');
    loadPageOptions('Liturgy', 'Small', '#smallLiturgyPage');
    loadPageOptions('Antiphony', 'Large', '#largeAntiphonyPage');
    loadPageOptions('Antiphony', 'Small', '#smallAntiphonyPage');
    loadPageOptions('Praise', 'Large', '#largePraisePage');
    loadPageOptions('Praise', 'Small', '#smallPraisePage');

    // Additional dynamic data (for other tabs like Songs, Leaders, etc.)
    function loadOptions(url, selectId) {
      $.ajax({
        type: 'GET',
        url: url,
        dataType: 'json',
        success: function (response) {
          var options = '';
          $.each(response.data, function (index, item) {
            options += '<option value="' + item.id + '">' + item.name + '</option>';
          });
          $(selectId).html(options);
        },
        error: function (xhr, status, error) {
          console.log('Error loading options:', error);
        }
      });
    }

    // Load songs, revenue streams, leaders, and choirs
    loadOptions('/api/data/praise_songs?limit=all', '#songSelect');
    loadOptions('/api/data/church_leaders?limit=all', '#serviceLeaderSelect');
    loadOptions('/api/data/church_leaders?limit=all', '#servicePreacherSelect');
    loadOptions('/api/data/church_leaders?limit=all', '#serviceElderSelect');
    loadOptions('../api/data/church_choirs?limit=all', '#choirSelect');
    
    
// Function to load Bible books dynamically
    function loadBooks() {
      $.ajax({
        type: 'GET',
        url: '../api/data/bible', // API endpoint to fetch books
        dataType: 'json',
        success: function (response) {
          if (response.success) {
            let options = '<option value="">Select Book</option>';
            $.each(response.data, function (index, book) {
              options += `<option value="${book.book_id}">${book.book_name_sw}</option>`; // Use the appropriate book name field
            });
            $('#bookSelect').html(options);
          } else {
            console.error('Error loading Bible books:', response.message);
          }
        },
        error: function (xhr, status, error) {
          console.error('AJAX error loading Bible books:', error);
        }
      });
    }

    // Function to load chapters based on the selected book
    function loadChapters(bookId) {
      $.ajax({
        type: 'GET',
        url: '../api/data/chapters', // API endpoint to fetch chapters for a book
        data: { book_id: bookId },
        dataType: 'json',
        success: function (response) {
          if (response.success) {
            let options = '<option value="">Select Chapter</option>';
            $.each(response.chapter_range, function (index, chapter) {
              options += `<option value="${chapter}">${chapter}</option>`;
            });
            $('#chapterNumber').html(options);
            $('#startingVerse').html('<option value="">Select Starting Verse</option>');
            $('#endingVerse').html('<option value="">Select Ending Verse</option>');
          } else {
            console.error('Error loading chapters:', response.message);
          }
        },
        error: function (xhr, status, error) {
          console.error('AJAX error loading chapters:', error);
        }
      });
    }

    // Function to load verses based on the selected chapter
    function loadVerses(bookId, chapterNumber) {
      $.ajax({
        type: 'GET',
        url: '../api/data/verses', // API endpoint to fetch verses for a chapter
        data: { book_id: bookId, chapter: chapterNumber },
        dataType: 'json',
        success: function (response) {
          if (response.success) {
            let optionsStart = '<option value="">Select Starting Verse</option>';
            let optionsEnd = '<option value="">Select Ending Verse</option>';
            $.each(response.verse_range, function (index, verse) {
              optionsStart += `<option value="${verse}">${verse}</option>`;
              optionsEnd += `<option value="${verse}">${verse}</option>`;
            });
            $('#startingVerse').html(optionsStart);
            $('#endingVerse').html(optionsEnd);
          } else {
            console.error('Error loading verses:', response.message);
          }
        },
        error: function (xhr, status, error) {
          console.error('AJAX error loading verses:', error);
        }
      });
    }

    // Event listener for when the book is selected
    $('#bookSelect').change(function () {
      let bookId = $(this).val();
      if (bookId) {
        loadChapters(bookId);  // Load chapters for the selected book
      } else {
        $('#chapterNumber').html('<option value="">Select Chapter</option>');
        $('#startingVerse').html('<option value="">Select Starting Verse</option>');
        $('#endingVerse').html('<option value="">Select Ending Verse</option>');
      }
    });

    // Event listener for when the chapter is selected
    $('#chapterNumber').change(function () {
      let bookId = $('#bookSelect').val();
      let chapterNumber = $(this).val();
      if (bookId && chapterNumber) {
        loadVerses(bookId, chapterNumber);  // Load verses for the selected chapter
      } else {
        $('#startingVerse').html('<option value="">Select Starting Verse</option>');
        $('#endingVerse').html('<option value="">Select Ending Verse</option>');
      }
    });

    // Initial load of Bible books
    loadBooks();
  });
  
</script>

</body>

</html>
