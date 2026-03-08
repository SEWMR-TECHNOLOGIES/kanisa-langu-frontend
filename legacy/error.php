<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('app/components/header_files.php'); 
    render_header('Error - Kanisa Langu');
  ?>
  <style>
    body {
      font-family: 'Arial', sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f9f9f9;
      color: #333;
    }
    .error-wrapper {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      text-align: center;
      background-color: #f0f0f0;
      padding: 20px;
    }
    .error-container {
      background: #fff;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      max-width: 500px;
      width: 100%;
    }
    .error-container h1 {
      font-size: 36px;
      color: #dc3545;
      margin-bottom: 10px;
    }
    .error-container p {
      font-size: 16px;
      color: #666;
      margin-bottom: 20px;
    }
    .error-container a {
      display: inline-block;
      text-decoration: none;
      color: #fff;
      background: #007bff;
      padding: 10px 20px;
      border-radius: 5px;
      font-size: 14px;
      transition: background-color 0.3s ease;
    }
    .error-container a:hover {
      background-color: #0056b3;
    }
    .footer {
      margin-top: 20px;
      font-size: 14px;
      color: #999;
    }
  </style>
</head>

<body>
  <div class="error-wrapper">
    <div class="error-container">
      <h1>Error</h1>
      <p>
        <?php 
          // Display custom error message if provided
          echo isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'An unexpected error occurred. Please try again later.';
        ?>
      </p>
      <a href="/">Return to Homepage</a>
      <div class="footer">
        <p>&copy; <span id="currentYear"></span> Kanisa Langu. All rights reserved.</p>
      </div>
    </div>
  </div>

  <?php require_once('app/components/footer_files.php'); ?>

  <script>
    // Set the current year in the footer
    document.getElementById('currentYear').textContent = new Date().getFullYear();
  </script>
</body>

</html>
