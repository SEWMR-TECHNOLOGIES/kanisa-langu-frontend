<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');
check_session('head_parish_admin_id', '../head-parish/sign-in');
?>
<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Clerks Report - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Clerks Report</h5>

            <!-- Harambee Contribution Form -->
            <form id="harambeeContributionForm" autocomplete="off">
              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="targetTypeSelectionIndividual" class="form-label">Select Type</label>
                    <select class="form-select" id="targetTypeSelectionIndividual" name="target">
                      <option value="">Select Type</option>
                      <option value="head-parish">Head Parish</option>
                      <option value="sub-parish">Sub Parish</option>
                      <option value="community">Community</option>
                      <option value="groups">Groups</option>
                    </select>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="harambeeIdIndividual" class="form-label">Harambee</label>
                    <select class="form-select" id="harambeeIdIndividual" name="harambee_id">
                      <option value="">Select Harambee</option>
                      <!-- Options populated by AJAX -->
                    </select>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="adminId" class="form-label">Select User</label>
                    <select class="form-select" id="adminId" name="admin_id">
                        <option value="">Select User</option>
                        <?php
                        $currentAdminId = $_SESSION['head_parish_admin_id'];
                        $currentAdminRole = $_SESSION['head_parish_admin_role'];
                    
                        if ($currentAdminRole === 'clerk') {
                            $query = "SELECT head_parish_admin_id, head_parish_admin_fullname, head_parish_admin_role, head_parish_admin_phone 
                                      FROM head_parish_admins 
                                      WHERE head_parish_admin_id = ?";
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param("i", $currentAdminId);
                        } else {
                            $query = "SELECT head_parish_admin_id, head_parish_admin_fullname, head_parish_admin_role, head_parish_admin_phone 
                                      FROM head_parish_admins WHERE head_parish_admin_role NOT IN ('pastor', 'evangelist')
                                      ORDER BY head_parish_admin_fullname ASC";
                            $stmt = $conn->prepare($query);
                        }
                    
                        if ($stmt->execute()) {
                            $result = $stmt->get_result();
                            while ($row = $result->fetch_assoc()) {
                                $id = $row['head_parish_admin_id'];
                                $name = ucwords(strtolower($row['head_parish_admin_fullname']));
                                $role = ucfirst(strtolower($row['head_parish_admin_role']));
                                $phone = $row['head_parish_admin_phone'] ?: 'No Phone';
                                $selected = ($id == $currentAdminId) ? 'selected' : '';
                                echo "<option value=\"$id\" $selected>$name - $role - $phone</option>";
                            }
                        } else {
                            echo '<option disabled>Error loading users</option>';
                        }
                    
                        $stmt->close();
                        ?>
                    </select>
                  </div>
                  
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="contributionDate" class="form-label">Contribution Date</label>
                    <input type="date" class="form-control" id="contributionDate" name="contribution_date" 
                           max="<?php echo date('Y-m-d'); ?>" 
                           value="<?php echo date('Y-m-d'); ?>">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="mb-3">
                  <button type="submit" class="btn btn-primary mb-2 me-2">Download Report</button>
                  <button type="button" id="downloadAllClerksReport" class="btn btn-success mb-2">Download Report for All Clerks</button>
                  <button type="button" id="downloadAllClerksDetailedReport" class="btn btn-warning mb-2">Download Detailed Report for All Clerks</button>
                </div>
              </div>
            </form>
            <div id="responseMessage" class="mt-3"></div>
          </div>
        </div>
      </div>
    </div>
  </div>


  <?php require_once('components/footer_files.php') ?>

  <script>
    $('select').select2({ width: '100%' });

    $(document).ready(function () {
      $('#targetTypeSelectionIndividual').change(function () {
        const selectedType = $(this).val();
        loadHarambee('harambeeIdIndividual', `../api/data/head_parish_harambee?limit=all&target=${selectedType}`);
      });

      function loadHarambee(targetId, url) {
        $.ajax({
          type: 'GET',
          url: url,
          dataType: 'json',
          success: function (response) {
            let options = '<option value="">Select Harambee</option>';
            $.each(response.data, function (index, harambee) {
              options += `<option value="${harambee.harambee_id}">${harambee.description} - ${harambee.from_date} - ${harambee.to_date} - TZS ${harambee.amount}</option>`;
            });
            $('#' + targetId).html(options);
          },
          error: function (xhr, status, error) {
            console.log('Error loading harambee:', error);
          }
        });
      }

      function handleFormSubmission(url, formData) {
        $('#responseMessage').html('<div class="response-message"><i class="fas fa-spinner fa-spin icon"></i> Sending Request...</div>');
        $.ajax({
          type: 'POST',
          url: url,
          data: formData,
          dataType: 'json',
          success: function (response) {
            let messageHtml = '';
            if (response.success) {
              messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i>' + response.message + '</div>';
              setTimeout(function () {
                if (response.url) {
                  window.open(response.url, '_blank');
                } else {
                  location.reload();
                }
              }, 2000);
            } else {
              messageHtml = '<div class="response-message error"><i class="fas fa-times-circle icon"></i>' + response.message + '</div>';
            }
            $('#responseMessage').html(messageHtml);
          },
          error: function (xhr, status, error) {
            const errorMessage = xhr.responseJSON?.message || `An error occurred.${error}`;
            $('#responseMessage').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i>' + errorMessage + '</div>');
          }
        });
      }

      $('#harambeeContributionForm').submit(function (event) {
        event.preventDefault();
        let formData = $(this).serialize();
        let localTime = new Date().toISOString();
        formData += `&local_timestamp=${encodeURIComponent(localTime)}`;
        handleFormSubmission('../api/data/download_harambee_clerks_report', formData);
      });

      $('#downloadAllClerksReport').click(function () {
        const target = $('#targetTypeSelectionIndividual').val();
        const harambeeId = $('#harambeeIdIndividual').val();
        const contributionDate = $('#contributionDate').val();
        const formData = {
          harambee_id: harambeeId || '',
          target: target || '',
          contribution_date: contributionDate || '',
          all: true
        };
        handleFormSubmission('../api/data/download_harambee_clerks_report', formData);
      });

    $('#downloadAllClerksDetailedReport').click(function () {
      const target = $('#targetTypeSelectionIndividual').val();
      const harambeeId = $('#harambeeIdIndividual').val();
      const contributionDate = $('#contributionDate').val();
    
      if (!target || !harambeeId || !contributionDate) {
        $('#responseMessage').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i> Please select Type, Harambee, and Date first.</div>');
        return;
      }
    
      const formData = {
        harambee_id: harambeeId,
        target: target,
        contribution_date: contributionDate,
        all: true,
        detailed: true
      };
    
      handleFormSubmission('../api/data/download_harambee_clerks_report', formData);
    });

    });
  </script>

</body>

</html>
