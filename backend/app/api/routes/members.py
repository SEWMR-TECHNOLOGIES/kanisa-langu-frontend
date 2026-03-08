# api/routes/members.py
"""Church member CRUD — mirrors register_church_member.php, church_members.php, update_church_member.php."""
from fastapi import APIRouter, Depends, HTTPException, Query
from pydantic import BaseModel
from sqlalchemy.orm import Session
from sqlalchemy import or_
from typing import Optional
from datetime import date

from core.database import get_db
from models.members import ChurchMember, ChurchLeader, ChurchChoir, MemberExclusion
from utils.validation import is_valid_email, is_valid_phone, normalize_phone, validate_age
from utils.response import success_response

router = APIRouter(prefix="/members", tags=["Church Members"])


class MemberCreate(BaseModel):
    title_id: Optional[int] = None
    first_name: str
    middle_name: Optional[str] = None
    last_name: str
    date_of_birth: date
    gender: str
    member_type: str
    head_parish_id: int
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
    status: Optional[str] = None


@router.get("/")
def list_members(
    head_parish_id: int,
    sub_parish_id: Optional[int] = None,
    community_id: Optional[int] = None,
    query: Optional[str] = None,
    page: int = Query(1, ge=1),
    limit: int = Query(10, ge=1, le=500),
    db: Session = Depends(get_db),
):
    q = db.query(ChurchMember).filter(
        ChurchMember.head_parish_id == head_parish_id,
        ChurchMember.is_active == True,
    )
    if sub_parish_id:
        q = q.filter(ChurchMember.sub_parish_id == sub_parish_id)
    if community_id:
        q = q.filter(ChurchMember.community_id == community_id)
    if query:
        search = f"%{query}%"
        q = q.filter(or_(
            ChurchMember.first_name.ilike(search),
            ChurchMember.middle_name.ilike(search),
            ChurchMember.last_name.ilike(search),
            ChurchMember.phone.ilike(search),
            ChurchMember.email.ilike(search),
            ChurchMember.envelope_number.ilike(search),
        ))

    total = q.count()
    offset = (page - 1) * limit
    members = q.order_by(ChurchMember.envelope_number).offset(offset).limit(limit).all()

    return success_response(data={
        "members": [{
            "id": m.id,
            "first_name": m.first_name,
            "middle_name": m.middle_name,
            "last_name": m.last_name,
            "date_of_birth": str(m.date_of_birth),
            "gender": m.gender,
            "member_type": m.member_type,
            "envelope_number": m.envelope_number,
            "phone": m.phone,
            "email": m.email,
            "sub_parish_id": m.sub_parish_id,
            "community_id": m.community_id,
        } for m in members],
        "total": total,
        "page": page,
        "total_pages": -(-total // limit),  # ceiling division
    })


@router.post("/")
def create_member(body: MemberCreate, db: Session = Depends(get_db)):
    if not body.first_name.strip():
        raise HTTPException(400, "First name is required")
    if not body.last_name.strip():
        raise HTTPException(400, "Last name is required")
    if not validate_age(body.date_of_birth, 5):
        raise HTTPException(400, "Date of birth must be at least 5 years ago")
    if body.member_type not in ("Mgeni", "Mwenyeji"):
        raise HTTPException(400, "Type must be Mgeni or Mwenyeji")

    phone = normalize_phone(body.phone) if body.phone else None
    if body.email and not is_valid_email(body.email):
        raise HTTPException(400, "Invalid email")
    if body.phone and not is_valid_phone(body.phone):
        raise HTTPException(400, "Invalid phone")

    # Uniqueness checks
    if body.email and db.query(ChurchMember).filter(ChurchMember.email == body.email).first():
        raise HTTPException(400, "Email already exists")
    if phone and db.query(ChurchMember).filter(ChurchMember.phone == phone).first():
        raise HTTPException(400, "Phone already exists")
    if body.envelope_number and db.query(ChurchMember).filter(ChurchMember.envelope_number == body.envelope_number).first():
        raise HTTPException(400, "Envelope number already exists")

    member = ChurchMember(
        title_id=body.title_id,
        first_name=body.first_name.capitalize(),
        middle_name=body.middle_name.capitalize() if body.middle_name else None,
        last_name=body.last_name.capitalize(),
        date_of_birth=body.date_of_birth,
        gender=body.gender,
        member_type=body.member_type,
        head_parish_id=body.head_parish_id,
        sub_parish_id=body.sub_parish_id,
        community_id=body.community_id,
        envelope_number=body.envelope_number,
        occupation_id=body.occupation_id,
        phone=phone,
        email=body.email,
    )
    db.add(member); db.commit(); db.refresh(member)
    return success_response("Church member registered", {"id": member.id})


@router.put("/{member_id}")
def update_member(member_id: int, body: MemberUpdate, db: Session = Depends(get_db)):
    member = db.query(ChurchMember).filter(ChurchMember.id == member_id).first()
    if not member:
        raise HTTPException(404, "Member not found")
    for field, value in body.dict(exclude_unset=True).items():
        setattr(member, field, value)
    db.commit()
    return success_response("Member updated")


@router.get("/{member_id}")
def get_member(member_id: int, db: Session = Depends(get_db)):
    m = db.query(ChurchMember).filter(ChurchMember.id == member_id).first()
    if not m:
        raise HTTPException(404, "Member not found")
    return success_response(data={
        "id": m.id, "first_name": m.first_name, "middle_name": m.middle_name,
        "last_name": m.last_name, "date_of_birth": str(m.date_of_birth),
        "gender": m.gender, "member_type": m.member_type,
        "envelope_number": m.envelope_number, "phone": m.phone, "email": m.email,
        "head_parish_id": m.head_parish_id, "sub_parish_id": m.sub_parish_id,
        "community_id": m.community_id, "status": m.status,
    })


# ── Exclusions ───────────────────────────────────────────────
class ExcludeRequest(BaseModel):
    reason: str

@router.post("/{member_id}/exclude")
def exclude_member(member_id: int, body: ExcludeRequest, db: Session = Depends(get_db)):
    member = db.query(ChurchMember).filter(ChurchMember.id == member_id).first()
    if not member:
        raise HTTPException(404, "Member not found")
    member.status = "Excluded"
    db.add(MemberExclusion(member_id=member_id, reason=body.reason))
    db.commit()
    return success_response("Member excluded")


# ── Church Leaders ───────────────────────────────────────────
class LeaderCreate(BaseModel):
    title_id: Optional[int] = None
    first_name: str
    middle_name: Optional[str] = None
    last_name: str
    gender: str
    leader_type: str
    head_parish_id: int
    role_id: int
    appointment_date: date
    end_date: Optional[date] = None

@router.get("/leaders")
def list_leaders(head_parish_id: int, db: Session = Depends(get_db)):
    rows = db.query(ChurchLeader).filter(ChurchLeader.head_parish_id == head_parish_id).order_by(ChurchLeader.first_name).all()
    return success_response(data=[{
        "id": l.id, "first_name": l.first_name, "last_name": l.last_name,
        "role_id": l.role_id, "status": l.status,
        "appointment_date": str(l.appointment_date),
    } for l in rows])

@router.post("/leaders")
def create_leader(body: LeaderCreate, db: Session = Depends(get_db)):
    leader = ChurchLeader(**body.dict())
    db.add(leader); db.commit(); db.refresh(leader)
    return success_response("Church leader registered", {"id": leader.id})


# ── Church Choirs ────────────────────────────────────────────
class ChoirCreate(BaseModel):
    name: str
    head_parish_id: int
    description: Optional[str] = None

@router.get("/choirs")
def list_choirs(head_parish_id: int, db: Session = Depends(get_db)):
    rows = db.query(ChurchChoir).filter(ChurchChoir.head_parish_id == head_parish_id).order_by(ChurchChoir.name).all()
    return success_response(data=[{"id": c.id, "name": c.name} for c in rows])

@router.post("/choirs")
def create_choir(body: ChoirCreate, db: Session = Depends(get_db)):
    if db.query(ChurchChoir).filter(ChurchChoir.name == body.name, ChurchChoir.head_parish_id == body.head_parish_id).first():
        raise HTTPException(400, "Choir already exists")
    c = ChurchChoir(**body.dict())
    db.add(c); db.commit(); db.refresh(c)
    return success_response("Choir registered", {"id": c.id})
