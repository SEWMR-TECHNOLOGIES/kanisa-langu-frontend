# api/routes/sub_parish_admin.py
"""Sub Parish, Community, and Group admin routes — lower-level scoped operations.
Includes: members, finance, harambee, envelope, meetings, attendance, notifications,
expense management, admin creation, and reports."""

from datetime import date as date_type, time as time_type
from decimal import Decimal
from fastapi import APIRouter, Depends, HTTPException, Query
from pydantic import BaseModel
from sqlalchemy.orm import Session
from sqlalchemy import func, or_
from typing import Optional, List

from core.database import get_db
from models.admins import Admin
from models.hierarchy import SubParish, Community, Group, HeadParish
from models.members import ChurchMember
from models.finance import Revenue, Expense, RevenueStream, BankAccount, ExpenseGroup, ExpenseName, ExpenseRequest, ExpenseRequestItem
from models.harambee import Harambee, HarambeeContribution, HarambeeTarget, HarambeeGroup, HarambeeGroupMember
from models.envelope import EnvelopeTarget, EnvelopeContribution
from models.operations import Attendance, Meeting, MeetingAgenda
from models.sunday_service import SundayService
from models.payments import PaymentGatewayWallet
from utils.auth import hash_password, get_current_admin
from utils.validation import is_valid_phone
from utils.helpers import update_account_balance, get_account_id_by_revenue_stream
from utils.response import success_response, error_response

router = APIRouter(prefix="/sub-level", tags=["Sub Parish / Community / Group Admin"])


def _get_admin_scope(admin: Admin) -> dict:
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


def _filter_by_scope(q, scope, model):
    """Apply scope filters to a query for a given model."""
    if hasattr(model, "head_parish_id"):
        q = q.filter(model.head_parish_id == scope["head_parish_id"])
    if "sub_parish_id" in scope and hasattr(model, "sub_parish_id"):
        q = q.filter(model.sub_parish_id == scope["sub_parish_id"])
    if "community_id" in scope and hasattr(model, "community_id"):
        q = q.filter(model.community_id == scope["community_id"])
    if "group_id" in scope and hasattr(model, "group_id"):
        q = q.filter(model.group_id == scope["group_id"])
    return q


# ═══════════════════════════════════════════════════════════════
# DASHBOARD
# ═══════════════════════════════════════════════════════════════

@router.get("/dashboard")
def sub_level_dashboard(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    scope = _get_admin_scope(admin)
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

    mq = db.query(func.count(ChurchMember.id)).filter(ChurchMember.is_active == True)
    if "sub_parish_id" in scope:
        mq = mq.filter(ChurchMember.sub_parish_id == scope["sub_parish_id"])
    if "community_id" in scope:
        mq = mq.filter(ChurchMember.community_id == scope["community_id"])

    rev_q = db.query(func.coalesce(func.sum(Revenue.amount), 0)).filter(Revenue.head_parish_id == scope["head_parish_id"])
    exp_q = db.query(func.coalesce(func.sum(Expense.amount), 0)).filter(Expense.head_parish_id == scope["head_parish_id"])
    if "sub_parish_id" in scope:
        rev_q = rev_q.filter(Revenue.sub_parish_id == scope["sub_parish_id"])
        exp_q = exp_q.filter(Expense.sub_parish_id == scope["sub_parish_id"])

    return success_response(data={
        "admin_level": admin.admin_level,
        "entity_name": entity_name,
        "total_members": mq.scalar(),
        "total_revenue": float(rev_q.scalar() or 0),
        "total_expense": float(exp_q.scalar() or 0),
    })


# ═══════════════════════════════════════════════════════════════
# ADMIN CREATION (sub-parish creates community/group admins)
# ═══════════════════════════════════════════════════════════════

class SubAdminCreate(BaseModel):
    fullname: str
    phone: str
    email: Optional[str] = ""
    role: str
    admin_level: str
    community_id: Optional[int] = None
    group_id: Optional[int] = None

@router.post("/admins")
def create_sub_admin(body: SubAdminCreate, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    scope = _get_admin_scope(admin)
    if admin.admin_level != "sub_parish":
        raise HTTPException(403, "Only sub-parish admins can create sub-level admins")
    if body.admin_level not in ("community", "group"):
        raise HTTPException(400, "Can only create community or group admins")
    if not is_valid_phone(body.phone):
        raise HTTPException(400, "Invalid phone")
    hp = db.query(HeadParish).filter(HeadParish.id == scope["head_parish_id"]).first()
    a = Admin(
        fullname=body.fullname, phone=body.phone, email=body.email or None,
        role=body.role, password=hash_password("KanisaLangu"),
        admin_level=body.admin_level,
        diocese_id=hp.diocese_id if hp else None,
        province_id=hp.province_id if hp else None,
        head_parish_id=scope["head_parish_id"],
        sub_parish_id=scope["sub_parish_id"],
        community_id=body.community_id, group_id=body.group_id,
    )
    db.add(a); db.commit(); db.refresh(a)
    return success_response("Admin created", {"id": a.id})


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
# MEMBERS (scoped)
# ═══════════════════════════════════════════════════════════════

@router.get("/members")
def list_members(
    search: Optional[str] = None,
    page: int = Query(1, ge=1), limit: int = Query(50, ge=1, le=500),
    db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin),
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
# SUNDAY SERVICES (read-only from sub level)
# ═══════════════════════════════════════════════════════════════

@router.get("/sunday-services")
def list_services(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    scope = _get_admin_scope(admin)
    rows = db.query(SundayService).filter(SundayService.head_parish_id == scope["head_parish_id"]).order_by(SundayService.service_date.desc()).limit(52).all()
    return success_response(data=[{
        "id": s.id, "service_date": str(s.service_date),
        "service_color_id": s.service_color_id, "base_scripture_text": s.base_scripture_text,
    } for s in rows])


# ═══════════════════════════════════════════════════════════════
# MEETINGS
# ═══════════════════════════════════════════════════════════════

class MeetingBody(BaseModel):
    title: str
    description: Optional[str] = None
    meeting_date: date_type
    meeting_time: str
    meeting_place: str

@router.get("/meetings")
def list_meetings(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    scope = _get_admin_scope(admin)
    q = db.query(Meeting).filter(Meeting.head_parish_id == scope["head_parish_id"])
    if "sub_parish_id" in scope:
        q = q.filter(Meeting.sub_parish_id == scope["sub_parish_id"])
    if "community_id" in scope:
        q = q.filter(Meeting.community_id == scope["community_id"])
    rows = q.order_by(Meeting.meeting_date.desc()).limit(50).all()
    return success_response(data=[{
        "id": m.id, "title": m.title, "meeting_date": str(m.meeting_date),
        "meeting_time": str(m.meeting_time), "meeting_place": m.meeting_place,
    } for m in rows])

@router.post("/meetings")
def create_meeting(body: MeetingBody, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    scope = _get_admin_scope(admin)
    m = Meeting(
        head_parish_id=scope["head_parish_id"],
        sub_parish_id=scope.get("sub_parish_id"),
        community_id=scope.get("community_id"),
        title=body.title, description=body.description,
        meeting_date=body.meeting_date,
        meeting_time=time_type.fromisoformat(body.meeting_time),
        meeting_place=body.meeting_place,
    )
    db.add(m); db.commit(); db.refresh(m)
    return success_response("Meeting created", {"id": m.id})


# ═══════════════════════════════════════════════════════════════
# REVENUE & EXPENSES (scoped)
# ═══════════════════════════════════════════════════════════════

@router.get("/revenue-streams")
def list_revenue_streams(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    scope = _get_admin_scope(admin)
    rows = db.query(RevenueStream).filter(
        RevenueStream.entity_type == "head_parish", RevenueStream.entity_id == scope["head_parish_id"],
        RevenueStream.is_active == True,
    ).all()
    return success_response(data=[{"id": r.id, "name": r.name, "account_id": r.account_id} for r in rows])

class RecordRevenueBody(BaseModel):
    revenue_stream_id: int
    amount: float
    payment_method: str = "Cash"
    description: Optional[str] = None
    revenue_date: date_type

@router.post("/revenues")
def record_revenue(body: RecordRevenueBody, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    scope = _get_admin_scope(admin)
    if body.amount <= 0:
        raise HTTPException(400, "Amount must be > 0")
    rev = Revenue(
        head_parish_id=scope["head_parish_id"],
        sub_parish_id=scope.get("sub_parish_id"),
        community_id=scope.get("community_id"),
        group_id=scope.get("group_id"),
        management_level=admin.admin_level,
        revenue_stream_id=body.revenue_stream_id,
        amount=body.amount, payment_method=body.payment_method,
        description=body.description, revenue_date=body.revenue_date,
        recorded_by=admin.id,
    )
    db.add(rev)
    account_id = get_account_id_by_revenue_stream(db, body.revenue_stream_id)
    if account_id:
        update_account_balance(db, account_id, Decimal(str(body.amount)))
    db.commit(); db.refresh(rev)
    return success_response("Revenue recorded", {"id": rev.id})

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

class RecordExpenseBody(BaseModel):
    expense_name_id: int
    amount: float
    payment_method: str = "Cash"
    description: Optional[str] = None
    expense_date: date_type

@router.post("/expenses")
def record_expense(body: RecordExpenseBody, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    scope = _get_admin_scope(admin)
    if body.amount <= 0:
        raise HTTPException(400, "Amount must be > 0")
    exp = Expense(
        head_parish_id=scope["head_parish_id"],
        sub_parish_id=scope.get("sub_parish_id"),
        community_id=scope.get("community_id"),
        group_id=scope.get("group_id"),
        management_level=admin.admin_level,
        expense_name_id=body.expense_name_id,
        amount=body.amount, payment_method=body.payment_method,
        description=body.description, expense_date=body.expense_date,
        recorded_by=admin.id,
    )
    db.add(exp); db.commit(); db.refresh(exp)
    return success_response("Expense recorded", {"id": exp.id})

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
# EXPENSE GROUPS & NAMES
# ═══════════════════════════════════════════════════════════════

@router.get("/expense-groups")
def list_expense_groups(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    scope = _get_admin_scope(admin)
    rows = db.query(ExpenseGroup).filter(
        ExpenseGroup.head_parish_id == scope["head_parish_id"],
        ExpenseGroup.management_level == admin.admin_level,
    ).all()
    return success_response(data=[{"id": g.id, "name": g.name} for g in rows])

class ExpenseGroupBody(BaseModel):
    name: str

@router.post("/expense-groups")
def create_expense_group(body: ExpenseGroupBody, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    scope = _get_admin_scope(admin)
    db.add(ExpenseGroup(head_parish_id=scope["head_parish_id"], name=body.name, management_level=admin.admin_level))
    db.commit()
    return success_response("Expense group created")

@router.get("/expense-names")
def list_expense_names(expense_group_id: int, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    _get_admin_scope(admin)
    rows = db.query(ExpenseName).filter(ExpenseName.expense_group_id == expense_group_id).all()
    return success_response(data=[{"id": n.id, "name": n.name} for n in rows])

class ExpenseNameBody(BaseModel):
    expense_group_id: int
    name: str

@router.post("/expense-names")
def create_expense_name(body: ExpenseNameBody, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    _get_admin_scope(admin)
    db.add(ExpenseName(expense_group_id=body.expense_group_id, name=body.name, management_level=admin.admin_level))
    db.commit()
    return success_response("Expense name created")


# ═══════════════════════════════════════════════════════════════
# EXPENSE REQUESTS
# ═══════════════════════════════════════════════════════════════

class ExpenseRequestBody(BaseModel):
    title: str
    description: Optional[str] = None
    items: List[dict] = []  # [{expense_name_id, amount, quantity}]

@router.post("/expense-requests")
def make_expense_request(body: ExpenseRequestBody, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    scope = _get_admin_scope(admin)
    req = ExpenseRequest(
        head_parish_id=scope["head_parish_id"],
        sub_parish_id=scope.get("sub_parish_id"),
        community_id=scope.get("community_id"),
        group_id=scope.get("group_id"),
        title=body.title, description=body.description,
        requested_by=admin.id,
    )
    db.add(req); db.flush()
    for item in body.items:
        db.add(ExpenseRequestItem(
            expense_request_id=req.id,
            expense_name_id=item.get("expense_name_id"),
            amount=item.get("amount", 0),
            quantity=item.get("quantity", 1),
        ))
    db.commit(); db.refresh(req)
    return success_response("Expense request submitted", {"id": req.id})

@router.get("/expense-requests")
def list_expense_requests(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    scope = _get_admin_scope(admin)
    q = db.query(ExpenseRequest).filter(ExpenseRequest.head_parish_id == scope["head_parish_id"])
    if "sub_parish_id" in scope:
        q = q.filter(ExpenseRequest.sub_parish_id == scope["sub_parish_id"])
    rows = q.order_by(ExpenseRequest.created_at.desc()).limit(50).all()
    return success_response(data=[{
        "id": r.id, "title": r.title, "status": r.status,
        "created_at": str(r.created_at),
    } for r in rows])


# ═══════════════════════════════════════════════════════════════
# HARAMBEE (scoped)
# ═══════════════════════════════════════════════════════════════

@router.get("/harambees")
def list_harambees(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    scope = _get_admin_scope(admin)
    q = db.query(Harambee).filter(Harambee.head_parish_id == scope["head_parish_id"], Harambee.is_active == True)
    if admin.admin_level == "sub_parish":
        q = q.filter(Harambee.management_level.in_(["head_parish", "sub_parish"]))
    elif admin.admin_level == "community":
        q = q.filter(Harambee.management_level.in_(["head_parish", "sub_parish", "community"]))
    elif admin.admin_level == "group":
        q = q.filter(Harambee.management_level.in_(["head_parish", "group"]))
    return success_response(data=[{
        "id": h.id, "name": h.name, "amount": float(h.amount),
        "from_date": str(h.from_date), "to_date": str(h.to_date),
        "management_level": h.management_level,
    } for h in q.order_by(Harambee.from_date.desc()).all()])

class HarambeeContribBody(BaseModel):
    harambee_id: int
    member_id: int
    amount: float
    contribution_date: date_type
    payment_method: str = "Cash"

@router.post("/harambee-contributions")
def record_harambee_contribution(body: HarambeeContribBody, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    scope = _get_admin_scope(admin)
    if body.amount <= 0:
        raise HTTPException(400, "Amount must be > 0")
    member = db.query(ChurchMember).filter(ChurchMember.id == body.member_id).first()
    if not member:
        raise HTTPException(404, "Member not found")
    contrib = HarambeeContribution(
        harambee_id=body.harambee_id, member_id=body.member_id,
        amount=body.amount, contribution_date=body.contribution_date,
        head_parish_id=scope["head_parish_id"],
        sub_parish_id=member.sub_parish_id, community_id=member.community_id,
        payment_method=body.payment_method,
    )
    db.add(contrib); db.commit(); db.refresh(contrib)
    return success_response("Contribution recorded", {"id": contrib.id})

class HarambeeTargetBody(BaseModel):
    harambee_id: int
    member_id: int
    target: float

@router.post("/harambee-targets")
def set_harambee_target(body: HarambeeTargetBody, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    _get_admin_scope(admin)
    existing = db.query(HarambeeTarget).filter(
        HarambeeTarget.harambee_id == body.harambee_id, HarambeeTarget.member_id == body.member_id
    ).first()
    if existing:
        existing.target = body.target
    else:
        member = db.query(ChurchMember).filter(ChurchMember.id == body.member_id).first()
        db.add(HarambeeTarget(
            harambee_id=body.harambee_id, member_id=body.member_id,
            target=body.target, target_type="individual",
            sub_parish_id=member.sub_parish_id if member else None,
            community_id=member.community_id if member else None,
        ))
    db.commit()
    return success_response("Target set")

@router.get("/harambee-groups")
def list_harambee_groups(harambee_id: int, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    _get_admin_scope(admin)
    groups = db.query(HarambeeGroup).filter(HarambeeGroup.harambee_id == harambee_id).all()
    return success_response(data=[{
        "id": g.id, "name": g.name, "target": float(g.target),
    } for g in groups])


# ═══════════════════════════════════════════════════════════════
# ENVELOPE (scoped)
# ═══════════════════════════════════════════════════════════════

class EnvelopeContribBody(BaseModel):
    member_id: int
    amount: float
    contribution_date: date_type
    payment_method: str = "Cash"

@router.post("/envelope-contributions")
def record_envelope_contribution(body: EnvelopeContribBody, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    scope = _get_admin_scope(admin)
    if body.amount <= 0:
        raise HTTPException(400, "Amount must be > 0")
    member = db.query(ChurchMember).filter(ChurchMember.id == body.member_id).first()
    if not member:
        raise HTTPException(404, "Member not found")
    contrib = EnvelopeContribution(
        member_id=body.member_id, amount=body.amount,
        contribution_date=body.contribution_date,
        head_parish_id=scope["head_parish_id"],
        sub_parish_id=member.sub_parish_id, community_id=member.community_id,
        payment_method=body.payment_method,
    )
    db.add(contrib); db.commit(); db.refresh(contrib)
    return success_response("Contribution recorded", {"id": contrib.id})

class EnvelopeTargetBody(BaseModel):
    member_id: int
    target: float
    year: int

@router.post("/envelope-targets")
def set_envelope_target(body: EnvelopeTargetBody, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    _get_admin_scope(admin)
    existing = db.query(EnvelopeTarget).filter(
        EnvelopeTarget.member_id == body.member_id,
        func.extract("year", EnvelopeTarget.from_date) == body.year,
    ).first()
    if existing:
        existing.target = body.target
    else:
        db.add(EnvelopeTarget(
            member_id=body.member_id, target=body.target,
            from_date=date_type(body.year, 1, 1), end_date=date_type(body.year, 12, 31),
        ))
    db.commit()
    return success_response("Envelope target set")

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


# ═══════════════════════════════════════════════════════════════
# ATTENDANCE (scoped)
# ═══════════════════════════════════════════════════════════════

class AttendanceBody(BaseModel):
    event_title: str
    male_attendance: int = 0
    female_attendance: int = 0
    children_attendance: int = 0
    attendance_date: date_type

@router.post("/attendance")
def record_attendance(body: AttendanceBody, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    scope = _get_admin_scope(admin)
    a = Attendance(
        head_parish_id=scope["head_parish_id"],
        sub_parish_id=scope.get("sub_parish_id"),
        community_id=scope.get("community_id"),
        group_id=scope.get("group_id"),
        management_level=admin.admin_level,
        event_title=body.event_title,
        male_attendance=body.male_attendance,
        female_attendance=body.female_attendance,
        children_attendance=body.children_attendance,
        attendance_date=body.attendance_date,
        recorded_by=admin.id,
    )
    db.add(a); db.commit(); db.refresh(a)
    return success_response("Attendance recorded", {"id": a.id})

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
        "children": a.children_attendance, "date": str(a.attendance_date),
    } for a in rows])


# ═══════════════════════════════════════════════════════════════
# BANK ACCOUNTS (group level)
# ═══════════════════════════════════════════════════════════════

@router.get("/bank-accounts")
def list_bank_accounts(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    scope = _get_admin_scope(admin)
    entity_type = admin.admin_level
    entity_id = scope.get("sub_parish_id") or scope.get("community_id") or scope.get("group_id")
    rows = db.query(BankAccount).filter(
        BankAccount.entity_type == entity_type, BankAccount.entity_id == entity_id, BankAccount.is_active == True
    ).all()
    return success_response(data=[{
        "id": a.id, "account_name": a.account_name, "account_number": a.account_number,
        "balance": float(a.balance),
    } for a in rows])

class BankAccountBody(BaseModel):
    account_name: str
    account_number: str
    bank_id: int
    balance: float = 0.0

@router.post("/bank-accounts")
def create_bank_account(body: BankAccountBody, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    scope = _get_admin_scope(admin)
    entity_type = admin.admin_level
    entity_id = scope.get("sub_parish_id") or scope.get("community_id") or scope.get("group_id")
    acc = BankAccount(entity_type=entity_type, entity_id=entity_id, **body.dict())
    db.add(acc); db.commit(); db.refresh(acc)
    return success_response("Bank account created", {"id": acc.id})


# ═══════════════════════════════════════════════════════════════
# PAYMENT GATEWAY WALLETS
# ═══════════════════════════════════════════════════════════════

@router.get("/payment-wallets")
def list_payment_wallets(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    scope = _get_admin_scope(admin)
    rows = db.query(PaymentGatewayWallet).filter(PaymentGatewayWallet.head_parish_id == scope["head_parish_id"]).all()
    return success_response(data=[{
        "id": w.id, "wallet_name": w.wallet_name, "wallet_number": w.wallet_number,
        "provider": w.provider,
    } for w in rows])


# ═══════════════════════════════════════════════════════════════
# NOTIFICATIONS
# ═══════════════════════════════════════════════════════════════

class NotificationBody(BaseModel):
    title: str
    body: str

@router.post("/notifications")
def send_notification(body: NotificationBody, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    _get_admin_scope(admin)
    # TODO: Integrate FCM/SMS notification
    return success_response("Notification queued")


# ═══════════════════════════════════════════════════════════════
# REPORTS (scoped)
# ═══════════════════════════════════════════════════════════════

@router.get("/reports/financial-summary")
def financial_summary(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    scope = _get_admin_scope(admin)
    rev_q = db.query(func.coalesce(func.sum(Revenue.amount), 0)).filter(Revenue.head_parish_id == scope["head_parish_id"])
    exp_q = db.query(func.coalesce(func.sum(Expense.amount), 0)).filter(Expense.head_parish_id == scope["head_parish_id"])
    if "sub_parish_id" in scope:
        rev_q = rev_q.filter(Revenue.sub_parish_id == scope["sub_parish_id"])
        exp_q = exp_q.filter(Expense.sub_parish_id == scope["sub_parish_id"])
    if "community_id" in scope:
        rev_q = rev_q.filter(Revenue.community_id == scope["community_id"])
        exp_q = exp_q.filter(Expense.community_id == scope["community_id"])
    if "group_id" in scope:
        rev_q = rev_q.filter(Revenue.group_id == scope["group_id"])
        exp_q = exp_q.filter(Expense.group_id == scope["group_id"])
    total_rev = float(rev_q.scalar() or 0)
    total_exp = float(exp_q.scalar() or 0)
    return success_response(data={"total_revenue": total_rev, "total_expense": total_exp, "balance": total_rev - total_exp})

@router.get("/reports/harambee-summary")
def harambee_report(harambee_id: int, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    scope = _get_admin_scope(admin)
    total = db.query(func.coalesce(func.sum(HarambeeContribution.amount), 0)).filter(
        HarambeeContribution.harambee_id == harambee_id
    )
    if "sub_parish_id" in scope:
        total = total.filter(HarambeeContribution.sub_parish_id == scope["sub_parish_id"])
    if "community_id" in scope:
        total = total.filter(HarambeeContribution.community_id == scope["community_id"])
    return success_response(data={"total_contributions": float(total.scalar() or 0)})
