<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
check_session('head_parish_admin_id', '../head-parish/sign-in');
?>
<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Set Benchmark for Church Attendance - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Set Benchmark for Church Attendance</h5>

            <!-- Information Row -->
            <div class="alert alert-info" role="alert">
              <strong>Note:</strong> The <strong>Benchmark</strong> is the standard measure of how real attendance compares to it. If no benchmark is set, the default value of 1000 will be used.
            </div>

            <!-- Display Current Benchmarks -->
            <div id="currentBenchmark" class="mb-4">
              <h6 class="fw-bold">Current Benchmarks</h6>
              <div class="alert alert-warning" role="alert" id="currentBenchmarkMessage">
                Loading current benchmarks...
              </div>
            </div>

            <!-- Form for Setting Benchmark -->
            <form id="setBenchmarkForm">
              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="adultBenchmark" class="form-label">Set Adult Benchmark</label>
                    <input type="number" class="form-control w-100" id="adultBenchmark" name="adultBenchmark" placeholder="Enter Adult Benchmark (default: 1000)" value="">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="childBenchmark" class="form-label">Set Child Benchmark</label>
                    <input type="number" class="form-control w-100" id="childBenchmark" name="childBenchmark" placeholder="Enter Child Benchmark (default: 500)" value="">
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <button type="submit" class="btn btn-primary">Set Benchmarks</button>
              </div>
              <div id="responseMessage"></div>
            </form>

          </div>
        </div>
      </div>
    </div>
  </div>

  <?php require_once('components/footer_files.php') ?>

  <script>
    $(document).ready(function () {
      // Fetch current benchmarks and display them
      function fetchCurrentBenchmark() {
        $.ajax({
          type: 'GET',
          url: '../api/data/get_attendance_benchmark.php', // Endpoint to get current benchmarks
          dataType: 'json',
          success: function (response) {
            if (response.success) {
              $('#currentBenchmarkMessage').html(`
                <strong>Adult Benchmark:</strong> ${response.adult_reading} <br>
                <strong>Child Benchmark:</strong> ${response.child_reading}
              `);
            } else {
              $('#currentBenchmarkMessage').html('No benchmarks have been set yet.');
            }
          },
          error: function (xhr, status, error) {
            $('#currentBenchmarkMessage').html('An error occurred while fetching the current benchmarks.');
          }
        });
      }

      // Load current benchmarks on page load
      fetchCurrentBenchmark();

      // Submit form to set benchmarks
      $('#setBenchmarkForm').on('submit', function (event) {
        event.preventDefault();
        $('#responseMessage').html('<div class="response-message"><i class="fas fa-spinner fa-spin icon"></i> Saving Benchmarks...</div>');
        
        var benchmarkData = {
          adultBenchmark: $('#adultBenchmark').val(),
          childBenchmark: $('#childBenchmark').val()
        };

        $.ajax({
          type: 'POST',
          url: '../api/records/set_attendance_benchmark.php', // Endpoint to save benchmarks
          data: benchmarkData,
          dataType: 'json',
          success: function (response) {
            if (response.success) {
              $('#responseMessage').html('<div class="response-message success"><i class="fas fa-check-circle icon"></i> Benchmarks set successfully!</div>');
              $('#setBenchmarkForm')[0].reset();
              // Fetch and update the current benchmarks after setting new values
              fetchCurrentBenchmark();
            } else {
              $('#responseMessage').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i>' + response.message + '</div>');
            }
          },
          error: function (xhr, status, error) {
            $('#responseMessage').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i>An error occurred: ' + error + '</div>');
          }
        });
      });
    });
  </script>
</body>

</html>
