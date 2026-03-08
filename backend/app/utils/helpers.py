# utils/helpers.py
"""Core business logic helpers for Kanisa Langu.
Complete port of legacy/utils/helpers.php (10,254 lines) into efficient Python."""

import logging
import random
import string
import math
from datetime import date, datetime, timedelta
from decimal import Decimal
from math import ceil
from typing import Any, Dict, List, Optional, Tuple

from sqlalchemy import func, text, and_, or_, distinct, extract
from sqlalchemy.orm import Session

logger = logging.getLogger(__name__)

# ── Constants ──────────────────────────────────────────────────────────────

MANAGEMENT_LEVELS = ("head_parish", "sub_parish", "community", "group")
PAYMENT_METHODS = ("Cash", "Bank Transfer", "Mobile Payment", "Card")
HARAMBEE_RESPONSIBILITIES = (
    "M/Kiti - Mtaa", "M/Kiti - Kamati", "M/M/Kiti",
    "Katibu", "M/Hazina", "Mjumbe",
)
SWAHILI_LOWERCASE_WORDS = {"ya", "na", "la", "wa", "kwa", "katika", "cha", "za", "ku"}


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
        "page": page, "limit": limit, "total_items": total_items,
        "total_pages": total_pages,
        "has_next": page < total_pages, "has_previous": page > 1,
    }


# ── OTP / Code generation ─────────────────────────────────────────────────

def generate_alphanumeric_otp(length: int = 5) -> str:
    chars = string.ascii_letters + string.digits
    return "".join(random.SystemRandom().choice(chars) for _ in range(length))


def generate_numeric_otp(length: int = 6) -> str:
    return "".join(str(random.randint(0, 9)) for _ in range(length))


# ── Phone / display helpers ────────────────────────────────────────────────

def format_phone_display(phone: str) -> str:
    """Convert 255xxx to 0xxx for display."""
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
    """Format number with commas."""
    if amount is None:
        return "0"
    return f"{int(amount):,}"


def format_expense_group_name(name: str) -> str:
    """Title case with Swahili lowercase words preserved. Mirrors formatExpenseGroupName."""
    if not name:
        return ""
    words = name.lower().split()
    return " ".join(
        w if w in SWAHILI_LOWERCASE_WORDS else w.capitalize()
        for w in words
    )


# ── Entity name lookups ───────────────────────────────────────────────────

def get_entity_name(db: Session, entity_type: str, entity_id: int) -> str:
    """Get name of any entity by type and ID."""
    table_map = {
        "head_parish": ("head_parishes", "head_parish_name", "head_parish_id"),
        "sub_parish": ("sub_parishes", "sub_parish_name", "sub_parish_id"),
        "community": ("communities", "community_name", "community_id"),
        "group": ("groups", "group_name", "group_id"),
    }
    cfg = table_map.get(entity_type)
    if not cfg:
        return ""
    table, col, id_col = cfg
    row = db.execute(text(f"SELECT {col} FROM {table} WHERE {id_col} = :eid"), {"eid": entity_id}).first()
    return row[0].upper() if row and row[0] else ""


def get_parish_info(db: Session, head_parish_id: int) -> Optional[dict]:
    """Get diocese/province/head_parish names."""
    sql = text("""
        SELECT 
            CONCAT('DAYOSISI YA ', d.diocese_name) AS diocese_name,
            CONCAT('JIMBO LA ', p.province_name) AS province_name,
            CONCAT('USHARIKA WA ', hp.head_parish_name) AS head_parish_name
        FROM head_parishes hp
        LEFT JOIN provinces p ON hp.province_id = p.province_id
        LEFT JOIN dioceses d ON p.diocese_id = d.diocese_id
        WHERE hp.head_parish_id = :hpid
    """)
    row = db.execute(sql, {"hpid": head_parish_id}).first()
    if not row:
        return None
    return {"diocese_name": row[0], "province_name": row[1], "head_parish_name": row[2]}


def get_head_parish_name(db: Session, head_parish_id: int) -> Optional[str]:
    """Get head parish name by ID."""
    row = db.execute(
        text("SELECT head_parish_name FROM head_parishes WHERE head_parish_id = :id"),
        {"id": head_parish_id}
    ).first()
    return row[0] if row else None


def get_first_sub_parish_id(db: Session, head_parish_id: int) -> Optional[int]:
    """Get first sub parish ID for a head parish."""
    row = db.execute(
        text("SELECT sub_parish_id FROM sub_parishes WHERE head_parish_id = :id ORDER BY sub_parish_id ASC LIMIT 1"),
        {"id": head_parish_id}
    ).first()
    return row[0] if row else None


def get_sub_parish_and_community(db: Session, member_id: int) -> Optional[dict]:
    """Get sub_parish_id and community_id for a member."""
    row = db.execute(
        text("SELECT sub_parish_id, community_id FROM church_members WHERE member_id = :mid"),
        {"mid": member_id}
    ).first()
    return {"sub_parish_id": row[0], "community_id": row[1]} if row else None


def get_sub_parish_and_community_names(db: Session, community_id: int) -> Optional[dict]:
    """Get community name and sub parish name from community ID."""
    row = db.execute(text("""
        SELECT c.community_name, sp.sub_parish_name
        FROM communities c JOIN sub_parishes sp ON sp.sub_parish_id = c.sub_parish_id
        WHERE c.community_id = :cid
    """), {"cid": community_id}).first()
    return {"community_name": row[0], "sub_parish_name": row[1]} if row else None


def get_operational_level_name(db: Session, target: str, head_parish_id: int,
                                sub_parish_id: int = None, community_id: int = None,
                                group_id: int = None) -> Optional[str]:
    """Get entity name based on management level target."""
    mapping = {
        "head-parish": ("head_parishes", "head_parish_name", "head_parish_id", head_parish_id),
        "sub-parish": ("sub_parishes", "sub_parish_name", "sub_parish_id", sub_parish_id),
        "community": ("communities", "community_name", "community_id", community_id),
        "group": ("groups", "group_name", "group_id", group_id),
        "groups": ("groups", "group_name", "group_id", group_id),
    }
    cfg = mapping.get(target)
    if not cfg or not cfg[3]:
        return None
    table, col, id_col, id_val = cfg
    row = db.execute(text(f"SELECT {col} FROM {table} WHERE {id_col} = :eid"), {"eid": id_val}).first()
    return row[0] if row else None


# ── Member helpers ─────────────────────────────────────────────────────────

def get_member_details(db: Session, member_id: int) -> Optional[dict]:
    """Full member details with joins."""
    base_avatar_url = "https://kanisalangu.sewmrtechnologies.com/uploads/avatars/"
    sql = text("""
        SELECT
            cm.member_id, cm.first_name, cm.middle_name, cm.last_name,
            t.name AS title, cm.phone, cm.type, cm.envelope_number, cm.email, cm.gender,
            CONCAT('DAYOSISI YA ', d.diocese_name) AS diocese_name,
            CONCAT('JIMBO LA ', p.province_name) AS province_name,
            CONCAT('USHARIKA WA ', hp.head_parish_name) AS head_parish_name,
            c.community_name, sp.sub_parish_name,
            sp.sub_parish_id, c.community_id, hp.head_parish_id,
            CASE WHEN cma.avatar_url IS NOT NULL THEN CONCAT(:avatar_base, cma.avatar_url) ELSE NULL END AS avatar_url
        FROM church_members cm
        LEFT JOIN titles t ON cm.title_id = t.id
        LEFT JOIN head_parishes hp ON cm.head_parish_id = hp.head_parish_id
        LEFT JOIN sub_parishes sp ON cm.sub_parish_id = sp.sub_parish_id
        LEFT JOIN communities c ON cm.community_id = c.community_id
        LEFT JOIN provinces p ON hp.province_id = p.province_id
        LEFT JOIN dioceses d ON p.diocese_id = d.diocese_id
        LEFT JOIN church_members_accounts cma ON cm.member_id = cma.member_id
        WHERE cm.member_id = :mid
    """)
    row = db.execute(sql, {"mid": member_id, "avatar_base": base_avatar_url}).mappings().first()
    return dict(row) if row else None


def get_member_full_name(member: dict) -> str:
    """Build full name from member dict."""
    parts = []
    if member.get("title"):
        parts.append(member["title"])
    parts.append((member.get("first_name") or "").upper())
    if member.get("middle_name"):
        parts.append(member["middle_name"].upper())
    parts.append((member.get("last_name") or "").upper())
    return " ".join(parts).strip()


def get_member_by_envelope(db: Session, envelope_number: str) -> Optional[dict]:
    """Find member by envelope number or phone."""
    row = db.execute(text("""
        SELECT member_id, sub_parish_id, community_id
        FROM church_members WHERE envelope_number = :val OR phone = :val
    """), {"val": envelope_number}).first()
    if not row:
        return None
    return {"member_id": row[0], "sub_parish_id": row[1], "community_id": row[2]}


def get_member_ids_by_location(db: Session, head_parish_id: int, sub_parish_id: int,
                                community_id: int, gender: Optional[str] = None) -> List[int]:
    """Get active member IDs by location."""
    sql = """SELECT member_id FROM church_members
             WHERE head_parish_id = :hpid AND sub_parish_id = :spid
             AND community_id = :cid AND status = 'Active'"""
    params = {"hpid": head_parish_id, "spid": sub_parish_id, "cid": community_id}
    if gender in ("Male", "Female"):
        sql += " AND gender = :gender"
        params["gender"] = gender
    rows = db.execute(text(sql), params).all()
    return [r[0] for r in rows]


def get_head_parish_secretary_phone(db: Session, head_parish_id: int) -> Optional[str]:
    """Get head parish secretary phone number."""
    row = db.execute(text("""
        SELECT head_parish_admin_phone FROM head_parish_admins
        WHERE head_parish_id = :hpid AND head_parish_admin_role = 'secretary' LIMIT 1
    """), {"hpid": head_parish_id}).first()
    if row and row[0]:
        return format_phone_display(row[0])
    return None


# ── Envelope helpers ───────────────────────────────────────────────────────

def get_envelope_target_amount(db: Session, member_id: int) -> Optional[float]:
    """Get current year envelope target."""
    year = date.today().year
    row = db.execute(text("""
        SELECT target FROM envelope_targets
        WHERE member_id = :mid AND EXTRACT(YEAR FROM from_date) = :yr
    """), {"mid": member_id, "yr": year}).first()
    return float(row[0]) if row and row[0] else None


def get_unique_envelope_years(db: Session) -> List[int]:
    """Get distinct years from envelope targets."""
    rows = db.execute(text("""
        SELECT DISTINCT EXTRACT(YEAR FROM from_date)::int AS yr FROM envelope_targets
        UNION
        SELECT DISTINCT EXTRACT(YEAR FROM end_date)::int AS yr FROM envelope_targets
        ORDER BY yr
    """)).all()
    return [r[0] for r in rows if r[0]]


def record_envelope_data(db: Session, member_id: int, amount: float, contribution_date: str,
                         payment_method: str, head_parish_id: int, sub_parish_id: int,
                         community_id: int, local_timestamp: str, recorded_by: int) -> bool:
    """Record envelope contribution."""
    try:
        db.execute(text("""
            INSERT INTO envelope_contribution
            (member_id, amount, contribution_date, recorded_by, head_parish_id,
             sub_parish_id, community_id, payment_method, local_timestamp)
            VALUES (:mid, :amt, :cd, :rb, :hpid, :spid, :cid, :pm, :lt)
        """), {
            "mid": member_id, "amt": amount, "cd": contribution_date,
            "rb": recorded_by, "hpid": head_parish_id, "spid": sub_parish_id,
            "cid": community_id, "pm": payment_method, "lt": local_timestamp,
        })
        db.commit()
        return True
    except Exception as e:
        logger.error(f"record_envelope_data error: {e}")
        db.rollback()
        return False


def fetch_member_envelope_data(db: Session, member_id: int, year: int) -> dict:
    """Fetch member's envelope data for a year. Mirrors fetchMemberEnvelopeData."""
    result = {
        "total_envelope_contribution": 0.0,
        "yearly_envelope_target": 0.0,
        "total_annual_envelopes": count_sundays(year),
        "total_envelopes_until_today": count_sundays_to_date(year),
        "member_contributions_until_today": 0,
    }
    # Total contribution
    row = db.execute(text("""
        SELECT COALESCE(SUM(amount), 0)
        FROM envelope_contribution
        WHERE member_id = :mid AND EXTRACT(YEAR FROM contribution_date) = :yr
          AND contribution_date <= CURRENT_DATE
    """), {"mid": member_id, "yr": year}).first()
    result["total_envelope_contribution"] = float(row[0]) if row else 0.0

    # Target
    row = db.execute(text("""
        SELECT target FROM envelope_targets
        WHERE member_id = :mid AND EXTRACT(YEAR FROM from_date) = :yr
    """), {"mid": member_id, "yr": year}).first()
    result["yearly_envelope_target"] = float(row[0]) if row and row[0] else 0.0

    # Distinct weeks contributed
    row = db.execute(text("""
        SELECT COUNT(DISTINCT (EXTRACT(YEAR FROM contribution_date)::text || '-' || EXTRACT(WEEK FROM contribution_date)::text))
        FROM envelope_contribution
        WHERE member_id = :mid AND EXTRACT(YEAR FROM contribution_date) = :yr
          AND contribution_date <= CURRENT_DATE
    """), {"mid": member_id, "yr": year}).first()
    result["member_contributions_until_today"] = int(row[0]) if row else 0

    return result


def count_sundays(year: int) -> int:
    """Count total Sundays in a year."""
    count = 0
    d = date(year, 1, 1)
    end = date(year, 12, 31)
    while d <= end:
        if d.weekday() == 6:  # Sunday
            count += 1
        d += timedelta(days=1)
    return count


def count_sundays_to_date(year: int) -> int:
    """Count Sundays from Jan 1 to today (or Dec 31 if past year)."""
    count = 0
    d = date(year, 1, 1)
    today = date.today()
    end = date(year, 12, 31) if year < today.year else today
    while d <= end:
        if d.weekday() == 6:
            count += 1
        d += timedelta(days=1)
    return count


# ── Harambee table mapping (unified from legacy per-level tables) ──────────

def get_tables_by_target(target: str) -> Optional[Tuple[str, str, str, str, str]]:
    """Map target to (target_table, group_info_table, harambee_table, contribution_table, group_members_table).
    Mirrors legacy getTablesByTarget. Returns None for invalid target."""
    mapping = {
        "head-parish": (
            "head_parish_harambee_targets", "hp_group_harambee_target_information",
            "head_parish_harambee", "head_parish_harambee_contribution",
            "head_parish_harambee_group_members"
        ),
        "sub-parish": (
            "sub_parish_harambee_targets", "sp_group_harambee_target_information",
            "sub_parish_harambee", "sub_parish_harambee_contribution",
            "sub_parish_harambee_group_members"
        ),
        "community": (
            "community_harambee_targets", "com_group_harambee_target_information",
            "community_harambee", "community_harambee_contribution",
            "community_harambee_group_members"
        ),
        "groups": (
            "groups_harambee_targets", "gp_group_harambee_target_information",
            "groups_harambee", "groups_harambee_contribution",
            "groups_harambee_group_members"
        ),
    }
    # Normalize target aliases
    if target == "group":
        target = "groups"
    return mapping.get(target)


def get_harambee_group_table(target: str) -> Optional[str]:
    """Get harambee groups table name by target."""
    mapping = {
        "head-parish": "head_parish_harambee_groups",
        "sub-parish": "sub_parish_harambee_groups",
        "community": "community_harambee_groups",
        "group": "groups_harambee_groups",
        "groups": "groups_harambee_groups",
    }
    return mapping.get(target)


def get_harambee_group_member_table(target: str) -> Optional[str]:
    """Get harambee group members table name by target."""
    mapping = {
        "head-parish": "head_parish_harambee_group_members",
        "sub-parish": "sub_parish_harambee_group_members",
        "community": "community_harambee_group_members",
        "group": "groups_harambee_group_members",
        "groups": "groups_harambee_group_members",
    }
    return mapping.get(target)


# ── Harambee detail helpers ────────────────────────────────────────────────

def get_harambee_details(db: Session, harambee_id: int, target: str) -> Optional[dict]:
    """Get harambee by ID and target. Mirrors legacy get_harambee_details."""
    tables = get_tables_by_target(target)
    if not tables:
        return None
    harambee_table = tables[2]
    row = db.execute(
        text(f"SELECT description, from_date, to_date, amount FROM {harambee_table} WHERE harambee_id = :hid"),
        {"hid": harambee_id}
    ).first()
    if not row:
        return None
    return {"description": row[0], "from_date": str(row[1]), "to_date": str(row[2]), "amount": float(row[3])}


def get_harambee_description(db: Session, harambee_id: int, target: str) -> Optional[str]:
    """Get harambee description."""
    details = get_harambee_details(db, harambee_id, target)
    return details["description"] if details else None


def get_head_parish_id_from_harambee(db: Session, harambee_id: int) -> int:
    """Get head_parish_id from head_parish_harambee table."""
    row = db.execute(
        text("SELECT head_parish_id FROM head_parish_harambee WHERE harambee_id = :hid LIMIT 1"),
        {"hid": harambee_id}
    ).first()
    return int(row[0]) if row else 0


# ── Harambee target helpers ────────────────────────────────────────────────

def get_member_harambee_target(db: Session, member_id: int, target: str, harambee_id: int) -> float:
    """Get member's target for a harambee. Mirrors getMemberHarambeeTarget."""
    tables = get_tables_by_target(target)
    if not tables:
        return 0.0
    target_table = tables[0]
    row = db.execute(
        text(f"SELECT target FROM {target_table} WHERE member_id = :mid AND harambee_id = :hid"),
        {"mid": member_id, "hid": harambee_id}
    ).first()
    return float(row[0]) if row and row[0] else 0.0


def get_member_harambee_target_type(db: Session, member_id: int, target: str, harambee_id: int) -> Optional[str]:
    """Get member's target type (individual/group)."""
    tables = get_tables_by_target(target)
    if not tables:
        return None
    row = db.execute(
        text(f"SELECT target_type FROM {tables[0]} WHERE member_id = :mid AND harambee_id = :hid"),
        {"mid": member_id, "hid": harambee_id}
    ).first()
    return row[0] if row else None


def set_member_harambee_target(db: Session, member_id: int, target: str, harambee_id: int,
                                target_amount: float, target_type: str = "individual") -> bool:
    """Insert or update member harambee target. Mirrors setMemberHarambeeTarget."""
    tables = get_tables_by_target(target)
    if not tables:
        return False
    target_table = tables[0]
    member = get_member_details(db, member_id)
    if not member:
        return False

    existing = db.execute(
        text(f"SELECT COUNT(*) FROM {target_table} WHERE member_id = :mid AND harambee_id = :hid"),
        {"mid": member_id, "hid": harambee_id}
    ).scalar()

    try:
        if existing and existing > 0:
            db.execute(text(f"""
                UPDATE {target_table} SET target = :tgt, target_type = :tt,
                    head_parish_id = :hpid, sub_parish_id = :spid, community_id = :cid
                WHERE member_id = :mid AND harambee_id = :hid
            """), {
                "tgt": target_amount, "tt": target_type,
                "hpid": member["head_parish_id"], "spid": member.get("sub_parish_id"),
                "cid": member.get("community_id"), "mid": member_id, "hid": harambee_id,
            })
        else:
            db.execute(text(f"""
                INSERT INTO {target_table}
                (member_id, harambee_id, target, target_type, head_parish_id, sub_parish_id, community_id)
                VALUES (:mid, :hid, :tgt, :tt, :hpid, :spid, :cid)
            """), {
                "mid": member_id, "hid": harambee_id, "tgt": target_amount, "tt": target_type,
                "hpid": member["head_parish_id"], "spid": member.get("sub_parish_id"),
                "cid": member.get("community_id"),
            })
        db.commit()
        return True
    except Exception as e:
        logger.error(f"set_member_harambee_target error: {e}")
        db.rollback()
        return False


def has_group_target_type(db: Session, harambee_id: int, member_id: int, target: str) -> bool:
    """Check if member has 'group' target type."""
    return get_member_harambee_target_type(db, member_id, target, harambee_id) == "group"


def is_in_harambee_group(db: Session, harambee_id: int, member_id: int, target: str) -> bool:
    """Check if member is in a harambee group for given harambee."""
    table = get_harambee_group_member_table(target)
    if not table:
        return False
    row = db.execute(
        text(f"SELECT member_id FROM {table} WHERE harambee_id = :hid AND member_id = :mid LIMIT 1"),
        {"hid": harambee_id, "mid": member_id}
    ).first()
    return row is not None


# ── Harambee contribution helpers ──────────────────────────────────────────

def calculate_total_contributions(db: Session, member_id: int, harambee_id: int, target: str) -> float:
    """Get total contributions for a member in a harambee."""
    tables = get_tables_by_target(target)
    if not tables:
        return 0.0
    contrib_table = tables[3]
    row = db.execute(
        text(f"SELECT COALESCE(SUM(amount), 0) FROM {contrib_table} WHERE member_id = :mid AND harambee_id = :hid"),
        {"mid": member_id, "hid": harambee_id}
    ).first()
    return float(row[0]) if row else 0.0


def get_contributions_by_date(db: Session, member_id: int, harambee_id: int, target: str) -> List[dict]:
    """Get contributions grouped by date and payment method."""
    tables = get_tables_by_target(target)
    if not tables:
        return []
    contrib_table = tables[3]
    rows = db.execute(text(f"""
        SELECT contribution_date, SUM(amount) AS amount, payment_method
        FROM {contrib_table}
        WHERE member_id = :mid AND harambee_id = :hid
        GROUP BY contribution_date, payment_method
        ORDER BY contribution_date ASC
    """), {"mid": member_id, "hid": harambee_id}).all()
    return [{"date": str(r[0]), "amount": float(r[1]), "method": r[2]} for r in rows]


def get_contribution_methods(db: Session, member_id: int, harambee_id: int, target: str,
                              contribution_date: str = None) -> dict:
    """Get contributions grouped by payment method."""
    tables = get_tables_by_target(target)
    if not tables:
        return {}
    contrib_table = tables[3]
    sql = f"SELECT payment_method, SUM(amount) FROM {contrib_table} WHERE member_id = :mid AND harambee_id = :hid"
    params = {"mid": member_id, "hid": harambee_id}
    if contribution_date:
        sql += " AND contribution_date = :cd"
        params["cd"] = contribution_date
    sql += " GROUP BY payment_method"
    rows = db.execute(text(sql), params).all()
    return {r[0]: float(r[1]) for r in rows}


def get_total_contributions_on_date(db: Session, harambee_id: int, member_id: int,
                                     date_str: str, target: str) -> dict:
    """Get total contributions before and on a given date."""
    tables = get_tables_by_target(target)
    if not tables:
        return {"total_before_date": 0, "on_date_contributions": {}, "contribution": 0}
    contrib_table = tables[3]
    rows = db.execute(text(f"""
        SELECT payment_method,
            SUM(CASE WHEN contribution_date < :dt THEN amount ELSE 0 END) AS before_date,
            SUM(CASE WHEN contribution_date = :dt THEN amount ELSE 0 END) AS on_date
        FROM {contrib_table}
        WHERE harambee_id = :hid AND member_id = :mid
        GROUP BY payment_method
    """), {"dt": date_str, "hid": harambee_id, "mid": member_id}).all()

    total_before = 0
    on_date = {}
    total_on = 0
    for r in rows:
        total_before += float(r[1] or 0)
        on_date[r[0]] = float(r[2] or 0)
        total_on += float(r[2] or 0)
    return {"total_before_date": total_before, "on_date_contributions": on_date, "contribution": total_on}


def get_harambee_member_ids(db: Session, harambee_id: int, target: str,
                             community_id: int = None) -> List[int]:
    """Get all unique member IDs involved in a harambee (from targets + contributions)."""
    tables = get_tables_by_target(target)
    if not tables:
        return []
    target_table, _, _, contrib_table, _ = tables
    params: dict = {"hid": harambee_id}

    sql1 = f"SELECT member_id FROM {target_table} WHERE harambee_id = :hid"
    sql2 = f"SELECT member_id FROM {contrib_table} WHERE harambee_id = :hid"
    if community_id:
        sql1 += " AND community_id = :cid"
        sql2 += " AND community_id = :cid"
        params["cid"] = community_id

    ids = set()
    for r in db.execute(text(sql1), params).all():
        ids.add(r[0])
    for r in db.execute(text(sql2 + f" AND member_id NOT IN ({','.join(str(i) for i in ids) or '0'})"), params).all():
        ids.add(r[0])
    return list(ids)


def get_contributing_harambee_member_ids(db: Session, harambee_id: int, target: str) -> List[int]:
    """Get distinct member IDs who contributed to a harambee."""
    tables = get_tables_by_target(target)
    if not tables:
        return []
    contrib_table = tables[3]
    rows = db.execute(
        text(f"SELECT DISTINCT member_id FROM {contrib_table} WHERE harambee_id = :hid"),
        {"hid": harambee_id}
    ).all()
    return [r[0] for r in rows]


def get_harambee_member_ids_by_contribution_date(db: Session, harambee_id: int, target: str,
                                                   from_date: str = None, to_date: str = None) -> List[int]:
    """Get member IDs who contributed on specific date(s)."""
    tables = get_tables_by_target(target)
    if not tables:
        return []
    contrib_table = tables[3]
    sql = f"SELECT DISTINCT member_id FROM {contrib_table} WHERE harambee_id = :hid"
    params: dict = {"hid": harambee_id}
    if from_date and to_date:
        sql += " AND contribution_date BETWEEN :fd AND :td"
        params["fd"] = from_date
        params["td"] = to_date
    elif from_date:
        sql += " AND contribution_date = :fd"
        params["fd"] = from_date
    rows = db.execute(text(sql), params).all()
    return [r[0] for r in rows]


def get_member_ids_recorded_by_admin(db: Session, harambee_id: int, contribution_date: str,
                                      target: str, admin_id: int) -> List[int]:
    """Get member IDs recorded by a specific admin on a date."""
    tables = get_tables_by_target(target)
    if not tables:
        return []
    contrib_table = tables[3]
    rows = db.execute(text(f"""
        SELECT DISTINCT member_id FROM {contrib_table}
        WHERE harambee_id = :hid AND contribution_date = :cd AND recorded_by = :aid
    """), {"hid": harambee_id, "cd": contribution_date, "aid": admin_id}).all()
    return [r[0] for r in rows]


# ── Harambee group helpers ─────────────────────────────────────────────────

def get_harambee_group_details(db: Session, harambee_id: int, member_id: int, target: str) -> Optional[dict]:
    """Get group details for a member in a harambee."""
    member_table = get_harambee_group_member_table(target)
    group_table = get_harambee_group_table(target)
    tables = get_tables_by_target(target)
    if not member_table or not group_table or not tables:
        return None
    harambee_table = tables[2]
    row = db.execute(text(f"""
        SELECT g.harambee_group_id, g.harambee_group_name, g.harambee_group_target,
            DATE(g.date_created) AS date_created, h.description AS harambee_description
        FROM {member_table} m
        JOIN {group_table} g ON m.harambee_group_id = g.harambee_group_id
        JOIN {harambee_table} h ON g.harambee_id = h.harambee_id
        WHERE m.member_id = :mid AND g.harambee_id = :hid LIMIT 1
    """), {"mid": member_id, "hid": harambee_id}).first()
    if not row:
        return None
    return {
        "harambee_group_id": row[0], "harambee_group_name": row[1],
        "harambee_group_target": float(row[2]), "date_created": str(row[3]),
        "harambee_description": row[4],
    }


def get_harambee_group_member_ids(db: Session, target: str, harambee_group_id: int) -> List[int]:
    """Get member IDs for a harambee group."""
    table = get_harambee_group_member_table(target)
    if not table:
        return []
    rows = db.execute(
        text(f"SELECT member_id FROM {table} WHERE harambee_group_id = :gid"),
        {"gid": harambee_group_id}
    ).all()
    return [r[0] for r in rows]


def get_harambee_group_member_contribution(db: Session, harambee_id: int, member_id: int,
                                            group_start_date: str, target: str) -> float:
    """Get total contribution for a member since group start date."""
    tables = get_tables_by_target(target)
    if not tables:
        return 0.0
    contrib_table = tables[3]
    row = db.execute(text(f"""
        SELECT COALESCE(SUM(amount), 0)
        FROM {contrib_table}
        WHERE harambee_id = :hid AND member_id = :mid AND contribution_date >= :sd
    """), {"hid": harambee_id, "mid": member_id, "sd": group_start_date}).first()
    return float(row[0]) if row else 0.0


def get_harambee_group_member_target(db: Session, harambee_id: int, member_id: int, target: str) -> float:
    """Get a member's target amount within a harambee group."""
    table = get_harambee_group_member_table(target)
    if not table:
        return 0.0
    row = db.execute(text(f"""
        SELECT target_amount FROM {table}
        WHERE harambee_id = :hid AND member_id = :mid
    """), {"hid": harambee_id, "mid": member_id}).first()
    return float(row[0]) if row and row[0] else 0.0


def get_total_harambee_group_contribution(db: Session, member_ids: List[int], harambee_id: int,
                                           target: str, contribution_start_date: str) -> float:
    """Get total contributions for all group members since start date."""
    if not member_ids:
        return 0.0
    tables = get_tables_by_target(target)
    if not tables:
        return 0.0
    contrib_table = tables[3]
    placeholders = ",".join(str(int(mid)) for mid in member_ids)
    row = db.execute(text(f"""
        SELECT COALESCE(SUM(amount), 0)
        FROM {contrib_table}
        WHERE harambee_id = :hid AND member_id IN ({placeholders})
          AND contribution_date >= :sd
    """), {"hid": harambee_id, "sd": contribution_start_date}).first()
    return float(row[0]) if row else 0.0


def get_harambee_group_info(db: Session, target: str, harambee_group_id: int) -> Optional[dict]:
    """Get harambee group info (name, target, description, date_created)."""
    group_table = get_harambee_group_table(target)
    if not group_table:
        return None
    row = db.execute(text(f"""
        SELECT harambee_group_name, harambee_group_target, description,
               DATE(date_created) AS date_created
        FROM {group_table} WHERE harambee_group_id = :gid
    """), {"gid": harambee_group_id}).first()
    if not row:
        return None
    return {
        "group_name": row[0], "harambee_group_target": format_amount(row[1]),
        "harambee_group_description": row[2], "date_created": str(row[3]),
    }


def get_harambee_group_ids_by_harambee_id(db: Session, harambee_id: int, target: str,
                                            sub_parish_id: int = None) -> List[int]:
    """Get all group IDs for a harambee."""
    group_table = get_harambee_group_table(target)
    if not group_table:
        return []
    sql = f"SELECT harambee_group_id FROM {group_table} WHERE harambee_id = :hid"
    params: dict = {"hid": harambee_id}
    if sub_parish_id:
        sql += " AND sub_parish_id = :spid"
        params["spid"] = sub_parish_id
    rows = db.execute(text(sql), params).all()
    return [r[0] for r in rows]


def get_harambee_id_from_harambee_group(db: Session, harambee_group_id: int, target: str) -> Optional[int]:
    """Get harambee_id from a harambee group."""
    group_table = get_harambee_group_table(target)
    if not group_table:
        return None
    row = db.execute(
        text(f"SELECT harambee_id FROM {group_table} WHERE harambee_group_id = :gid"),
        {"gid": harambee_group_id}
    ).first()
    return row[0] if row else None


# ── Mr & Mrs pair helpers ──────────────────────────────────────────────────

def get_mr_and_mrs_members_ids(db: Session, harambee_id: int, member_id: int, target: str) -> List[dict]:
    """Get Mr & Mrs pair details."""
    tables = get_tables_by_target(target)
    if not tables or len(tables) < 2:
        return []
    info_table = tables[1]
    rows = db.execute(text(f"""
        SELECT first_member_id, second_member_id, group_name
        FROM {info_table}
        WHERE harambee_id = :hid AND (first_member_id = :mid OR second_member_id = :mid)
    """), {"hid": harambee_id, "mid": member_id}).all()
    return [{"first_member_id": r[0], "second_member_id": r[1], "group_name": r[2]} for r in rows]


# ── Harambee distribution helpers ──────────────────────────────────────────

def get_distributed_amount(db: Session, target: str, reference_key: int, harambee_id: int) -> float:
    """Get distributed amount for a harambee."""
    table_map = {
        "head-parish": ("head_parish_harambee_distribution", "sub_parish_id"),
        "sub-parish": ("sub_parish_harambee_distribution", "sub_parish_id"),
        "community": ("community_harambee_distribution", "community_id"),
        "group": ("group_harambee_distribution", "group_id"),
        "groups": ("group_harambee_distribution", "group_id"),
    }
    cfg = table_map.get(target)
    if not cfg:
        return 0.0
    table, col = cfg
    row = db.execute(
        text(f"SELECT amount FROM {table} WHERE {col} = :rk AND harambee_id = :hid LIMIT 1"),
        {"rk": reference_key, "hid": harambee_id}
    ).first()
    return float(row[0]) if row and row[0] else 0.0


# ── Finance helpers ────────────────────────────────────────────────────────

def get_account_id_by_revenue_stream(db: Session, revenue_stream_id: int, target: str = "head_parish") -> Optional[int]:
    """Get bank account ID for a revenue stream."""
    table_map = {
        "head_parish": "head_parish_revenue_streams",
        "diocese": "diocese_revenue_streams",
        "province": "province_revenue_streams",
    }
    table = table_map.get(target, "head_parish_revenue_streams")
    row = db.execute(
        text(f"SELECT account_id FROM {table} WHERE revenue_stream_id = :rsid"),
        {"rsid": revenue_stream_id}
    ).first()
    return row[0] if row else None


def update_bank_balance(db: Session, account_id: int, amount: float, target: str = "head_parish") -> bool:
    """Add amount to bank account balance."""
    table_map = {
        "head_parish": "head_parish_bank_accounts",
        "diocese": "diocese_bank_accounts",
        "province": "province_bank_accounts",
    }
    table = table_map.get(target)
    if not table:
        return False
    try:
        db.execute(
            text(f"UPDATE {table} SET balance = balance + :amt WHERE account_id = :aid"),
            {"amt": amount, "aid": account_id}
        )
        db.commit()
        return True
    except Exception as e:
        logger.error(f"update_bank_balance error: {e}")
        db.rollback()
        return False


def get_bank_account_name(db: Session, account_id: int) -> Optional[str]:
    """Get bank account name, strip part before hyphen."""
    row = db.execute(
        text("SELECT account_name FROM head_parish_bank_accounts WHERE account_id = :aid"),
        {"aid": account_id}
    ).first()
    if not row or not row[0]:
        return None
    name = row[0]
    if "-" in name:
        name = name.split("-", 1)[1].strip()
    return name


def get_revenue_stream_name(db: Session, head_parish_id: int, revenue_stream_id: int) -> Optional[str]:
    """Get revenue stream name."""
    row = db.execute(text("""
        SELECT revenue_stream_name FROM head_parish_revenue_streams
        WHERE head_parish_id = :hpid AND revenue_stream_id = :rsid LIMIT 1
    """), {"hpid": head_parish_id, "rsid": revenue_stream_id}).first()
    return row[0] if row else None


# ── Admin helpers ──────────────────────────────────────────────────────────

def get_admin_prefix(target: str) -> str:
    """Get display prefix for admin level."""
    prefixes = {
        "head-parish": "Head Parish ", "sub-parish": "Sub Parish ",
        "community": "Community ", "group": "Group ", "groups": "Group ",
    }
    return prefixes.get(target, "")


def get_admin_email_by_target_and_role(db: Session, target: str, role: str, admin_id: int) -> Optional[str]:
    """Get admin email by target, role, and entity ID."""
    table_map = {
        "head-parish": ("head_parish_admins", "head_parish_admin_email", "head_parish_admin_role", "head_parish_id"),
        "sub-parish": ("sub_parish_admins", "sub_parish_admin_email", "sub_parish_admin_role", "sub_parish_id"),
        "community": ("community_admins", "community_admin_email", "community_admin_role", "community_id"),
        "group": ("group_admins", "group_admin_email", "group_admin_role", "group_id"),
        "groups": ("group_admins", "group_admin_email", "group_admin_role", "group_id"),
    }
    cfg = table_map.get(target)
    if not cfg:
        return None
    table, email_col, role_col, id_col = cfg
    row = db.execute(
        text(f"SELECT {email_col} FROM {table} WHERE {role_col} = :role AND {id_col} = :aid LIMIT 1"),
        {"role": role, "aid": admin_id}
    ).first()
    return row[0] if row else None


def get_admin_phone_and_first_name_by_role(db: Session, head_parish_id: int, role: str) -> dict:
    """Get admin phone and first name by role for head parish."""
    row = db.execute(text("""
        SELECT head_parish_admin_phone, head_parish_admin_fullname
        FROM head_parish_admins
        WHERE head_parish_id = :hpid AND head_parish_admin_role = :role LIMIT 1
    """), {"hpid": head_parish_id, "role": role}).first()
    if not row:
        return {"first_name": "", "phone": ""}
    phone = format_phone_display(row[0] or "")
    names = (row[1] or "").split()
    first_name = names[0].capitalize() if names else ""
    return {"first_name": first_name, "phone": phone}


def get_accountant_phone_by_head_parish(db: Session, head_parish_id: int) -> dict:
    """Get accountant phone and first name for head parish."""
    return get_admin_phone_and_first_name_by_role(db, head_parish_id, "accountant")


def get_head_parish_admin_details_by_id(db: Session, head_parish_id: int, admin_id: int) -> Optional[dict]:
    """Get head parish admin details by ID."""
    row = db.execute(text("""
        SELECT head_parish_admin_fullname, head_parish_admin_phone,
               head_parish_admin_email, head_parish_admin_role
        FROM head_parish_admins
        WHERE head_parish_id = :hpid AND head_parish_admin_id = :aid LIMIT 1
    """), {"hpid": head_parish_id, "aid": admin_id}).first()
    if not row:
        return None
    role_sw = translate_role_to_swahili(row[3].lower() if row[3] else "")
    return {
        "full_name": row[0].title() if row[0] else "",
        "phone": row[1], "email": (row[2] or "").lower(),
        "role_en": (row[3] or "").lower(), "role_sw": role_sw,
    }


def translate_role_to_swahili(role: str) -> str:
    mapping = {
        "admin": "Msimamizi Mkuu", "pastor": "Mchungaji",
        "secretary": "Katibu", "chairperson": "Mwenyekiti",
        "clerk": "Karani", "accountant": "M/Hazina",
        "evangelist": "Mwinjilisti", "elder": "Mzee wa Kanisa",
    }
    return mapping.get(role, "Kiongozi")


# ── Harambee accountant lookup ─────────────────────────────────────────────

def get_harambee_accountant(db: Session, target: str, identifier_id: int,
                             identifier_type: str = "harambee") -> dict:
    """Get accountant name and phone for a harambee or harambee group.
    Mirrors legacy getHarambeeAccountant (simplified for unified admin table approach)."""
    admin_table_map = {
        "head-parish": ("head_parish_admins", "head_parish_admin_fullname", "head_parish_admin_phone", "head_parish_admin_role"),
        "sub-parish": ("sub_parish_admins", "sub_parish_admin_fullname", "sub_parish_admin_phone", "sub_parish_admin_role"),
        "community": ("community_admins", "community_admin_fullname", "community_admin_phone", "community_admin_role"),
        "group": ("group_admins", "group_admin_fullname", "group_admin_phone", "group_admin_role"),
        "groups": ("group_admins", "group_admin_fullname", "group_admin_phone", "group_admin_role"),
    }
    cfg = admin_table_map.get(target)
    if not cfg:
        return {"first_name": "", "phone": ""}
    admin_table, name_col, phone_col, role_col = cfg

    # Resolve harambee_id
    harambee_id = identifier_id
    if identifier_type == "harambee_group":
        group_table = get_harambee_group_table(target)
        if group_table:
            row = db.execute(
                text(f"SELECT harambee_id FROM {group_table} WHERE harambee_group_id = :gid LIMIT 1"),
                {"gid": identifier_id}
            ).first()
            harambee_id = row[0] if row else None

    if not harambee_id:
        return {"first_name": "", "phone": ""}

    # Get head_parish_id
    head_parish_id = get_head_parish_id_from_harambee(db, harambee_id)
    if not head_parish_id:
        return {"first_name": "", "phone": ""}

    row = db.execute(
        text(f"SELECT {name_col}, {phone_col} FROM {admin_table} WHERE head_parish_id = :hpid AND {role_col} = 'accountant' LIMIT 1"),
        {"hpid": head_parish_id}
    ).first()
    if not row:
        return {"first_name": "", "phone": ""}

    fullname = row[0] or ""
    names = fullname.split()
    first_name = names[0].capitalize() if names else ""
    phone = format_phone_display(row[1] or "")
    return {"first_name": first_name, "phone": phone}


# ── Excluded members ───────────────────────────────────────────────────────

def get_excluded_member_ids(db: Session, head_parish_id: int, harambee_target: str, harambee_id: int) -> List[int]:
    """Get excluded member IDs for a harambee."""
    rows = db.execute(text("""
        SELECT member_id FROM harambee_exclusions
        WHERE head_parish_id = :hpid AND harambee_target = :ht AND harambee_id = :hid
    """), {"hpid": head_parish_id, "ht": harambee_target, "hid": harambee_id}).all()
    return [r[0] for r in rows]


# ── Harambee member IDs by target (for mobile app) ────────────────────────

def get_member_harambee_ids(db: Session, member_id: int, target: str) -> List[int]:
    """Get all harambee IDs a member is involved in."""
    tables = get_tables_by_target(target)
    if not tables:
        return []
    target_table, _, _, contrib_table, _ = tables
    ids = set()
    for r in db.execute(text(f"SELECT DISTINCT harambee_id FROM {target_table} WHERE member_id = :mid"), {"mid": member_id}).all():
        if r[0]:
            ids.add(r[0])
    for r in db.execute(text(f"SELECT DISTINCT harambee_id FROM {contrib_table} WHERE member_id = :mid"), {"mid": member_id}).all():
        if r[0]:
            ids.add(r[0])
    return sorted(ids, reverse=True)


# ── Utility ────────────────────────────────────────────────────────────────

def calculate_percentage(numerator: float, denominator: float) -> float:
    if denominator == 0:
        return 0.0
    return (numerator / denominator) * 100


def get_quarter_dates(year: int, quarter: int) -> Tuple[str, str]:
    """Get start and end dates for a quarter."""
    starts = {1: f"{year}-01-01", 2: f"{year}-04-01", 3: f"{year}-07-01", 4: f"{year}-10-01"}
    ends = {1: f"{year}-03-31", 2: f"{year}-06-30", 3: f"{year}-09-30", 4: f"{year}-12-31"}
    return starts[quarter], ends[quarter]


def get_quarter_from_date(date_str: str) -> int:
    """Get quarter number (1-4) from a date string."""
    month = int(date_str.split("-")[1])
    return (month - 1) // 3 + 1
