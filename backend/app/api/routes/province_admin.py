# api/routes/province_admin.py
"""Province admin routes — manage head parishes, province-level admins,
financial overview, members overview, payments, and reports."""

from fastapi import APIRouter, Depends, HTTPException, Query
from pydantic import BaseModel
from sqlalchemy.orm import Session
from sqlalchemy import func, text
from typing import Optional
from datetime import date

from core.database import get_db
from models.admins import Admin
from models.hierarchy import Province, HeadParish, SubParish
from models.members import ChurchMember
from models.finance import BankAccount, RevenueStream, Revenue, Expense
from models.payments import Payment
from utils.auth import hash_password, get_current_admin
from utils.validation import is_valid_email, is_valid_phone
from utils.response import success_response, error_response

router = APIRouter(prefix="/province", tags=["Province Admin"])


def _require_province_admin(admin: Admin) -> int:
    if admin.admin_level != "province":
        raise HTTPException(403, "Province-level access required")
    if not admin.province_id:
        raise HTTPException(403, "No province assigned")
    return admin.province_id


# ═══════════════════════════════════════════════════════════════
# DASHBOARD
# ═══════════════════════════════════════════════════════════════

@router.get("/dashboard")
def province_dashboard(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    pid = _require_province_admin(admin)
    province = db.query(Province).filter(Province.id == pid).first()
    hp_ids = [hp.id for hp in db.query(HeadParish.id).filter(HeadParish.province_id == pid).all()]
    total_revenue = 0.0
    if hp_ids:
        total_revenue = float(db.query(func.coalesce(func.sum(Revenue.amount), 0)).filter(Revenue.head_parish_id.in_(hp_ids)).scalar() or 0)
    return success_response(data={
        "province": {"id": province.id, "name": province.name} if province else None,
        "total_head_parishes": db.query(func.count(HeadParish.id)).filter(HeadParish.province_id == pid, HeadParish.is_active == True).scalar(),
        "total_sub_parishes": db.query(func.count(SubParish.id)).join(HeadParish).filter(HeadParish.province_id == pid, SubParish.is_active == True).scalar(),
        "total_members": db.query(func.count(ChurchMember.id)).join(HeadParish).filter(
            HeadParish.province_id == pid, ChurchMember.is_active == True
        ).scalar(),
        "total_admins": db.query(func.count(Admin.id)).filter(Admin.province_id == pid, Admin.is_active == True).scalar(),
        "total_revenue": total_revenue,
    })


# ═══════════════════════════════════════════════════════════════
# HEAD PARISH MANAGEMENT
# ═══════════════════════════════════════════════════════════════

class HeadParishCreate(BaseModel):
    name: str
    region_id: int
    district_id: int
    address: str
    email: str
    phone: str = ""

class HeadParishUpdate(BaseModel):
    name: Optional[str] = None
    address: Optional[str] = None
    email: Optional[str] = None
    phone: Optional[str] = None

@router.get("/head-parishes")
def list_head_parishes(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    pid = _require_province_admin(admin)
    rows = db.query(HeadParish).filter(HeadParish.province_id == pid, HeadParish.is_active == True).order_by(HeadParish.name).all()
    result = []
    for hp in rows:
        member_count = db.query(func.count(ChurchMember.id)).filter(
            ChurchMember.head_parish_id == hp.id, ChurchMember.is_active == True
        ).scalar()
        sp_count = db.query(func.count(SubParish.id)).filter(SubParish.head_parish_id == hp.id, SubParish.is_active == True).scalar()
        result.append({
            "id": hp.id, "name": hp.name, "address": hp.address,
            "email": hp.email, "phone": hp.phone,
            "sub_parish_count": sp_count, "member_count": member_count,
        })
    return success_response(data=result)

@router.post("/head-parishes")
def create_head_parish(body: HeadParishCreate, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    pid = _require_province_admin(admin)
    province = db.query(Province).filter(Province.id == pid).first()
    name = body.name.strip().upper()
    if not is_valid_email(body.email):
        raise HTTPException(400, "Invalid email")
    if db.query(HeadParish).filter(HeadParish.name == name, HeadParish.province_id == pid).first():
        raise HTTPException(400, "Head parish already exists in this province")
    hp = HeadParish(
        name=name, diocese_id=province.diocese_id, province_id=pid,
        region_id=body.region_id, district_id=body.district_id,
        address=body.address, email=body.email, phone=body.phone,
    )
    db.add(hp); db.commit(); db.refresh(hp)
    return success_response("Head parish registered", {"id": hp.id})

@router.put("/head-parishes/{hp_id}")
def update_head_parish(hp_id: int, body: HeadParishUpdate, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    pid = _require_province_admin(admin)
    hp = db.query(HeadParish).filter(HeadParish.id == hp_id, HeadParish.province_id == pid).first()
    if not hp:
        raise HTTPException(404, "Head parish not found")
    for field, value in body.dict(exclude_unset=True).items():
        if field == "name" and value:
            value = value.strip().upper()
        setattr(hp, field, value)
    db.commit()
    return success_response("Head parish updated")

@router.delete("/head-parishes/{hp_id}")
def deactivate_head_parish(hp_id: int, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    pid = _require_province_admin(admin)
    hp = db.query(HeadParish).filter(HeadParish.id == hp_id, HeadParish.province_id == pid).first()
    if not hp:
        raise HTTPException(404, "Head parish not found")
    hp.is_active = False; db.commit()
    return success_response("Head parish deactivated")

@router.get("/head-parishes/{hp_id}")
def get_head_parish_detail(hp_id: int, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    pid = _require_province_admin(admin)
    hp = db.query(HeadParish).filter(HeadParish.id == hp_id, HeadParish.province_id == pid).first()
    if not hp:
        raise HTTPException(404, "Head parish not found")
    sub_parishes = db.query(SubParish).filter(SubParish.head_parish_id == hp.id, SubParish.is_active == True).all()
    admins = db.query(Admin).filter(Admin.head_parish_id == hp.id, Admin.admin_level == "head_parish", Admin.is_active == True).all()
    return success_response(data={
        "id": hp.id, "name": hp.name, "address": hp.address, "email": hp.email, "phone": hp.phone,
        "sub_parishes": [{"id": sp.id, "name": sp.name} for sp in sub_parishes],
        "admins": [{"id": a.id, "fullname": a.fullname, "role": a.role} for a in admins],
    })


# ═══════════════════════════════════════════════════════════════
# PROVINCE ADMIN MANAGEMENT
# ═══════════════════════════════════════════════════════════════

class ProvinceAdminCreate(BaseModel):
    fullname: str
    phone: str
    email: Optional[str] = ""
    role: str
    admin_level: str
    head_parish_id: Optional[int] = None

@router.get("/admins")
def list_province_admins(admin_level: Optional[str] = None, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    pid = _require_province_admin(admin)
    q = db.query(Admin).filter(Admin.province_id == pid, Admin.is_active == True)
    if admin_level:
        q = q.filter(Admin.admin_level == admin_level)
    return success_response(data=[{
        "id": a.id, "fullname": a.fullname, "phone": a.phone,
        "role": a.role, "admin_level": a.admin_level,
        "head_parish_id": a.head_parish_id,
    } for a in q.order_by(Admin.fullname).all()])

@router.post("/admins")
def create_province_admin(body: ProvinceAdminCreate, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    pid = _require_province_admin(admin)
    province = db.query(Province).filter(Province.id == pid).first()
    if body.admin_level not in ("province", "head_parish"):
        raise HTTPException(400, "Province admin can only create province or head_parish level admins")
    if not is_valid_phone(body.phone):
        raise HTTPException(400, "Invalid phone")
    a = Admin(
        fullname=body.fullname, phone=body.phone, email=body.email or None,
        role=body.role, password=hash_password("KanisaLangu"),
        admin_level=body.admin_level, diocese_id=province.diocese_id, province_id=pid,
        head_parish_id=body.head_parish_id,
    )
    db.add(a); db.commit(); db.refresh(a)
    return success_response("Admin registered", {"id": a.id})

@router.delete("/admins/{admin_id}")
def deactivate_province_admin(admin_id: int, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    pid = _require_province_admin(admin)
    target = db.query(Admin).filter(Admin.id == admin_id, Admin.province_id == pid).first()
    if not target:
        raise HTTPException(404, "Admin not found")
    target.is_active = False; db.commit()
    return success_response("Admin deactivated")


# ═══════════════════════════════════════════════════════════════
# MEMBERS OVERVIEW
# ═══════════════════════════════════════════════════════════════

@router.get("/members")
def province_members_overview(
    head_parish_id: Optional[int] = None,
    search: Optional[str] = None,
    page: int = Query(1, ge=1), limit: int = Query(50, ge=1, le=500),
    db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin),
):
    pid = _require_province_admin(admin)
    q = db.query(ChurchMember).join(HeadParish).filter(
        HeadParish.province_id == pid, ChurchMember.is_active == True
    )
    if head_parish_id:
        q = q.filter(ChurchMember.head_parish_id == head_parish_id)
    if search:
        from sqlalchemy import or_
        s = f"%{search}%"
        q = q.filter(or_(ChurchMember.first_name.ilike(s), ChurchMember.last_name.ilike(s), ChurchMember.phone.ilike(s)))
    total = q.count()
    rows = q.order_by(ChurchMember.first_name).offset((page - 1) * limit).limit(limit).all()
    return success_response(data={
        "members": [{
            "id": m.id, "first_name": m.first_name, "last_name": m.last_name,
            "phone": m.phone, "head_parish_id": m.head_parish_id,
        } for m in rows],
        "total": total, "page": page, "total_pages": -(-total // limit),
    })


# ═══════════════════════════════════════════════════════════════
# PROVINCE FINANCIAL
# ═══════════════════════════════════════════════════════════════

@router.get("/bank-accounts")
def province_bank_accounts(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    pid = _require_province_admin(admin)
    rows = db.query(BankAccount).filter(
        BankAccount.entity_type == "province", BankAccount.entity_id == pid, BankAccount.is_active == True
    ).all()
    return success_response(data=[{
        "id": a.id, "account_name": a.account_name, "account_number": a.account_number,
        "balance": float(a.balance),
    } for a in rows])

@router.get("/revenue-streams")
def province_revenue_streams(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    pid = _require_province_admin(admin)
    rows = db.query(RevenueStream).filter(
        RevenueStream.entity_type == "province", RevenueStream.entity_id == pid, RevenueStream.is_active == True
    ).all()
    return success_response(data=[{"id": r.id, "name": r.name, "account_id": r.account_id} for r in rows])

@router.get("/financial-summary")
def province_financial_summary(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    pid = _require_province_admin(admin)
    hp_ids = [hp.id for hp in db.query(HeadParish.id).filter(HeadParish.province_id == pid).all()]
    if not hp_ids:
        return success_response(data={"total_revenue": 0, "total_expense": 0, "balance": 0})
    total_rev = float(db.query(func.coalesce(func.sum(Revenue.amount), 0)).filter(Revenue.head_parish_id.in_(hp_ids)).scalar() or 0)
    total_exp = float(db.query(func.coalesce(func.sum(Expense.amount), 0)).filter(Expense.head_parish_id.in_(hp_ids)).scalar() or 0)
    return success_response(data={"total_revenue": total_rev, "total_expense": total_exp, "balance": total_rev - total_exp})


# ═══════════════════════════════════════════════════════════════
# PAYMENTS
# ═══════════════════════════════════════════════════════════════

@router.get("/payments")
def province_payments(
    page: int = Query(1, ge=1), limit: int = Query(50, ge=1, le=200),
    db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin),
):
    pid = _require_province_admin(admin)
    hp_ids = [hp.id for hp in db.query(HeadParish.id).filter(HeadParish.province_id == pid).all()]
    q = db.query(Payment).filter(Payment.head_parish_id.in_(hp_ids)) if hp_ids else db.query(Payment).filter(False)
    total = q.count()
    rows = q.order_by(Payment.created_at.desc()).offset((page - 1) * limit).limit(limit).all()
    return success_response(data={
        "payments": [{
            "id": p.id, "amount": float(p.amount), "payment_reason": p.payment_reason,
            "payment_status": p.payment_status, "payment_date": str(p.payment_date),
        } for p in rows],
        "total": total, "page": page,
    })


# ═══════════════════════════════════════════════════════════════
# REPORTS
# ═══════════════════════════════════════════════════════════════

@router.get("/reports/sales")
def province_sales_report(year: Optional[int] = None, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    pid = _require_province_admin(admin)
    hp_ids = [hp.id for hp in db.query(HeadParish.id).filter(HeadParish.province_id == pid).all()]
    q = db.query(Payment).filter(Payment.head_parish_id.in_(hp_ids), Payment.payment_status == "Completed") if hp_ids else db.query(Payment).filter(False)
    if year:
        q = q.filter(func.extract("year", Payment.payment_date) == year)
    total = float(q.with_entities(func.coalesce(func.sum(Payment.amount), 0)).scalar() or 0)
    return success_response(data={"total_sales": total, "transaction_count": q.count()})

@router.get("/reports/sms-usage")
def province_sms_report(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    pid = _require_province_admin(admin)
    rows = db.execute(text("""
        SELECT hp.id, hp.name, COUNT(sl.id) AS total_sms, COALESCE(SUM(sl.cost), 0) AS total_cost
        FROM head_parishes hp
        LEFT JOIN sms_logs sl ON sl.head_parish_id = hp.id
        WHERE hp.province_id = :pid AND hp.is_active = true
        GROUP BY hp.id, hp.name ORDER BY total_sms DESC
    """), {"pid": pid}).mappings().all()
    return success_response(data=[dict(r) for r in rows])

@router.get("/reports/revenue-by-parish")
def province_revenue_by_parish(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    pid = _require_province_admin(admin)
    rows = db.execute(text("""
        SELECT hp.id, hp.name, COALESCE(SUM(r.amount), 0) AS total_revenue
        FROM head_parishes hp
        LEFT JOIN revenues r ON r.head_parish_id = hp.id
        WHERE hp.province_id = :pid AND hp.is_active = true
        GROUP BY hp.id, hp.name ORDER BY total_revenue DESC
    """), {"pid": pid}).mappings().all()
    return success_response(data=[dict(r) for r in rows])
