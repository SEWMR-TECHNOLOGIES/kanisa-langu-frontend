# utils/helpers.py
"""Core business logic helpers for Kanisa Langu.
Mirrors all key functions from legacy/utils/helpers.php (10,000+ lines)
consolidated into efficient, reusable Python functions."""

import logging
import random
import string
from datetime import date, datetime, timedelta
from decimal import Decimal
from math import ceil
from typing import Any, Dict, List, Optional, Tuple

from sqlalchemy import func, text, and_, or_
from sqlalchemy.orm import Session

logger = logging.getLogger(__name__)

# ── Constants ──────────────────────────────────────────────────────────────

MANAGEMENT_LEVELS = ("head_parish", "sub_parish", "community", "group")
PAYMENT_METHODS = ("Cash", "Bank Transfer", "Mobile Payment", "Card")
HARAMBEE_RESPONSIBILITIES = (
    "M/Kiti - Mtaa", "M/Kiti - Kamati", "M/M/Kiti",
    "Katibu", "M/Hazina", "Mjumbe",
)


# ── Response helpers ───────────────────────────────────────────────────────

def success_response(message: str = "Success", data: Any = None) -> dict:
    return {"success": True, "message": message, "data": data}


def error_response(message: str = "An error occurred", data: Any = None) -> dict:
    return {"success": False, "message": message, "data": data}


def paginate(query, page: int = 1, limit: int = 20):
    """SQLAlchemy query pagination. Returns (items, pagination_dict)."""
    page = max(page, 1)
    limit = min(max(limit, 1), 100)
    total_items = query.count()
    total_pages = ceil(total_items / limit) if total_items else 1
    items = query.offset((page - 1) * limit).limit(limit).all()
    return items, {
        "page": page,
        "limit": limit,
        "total_items": total_items,
        "total_pages": total_pages,
        "has_next": page < total_pages,
        "has_previous": page > 1,
    }


# ── OTP / Code generation ─────────────────────────────────────────────────

def generate_alphanumeric_otp(length: int = 5) -> str:
    """Generate alphanumeric OTP (mirrors legacy generateAlphanumericOTP)."""
    chars = string.ascii_letters + string.digits
    return "".join(random.SystemRandom().choice(chars) for _ in range(length))


def generate_numeric_otp(length: int = 6) -> str:
    return "".join(str(random.randint(0, 9)) for _ in range(length))


# ── Phone / display helpers ────────────────────────────────────────────────

def format_phone_display(phone: str) -> str:
    """Convert 255xxx to 0xxx for display (mirrors legacy)."""
    if not phone:
        return ""
    phone = phone.strip().lstrip("+")
    if phone.startswith("255"):
        return "0" + phone[3:]
    return phone


def normalize_phone(phone: str) -> str:
    """Convert 0xxx to 255xxx for API calls."""
    if phone and phone.startswith("0"):
        return "255" + phone[1:]
    return phone


def format_amount(amount) -> str:
    """Format number with commas (mirrors legacy number_format)."""
    if amount is None:
        return "0"
    return f"{int(amount):,}"


# ── Entity name lookups ───────────────────────────────────────────────────

def get_entity_name(db: Session, entity_type: str, entity_id: int) -> str:
    """Get name of any entity by type and ID. Replaces getSubParishName/getCommunityName/getGroupName."""
    from models.hierarchy import SubParish, Community, Group, HeadParish

    model_map = {
        "head_parish": HeadParish,
        "sub_parish": SubParish,
        "community": Community,
        "group": Group,
    }
    model = model_map.get(entity_type)
    if not model:
        return ""

    entity = db.query(model).filter(model.id == entity_id).first()
    return entity.name.upper() if entity else ""


def get_parish_info(db: Session, head_parish_id: int) -> Optional[dict]:
    """Get diocese/province/head_parish names. Mirrors legacy getParishInfo."""
    from models.hierarchy import HeadParish, Province, Diocese

    result = (
        db.query(
            Diocese.name.label("diocese_name"),
            Province.name.label("province_name"),
            HeadParish.name.label("head_parish_name"),
        )
        .join(Province, HeadParish.province_id == Province.id)
        .join(Diocese, HeadParish.diocese_id == Diocese.id)
        .filter(HeadParish.id == head_parish_id)
        .first()
    )
    if not result:
        return None
    return {
        "diocese_name": f"DAYOSISI YA {result.diocese_name}",
        "province_name": f"JIMBO LA {result.province_name}",
        "head_parish_name": f"USHARIKA WA {result.head_parish_name}",
    }


# ── Member helpers ─────────────────────────────────────────────────────────

def get_member_details(db: Session, member_id: int) -> Optional[dict]:
    """Get full member details with joins. Mirrors legacy getMemberDetails."""
    from models.members import ChurchMember
    from models.hierarchy import HeadParish, SubParish, Community, Province, Diocese
    from models.config import RevenueGroupModel  # just for titles table

    result = (
        db.query(ChurchMember)
        .filter(ChurchMember.id == member_id)
        .first()
    )
    if not result:
        return None

    parish_info = get_parish_info(db, result.head_parish_id)
    sp_name = get_entity_name(db, "sub_parish", result.sub_parish_id) if result.sub_parish_id else ""
    com_name = get_entity_name(db, "community", result.community_id) if result.community_id else ""

    return {
        "member_id": result.id,
        "first_name": result.first_name,
        "middle_name": result.middle_name,
        "last_name": result.last_name,
        "phone": result.phone,
        "email": result.email,
        "envelope_number": result.envelope_number,
        "gender": result.gender,
        "member_type": result.member_type,
        "head_parish_id": result.head_parish_id,
        "sub_parish_id": result.sub_parish_id,
        "community_id": result.community_id,
        "sub_parish_name": sp_name,
        "community_name": com_name,
        **(parish_info or {}),
    }


def get_member_full_name(member: dict) -> str:
    """Build full name from member dict. Mirrors legacy getMemberFullName."""
    parts = []
    if member.get("title"):
        parts.append(member["title"])
    parts.append(member.get("first_name", "").upper())
    if member.get("middle_name"):
        parts.append(member["middle_name"].upper())
    parts.append(member.get("last_name", "").upper())
    return " ".join(parts).strip()


def get_member_by_envelope(db: Session, envelope_number: str) -> Optional[dict]:
    """Find member by envelope number or phone. Mirrors legacy getMemberDetailsByEnvelope."""
    from models.members import ChurchMember

    member = db.query(ChurchMember).filter(
        or_(
            ChurchMember.envelope_number == envelope_number,
            ChurchMember.phone == envelope_number,
        )
    ).first()
    if not member:
        return None
    return {
        "member_id": member.id,
        "sub_parish_id": member.sub_parish_id,
        "community_id": member.community_id,
    }


def get_member_ids_by_location(
    db: Session,
    head_parish_id: int,
    sub_parish_id: int,
    community_id: int,
    gender: Optional[str] = None,
) -> List[int]:
    """Get active member IDs by location. Mirrors legacy getMemberIdsByLocation."""
    from models.members import ChurchMember

    q = db.query(ChurchMember.id).filter(
        ChurchMember.head_parish_id == head_parish_id,
        ChurchMember.sub_parish_id == sub_parish_id,
        ChurchMember.community_id == community_id,
        ChurchMember.status == "Active",
    )
    if gender in ("Male", "Female"):
        q = q.filter(ChurchMember.gender == gender)

    return [r[0] for r in q.all()]


# ── Envelope helpers ───────────────────────────────────────────────────────

def get_envelope_target_amount(db: Session, member_id: int) -> Optional[float]:
    """Get current year envelope target. Mirrors legacy getEnvelopeTargetAmount."""
    from models.envelope import EnvelopeTarget

    year = date.today().year
    from_date = date(year, 1, 1)
    end_date = date(year, 12, 31)

    target = db.query(EnvelopeTarget).filter(
        EnvelopeTarget.member_id == member_id,
        EnvelopeTarget.from_date == from_date,
        EnvelopeTarget.end_date == end_date,
    ).first()
    return float(target.target) if target else None


def get_unique_envelope_years(db: Session) -> List[int]:
    """Get distinct years from envelope targets. Mirrors legacy getUniqueYearsFromEnvelopeTargets."""
    from models.envelope import EnvelopeTarget

    years = set()
    for row in db.query(func.extract("year", EnvelopeTarget.from_date)).distinct().all():
        if row[0]:
            years.add(int(row[0]))
    for row in db.query(func.extract("year", EnvelopeTarget.end_date)).distinct().all():
        if row[0]:
            years.add(int(row[0]))
    return sorted(years)


# ── Harambee helpers ───────────────────────────────────────────────────────

def get_harambee_details(db: Session, harambee_id: int) -> Optional[dict]:
    """Get harambee by ID. Mirrors legacy get_harambee_details (unified table)."""
    from models.harambee import Harambee

    h = db.query(Harambee).filter(Harambee.id == harambee_id).first()
    if not h:
        return None
    return {
        "id": h.id,
        "name": h.name,
        "description": h.description,
        "from_date": str(h.from_date),
        "to_date": str(h.to_date),
        "amount": float(h.amount),
        "management_level": h.management_level,
        "head_parish_id": h.head_parish_id,
    }


def get_harambee_member_target(
    db: Session, harambee_id: int, member_id: int
) -> float:
    """Get member's target for a harambee. Mirrors legacy getMemberHarambeeTarget."""
    from models.harambee import HarambeeTarget

    target = db.query(HarambeeTarget).filter(
        HarambeeTarget.harambee_id == harambee_id,
        HarambeeTarget.member_id == member_id,
    ).first()
    return float(target.target) if target else 0.0


def get_harambee_member_total_contribution(
    db: Session, harambee_id: int, member_id: int
) -> float:
    """Get total contributions for a member in a harambee. Mirrors legacy calculateTotalContributions."""
    from models.harambee import HarambeeContribution

    result = db.query(func.sum(HarambeeContribution.amount)).filter(
        HarambeeContribution.harambee_id == harambee_id,
        HarambeeContribution.member_id == member_id,
    ).scalar()
    return float(result) if result else 0.0


def get_harambee_contributions_by_date(
    db: Session, harambee_id: int, member_id: int
) -> List[dict]:
    """Get contributions grouped by date. Mirrors legacy getContributionsByDate."""
    from models.harambee import HarambeeContribution

    rows = (
        db.query(
            HarambeeContribution.contribution_date,
            func.sum(HarambeeContribution.amount).label("amount"),
            HarambeeContribution.payment_method,
        )
        .filter(
            HarambeeContribution.harambee_id == harambee_id,
            HarambeeContribution.member_id == member_id,
        )
        .group_by(HarambeeContribution.contribution_date, HarambeeContribution.payment_method)
        .order_by(HarambeeContribution.contribution_date)
        .all()
    )
    return [
        {"date": str(r.contribution_date), "amount": float(r.amount), "method": r.payment_method}
        for r in rows
    ]


def set_harambee_target(
    db: Session,
    harambee_id: int,
    member_id: int,
    target: float,
    target_type: str = "individual",
    head_parish_id: int = 0,
    sub_parish_id: Optional[int] = None,
    community_id: Optional[int] = None,
) -> bool:
    """Insert or update member harambee target. Mirrors legacy setMemberHarambeeTarget / recordHarambeeTarget."""
    from models.harambee import HarambeeTarget

    existing = db.query(HarambeeTarget).filter(
        HarambeeTarget.harambee_id == harambee_id,
        HarambeeTarget.member_id == member_id,
    ).first()

    if existing:
        if target > float(existing.target):
            existing.target = target
            existing.target_type = target_type
            db.commit()
        return True
    else:
        new_target = HarambeeTarget(
            harambee_id=harambee_id,
            member_id=member_id,
            target=target,
            target_type=target_type,
            sub_parish_id=sub_parish_id,
            community_id=community_id,
        )
        db.add(new_target)
        db.commit()
        return True


def get_harambee_group_members(
    db: Session, harambee_group_id: int
) -> List[int]:
    """Get member IDs for a harambee group. Mirrors legacy getHarambeeGroupMemberIds."""
    from models.harambee import HarambeeGroupMember

    rows = db.query(HarambeeGroupMember.member_id).filter(
        HarambeeGroupMember.harambee_group_id == harambee_group_id
    ).all()
    return [r[0] for r in rows]


# ── Finance helpers ────────────────────────────────────────────────────────

def get_account_id_by_revenue_stream(db: Session, revenue_stream_id: int) -> Optional[int]:
    """Get bank account ID for a revenue stream. Mirrors legacy get_account_id_by_revenue_stream (unified)."""
    from models.finance import RevenueStream

    stream = db.query(RevenueStream).filter(RevenueStream.id == revenue_stream_id).first()
    return stream.account_id if stream else None


def update_bank_balance(db: Session, account_id: int, amount: float) -> bool:
    """Add amount to bank account balance. Mirrors legacy update_account_balance (unified)."""
    from models.finance import BankAccount

    account = db.query(BankAccount).filter(BankAccount.id == account_id).first()
    if not account:
        return False
    account.balance = Decimal(str(float(account.balance) + amount))
    db.commit()
    return True


# ── Admin helpers ──────────────────────────────────────────────────────────

def get_admin_by_role(
    db: Session,
    admin_level: str,
    role: str,
    entity_id: int,
) -> Optional[dict]:
    """Get admin details by role for an entity. Mirrors legacy getAdminEmailByTargetAndRole (unified)."""
    from models.admins import Admin

    level_field_map = {
        "head_parish": "head_parish_id",
        "sub_parish": "sub_parish_id",
        "community": "community_id",
        "group": "group_id",
    }
    field = level_field_map.get(admin_level)
    if not field:
        return None

    admin = db.query(Admin).filter(
        Admin.admin_level == admin_level,
        Admin.role == role,
        getattr(Admin, field) == entity_id,
        Admin.is_active == True,
    ).first()

    if not admin:
        return None

    return {
        "id": admin.id,
        "fullname": admin.fullname,
        "email": admin.email,
        "phone": admin.phone,
        "role": admin.role,
        "first_name": admin.fullname.split()[0] if admin.fullname else "",
    }


def get_admin_prefix(admin_level: str) -> str:
    """Get display prefix for admin level. Mirrors legacy getAdminPrefix."""
    prefixes = {
        "head_parish": "Head Parish ",
        "sub_parish": "Sub Parish ",
        "community": "Community ",
        "group": "Group ",
    }
    return prefixes.get(admin_level, "")


# ── Utility ────────────────────────────────────────────────────────────────

def calculate_percentage(numerator: float, denominator: float) -> float:
    """Mirrors legacy calculatePercentage."""
    if denominator == 0:
        return 0.0
    return (numerator / denominator) * 100
