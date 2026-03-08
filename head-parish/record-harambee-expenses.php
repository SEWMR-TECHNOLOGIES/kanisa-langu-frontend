<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');
check_session('head_parish_admin_id', '../head-parish/sign-in');
?>
<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Record Harambee Expense - Kanisa Langu');
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
            <h5 class="card-title fw-semibold mb-4">Record Harambee Expense</h5>

            <!-- Expense Form -->
            <form id="harambeeExpenseForm" autocomplete="off">
              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="targetTypeExpense" class="form-label">Select Type</label>
                    <select class="form-select" id="targetTypeExpense" name="target">
                      <option value="">Select Type</option>
                      <option value="head-parish">Head Parish</option>
                      <option value="sub-parish">Sub Parish</option>
                      <option value="community">Community</option>
                      <option value="group">Groups</option>
                    </select>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="harambeeIdExpense" class="form-label">Harambee</label>
                    <select class="form-select" id="harambeeIdExpense" name="harambee_id">
                      <option value="">Select Harambee</option>
                      <!-- Options populated by AJAX -->
                    </select>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="expenseNameId" class="form-label">Expense Name</label>
                    <select class="form-select" id="expenseNameId" name="expense_name_id">
                      <option value="">Select Expense Name</option>
                      <!-- Options populated by AJAX -->
                    </select>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="amountExpense" class="form-label">Amount</label>
                    <input type="text" class="form-control" id="amountExpense" name="amount" placeholder="Amount">
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="expenseDate" class="form-label">Expense Date</label>
                    <input type="date" class="form-control" id="expenseDate" name="expense_date" 
                           max="<?php echo date('Y-m-d'); ?>" 
                           value="<?php echo date('Y-m-d'); ?>">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="descriptionExpense" class="form-label">Description</label>
                    <input type="text" class="form-control" id="descriptionExpense" name="description" placeholder="Description">
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <button type="submit" class="btn btn-primary w-100 w-md-auto">Record Harambee Expense</button>
              </div>
            </form>
            <div id="responseMessageExpense" class="mt-3"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

<?php require_once('components/footer_files.php') ?>

<script>
  $('select').select2({ width: '100%' });

  $(document).ready(function () {

    function formatWithCommas(number) {
      return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
    function removeCommas(value) { return value.replace(/,/g, ''); }

    $('#amountExpense').on('input', function () {
      const raw = removeCommas($(this).val());
      if (!isNaN(raw) && raw !== '') $(this).val(formatWithCommas(raw));
      else $(this).val('');
    });

    function loadHarambee(targetId, url) {
      $.ajax({
        type: 'GET',
        url: url,
        dataType: 'json',
        success: function (response) {
          let options = '<option value="">Select Harambee</option>';
          $.each(response.data, function(i, h) {
            options += `<option value="${h.harambee_id}">${h.description} - ${h.from_date} - ${h.to_date} - TZS ${h.amount}</option>`;
          });
          $('#' + targetId).html(options);
        },
        error: function () { console.log('Error loading harambee'); }
      });
    }

    function loadExpenseNames(targetType) {
      $.ajax({
        type: 'GET',
        url: `../api/data/expense_names.php?target=${encodeURIComponent(targetType)}`,
        dataType: 'json',
        success: function(response) {
          let options = '<option value="">Select Expense Name</option>';
          if (response.success) {
            $.each(response.data, function(i, expense) {
              options += `<option value="${expense.expense_name_id}">${expense.expense_name}</option>`;
            });
          }
          $('#expenseNameId').html(options);
        },
        error: function() { console.log('Error loading expense names'); }
      });
    }

    $('#targetTypeExpense').change(function() {
      const selectedType = $(this).val();
      if (!selectedType) return;
      loadHarambee('harambeeIdExpense', `../api/data/head_parish_harambee?limit=all&target=${selectedType}`);
      loadExpenseNames(selectedType);
    });

    $('#harambeeExpenseForm').submit(function(event) {
      event.preventDefault();
      const cleanedAmount = removeCommas($('#amountExpense').val());
      $('#amountExpense').val(cleanedAmount);

      const submitBtn = $(this).find('button[type="submit"]');
      submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Recording...');

      $('#responseMessageExpense').html('<div class="response-message"><i class="fas fa-spinner fa-spin icon"></i> Sending Request...</div>');

      let formData = $(this).serialize();

      $.ajax({
        type: 'POST',
        url: '../api/records/record_harambee_expenses.php',
        data: formData,
        dataType: 'json',
        success: function(response) {
          let html = '';
          if (response.success) {
            html = '<div class="response-message success"><i class="fas fa-check-circle icon"></i>' + response.message + '</div>';
            setTimeout(() => {
              $('#harambeeExpenseForm')[0].reset();
              $('#targetTypeExpense').val('').trigger('change');
              $('#harambeeIdExpense').val('').trigger('change');
              $('#expenseNameId').val('').trigger('change');
              $('#responseMessageExpense').html('');
            }, 2000);
          } else {
            html = '<div class="response-message error"><i class="fas fa-times-circle icon"></i>' + response.message + '</div>';
          }
          $('#responseMessageExpense').html(html);
        },
        error: function(xhr, status, error) {
          const errorMessage = xhr.responseJSON?.message || `An error occurred: ${error}`;
          $('#responseMessageExpense').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i>' + errorMessage + '</div>');
        },
        complete: function() { submitBtn.prop('disabled', false).html('Record Harambee Expense'); }
      });
    });

  });
</script>
</body>
</html>
