# api/routes/system_admin.py
"""Kanisa Langu Super Admin routes — system-wide management.
Manages: dioceses, provinces, head-parishes, system admins, app versions,
reference data (regions, districts, banks, titles, occupations, praise songs),
payments overview, global stats, audit log, feedback."""

from fastapi import APIRouter, Depends, HTTPException, Query
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
from models.finance import BankAccount, Revenue, Expense
from models.payments import Payment
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
    d.is_active = False; db.commit()
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
# PROVINCE MANAGEMENT (system-wide view)
# ═══════════════════════════════════════════════════════════════

class ProvinceCreate(BaseModel):
    name: str
    diocese_id: int
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
def list_provinces(diocese_id: Optional[int] = None, db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    q = db.query(Province).filter(Province.is_active == True)
    if diocese_id:
        q = q.filter(Province.diocese_id == diocese_id)
    rows = q.order_by(Province.name).all()
    result = []
    for p in rows:
        hp_count = db.query(func.count(HeadParish.id)).filter(HeadParish.province_id == p.id, HeadParish.is_active == True).scalar()
        result.append({
            "id": p.id, "name": p.name, "diocese_id": p.diocese_id,
            "address": p.address, "email": p.email, "phone": p.phone,
            "head_parish_count": hp_count,
        })
    return success_response(data=result)

@router.post("/provinces")
def create_province(body: ProvinceCreate, db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    name = body.name.strip().upper()
    if not is_valid_email(body.email):
        raise HTTPException(400, "Invalid email")
    if not is_valid_phone(body.phone):
        raise HTTPException(400, "Invalid phone")
    if db.query(Province).filter(Province.name == name, Province.diocese_id == body.diocese_id).first():
        raise HTTPException(400, "Province already exists in this diocese")
    p = Province(name=name, diocese_id=body.diocese_id, region_id=body.region_id,
                 district_id=body.district_id, address=body.address, email=body.email, phone=body.phone)
    db.add(p); db.commit(); db.refresh(p)
    return success_response("Province registered", {"id": p.id})

@router.put("/provinces/{province_id}")
def update_province(province_id: int, body: ProvinceUpdate, db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    p = db.query(Province).filter(Province.id == province_id).first()
    if not p:
        raise HTTPException(404, "Province not found")
    for field, value in body.dict(exclude_unset=True).items():
        if field == "name" and value:
            value = value.strip().upper()
        setattr(p, field, value)
    db.commit()
    return success_response("Province updated")

@router.delete("/provinces/{province_id}")
def deactivate_province(province_id: int, db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    p = db.query(Province).filter(Province.id == province_id).first()
    if not p:
        raise HTTPException(404, "Province not found")
    p.is_active = False; db.commit()
    return success_response("Province deactivated")


# ═══════════════════════════════════════════════════════════════
# HEAD PARISH MANAGEMENT (system-wide view)
# ═══════════════════════════════════════════════════════════════

class HeadParishCreate(BaseModel):
    name: str
    diocese_id: int
    province_id: int
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
def list_head_parishes(diocese_id: Optional[int] = None, province_id: Optional[int] = None,
                       db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    q = db.query(HeadParish).filter(HeadParish.is_active == True)
    if diocese_id:
        q = q.filter(HeadParish.diocese_id == diocese_id)
    if province_id:
        q = q.filter(HeadParish.province_id == province_id)
    rows = q.order_by(HeadParish.name).all()
    result = []
    for hp in rows:
        member_count = db.query(func.count(ChurchMember.id)).filter(
            ChurchMember.head_parish_id == hp.id, ChurchMember.is_active == True
        ).scalar()
        result.append({
            "id": hp.id, "name": hp.name, "diocese_id": hp.diocese_id,
            "province_id": hp.province_id, "address": hp.address,
            "email": hp.email, "phone": hp.phone, "member_count": member_count,
        })
    return success_response(data=result)

@router.post("/head-parishes")
def create_head_parish(body: HeadParishCreate, db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    name = body.name.strip().upper()
    if not is_valid_email(body.email):
        raise HTTPException(400, "Invalid email")
    if db.query(HeadParish).filter(HeadParish.name == name, HeadParish.province_id == body.province_id).first():
        raise HTTPException(400, "Head parish already exists in this province")
    hp = HeadParish(name=name, **body.dict(exclude={"name"}))
    db.add(hp); db.commit(); db.refresh(hp)
    return success_response("Head parish registered", {"id": hp.id})

@router.put("/head-parishes/{hp_id}")
def update_head_parish(hp_id: int, body: HeadParishUpdate, db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    hp = db.query(HeadParish).filter(HeadParish.id == hp_id).first()
    if not hp:
        raise HTTPException(404, "Head parish not found")
    for field, value in body.dict(exclude_unset=True).items():
        if field == "name" and value:
            value = value.strip().upper()
        setattr(hp, field, value)
    db.commit()
    return success_response("Head parish updated")

@router.delete("/head-parishes/{hp_id}")
def deactivate_head_parish(hp_id: int, db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    hp = db.query(HeadParish).filter(HeadParish.id == hp_id).first()
    if not hp:
        raise HTTPException(404, "Head parish not found")
    hp.is_active = False; db.commit()
    return success_response("Head parish deactivated")


# ═══════════════════════════════════════════════════════════════
# REFERENCE DATA CRUD — Regions, Districts, Banks, Titles, Occupations, Praise Songs
# ═══════════════════════════════════════════════════════════════

class NameBody(BaseModel):
    name: str

class RegionBody(BaseModel):
    name: str

class DistrictBody(BaseModel):
    name: str
    region_id: int

class BankBody(BaseModel):
    name: str

class PraiseSongBody(BaseModel):
    song_number: int
    name: str

# ── Regions ──
@router.get("/regions")
def list_regions(db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    rows = db.execute(text("SELECT id, name FROM regions ORDER BY name")).mappings().all()
    return success_response(data=[dict(r) for r in rows])

@router.post("/regions")
def create_region(body: RegionBody, db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    name = body.name.strip().upper()
    existing = db.execute(text("SELECT id FROM regions WHERE UPPER(name)=:n"), {"n": name}).first()
    if existing:
        raise HTTPException(400, "Region already exists")
    db.execute(text("INSERT INTO regions (name) VALUES (:n)"), {"n": name})
    db.commit()
    return success_response("Region added")

@router.put("/regions/{region_id}")
def update_region(region_id: int, body: RegionBody, db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    db.execute(text("UPDATE regions SET name=:n WHERE id=:id"), {"n": body.name.strip().upper(), "id": region_id})
    db.commit()
    return success_response("Region updated")

@router.delete("/regions/{region_id}")
def delete_region(region_id: int, db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    db.execute(text("DELETE FROM regions WHERE id=:id"), {"id": region_id})
    db.commit()
    return success_response("Region deleted")

# ── Districts ──
@router.get("/districts")
def list_districts(region_id: Optional[int] = None, db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    sql = "SELECT id, name, region_id FROM districts"
    params = {}
    if region_id:
        sql += " WHERE region_id=:rid"
        params["rid"] = region_id
    rows = db.execute(text(sql + " ORDER BY name"), params).mappings().all()
    return success_response(data=[dict(r) for r in rows])

@router.post("/districts")
def create_district(body: DistrictBody, db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    name = body.name.strip().upper()
    existing = db.execute(text("SELECT id FROM districts WHERE UPPER(name)=:n AND region_id=:rid"), {"n": name, "rid": body.region_id}).first()
    if existing:
        raise HTTPException(400, "District already exists in this region")
    db.execute(text("INSERT INTO districts (name, region_id) VALUES (:n, :rid)"), {"n": name, "rid": body.region_id})
    db.commit()
    return success_response("District added")

@router.put("/districts/{district_id}")
def update_district(district_id: int, body: DistrictBody, db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    db.execute(text("UPDATE districts SET name=:n, region_id=:rid WHERE id=:id"), {"n": body.name.strip().upper(), "rid": body.region_id, "id": district_id})
    db.commit()
    return success_response("District updated")

@router.delete("/districts/{district_id}")
def delete_district(district_id: int, db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    db.execute(text("DELETE FROM districts WHERE id=:id"), {"id": district_id})
    db.commit()
    return success_response("District deleted")

# ── Banks ──
@router.get("/banks")
def list_banks(db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    rows = db.execute(text("SELECT id, name FROM banks ORDER BY name")).mappings().all()
    return success_response(data=[dict(r) for r in rows])

@router.post("/banks")
def create_bank(body: BankBody, db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    name = body.name.strip().upper()
    existing = db.execute(text("SELECT id FROM banks WHERE UPPER(name)=:n"), {"n": name}).first()
    if existing:
        raise HTTPException(400, "Bank already exists")
    db.execute(text("INSERT INTO banks (name) VALUES (:n)"), {"n": name})
    db.commit()
    return success_response("Bank added")

@router.put("/banks/{bank_id}")
def update_bank(bank_id: int, body: BankBody, db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    db.execute(text("UPDATE banks SET name=:n WHERE id=:id"), {"n": body.name.strip().upper(), "id": bank_id})
    db.commit()
    return success_response("Bank updated")

@router.delete("/banks/{bank_id}")
def delete_bank(bank_id: int, db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    db.execute(text("DELETE FROM banks WHERE id=:id"), {"id": bank_id})
    db.commit()
    return success_response("Bank deleted")

# ── Titles ──
@router.get("/titles")
def list_titles(db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    rows = db.execute(text("SELECT id, name FROM titles ORDER BY name")).mappings().all()
    return success_response(data=[dict(r) for r in rows])

@router.post("/titles")
def create_title(body: NameBody, db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    name = body.name.strip()
    existing = db.execute(text("SELECT id FROM titles WHERE LOWER(name)=LOWER(:n)"), {"n": name}).first()
    if existing:
        raise HTTPException(400, "Title already exists")
    db.execute(text("INSERT INTO titles (name) VALUES (:n)"), {"n": name})
    db.commit()
    return success_response("Title added")

@router.put("/titles/{title_id}")
def update_title(title_id: int, body: NameBody, db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    db.execute(text("UPDATE titles SET name=:n WHERE id=:id"), {"n": body.name.strip(), "id": title_id})
    db.commit()
    return success_response("Title updated")

@router.delete("/titles/{title_id}")
def delete_title(title_id: int, db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    db.execute(text("DELETE FROM titles WHERE id=:id"), {"id": title_id})
    db.commit()
    return success_response("Title deleted")

# ── Occupations ──
@router.get("/occupations")
def list_occupations(db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    rows = db.execute(text("SELECT id, name FROM occupations ORDER BY name")).mappings().all()
    return success_response(data=[dict(r) for r in rows])

@router.post("/occupations")
def create_occupation(body: NameBody, db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    name = body.name.strip()
    existing = db.execute(text("SELECT id FROM occupations WHERE LOWER(name)=LOWER(:n)"), {"n": name}).first()
    if existing:
        raise HTTPException(400, "Occupation already exists")
    db.execute(text("INSERT INTO occupations (name) VALUES (:n)"), {"n": name})
    db.commit()
    return success_response("Occupation added")

@router.put("/occupations/{occ_id}")
def update_occupation(occ_id: int, body: NameBody, db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    db.execute(text("UPDATE occupations SET name=:n WHERE id=:id"), {"n": body.name.strip(), "id": occ_id})
    db.commit()
    return success_response("Occupation updated")

@router.delete("/occupations/{occ_id}")
def delete_occupation(occ_id: int, db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    db.execute(text("DELETE FROM occupations WHERE id=:id"), {"id": occ_id})
    db.commit()
    return success_response("Occupation deleted")

# ── Praise Songs ──
@router.get("/praise-songs")
def list_praise_songs(db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    rows = db.execute(text("SELECT id, song_number, name FROM praise_songs ORDER BY song_number")).mappings().all()
    return success_response(data=[dict(r) for r in rows])

@router.post("/praise-songs")
def create_praise_song(body: PraiseSongBody, db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    existing = db.execute(text("SELECT id FROM praise_songs WHERE song_number=:sn"), {"sn": body.song_number}).first()
    if existing:
        raise HTTPException(400, "Song number already exists")
    db.execute(text("INSERT INTO praise_songs (song_number, name) VALUES (:sn, :n)"), {"sn": body.song_number, "n": body.name.strip()})
    db.commit()
    return success_response("Praise song added")

@router.put("/praise-songs/{song_id}")
def update_praise_song(song_id: int, body: PraiseSongBody, db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    db.execute(text("UPDATE praise_songs SET song_number=:sn, name=:n WHERE id=:id"), {"sn": body.song_number, "n": body.name.strip(), "id": song_id})
    db.commit()
    return success_response("Praise song updated")

@router.delete("/praise-songs/{song_id}")
def delete_praise_song(song_id: int, db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    db.execute(text("DELETE FROM praise_songs WHERE id=:id"), {"id": song_id})
    db.commit()
    return success_response("Praise song deleted")


# ═══════════════════════════════════════════════════════════════
# DIOCESE ADMIN MANAGEMENT
# ═══════════════════════════════════════════════════════════════

class AdminCreateBody(BaseModel):
    fullname: str
    phone: str
    email: Optional[str] = ""
    role: str
    admin_level: str
    diocese_id: int
    province_id: Optional[int] = None
    head_parish_id: Optional[int] = None

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

@router.post("/admins")
def create_admin(body: AdminCreateBody, db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    if not is_valid_phone(body.phone):
        raise HTTPException(400, "Invalid phone")
    a = Admin(
        fullname=body.fullname, phone=body.phone, email=body.email or None,
        role=body.role, password=hash_password("KanisaLangu"),
        admin_level=body.admin_level, diocese_id=body.diocese_id,
        province_id=body.province_id, head_parish_id=body.head_parish_id,
    )
    db.add(a); db.commit(); db.refresh(a)
    return success_response("Admin created", {"id": a.id})

@router.delete("/admins/{admin_id}")
def deactivate_admin(admin_id: int, db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    target = db.query(Admin).filter(Admin.id == admin_id).first()
    if not target:
        raise HTTPException(404, "Admin not found")
    target.is_active = False; db.commit()
    return success_response("Admin deactivated")


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
    return success_response(data=[{"id": a.id, "username": a.username, "role": a.role} for a in rows])

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
# PAYMENTS OVERVIEW
# ═══════════════════════════════════════════════════════════════

@router.get("/payments")
def list_all_payments(
    head_parish_id: Optional[int] = None,
    page: int = Query(1, ge=1), limit: int = Query(50, ge=1, le=200),
    db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin),
):
    q = db.query(Payment)
    if head_parish_id:
        q = q.filter(Payment.head_parish_id == head_parish_id)
    total = q.count()
    rows = q.order_by(Payment.created_at.desc()).offset((page - 1) * limit).limit(limit).all()
    return success_response(data={
        "payments": [{
            "id": p.id, "amount": float(p.amount), "payment_reason": p.payment_reason,
            "payment_status": p.payment_status, "payment_date": str(p.payment_date),
            "head_parish_id": p.head_parish_id,
        } for p in rows],
        "total": total, "page": page, "total_pages": -(-total // limit),
    })

@router.get("/payments/summary")
def payments_summary(db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    total_amount = db.query(func.coalesce(func.sum(Payment.amount), 0)).filter(Payment.payment_status == "Completed").scalar()
    total_count = db.query(func.count(Payment.id)).scalar()
    completed = db.query(func.count(Payment.id)).filter(Payment.payment_status == "Completed").scalar()
    pending = db.query(func.count(Payment.id)).filter(Payment.payment_status == "Pending").scalar()
    return success_response(data={
        "total_amount": float(total_amount or 0), "total_transactions": total_count,
        "completed": completed, "pending": pending,
    })


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
        "id": v.id, "platform": v.platform, "version": v.version, "force_update": v.force_update,
    } for v in rows])

@router.post("/app-versions")
def create_app_version(body: AppVersionCreate, db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    v = AppVersion(**body.dict())
    db.add(v); db.commit(); db.refresh(v)
    return success_response("App version created", {"id": v.id})


# ═══════════════════════════════════════════════════════════════
# FEEDBACK
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
# AUDIT LOG
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

@router.get("/stats/revenue-by-diocese")
def revenue_by_diocese(db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    rows = db.execute(text("""
        SELECT d.id, d.name,
               COALESCE(SUM(r.amount), 0) AS total_revenue
        FROM dioceses d
        LEFT JOIN head_parishes hp ON hp.diocese_id = d.id
        LEFT JOIN revenues r ON r.head_parish_id = hp.id
        WHERE d.is_active = true
        GROUP BY d.id, d.name
        ORDER BY total_revenue DESC
    """)).mappings().all()
    return success_response(data=[dict(r) for r in rows])

@router.get("/reports/sales")
def sales_report(
    year: Optional[int] = None, month: Optional[int] = None,
    db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin),
):
    q = db.query(Payment).filter(Payment.payment_status == "Completed")
    if year:
        q = q.filter(func.extract("year", Payment.payment_date) == year)
    if month:
        q = q.filter(func.extract("month", Payment.payment_date) == month)
    total = float(q.with_entities(func.coalesce(func.sum(Payment.amount), 0)).scalar() or 0)
    count = q.count()
    return success_response(data={"total_sales": total, "transaction_count": count})

@router.get("/reports/sms-usage")
def sms_usage_report(db: Session = Depends(get_db), admin: SystemAdmin = Depends(get_current_system_admin)):
    # SMS usage tracked via sms_logs table
    rows = db.execute(text("""
        SELECT hp.id AS head_parish_id, hp.name AS head_parish_name,
               COUNT(sl.id) AS total_sms, COALESCE(SUM(sl.cost), 0) AS total_cost
        FROM head_parishes hp
        LEFT JOIN sms_logs sl ON sl.head_parish_id = hp.id
        WHERE hp.is_active = true
        GROUP BY hp.id, hp.name
        ORDER BY total_sms DESC
    """)).mappings().all()
    return success_response(data=[dict(r) for r in rows])
