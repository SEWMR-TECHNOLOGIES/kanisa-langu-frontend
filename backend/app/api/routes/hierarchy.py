# api/routes/hierarchy.py
"""CRUD for church hierarchy: dioceses, provinces, head parishes, sub parishes, communities, groups."""
from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.orm import Session
from typing import Optional, List

from core.database import get_db
from models.hierarchy import Diocese, Province, HeadParish, SubParish, Community, Group
from utils.validation import is_valid_email, is_valid_phone
from utils.response import success_response

from schemas.base import ApiResponse, IdData
from schemas.hierarchy import (
    DioceseCreate, ProvinceCreate, HeadParishCreate,
    SubParishCreate, CommunityCreate, GroupCreate,
    DioceseOut, ProvinceOut, HeadParishOut, SubParishOut, CommunityOut, GroupOut,
)

router = APIRouter(tags=["Church Hierarchy"])


# ══════════════════════════════════════════════════════════════
# DIOCESES
# ══════════════════════════════════════════════════════════════

@router.get("/dioceses", response_model=ApiResponse[List[DioceseOut]], summary="List all active dioceses")
def list_dioceses(db: Session = Depends(get_db)):
    rows = db.query(Diocese).filter(Diocese.is_active == True).order_by(Diocese.name).all()
    return success_response(data=[{
        "id": d.id, "name": d.name, "address": d.address,
        "email": d.email, "phone": d.phone,
        "region_id": d.region_id, "district_id": d.district_id,
    } for d in rows])

@router.post("/dioceses", response_model=ApiResponse[IdData], summary="Register a new diocese")
def create_diocese(body: DioceseCreate, db: Session = Depends(get_db)):
    name = body.name.upper()
    if not is_valid_email(body.email):
        raise HTTPException(400, "Invalid email address")
    if not is_valid_phone(body.phone):
        raise HTTPException(400, "Invalid phone number")
    if db.query(Diocese).filter(Diocese.name == name).first():
        raise HTTPException(400, "Diocese already exists")
    d = Diocese(name=name, region_id=body.region_id, district_id=body.district_id,
                address=body.address, email=body.email, phone=body.phone)
    db.add(d); db.commit(); db.refresh(d)
    return success_response("Diocese registered", {"id": d.id})

@router.get("/dioceses/{diocese_id}", response_model=ApiResponse[DioceseOut], summary="Get diocese details")
def get_diocese(diocese_id: int, db: Session = Depends(get_db)):
    d = db.query(Diocese).filter(Diocese.id == diocese_id).first()
    if not d:
        raise HTTPException(404, "Diocese not found")
    return success_response(data={"id": d.id, "name": d.name, "address": d.address,
                                   "email": d.email, "phone": d.phone})


# ══════════════════════════════════════════════════════════════
# PROVINCES
# ══════════════════════════════════════════════════════════════

@router.get("/provinces", response_model=ApiResponse[List[ProvinceOut]], summary="List provinces, optionally filtered by diocese")
def list_provinces(diocese_id: Optional[int] = None, db: Session = Depends(get_db)):
    q = db.query(Province).filter(Province.is_active == True)
    if diocese_id:
        q = q.filter(Province.diocese_id == diocese_id)
    return success_response(data=[{
        "id": p.id, "name": p.name, "diocese_id": p.diocese_id,
        "address": p.address, "email": p.email, "phone": p.phone,
        "region_id": p.region_id, "district_id": p.district_id,
    } for p in q.order_by(Province.name).all()])

@router.post("/provinces", response_model=ApiResponse[IdData], summary="Register a new province")
def create_province(body: ProvinceCreate, db: Session = Depends(get_db)):
    name = body.name.upper()
    if not is_valid_email(body.email):
        raise HTTPException(400, "Invalid email")
    if not is_valid_phone(body.phone):
        raise HTTPException(400, "Invalid phone")
    p = Province(name=name, diocese_id=body.diocese_id, region_id=body.region_id,
                 district_id=body.district_id, address=body.address,
                 email=body.email, phone=body.phone)
    db.add(p); db.commit(); db.refresh(p)
    return success_response("Province registered", {"id": p.id})


# ══════════════════════════════════════════════════════════════
# HEAD PARISHES
# ══════════════════════════════════════════════════════════════

@router.get("/head-parishes", response_model=ApiResponse[List[HeadParishOut]], summary="List head parishes")
def list_head_parishes(province_id: Optional[int] = None, diocese_id: Optional[int] = None, db: Session = Depends(get_db)):
    q = db.query(HeadParish).filter(HeadParish.is_active == True)
    if province_id:
        q = q.filter(HeadParish.province_id == province_id)
    if diocese_id:
        q = q.filter(HeadParish.diocese_id == diocese_id)
    return success_response(data=[{
        "id": h.id, "name": h.name, "diocese_id": h.diocese_id,
        "province_id": h.province_id, "address": h.address,
        "email": h.email, "phone": h.phone,
    } for h in q.order_by(HeadParish.name).all()])

@router.post("/head-parishes", response_model=ApiResponse[IdData], summary="Register a new head parish")
def create_head_parish(body: HeadParishCreate, db: Session = Depends(get_db)):
    name = body.name.upper()
    if not is_valid_email(body.email):
        raise HTTPException(400, "Invalid email")
    if db.query(HeadParish).filter(HeadParish.name == name, HeadParish.diocese_id == body.diocese_id, HeadParish.province_id == body.province_id).first():
        raise HTTPException(400, "Head parish already exists in this diocese/province")
    hp = HeadParish(name=name, diocese_id=body.diocese_id, province_id=body.province_id,
                    region_id=body.region_id, district_id=body.district_id,
                    address=body.address, email=body.email, phone=body.phone)
    db.add(hp); db.commit(); db.refresh(hp)
    return success_response("Head parish registered", {"id": hp.id})


# ══════════════════════════════════════════════════════════════
# SUB PARISHES
# ══════════════════════════════════════════════════════════════

@router.get("/sub-parishes", response_model=ApiResponse[List[SubParishOut]], summary="List sub parishes for a head parish")
def list_sub_parishes(head_parish_id: int, db: Session = Depends(get_db)):
    rows = db.query(SubParish).filter(SubParish.head_parish_id == head_parish_id, SubParish.is_active == True).order_by(SubParish.name).all()
    return success_response(data=[{"id": s.id, "name": s.name, "description": s.description} for s in rows])

@router.post("/sub-parishes", response_model=ApiResponse[IdData], summary="Create a sub parish")
def create_sub_parish(body: SubParishCreate, db: Session = Depends(get_db)):
    name = body.name.upper()
    if db.query(SubParish).filter(SubParish.name == name, SubParish.head_parish_id == body.head_parish_id).first():
        raise HTTPException(400, "Sub parish already exists")
    sp = SubParish(name=name, head_parish_id=body.head_parish_id, description=body.description)
    db.add(sp); db.commit(); db.refresh(sp)
    return success_response("Sub parish added", {"id": sp.id})


# ══════════════════════════════════════════════════════════════
# COMMUNITIES
# ══════════════════════════════════════════════════════════════

@router.get("/communities", response_model=ApiResponse[List[CommunityOut]], summary="List communities")
def list_communities(sub_parish_id: Optional[int] = None, head_parish_id: Optional[int] = None, db: Session = Depends(get_db)):
    q = db.query(Community).filter(Community.is_active == True)
    if sub_parish_id:
        q = q.filter(Community.sub_parish_id == sub_parish_id)
    if head_parish_id:
        q = q.filter(Community.head_parish_id == head_parish_id)
    return success_response(data=[{"id": c.id, "name": c.name, "sub_parish_id": c.sub_parish_id} for c in q.order_by(Community.name).all()])

@router.post("/communities", response_model=ApiResponse[IdData], summary="Create a community")
def create_community(body: CommunityCreate, db: Session = Depends(get_db)):
    name = body.name.upper()
    if db.query(Community).filter(Community.name == name, Community.sub_parish_id == body.sub_parish_id).first():
        raise HTTPException(400, "Community already exists in this sub parish")
    c = Community(name=name, head_parish_id=body.head_parish_id, sub_parish_id=body.sub_parish_id, description=body.description)
    db.add(c); db.commit(); db.refresh(c)
    return success_response("Community added", {"id": c.id})


# ══════════════════════════════════════════════════════════════
# GROUPS
# ══════════════════════════════════════════════════════════════

@router.get("/groups", response_model=ApiResponse[List[GroupOut]], summary="List groups for a head parish")
def list_groups(head_parish_id: int, db: Session = Depends(get_db)):
    rows = db.query(Group).filter(Group.head_parish_id == head_parish_id, Group.is_active == True).order_by(Group.name).all()
    return success_response(data=[{"id": g.id, "name": g.name, "description": g.description} for g in rows])

@router.post("/groups", response_model=ApiResponse[IdData], summary="Create a group")
def create_group(body: GroupCreate, db: Session = Depends(get_db)):
    name = body.name.upper()
    if db.query(Group).filter(Group.name == name, Group.head_parish_id == body.head_parish_id).first():
        raise HTTPException(400, "Group already exists")
    g = Group(name=name, head_parish_id=body.head_parish_id, description=body.description)
    db.add(g); db.commit(); db.refresh(g)
    return success_response("Group added", {"id": g.id})
