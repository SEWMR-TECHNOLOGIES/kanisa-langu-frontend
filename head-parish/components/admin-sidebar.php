<!-- Sidebar Start -->
<aside class="left-sidebar">
  <!-- Sidebar scroll-->
  <div>
    <div class="brand-logo d-flex align-items-center justify-content-between">
      <a href="/head-parish/" class="text-nowrap logo-img">
        <img src="/assets/images/logos/dark-logo.svg" width="180" alt="" />
      </a>
      <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
        <i class="ti ti-x fs-8"></i>
      </div>
    </div>
    <!-- Sidebar navigation-->
    <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
      <ul id="sidebarnav">
        <li class="nav-small-cap">
          <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
          <span class="hide-menu">Home</span>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="/head-parish/" aria-expanded="false">
            <span>
              <i class="ti ti-layout-dashboard"></i>
            </span>
            <span class="hide-menu">Dashboard</span>
          </a>
        </li>

        <!-- Administration -->
        <li class="nav-small-cap">
          <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
          <span class="hide-menu">ADMINISTRATION</span>
        </li>

        <li class="sidebar-item has-submenu">
          <a class="sidebar-link" href="#" aria-expanded="false">
            <span>
              <i class="ti ti-building"></i>
            </span>
            <span class="hide-menu">Sub Parishes</span>
          </a>
          <ul class="submenu">
            <li><a href="/head-parish/register-sub-parish">Add Sub Parish</a></li>
            <li><a href="/head-parish/sub-parishes">Manage Sub Parishes</a></li>
          </ul>
        </li>
        <li class="sidebar-item has-submenu">
          <a class="sidebar-link" href="#" aria-expanded="false">
            <span>
              <i class="ti ti-users"></i>
            </span>
            <span class="hide-menu">Communities</span>
          </a>
          <ul class="submenu">
            <li><a href="/head-parish/register-community">Add Community</a></li>
            <li><a href="/head-parish/communities">Manage Communities</a></li>
          </ul>
        </li>
        <li class="sidebar-item has-submenu">
          <a class="sidebar-link" href="#" aria-expanded="false">
            <span>
              <i class="ti ti-users"></i>
            </span>
            <span class="hide-menu">Groups</span>
          </a>
          <ul class="submenu">
            <li><a href="/head-parish/register-group">Add Group</a></li>
            <li><a href="/head-parish/groups">Manage Groups</a></li>
          </ul>
        </li>
        
        <!-- New Roles Section -->
        <li class="nav-small-cap">
          <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
          <span class="hide-menu">ROLES</span>
        </li>

        <li class="sidebar-item has-submenu">
          <a class="sidebar-link" href="#" aria-expanded="false">
            <span>
              <i class="ti ti-shield"></i> <!-- Icon for roles -->
            </span>
            <span class="hide-menu">System Users</span>
          </a>
          <ul class="submenu">
            <li><a href="/head-parish/create-admin">Create User</a></li>
          </ul>
        </li>
        
        <!-- Church Management -->
        <li class="nav-small-cap">
          <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
          <span class="hide-menu">CHURCH MANAGEMENT</span>
        </li>
        
        <li class="sidebar-item has-submenu">
          <a class="sidebar-link" href="#" aria-expanded="false">
            <span>
              <i class="ti ti-users"></i>
            </span>
            <span class="hide-menu">Church Leaders</span>
          </a>
          <ul class="submenu">
            <li><a href="/head-parish/register-church-leader">Register Leader</a></li>
            <li><a href="/head-parish/church-leaders">Manage Leaders</a></li>
          </ul>
        </li>
        
        <li class="sidebar-item has-submenu">
          <a class="sidebar-link" href="#" aria-expanded="false">
            <span>
              <i class="ti ti-users"></i>
            </span>
            <span class="hide-menu">Church Members</span>
          </a>
          <ul class="submenu">
            <li><a href="/head-parish/register-church-member">Register Member</a></li>
            <li><a href="/head-parish/upload-church-members">Upload From File</a></li>
            <li><a href="/head-parish/church-members">Manage Members</a></li>
            <li><a href="/head-parish/church-members-accounts">Active Accounts</a></li>
            <li><a href="/head-parish/download-church-members-list">Download Members List</a></li>
          </ul>
        </li>

        <!-- Member Exclusion Section -->
        <li class="sidebar-item has-submenu">
            <a class="sidebar-link" href="#" aria-expanded="false">
                <span>
                    <i class="ti ti-ban"></i> <!-- Icon for exclusion -->
                </span>
                <span class="hide-menu">Member Exclusion</span>
            </a>
            <ul class="submenu">
                <li><a href="/head-parish/add-exclusion-reason">Add Reason</a></li>
                <li><a href="/head-parish/member-exclusions">View Exclusions</a></li>
                <li><a href="/head-parish/exclude-church-member">Exclude Member</a></li>
                <li><a href="/head-parish/excluded-church-members">Excluded Members</a></li>
            </ul>
        </li>
        <!-- Church Choirs Management -->
        <li class="sidebar-item has-submenu">
          <a class="sidebar-link" href="#" aria-expanded="false">
            <span>
              <i class="ti ti-music"></i>
            </span>
            <span class="hide-menu">Church Choirs</span>
          </a>
          <ul class="submenu">
            <li><a href="/head-parish/register-church-choir">Register Choir</a></li>
            <li><a href="/head-parish/church-choirs">Choirs</a></li>
          </ul>
        </li>
        
        <!-- New Sunday Services Section -->
        <li class="sidebar-item has-submenu">
          <a class="sidebar-link" href="#" aria-expanded="false">
            <span>
              <i class="ti ti-book"></i> <!-- Icon that resembles a Bible or book -->
            </span>
            <span class="hide-menu">Sunday Services</span>
          </a>
          <ul class="submenu">
            <li><a href="/head-parish/set-services-count">Set Services Count</a></li>
            <li><a href="/head-parish/set-service-time">Set Service Time</a></li>
            <li><a href="/head-parish/services">Services Numbers</a></li>
            <li><a href="/head-parish/record-sunday-service">Record Services</a></li>
            <li><a href="/head-parish/sunday-services">View Services</a></li>
          </ul>
        </li>
      
      <!-- Events Section -->
        <li class="nav-small-cap">
          <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
          <span class="hide-menu">EVENTS</span>
        </li>

        <li class="sidebar-item has-submenu">
          <a class="sidebar-link" href="#" aria-expanded="false">
            <span>
              <i class="ti ti-calendar-event"></i> <!-- Event-related icon -->
            </span>
            <span class="hide-menu">Meetings</span>
          </a>
          <ul class="submenu">
            <li><a href="/head-parish/new-meeting">New Meeting</a></li>
            <li><a href="/head-parish/all-meetings">All Meetings</a></li>
          </ul>
        </li>
        <!--Church Events-->
        <li class="sidebar-item has-submenu">
          <a class="sidebar-link" href="#" aria-expanded="false">
            <span>
              <i class="ti ti-calendar-plus"></i> <!-- Icon for Church Events -->
            </span>
            <span class="hide-menu">Church Events</span>
          </a>
          <ul class="submenu">
            <li><a href="/head-parish/new-church-event">Create Event</a></li>
            <li><a href="/head-parish/church-events">All Events</a></li>
          </ul>
        </li>
        <!-- Attendance Section -->
        <li class="sidebar-item has-submenu">
          <a class="sidebar-link" href="#" aria-expanded="false">
            <span>
              <i class="ti ti-user-check"></i> 
            </span>
            <span class="hide-menu">Attendance</span>
          </a>
          <ul class="submenu">
             <li><a href="/head-parish/set-attendance-benchmark">Set Attendance Benchmark</a></li> <!-- New item for recording attendance -->
            <li><a href="/head-parish/record-attendance">Record Attendance</a></li> <!-- New item for recording attendance -->
            <li><a href="/head-parish/view-attendance">View Attendance</a></li> <!-- Option to view recorded attendance -->
          </ul>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="/head-parish/send-push-notification" aria-expanded="false">
            <span>
              <i class="ti ti-bell"></i>
            </span>
            <span class="hide-menu">Send Push Notification</span>
          </a>
        </li>
        <!-- Banking and Finance -->
        <li class="nav-small-cap">
          <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
          <span class="hide-menu">BANKING AND FINANCE</span>
        </li>
        <li class="sidebar-item has-submenu">
          <a class="sidebar-link" href="#" aria-expanded="false">
            <span>
              <i class="ti ti-credit-card"></i>
            </span>
            <span class="hide-menu">Bank Accounts</span>
          </a>
          <ul class="submenu">
            <li><a href="/head-parish/register-bank-account">Add Bank Account</a></li>
            <li><a href="/head-parish/bank-accounts">Manage Bank Accounts</a></li>
            <li><a href="/head-parish/record-parish-transactions">Record Transactions</a></li>
            <li><a href="/head-parish/financial-statement">Financial Statement</a></li>
          </ul>
        </li>

        <!-- Revenues -->
        <li class="nav-small-cap">
          <i class="ti ti-coins nav-small-cap-icon fs-4"></i>
          <span class="hide-menu">REVENUES AND DEBITS</span>
        </li>
        <li class="sidebar-item has-submenu">
          <a class="sidebar-link" href="#" aria-expanded="false">
            <span>
              <i class="ti ti-credit-card"></i>
            </span>
            <span class="hide-menu">Revenues</span>
          </a>
          <ul class="submenu">
            <li><a href="/head-parish/create-revenue-groups">Create Revenue Groups</a></li>
            <li><a href="/head-parish/add-revenue-stream">Add Revenue Stream</a></li>
            <li><a href="/head-parish/revenue-streams">Manage Revenue Streams</a></li>
            <li><a href="/head-parish/map-revenue-streams">Map Revenue Streams</a></li>
            <li><a href="/head-parish/link-revenue-stream">Link Revenue Stream</a></li>
            <li><a href="/head-parish/map-program-to-revenue-stream">Map Program to Revenue Streams</a></li>
            <li><a href="/head-parish/revenue-streams-map">Revenue Streams Map</a></li>
            <li><a href="/head-parish/record-revenue">Record Revenue</a></li>
            <li><a href="/head-parish/verify-revenues">Verify Revenues</a></li>
            <li><a href="/head-parish/envelope-usage">Envelope Usage</a></li>
            <li><a href="/head-parish/set-annual-revenue-target">Set Collection Targets</a></li>
            <li><a href="/head-parish/set-revenue-stream-target">Set Revenue Stream Targets</a></li>
            <li><a href="/head-parish/distribute-annual-revenue-target">Distribute Revenue Targets</a></li>
          </ul>
        </li>
        <!-- Debits and Loans -->
        <li class="sidebar-item has-submenu">
          <a class="sidebar-link" href="#" aria-expanded="false">
            <span>
              <i class="ti ti-report-money"></i>
            </span>
            <span class="hide-menu">Debits and Loans</span>
          </a>
          <ul class="submenu">
            <li><a href="/head-parish/record-debit">Record Debit</a></li>
            <li><a href="/head-parish/debits">All Debits</a></li>
          </ul>
        </li>
        <!-- Expenses -->
        <li class="nav-small-cap">
          <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
          <span class="hide-menu">BUDGETING & EXPENSES</span>
        </li>
        
        <!-- Budgeting Section -->
        <li class="sidebar-item has-submenu">
            <a class="sidebar-link" href="#" aria-expanded="false">
                <span>
                    <i class="ti ti-chart-line"></i> <!-- Graph or trend icon -->
                </span>
                <span class="hide-menu">Budgeting</span>
            </a>
            <ul class="submenu">
                <li><a href="/head-parish/ogo">OGO</a></li>
            </ul>
        </li>
        
        <li class="sidebar-item has-submenu">
          <a class="sidebar-link" href="#" aria-expanded="false">
            <span>
              <i class="ti ti-wallet"></i>
            </span>
            <span class="hide-menu">Expense Management</span>
          </a>
          <ul class="submenu">
            <li><a href="/head-parish/create-expense-groups">Create Expense Groups</a></li>
            <li><a href="/head-parish/record-expense-names">Record Expense Names</a></li>
            <li><a href="/head-parish/set-annual-expense-budget">Set Annual Expense Budgets</a></li>
            <li><a href="/head-parish/distribute-annual-expense-budget">Distribute Expense Budgets</a></li>
            <li><a href="/head-parish/set-expense-budget">Allocate Expense Budgets</a></li>
          </ul>
        </li>
        
        <li class="sidebar-item has-submenu">
          <a class="sidebar-link" href="#" aria-expanded="false">
            <span>
              <i class="ti ti-receipt"></i>
            </span>
            <span class="hide-menu">Expense Requests</span>
          </a>
          <ul class="submenu">
            <li><a href="/head-parish/make-expense-request">Make Expense Request</a></li>
            <li><a href="/head-parish/grouped-requests">Grouped Requests</a></li>
            <li><a href="/head-parish/expense-requests">Expense Requests</a></li>
          </ul>
        </li>

        <!-- Head Parish Assets Section -->
        <li class="nav-small-cap">
          <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
          <span class="hide-menu">HEAD PARISH ASSETS</span>
        </li>
        
        <li class="sidebar-item has-submenu">
          <a class="sidebar-link" href="#" aria-expanded="false">
            <span>
              <i class="ti ti-building-bank"></i> <!-- Asset-related icon -->
            </span>
            <span class="hide-menu">Assets Management</span>
          </a>
          <ul class="submenu">
            <li><a href="/head-parish/add-asset">Add New Asset</a></li>
            <li><a href="/head-parish/set-asset-status">Set Asset Status</a></li>
            <li><a href="/head-parish/record-asset-revenue">Record Asset Revenue</a></li>
            <li><a href="/head-parish/record-asset-expenses">Record Asset Expenses</a></li>
          </ul>
        </li>


        <!-- Development Programs -->
        <li class="nav-small-cap">
            <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
            <span class="hide-menu">CHURCH PROGRAMS</span>
        </li>
        
        <!-- Harambee Section -->
        <li class="sidebar-item has-submenu">
            <a class="sidebar-link" href="#" aria-expanded="false">
                <span>
                    <i class="ti ti-flag"></i>
                </span>
                <span class="hide-menu">Harambee</span>
            </a>
            <ul class="submenu">
                <li><a href="/head-parish/record-harambee-classes">Record Harambee Class</a></li>
                <li><a href="/head-parish/harambee-classes">Classes</a></li>
                <li><a href="/head-parish/record-harambee">Record New Harambee</a></li>
                <li><a href="/head-parish/harambee">Harambee Details</a></li>
                <li><a href="/head-parish/distribute-harambee">Distribute Harambee</a></li>
                <li><a href="/head-parish/harambee-distribution">Distribution Status</a></li>
                <li><a href="/head-parish/record-harambee-target">Set Member Target</a></li>
                <li><a href="/head-parish/upload-harambee-targets">Upload Targets From File</a></li>
                <li><a href="/head-parish/record-harambee-contribution">Record Contribution</a></li>
                <li><a href="/head-parish/harambee-contribution">Contributions</a></li>
                <li><a href="/head-parish/send-harambee-contribution-sms">Send Contribution SMS</a></li>
                <li><a href="/head-parish/send-harambee-summary-message">Send Summary SMS</a></li>
                <li><a href="/head-parish/send-harambee-contribution-notification">Send Notification</a></li>
                <li><a href="/head-parish/generate-harambee-letter">Harambee Letter</a></li>
                <li><a href="/head-parish/harambee-letter-status">Letter Status</a></li>
                <li><a href="/head-parish/non-harambee-members">Non Harambee Participants</a></li>
                <li><a href="/head-parish/record-harambee-expenses">Record Expenses</a></li>
            </ul>
        </li>

        <!-- Harambee Groups Section -->
        <li class="sidebar-item has-submenu">
            <a class="sidebar-link" href="#" aria-expanded="false">
                <span>
                    <i class="ti ti-users"></i> <!-- You can change the icon here -->
                </span>
                <span class="hide-menu">Harambee Groups</span>
            </a>
            <ul class="submenu">
                <li><a href="/head-parish/create-harambee-group">Create Group</a></li>
                <li><a href="/head-parish/assign-member-to-group">Assign Member to Group</a></li>
                <li><a href="/head-parish/harambee-groups">All Groups</a></li>
            </ul>
        </li>

        <!-- Harambee Exclusion Section -->
        
        <li class="sidebar-item has-submenu">
            <a class="sidebar-link" href="#" aria-expanded="false">
                <span>
                    <i class="ti ti-ban"></i> 
                </span>
                <span class="hide-menu">Harambee Exclusion</span>
            </a>
            <ul class="submenu">
                <li><a href="/head-parish/add-harambee-exclusion-reason">Add Reason</a></li>
                <li><a href="/head-parish/harambee-exclusions">View Exclusions</a></li>
                <li><a href="/head-parish/exclude-church-member-from-harambee">Exclude Member</a></li>
                <li><a href="/head-parish/excluded-church-members-from-harambee">Excluded Members</a></li>
            </ul>
        </li>

    
        <!-- Envelope Section -->
        <li class="sidebar-item has-submenu">
            <a class="sidebar-link" href="#" aria-expanded="false">
                <span>
                    <i class="ti ti-mail"></i> <!-- You can choose a suitable icon -->
                </span>
                <span class="hide-menu">Envelope</span>
            </a>
            <ul class="submenu">
                <li><a href="/head-parish/set-annual-envelope-target">Set Parish Target</a></li>
                <li><a href="/head-parish/distribute-annual-envelope-target">Distribute Target</a></li>
                <li><a href="/head-parish/set-envelope-target">Set Member Target</a></li>
                <li><a href="/head-parish/record-envelope-contribution">Record Contribution</a></li>
                <li><a href="/head-parish/upload-envelope-data">Upload From File</a></li>
                <li><a href="/head-parish/manage-envelopes">Manage Envelopes</a></li>
                <li><a href="/head-parish/envelope-usage-summary">Envelope Usage</a></li>
            </ul>
        </li>


        <!-- Reports -->
        <li class="nav-small-cap">
          <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
          <span class="hide-menu">REPORTS, TRENDS AND INSIGHTS</span>
        </li>
        <!-- Finances Section (Newly Added) -->
        <li class="sidebar-item has-submenu">
            <a class="sidebar-link" href="#" aria-expanded="false">
                <span>
                    <i class="ti ti-wallet"></i> <!-- Financial Icon -->
                </span>
                <span class="hide-menu">Finances</span>
            </a>
            <ul class="submenu">
                <li><a href="/head-parish/download-revenue-breakdown">Revenue Breakdown</a></li>
                <li><a href="/head-parish/download-revenue-statement">Revenue Statement</a></li>
                <!-- Add other finance-related items here if needed -->
            </ul>
        </li>
        <!-- Harambee Section -->
        <li class="sidebar-item has-submenu">
            <a class="sidebar-link" href="#" aria-expanded="false">
                <span>
                    <i class="ti ti-report"></i>
                </span>
                <span class="hide-menu">Harambee</span>
            </a>
            <ul class="submenu">
                <li><a href="/head-parish/head-parish-harambee-report">Head Parish Report</a></li>
                <li><a href="/head-parish/harambee-contribution-summary">Contribution Summary</a></li>
                <li><a href="/head-parish/harambee-contribution-report">Contribution Report</a></li>
                <li><a href="/head-parish/harambee-groups-report">Harambee Groups Report</a></li>
                <li><a href="/head-parish/harambee-community-report">Community Report</a></li>
                <li><a href="/head-parish/contribution-report-by-class">Contribution by Class</a></li>
                <li><a href="/head-parish/harambee-letters-report">Letters Reports</a></li>
                <li><a href="/head-parish/clerks-harambee-report">Clerks Report</a></li>
            </ul>
        </li>
        <!-- Budgeting Section -->
        <li class="sidebar-item has-submenu">
            <a class="sidebar-link" href="#" aria-expanded="false">
                <span>
                    <i class="ti ti-chart-line"></i> <!-- Graph or trend icon -->
                </span>
                <span class="hide-menu">Revenues & Budgeting</span>
            </a>
            <ul class="submenu">
                <li><a href="/head-parish/ogo">OGO</a></li>
                <li><a href="/head-parish/revenue-group-report">Revenue Groups Report</a></li>
                <li><a href="/head-parish/expense-group-report">Expense Groups Report</a></li>
            </ul>
        </li>
        
        <!-- Third Parties Configuration -->
        <li class="nav-small-cap">
          <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
          <span class="hide-menu">THIRD PARTIES CONFIGURATION</span>
        </li>
        <li class="sidebar-item has-submenu">
          <a class="sidebar-link" href="#" aria-expanded="false">
            <span>
              <i class="ti ti-wallet"></i>
            </span>
            <span class="hide-menu">Payment Gateway Wallets</span>
          </a>
          <ul class="submenu">
            <li><a href="/head-parish/register-payment-gateway-wallet">Record Wallet</a></li>
            <li><a href="/head-parish/payment-gateway-wallets">Manage Wallets</a></li>
          </ul>
        </li>
        <li class="sidebar-item has-submenu">
          <a class="sidebar-link" href="#" aria-expanded="false">
            <span>
              <i class="ti ti-settings"></i>
            </span>
            <span class="hide-menu">SMS API Gateway</span>
          </a>
          <ul class="submenu">
            <li><a href="/head-parish/record-sms-api-info">Record API Info</a></li>
          </ul>
        </li>
        <!-- Auth -->
        <li class="nav-small-cap">
          <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
          <span class="hide-menu">AUTH</span>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="/head-parish/sign-out" aria-expanded="false">
            <span>
              <i class="ti ti-logout"></i>
            </span>
            <span class="hide-menu">Sign Out</span>
          </a>
        </li>
      </ul>
    </nav>
    <!-- End Sidebar navigation -->
  </div>
  <!-- End Sidebar scroll-->
</aside>
