<?php
// mail_functions.php

/**
 * Send an email with HTML content using PHP's mail function.
 *
 * @param string $to The recipient's email address.
 * @param string $subject The subject of the email.
 * @param string $message The HTML body of the email.
 * @param string $headers Optional. Additional headers, like "From", "Cc", etc.
 * @return bool Returns true if the mail was successfully accepted for delivery, false otherwise.
 */
function sendEmail($to, $subject, $message, $headers = '') {
    // Validate email address
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    // Define the content type as HTML
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Kanisa Langu <info@kanisalangu.sewmrtechnologies.com>\r\n";


    // CSS Styling for the email
    $css = '
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .logo {
            display: block;
            margin: 0 auto 20px;
            max-width: 150px;
        }
        h1 {
            color: #6c757d;
            font-size: 24px;
            text-align: center;
            margin: 20px 0;
        }
        .content {
            font-size: 16px;
            line-height: 1.6;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e0e0e0;
            font-size: 14px;
            color: #777;
        }
        @media (max-width: 600px) {
            .container {
                padding: 10px;
                margin: 10px 15px;
            }
            .logo {
                max-width: 200px;
            }
            h1 {
                font-size: 20px;
            }
            .content {
                font-size: 14px;
            }
        }
    </style>';

    // Add logo to the email content
    $logoUrl = 'https://kanisalangu.sewmrtechnologies.com/logo.png'; 
    $message = '
    <html>
    <head>
        <title>Password Reset Request</title>
        ' . $css . '
    </head>
    <body>
        <div class="container">
            <div class="content">
                <img src="' . $logoUrl . '" alt="Kanisa Langu Logo" class="logo"/>
                <h1>Password Reset Request</h1>
                ' . $message . '
                <p>If you did not request this password reset, please ignore this email.</p>
            </div>
            <div class="footer">
                <p>&copy; ' . date('Y') . ' Kanisa Langu. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>';

    // Attempt to send the email
    $success = mail($to, $subject, $message, $headers);

    return $success;
}
?>
