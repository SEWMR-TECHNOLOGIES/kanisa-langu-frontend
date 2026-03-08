# utils/sms.py
"""SMS sending via Sewmr API — mirrors legacy PHP SewmrSMSClient usage."""
import logging
from typing import List, Optional
import httpx
from sqlalchemy.orm import Session

from utils.encryption import decrypt_data

logger = logging.getLogger(__name__)

SEWMR_BASE_URL = "https://api.sewmrsms.co.tz/api/v1"


class SewmrSMSClient:
    """Lightweight Sewmr SMS client matching the legacy PHP SewmrSMSClient."""

    def __init__(self, api_token: str, sender_id: str = ""):
        self.api_token = api_token
        self.sender_id = sender_id

    def send_quick_sms(
        self,
        message: str,
        recipients: List[str],
        campaign_name: Optional[str] = None,
    ) -> dict:
        """Send SMS to a list of recipients. Returns API response dict."""
        if not recipients or not message:
            return {"success": False, "message": "Missing recipients or message"}

        payload = {
            "source_addr": self.sender_id,
            "message": message,
            "encoding": 0,
            "schedule_time": "",
        }
        if campaign_name:
            payload["campaign_name"] = campaign_name

        # Format recipients as the API expects
        for i, phone in enumerate(recipients):
            payload[f"recipients[{i}][recipient_id]"] = i + 1
            payload[f"recipients[{i}][dest_addr]"] = phone

        headers = {
            "Authorization": f"Bearer {self.api_token}",
            "Accept": "application/json",
        }

        try:
            with httpx.Client(timeout=30) as client:
                resp = client.post(
                    f"{SEWMR_BASE_URL}/send/quick-sms",
                    data=payload,
                    headers=headers,
                )
                return resp.json()
        except Exception as e:
            logger.error(f"SMS send failed: {e}")
            return {"success": False, "message": str(e)}


def get_sms_credentials(db: Session, head_parish_id: int) -> Optional[dict]:
    """Fetch and decrypt SMS API credentials for a head parish.
    Mirrors legacy get_sms_credentials() / getHeadParishSmsInfo()."""
    from models.config import SmsApiConfig

    config = db.query(SmsApiConfig).filter(
        SmsApiConfig.head_parish_id == head_parish_id
    ).first()

    if not config or not config.api_username or not config.api_password:
        return None

    try:
        api_password = decrypt_data(config.api_password)
        api_token = decrypt_data(config.api_token) if config.api_token else None
    except Exception:
        logger.error(f"Failed to decrypt SMS credentials for head_parish_id={head_parish_id}")
        return None

    return {
        "account_name": config.account_name,
        "username": config.api_username,
        "password": api_password,
        "api_token": api_token,
        "sender_id": config.sender_id or "",
    }


def send_sms(
    db: Session,
    head_parish_id: int,
    phone: str,
    message: str,
) -> bool:
    """Send a single SMS using head parish's stored credentials."""
    creds = get_sms_credentials(db, head_parish_id)
    if not creds or not creds.get("api_token"):
        logger.warning(f"No SMS credentials for head_parish_id={head_parish_id}")
        return False

    client = SewmrSMSClient(creds["api_token"], creds["sender_id"])
    response = client.send_quick_sms(message=message, recipients=[phone])

    if not response.get("success", False):
        logger.warning(f"SMS to {phone} failed: {response.get('message', 'Unknown')}")
        return False
    return True


def send_bulk_sms(
    db: Session,
    head_parish_id: int,
    phones: List[str],
    message: str,
) -> bool:
    """Send SMS to multiple recipients."""
    creds = get_sms_credentials(db, head_parish_id)
    if not creds or not creds.get("api_token"):
        return False

    client = SewmrSMSClient(creds["api_token"], creds["sender_id"])
    response = client.send_quick_sms(message=message, recipients=phones)
    return response.get("success", False)


# ── Pre-built SMS templates (Swahili, matching legacy) ─────────────────────

def send_harambee_contribution_sms(
    db: Session,
    head_parish_id: int,
    phone: str,
    recipient_name: str,
    amount: float,
    total_contribution: float,
    target_amount: Optional[float],
    contribution_date: str,
    accountant_phone: str = "",
    harambee_title: str = "",
    is_group: bool = False,
):
    """Send harambee contribution notification SMS (Swahili)."""
    from datetime import date as date_type

    today = date_type.today().isoformat()
    date_text = "Leo tumepokea" if today == contribution_date else f"Tarehe {contribution_date} tulipokea"
    blessing = "awabariki" if is_group else "akubariki"

    msg = ""
    if harambee_title:
        msg += f"{harambee_title.upper()}\n"
    msg += f"Shalom {recipient_name}!\n"

    if target_amount and target_amount > 0:
        target_text = "Ombi letu la Harambee kwenu ni shs." if is_group else "Ombi letu la Harambee ni shs."
        msg += f"{target_text} {target_amount:,.0f}/=\n"

    msg += f"{date_text} {amount:,.0f}/=\n"
    msg += f"Jumla taslimu {total_contribution:,.0f}/=\n"

    if target_amount and target_amount > 0:
        balance = target_amount - total_contribution
        label = "Zidio" if balance < 0 else "Salio"
        msg += f"{label} {abs(balance):,.0f}/=\n"

    msg += f"Mungu {blessing}.\n"
    if accountant_phone:
        msg += f"M/Hazina\n{accountant_phone}\n"

    return send_sms(db, head_parish_id, phone, msg)


def send_harambee_target_update_sms(
    db: Session,
    head_parish_id: int,
    phone: str,
    recipient_name: str,
    old_target: float,
    new_target: float,
    target_difference: float,
    is_mr_and_mrs: bool = False,
):
    """Send harambee target update notification."""
    msg = f"Shalom {recipient_name}!\n"
    msg += f"Ombi lako la Harambee limebadilishwa.\n"
    msg += f"Ombi la awali: {old_target:,.0f}/=\n"
    msg += f"Ombi jipya: {new_target:,.0f}/=\n"
    msg += f"Tofauti: {target_difference:,.0f}/=\n"
    msg += "Mungu akubariki."

    return send_sms(db, head_parish_id, phone, msg)


def send_admin_registration_sms(
    db: Session,
    head_parish_id: int,
    phone: str,
    first_name: str,
    role: str,
    head_parish_name: str,
):
    """Send admin registration SMS notification."""
    role_sw = translate_role_to_swahili(role)
    msg = f"Shalom {first_name},\n"
    msg += f"Umesajiliwa kama {role_sw} wa {head_parish_name} katika mfumo wa Kanisa Langu.\n"
    msg += f"Tumia link hii kuingia: https://kanisalangu.lovable.app/head-parish/sign-in\n\n"
    msg += f"Karibu,\n{head_parish_name}."

    return send_sms(db, head_parish_id, phone, msg)


def translate_role_to_swahili(role: str) -> str:
    """Translate admin role to Swahili (mirrors legacy translateRoleToSwahili)."""
    mapping = {
        "admin": "Msimamizi Mkuu",
        "pastor": "Mchungaji",
        "secretary": "Katibu",
        "chairperson": "Mwenyekiti",
        "clerk": "Karani",
        "accountant": "M/Hazina",
        "evangelist": "Mwinjilisti",
        "elder": "Mzee wa Kanisa",
    }
    return mapping.get(role, "Kiongozi")
