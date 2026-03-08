<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
// Ensure only head parish admins can access this page
check_session('head_parish_admin_id', '../head-parish/sign-in');
?>
<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Financial Statement - Kanisa Langu');
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
              <h5 class="card-title fw-semibold mb-4">Financial Statement Report</h5>
            
            <ul class="nav nav-tabs" id="statementTabs" role="tablist">
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

            <div class="tab-content mt-3" id="statementTabContent">

              <!-- Head Parish -->
              <div class="tab-pane fade show active" id="head-parish" role="tabpanel">
                <form id="headParishForm">
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="accountId" class="form-label">Bank Account</label>
                        <select class="form-select" id="accountId" name="account_id" required>
                          <option value="">Select Bank Account</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-3">
                      <div class="mb-3">
                        <label for="startDate" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="startDate" name="start_date" value="<?php echo date('Y-m-01'); ?>" required>
                      </div>
                    </div>
                    <div class="col-lg-3">
                      <div class="mb-3">
                        <label for="endDate" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="endDate" name="end_date" value="<?php echo date('Y-m-t'); ?>" required>
                      </div>
                    </div>
                  </div>
                  <input type="hidden" name="management_level" value="head-parish">
                  <div class="mb-3">
                    <button type="submit" class="btn btn-success">Download Report</button>
                  </div>
                </form>
              </div>

              <!-- Sub Parish -->
              <div class="tab-pane fade" id="sub-parish" role="tabpanel">
                <form id="subParishForm">
                  <div class="row">
                    <div class="col-lg-4">
                      <div class="mb-3">
                        <label for="subParishId" class="form-label">Sub Parish</label>
                        <select class="form-select" id="subParishId" name="sub_parish_id" required>
                          <option value="">Select Sub Parish</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-4">
                      <div class="mb-3">
                        <label for="accountIdSub" class="form-label">Bank Account</label>
                        <select class="form-select" id="accountIdSub" name="account_id" required>
                          <option value="">Select Bank Account</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-2">
                      <label class="form-label">Start Date</label>
                      <input type="date" class="form-control" name="start_date" value="<?php echo date('Y-m-01'); ?>" required>
                    </div>
                    <div class="col-lg-2">
                      <label class="form-label">End Date</label>
                      <input type="date" class="form-control" name="end_date" value="<?php echo date('Y-m-t'); ?>" required>
                    </div>
                  </div>
                  <input type="hidden" name="management_level" value="sub-parish">
                  <div class="mb-3">
                    <button type="submit" class="btn btn-success">Download Report</button>
                  </div>
                </form>
              </div>

              <!-- Community -->
              <div class="tab-pane fade" id="community" role="tabpanel">
                <form id="communityForm">
                  <div class="row">
                    <div class="col-lg-4">
                      <div class="mb-3">
                        <label for="subParishIdCom" class="form-label">Sub Parish</label>
                        <select class="form-select" id="subParishIdCom" name="sub_parish_id" required>
                          <option value="">Select Sub Parish</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-4">
                      <div class="mb-3">
                        <label for="communityId" class="form-label">Community</label>
                        <select class="form-select" id="communityId" name="community_id" required>
                          <option value="">Select Community</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-4">
                      <div class="mb-3">
                        <label for="accountIdCom" class="form-label">Bank Account</label>
                        <select class="form-select" id="accountIdCom" name="account_id" required>
                          <option value="">Select Bank Account</option>
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-6">
                      <label class="form-label">Start Date</label>
                      <input type="date" class="form-control" name="start_date" value="<?php echo date('Y-m-01'); ?>" required>
                    </div>
                    <div class="col-lg-6">
                      <label class="form-label">End Date</label>
                      <input type="date" class="form-control" name="end_date" value="<?php echo date('Y-m-t'); ?>" required>
                    </div>
                  </div>
                  <input type="hidden" name="management_level" value="community">
                  <div class="mb-3 mt-3">
                    <button type="submit" class="btn btn-success">Download Report</button>
                  </div>
                </form>
              </div>


              <!-- Group -->
              <div class="tab-pane fade" id="group" role="tabpanel">
                <form id="groupForm">
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="groupId" class="form-label">Group</label>
                        <select class="form-select" id="groupId" name="group_id" required>
                          <option value="">Select Group</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="accountIdGp" class="form-label">Bank Account</label>
                        <select class="form-select" id="accountIdGp" name="account_id" required>
                          <option value="">Select Bank Account</option>
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-6">
                      <label class="form-label">Start Date</label>
                      <input type="date" class="form-control" name="start_date" value="<?php echo date('Y-m-01'); ?>" required>
                    </div>
                    <div class="col-lg-6">
                      <label class="form-label">End Date</label>
                      <input type="date" class="form-control" name="end_date" value="<?php echo date('Y-m-t'); ?>" required>
                    </div>
                  </div>
                  <input type="hidden" name="management_level" value="group">
                  <div class="mb-3 mt-3">
                    <button type="submit" class="btn btn-success">Download Report</button>
                  </div>
                </form>
              </div>
              <div id="responseMessage"></div>
            </div>
          </div>
        </div>
      </div>
  </div>

  <?php require_once('components/footer_files.php') ?>

<script>
$(document).ready(function () {
   $('select').select2({ width: '100%' });
    function submitReportForm(formId, managementLevel) {
        const formData = $(formId).serialize() + '&management_level=' + managementLevel;

        // Show loader
        $('#responseMessage').html('<div class="response-message"><i class="fas fa-spinner fa-spin icon"></i> Generating Report...</div>');

       $.ajax({
            type: 'POST',
            url: '/reports/financial_statement.php',
            data: formData,
            dataType: 'json',
            success: function(response) {
                let messageHtml = '';
                if (response.success && response.pdf_base64) {
                    // Create a temporary link to download the PDF
                    const link = document.createElement('a');
                    link.href = 'data:application/pdf;base64,' + response.pdf_base64;
                    link.download = response.filename || 'report.pdf';
                    link.click();

                    messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i> Report generated successfully. Download should start automatically.</div>';
                } else {
                    messageHtml = '<div class="response-message error"><i class="fas fa-times-circle icon"></i> ' + (response.message || 'Failed to generate report.') + '</div>';
                }
                $('#responseMessage').html(messageHtml);
            },
            error: function(xhr, status, error) {
                $('#responseMessage').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i> An error occurred: ' + error + '</div>');
            }
        });
    }


   // Load select options
   function loadBankAccounts() {
      $.ajax({ type:'GET', url:'../api/data/head_parish_bank_accounts?limit=all', dataType:'json', success:function(response){
          let options='<option value="">Select Bank Account</option>';
          $.each(response.data,function(i,account){ options+='<option value="'+account.account_id+'">'+account.account_name+'</option>'; });
          $('#accountId,#accountIdSub,#accountIdCom,#accountIdGp').html(options);
      }});
   }
   function loadSubParishes() {
      $.ajax({ type:'GET', url:'../api/data/head_parish_sub_parishes?limit=all', dataType:'json', success:function(response){
        let options='<option value="">Select Sub Parish</option>';
        $.each(response.data,function(i,s){ options+='<option value="'+s.sub_parish_id+'">'+s.sub_parish_name+'</option>'; });
        $('#subParishId,#subParishIdCom').html(options);
      }});
   }
   function loadCommunities(subParishId){
      $.ajax({ type:'GET', url:'../api/data/head_parish_communities?limit=all', data:{sub_parish_id:subParishId}, dataType:'json', success:function(response){
          let options='<option value="">Select Community</option>';
          $.each(response.data,function(i,c){ options+='<option value="'+c.community_id+'">'+c.community_name+'</option>'; });
          $('#communityId').html(options);
      }});
   }
   function loadGroups() {
      $.ajax({ type:'GET', url:'../api/data/head_parish_groups?limit=all', dataType:'json', success:function(response){
        let options='<option value="">Select Group</option>';
        $.each(response.data,function(i,g){ options+='<option value="'+g.group_id+'">'+g.group_name+'</option>'; });
        $('#groupId').html(options);
      }});
   }

   loadBankAccounts();
   loadSubParishes();
   loadGroups();

   $('#subParishIdCom').on('change', function(){ loadCommunities($(this).val()); });

   $('#headParishForm').on('submit', function(e){ 
        e.preventDefault(); 
        submitReportForm('#headParishForm','head-parish'); 
    });
    $('#subParishForm').on('submit', function(e){ 
        e.preventDefault(); 
        submitReportForm('#subParishForm','sub-parish'); 
    });
    $('#communityForm').on('submit', function(e){ 
        e.preventDefault(); 
        submitReportForm('#communityForm','community'); 
    });
    $('#groupForm').on('submit', function(e){ 
        e.preventDefault(); 
        submitReportForm('#groupForm','group'); 
    });

});
</script>

</body>
</html>
