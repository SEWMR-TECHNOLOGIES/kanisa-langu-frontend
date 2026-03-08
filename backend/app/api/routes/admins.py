# api/routes/admins.py
"""Admin CRUD — unified for all levels (diocese, province, head_parish, sub_parish, community, group)."""
from fastapi import APIRouter, Depends, HTTPException
from pydantic import BaseModel
from sqlalchemy.orm import Session
from typing import Optional

from core.database import get_db
from models.admins import Admin
from utils.auth import hash_password, get_current_admin
from utils.validation import is_valid_email, is_valid_phone
from utils.response import success_response

router = APIRouter(prefix="/admins", tags=["Admins"])

VALID_LEVELS = ("diocese", "province", "head_parish", "sub_parish", "community", "group")
VALID_ROLES = ("admin", "bishop", "secretary", "chairperson", "accountant", "clerk", "pastor", "evangelist", "elder")


class AdminCreate(BaseModel):
    fullname: str
    email: Optional[str] = ""
    phone: str
    role: str
    admin_level: str
    diocese_id: Optional[int] = None
    province_id: Optional[int] = None
    head_parish_id: Optional[int] = None
    sub_parish_id: Optional[int] = None
    community_id: Optional[int] = None
    group_id: Optional[int] = None


@router.get("/")
def list_admins(
    admin_level: Optional[str] = None,
    head_parish_id: Optional[int] = None,
    diocese_id: Optional[int] = None,
    province_id: Optional[int] = None,
    db: Session = Depends(get_db),
):
    q = db.query(Admin).filter(Admin.is_active == True)
    if admin_level:
        q = q.filter(Admin.admin_level == admin_level)
    if head_parish_id:
        q = q.filter(Admin.head_parish_id == head_parish_id)
    if diocese_id:
        q = q.filter(Admin.diocese_id == diocese_id)
    if province_id:
        q = q.filter(Admin.province_id == province_id)

    return success_response(data=[{
        "id": a.id, "fullname": a.fullname, "email": a.email,
        "phone": a.phone, "role": a.role, "admin_level": a.admin_level,
        "head_parish_id": a.head_parish_id,
    } for a in q.order_by(Admin.fullname).all()])


@router.post("/")
def create_admin(body: AdminCreate, db: Session = Depends(get_db)):
    if body.admin_level not in VALID_LEVELS:
        raise HTTPException(400, "Invalid admin level")
    if body.role not in VALID_ROLES:
        raise HTTPException(400, "Invalid role")
    if not body.fullname.strip():
        raise HTTPException(400, "Fullname is required")
    if not is_valid_phone(body.phone):
        raise HTTPException(400, "Invalid phone number")
    if body.email and not is_valid_email(body.email):
        raise HTTPException(400, "Invalid email")

    # Duplicate phone check within scope
    scope_q = db.query(Admin).filter(Admin.phone == body.phone, Admin.admin_level == body.admin_level, Admin.is_active == True)
    if body.head_parish_id:
        scope_q = scope_q.filter(Admin.head_parish_id == body.head_parish_id)
    if scope_q.first():
        raise HTTPException(400, "Phone already exists for this level")

    default_password = hash_password("KanisaLangu")

    admin = Admin(
        fullname=body.fullname, email=body.email or None, phone=body.phone,
        role=body.role, password=default_password, admin_level=body.admin_level,
        diocese_id=body.diocese_id, province_id=body.province_id,
        head_parish_id=body.head_parish_id, sub_parish_id=body.sub_parish_id,
        community_id=body.community_id, group_id=body.group_id,
    )
    db.add(admin); db.commit(); db.refresh(admin)
    return success_response("Admin registered", {"id": admin.id})


@router.delete("/{admin_id}")
def delete_admin(admin_id: int, db: Session = Depends(get_db)):
    admin = db.query(Admin).filter(Admin.id == admin_id).first()
    if not admin:
        raise HTTPException(404, "Admin not found")
    admin.is_active = False
    db.commit()
    return success_response("Admin deactivated")
