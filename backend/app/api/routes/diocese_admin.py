# api/routes/diocese_admin.py
"""Diocese admin routes — manage provinces, head parishes, diocese-level admins,
financial overview, payments, and reports. Scoped by diocese_id."""

from fastapi import APIRouter, Depends, HTTPException, Query
from pydantic import BaseModel
from sqlalchemy.orm import Session
from sqlalchemy import func, text
from typing import Optional
from datetime import date

from core.database import get_db
from models.admins import Admin
from models.hierarchy import Diocese, Province, HeadParish, SubParish
from models.members import ChurchMember
from models.finance import BankAccount, RevenueStream, Revenue, Expense
from models.payments import Payment
from utils.auth import hash_password, get_current_admin
from utils.validation import is_valid_email, is_valid_phone
from utils.response import success_response, error_response

router = APIRouter(prefix="/diocese", tags=["Diocese Admin"])


def _require_diocese_admin(admin: Admin) -> int:
    if admin.admin_level != "diocese":
        raise HTTPException(403, "Diocese-level access required")
    if not admin.diocese_id:
        raise HTTPException(403, "No diocese assigned")
    return admin.diocese_id


# ═══════════════════════════════════════════════════════════════
# DASHBOARD
# ═══════════════════════════════════════════════════════════════

@router.get("/dashboard")
def diocese_dashboard(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    did = _require_diocese_admin(admin)
    diocese = db.query(Diocese).filter(Diocese.id == did).first()
    total_revenue = float(db.query(func.coalesce(func.sum(Revenue.amount), 0)).join(
        HeadParish, Revenue.head_parish_id == HeadParish.id
    ).filter(HeadParish.diocese_id == did).scalar() or 0)
    return success_response(data={
        "diocese": {"id": diocese.id, "name": diocese.name} if diocese else None,
        "total_provinces": db.query(func.count(Province.id)).filter(Province.diocese_id == did, Province.is_active == True).scalar(),
        "total_head_parishes": db.query(func.count(HeadParish.id)).filter(HeadParish.diocese_id == did, HeadParish.is_active == True).scalar(),
        "total_members": db.query(func.count(ChurchMember.id)).join(HeadParish).filter(
            HeadParish.diocese_id == did, ChurchMember.is_active == True
        ).scalar(),
        "total_admins": db.query(func.count(Admin.id)).filter(Admin.diocese_id == did, Admin.is_active == True).scalar(),
        "total_revenue": total_revenue,
    })


# ═══════════════════════════════════════════════════════════════
# PROVINCE MANAGEMENT
# ═══════════════════════════════════════════════════════════════

class ProvinceCreate(BaseModel):
    name: str
    region_id: int
    district_id: int
    address: str
    email: str
    phone: str

class ProvinceUpdate(BaseModel):
    name: Optional[str] = None
    address: Optional[str] = None
    email: Optional[str] = None
    phone: Optional[str] = None

@router.get("/provinces")
def list_provinces(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    did = _require_diocese_admin(admin)
    rows = db.query(Province).filter(Province.diocese_id == did, Province.is_active == True).order_by(Province.name).all()
    result = []
    for p in rows:
        hp_count = db.query(func.count(HeadParish.id)).filter(HeadParish.province_id == p.id, HeadParish.is_active == True).scalar()
        result.append({
            "id": p.id, "name": p.name, "address": p.address,
            "email": p.email, "phone": p.phone, "head_parish_count": hp_count,
        })
    return success_response(data=result)

@router.post("/provinces")
def create_province(body: ProvinceCreate, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    did = _require_diocese_admin(admin)
    name = body.name.strip().upper()
    if not is_valid_email(body.email):
        raise HTTPException(400, "Invalid email")
    if not is_valid_phone(body.phone):
        raise HTTPException(400, "Invalid phone")
    if db.query(Province).filter(Province.name == name, Province.diocese_id == did).first():
        raise HTTPException(400, "Province already exists in this diocese")
    p = Province(name=name, diocese_id=did, region_id=body.region_id, district_id=body.district_id,
                 address=body.address, email=body.email, phone=body.phone)
    db.add(p); db.commit(); db.refresh(p)
    return success_response("Province registered", {"id": p.id})

@router.put("/provinces/{province_id}")
def update_province(province_id: int, body: ProvinceUpdate, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    did = _require_diocese_admin(admin)
    p = db.query(Province).filter(Province.id == province_id, Province.diocese_id == did).first()
    if not p:
        raise HTTPException(404, "Province not found")
    for field, value in body.dict(exclude_unset=True).items():
        if field == "name" and value:
            value = value.strip().upper()
        setattr(p, field, value)
    db.commit()
    return success_response("Province updated")

@router.delete("/provinces/{province_id}")
def deactivate_province(province_id: int, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    did = _require_diocese_admin(admin)
    p = db.query(Province).filter(Province.id == province_id, Province.diocese_id == did).first()
    if not p:
        raise HTTPException(404, "Province not found")
    p.is_active = False; db.commit()
    return success_response("Province deactivated")

@router.get("/provinces/{province_id}")
def get_province_detail(province_id: int, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    did = _require_diocese_admin(admin)
    p = db.query(Province).filter(Province.id == province_id, Province.diocese_id == did).first()
    if not p:
        raise HTTPException(404, "Province not found")
    head_parishes = db.query(HeadParish).filter(HeadParish.province_id == p.id, HeadParish.is_active == True).order_by(HeadParish.name).all()
    admins = db.query(Admin).filter(Admin.province_id == p.id, Admin.admin_level == "province", Admin.is_active == True).all()
    return success_response(data={
        "id": p.id, "name": p.name, "address": p.address, "email": p.email, "phone": p.phone,
        "head_parishes": [{"id": hp.id, "name": hp.name} for hp in head_parishes],
        "admins": [{"id": a.id, "fullname": a.fullname, "role": a.role} for a in admins],
    })


# ═══════════════════════════════════════════════════════════════
# HEAD PARISHES (view + register from diocese level)
# ═══════════════════════════════════════════════════════════════

class HeadParishCreate(BaseModel):
    name: str
    province_id: int
    region_id: int
    district_id: int
    address: str
    email: str
    phone: str = ""

@router.get("/head-parishes")
def list_head_parishes(province_id: Optional[int] = None, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    did = _require_diocese_admin(admin)
    q = db.query(HeadParish).filter(HeadParish.diocese_id == did, HeadParish.is_active == True)
    if province_id:
        q = q.filter(HeadParish.province_id == province_id)
    rows = q.order_by(HeadParish.name).all()
    result = []
    for hp in rows:
        member_count = db.query(func.count(ChurchMember.id)).filter(
            ChurchMember.head_parish_id == hp.id, ChurchMember.is_active == True
        ).scalar()
        result.append({
            "id": hp.id, "name": hp.name, "province_id": hp.province_id,
            "address": hp.address, "email": hp.email, "phone": hp.phone,
            "member_count": member_count,
        })
    return success_response(data=result)

@router.post("/head-parishes")
def register_head_parish(body: HeadParishCreate, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    did = _require_diocese_admin(admin)
    name = body.name.strip().upper()
    if not is_valid_email(body.email):
        raise HTTPException(400, "Invalid email")
    if db.query(HeadParish).filter(HeadParish.name == name, HeadParish.province_id == body.province_id).first():
        raise HTTPException(400, "Head parish already exists in this province")
    hp = HeadParish(name=name, diocese_id=did, province_id=body.province_id,
                    region_id=body.region_id, district_id=body.district_id,
                    address=body.address, email=body.email, phone=body.phone)
    db.add(hp); db.commit(); db.refresh(hp)
    return success_response("Head parish registered", {"id": hp.id})


# ═══════════════════════════════════════════════════════════════
# DIOCESE-LEVEL ADMIN MANAGEMENT
# ═══════════════════════════════════════════════════════════════

class DioceseAdminCreate(BaseModel):
    fullname: str
    phone: str
    email: Optional[str] = ""
    role: str
    admin_level: str
    province_id: Optional[int] = None
    head_parish_id: Optional[int] = None

@router.get("/admins")
def list_diocese_admins(admin_level: Optional[str] = None, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    did = _require_diocese_admin(admin)
    q = db.query(Admin).filter(Admin.diocese_id == did, Admin.is_active == True)
    if admin_level:
        q = q.filter(Admin.admin_level == admin_level)
    return success_response(data=[{
        "id": a.id, "fullname": a.fullname, "email": a.email, "phone": a.phone,
        "role": a.role, "admin_level": a.admin_level,
        "province_id": a.province_id, "head_parish_id": a.head_parish_id,
    } for a in q.order_by(Admin.fullname).all()])

@router.post("/admins")
def create_diocese_admin(body: DioceseAdminCreate, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    did = _require_diocese_admin(admin)
    if body.admin_level not in ("diocese", "province", "head_parish"):
        raise HTTPException(400, "Diocese admin can create diocese, province, or head_parish level admins")
    if not is_valid_phone(body.phone):
        raise HTTPException(400, "Invalid phone")
    province_id = body.province_id
    if body.admin_level == "head_parish" and body.head_parish_id:
        hp = db.query(HeadParish).filter(HeadParish.id == body.head_parish_id, HeadParish.diocese_id == did).first()
        if not hp:
            raise HTTPException(404, "Head parish not found in this diocese")
        province_id = hp.province_id
    a = Admin(
        fullname=body.fullname, phone=body.phone, email=body.email or None,
        role=body.role, password=hash_password("KanisaLangu"),
        admin_level=body.admin_level, diocese_id=did,
        province_id=province_id, head_parish_id=body.head_parish_id,
    )
    db.add(a); db.commit(); db.refresh(a)
    return success_response("Admin registered", {"id": a.id})

@router.delete("/admins/{admin_id}")
def deactivate_diocese_admin(admin_id: int, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    did = _require_diocese_admin(admin)
    target = db.query(Admin).filter(Admin.id == admin_id, Admin.diocese_id == did).first()
    if not target:
        raise HTTPException(404, "Admin not found")
    target.is_active = False; db.commit()
    return success_response("Admin deactivated")


# ═══════════════════════════════════════════════════════════════
# DIOCESE FINANCIAL OVERVIEW
# ═══════════════════════════════════════════════════════════════

@router.get("/bank-accounts")
def diocese_bank_accounts(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    did = _require_diocese_admin(admin)
    rows = db.query(BankAccount).filter(
        BankAccount.entity_type == "diocese", BankAccount.entity_id == did, BankAccount.is_active == True
    ).all()
    return success_response(data=[{
        "id": a.id, "account_name": a.account_name, "account_number": a.account_number,
        "balance": float(a.balance),
    } for a in rows])

@router.get("/revenue-streams")
def diocese_revenue_streams(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    did = _require_diocese_admin(admin)
    rows = db.query(RevenueStream).filter(
        RevenueStream.entity_type == "diocese", RevenueStream.entity_id == did, RevenueStream.is_active == True
    ).all()
    return success_response(data=[{"id": r.id, "name": r.name, "account_id": r.account_id} for r in rows])

@router.get("/financial-overview")
def diocese_financial_overview(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    did = _require_diocese_admin(admin)
    hps = db.query(HeadParish.id).filter(HeadParish.diocese_id == did).all()
    hp_ids = [hp.id for hp in hps]
    if not hp_ids:
        return success_response(data={"total_revenue": 0, "total_expense": 0, "balance": 0})
    total_rev = float(db.query(func.coalesce(func.sum(Revenue.amount), 0)).filter(Revenue.head_parish_id.in_(hp_ids)).scalar() or 0)
    total_exp = float(db.query(func.coalesce(func.sum(Expense.amount), 0)).filter(Expense.head_parish_id.in_(hp_ids)).scalar() or 0)
    return success_response(data={"total_revenue": total_rev, "total_expense": total_exp, "balance": total_rev - total_exp})


# ═══════════════════════════════════════════════════════════════
# PAYMENTS
# ═══════════════════════════════════════════════════════════════

@router.get("/payments")
def diocese_payments(
    page: int = Query(1, ge=1), limit: int = Query(50, ge=1, le=200),
    db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin),
):
    did = _require_diocese_admin(admin)
    hp_ids = [hp.id for hp in db.query(HeadParish.id).filter(HeadParish.diocese_id == did).all()]
    q = db.query(Payment).filter(Payment.head_parish_id.in_(hp_ids)) if hp_ids else db.query(Payment).filter(False)
    total = q.count()
    rows = q.order_by(Payment.created_at.desc()).offset((page - 1) * limit).limit(limit).all()
    return success_response(data={
        "payments": [{
            "id": p.id, "amount": float(p.amount), "payment_reason": p.payment_reason,
            "payment_status": p.payment_status, "payment_date": str(p.payment_date),
            "head_parish_id": p.head_parish_id,
        } for p in rows],
        "total": total, "page": page,
    })


# ═══════════════════════════════════════════════════════════════
# REPORTS
# ═══════════════════════════════════════════════════════════════

@router.get("/reports/sales")
def diocese_sales_report(year: Optional[int] = None, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    did = _require_diocese_admin(admin)
    hp_ids = [hp.id for hp in db.query(HeadParish.id).filter(HeadParish.diocese_id == did).all()]
    q = db.query(Payment).filter(Payment.head_parish_id.in_(hp_ids), Payment.payment_status == "Completed") if hp_ids else db.query(Payment).filter(False)
    if year:
        q = q.filter(func.extract("year", Payment.payment_date) == year)
    total = float(q.with_entities(func.coalesce(func.sum(Payment.amount), 0)).scalar() or 0)
    return success_response(data={"total_sales": total, "transaction_count": q.count()})

@router.get("/reports/sms-usage")
def diocese_sms_report(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    did = _require_diocese_admin(admin)
    rows = db.execute(text("""
        SELECT hp.id, hp.name, COUNT(sl.id) AS total_sms, COALESCE(SUM(sl.cost), 0) AS total_cost
        FROM head_parishes hp
        LEFT JOIN sms_logs sl ON sl.head_parish_id = hp.id
        WHERE hp.diocese_id = :did AND hp.is_active = true
        GROUP BY hp.id, hp.name ORDER BY total_sms DESC
    """), {"did": did}).mappings().all()
    return success_response(data=[dict(r) for r in rows])

@router.get("/reports/revenue-by-parish")
def revenue_by_parish(db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    did = _require_diocese_admin(admin)
    rows = db.execute(text("""
        SELECT hp.id, hp.name, COALESCE(SUM(r.amount), 0) AS total_revenue
        FROM head_parishes hp
        LEFT JOIN revenues r ON r.head_parish_id = hp.id
        WHERE hp.diocese_id = :did AND hp.is_active = true
        GROUP BY hp.id, hp.name ORDER BY total_revenue DESC
    """), {"did": did}).mappings().all()
    return success_response(data=[dict(r) for r in rows])
