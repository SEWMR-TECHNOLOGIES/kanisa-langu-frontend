<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Manage Envelopes - Kanisa Langu');
  ?>
  <style>
    #loading {
      display: none; /* Hidden by default */
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      z-index: 9999;
    }
    td.text-end {
      white-space: nowrap; /* Keeps the buttons on one line */
    }
  </style>
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
    <?php require_once('components/sidebar.php') ?>
    <div class="body-wrapper">
      <?php require_once('components/header.php') ?>
      <div class="container-fluid">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Manage Envelopes</h5>

            <!-- Search Form -->
            <div class="row mb-4">
              <div class="col-md-8">
                <input type="text" id="searchHarambee" class="form-control" placeholder="Search Envelopes">
              </div>
              <div class="col-md-4 text-end">
                <button class="btn btn-primary" id="searchBtn">Search</button>
              </div>
            </div>

            <!-- Year Tabs for Harambee Targets -->
            <ul class="nav nav-tabs" id="yearTabs" role="tablist">
              <!-- Dynamically populated year tabs will go here -->
            </ul>

            <!-- Tab content -->
            <div class="tab-content mt-4">
              <!-- Envelope Target Table -->
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Member Name</th>
                      <th>Envelope Number</th>
                      <th>Target Amount</th>
                      <th class="text-end">Actions</th>
                    </tr>
                  </thead>
                  <tbody id="envelopeTargetList"></tbody>
                </table>
              </div>

              <!-- Loading GIF -->
              <div id="loading">
                <img src="../assets/images/gifs/loading.gif" alt="Loading..." height="100">
              </div>

              <!-- Pagination -->
              <nav id="paginationNav" aria-label="Harambee pagination">
                <ul class="pagination justify-content-center">
                  <!-- Pagination buttons will be generated dynamically -->
                </ul>
              </nav>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
  <?php require_once('components/footer_files.php') ?>

  <script>
$(document).ready(function () {
    let activeYear = new Date().getFullYear(); // Default to the current year
    let currentPage = 1; // Current page for pagination

    // Function to fetch available years for the year tabs
    const fetchAvailableYears = () => {
        $.ajax({
            type: 'GET',
            url: '../api/data/available_envelope_years',
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    let yearTabsHtml = '';
                    response.years.forEach((year, index) => {
                        yearTabsHtml += `
                            <li class="nav-item" role="presentation">
                                <button class="nav-link ${index === 0 ? 'active' : ''}" id="year-${year}-tab" data-bs-toggle="tab" data-year="${year}">
                                    ${year}
                                </button>
                            </li>`;
                        if (index === 0) activeYear = year;  // Set the first year as active by default
                    });
                    $('#yearTabs').html(yearTabsHtml);
                    fetchEnvelopeTargets(activeYear);  // Fetch data for the first year by default
                } else {
                    console.error('Error fetching years:', response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('Error fetching available years:', error);
            }
        });
    };


    // Function to fetch envelope targets for a specific year
    const fetchEnvelopeTargets = (year, page = 1, query = '') => {
        $('#loading').show();
        let url = `../api/data/envelope_targets?year=${year}&page=${page}&query=${query}`;
        
        $.ajax({
            type: 'GET',
            url: url,
            dataType: 'json',
            success: function (response) {
                let targetListHtml = response.data.map((target, index) => {
                    // Construct full name from target
                    const fullName = `${target.title ? target.title + '. ' : ''}${target.first_name}${target.middle_name ? ' ' + target.middle_name : ''} ${target.last_name}`;
                    
                    return `
                        <tr>
                            <td>${(page - 1) * 10 + index + 1}</td>
                            <td>${fullName}</td>
                            <td>${target.envelope_number}</td>
                            <td>TZS ${target.target}</td>
                            <td class="text-end">
                                <a href="#" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i> Delete</a>
                                <a href="/reports/member_envelope_statement.php?member=${target.id}&year=${year}" target="_blank" class="btn btn-primary btn-sm"><i class="fas fa-download"></i> Print Statement</a>
                            </td>
                        </tr>
                    `;
                }).join('');
                
                $('#envelopeTargetList').html(targetListHtml);
    
                // Handle pagination
                $('#paginationNav .pagination').empty();
                for (let i = 1; i <= response.total_pages; i++) {
                    $('#paginationNav .pagination').append(`
                        <li class="page-item ${i === page ? 'active' : ''}">
                            <a class="page-link" href="#">${i}</a>
                        </li>
                    `);
                }
    
                $('#loading').hide();  // Hide loading GIF
            },
            error: function (xhr, status, error) {
                console.error('Error fetching envelope targets:', error);
                $('#loading').hide();  // Hide loading GIF on error
            }
        });
    };


    // Load available years for the year tabs on page load
    fetchAvailableYears();

    // Handle year tab click event to fetch envelope targets for the selected year
    $(document).on('click', '#yearTabs button', function () {
        activeYear = $(this).data('year');
        currentPage = 1;  // Reset to the first page
        fetchEnvelopeTargets(activeYear);
    });

    // Search functionality
    $('#searchBtn').on('click', function () {
        const query = $('#searchHarambee').val();
        currentPage = 1;  // Reset to the first page on search
        fetchEnvelopeTargets(activeYear, currentPage, query);
    });

    // Attach onchange event to search input
    $('#searchHarambee').on('input', function () {
        const query = $(this).val();
        currentPage = 1;  // Reset to the first page on search
        fetchEnvelopeTargets(activeYear, currentPage, query);
    });

    // Pagination click event
    $(document).on('click', '#paginationNav .page-link', function (e) {
        e.preventDefault();
        currentPage = parseInt($(this).text());  // Get the clicked page number
        fetchEnvelopeTargets(activeYear, currentPage);  // Fetch data for that page
    });
});
  </script>
</body>

</html>
