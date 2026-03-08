<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
check_session('head_parish_admin_id', '../head-parish/sign-in');
?>
<!doctype html>
<html lang="en">
<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Download Revenue Breakdown - Kanisa Langu');
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
          <h5 class="card-title fw-semibold mb-4">Download Revenue Breakdown</h5>
          
          <form id="incomeStatementForm">

            <!-- Tab Navigation -->
            <ul class="nav nav-tabs" id="reportTab" role="tablist">
              <li class="nav-item" role="presentation">
                <a class="nav-link active" id="summarized-tab" data-bs-toggle="tab" href="#summarized" role="tab" aria-controls="summarized" aria-selected="true">Summarized</a>
              </li>
              <li class="nav-item" role="presentation">
                <a class="nav-link" id="detailed-tab" data-bs-toggle="tab" href="#detailed" role="tab" aria-controls="detailed" aria-selected="false">Detailed</a>
              </li>
            </ul>
            <div class="tab-content mt-3" id="reportTabContent">
              <div class="tab-pane fade show active" id="summarized" role="tabpanel" aria-labelledby="summarized-tab">
                <!-- Summarized Report Form -->
                <div class="mb-3">
                  <label for="reportTypeSummarized" class="form-label">Select Report Type</label>
                  <select class="form-select" id="reportTypeSummarized" name="reportTypeSummarized">
                    <option value="annual">Annual</option>
                    <option value="quarterly">Quarterly</option>
                  </select>
                </div>
              </div>
              <div class="tab-pane fade" id="detailed" role="tabpanel" aria-labelledby="detailed-tab">
                <!-- Detailed Report Form -->
                <div class="mb-3">
                  <label for="reportTypeDetailed" class="form-label">Select Report Type</label>
                  <select class="form-select" id="reportTypeDetailed" name="reportTypeDetailed">
                    <option value="annual">Annual</option>
                    <option value="quarterly">Quarterly</option>
                  </select>
                </div>
                <!-- Bank Account Selection (Visible only for Detailed Report) -->
                <div class="mb-3" id="bankAccountContainer" style="display: none;">
                  <label for="accountId" class="form-label">Bank Account</label>
                  <select class="form-select" id="accountId" name="account_id">
                    <option value="">Select Bank Account</option>
                    <!-- Options populated by AJAX -->
                  </select>
                </div>

                <!-- Include Envelope Report Checkbox (Visible only for Detailed Report) -->
                <div class="mb-3" id="envelopeContainer" style="display: none;">
                  <label class="form-label" for="includeEnvelope">
                    <input type="checkbox" id="includeEnvelope" name="includeEnvelope"> Include Envelope Report
                  </label>
                </div>
              </div>
            </div>

            <!-- Year Selection -->
            <div class="mb-3">
              <label for="year" class="form-label">Select Year</label>
              <select class="form-select" id="year" name="year"></select>
            </div>

            <!-- Quarter Selection (Hidden Initially) -->
            <div class="mb-3" id="quarterContainer" style="display: none;">
              <label for="quarter" class="form-label">Select Quarter</label>
              <select class="form-select" id="quarter" name="quarter">
                <option value="Q1">Q1 (Jan - Mar)</option>
                <option value="Q2">Q2 (Apr - Jun)</option>
                <option value="Q3">Q3 (Jul - Sep)</option>
                <option value="Q4">Q4 (Oct - Dec)</option>
              </select>
            </div>

            <!-- Submit Button -->
            <div class="mb-3 mt-4">
              <button type="submit" class="btn btn-primary">Download Report</button>
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
  // Initialize Select2 for better styling
  $(document).ready(function () {
    $('#year, #reportTypeSummarized, #reportTypeDetailed, #quarter, #accountId').select2({ width: '100%' });

    // Populate year options from 2024 to the current year
    const currentYear = new Date().getFullYear();
    let options = '';
    for (let year = 2024; year <= currentYear; year++) {
      options += `<option value="${year}">${year}</option>`;
    }
    $('#year').html(options);

    // Show/hide quarter selection based on report type
    $('#reportTypeSummarized, #reportTypeDetailed').on('change', function () {
      if ($(this).val() === 'quarterly') {
        $('#quarterContainer').show();
      } else {
        $('#quarterContainer').hide();
      }
    });

    // Show/hide Bank Account and Include Envelope Report based on selected report type
    $('#reportTab a').on('click', function () {
      const selectedTab = $(this).attr('id');

      if (selectedTab === 'detailed-tab') {
        $('#bankAccountContainer').show();
        $('#envelopeContainer').show();
      } else {
        $('#bankAccountContainer').hide();
        $('#envelopeContainer').hide();
      }
    });

    // Handle form submission
    $('#incomeStatementForm').on('submit', function (event) {
      event.preventDefault(); // Prevent default form submission

      let reportTypeSummarized = $('#reportTypeSummarized').val();
      let reportTypeDetailed = $('#reportTypeDetailed').val();
      let selectedYear = $('#year').val();
      let selectedQuarter = $('#quarter').val();
      let accountId = $('#accountId').val();
      let includeEnvelope = $('#includeEnvelope').prop('checked') ? 'yes' : 'no';

      if (!selectedYear) {
        $('#responseMessage').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i> Please select a year.</div>');
        return;
      }

      // Determine URL based on active tab and report type
      let reportUrl = '';
      if ($('#summarized-tab').hasClass('active')) {
        reportUrl = reportTypeSummarized === 'annual' 
          ? `/reports/download_annual_revenue_breakdown_summarized.php?year=${selectedYear}` 
          : `/reports/download_quarterly_revenue_breakdown_summarized.php?year=${selectedYear}&quarter=${selectedQuarter}`;
      } else if ($('#detailed-tab').hasClass('active')) {
        reportUrl = reportTypeDetailed === 'annual' 
          ? `/reports/download_annual_revenue_breakdown_detailed.php?year=${selectedYear}&account_id=${accountId}&include_envelope=${includeEnvelope}` 
          : `/reports/download_quarterly_revenue_breakdown_detailed.php?year=${selectedYear}&quarter=${selectedQuarter}&account_id=${accountId}&include_envelope=${includeEnvelope}`;
      }

      // Redirect to the appropriate report URL
      window.location.href = reportUrl;
    });
  });

  // Function to load bank accounts via AJAX
  function loadBankAccounts() {
    $.ajax({
      type: 'GET',
      url: '../api/data/head_parish_bank_accounts?limit=all',
      dataType: 'json',
      success: function (response) {
        let options = '<option value="">Select Bank Account</option>';
        $.each(response.data, function (index, account) {
          options += '<option value="' + account.account_id + '">' + account.account_name + '</option>';
        });
        $('#accountId').html(options);
      },
      error: function () {
        console.log('Error loading accounts.');
      }
    });
  }

  // Load bank accounts when document is ready
  $(document).ready(function () {
    loadBankAccounts();
  });
</script>

</body>
</html>
