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
    render_header('Revenue Stream Linking - Kanisa Langu');
  ?>
  <style>
      .nav-tabs .nav-link {
        position: relative;
        padding-right: 2.2rem;
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
        <div class="card">
          <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Revenue Stream Linking</h5>

            <ul class="nav nav-tabs" id="mappingTabs" role="tablist">
              <li class="nav-item" role="presentation">
                <a class="nav-link active" id="map-head-parish-tab" data-bs-toggle="tab" href="#map-head-parish" role="tab">
                  Head Parish
                </a>
              </li>
              <li class="nav-item" role="presentation">
                <a class="nav-link" id="map-sub-parish-tab" data-bs-toggle="tab" href="#map-sub-parish" role="tab">
                  Sub Parish
                </a>
              </li>
              <li class="nav-item" role="presentation">
                <a class="nav-link" id="map-community-tab" data-bs-toggle="tab" href="#map-community" role="tab">
                  Community
                </a>
              </li>
              <li class="nav-item" role="presentation">
                <a class="nav-link" id="map-group-tab" data-bs-toggle="tab" href="#map-group" role="tab">
                  Group
                </a>
              </li>
            </ul>

            <div class="tab-content mt-3" id="mappingTabContent">

              <div class="tab-pane fade show active" id="map-head-parish" role="tabpanel">
                <form id="mapHeadParishForm">
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="hpRevenueStream" class="form-label">Revenue Stream</label>
                        <select class="form-select" id="hpRevenueStream" name="revenue_stream_id">
                          <option value="">Select Revenue Stream</option>
                        </select>
                      </div>
                    </div>
                  </div>
                  <button type="submit" class="btn btn-primary w-100">Link to Head Parish</button>
                </form>
              </div>

              <div class="tab-pane fade" id="map-sub-parish" role="tabpanel">
                <form id="mapSubParishForm">
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="mapSubParishId" class="form-label">Sub Parish</label>
                        <select class="form-select" id="mapSubParishId" name="sub_parish_id">
                          <option value="">Select Sub Parish</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="spRevenueStream" class="form-label">Revenue Stream</label>
                        <select class="form-select" id="spRevenueStream" name="revenue_stream_id">
                          <option value="">Select Revenue Stream</option>
                        </select>
                      </div>
                    </div>
                  </div>
                  <button type="submit" class="btn btn-primary w-100">Link to Sub Parish</button>
                </form>
              </div>

              <div class="tab-pane fade" id="map-community" role="tabpanel">
                <form id="mapCommunityForm">
                  <div class="row">
                    <div class="col-lg-4">
                      <div class="mb-3">
                        <label for="mapSubParishIdCom" class="form-label">Sub Parish</label>
                        <select class="form-select" id="mapSubParishIdCom" name="sub_parish_id">
                          <option value="">Select Sub Parish</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-4">
                      <div class="mb-3">
                        <label for="mapCommunityId" class="form-label">Community</label>
                        <select class="form-select" id="mapCommunityId" name="community_id">
                          <option value="">Select Community</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-4">
                      <div class="mb-3">
                        <label for="cmRevenueStream" class="form-label">Revenue Stream</label>
                        <select class="form-select" id="cmRevenueStream" name="revenue_stream_id">
                          <option value="">Select Revenue Stream</option>
                        </select>
                      </div>
                    </div>
                  </div>
                  <button type="submit" class="btn btn-primary w-100">Link to Community</button>
                </form>
              </div>

              <div class="tab-pane fade" id="map-group" role="tabpanel">
                <form id="mapGroupForm">
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="mapGroupId" class="form-label">Group</label>
                        <select class="form-select" id="mapGroupId" name="group_id">
                          <option value="">Select Group</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label for="gpRevenueStream" class="form-label">Revenue Stream</label>
                        <select class="form-select" id="gpRevenueStream" name="revenue_stream_id">
                          <option value="">Select Revenue Stream</option>
                        </select>
                      </div>
                    </div>
                  </div>
                  <button type="submit" class="btn btn-primary w-100">Link to Group</button>
                </form>
              </div>

            </div>

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

    let msgTimer = null;
    
    function showMsg(success, message) {
      if (success) {
        $('#responseMessage').html('<div class="response-message success"><i class="fas fa-check-circle icon"></i>' + message + '</div>');
      } else {
        $('#responseMessage').html('<div class="response-message error"><i class="fas fa-times-circle icon"></i>' + message + '</div>');
      }
    
      if (msgTimer) clearTimeout(msgTimer);
    
      msgTimer = setTimeout(function () {
        $('#responseMessage').html('');
      }, 5000);
    }


    function loadRevenueStreamsAll() {
      return $.ajax({
        type: 'GET',
        url: '../api/data/head_parish_revenue_streams?limit=all&target=main',
        dataType: 'json'
      });
    }

    function loadSubParishes() {
      return $.ajax({
        type: 'GET',
        url: '../api/data/head_parish_sub_parishes?limit=all',
        dataType: 'json'
      });
    }

    function loadCommunities(subParishId) {
      return $.ajax({
        type: 'GET',
        url: '../api/data/head_parish_communities?limit=all',
        data: { sub_parish_id: subParishId },
        dataType: 'json'
      });
    }

    function loadGroups() {
      return $.ajax({
        type: 'GET',
        url: '../api/data/head_parish_groups?limit=all',
        dataType: 'json'
      });
    }

    function fillSelect(selector, data, valueKey, textKey, placeholder) {
      let options = `<option value="">${placeholder}</option>`;
      if (Array.isArray(data)) {
        data.forEach(item => {
          options += `<option value="${item[valueKey]}">${item[textKey]}</option>`;
        });
      }
      $(selector).html(options).trigger('change.select2');
    }

    function submitMap(url, payload) {
      $('#responseMessage').html('<div class="response-message"><i class="fas fa-spinner fa-spin icon"></i> Saving mapping...</div>');
      $.ajax({
        type: 'POST',
        url: url,
        data: payload,
        dataType: 'json',
        success: function (response) {
          showMsg(response.success, response.message);
        },
        error: function (xhr, status, error) {
          const msg = xhr.responseJSON?.message || ('An error occurred: ' + error);
          showMsg(false, msg);
        }
      });
    }

    loadRevenueStreamsAll().done(function(resp){
      const streams = resp && resp.success ? resp.data : [];
      fillSelect('#hpRevenueStream', streams, 'revenue_stream_id', 'revenue_stream_name', 'Select Revenue Stream');
      fillSelect('#spRevenueStream', streams, 'revenue_stream_id', 'revenue_stream_name', 'Select Revenue Stream');
      fillSelect('#cmRevenueStream', streams, 'revenue_stream_id', 'revenue_stream_name', 'Select Revenue Stream');
      fillSelect('#gpRevenueStream', streams, 'revenue_stream_id', 'revenue_stream_name', 'Select Revenue Stream');
    });

    loadSubParishes().done(function(resp){
      const subParishes = resp && resp.success ? resp.data : [];
      fillSelect('#mapSubParishId', subParishes, 'sub_parish_id', 'sub_parish_name', 'Select Sub Parish');
      fillSelect('#mapSubParishIdCom', subParishes, 'sub_parish_id', 'sub_parish_name', 'Select Sub Parish');
    });

    loadGroups().done(function(resp){
      const groups = resp && resp.success ? resp.data : [];
      fillSelect('#mapGroupId', groups, 'group_id', 'group_name', 'Select Group');
    });

    $('#mapSubParishIdCom').on('change', function(){
      const sp = $(this).val();
      fillSelect('#mapCommunityId', [], 'community_id', 'community_name', 'Select Community');
      if (!sp) return;

      loadCommunities(sp).done(function(resp){
        const communities = resp && resp.success ? resp.data : [];
        fillSelect('#mapCommunityId', communities, 'community_id', 'community_name', 'Select Community');
      });
    });

    $('#mapHeadParishForm').on('submit', function(e){
      e.preventDefault();
      const revenue_stream_id = $('#hpRevenueStream').val();
      if (!revenue_stream_id) return showMsg(false, 'Please select revenue stream');
      submitMap('../api/records/link_revenue_stream', { target: 'head-parish', revenue_stream_id: revenue_stream_id });
    });

    $('#mapSubParishForm').on('submit', function(e){
      e.preventDefault();
      const sub_parish_id = $('#mapSubParishId').val();
      const revenue_stream_id = $('#spRevenueStream').val();
      if (!sub_parish_id) return showMsg(false, 'Please select sub parish');
      if (!revenue_stream_id) return showMsg(false, 'Please select revenue stream');
      submitMap('../api/records/link_revenue_stream', { target: 'sub-parish', sub_parish_id: sub_parish_id, revenue_stream_id: revenue_stream_id });
    });

    $('#mapCommunityForm').on('submit', function(e){
      e.preventDefault();
      const sub_parish_id = $('#mapSubParishIdCom').val();
      const community_id = $('#mapCommunityId').val();
      const revenue_stream_id = $('#cmRevenueStream').val();
      if (!sub_parish_id) return showMsg(false, 'Please select sub parish');
      if (!community_id) return showMsg(false, 'Please select community');
      if (!revenue_stream_id) return showMsg(false, 'Please select revenue stream');
      submitMap('../api/records/link_revenue_stream', { target: 'community', sub_parish_id: sub_parish_id, community_id: community_id, revenue_stream_id: revenue_stream_id });
    });

    $('#mapGroupForm').on('submit', function(e){
      e.preventDefault();
      const group_id = $('#mapGroupId').val();
      const revenue_stream_id = $('#gpRevenueStream').val();
      if (!group_id) return showMsg(false, 'Please select group');
      if (!revenue_stream_id) return showMsg(false, 'Please select revenue stream');
      submitMap('../api/records/link_revenue_stream', { target: 'groups', group_id: group_id, revenue_stream_id: revenue_stream_id });
    });

  });
</script>

</body>
</html>
