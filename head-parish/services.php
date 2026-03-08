<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
// Ensure the user is authenticated as superadmin
check_session('head_parish_admin_id', '../head-parish/sign-in');
?>
<!doctype html>
<html lang="en">
<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Manage Services - Kanisa Langu');
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
      white-space: nowrap; 
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
            <h5 class="card-title fw-semibold mb-4">Manage Services</h5>

            <!-- Loading GIF -->
            <div id="loading">
              <img src="../assets/images/gifs/loading.gif" alt="Loading..." height="100">
            </div>

            <!-- Services Table -->
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Service</th>
                    <th>Start Time</th>
                    <th class="text-end">Actions</th>
                  </tr>
                </thead>
                <tbody id="serviceList">
                  <!-- Table rows will be populated here using AJAX -->
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php require_once('components/footer_files.php') ?>
  <script>
    $(document).ready(function () {
        const fetchServices = () => {
            $('#loading').show(); // Show loading GIF
            $.ajax({
                type: 'GET',
                url: '../api/data/head_parish_services', 
                dataType: 'json',
                success: function (response) {
                    const services = response.data;
                    const serviceList = $('#serviceList');
                    serviceList.empty();
                    let rowIndex = 1;
                    services.forEach(service => {
                        serviceList.append(`
                            <tr>
                                <td>${rowIndex++}</td>
                                <td>${service.service}</td>
                                <td>${service.time}</td>
                                <td class="text-end">
                                    <a href="./set-service-time" class="btn btn-secondary btn-sm"><i class="fas fa-edit"></i> Update</a>
                                    <a href="#" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i> Delete</a>
                                </td>
                            </tr>
                        `);
                    });

                    $('#loading').hide(); // Hide loading GIF
                },
                error: function (xhr, status, error) {
                    console.log('Error fetching services:', error);
                    $('#loading').hide(); // Hide loading GIF even on error
                }
            });
        };

        // Load services initially
        fetchServices();
    });
  </script>
</body>
</html>
