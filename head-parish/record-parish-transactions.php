<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Record Transactions - Kanisa Langu');
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
              <h5 class="card-title fw-semibold mb-4">Record Transactions</h5>
            
            <ul class="nav nav-tabs" id="transactionTabs" role="tablist">
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

            <div class="tab-content mt-3" id="transactionTabContent">

              <!-- Head Parish Form -->
              <div class="tab-pane fade show active" id="head-parish" role="tabpanel">
                <form id="headParishForm">
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="accountId" class="form-label">Bank Account</label>
                        <select class="form-select" id="accountId" name="account_id">
                          <option value="">Select Bank Account</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="txnAmount" class="form-label">Amount</label>
                        <input type="number" class="form-control" id="txnAmount" name="amount" placeholder="Enter Amount" min="0.00" step="0.01" required>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="txnType" class="form-label">Transaction Type</label>
                        <select class="form-select" id="txnType" name="type" required>
                          <option value="">Select Type</option>
                          <option value="revenue">Revenue</option>
                          <option value="expense">Expense</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="mb-3">
                            <label for="txnDate" class="form-label">Transaction Date</label>
                            <input type="date" class="form-control" id="txnDate" name="txn_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                  </div>
                  <div class="mb-3">
                    <label for="txnDescription" class="form-label">Description</label>
                    <textarea class="form-control" id="txnDescription" name="description" rows="2" placeholder="Enter transaction description"></textarea>
                  </div>

                  <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Record Transaction</button>
                  </div>
                </form>
              </div>

              <!-- Sub Parish Form -->
              <div class="tab-pane fade" id="sub-parish" role="tabpanel">
                <form id="subParishForm">
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="subParishId" class="form-label">Sub Parish</label>
                        <select class="form-select" id="subParishId" name="sub_parish_id">
                          <option value="">Select Sub Parish</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="accountIdSub" class="form-label">Bank Account</label>
                        <select class="form-select" id="accountIdSub" name="account_id">
                          <option value="">Select Bank Account</option>
                        </select>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-lg-4">
                      <div class="mb-3">
                        <label for="txnAmountSub" class="form-label">Amount</label>
                        <input type="number" class="form-control" id="txnAmountSub" name="amount" placeholder="Enter Amount" min="0.00" step="0.01" required>
                      </div>
                    </div>
                    <div class="col-lg-4">
                      <div class="mb-3">
                        <label for="txnTypeSub" class="form-label">Transaction Type</label>
                        <select class="form-select" id="txnTypeSub" name="type" required>
                          <option value="">Select Type</option>
                          <option value="revenue">Revenue</option>
                          <option value="expense">Expense</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-4">
                      <div class="mb-3">
                        <label for="txnDateSub" class="form-label">Transaction Date</label>
                        <input type="date" class="form-control" id="txnDateSub" name="txn_date" value="<?php echo date('Y-m-d'); ?>" required>
                      </div>
                    </div>
                  </div>

                  <div class="mb-3">
                    <label for="txnDescriptionSub" class="form-label">Description</label>
                    <textarea class="form-control" id="txnDescriptionSub" name="description" rows="2" placeholder="Enter transaction description"></textarea>
                  </div>

                  <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Record Transaction</button>
                  </div>
                </form>
              </div>

              <!-- Community Form -->
              <div class="tab-pane fade" id="community" role="tabpanel">
                <form id="communityForm">
                  <div class="row">
                    <div class="col-lg-4">
                      <div class="mb-3">
                        <label for="subParishIdCom" class="form-label">Sub Parish</label>
                        <select class="form-select" id="subParishIdCom" name="sub_parish_id">
                          <option value="">Select Sub Parish</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-4">
                      <div class="mb-3">
                        <label for="communityId" class="form-label">Community</label>
                        <select class="form-select" id="communityId" name="community_id">
                          <option value="">Select Community</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="mb-3">
                            <label for="accountIdCom" class="form-label">Bank Account</label>
                            <select class="form-select" id="accountIdCom" name="account_id">
                            <option value="">Select Bank Account</option>
                            </select>
                        </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-4">
                      <div class="mb-3">
                        <label for="txnAmountCom" class="form-label">Amount</label>
                        <input type="number" class="form-control" id="txnAmountCom" name="amount" placeholder="Enter Amount" min="0.00" step="0.01" required>
                      </div>
                    </div>
                    <div class="col-lg-4">
                      <div class="mb-3">
                        <label for="txnTypeCom" class="form-label">Transaction Type</label>
                        <select class="form-select" id="txnTypeCom" name="type" required>
                          <option value="">Select Type</option>
                          <option value="revenue">Revenue</option>
                          <option value="expense">Expense</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="mb-3">
                            <label for="txnDateCom" class="form-label">Transaction Date</label>
                            <input type="date" class="form-control" id="txnDateCom" name="txn_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>    
                    </div>
                  </div>

                  <div class="mb-3">
                    <label for="txnDescriptionCom" class="form-label">Description</label>
                    <textarea class="form-control" id="txnDescriptionCom" name="description" rows="2" placeholder="Enter transaction description"></textarea>
                  </div>

                  <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Record Transaction</button>
                  </div>
                </form>
              </div>

              <!-- Group Form -->
              <div class="tab-pane fade" id="group" role="tabpanel">
                <form id="groupForm">
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="groupId" class="form-label">Group</label>
                        <select class="form-select" id="groupId" name="group_id">
                          <option value="">Select Group</option>
                        </select>
                      </div>
                    </div>
                     <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="accountIdGp" class="form-label">Bank Account</label>
                          <select class="form-select" id="accountIdGp" name="account_id">
                            <option value="">Select Bank Account</option>
                          </select>
                        </div>
                      </div>
                  </div>

                  <div class="row">
                    <div class="col-lg-4">
                      <div class="mb-3">
                        <label for="txnAmountGp" class="form-label">Amount</label>
                        <input type="number" class="form-control" id="txnAmountGp" name="amount" placeholder="Enter Amount" min="0.00" step="0.01" required>
                      </div>
                    </div>
                    <div class="col-lg-4">
                      <div class="mb-3">
                        <label for="txnTypeGp" class="form-label">Transaction Type</label>
                        <select class="form-select" id="txnTypeGp" name="type" required>
                          <option value="">Select Type</option>
                          <option value="revenue">Revenue</option>
                          <option value="expense">Expense</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-4">
                      <div class="mb-3">
                        <label for="txnDateGp" class="form-label">Transaction Date</label>
                        <input type="date" class="form-control" id="txnDateGp" name="txn_date" value="<?php echo date('Y-m-d'); ?>" required>
                      </div>
                    </div>
                  </div>

                  <div class="mb-3">
                    <label for="txnDescriptionGp" class="form-label">Description</label>
                    <textarea class="form-control" id="txnDescriptionGp" name="description" rows="2" placeholder="Enter transaction description"></textarea>
                  </div>

                  <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Record Transaction</button>
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

   // Generic submit function
   function submitForm(formId, target) {
      const formData = $(formId).serialize() + '&management_level=' + target;
      $('#responseMessage').html('<div class="response-message"><i class="fas fa-spinner fa-spin icon"></i> Recording Transaction...</div>');
      $.ajax({
        type: 'POST',
        url: '../api/records/record_parish_transaction', 
        data: formData,
        dataType: 'json',
        success: function(response) {
          let messageHtml = '';
          if (response.success) {
            messageHtml = '<div class="response-message success"><i class="fas fa-check-circle icon"></i>' + response.message + '</div>';
            setTimeout(() => { location.reload(); }, 2000);
          } else {
            messageHtml = '<div class="response-message error"><i class="fas fa-times-circle icon"></i>' + response.message + '</div>';
          }
          $('#responseMessage').html(messageHtml);
        },
        error: function(xhr, status, error) {
          $('#responseMessage').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i>An error occurred: ' + error + '</div>');
        }
      });
   }

   // Attach submit handlers
   $('#headParishForm').on('submit', function(e){ e.preventDefault(); submitForm('#headParishForm','head-parish'); });
   $('#subParishForm').on('submit', function(e){ e.preventDefault(); submitForm('#subParishForm','sub-parish'); });
   $('#communityForm').on('submit', function(e){ e.preventDefault(); submitForm('#communityForm','community'); });
   $('#groupForm').on('submit', function(e){ e.preventDefault(); submitForm('#groupForm','group'); });

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
});
</script>

</body>
</html>
