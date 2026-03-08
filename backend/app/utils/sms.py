# utils/sms.py
"""SMS sending via Sewmr API — complete port of all legacy PHP SMS functions."""
import logging
from typing import List, Optional
from datetime import date
import httpx
from sqlalchemy.orm import Session

from utils.encryption import decrypt_data
from utils.helpers import (
    format_amount, format_phone_display, get_member_details, get_member_full_name,
    get_harambee_accountant, get_harambee_details, get_accountant_phone_by_head_parish,
    get_head_parish_name, get_head_parish_secretary_phone, get_harambee_group_info,
    fetch_member_envelope_data, get_revenue_stream_name, translate_role_to_swahili,
    get_sms_credentials as _get_sms_creds_helper,
)

logger = logging.getLogger(__name__)

SEWMR_BASE_URL = "https://api.sewmrsms.co.tz/api/v1"


class SewmrSMSClient:
    """Lightweight Sewmr SMS client matching legacy PHP SewmrSMSClient."""

    def __init__(self, api_token: str, sender_id: str = ""):
        self.api_token = api_token
        self.sender_id = sender_id

    def send_quick_sms(self, message: str, recipients: List[str],
                       campaign_name: Optional[str] = None) -> dict:
        if not recipients or not message:
            return {"success": False, "message": "Missing recipients or message"}

        payload = {
            "sender_id": self.sender_id,
            "message": message,
            "recipients": "\n".join(recipients),
            "schedule": False,
        }
        if campaign_name:
            payload["schedule_name"] = campaign_name

        headers = {
            "Authorization": f"Bearer {self.api_token}",
            "Content-Type": "application/json",
        }
        try:
            with httpx.Client(timeout=30) as client:
                resp = client.post(f"{SEWMR_BASE_URL}/sms/quick-send", json=payload, headers=headers)
                return resp.json()
        except Exception as e:
            logger.error(f"SMS send failed: {e}")
            return {"success": False, "message": str(e)}


def get_sms_credentials(db: Session, head_parish_id: int) -> Optional[dict]:
    """Fetch and decrypt SMS API credentials for a head parish."""
    row = db.execute(
        __import__("sqlalchemy").text("""
            SELECT account_name, api_username, api_password, api_token, sender_id
            FROM head_parish_sms_api_info WHERE head_parish_id = :hpid LIMIT 1
        """),
        {"hpid": head_parish_id}
    ).first()
    if not row or not row[1] or not row[2]:
        return None
    try:
        api_password = decrypt_data(row[2])
        api_token = decrypt_data(row[3]) if row[3] else None
    except Exception:
        logger.error(f"Failed to decrypt SMS credentials for head_parish_id={head_parish_id}")
        return None
    return {
        "account_name": row[0], "username": row[1],
        "password": api_password, "api_token": api_token,
        "sender_id": row[4] or "",
    }


def send_sms(db: Session, head_parish_id: int, phone: str, message: str) -> bool:
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


def send_bulk_sms(db: Session, head_parish_id: int, phones: List[str], message: str) -> bool:
    """Send SMS to multiple recipients."""
    creds = get_sms_credentials(db, head_parish_id)
    if not creds or not creds.get("api_token"):
        return False
    client = SewmrSMSClient(creds["api_token"], creds["sender_id"])
    response = client.send_quick_sms(message=message, recipients=phones)
    return response.get("success", False)


# ── SMS Templates (complete port from legacy) ──────────────────────────────

def send_harambee_contribution_sms(db: Session, amount: float, member: dict,
                                    contribution_date: str, target: str, harambee_id: int) -> bool:
    """Send harambee contribution notification SMS (full version with completion logic)."""
    head_parish_id = member.get("head_parish_id", 0)
    harambee_details_data = get_harambee_details(db, harambee_id, target)
    harambee_title = ""
    if harambee_details_data and harambee_details_data.get("description"):
        harambee_title = harambee_details_data["description"].upper() + "\n"

    accountant = get_harambee_accountant(db, target, harambee_id, "harambee")
    accountant_phone = accountant.get("phone", "")

    first_name = member.get("first_name", "[No First Name]")
    group_name = member.get("group_name")
    phone = member.get("phone", "")
    if not phone:
        return False
    total_contribution = member.get("total_contribution", 0.0)
    target_amount = member.get("target_amount")

    today = date.today().isoformat()
    date_text = "Leo tumepokea" if today == contribution_date else f"Tarehe {contribution_date} tulipokea"
    recipient = group_name or first_name
    target_text = "Ombi letu la Harambee kwenu ni shs. " if group_name else "Ombi letu la Harambee ni shs. "
    blessing = "awabariki" if group_name else "akubariki"

    # Dynamic pronouns
    sadaka = "zenu" if group_name else "yako"
    maliza = "mmemaliza" if group_name else "umemaliza"
    uwiano = "wenu" if group_name else "wako"
    pokee = "mpokee" if group_name else "upokee"
    mnavyo = "mnavyomtumikia" if group_name else "unavyomtumikia"
    afya = "awajalie" if group_name else "akujalie"
    kazi = "zenu" if group_name else "yako"
    ziada = "mkiwa" if group_name else "ukiwa"

    if target_amount and target_amount != 0:
        prefix = (f"Shalom {recipient}!\n{target_text}{format_amount(target_amount)}/=\n"
                  f"{date_text} {format_amount(amount)}/=\n"
                  f"Jumla taslimu {format_amount(total_contribution)}/=\n")

        if total_contribution == target_amount:
            msg = (f"{prefix}!\nKwa sadaka {sadaka} ya leo, {maliza} uwiano {uwiano}.\n"
                   f"Ofisi ya Mchungaji Kiongozi na M/Kiti wa Harambee, tunaomba {pokee} Shukrani zetu kwa jinsi {mnavyo} Mungu kwa moyo.\n"
                   f"Mungu {blessing}, {afya} afya njema, na abariki kazi za mikono {kazi}.\n"
                   f"M/Hazina\n{accountant_phone}\n")
        elif total_contribution > target_amount:
            extra = total_contribution - target_amount
            msg = (f"{prefix}\nKwa sadaka {sadaka} ya leo, {maliza} uwiano {uwiano} {ziada} na ziada ya  "
                   f"{format_amount(extra)}/=\n"
                   f"Ofisi ya Mchungaji Kiongozi na M/Kiti wa Harambee, tunaomba {pokee} Shukrani zetu kwa jinsi {mnavyo} Mungu kwa moyo.\n"
                   f"Mungu {blessing}, {afya} afya njema, na abariki kazi za mikono {kazi}.\n"
                   f"M/Hazina\n{accountant_phone}\n")
        else:
            balance = target_amount - total_contribution
            label = "Zidio" if balance < 0 else "Salio"
            msg = (f"Shalom {recipient}!\n{target_text}{format_amount(target_amount)}/=\n"
                   f"{date_text} {format_amount(amount)}/=\n"
                   f"Jumla taslimu {format_amount(total_contribution)}/=\n"
                   f"{label} {format_amount(abs(balance))}/=\n"
                   f"Mungu {blessing}.\nM/Hazina\n{accountant_phone}\n")
    else:
        msg = (f"Shalom {recipient}!\n{date_text} {format_amount(amount)}/=\n"
               f"Jumla taslimu {format_amount(total_contribution)}/=\n"
               f"Mungu akubariki.\nM/Hazina\n{accountant_phone}\n")

    final_message = harambee_title + msg
    return send_sms(db, head_parish_id, phone, final_message)


def send_envelope_contribution_sms(db: Session, member_id: int, amount: float, contribution_date: str) -> bool:
    """Send envelope contribution SMS."""
    year = int(contribution_date.split("-")[0])
    envelope_data = fetch_member_envelope_data(db, member_id, year)
    member = get_member_details(db, member_id)
    if not member:
        return False

    head_parish_id = member.get("head_parish_id", 0)
    accountant = get_accountant_phone_by_head_parish(db, head_parish_id)
    accountant_phone = accountant.get("phone", "")
    first_name = (member.get("first_name") or "Mpendwa").title()
    phone = member.get("phone", "")
    if not phone:
        return False

    today = date.today().isoformat()
    date_text = "Leo tumepokea" if today == contribution_date else f"Tarehe {contribution_date} tulipokea"
    target = envelope_data["yearly_envelope_target"]
    total = envelope_data["total_envelope_contribution"]
    balance = target - total

    msg = f"Shalom {first_name}!\n"
    if target > 0:
        msg += f"Ahadi yako ya Bahasha ni: {format_amount(target)}/=\n"
    else:
        msg += "Unakumbushwa kuweka ahadi ya Sadaka ya Bahasha.\n"
    msg += f"{date_text} {format_amount(amount)}/=\n"
    msg += f"Jumla taslimu {format_amount(total)}/=\n"
    if target > 0:
        label = "Zidio" if balance < 0 else "Salio"
        msg += f"{label} {format_amount(abs(balance))}/=\n"
    msg += f"Mungu akubariki.\nM/Hazina\n{accountant_phone}\n"

    return send_sms(db, head_parish_id, phone, msg)


def send_offering_sms(db: Session, head_parish_id: int, member_id: int,
                       revenue_stream_id: int, amount: float) -> bool:
    """Send offering receipt SMS."""
    member = get_member_details(db, member_id)
    if not member:
        return False
    rs_name = get_revenue_stream_name(db, head_parish_id, revenue_stream_id) or "Sadaka"
    phone = member.get("phone", "")
    name = member.get("first_name", "Mpendwa")
    msg = f"Shalom {name},\nTumepokea sadaka yako ya {rs_name} Shs. {format_amount(amount)}/=.\nMungu akubariki."
    return send_sms(db, head_parish_id, phone, msg)


def send_harambee_target_sms(db: Session, member_id: int, amount: float, target: str,
                              harambee_id: int, target_type: str = "individual",
                              group_name: str = None) -> bool:
    """Send harambee target set notification SMS."""
    member = get_member_details(db, member_id)
    if not member:
        return False
    phone = member.get("phone", "")
    recipient = group_name if (target_type == "group" and group_name) else (member.get("first_name") or "Mpendwa")
    thanks = "Tunawashukuru" if (target_type == "group" and group_name) else "Tunakushukuru"
    head_parish_id = member.get("head_parish_id", 0)
    accountant = get_harambee_accountant(db, target, harambee_id, "harambee")
    accountant_phone = accountant.get("phone", "")
    msg = (f"Shalom {recipient}!\n{thanks} kwa kupokea ombi letu la Harambee Shs. {format_amount(amount)}/=\n"
           f"Mungu akubariki.\nM/Hazina\n{accountant_phone}")
    return send_sms(db, head_parish_id, phone, msg)


def send_harambee_target_update_notification(db: Session, member_id: int, current_target: float,
                                              new_target: float, target: str, harambee_id: int,
                                              is_mr_and_mrs: bool = False, mr_and_mrs_name: str = None,
                                              target_difference: float = 0) -> bool:
    """Send harambee target update SMS."""
    member = get_member_details(db, member_id)
    if not member:
        return False
    phone = member.get("phone", "")
    head_parish_id = member.get("head_parish_id", 0)
    identifier = mr_and_mrs_name if is_mr_and_mrs else (member.get("first_name") or "").capitalize()
    welcome = "Mungu awabariki" if is_mr_and_mrs else "Mungu akubariki"
    accountant = get_harambee_accountant(db, target, harambee_id, "harambee")
    accountant_phone = accountant.get("phone", "")
    msg = (f"Shalom {identifier}!\n{welcome} kwa nyongeza ya Ahadi ya Harambee Shs. "
           f"{format_amount(target_difference)}/=\nM/Hazina\n{accountant_phone}")
    return send_sms(db, head_parish_id, phone, msg)


def notify_harambee_group_members_by_sms(db: Session, group_target: float,
                                          total_group_contributions: float, group_name: str,
                                          harambee_description: str, group_member: dict,
                                          contributing_member: dict, amount: float,
                                          contribution_date: str, target: str, harambee_id: int,
                                          is_mr_and_mrs: bool = False, mr_and_mrs_name: str = None) -> bool:
    """Notify all harambee group members of a contribution."""
    phone = group_member.get("phone", "")
    head_parish_id = group_member.get("head_parish_id", 0)
    if not phone:
        return False

    accountant = get_harambee_accountant(db, target, harambee_id, "harambee")
    accountant_phone = accountant.get("phone", "")

    today = date.today().isoformat()
    if contribution_date == today:
        date_text = "Leo"
        extra_text = "wametoa" if is_mr_and_mrs else "ametoa"
    else:
        date_text = f"Tarehe {contribution_date}"
        extra_text = "walitoa" if is_mr_and_mrs else "alitoa"

    identifier = mr_and_mrs_name if is_mr_and_mrs else (contributing_member.get("first_name") or "").capitalize()
    balance = group_target - total_group_contributions
    label = "Zidio Shs." if balance < 0 else "Salio Shs."

    msg = (f"Shalom {group_name}!\n"
           f"Ombi letu la Harambee kwenu ni Shs. {format_amount(group_target)}/=\n"
           f"{date_text} {identifier} {extra_text} {format_amount(amount)}/=\n"
           f"Jumla ya Taslimu Shs. {format_amount(total_group_contributions)}/=\n"
           f"{label} {format_amount(abs(balance))}/=\n"
           f"Mungu awabariki.\nM/Hazina\n{accountant_phone}\n")
    return send_sms(db, head_parish_id, phone, msg)


def notify_member_assignment_by_sms(db: Session, member_id: int, harambee_group_id: int,
                                     target: str, is_mr_and_mrs: bool = False,
                                     mr_and_mrs_name: str = None) -> bool:
    """Send SMS when member is assigned to harambee group."""
    member = get_member_details(db, member_id)
    if not member:
        return False
    group_info = get_harambee_group_info(db, target, harambee_group_id)
    if not group_info:
        return False

    head_parish_id = member.get("head_parish_id", 0)
    accountant = get_harambee_accountant(db, target, harambee_group_id, "harambee_group")
    accountant_phone = accountant.get("phone", "")
    first_name = (member.get("first_name") or "").capitalize()
    identifier = mr_and_mrs_name if is_mr_and_mrs else first_name
    welcome = "Mmeunganishwa" if is_mr_and_mrs else "Umeunganishwa"

    msg = (f"Shalom {identifier}!\n{welcome} kwenye kundi la Harambee: {group_info['group_name']}.\n"
           f"Lengo la kundi: {group_info['harambee_group_target']}/=\n"
           f"Mungu akubariki.\nM/Hazina \n{accountant_phone}")
    return send_sms(db, head_parish_id, member.get("phone", ""), msg)


def send_harambee_reminder(db: Session, member_id: int) -> bool:
    """Send harambee reminder SMS."""
    member = get_member_details(db, member_id)
    if not member or not member.get("phone"):
        return False
    first_name = (member.get("first_name") or "").capitalize()
    envelope_number = member.get("envelope_number")
    msg = f"Shalom {first_name}!\n"
    msg += "Tunaendelea kukukaribisha kutoa Sadaka yako ya Harambee.\n"
    if envelope_number:
        msg += f"Taja namba yako ya bahasha {envelope_number} kwa karani.\n"
    msg += "Mungu akubariki.\n"
    return send_sms(db, member.get("head_parish_id", 0), member["phone"], msg)


def send_church_member_otp(db: Session, member: dict, otp: str, request_type: str) -> bool:
    """Send OTP SMS for registration or password reset."""
    head_parish_id = member.get("head_parish_id", 0)
    phone = member.get("phone", "")
    first_name = (member.get("first_name") or "").capitalize()

    if request_type == "registration":
        message = (f"Shalom {first_name}! To complete your registration, use the code {otp}. "
                   "This code expires soon. Do not share it with anyone.")
    elif request_type == "reset":
        message = (f"We've received a request to reset your password. Use {otp} to proceed. "
                   "If you didn't request this, ignore this message.")
    else:
        return False

    return send_sms(db, head_parish_id, phone, message)


def send_church_member_registration_message(db: Session, member: dict) -> bool:
    """Send registration confirmation SMS."""
    head_parish_id = member.get("head_parish_id", 0)
    phone = member.get("phone", "")
    first_name = (member.get("first_name") or "").title()
    envelope_number = member.get("envelope_number")
    secretary_phone = get_head_parish_secretary_phone(db, head_parish_id)
    secretary_info = f"\nKatibu\n{secretary_phone}" if secretary_phone else ""

    if not envelope_number:
        msg = f"Shalom {first_name}!\nUmesajiliwa kwenye Mfumo wetu wa Kanisa.\nMungu akubariki.{secretary_info}"
    else:
        msg = (f"Shalom {first_name}!\nUmesajiliwa kwenye Mfumo wetu wa Kanisa, "
               f"kwa Bahasha Na. {envelope_number}\nMungu akubariki.{secretary_info}")
    return send_sms(db, head_parish_id, phone, msg)


def send_church_member_envelope_update_message(db: Session, member: dict,
                                                old_envelope: str, new_envelope: str = None) -> bool:
    """Send envelope number update SMS."""
    head_parish_id = member.get("head_parish_id", 0)
    phone = member.get("phone", "")
    first_name = (member.get("first_name") or "").title()
    secretary_phone = get_head_parish_secretary_phone(db, head_parish_id)
    secretary_info = f"\nKatibu\n{secretary_phone}" if secretary_phone else ""

    if not new_envelope or new_envelope == old_envelope:
        msg = f"Shalom {first_name}!\nTaarifa zako zimefanyiwa marekebisho.\nMungu akubariki.{secretary_info}"
    else:
        msg = (f"Shalom {first_name}!\nNamba yako ya Bahasha imebadilishwa kutoka {old_envelope} "
               f"hadi {new_envelope}.\nMungu akubariki.{secretary_info}")
    return send_sms(db, head_parish_id, phone, msg)


def send_admin_registration_sms(db: Session, head_parish_id: int, admin: dict) -> bool:
    """Send admin registration SMS notification."""
    first_name = (admin.get("first_name") or "").upper()
    phone = admin.get("phone", "")
    role = translate_role_to_swahili(admin.get("role", "admin"))
    hp_name = get_head_parish_name(db, head_parish_id) or "Parokia"

    msg = (f"Shalom {first_name},\nUmesajiliwa kama {role} wa {hp_name} katika mfumo wa Kanisa Langu.\n"
           f"Tumia link hii kuingia: https://kanisalangu.lovable.app/head-parish/sign-in\n\nKaribu,\n{hp_name}.")
    return send_sms(db, head_parish_id, phone, msg)
