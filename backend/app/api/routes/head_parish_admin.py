# api/routes/head_parish_admin.py
"""Head Parish admin routes — the core operational level.
Full CRUD for sub-parishes, communities, groups, members, finance, harambee,
envelope, attendance, meetings, assets, sunday services, events, config."""

from datetime import date as date_type, time as time_type
from decimal import Decimal
from fastapi import APIRouter, Depends, HTTPException, Query
from pydantic import BaseModel
from sqlalchemy.orm import Session
from sqlalchemy import func, or_, text
from typing import Optional, List

from core.database import get_db
from models.admins import Admin
from models.hierarchy import HeadParish, SubParish, Community, Group
from models.members import ChurchMember, MemberExclusion, ChurchLeader, ChurchChoir
from models.finance import (
    BankAccount, RevenueStream, Revenue, ExpenseGroup, ExpenseName,
    Expense, ExpenseRequest, ExpenseRequestItem, AnnualRevenueTarget, AnnualExpenseBudget,
)
from models.harambee import (
    Harambee, HarambeeGroup, HarambeeGroupMember, HarambeeTarget,
    HarambeeContribution, HarambeeClass, HarambeeDistribution,
    HarambeeExclusion, HarambeeExpense, HarambeeLetterStatus, DelayedHarambeeNotification,
)
from models.envelope import EnvelopeTarget, EnvelopeContribution
from models.operations import (
    Attendance, AttendanceBenchmark, Meeting, MeetingAgenda,
    MeetingMinutes, MeetingNotes, ChurchEvent, Asset, AssetRevenue, AssetExpense, AssetStatusLog,
)
from models.sunday_service import (
    SundayService, SundayServiceScripture, SundayServiceSong,
    SundayServiceChoir, SundayServiceOffering, SundayServiceLeader,
    SundayServiceElder, SundayServicePreacher, HeadParishServiceTime, HeadParishServicesCount,
)
from models.payments import PaymentGatewayWallet
from models.banking import BankPosting, BankClosingBalance
from models.config import SmsApiConfig, RevenueGroupModel, ProgramRevenueMap
from models.misc import HeadParishDebit, MemberExclusionReason, HarambeeExclusionReason, Feedback
from utils.auth import hash_password, get_current_admin
from utils.validation import is_valid_email, is_valid_phone, normalize_phone, validate_age
from utils.helpers import update_account_balance, get_account_id_by_revenue_stream
from utils.response import success_response, error_response

router = APIRouter(prefix="/head-parish", tags=["Head Parish Admin"])


def _require_hp_admin(admin: Admin) -> int:
    if admin.admin_level != "head_parish":
        raise HTTPException(403, "Head parish level access required")
    if not admin.head_parish_id:
        raise HTTPException(403, "No head parish assigned")
    return admin.head_parish_id


# ═══════════════════════════════════════════════════════════════
# DASHBOARD
# ═══════════════════════════════════════════════════════════════

@router.get("/dashboard")
def hp_dashboard(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    hp = db.query(HeadParish).filter(HeadParish.id == hpid).first()
    return success_response(data={
        "head_parish": {"id": hp.id, "name": hp.name} if hp else None,
        "total_sub_parishes": db.query(func.count(SubParish.id)).filter(SubParish.head_parish_id == hpid, SubParish.is_active == True).scalar(),
        "total_communities": db.query(func.count(Community.id)).filter(Community.head_parish_id == hpid, Community.is_active == True).scalar(),
        "total_groups": db.query(func.count(Group.id)).filter(Group.head_parish_id == hpid, Group.is_active == True).scalar(),
        "total_members": db.query(func.count(ChurchMember.id)).filter(ChurchMember.head_parish_id == hpid, ChurchMember.is_active == True).scalar(),
        "total_revenue": float(db.query(func.coalesce(func.sum(Revenue.amount), 0)).filter(Revenue.head_parish_id == hpid).scalar()),
        "total_expense": float(db.query(func.coalesce(func.sum(Expense.amount), 0)).filter(Expense.head_parish_id == hpid).scalar()),
    })


# ═══════════════════════════════════════════════════════════════
# SUB PARISH CRUD
# ═══════════════════════════════════════════════════════════════

class SubParishBody(BaseModel):
    name: str
    description: Optional[str] = None

@router.get("/sub-parishes")
def list_sub_parishes(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    rows = db.query(SubParish).filter(SubParish.head_parish_id == hpid, SubParish.is_active == True).order_by(SubParish.name).all()
    result = []
    for sp in rows:
        comm_count = db.query(func.count(Community.id)).filter(Community.sub_parish_id == sp.id, Community.is_active == True).scalar()
        member_count = db.query(func.count(ChurchMember.id)).filter(ChurchMember.sub_parish_id == sp.id, ChurchMember.is_active == True).scalar()
        result.append({"id": sp.id, "name": sp.name, "description": sp.description, "community_count": comm_count, "member_count": member_count})
    return success_response(data=result)

@router.post("/sub-parishes")
def create_sub_parish(body: SubParishBody, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    name = body.name.strip().upper()
    if db.query(SubParish).filter(SubParish.name == name, SubParish.head_parish_id == hpid).first():
        raise HTTPException(400, "Sub parish already exists")
    sp = SubParish(name=name, head_parish_id=hpid, description=body.description)
    db.add(sp); db.commit(); db.refresh(sp)
    return success_response("Sub parish added", {"id": sp.id})

@router.put("/sub-parishes/{sp_id}")
def update_sub_parish(sp_id: int, body: SubParishBody, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    sp = db.query(SubParish).filter(SubParish.id == sp_id, SubParish.head_parish_id == hpid).first()
    if not sp:
        raise HTTPException(404, "Sub parish not found")
    sp.name = body.name.strip().upper()
    sp.description = body.description
    db.commit()
    return success_response("Sub parish updated")

@router.delete("/sub-parishes/{sp_id}")
def deactivate_sub_parish(sp_id: int, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    sp = db.query(SubParish).filter(SubParish.id == sp_id, SubParish.head_parish_id == hpid).first()
    if not sp:
        raise HTTPException(404, "Sub parish not found")
    sp.is_active = False; db.commit()
    return success_response("Sub parish deactivated")


# ═══════════════════════════════════════════════════════════════
# COMMUNITY CRUD
# ═══════════════════════════════════════════════════════════════

class CommunityBody(BaseModel):
    name: str
    sub_parish_id: int
    description: Optional[str] = None

@router.get("/communities")
def list_communities(sub_parish_id: Optional[int] = None, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    q = db.query(Community).filter(Community.head_parish_id == hpid, Community.is_active == True)
    if sub_parish_id:
        q = q.filter(Community.sub_parish_id == sub_parish_id)
    return success_response(data=[{"id": c.id, "name": c.name, "sub_parish_id": c.sub_parish_id, "description": c.description} for c in q.order_by(Community.name).all()])

@router.post("/communities")
def create_community(body: CommunityBody, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    name = body.name.strip().upper()
    if db.query(Community).filter(Community.name == name, Community.sub_parish_id == body.sub_parish_id).first():
        raise HTTPException(400, "Community already exists in this sub parish")
    c = Community(name=name, head_parish_id=hpid, sub_parish_id=body.sub_parish_id, description=body.description)
    db.add(c); db.commit(); db.refresh(c)
    return success_response("Community added", {"id": c.id})


# ═══════════════════════════════════════════════════════════════
# GROUP CRUD
# ═══════════════════════════════════════════════════════════════

class GroupBody(BaseModel):
    name: str
    description: Optional[str] = None

@router.get("/groups")
def list_groups(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    rows = db.query(Group).filter(Group.head_parish_id == hpid, Group.is_active == True).order_by(Group.name).all()
    return success_response(data=[{"id": g.id, "name": g.name, "description": g.description} for g in rows])

@router.post("/groups")
def create_group(body: GroupBody, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    name = body.name.strip().upper()
    if db.query(Group).filter(Group.name == name, Group.head_parish_id == hpid).first():
        raise HTTPException(400, "Group already exists")
    g = Group(name=name, head_parish_id=hpid, description=body.description)
    db.add(g); db.commit(); db.refresh(g)
    return success_response("Group added", {"id": g.id})


# ═══════════════════════════════════════════════════════════════
# MEMBER MANAGEMENT
# ═══════════════════════════════════════════════════════════════

class MemberCreate(BaseModel):
    title_id: Optional[int] = None
    first_name: str
    middle_name: Optional[str] = None
    last_name: str
    date_of_birth: date_type
    gender: str
    member_type: str = "Mwenyeji"
    sub_parish_id: int
    community_id: int
    envelope_number: Optional[str] = None
    occupation_id: Optional[int] = None
    phone: Optional[str] = None
    email: Optional[str] = None

class MemberUpdate(BaseModel):
    title_id: Optional[int] = None
    first_name: Optional[str] = None
    middle_name: Optional[str] = None
    last_name: Optional[str] = None
    occupation_id: Optional[int] = None
    phone: Optional[str] = None
    email: Optional[str] = None

@router.get("/members")
def list_members(
    sub_parish_id: Optional[int] = None,
    community_id: Optional[int] = None,
    search: Optional[str] = None,
    page: int = Query(1, ge=1),
    limit: int = Query(50, ge=1, le=500),
    db: Session = Depends(get_db),
    admin: Admin = Depends(get_current_admin),
):
    hpid = _require_hp_admin(admin)
    q = db.query(ChurchMember).filter(ChurchMember.head_parish_id == hpid, ChurchMember.is_active == True)
    if sub_parish_id:
        q = q.filter(ChurchMember.sub_parish_id == sub_parish_id)
    if community_id:
        q = q.filter(ChurchMember.community_id == community_id)
    if search:
        s = f"%{search}%"
        q = q.filter(or_(
            ChurchMember.first_name.ilike(s), ChurchMember.middle_name.ilike(s),
            ChurchMember.last_name.ilike(s), ChurchMember.phone.ilike(s),
            ChurchMember.envelope_number.ilike(s),
        ))
    total = q.count()
    members = q.order_by(ChurchMember.envelope_number).offset((page - 1) * limit).limit(limit).all()
    return success_response(data={
        "members": [{
            "id": m.id, "first_name": m.first_name, "middle_name": m.middle_name,
            "last_name": m.last_name, "envelope_number": m.envelope_number,
            "phone": m.phone, "gender": m.gender, "member_type": m.member_type,
            "sub_parish_id": m.sub_parish_id, "community_id": m.community_id,
        } for m in members],
        "total": total, "page": page, "total_pages": -(-total // limit),
    })

@router.post("/members")
def create_member(body: MemberCreate, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    if not body.first_name.strip() or not body.last_name.strip():
        raise HTTPException(400, "Name fields are required")
    if not validate_age(body.date_of_birth, 5):
        raise HTTPException(400, "Date of birth must be at least 5 years ago")
    phone = normalize_phone(body.phone) if body.phone else None
    if body.email and not is_valid_email(body.email):
        raise HTTPException(400, "Invalid email")
    if body.phone and not is_valid_phone(body.phone):
        raise HTTPException(400, "Invalid phone")
    if body.email and db.query(ChurchMember).filter(ChurchMember.email == body.email).first():
        raise HTTPException(400, "Email already exists")
    if phone and db.query(ChurchMember).filter(ChurchMember.phone == phone).first():
        raise HTTPException(400, "Phone already exists")
    if body.envelope_number and db.query(ChurchMember).filter(ChurchMember.envelope_number == body.envelope_number).first():
        raise HTTPException(400, "Envelope number already exists")
    member = ChurchMember(
        title_id=body.title_id, first_name=body.first_name.capitalize(),
        middle_name=body.middle_name.capitalize() if body.middle_name else None,
        last_name=body.last_name.capitalize(), date_of_birth=body.date_of_birth,
        gender=body.gender, member_type=body.member_type,
        head_parish_id=hpid, sub_parish_id=body.sub_parish_id,
        community_id=body.community_id, envelope_number=body.envelope_number,
        occupation_id=body.occupation_id, phone=phone, email=body.email,
        recorded_by=admin.id,
    )
    db.add(member); db.commit(); db.refresh(member)
    return success_response("Member registered", {"id": member.id})

@router.put("/members/{member_id}")
def update_member(member_id: int, body: MemberUpdate, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    member = db.query(ChurchMember).filter(ChurchMember.id == member_id, ChurchMember.head_parish_id == hpid).first()
    if not member:
        raise HTTPException(404, "Member not found")
    for field, value in body.dict(exclude_unset=True).items():
        setattr(member, field, value)
    db.commit()
    return success_response("Member updated")

@router.get("/members/{member_id}")
def get_member(member_id: int, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    m = db.query(ChurchMember).filter(ChurchMember.id == member_id, ChurchMember.head_parish_id == hpid).first()
    if not m:
        raise HTTPException(404, "Member not found")
    return success_response(data={
        "id": m.id, "first_name": m.first_name, "middle_name": m.middle_name,
        "last_name": m.last_name, "date_of_birth": str(m.date_of_birth),
        "gender": m.gender, "member_type": m.member_type, "status": m.status,
        "envelope_number": m.envelope_number, "phone": m.phone, "email": m.email,
        "sub_parish_id": m.sub_parish_id, "community_id": m.community_id,
    })

class ExcludeMemberBody(BaseModel):
    reason: str

@router.post("/members/{member_id}/exclude")
def exclude_member(member_id: int, body: ExcludeMemberBody, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    member = db.query(ChurchMember).filter(ChurchMember.id == member_id, ChurchMember.head_parish_id == hpid).first()
    if not member:
        raise HTTPException(404, "Member not found")
    member.status = "Excluded"
    db.add(MemberExclusion(member_id=member_id, reason=body.reason, excluded_by=admin.id))
    db.commit()
    return success_response("Member excluded")

@router.post("/members/{member_id}/reinstate")
def reinstate_member(member_id: int, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    member = db.query(ChurchMember).filter(ChurchMember.id == member_id, ChurchMember.head_parish_id == hpid).first()
    if not member:
        raise HTTPException(404, "Member not found")
    member.status = "Active"
    db.commit()
    return success_response("Member reinstated")


# ═══════════════════════════════════════════════════════════════
# LEADERS & CHOIRS
# ═══════════════════════════════════════════════════════════════

class LeaderCreate(BaseModel):
    title_id: Optional[int] = None
    first_name: str
    middle_name: Optional[str] = None
    last_name: str
    gender: str
    leader_type: str
    role_id: int
    appointment_date: date_type
    end_date: Optional[date_type] = None

@router.get("/leaders")
def list_leaders(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    rows = db.query(ChurchLeader).filter(ChurchLeader.head_parish_id == hpid).order_by(ChurchLeader.first_name).all()
    return success_response(data=[{
        "id": l.id, "first_name": l.first_name, "last_name": l.last_name,
        "role_id": l.role_id, "status": l.status, "appointment_date": str(l.appointment_date),
    } for l in rows])

@router.post("/leaders")
def create_leader(body: LeaderCreate, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    leader = ChurchLeader(head_parish_id=hpid, **body.dict())
    db.add(leader); db.commit(); db.refresh(leader)
    return success_response("Leader registered", {"id": leader.id})

class ChoirBody(BaseModel):
    name: str
    description: Optional[str] = None

@router.get("/choirs")
def list_choirs(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    rows = db.query(ChurchChoir).filter(ChurchChoir.head_parish_id == hpid).order_by(ChurchChoir.name).all()
    return success_response(data=[{"id": c.id, "name": c.name} for c in rows])

@router.post("/choirs")
def create_choir(body: ChoirBody, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    if db.query(ChurchChoir).filter(ChurchChoir.name == body.name, ChurchChoir.head_parish_id == hpid).first():
        raise HTTPException(400, "Choir already exists")
    c = ChurchChoir(name=body.name, head_parish_id=hpid, description=body.description)
    db.add(c); db.commit(); db.refresh(c)
    return success_response("Choir registered", {"id": c.id})


# ═══════════════════════════════════════════════════════════════
# HEAD PARISH ADMIN MANAGEMENT
# ═══════════════════════════════════════════════════════════════

class HPAdminCreate(BaseModel):
    fullname: str
    phone: str
    email: Optional[str] = ""
    role: str
    admin_level: str  # head_parish, sub_parish, community, group
    sub_parish_id: Optional[int] = None
    community_id: Optional[int] = None
    group_id: Optional[int] = None

@router.get("/admins")
def list_hp_admins(admin_level: Optional[str] = None, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    q = db.query(Admin).filter(Admin.head_parish_id == hpid, Admin.is_active == True)
    if admin_level:
        q = q.filter(Admin.admin_level == admin_level)
    return success_response(data=[{
        "id": a.id, "fullname": a.fullname, "phone": a.phone, "role": a.role,
        "admin_level": a.admin_level, "sub_parish_id": a.sub_parish_id,
        "community_id": a.community_id, "group_id": a.group_id,
    } for a in q.order_by(Admin.fullname).all()])

@router.post("/admins")
def create_hp_admin(body: HPAdminCreate, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    hp = db.query(HeadParish).filter(HeadParish.id == hpid).first()
    if body.admin_level not in ("head_parish", "sub_parish", "community", "group"):
        raise HTTPException(400, "Invalid admin level for head parish scope")
    if not is_valid_phone(body.phone):
        raise HTTPException(400, "Invalid phone")
    a = Admin(
        fullname=body.fullname, phone=body.phone, email=body.email or None,
        role=body.role, password=hash_password("KanisaLangu"),
        admin_level=body.admin_level, diocese_id=hp.diocese_id, province_id=hp.province_id,
        head_parish_id=hpid, sub_parish_id=body.sub_parish_id,
        community_id=body.community_id, group_id=body.group_id,
    )
    db.add(a); db.commit(); db.refresh(a)
    return success_response("Admin registered", {"id": a.id})

@router.delete("/admins/{admin_id}")
def deactivate_hp_admin(admin_id: int, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    target = db.query(Admin).filter(Admin.id == admin_id, Admin.head_parish_id == hpid).first()
    if not target:
        raise HTTPException(404, "Admin not found")
    target.is_active = False; db.commit()
    return success_response("Admin deactivated")


# ═══════════════════════════════════════════════════════════════
# FINANCE — Bank Accounts, Revenue Streams, Revenue, Expenses
# ═══════════════════════════════════════════════════════════════

class BankAccountBody(BaseModel):
    account_name: str
    account_number: str
    bank_id: int
    balance: float = 0.0

@router.get("/bank-accounts")
def list_bank_accounts(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    rows = db.query(BankAccount).filter(BankAccount.entity_type == "head_parish", BankAccount.entity_id == hpid, BankAccount.is_active == True).all()
    return success_response(data=[{
        "id": a.id, "account_name": a.account_name, "account_number": a.account_number,
        "bank_id": a.bank_id, "balance": float(a.balance),
    } for a in rows])

@router.post("/bank-accounts")
def create_bank_account(body: BankAccountBody, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    acc = BankAccount(entity_type="head_parish", entity_id=hpid, **body.dict())
    db.add(acc); db.commit(); db.refresh(acc)
    return success_response("Bank account created", {"id": acc.id})

class RevenueStreamBody(BaseModel):
    name: str
    account_id: int

@router.get("/revenue-streams")
def list_revenue_streams(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    rows = db.query(RevenueStream).filter(RevenueStream.entity_type == "head_parish", RevenueStream.entity_id == hpid, RevenueStream.is_active == True).all()
    return success_response(data=[{"id": r.id, "name": r.name, "account_id": r.account_id} for r in rows])

@router.post("/revenue-streams")
def create_revenue_stream(body: RevenueStreamBody, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    rs = RevenueStream(entity_type="head_parish", entity_id=hpid, **body.dict())
    db.add(rs); db.commit(); db.refresh(rs)
    return success_response("Revenue stream created", {"id": rs.id})

class RecordRevenueBody(BaseModel):
    management_level: str = "head_parish"
    revenue_stream_id: int
    sub_parish_id: Optional[int] = None
    community_id: Optional[int] = None
    group_id: Optional[int] = None
    service_number: Optional[int] = None
    amount: float
    payment_method: str = "Cash"
    description: Optional[str] = None
    revenue_date: date_type

@router.post("/revenues")
def record_revenue(body: RecordRevenueBody, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    if body.amount <= 0:
        raise HTTPException(400, "Amount must be > 0")
    rev = Revenue(head_parish_id=hpid, recorded_by=admin.id, **body.dict())
    db.add(rev)
    account_id = get_account_id_by_revenue_stream(db, body.revenue_stream_id)
    if account_id:
        update_account_balance(db, account_id, Decimal(str(body.amount)))
    db.commit(); db.refresh(rev)
    return success_response("Revenue recorded", {"id": rev.id})

class RecordExpenseBody(BaseModel):
    management_level: str = "head_parish"
    expense_name_id: int
    sub_parish_id: Optional[int] = None
    community_id: Optional[int] = None
    group_id: Optional[int] = None
    amount: float
    payment_method: str = "Cash"
    description: Optional[str] = None
    expense_date: date_type

@router.post("/expenses")
def record_expense(body: RecordExpenseBody, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    if body.amount <= 0:
        raise HTTPException(400, "Amount must be > 0")
    exp = Expense(head_parish_id=hpid, recorded_by=admin.id, **body.dict())
    db.add(exp); db.commit(); db.refresh(exp)
    return success_response("Expense recorded", {"id": exp.id})

class ExpenseGroupBody(BaseModel):
    name: str
    management_level: str = "head_parish"

@router.post("/expense-groups")
def create_expense_group(body: ExpenseGroupBody, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    db.add(ExpenseGroup(head_parish_id=hpid, **body.dict())); db.commit()
    return success_response("Expense group created")

@router.get("/expense-groups")
def list_expense_groups(management_level: Optional[str] = None, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    q = db.query(ExpenseGroup).filter(ExpenseGroup.head_parish_id == hpid)
    if management_level:
        q = q.filter(ExpenseGroup.management_level == management_level)
    return success_response(data=[{"id": g.id, "name": g.name, "management_level": g.management_level} for g in q.all()])

class ExpenseNameBody(BaseModel):
    expense_group_id: int
    name: str
    management_level: str = "head_parish"

@router.post("/expense-names")
def create_expense_name(body: ExpenseNameBody, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    _require_hp_admin(admin)
    db.add(ExpenseName(**body.dict())); db.commit()
    return success_response("Expense name created")


# ═══════════════════════════════════════════════════════════════
# HARAMBEE
# ═══════════════════════════════════════════════════════════════

class HarambeeBody(BaseModel):
    management_level: str = "head_parish"
    sub_parish_id: Optional[int] = None
    community_id: Optional[int] = None
    group_id: Optional[int] = None
    account_id: int
    name: str
    description: str
    from_date: date_type
    to_date: date_type
    amount: float

@router.get("/harambees")
def list_harambees(management_level: Optional[str] = None, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    q = db.query(Harambee).filter(Harambee.head_parish_id == hpid, Harambee.is_active == True)
    if management_level:
        q = q.filter(Harambee.management_level == management_level)
    return success_response(data=[{
        "id": h.id, "name": h.name, "description": h.description,
        "from_date": str(h.from_date), "to_date": str(h.to_date),
        "amount": float(h.amount), "management_level": h.management_level,
    } for h in q.order_by(Harambee.from_date.desc()).all()])

@router.post("/harambees")
def create_harambee(body: HarambeeBody, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    h = Harambee(head_parish_id=hpid, **body.dict())
    db.add(h); db.commit(); db.refresh(h)
    return success_response("Harambee created", {"id": h.id})

class HarambeeContribBody(BaseModel):
    harambee_id: int
    member_id: int
    amount: float
    contribution_date: date_type
    payment_method: str = "Cash"

@router.post("/harambee-contributions")
def record_harambee_contribution(body: HarambeeContribBody, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    if body.amount <= 0:
        raise HTTPException(400, "Amount must be > 0")
    member = db.query(ChurchMember).filter(ChurchMember.id == body.member_id).first()
    if not member:
        raise HTTPException(404, "Member not found")
    contrib = HarambeeContribution(
        harambee_id=body.harambee_id, member_id=body.member_id,
        amount=body.amount, contribution_date=body.contribution_date,
        head_parish_id=hpid, sub_parish_id=member.sub_parish_id,
        community_id=member.community_id, payment_method=body.payment_method,
    )
    db.add(contrib); db.commit(); db.refresh(contrib)
    return success_response("Contribution recorded", {"id": contrib.id})

class HarambeeTargetBody(BaseModel):
    harambee_id: int
    member_id: int
    target: float
    target_type: str = "individual"

@router.post("/harambee-targets")
def set_harambee_target(body: HarambeeTargetBody, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    _require_hp_admin(admin)
    existing = db.query(HarambeeTarget).filter(
        HarambeeTarget.harambee_id == body.harambee_id, HarambeeTarget.member_id == body.member_id
    ).first()
    if existing:
        existing.target = body.target; existing.target_type = body.target_type
    else:
        member = db.query(ChurchMember).filter(ChurchMember.id == body.member_id).first()
        db.add(HarambeeTarget(
            harambee_id=body.harambee_id, member_id=body.member_id,
            target=body.target, target_type=body.target_type,
            sub_parish_id=member.sub_parish_id if member else None,
            community_id=member.community_id if member else None,
        ))
    db.commit()
    return success_response("Target set")


# ═══════════════════════════════════════════════════════════════
# ENVELOPE
# ═══════════════════════════════════════════════════════════════

class EnvelopeContribBody(BaseModel):
    member_id: int
    amount: float
    contribution_date: date_type
    payment_method: str = "Cash"

@router.post("/envelope-contributions")
def record_envelope_contribution(body: EnvelopeContribBody, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    if body.amount <= 0:
        raise HTTPException(400, "Amount must be > 0")
    member = db.query(ChurchMember).filter(ChurchMember.id == body.member_id).first()
    if not member:
        raise HTTPException(404, "Member not found")
    contrib = EnvelopeContribution(
        member_id=body.member_id, amount=body.amount,
        contribution_date=body.contribution_date, head_parish_id=hpid,
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
    _require_hp_admin(admin)
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


# ═══════════════════════════════════════════════════════════════
# ATTENDANCE
# ═══════════════════════════════════════════════════════════════

class AttendanceBody(BaseModel):
    management_level: str = "head_parish"
    event_title: str
    sub_parish_id: Optional[int] = None
    community_id: Optional[int] = None
    group_id: Optional[int] = None
    service_number: Optional[int] = None
    male_attendance: int = 0
    female_attendance: int = 0
    children_attendance: int = 0
    attendance_date: date_type

@router.post("/attendance")
def record_attendance(body: AttendanceBody, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    a = Attendance(head_parish_id=hpid, recorded_by=admin.id, **body.dict())
    db.add(a); db.commit(); db.refresh(a)
    return success_response("Attendance recorded", {"id": a.id})

@router.get("/attendance")
def list_attendance(management_level: Optional[str] = None, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    q = db.query(Attendance).filter(Attendance.head_parish_id == hpid)
    if management_level:
        q = q.filter(Attendance.management_level == management_level)
    rows = q.order_by(Attendance.attendance_date.desc()).limit(100).all()
    return success_response(data=[{
        "id": a.id, "event_title": a.event_title,
        "male": a.male_attendance, "female": a.female_attendance,
        "children": a.children_attendance,
        "total": a.male_attendance + a.female_attendance + a.children_attendance,
        "date": str(a.attendance_date),
    } for a in rows])


# ═══════════════════════════════════════════════════════════════
# MEETINGS
# ═══════════════════════════════════════════════════════════════

class MeetingBody(BaseModel):
    title: str
    description: Optional[str] = None
    meeting_date: date_type
    meeting_time: str
    meeting_place: str

@router.post("/meetings")
def create_meeting(body: MeetingBody, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    m = Meeting(
        head_parish_id=hpid, title=body.title, description=body.description,
        meeting_date=body.meeting_date, meeting_time=time_type.fromisoformat(body.meeting_time),
        meeting_place=body.meeting_place,
    )
    db.add(m); db.commit(); db.refresh(m)
    return success_response("Meeting created", {"id": m.id})

@router.get("/meetings")
def list_meetings(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    rows = db.query(Meeting).filter(Meeting.head_parish_id == hpid).order_by(Meeting.meeting_date.desc()).all()
    return success_response(data=[{
        "id": m.id, "title": m.title, "meeting_date": str(m.meeting_date),
        "meeting_time": str(m.meeting_time), "meeting_place": m.meeting_place,
    } for m in rows])


# ═══════════════════════════════════════════════════════════════
# SUNDAY SERVICES
# ═══════════════════════════════════════════════════════════════

class SundayServiceBody(BaseModel):
    service_date: date_type
    service_color_id: Optional[int] = None
    base_scripture_text: Optional[str] = None
    large_liturgy_page_number: Optional[int] = None
    small_liturgy_page_number: Optional[int] = None
    large_antiphony_page_number: Optional[int] = None
    small_antiphony_page_number: Optional[int] = None
    large_praise_page_number: Optional[int] = None
    small_praise_page_number: Optional[int] = None

@router.post("/sunday-services")
def create_or_update_service(body: SundayServiceBody, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    existing = db.query(SundayService).filter(
        SundayService.head_parish_id == hpid, SundayService.service_date == body.service_date,
    ).first()
    if existing:
        for field, value in body.dict(exclude={"service_date"}).items():
            if value is not None:
                setattr(existing, field, value)
        db.commit()
        return success_response("Service updated", {"id": existing.id})
    s = SundayService(head_parish_id=hpid, **body.dict())
    db.add(s); db.commit(); db.refresh(s)
    return success_response("Service created", {"id": s.id})

@router.get("/sunday-services")
def list_services(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    rows = db.query(SundayService).filter(SundayService.head_parish_id == hpid).order_by(SundayService.service_date.desc()).limit(52).all()
    return success_response(data=[{
        "id": s.id, "service_date": str(s.service_date),
        "service_color_id": s.service_color_id, "base_scripture_text": s.base_scripture_text,
    } for s in rows])


# ═══════════════════════════════════════════════════════════════
# ASSETS
# ═══════════════════════════════════════════════════════════════

class AssetBody(BaseModel):
    name: str
    generates_revenue: bool = False

@router.get("/assets")
def list_assets(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    rows = db.query(Asset).filter(Asset.head_parish_id == hpid).order_by(Asset.name).all()
    return success_response(data=[{"id": a.id, "name": a.name, "generates_revenue": a.generates_revenue, "status": a.status} for a in rows])

@router.post("/assets")
def create_asset(body: AssetBody, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    a = Asset(head_parish_id=hpid, **body.dict())
    db.add(a); db.commit(); db.refresh(a)
    return success_response("Asset added", {"id": a.id})


# ═══════════════════════════════════════════════════════════════
# EVENTS
# ═══════════════════════════════════════════════════════════════

class EventBody(BaseModel):
    title: str
    description: Optional[str] = None
    event_date: date_type
    event_time: Optional[str] = None
    location: Optional[str] = None

@router.post("/events")
def create_event(body: EventBody, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    evt = ChurchEvent(
        head_parish_id=hpid, title=body.title, description=body.description,
        event_date=body.event_date,
        event_time=time_type.fromisoformat(body.event_time) if body.event_time else None,
        location=body.location,
    )
    db.add(evt); db.commit(); db.refresh(evt)
    return success_response("Event created", {"id": evt.id})


# ═══════════════════════════════════════════════════════════════
# CONFIG — SMS, Service times, Feedback
# ═══════════════════════════════════════════════════════════════

class SmsApiBody(BaseModel):
    account_name: str
    api_username: str
    api_password: str
    sender_id: Optional[str] = None

@router.post("/sms-config")
def set_sms_config(body: SmsApiBody, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    existing = db.query(SmsApiConfig).filter(SmsApiConfig.head_parish_id == hpid).first()
    if existing:
        for k, v in body.dict().items():
            setattr(existing, k, v)
    else:
        db.add(SmsApiConfig(head_parish_id=hpid, **body.dict()))
    db.commit()
    return success_response("SMS config saved")

class FeedbackBody(BaseModel):
    feedback_type: str
    subject: str
    message: str

@router.post("/feedback")
def submit_feedback(body: FeedbackBody, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    db.add(Feedback(head_parish_id=hpid, submitted_by_admin_id=admin.id, **body.dict()))
    db.commit()
    return success_response("Feedback submitted")


# ═══════════════════════════════════════════════════════════════
# REPORTS (scoped to head parish)
# ═══════════════════════════════════════════════════════════════

@router.get("/reports/financial-summary")
def financial_summary(management_level: Optional[str] = None, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    rev_q = db.query(func.coalesce(func.sum(Revenue.amount), 0)).filter(Revenue.head_parish_id == hpid)
    exp_q = db.query(func.coalesce(func.sum(Expense.amount), 0)).filter(Expense.head_parish_id == hpid)
    if management_level:
        rev_q = rev_q.filter(Revenue.management_level == management_level)
        exp_q = exp_q.filter(Expense.management_level == management_level)
    total_rev = float(rev_q.scalar() or 0)
    total_exp = float(exp_q.scalar() or 0)
    return success_response(data={
        "total_revenue": total_rev, "total_expense": total_exp, "balance": total_rev - total_exp,
    })

@router.get("/reports/envelope-summary")
def envelope_summary(year: Optional[int] = None, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    q = db.query(func.coalesce(func.sum(EnvelopeContribution.amount), 0)).filter(EnvelopeContribution.head_parish_id == hpid)
    if year:
        q = q.filter(func.extract("year", EnvelopeContribution.contribution_date) == year)
    return success_response(data={"total_contributions": float(q.scalar() or 0)})

@router.get("/reports/attendance-summary")
def attendance_summary(year: Optional[int] = None, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    hpid = _require_hp_admin(admin)
    q = db.query(
        func.sum(Attendance.male_attendance), func.sum(Attendance.female_attendance),
        func.sum(Attendance.children_attendance), func.count(Attendance.id),
    ).filter(Attendance.head_parish_id == hpid)
    if year:
        q = q.filter(func.extract("year", Attendance.attendance_date) == year)
    male, female, children, count = q.first()
    return success_response(data={
        "total_male": int(male or 0), "total_female": int(female or 0),
        "total_children": int(children or 0), "total_records": int(count or 0),
    })
