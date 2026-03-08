<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
check_session('head_parish_admin_id', '../head-parish/sign-in');
?>
<!doctype html>
<html lang="en">
<head>
  <?php
    // these files should load Bootstrap, jQuery, Select2 and any site CSS/JS you use
    require_once('components/header_files.php');
    render_header('Download Revenue Statement - Kanisa Langu');
    // today for date max on inputs
    $today = date('Y-m-d');
  ?>
  <style>
    /* small helper styles for response messages (keep consistent with your app) */
    .response-message { padding: .6rem 1rem; border-radius: .375rem; margin-top: .5rem; }
    .response-message.error { background-color: #ffe6e6; color: #a40000; border: 1px solid #f5c2c2; }
    .response-message.success { background-color: #e9f7ea; color: #0b6b25; border: 1px solid #bfe6bf; }
  </style>
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
          <h5 class="card-title fw-semibold mb-4">Download Revenue Statement</h5>

          <form id="incomeStatementForm" novalidate>

            <!-- Report Type -->
            <div class="mb-3">
              <label for="reportTypeSummarized" class="form-label">Select Report Type</label>
              <select class="form-select" id="reportTypeSummarized" name="reportTypeSummarized" aria-label="Select report type">
                <option value="annual">Annual</option>
                <option value="quarterly">Quarterly</option>
                <option value="custom">Custom Range</option>
              </select>
            </div>

            <!-- Year Selection -->
            <div class="mb-3" id="yearContainer">
              <label for="year" class="form-label">Select Year</label>
              <select class="form-select" id="year" name="year" aria-label="Select year"></select>
            </div>

            <!-- Quarter Selection (Hidden initially) -->
            <div class="mb-3" id="quarterContainer" style="display: none;">
              <label for="quarter" class="form-label">Select Quarter</label>
              <select class="form-select" id="quarter" name="quarter" aria-label="Select quarter">
                <option value="Q1">Q1 (Jan - Mar)</option>
                <option value="Q2">Q2 (Apr - Jun)</option>
                <option value="Q3">Q3 (Jul - Sep)</option>
                <option value="Q4">Q4 (Oct - Dec)</option>
              </select>
            </div>

            <!-- Custom Date Range -->
            <div id="customDateContainer" style="display:none;">
              <div class="mb-3">
                <label for="startDate" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="startDate" name="startDate" max="<?php echo $today; ?>">
              </div>

              <div class="mb-3">
                <label for="endDate" class="form-label">End Date</label>
                <input type="date" class="form-control" id="endDate" name="endDate" max="<?php echo $today; ?>">
              </div>
            </div>

            <!-- Submit Button -->
            <div class="mb-3 mt-4">
              <button type="submit" class="btn btn-primary">Download Report</button>
            </div>

            <div id="responseMessage" aria-live="polite"></div>
          </form>

        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once('components/footer_files.php') ?>

<script>
  $(document).ready(function () {

    // initialize Select2 where appropriate
    // ensure Select2 files are loaded via header_files.php or footer_files.php
    if ($.fn.select2) {
      $('#year, #reportTypeSummarized, #quarter').select2({ width: '100%' });
    }

    // populate years from 2024 to current year
    const currentYear = new Date().getFullYear();
    let yearOptions = '';
    for (let y = 2024; y <= currentYear; y++) {
      yearOptions += `<option value="${y}">${y}</option>`;
    }
    $('#year').html(yearOptions);

    // set today string for validation
    const today = new Date().toISOString().split('T')[0];

    // helper to show messages
    function showMessage(msg, type = 'error') {
      const cls = type === 'success' ? 'response-message success' : 'response-message error';
      $('#responseMessage').html(`<div class="${cls}">${msg}</div>`);
      // remove after 6 seconds
      setTimeout(() => { $('#responseMessage').fadeOut(300, function(){ $(this).html('').show(); }); }, 6000);
    }

    // toggle UI based on report type
    function updateUIForReportType(type) {
      $('#quarterContainer').hide();
      $('#customDateContainer').hide();
      $('#yearContainer').show();

      if (type === 'quarterly') {
        $('#quarterContainer').show();
      } else if (type === 'custom') {
        $('#customDateContainer').show();
        $('#yearContainer').hide();
      }
    }

    // initial UI state
    const initialType = $('#reportTypeSummarized').val();
    updateUIForReportType(initialType);

    // on change
    $('#reportTypeSummarized').on('change', function () {
      const t = $(this).val();
      updateUIForReportType(t);
      // when toggling, clear previous messages
      $('#responseMessage').html('');
      // reset Select2 focus to avoid trapped dropdowns
      if ($.fn.select2) { $('#year').trigger('change.select2'); }
    });

    // form submission
    $('#incomeStatementForm').on('submit', function (e) {
      e.preventDefault();

      const reportType = $('#reportTypeSummarized').val();
      let url = '/reports/download_revenue_statement.php';
      let query = [];

      if (reportType === 'annual') {
        const year = $('#year').val();
        if (!year) { showMessage('Please select a year.'); return; }
        query.push('report_type=annual');
        query.push('year=' + encodeURIComponent(year));
      } else if (reportType === 'quarterly') {
        const year = $('#year').val();
        const quarter = $('#quarter').val();
        if (!year) { showMessage('Please select a year.'); return; }
        if (!quarter) { showMessage('Please select a quarter.'); return; }
        query.push('report_type=quarterly');
        query.push('year=' + encodeURIComponent(year));
        query.push('quarter=' + encodeURIComponent(quarter));
      } else if (reportType === 'custom') {
        const start = $('#startDate').val();
        const end = $('#endDate').val();

        if (!start || !end) {
          showMessage('Please select both start and end dates.');
          return;
        }

        // basic ISO string comparison works since inputs are yyyy-mm-dd
        if (start > end) {
          showMessage('End date cannot be earlier than start date.');
          return;
        }

        if (start > today || end > today) {
          showMessage('Dates cannot be greater than today.');
          return;
        }

        const startYear = start.split('-')[0];
        const endYear = end.split('-')[0];
        if (startYear !== endYear) {
          showMessage('Start and end dates must be in the same year.');
          return;
        }

        query.push('report_type=custom');
        query.push('start_date=' + encodeURIComponent(start));
        query.push('end_date=' + encodeURIComponent(end));
        // pass year for server convenience
        query.push('year=' + encodeURIComponent(startYear));
      } else {
        showMessage('Invalid report type selected.');
        return;
      }

      // build final url and redirect
      const finalUrl = url + '?' + query.join('&');
      // a small UX step: show a success message before redirect so user sees action
      showMessage('Preparing report. You will be redirected shortly.', 'success');
      // short delay so message shows, then redirect
      setTimeout(() => { window.location.href = finalUrl; }, 350);
    });

    // optional: keep endDate min to startDate when user selects start
    $('#startDate').on('change', function () {
      const s = $(this).val();
      if (s) $('#endDate').attr('min', s);
      else $('#endDate').removeAttr('min');
    });

    // optional: keep startDate max to endDate when user selects end
    $('#endDate').on('change', function () {
      const e = $(this).val();
      if (e) $('#startDate').attr('max', e);
      else $('#startDate').attr('max', '<?php echo $today; ?>');
    });

  });
</script>

</body>
</html>
