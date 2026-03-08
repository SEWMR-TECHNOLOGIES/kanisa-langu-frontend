# api/routes/diocese_admin.py
"""Diocese admin routes — manage provinces, diocese-level admins, and diocese-level reporting.
Scoped: all operations filtered by the authenticated admin's diocese_id."""

from fastapi import APIRouter, Depends, HTTPException
from pydantic import BaseModel
from sqlalchemy.orm import Session
from sqlalchemy import func
from typing import Optional
from datetime import date

from core.database import get_db
from models.admins import Admin
from models.hierarchy import Diocese, Province, HeadParish, SubParish
from models.members import ChurchMember
from models.finance import BankAccount, RevenueStream, Revenue, Expense
from utils.auth import hash_password, get_current_admin
from utils.validation import is_valid_email, is_valid_phone
from utils.response import success_response, error_response

router = APIRouter(prefix="/diocese", tags=["Diocese Admin"])


def _require_diocese_admin(admin: Admin) -> int:
    """Ensure admin is diocese-level and return diocese_id."""
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
    return success_response(data={
        "diocese": {"id": diocese.id, "name": diocese.name} if diocese else None,
        "total_provinces": db.query(func.count(Province.id)).filter(Province.diocese_id == did, Province.is_active == True).scalar(),
        "total_head_parishes": db.query(func.count(HeadParish.id)).filter(HeadParish.diocese_id == did, HeadParish.is_active == True).scalar(),
        "total_members": db.query(func.count(ChurchMember.id)).join(HeadParish).filter(
            HeadParish.diocese_id == did, ChurchMember.is_active == True
        ).scalar(),
        "total_admins": db.query(func.count(Admin.id)).filter(Admin.diocese_id == did, Admin.is_active == True).scalar(),
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
            "email": p.email, "phone": p.phone,
            "head_parish_count": hp_count,
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
# HEAD PARISHES (view from diocese level)
# ═══════════════════════════════════════════════════════════════

@router.get("/head-parishes")
def list_head_parishes(province_id: Optional[int] = None, db: Session = Depends(get_db), admin: Admin = Depends(get_current_admin)):
    did = _require_diocese_admin(admin)
    q = db.query(HeadParish).filter(HeadParish.diocese_id == did, HeadParish.is_active == True)
    if province_id:
        q = q.filter(HeadParish.province_id == province_id)
    return success_response(data=[{
        "id": hp.id, "name": hp.name, "province_id": hp.province_id,
        "address": hp.address, "email": hp.email, "phone": hp.phone,
    } for hp in q.order_by(HeadParish.name).all()])


# ═══════════════════════════════════════════════════════════════
# DIOCESE-LEVEL ADMIN MANAGEMENT
# ═══════════════════════════════════════════════════════════════

class DioceseAdminCreate(BaseModel):
    fullname: str
    phone: str
    email: Optional[str] = ""
    role: str
    admin_level: str  # diocese or province
    province_id: Optional[int] = None

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
    if body.admin_level not in ("diocese", "province"):
        raise HTTPException(400, "Diocese admin can only create diocese or province level admins")
    if not is_valid_phone(body.phone):
        raise HTTPException(400, "Invalid phone")
    a = Admin(
        fullname=body.fullname, phone=body.phone, email=body.email or None,
        role=body.role, password=hash_password("KanisaLangu"),
        admin_level=body.admin_level, diocese_id=did,
        province_id=body.province_id,
    )
    db.add(a); db.commit(); db.refresh(a)
    return success_response("Admin registered", {"id": a.id})


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
