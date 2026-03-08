<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
// Ensure the user is authenticated as superadmin
check_session('kanisalangu_admin_id', '../app/sign-in');
?>
<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Manage Praise Songs - Kanisa Langu');
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
      white-space: nowrap; /* Keeps the buttons on one line */
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
            <h5 class="card-title fw-semibold mb-4">Manage Praise Songs</h5>

            <!-- Search Form -->
            <div class="row mb-4">
              <div class="col-md-8">
                <input type="text" id="searchSong" class="form-control" placeholder="Search Praise Songs by Name">
              </div>
              <div class="col-md-4 text-end">
                <button class="btn btn-primary" id="searchBtn">Search</button>
              </div>
            </div>

            <!-- Loading GIF -->
            <div id="loading">
              <img src="../assets/images/gifs/loading.gif" alt="Loading..." height="100">
            </div>

            <!-- Praise Songs Table -->
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Page Number</th>
                    <th>Song Number</th>
                    <th>Praise Song</th>
                    <th class="text-end">Actions</th>
                  </tr>
                </thead>
                <tbody id="songList">
                  <!-- Table rows will be populated here using AJAX -->
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <nav id="paginationNav" aria-label="Praise Song pagination">
                <div class="overflow-auto scroll-sidebar" data-simplebar="">
                    <ul class="pagination justify-content-start">
                        <!-- Pagination buttons will be generated dynamically -->
                    </ul>
                </div>
            </nav>

          </div>
        </div>
      </div>
    </div>
  </div>
  
  <?php require_once('components/footer_files.php') ?>

<script>
$(document).ready(function () {
    const fetchPraiseSongs = (page = 1, query = '') => {
        $('#loading').show(); // Show loading GIF
        $.ajax({
            type: 'GET',
            url: `../api/data/praise_songs?page=${page}&query=${query}`, 
            dataType: 'json',
            success: function (response) {
                const songs = response.data;
                const songList = $('#songList');
                songList.empty();
                let rowIndex = (page - 1) * 10 + 1;
                songs.forEach(song => {
                    songList.append(`
                        <tr data-song-id="${song.id}">
                            <td>${rowIndex++}</td>
                            <td>${song.page_number}</td>
                            <td>${song.song_number}</td>
                            <td>${song.song_name}</td>
                            <td class="text-end">
                                <button class="btn btn-warning btn-sm edit-btn"><i class="fas fa-edit"></i> Edit</button>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i> Delete</button>
                            </td>
                        </tr>
                    `);
                });

                // Handle pagination
                $('#paginationNav .pagination').empty();
                for (let i = 1; i <= response.total_pages; i++) {
                    $('#paginationNav .pagination').append(`
                        <li class="page-item ${i == page ? 'active' : ''}">
                            <a class="page-link" href="#">${i}</a>
                        </li>
                    `);
                }

                $('#loading').hide(); // Hide loading GIF
            },
            error: function (xhr, status, error) {
                console.log('Error fetching songs:', error);
                $('#loading').hide(); // Hide loading GIF even on error
            }
        });
    };

    // Load songs initially
    fetchPraiseSongs();

    // Search functionality
    $('#searchBtn').on('click', function () {
        const query = $('#searchSong').val();
        fetchPraiseSongs(1, query);
    });

    // Attach onchange event to search input
    $('#searchSong').on('input', function () {
        const query = $(this).val();
        fetchPraiseSongs(1, query);
    });

    // Pagination click event
    $(document).on('click', '.page-link', function (e) {
        e.preventDefault();
        const page = $(this).text();
        fetchPraiseSongs(page);
    });
    
    // Edit button click event
    $(document).on('click', '.edit-btn', function () {
        const songId = $(this).closest('tr').data('song-id');
        // Fetch song details for editing
        $.ajax({
            url: `../api/data/praise_songs/${songId}`,
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                const song = response.data;
                // Assuming you have an edit modal or input fields to edit the song
                $('#songId').val(song.id);
                $('#songName').val(song.song_name);
                $('#editSongModal').modal('show'); // Open the edit modal
            }
        });
    });

    // Delete button click event
    $(document).on('click', '.btn-danger', function () {
        const songId = $(this).closest('tr').data('song-id');
        // Handle deletion logic (ask for confirmation before deletion)
        if (confirm('Are you sure you want to delete this song?')) {
            $.ajax({
                url: `../api/data/praise_songs/${songId}`,
                method: 'DELETE',
                success: function (response) {
                    if (response.success) {
                        alert('Song deleted successfully!');
                        fetchPraiseSongs(); // Refresh the list
                    } else {
                        alert('Error deleting song: ' + response.message);
                    }
                },
                error: function () {
                    alert('Error deleting song.');
                }
            });
        }
    });
});
</script>
</body>
</html>
