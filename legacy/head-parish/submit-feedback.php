<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/session_checks.php');
check_session('head_parish_admin_id', '../head-parish/sign-in');
?>
<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('components/header_files.php'); 
    render_header('Help Us Improve - Kanisa Langu');
  ?>
  <style>
    .feedback-form-wrapper {
      /*background-color: #f8f9fa;*/
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    }

    .pulse-button {
      animation: pulse 2s infinite;
    }

    @keyframes pulse {
      0% {
        box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.4);
      }
      70% {
        box-shadow: 0 0 0 10px rgba(13, 110, 253, 0);
      }
      100% {
        box-shadow: 0 0 0 0 rgba(13, 110, 253, 0);
      }
    }

    .feedback-icon {
      font-size: 1.2rem;
      color: #0d6efd;
      margin-right: 6px;
    }

    .response-message {
      margin-top: 15px;
    }
  </style>
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6"
    data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">

    <?php require_once('components/sidebar.php') ?>

    <div class="body-wrapper">
      <?php require_once('components/header.php') ?>

      <div class="container-fluid">
        <div class="card">
          <div class="card-body">
            <h4 class="fw-semibold mb-3">We'd Love Your Feedback</h4>

            <div class="alert alert-info d-flex align-items-start" role="alert">
              <i class="ti ti-message-dots feedback-icon mt-1"></i>
              <div>
                <strong>How can we make Kanisa Langu better for you?</strong><br>
                Tell us what's going well, what's confusing, or anything else on your mind.
              </div>
            </div>

            <!-- Feedback Form -->
            <div class="feedback-form-wrapper">
              <form id="feedbackForm">
                <input type="hidden" id="headParishId" name="head_parish_id" value="<?php echo htmlspecialchars($_SESSION['head_parish_id']); ?>">

                <div class="mb-3">
                  <label for="feedbackType" class="form-label">Feedback Type</label>
                  <select class="form-select" id="feedbackType" name="feedback_type" required>
                    <option value="">Choose an option</option>
                    <option value="suggestion">A Suggestion</option>
                    <option value="complaint">A Problem</option>
                    <option value="question">A Question</option>
                    <option value="report">A Report</option>
                    <option value="other">Something Else</option>
                  </select>
                </div>

                <div class="mb-3">
                  <label for="feedbackSubject" class="form-label">Subject</label>
                  <input type="text" class="form-control" id="feedbackSubject" name="subject" placeholder="Short summary of your feedback" required>
                </div>

                <div class="mb-3">
                  <label for="feedbackMessage" class="form-label">Message</label>
                  <textarea class="form-control" id="feedbackMessage" name="message" rows="5" placeholder="Write your message here..." required></textarea>
                </div>

                <div class="mb-3">
                  <button type="submit" class="btn btn-primary fw-bold pulse-button">
                    <i class="ti ti-send me-1"></i>Send Feedback
                  </button>
                </div>

                <div id="responseMessage"></div>
              </form>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>

  <?php require_once('components/footer_files.php') ?>
  <script>
    $('select').select2({ width: '100%' });

    $(document).ready(function () {
      $('#feedbackForm').on('submit', function (e) {
        e.preventDefault();

        $('#responseMessage').html('<div class="response-message"><i class="fas fa-spinner fa-spin icon"></i> Sending feedback...</div>');

        const feedbackData = {
          feedback_type: $('#feedbackType').val(),
          subject: $('#feedbackSubject').val(),
          message: $('#feedbackMessage').val()
        };

        $.ajax({
          type: 'POST',
          url: '../api/feedback/record_head_parish_feedback.php',
          data: feedbackData,
          dataType: 'json',
          success: function (response) {
            let msgHtml = '';
            if (response.success) {
              msgHtml = '<div class="response-message success alert alert-success"><i class="fas fa-check-circle icon"></i> ' + response.message + '</div>';
              $('#feedbackForm')[0].reset();
            } else {
              msgHtml = '<div class="response-message error alert alert-danger"><i class="fas fa-times-circle icon"></i> ' + response.message + '</div>';
            }
            $('#responseMessage').html(msgHtml);

            // Clear after 3 seconds
            setTimeout(() => {
              $('#responseMessage').fadeOut('slow', function () {
                $(this).html('').show();
              });
            }, 3000);
          },
          error: function (xhr, status, error) {
            $('#responseMessage').html('<div class="response-message alert alert-danger"><i class="fas fa-times-circle icon"></i> An error occurred: ' + error + '</div>');

            setTimeout(() => {
              $('#responseMessage').fadeOut('slow', function () {
                $(this).html('').show();
              });
            }, 3000);
          }
        });
      });
    });
  </script>
</body>
</html>
