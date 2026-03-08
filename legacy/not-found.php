<!doctype html>
<html lang="en">

<head>
  <?php 
    require_once('app/components/header_files.php'); 
    render_header('404 - Page Not Found - Kanisa Langu');
  ?>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-size: cover;
      margin: 0;
      padding: 0;
      color: #fff;
    }
    .page-wrapper {
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      height: 100vh;
      text-align: center;
      backdrop-filter: blur(5px);
    }
    .error-container {
      max-width: 600px;
      padding: 40px;
      background: rgba(255, 255, 255, 0.9);
      border-radius: 12px;
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
      margin: 20px;
      animation: fadeIn 1s ease-in-out;
    }
    .icon {
      font-size: 100px;
      color: #dc3545;
      margin-bottom: 20px;
      animation: bounce 1s infinite;
    }
    .error-container h1 {
      font-size: 100px;
      margin: 0;
      color: #dc3545;
      animation: pulse 1.5s infinite;
    }
    .error-container h2 {
      font-size: 24px;
      margin: 10px 0;
      color: #333;
    }
    .error-container p {
      font-size: 18px;
      margin: 20px 0;
      color: #666;
    }
    .error-container a {
      text-decoration: none;
      color: #ffffff;
      background: #007bff;
      font-weight: bold;
      border: 2px solid #007bff;
      padding: 12px 24px;
      border-radius: 5px;
      transition: background 0.3s, border-color 0.3s;
    }
    .error-container a:hover {
      background: #0056b3;
      border-color: #0056b3;
    }
    .separator {
      margin: 40px 0;
      border-top: 2px solid #ddd;
      width: 100%;
    }
    .footer {
      font-size: 14px;
      color: #999;
    }
    .footer p {
      margin: 0;
    }
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    @keyframes bounce {
      0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
      40% { transform: translateY(-30px); }
      60% { transform: translateY(-15px); }
    }
    @keyframes pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.1); }
      100% { transform: scale(1); }
    }
  </style>
</head>

<body>
  <!-- Body Wrapper -->
  <div class="page-wrapper">
    <div class="error-container">
      <div class="icon">
        <i class="fas fa-exclamation-triangle"></i>
      </div>
      <h1>404</h1>
      <h2>Page Not Found</h2>
      <p>Sorry, the page you are looking for does not exist. It might have been moved or deleted.</p>
      <a href="/">Return to Homepage</a>
    </div>
    <div class="separator"></div>
    <div class="footer">
      <p>&copy; <span id="currentYear"></span> Kanisa Langu. All rights reserved.</p>
    </div>
  </div>

  <?php require_once('app/components/footer_files.php') ?>

  <script>
    // Set the current year in the footer
    document.getElementById('currentYear').textContent = new Date().getFullYear();
  </script>
</body>

</html>
