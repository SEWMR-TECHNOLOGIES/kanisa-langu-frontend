# utils/email.py
"""Email sending utilities — complete port of legacy mail_functions.php + helpers.php email functions."""
import logging
import smtplib
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
from datetime import date
from typing import Optional

from sqlalchemy.orm import Session
from utils.helpers import (
    get_admin_email_by_target_and_role, get_admin_prefix, format_amount,
    format_phone_display, get_harambee_accountant, get_harambee_details,
)

logger = logging.getLogger(__name__)

LOGO_URL = "https://kanisalangu.lovable.app/logo.png"
FROM_EMAIL = "info@kanisalangu.sewmrtechnologies.com"
FROM_NAME = "Kanisa Langu"


def _build_html_email(title: str, body_html: str) -> str:
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
        .recipient-name {{ font-weight: bold; color: #2c3e50; }}
        strong {{ color: #e74c3c; }}
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
    """Send an HTML email."""
    try:
        msg = MIMEMultipart("alternative")
        msg["From"] = f"{FROM_NAME} <{FROM_EMAIL}>"
        msg["To"] = to
        msg["Subject"] = subject
        html_content = _build_html_email(title or subject, body_html)
        msg.attach(MIMEText(html_content, "html"))
        with smtplib.SMTP("localhost", 25, timeout=10) as server:
            server.sendmail(FROM_EMAIL, [to], msg.as_string())
        return True
    except Exception as e:
        logger.error(f"Email send failed to {to}: {e}")
        return False


def send_password_reset_email(to: str, reset_code: str) -> bool:
    body = f"""
    <p>You have requested a password reset for your Kanisa Langu account.</p>
    <p>Your reset code is: <strong>{reset_code}</strong></p>
    <p>This code expires in 1 hour.</p>
    <p>If you did not request this password reset, please ignore this email.</p>
    """
    return send_email(to, "Password Reset Request", body, "Password Reset Request")


def send_expense_request_notification(db: Session, target: str, role: str,
                                       message_html: str, admin_id: int,
                                       subject: str = "Expense Request Notification",
                                       title: str = "New Expense Request") -> bool:
    """Send expense request notification email to admin. Mirrors sendExpenseRequestNotification."""
    admin_email = get_admin_email_by_target_and_role(db, target, role, admin_id)
    if not admin_email:
        return False
    prefix = get_admin_prefix(target)
    body = f"<p>Dear {prefix}{role.capitalize()},</p>{message_html}"
    return send_email(admin_email, subject, body, title)


def send_feedback_email(feedback_type: str, subject: str, message: str,
                         submitted_by: str = "Head Parish Admin",
                         to: str = "davidfaustinempinzile@gmail.com") -> bool:
    body = f"""
    <p><strong>Type:</strong> {feedback_type.capitalize()}</p>
    <p><strong>Subject:</strong> {subject}</p>
    <p><strong>Message:</strong><br/>{message}</p>
    <p><em>Submitted by: {submitted_by}</em></p>
    """
    return send_email(to, f"New Feedback Received: {subject}", body, "New Feedback Submitted")


def send_harambee_contribution_email(db: Session, amount: float, member: dict,
                                      contribution_date: str, target: str, harambee_id: int,
                                      default_email: str = "info@kanisalangu.sewmrtechnologies.com") -> bool:
    """Send harambee contribution receipt email. Mirrors sendHarambeeContributionEmail."""
    first_name = member.get("first_name", "")
    group_name = member.get("group_name")
    member_email = member.get("email")
    total_contribution = member.get("total_contribution", 0)
    target_amount = member.get("target_amount")

    balance = (target_amount - total_contribution) if target_amount is not None else None
    recipient = group_name or first_name

    accountant = get_harambee_accountant(db, target, harambee_id, "harambee")
    accountant_phone = accountant.get("phone", "")

    today = date.today().isoformat()
    date_text = "Leo tumepokea" if today == contribution_date else f"Tarehe {contribution_date} tulipokea"
    target_text = "Ombi letu la Harambee kwenu ni Shs." if group_name else "Ombi letu la Harambee ni Shs."
    blessing = "awabariki" if group_name else "akubariki"

    body = f'<p>Shalom <span class="recipient-name">{{recipient}}</span>,</p>'
    if target_amount is not None:
        body += f"<p>{{target_text}} <strong>{{format_amount(target_amount)}}/=</strong></p>"
    body += f"<p>{{date_text}} <strong>{{format_amount(amount)}}/=</strong></p>"
    body += f"<p>Jumla taslimu hadi sasa: <strong>{{format_amount(total_contribution)}}/=</strong></p>"
    if balance is not None:
        label = "Zidio" if balance < 0 else "Salio"
        body += f"<p>{{label}}: <strong>{{format_amount(abs(balance))}}/=</strong></p>"
    body += f"<p>Mungu {{blessing}}.</p>"
    if accountant_phone:
        body += f"<p><br>M/Hazina<br>{{accountant_phone}}</p>"

    to_addr = member_email or default_email
    return send_email(to_addr, "Mchango wa Harambee umepokelewa", body, "Mchango wa Harambee Umepokelewa")
