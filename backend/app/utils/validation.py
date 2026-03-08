# utils/validation.py
"""Input validation helpers mirroring legacy PHP validation_functions.php."""
import re
from datetime import date, datetime
from typing import Optional


def is_valid_email(email: str) -> bool:
    pattern = r"^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$"
    return bool(re.match(pattern, email))


def is_valid_phone(phone: str) -> bool:
    """Tanzanian phone: 07xx or 06xx, 10 digits total."""
    return bool(re.match(r"^0(7[0-9]|6[0-9])\d{7}$", phone))


def normalize_phone(phone: str) -> str:
    """Replace leading 0 with 255 for international format."""
    if phone and phone.startswith("0"):
        return "255" + phone[1:]
    return phone


def validate_date_format(date_str: str, fmt: str = "%Y-%m-%d") -> Optional[date]:
    try:
        return datetime.strptime(date_str, fmt).date()
    except (ValueError, TypeError):
        return None


def validate_age(dob: date, min_age: int = 5) -> bool:
    today = date.today()
    age = today.year - dob.year - ((today.month, today.day) < (dob.month, dob.day))
    return age >= min_age
