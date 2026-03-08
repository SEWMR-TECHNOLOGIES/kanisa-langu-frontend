<!-- components/sms_stats.php -->
<style>
  /* Limit card padding to reduce height */
  .sms-stats-card .card-body {
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
  }

  /* Smaller badges and tighter spacing */
  .sms-stats-card small {
    font-size: 0.7rem;
  }

  .sms-stats-card span.badge {
    font-size: 1.1rem; /* smaller than fs-5 */
    padding: 0.25rem 1rem; /* less vertical padding */
  }

  /* Remove excessive margins */
  .sms-stats-card div > small {
    margin-bottom: 0.15rem;
    display: block;
  }

  /* Reduce space above the message */
  #smsMessage {
    margin-top: 0.5rem !important;
  }
</style>

<div class="card mb-4 sms-stats-card" style="width: 100%;">
  <div class="card-header fw-bold py-2">
    SMS Usage Statistics
  </div>
  <div class="card-body d-flex justify-content-around align-items-center flex-wrap">
    <div>
      <small class="text-muted">Purchased</small>
      <span id="smsPurchased" class="badge bg-primary px-3">-</span>
    </div>
    <div>
      <small class="text-muted">Used</small>
      <span id="smsUsed" class="badge bg-warning text-dark px-3">-</span>
    </div>
    <div>
      <small class="text-muted">Balance</small>
      <span id="smsBalance" class="badge bg-success px-3">-</span>
    </div>
  </div>
  <div id="smsMessage" class="text-center text-danger"></div>
</div>
