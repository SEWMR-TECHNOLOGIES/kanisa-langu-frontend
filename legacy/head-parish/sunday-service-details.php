<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
check_session('head_parish_admin_id', '../head-parish/sign-in');
?>
<!doctype html>
<html lang="en">
<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Sunday Service Preview - Kanisa Langu'); // Updated title
  ?>
  <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        .header h1 {
            font-size: 2.5rem;
            color: #333;
        }
        .service-card {
            background: #fff;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .service-header {
            padding: 20px;
            color: #fff;
            text-align: center;
        }
        .service-header h2 {
            font-size: 2rem;
            margin: 0;
        }
        .service-header p {
            font-size: 1rem;
            margin-top: 5px;
        }
        .section {
            padding: 20px;
            border-top: 1px solid #eaeaea;
        }
        .section h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: #333;
        }
        .time-card, .choir-card, .elder-card {
            background: #f8f9fa;
            margin: 5px 0;
            padding: 10px 15px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .time-card span {
            font-size: 1.2rem;
            color: #555;
            font-weight: bold;
            margin-right: 10px;
        }
        .badge {
            display: inline-block;
            padding: 6px 12px;
            margin: 2px 0;
            border-radius: 5px;
            font-size: 0.9rem;
            font-weight: bold;
            color: #fff;
        }
        .badge-preacher { background: #3b82f6; }
        .badge-leader { background: #f59e0b; }
        .badge-elder { background: #10b981; }

        .book-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin: 0;
            padding: 0;
        }
        .book-card {
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .book-card strong {
            display: block;
            margin-bottom: 5px;
        }

        @media (max-width: 768px) {
            .book-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .time-card, .choir-card, .elder-card {
                flex-direction: column;
                text-align: center;
            }
        }
        .card-title {
            font-size: 1.25rem;
            font-weight: bold;
            color: #006400; /* Deep Green for title */
        }
        
        .card-text strong {
            font-size: 1.2rem;
            color: #333; /* Dark color for numbers */
        }
        
        .badge {
            padding: 5px 10px;
            font-size: 1rem;
        }
        
        .badge-info {
            background-color: #17a2b8; /* Info Blue */
        }
        
        .badge-warning {
            background-color: #ffc107; /* Warning Yellow */
        }
  </style>
</head>
<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <?php require_once('components/sidebar.php') ?>
    <div class="body-wrapper">
      <?php require_once('components/header.php') ?>
      <div class="container-fluid">
          <!-- Service Details Section -->
            <div id="services"></div>
        <!--<div class="card">-->
        <!--  <div class="card-body">-->
            <!-- Service Details Section -->
        <!--    <div id="services"></div>-->
        <!--  </div>-->
        <!--</div>-->
      </div>
    </div>
  </div>
  <?php require_once('components/footer_files.php') ?>

  <script>
    const fetchData = async () => {
      try {
        // Assuming you're passing parameters like head_parish_id and service_date from PHP
        const serviceDate = '<?php echo isset($_GET['service_date']) ? $_GET['service_date'] : date('Y-m-d'); ?>';
        const headParishId = '<?php echo isset($_GET["head_parish_id"]) ? $_GET["head_parish_id"] : "1"; ?>';
        
        const response = await fetch(`../api/data/sunday_service.php?head_parish_id=${headParishId}&service_date=${serviceDate}`);
        const data = await response.json();
        renderServices(data);
      } catch (error) {
        console.error('Error fetching data:', error);
      }
    };

    const renderServices = (services) => {
      const servicesContainer = document.getElementById('services');
      services.forEach(service => {
        const formattedDate = formatDate(service.service_date);
        const serviceHTML = `
          <div class="service-card">
            <div class="service-header" style="background-color: ${service.service_color.code}">
              <h2>${service.main_text}</h2>
              <h2>${formattedDate}</h2>
            </div>
            <div class="section">
                <h3 class="text-center text-primary mb-4">Books & Page Numbers</h3>
                <div class="row">
                  <div class="col-md-4">
                    <div class="card mb-3">
                      <div class="card-body">
                        <h5 class="card-title text-center text-success">Liturgy</h5>
                        <div class="row">
                          <div class="col-6 text-center">
                            <span class="badge badge-info">Small</span>
                            <p class="card-text"><strong>${service.books_page_numbers.small_liturgy}</strong></p>
                          </div>
                          <div class="col-6 text-center">
                            <span class="badge badge-warning">Large</span>
                            <p class="card-text"><strong>${service.books_page_numbers.large_liturgy}</strong></p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                
                  <div class="col-md-4">
                    <div class="card mb-3">
                      <div class="card-body">
                        <h5 class="card-title text-center text-success">Antiphony</h5>
                        <div class="row">
                          <div class="col-6 text-center">
                            <span class="badge badge-info">Small</span>
                            <p class="card-text"><strong>${service.books_page_numbers.small_antiphony}</strong></p>
                          </div>
                          <div class="col-6 text-center">
                            <span class="badge badge-warning">Large</span>
                            <p class="card-text"><strong>${service.books_page_numbers.large_antiphony}</strong></p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                
                  <div class="col-md-4">
                    <div class="card mb-3">
                      <div class="card-body">
                        <h5 class="card-title text-center text-success">Praise</h5>
                        <div class="row">
                          <div class="col-6 text-center">
                            <span class="badge badge-info">Small</span>
                            <p class="card-text"><strong>${service.books_page_numbers.small_praise}</strong></p>
                          </div>
                          <div class="col-6 text-center">
                            <span class="badge badge-warning">Large</span>
                            <p class="card-text"><strong>${service.books_page_numbers.large_praise}</strong></p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
            </div>
            <div class="section">
              <h3 class="text-center text-primary mb-4">Service Times</h3>
              <div class="row">
                ${service.service_times.map(time => `
                  <div class="col-md-4">
                    <div class="card mb-3">
                      <div class="card-body">
                        <h5 class="card-title text-center text-success">Service ${time.service_number}</h5>
                        <p class="card-text text-center"><strong>Time:</strong> ${time.start_time}</p>
                      </div>
                    </div>
                  </div>
                `).join('')}
              </div>
            </div>
            <div class="section">
              <h3 class="text-center text-primary mb-4">Offerings</h3>
              <ul class="list-unstyled">
                ${service.offerings.map(offer => `
                  <li class="mb-2">
                    <i class="fas fa-check text-success"></i> <span>${offer}</span>
                  </li>
                `).join('')}
              </ul>
            </div>

            <div class="section">
              <h3 class="text-center text-primary mb-4">Scriptures</h3>
              <div class="d-flex flex-wrap">
                ${service.scriptures.map(scripture => `
                  <div class="time-card me-3 mb-3 d-flex align-items-center">
                    <i class="fa fa-book me-2"></i> 
                    ${scripture.book_name} ${scripture.chapter}:${scripture.starting_verse_number}${scripture.ending_verse_number ? '-' + scripture.ending_verse_number : ''}
                  </div>
                `).join('')}
              </div>
            </div>

            <div class="section">
              <h3 class="text-center text-primary mb-4">Choirs</h3>
              ${Object.entries(service.choirs).map(([serviceNum, choirs]) => `
                <div class="choir-card mb-3">
                  <strong>Service ${serviceNum}:</strong>
                  <ul class="list-unstyled">
                    ${choirs.map(choir => `
                      <li class="mb-2">
                        <i class="fas fa-music text-info"></i> <span>${choir}</span>
                      </li>
                    `).join('')}
                  </ul>
                </div>
              `).join('')}
            </div>

            <!-- Preachers Section -->
            <div class="section">
              <h3>Preachers</h3>
              ${Object.entries(service.preacher).map(([serviceNum, preacher]) => `
                <div class="time-card"><span class="badge badge-preacher">Service ${serviceNum}</span> ${preacher}</div>
              `).join('')}
            </div>
            <!-- Leaders Section -->
            <div class="section">
              <h3>Leaders</h3>
              ${Object.entries(service.leader).map(([serviceNum, leader]) => `
                <div class="time-card"><span class="badge badge-leader">Service ${serviceNum}</span> ${leader}</div>
              `).join('')}
            </div>
            <div class="section">
              <h3>Elders</h3>
              ${Object.entries(service.elders).map(([serviceNum, elders]) => `
                <div class="elder-card"><strong>Service ${serviceNum}:</strong> ${elders.join(', ')}</div>
              `).join('')}
            </div>
          </div>
        `;
        servicesContainer.innerHTML += serviceHTML;
      });
    };

    fetchData();
    
    // Reusable function to format a date
    const formatDate = (dateString) => {
      const date = new Date(dateString);
      const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
      return date.toLocaleDateString('en-US', options);
    };

  </script>

</body>
</html>
