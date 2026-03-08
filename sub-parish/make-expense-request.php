<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Make Expense Request - Kanisa Langu');
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
              <h5 class="card-title fw-semibold mb-4">Make Expense Request</h5>

              <ul class="nav nav-tabs" id="expenseBudgetsTabs" role="tablist">
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

              <div class="tab-content mt-3" id="expenseBudgetTabContent">
                <!-- Head Parish Expense Request Form -->
                <div class="tab-pane fade show active" id="head-parish" role="tabpanel">
                  <form id="headParishForm" autocomplete="off">
                    <input type="hidden" name="target" value="head_parish">
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="expenseGroupHP" class="form-label">Expense Group</label>
                          <select class="form-select" id="expenseGroupHP" name="expense_group_id">
                            <option value="">Select Expense Group</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="expenseNameHP" class="form-label">Expense Name</label>
                          <select class="form-select" id="expenseNameHP" name="expense_name_id">
                            <option value="">Select Expense Name</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="budgetedAmountHP" class="form-label">Request Amount</label>
                          <input type="number" class="form-control" id="budgetedAmountHP" name="budgeted_amount" placeholder="Request Amount" min="0.01" step="0.01">
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="budgetDescriptionHP" class="form-label">Request Description</label>
                          <input type="text" class="form-control" id="budgetDescriptionHP" name="budget_description" placeholder="Request Description">
                        </div>
                      </div>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="addToContainer('head-parish')">Add to Container</button>
                    <button type="button" class="btn btn-info mx-2" onclick="showContainer('head-parish')">Show Head Parish Items</button>
                  </form>
                </div>

                <!-- Sub Parish Expense Request Form -->
                <div class="tab-pane fade" id="sub-parish" role="tabpanel">
                  <form id="subParishForm" autocomplete="off">
                    <input type="hidden" name="target" value="sub_parish">
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="subParishId" class="form-label">Sub Parish</label>
                          <select class="form-select" id="subParishId" name="sub_parish_id">
                            <option value="">Select Sub Parish</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="expenseGroupSP" class="form-label">Expense Group</label>
                          <select class="form-select" id="expenseGroupSP" name="expense_group_id">
                            <option value="">Select Expense Group</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="expenseNameSP" class="form-label">Expense Name</label>
                          <select class="form-select" id="expenseNameSP" name="expense_name_id">
                            <option value="">Select Expense Name</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="budgetedAmountSP" class="form-label">Request Amount</label>
                          <input type="number" class="form-control" id="budgetedAmountSP" name="budgeted_amount" placeholder="Request Amount" min="0.01" step="0.01">
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="budgetDescriptionSP" class="form-label">Request Description</label>
                          <input type="text" class="form-control" id="budgetDescriptionSP" name="budget_description" placeholder="Request Description">
                        </div>
                      </div>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="addToContainer('sub-parish')">Add to Container</button>
                    <button type="button" class="btn btn-info mx-2" onclick="showContainer('sub-parish')">Show Sub Parish Items</button>
                  </form>
                </div>

                <!-- Community Expense Request Form -->
                <div class="tab-pane fade" id="community" role="tabpanel">
                  <form id="communityForm" autocomplete="off">
                    <input type="hidden" name="target" value="community">
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="subParishIdCom" class="form-label">Sub Parish</label>
                          <select class="form-select" id="subParishIdCom" name="sub_parish_id">
                            <option value="">Select Sub Parish</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="communityId" class="form-label">Community</label>
                          <select class="form-select" id="communityId" name="community_id">
                            <option value="">Select Community</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="expenseGroupCom" class="form-label">Expense Group</label>
                          <select class="form-select" id="expenseGroupCom" name="expense_group_id">
                            <option value="">Select Expense Group</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="expenseNameCom" class="form-label">Expense Name</label>
                          <select class="form-select" id="expenseNameCom" name="expense_name_id">
                            <option value="">Select Expense Name</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="budgetedAmountCom" class="form-label">Request Amount</label>
                          <input type="number" class="form-control" id="budgetedAmountCom" name="budgeted_amount" placeholder="Request Amount" min="0.01" step="0.01">
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="budgetDescriptionCom" class="form-label">Request Description</label>
                          <input type="text" class="form-control" id="budgetDescriptionCom" name="budget_description" placeholder="Request Description">
                        </div>
                      </div>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="addToContainer('community')">Add to Container</button>
                    <button type="button" class="btn btn-info mx-2" onclick="showContainer('community')">Show Community Items</button>
                  </form>
                </div>

                <!-- Group Expense Request Form -->
                <div class="tab-pane fade" id="group" role="tabpanel">
                  <form id="groupForm" autocomplete="off">
                    <input type="hidden" name="target" value="group">
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="groupId" class="form-label">Group</label>
                          <select class="form-select" id="groupId" name="group_id">
                            <option value="">Select Group</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="expenseGroupG" class="form-label">Expense Group</label>
                          <select class="form-select" id="expenseGroupG" name="expense_group_id">
                            <option value="">Select Expense Group</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="expenseNameG" class="form-label">Expense Name</label>
                          <select class="form-select" id="expenseNameG" name="expense_name_id">
                            <option value="">Select Expense Name</option>
                            <!-- Options populated by AJAX -->
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="budgetedAmountG" class="form-label">Request Amount</label>
                          <input type="number" class="form-control" id="budgetedAmountG" name="budgeted_amount" placeholder="Request Amount" min="0.01" step="0.01">
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="budgetDescriptionG" class="form-label">Request Description</label>
                          <input type="text" class="form-control" id="budgetDescriptionG" name="budget_description" placeholder="Request Description">
                        </div>
                      </div>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="addToContainer('group')">Add to Container</button>
                    <button type="button" class="btn btn-info mx-2" onclick="showContainer('group')">Show Group Items</button>
                  </form>
                </div>
              </div>
              <div id="responseMessage"></div>
            </div>
          </div>
        </div>
      </div>
  </div>

<!-- Container Modal -->
<div class="modal fade" id="containerModal" tabindex="-1" aria-labelledby="containerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="containerModalLabel">Expense Request Items</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="container-items"></div>
                <div id="responseMessageModal"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" onclick="submitRequest()">Submit Expense Request</button>
            </div>
        </div>
    </div>
</div>



 <?php require_once('components/footer_files.php') ?>

</body>
<script>
  $(document).ready(function () {
    // Initialize Select2 for all select elements
    $('select').select2({
      width: '100%'
    });
    // Keep track of the current target
    let currentTarget = '';

    // Load sub-parishes
    function loadSubParishes() {
      $.ajax({
        type: 'GET',
        url: '../api/data/head_parish_sub_parishes?limit=all',
        dataType: 'json',
        success: function (response) {
          let options = '<option value="">Select Sub Parish</option>';
          $.each(response.data, function (index, subParish) {
            options += '<option value="' + subParish.sub_parish_id + '">' + subParish.sub_parish_name + '</option>';
          });
          $('#subParishId, #subParishIdHP, #subParishIdCom').html(options); // Populate all relevant selects
        },
        error: function (xhr, status, error) {
          console.log('Error loading sub-parishes:', error);
        }
      });
    }

    // Load expense groups based on the form context
    function loadExpenseGroups(targetId, url, params = {}) {
      $.ajax({
        type: 'GET',
        url: url,
        data: params,
        dataType: 'json',
        success: function (response) {
          let options = '<option value="">Select Expense Group</option>';
          $.each(response.data, function (index, group) {
            options += '<option value="' + group.expense_group_id + '">' + group.expense_group_name + '</option>';
          });
          $(targetId).html(options);
        },
        error: function (xhr, status, error) {
          console.log('Error loading expense groups:', error);
        }
      });
    }

    // Load expense groups for Head Parish on page load
    loadExpenseGroups('#expenseGroupHP', '../api/data/expense_groups', { target: 'head-parish', limit: 'all' });

    // Load communities based on selected sub-parish
    function loadCommunities(subParishId) {
      $.ajax({
        type: 'GET',
        url: '../api/data/head_parish_communities?limit=all',
        data: { sub_parish_id: subParishId },
        dataType: 'json',
        success: function (response) {
          let options = '<option value="">Select Community</option>';
          $.each(response.data, function (index, community) {
            options += '<option value="' + community.community_id + '">' + community.community_name + '</option>';
          });
          $('#communityId').html(options);
        },
        error: function (xhr, status, error) {
          console.log('Error loading communities:', error);
        }
      });
    }

    // Load groups
    function loadGroups() {
      $.ajax({
        type: 'GET',
        url: '../api/data/head_parish_groups?limit=all',
        dataType: 'json',
        success: function (response) {
          let options = '<option value="">Select Group</option>';
          $.each(response.data, function (index, group) {
            options += '<option value="' + group.group_id + '">' + group.group_name + '</option>';
          });
          $('#groupId').html(options);
        },
        error: function (xhr, status, error) {
          console.log('Error loading groups:', error);
        }
      });
    }

    // Event handlers for loading expense groups and communities
    $('#subParishId').on('change', function () {
      const subParishId = $(this).val();
      loadExpenseGroups('#expenseGroupSP', '../api/data/expense_groups', { target: 'sub-parish', sub_parish_id: subParishId, limit: 'all' });
    });

    $('#groupId').on('change', function () {
      const groupId = $(this).val();
      loadExpenseGroups('#expenseGroupG', '../api/data/expense_groups', { target: 'group', group_id: groupId, limit: 'all' });
    });

    $('#subParishIdCom').on('change', function () {
      loadCommunities($(this).val());
    });

    $('#subParishIdCom, #communityId').on('change', function () {
      const subParishId = $('#subParishIdCom').val();
      const communityId = $('#communityId').val();

      // Check if both Sub Parish and Community are selected
      if (subParishId && communityId) {
        loadExpenseGroups('#expenseGroupCom', '../api/data/expense_groups', {
          target: 'community',
          sub_parish_id: subParishId,
          community_id: communityId,
          limit: 'all'
        });
      }
    });

    // Load expense names based on selected expense group
    function loadExpenseNames(expenseGroupId, target, elementId) {
      $.ajax({
        type: 'GET',
        url: '../api/data/expense_names',
        data: { target: target, expense_group_id: expenseGroupId },
        dataType: 'json',
        success: function (response) {
          let options = '<option value="">Select Expense Name</option>';
          $.each(response.data, function (index, expense) {
            options += '<option value="' + expense.expense_name_id + '">' + expense.expense_name + '</option>';
          });
          $(elementId).html(options);  // Populate the relevant dropdown
        },
        error: function (xhr, status, error) {
          console.log('Error loading expense names:', error);
        }
      });
    }

    // Attach change event listeners to each expense group dropdown
    $('#expenseGroupHP').on('change', function () {
      const expenseGroupId = $(this).val();
      if (expenseGroupId) {
        loadExpenseNames(expenseGroupId, 'head-parish', '#expenseNameHP');
      }
    });

    $('#expenseGroupSP').on('change', function () {
      const expenseGroupId = $(this).val();
      if (expenseGroupId) {
        loadExpenseNames(expenseGroupId, 'sub-parish', '#expenseNameSP');
      }
    });

    $('#expenseGroupCom').on('change', function () {
      const expenseGroupId = $(this).val();
      if (expenseGroupId) {
        loadExpenseNames(expenseGroupId, 'community', '#expenseNameCom');
      }
    });

    $('#expenseGroupG').on('change', function () {
      const expenseGroupId = $(this).val();
      if (expenseGroupId) {
        loadExpenseNames(expenseGroupId, 'group', '#expenseNameG');
      }
    });

    // Initial data loading
    loadSubParishes();
    loadGroups();
  });

// Mapping of targets to form IDs
const formMapping = {
    'head-parish': 'headParishForm',
    'sub-parish': 'subParishForm',
    'community': 'communityForm',
    'group': 'groupForm'
};

// Initialize containers for each target type
const containers = {
    "head-parish": [],
    "sub-parish": [],
    "community": [],
    "group": [],
};

// Function to add items to the container based on the target
function addToContainer(target) {
    const formId = formMapping[target]; // Get the corresponding form ID
    const form = document.getElementById(formId);
    
    // Map of form elements based on target
    const expenseGroupMap = {
        'head-parish': form.expenseGroupHP,
        'sub-parish': form.expenseGroupSP,
        'community': form.expenseGroupCom,
        'group': form.expenseGroupG
    };
    
    const expenseNameMap = {
        'head-parish': form.expenseNameHP,
        'sub-parish': form.expenseNameSP,
        'community': form.expenseNameCom,
        'group': form.expenseNameG
    };

    const expenseGroupId = form.expense_group_id.value;
    const expenseGroupName = expenseGroupMap[target] ? expenseGroupMap[target].options[expenseGroupMap[target].selectedIndex].text : '';
    const expenseNameId = form.expense_name_id.value;
    const expenseRequestName = expenseNameMap[target] ? expenseNameMap[target].options[expenseNameMap[target].selectedIndex].text : '';
    const budgetedAmount = parseFloat(form.budgeted_amount.value);
    const budgetDescription = form.budget_description.value;

    // Validation checks
    const errors = [];

    // Common validations
    if (!expenseGroupId) errors.push('Please select a valid Expense Group.');
    if (!expenseNameId) errors.push('Please select a valid Expense Name.');
    if (isNaN(budgetedAmount) || budgetedAmount <= 0) errors.push('Please enter a valid budgeted amount.');
    if (!budgetDescription.trim()) errors.push('Please enter a budget description.');

    // Target-specific validations
    if (target === 'sub-parish') {
        const subParishId = form.sub_parish_id.value;
        if (!subParishId) errors.push('Sub Parish is required.');
    } else if (target === 'community') {
        const subParishId = form.sub_parish_id.value;
        const communityId = form.community_id.value;
        if (!subParishId) errors.push('Sub Parish is required.');
        if (!communityId) errors.push('Community is required.');
    } else if (target === 'group') {
        const groupId = form.group_id.value;
        if (!groupId) errors.push('Group is required.');
    }

    let item;

    // Create item structure based on target if no errors
    if (errors.length === 0) {
        item = {
            target, // Add target to the item
            expenseGroupId,
            expenseGroupName,
            expenseNameId,
            expenseRequestName,
            requestAmount: budgetedAmount,
            requestDescription: budgetDescription,
        };

        // Additional fields based on the target
        if (target === 'sub-parish') {
            const subParishId = form.sub_parish_id.value;
            const subParishName = form.subParishId.options[form.subParishId.selectedIndex].text;
            item.subParishId = subParishId;
            item.subParishName = subParishName;
        } else if (target === 'community') {
            const subParishId = form.sub_parish_id.value;
            const subParishName = form.subParishIdCom.options[form.subParishIdCom.selectedIndex].text;
            const communityId = form.community_id.value;
            const communityName = form.communityId.options[form.communityId.selectedIndex].text;
            item.subParishId = subParishId;
            item.subParishName = subParishName;
            item.communityId = communityId;
            item.communityName = communityName;
        } else if (target === 'group') {
            const groupId = form.group_id.value;
            const groupName = form.groupId.options[form.groupId.selectedIndex].text;
            item.groupId = groupId;
            item.groupName = groupName;
        }

        // Check for target conflicts first
        const existingItems = containers[target];
        // Check if the item already exists in the container
        const exists = existingItems.some(existingItem =>
            existingItem.expenseNameId === item.expenseNameId && existingItem.requestAmount === item.requestAmount
        );

        if (exists) {
            showMessage('This item already exists in the container.', 'error','#responseMessage');
        } else {
            // Add the item to the corresponding container
            containers[target].push(item);
            console.log('Current items in the container:', containers[target]);
            showMessage('Item added to container!', 'success','#responseMessage');
            
            // Reset the form after adding the item
            resetForm(form);
        }
    } else {
        // Show errors in response div
        showMessage(errors.join('<br>'), 'error','#responseMessage');
    }
}

// Function to reset the form fields
function resetForm(form) {
    form.reset(); // Resets all form fields to their initial values
    // If you have specific select elements, you may want to reset them to the first option:
    for (const key in form.elements) {
        const element = form.elements[key];
        if (element.tagName === 'SELECT') {
            element.selectedIndex = 0; 
        }
    }
}

    // Function to remove an item from the container
    function removeFromContainer(target, index) {
        containers[target].splice(index, 1);
        showMessage('Item removed from container!', 'success','#responseMessageModal');
        showContainer(target); 
    }
    
    // Updated showMessage function to accept a target selector
    function showMessage(message, type, targetSelector) {
        const responseDiv = $(targetSelector);
        let messageHtml = `<div class="response-message ${type}"><i class="${type === 'success' ? 'fas fa-check-circle' : 'fas fa-times-circle'} icon"></i>${message}</div>`;
        responseDiv.html(messageHtml);
    
        // Clear the message after 3 seconds
        setTimeout(() => {
            responseDiv.html(''); // Clear the message
        }, 3000); // 3000 milliseconds = 3 seconds
    }


    // Function to show items in the container
    function showContainer(target) {
        currentTarget = target; 
        const items = containers[target];
        const containerDiv = document.getElementById('container-items');
        containerDiv.innerHTML = ''; // Clear previous items

        if (items.length === 0) {
            containerDiv.innerHTML = '<p>No items in the container.</p>';
        } else {
            items.forEach((item, index) => {
                const itemHtml = `
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">${item.expenseGroupName}</h5>
                            <p class="card-text">Request Name: ${item.expenseRequestName}</p>
                            <p class="card-text">Amount: <strong>${item.requestAmount.toFixed(2)}</strong></p>
                            <p class="card-text">Description: ${item.requestDescription}</p>
                            ${item.subParishName ? `<p class="card-text">Sub Parish: ${item.subParishName}</p>` : ''}
                            ${item.communityName ? `<p class="card-text">Community: ${item.communityName}</p>` : ''}
                            ${item.groupName ? `<p class="card-text">Group: ${item.groupName}</p>` : ''}
                            <button class="btn btn-danger" onclick="removeFromContainer('${target}', ${index})">Remove</button>
                        </div>
                    </div>
                `;
                containerDiv.innerHTML += itemHtml;
            });
        }
        
    showModal();
    }

    function showModal() {
        $('#containerModal').modal('show');
    }
    
function closeModal() {
        $('#containerModal').modal('hide');
    }

// Function to get the current local date and time in 'YYYY-MM-DD HH:MM:SS' format
function getLocalDateTime() {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0'); // Months are zero-based
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');

    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
}

// Function to submit the expense request
function submitRequest() {
    if (!currentTarget) {
        showMessage('No target selected for submission.', 'error', '#responseMessageModal');
        return;
    }

    const itemsToSubmit = containers[currentTarget];
    if (itemsToSubmit.length === 0) {
        showMessage('No items to submit.', 'error', '#responseMessageModal');
        return;
    }
    
    // Get the local datetime
    const requestDatetime = getLocalDateTime();

    // Prepare data for submission
    const payload = {
        target: currentTarget,
        items: itemsToSubmit.map(item => ({
            ...item,
            request_datetime: requestDatetime // Add the local request datetime to each item
        }))
    };

    submitForm(payload);
}

// Function to submit the form dynamically
function submitForm(payload) {
    console.log(payload);
    $.ajax({
        type: 'POST',
        url: '../api/records/submit_expense_request',
        data: JSON.stringify(payload),
        contentType: 'application/json',
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                showMessage(response.message, 'success', '#responseMessageModal');
                // Empty the container
                containers[currentTarget] = [];
                setTimeout(function () {
                    $('#containerModal').modal('hide'); // Close modal on success
                    // location.reload();
                }, 3000); // Close after 3 seconds
            } else {
                showMessage(response.message, 'error', '#responseMessageModal');
            }
        },
        error: function (xhr, status, error) {
            showMessage('An error occurred: ' + error, 'error', '#responseMessageModal');
        }
    });
}


</script>
</html>
