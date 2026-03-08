# api/routes/system_admin.py
"""Kanisa Langu Super Admin routes — system-wide management.
Manages: dioceses, system admins, app versions, global config, overview reports."""

from fastapi import APIRouter, Depends, HTTPException
from pydantic import BaseModel
from sqlalchemy.orm import Session
from sqlalchemy import func, text
from typing import Optional, List
from datetime import date

from core.database import get_db
from models.admins import SystemAdmin, Admin, AdminLogin
from models.hierarchy import Diocese, Province, HeadParish, SubParish, Community, Group
from models.members import ChurchMember
from models.misc import AppVersion, Feedback
from models.finance import BankAccount
from utils.auth import hash_password, get_current_system_admin
from utils.validation import is_valid_email, is_valid_phone
from utils.response import success_response, error_response

router = APIRouter(prefix="/system-admin", tags=["System Admin"])


# ═══════════════════════════════════════════════════════════════
# DASHBOARD / OVERVIEW
# ═══════════════════════════════════════════════════════════════

@router.get("/dashboard")
def system_dashboard(db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    return success_response(data={
        "total_dioceses": db.query(func.count(Diocese.id)).filter(Diocese.is_active == True).scalar(),
        "total_provinces": db.query(func.count(Province.id)).filter(Province.is_active == True).scalar(),
        "total_head_parishes": db.query(func.count(HeadParish.id)).filter(HeadParish.is_active == True).scalar(),
        "total_sub_parishes": db.query(func.count(SubParish.id)).filter(SubParish.is_active == True).scalar(),
        "total_communities": db.query(func.count(Community.id)).filter(Community.is_active == True).scalar(),
        "total_groups": db.query(func.count(Group.id)).filter(Group.is_active == True).scalar(),
        "total_members": db.query(func.count(ChurchMember.id)).filter(ChurchMember.is_active == True).scalar(),
        "total_admins": db.query(func.count(Admin.id)).filter(Admin.is_active == True).scalar(),
    })


# ═══════════════════════════════════════════════════════════════
# DIOCESE MANAGEMENT (CRUD)
# ═══════════════════════════════════════════════════════════════

class DioceseCreate(BaseModel):
    name: str
    region_id: int
    district_id: int
    address: str
    email: str
    phone: str
    website: Optional[str] = None

class DioceseUpdate(BaseModel):
    name: Optional[str] = None
    address: Optional[str] = None
    email: Optional[str] = None
    phone: Optional[str] = None
    website: Optional[str] = None
    region_id: Optional[int] = None
    district_id: Optional[int] = None

@router.get("/dioceses")
def list_dioceses(db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    rows = db.query(Diocese).filter(Diocese.is_active == True).order_by(Diocese.name).all()
    result = []
    for d in rows:
        province_count = db.query(func.count(Province.id)).filter(Province.diocese_id == d.id, Province.is_active == True).scalar()
        hp_count = db.query(func.count(HeadParish.id)).filter(HeadParish.diocese_id == d.id, HeadParish.is_active == True).scalar()
        member_count = db.query(func.count(ChurchMember.id)).join(HeadParish).filter(
            HeadParish.diocese_id == d.id, ChurchMember.is_active == True
        ).scalar()
        result.append({
            "id": d.id, "name": d.name, "address": d.address,
            "email": d.email, "phone": d.phone, "website": d.website,
            "region_id": d.region_id, "district_id": d.district_id,
            "province_count": province_count, "head_parish_count": hp_count,
            "member_count": member_count,
        })
    return success_response(data=result)

@router.post("/dioceses")
def create_diocese(body: DioceseCreate, db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    name = body.name.strip().upper()
    if not is_valid_email(body.email):
        raise HTTPException(400, "Invalid email address")
    if not is_valid_phone(body.phone):
        raise HTTPException(400, "Invalid phone number")
    if db.query(Diocese).filter(Diocese.name == name).first():
        raise HTTPException(400, "Diocese already exists")
    d = Diocese(name=name, region_id=body.region_id, district_id=body.district_id,
                address=body.address, email=body.email, phone=body.phone, website=body.website)
    db.add(d); db.commit(); db.refresh(d)
    return success_response("Diocese registered", {"id": d.id})

@router.put("/dioceses/{diocese_id}")
def update_diocese(diocese_id: int, body: DioceseUpdate, db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    d = db.query(Diocese).filter(Diocese.id == diocese_id).first()
    if not d:
        raise HTTPException(404, "Diocese not found")
    for field, value in body.dict(exclude_unset=True).items():
        if field == "name" and value:
            value = value.strip().upper()
        setattr(d, field, value)
    db.commit()
    return success_response("Diocese updated")

@router.delete("/dioceses/{diocese_id}")
def deactivate_diocese(diocese_id: int, db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    d = db.query(Diocese).filter(Diocese.id == diocese_id).first()
    if not d:
        raise HTTPException(404, "Diocese not found")
    d.is_active = False
    db.commit()
    return success_response("Diocese deactivated")

@router.get("/dioceses/{diocese_id}")
def get_diocese_detail(diocese_id: int, db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    d = db.query(Diocese).filter(Diocese.id == diocese_id).first()
    if not d:
        raise HTTPException(404, "Diocese not found")
    provinces = db.query(Province).filter(Province.diocese_id == d.id, Province.is_active == True).order_by(Province.name).all()
    admins = db.query(Admin).filter(Admin.diocese_id == d.id, Admin.admin_level == "diocese", Admin.is_active == True).all()
    return success_response(data={
        "id": d.id, "name": d.name, "address": d.address, "email": d.email,
        "phone": d.phone, "website": d.website,
        "provinces": [{"id": p.id, "name": p.name} for p in provinces],
        "admins": [{"id": a.id, "fullname": a.fullname, "role": a.role, "phone": a.phone} for a in admins],
    })


# ═══════════════════════════════════════════════════════════════
# SYSTEM ADMIN MANAGEMENT
# ═══════════════════════════════════════════════════════════════

class SystemAdminCreate(BaseModel):
    username: str
    password: str
    role: str = "admin"

@router.get("/system-admins")
def list_system_admins(db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    rows = db.query(SystemAdmin).filter(SystemAdmin.is_active == True).order_by(SystemAdmin.username).all()
    return success_response(data=[{
        "id": a.id, "username": a.username, "role": a.role,
    } for a in rows])

@router.post("/system-admins")
def create_system_admin(body: SystemAdminCreate, db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    if admin.role != "super_admin":
        raise HTTPException(403, "Only super admins can create system admins")
    if db.query(SystemAdmin).filter(SystemAdmin.username == body.username).first():
        raise HTTPException(400, "Username already exists")
    sa = SystemAdmin(username=body.username, password=hash_password(body.password), role=body.role)
    db.add(sa); db.commit(); db.refresh(sa)
    return success_response("System admin created", {"id": sa.id})

@router.delete("/system-admins/{sa_id}")
def deactivate_system_admin(sa_id: int, db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    if admin.role != "super_admin":
        raise HTTPException(403, "Only super admins can manage system admins")
    sa = db.query(SystemAdmin).filter(SystemAdmin.id == sa_id).first()
    if not sa:
        raise HTTPException(404, "System admin not found")
    sa.is_active = False; db.commit()
    return success_response("System admin deactivated")


# ═══════════════════════════════════════════════════════════════
# ALL ADMINS — View/Manage admins across all levels
# ═══════════════════════════════════════════════════════════════

@router.get("/admins")
def list_all_admins(
    admin_level: Optional[str] = None,
    diocese_id: Optional[int] = None,
    province_id: Optional[int] = None,
    head_parish_id: Optional[int] = None,
    db: Session = Depends(get_db),
    admin: SystemAdmin = Depends(get_current_system_admin),
):
    q = db.query(Admin).filter(Admin.is_active == True)
    if admin_level:
        q = q.filter(Admin.admin_level == admin_level)
    if diocese_id:
        q = q.filter(Admin.diocese_id == diocese_id)
    if province_id:
        q = q.filter(Admin.province_id == province_id)
    if head_parish_id:
        q = q.filter(Admin.head_parish_id == head_parish_id)
    return success_response(data=[{
        "id": a.id, "fullname": a.fullname, "email": a.email, "phone": a.phone,
        "role": a.role, "admin_level": a.admin_level,
        "diocese_id": a.diocese_id, "province_id": a.province_id,
        "head_parish_id": a.head_parish_id,
    } for a in q.order_by(Admin.admin_level, Admin.fullname).all()])


# ═══════════════════════════════════════════════════════════════
# APP VERSIONS
# ═══════════════════════════════════════════════════════════════

class AppVersionCreate(BaseModel):
    platform: str
    version: str
    force_update: bool = False

@router.get("/app-versions")
def list_app_versions(db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    rows = db.query(AppVersion).order_by(AppVersion.created_at.desc()).all()
    return success_response(data=[{
        "id": v.id, "platform": v.platform, "version": v.version,
        "force_update": v.force_update,
    } for v in rows])

@router.post("/app-versions")
def create_app_version(body: AppVersionCreate, db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    v = AppVersion(**body.dict())
    db.add(v); db.commit(); db.refresh(v)
    return success_response("App version created", {"id": v.id})


# ═══════════════════════════════════════════════════════════════
# FEEDBACK VIEWING
# ═══════════════════════════════════════════════════════════════

@router.get("/feedback")
def list_all_feedback(db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    rows = db.query(Feedback).order_by(Feedback.submitted_at.desc()).limit(100).all()
    return success_response(data=[{
        "id": f.id, "head_parish_id": f.head_parish_id,
        "feedback_type": f.feedback_type, "subject": f.subject,
        "message": f.message, "submitted_at": str(f.submitted_at),
    } for f in rows])


# ═══════════════════════════════════════════════════════════════
# AUDIT LOG — Recent logins
# ═══════════════════════════════════════════════════════════════

@router.get("/audit-log")
def get_audit_log(limit: int = 50, db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    rows = db.query(AdminLogin).order_by(AdminLogin.login_time.desc()).limit(limit).all()
    return success_response(data=[{
        "id": l.id, "admin_id": l.admin_id, "system_admin_id": l.system_admin_id,
        "login_time": str(l.login_time), "ip_address": l.ip_address,
    } for l in rows])


# ═══════════════════════════════════════════════════════════════
# GLOBAL STATS / REPORTS
# ═══════════════════════════════════════════════════════════════

@router.get("/stats/members-by-diocese")
def members_by_diocese(db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    rows = db.execute(text("""
        SELECT d.id, d.name,
               COUNT(DISTINCT hp.id) AS head_parishes,
               COUNT(DISTINCT cm.id) AS members
        FROM dioceses d
        LEFT JOIN head_parishes hp ON hp.diocese_id = d.id AND hp.is_active = true
        LEFT JOIN church_members cm ON cm.head_parish_id = hp.id AND cm.is_active = true
        WHERE d.is_active = true
        GROUP BY d.id, d.name
        ORDER BY d.name
    """)).mappings().all()
    return success_response(data=[dict(r) for r in rows])
