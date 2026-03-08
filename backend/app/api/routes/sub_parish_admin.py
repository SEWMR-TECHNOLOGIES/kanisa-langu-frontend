# api/routes/sub_parish_admin.py
"""Sub Parish, Community, and Group admin routes — lower-level scoped operations."""

from fastapi import APIRouter, Depends, HTTPException, Query
from pydantic import BaseModel
from sqlalchemy.orm import Session
from sqlalchemy import func, or_
from typing import Optional
from datetime import date as date_type

from core.database import get_db
from models.admins import Admin
from models.hierarchy import SubParish, Community, Group
from models.members import ChurchMember
from models.finance import Revenue, Expense
from models.harambee import Harambee, HarambeeContribution
from models.envelope import EnvelopeContribution
from models.operations import Attendance
from utils.auth import get_current_admin
from utils.response import success_response, error_response

router = APIRouter(prefix="/sub-level", tags=["Sub Parish / Community / Group Admin"])


def _get_admin_scope(admin: Admin) -> dict:
    """Return the scope filters based on admin level."""
    if admin.admin_level == "sub_parish":
        if not admin.sub_parish_id:
            raise HTTPException(403, "No sub parish assigned")
        return {"sub_parish_id": admin.sub_parish_id, "head_parish_id": admin.head_parish_id}
    elif admin.admin_level == "community":
        if not admin.community_id:
            raise HTTPException(403, "No community assigned")
        return {"community_id": admin.community_id, "head_parish_id": admin.head_parish_id}
    elif admin.admin_level == "group":
        if not admin.group_id:
            raise HTTPException(403, "No group assigned")
        return {"group_id": admin.group_id, "head_parish_id": admin.head_parish_id}
    raise HTTPException(403, "Sub-parish, community, or group level access required")


# ═══════════════════════════════════════════════════════════════
# DASHBOARD
# ═══════════════════════════════════════════════════════════════

@router.get("/dashboard")
def sub_level_dashboard(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    scope = _get_admin_scope(admin)

    # Get entity name
    entity_name = ""
    if admin.admin_level == "sub_parish":
        sp = db.query(SubParish).filter(SubParish.id == scope["sub_parish_id"]).first()
        entity_name = sp.name if sp else ""
    elif admin.admin_level == "community":
        c = db.query(Community).filter(Community.id == scope["community_id"]).first()
        entity_name = c.name if c else ""
    elif admin.admin_level == "group":
        g = db.query(Group).filter(Group.id == scope["group_id"]).first()
        entity_name = g.name if g else ""

    # Count members in scope
    mq = db.query(func.count(ChurchMember.id)).filter(ChurchMember.is_active == True)
    if "sub_parish_id" in scope:
        mq = mq.filter(ChurchMember.sub_parish_id == scope["sub_parish_id"])
    if "community_id" in scope:
        mq = mq.filter(ChurchMember.community_id == scope["community_id"])

    return success_response(data={
        "admin_level": admin.admin_level,
        "entity_name": entity_name,
        "total_members": mq.scalar(),
    })


# ═══════════════════════════════════════════════════════════════
# COMMUNITIES (sub-parish admin only)
# ═══════════════════════════════════════════════════════════════

@router.get("/communities")
def list_communities(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    scope = _get_admin_scope(admin)
    if admin.admin_level != "sub_parish":
        raise HTTPException(403, "Only sub-parish admins can list communities")
    rows = db.query(Community).filter(
        Community.sub_parish_id == scope["sub_parish_id"], Community.is_active == True
    ).order_by(Community.name).all()
    return success_response(data=[{"id": c.id, "name": c.name, "description": c.description} for c in rows])


# ═══════════════════════════════════════════════════════════════
# MEMBERS (scoped to admin level)
# ═══════════════════════════════════════════════════════════════

@router.get("/members")
def list_members(
    search: Optional[str] = None,
    page: int = Query(1, ge=1),
    limit: int = Query(50, ge=1, le=500),
    db: Session = Depends(get_db),
    admin: Admin = Depends(get_current_admin),
):
    scope = _get_admin_scope(admin)
    q = db.query(ChurchMember).filter(ChurchMember.is_active == True)

    if "community_id" in scope:
        q = q.filter(ChurchMember.community_id == scope["community_id"])
    elif "sub_parish_id" in scope:
        q = q.filter(ChurchMember.sub_parish_id == scope["sub_parish_id"])
    else:
        q = q.filter(ChurchMember.head_parish_id == scope["head_parish_id"])

    if search:
        s = f"%{search}%"
        q = q.filter(or_(
            ChurchMember.first_name.ilike(s), ChurchMember.last_name.ilike(s),
            ChurchMember.phone.ilike(s), ChurchMember.envelope_number.ilike(s),
        ))

    total = q.count()
    members = q.order_by(ChurchMember.envelope_number).offset((page - 1) * limit).limit(limit).all()
    return success_response(data={
        "members": [{
            "id": m.id, "first_name": m.first_name, "middle_name": m.middle_name,
            "last_name": m.last_name, "envelope_number": m.envelope_number,
            "phone": m.phone, "gender": m.gender,
        } for m in members],
        "total": total, "page": page, "total_pages": -(-total // limit),
    })


# ═══════════════════════════════════════════════════════════════
# FINANCIAL — Revenue & Expense (scoped)
# ═══════════════════════════════════════════════════════════════

@router.get("/revenues")
def list_revenues(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    scope = _get_admin_scope(admin)
    q = db.query(Revenue).filter(Revenue.head_parish_id == scope["head_parish_id"])
    if "sub_parish_id" in scope:
        q = q.filter(Revenue.sub_parish_id == scope["sub_parish_id"])
    if "community_id" in scope:
        q = q.filter(Revenue.community_id == scope["community_id"])
    if "group_id" in scope:
        q = q.filter(Revenue.group_id == scope["group_id"])
    rows = q.order_by(Revenue.revenue_date.desc()).limit(100).all()
    return success_response(data=[{
        "id": r.id, "amount": float(r.amount), "revenue_date": str(r.revenue_date),
        "payment_method": r.payment_method, "description": r.description,
    } for r in rows])

@router.get("/expenses")
def list_expenses(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    scope = _get_admin_scope(admin)
    q = db.query(Expense).filter(Expense.head_parish_id == scope["head_parish_id"])
    if "sub_parish_id" in scope:
        q = q.filter(Expense.sub_parish_id == scope["sub_parish_id"])
    if "community_id" in scope:
        q = q.filter(Expense.community_id == scope["community_id"])
    if "group_id" in scope:
        q = q.filter(Expense.group_id == scope["group_id"])
    rows = q.order_by(Expense.expense_date.desc()).limit(100).all()
    return success_response(data=[{
        "id": e.id, "amount": float(e.amount), "expense_date": str(e.expense_date),
        "payment_method": e.payment_method, "description": e.description,
    } for e in rows])


# ═══════════════════════════════════════════════════════════════
# HARAMBEE (scoped)
# ═══════════════════════════════════════════════════════════════

@router.get("/harambees")
def list_harambees(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    scope = _get_admin_scope(admin)
    q = db.query(Harambee).filter(Harambee.head_parish_id == scope["head_parish_id"], Harambee.is_active == True)
    if admin.admin_level == "sub_parish":
        q = q.filter(Harambee.management_level == "sub_parish")
    elif admin.admin_level == "community":
        q = q.filter(Harambee.management_level == "community")
    elif admin.admin_level == "group":
        q = q.filter(Harambee.management_level == "group")
    return success_response(data=[{
        "id": h.id, "name": h.name, "amount": float(h.amount),
        "from_date": str(h.from_date), "to_date": str(h.to_date),
    } for h in q.order_by(Harambee.from_date.desc()).all()])


# ═══════════════════════════════════════════════════════════════
# ATTENDANCE (scoped)
# ═══════════════════════════════════════════════════════════════

@router.get("/attendance")
def list_attendance(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    scope = _get_admin_scope(admin)
    q = db.query(Attendance).filter(Attendance.head_parish_id == scope["head_parish_id"])
    if "sub_parish_id" in scope:
        q = q.filter(Attendance.sub_parish_id == scope["sub_parish_id"])
    if "community_id" in scope:
        q = q.filter(Attendance.community_id == scope["community_id"])
    if "group_id" in scope:
        q = q.filter(Attendance.group_id == scope["group_id"])
    rows = q.order_by(Attendance.attendance_date.desc()).limit(100).all()
    return success_response(data=[{
        "id": a.id, "event_title": a.event_title,
        "male": a.male_attendance, "female": a.female_attendance,
        "children": a.children_attendance,
        "date": str(a.attendance_date),
    } for a in rows])


# ═══════════════════════════════════════════════════════════════
# ENVELOPE SUMMARY (scoped)
# ═══════════════════════════════════════════════════════════════

@router.get("/envelope-summary")
def envelope_summary(year: Optional[int] = None, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    scope = _get_admin_scope(admin)
    q = db.query(func.coalesce(func.sum(EnvelopeContribution.amount), 0)).filter(
        EnvelopeContribution.head_parish_id == scope["head_parish_id"]
    )
    if "sub_parish_id" in scope:
        q = q.filter(EnvelopeContribution.sub_parish_id == scope["sub_parish_id"])
    if "community_id" in scope:
        q = q.filter(EnvelopeContribution.community_id == scope["community_id"])
    if year:
        q = q.filter(func.extract("year", EnvelopeContribution.contribution_date) == year)
    return success_response(data={"total_contributions": float(q.scalar() or 0)})
