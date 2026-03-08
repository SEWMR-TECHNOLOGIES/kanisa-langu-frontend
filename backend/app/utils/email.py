# utils/email.py
"""Email sending utilities for Kanisa Langu — mirrors legacy mail_functions.php."""
import logging
import smtplib
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
from typing import Optional
from datetime import date

logger = logging.getLogger(__name__)

LOGO_URL = "https://kanisalangu.lovable.app/logo.png"
FROM_EMAIL = "info@kanisalangu.sewmrtechnologies.com"
FROM_NAME = "Kanisa Langu"


def _build_html_email(title: str, body_html: str) -> str:
    """Wrap body content in a styled email template."""
    return f"""
    <html>
    <head><title>{title}</title>
    <style>
        body {{ font-family: Arial, sans-serif; background: #f4f4f4; color: #333; margin: 0; padding: 0; }}
        .container {{ max-width: 600px; margin: 20px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }}
        .logo {{ display: block; margin: 0 auto 20px; max-width: 150px; }}
        h1 {{ color: #6c757d; font-size: 24px; text-align: center; margin: 20px 0; }}
        .content {{ font-size: 16px; line-height: 1.6; }}
        .footer {{ text-align: center; margin-top: 20px; padding-top: 10px; border-top: 1px solid #e0e0e0; font-size: 14px; color: #777; }}
    </style>
    </head>
    <body>
        <div class="container">
            <img src="{LOGO_URL}" alt="Kanisa Langu Logo" class="logo"/>
            <h1>{title}</h1>
            <div class="content">{body_html}</div>
            <div class="footer"><p>&copy; {date.today().year} Kanisa Langu. All rights reserved.</p></div>
        </div>
    </body>
    </html>
    """


def send_email(to: str, subject: str, body_html: str, title: str = "") -> bool:
    """Send an HTML email. Uses PHP mail() compatible SMTP or fallback."""
    try:
        msg = MIMEMultipart("alternative")
        msg["From"] = f"{FROM_NAME} <{FROM_EMAIL}>"
        msg["To"] = to
        msg["Subject"] = subject

        html_content = _build_html_email(title or subject, body_html)
        msg.attach(MIMEText(html_content, "html"))

        # Use local SMTP (matching legacy PHP mail() function)
        with smtplib.SMTP("localhost", 25, timeout=10) as server:
            server.sendmail(FROM_EMAIL, [to], msg.as_string())

        return True
    except Exception as e:
        logger.error(f"Email send failed to {to}: {e}")
        return False


def send_password_reset_email(to: str, reset_code: str) -> bool:
    """Send password reset code via email."""
    body = f"""
    <p>You have requested a password reset for your Kanisa Langu account.</p>
    <p>Your reset code is: <strong>{reset_code}</strong></p>
    <p>This code expires in 1 hour.</p>
    <p>If you did not request this password reset, please ignore this email.</p>
    """
    return send_email(to, "Password Reset Request", body, "Password Reset Request")


def send_expense_request_email(
    to: str,
    role_prefix: str,
    role: str,
    message_html: str,
) -> bool:
    """Send expense request notification email to admin."""
    body = f"<p>Dear {role_prefix}{role.capitalize()},</p>{message_html}"
    return send_email(to, "Expense Request Notification", body, "New Expense Request")


def send_feedback_email(
    feedback_type: str,
    subject: str,
    message: str,
    submitted_by: str = "Head Parish Admin",
    to: str = "davidfaustinempinzile@gmail.com",
) -> bool:
    """Send feedback notification to system administrators."""
    body = f"""
    <p><strong>Type:</strong> {feedback_type.capitalize()}</p>
    <p><strong>Subject:</strong> {subject}</p>
    <p><strong>Message:</strong><br/>{message}</p>
    <p><em>Submitted by: {submitted_by}</em></p>
    """
    return send_email(to, f"New Feedback Received: {subject}", body, "New Feedback Submitted")
