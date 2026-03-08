# api/routes/harambee.py
"""Harambee (fundraising) routes — unified for all management levels."""
from fastapi import APIRouter, Depends, HTTPException
from pydantic import BaseModel
from sqlalchemy.orm import Session
from sqlalchemy import func
from typing import Optional, List
from datetime import date

from core.database import get_db
from models.harambee import (
    Harambee, HarambeeGroup, HarambeeGroupMember, HarambeeTarget,
    HarambeeContribution, HarambeeClass, HarambeeExclusion,
)
from utils.response import success_response

router = APIRouter(prefix="/harambee", tags=["Harambee"])


class HarambeeCreate(BaseModel):
    management_level: str
    head_parish_id: int
    sub_parish_id: Optional[int] = None
    community_id: Optional[int] = None
    group_id: Optional[int] = None
    account_id: int
    name: str
    description: str
    from_date: date
    to_date: date
    amount: float


class HarambeeContributionCreate(BaseModel):
    harambee_id: int
    member_id: int
    amount: float
    contribution_date: date
    head_parish_id: int
    payment_method: str = "Cash"


class HarambeeGroupCreate(BaseModel):
    harambee_id: int
    name: str
    target: float = 0


class HarambeeTargetCreate(BaseModel):
    harambee_id: int
    member_id: int
    target: float
    target_type: str = "individual"
    sub_parish_id: Optional[int] = None
    community_id: Optional[int] = None


@router.get("/")
def list_harambees(
    head_parish_id: int,
    management_level: Optional[str] = None,
    db: Session = Depends(get_db),
):
    q = db.query(Harambee).filter(Harambee.head_parish_id == head_parish_id, Harambee.is_active == True)
    if management_level:
        q = q.filter(Harambee.management_level == management_level)
    return success_response(data=[{
        "id": h.id, "name": h.name, "description": h.description,
        "from_date": str(h.from_date), "to_date": str(h.to_date),
        "amount": float(h.amount), "management_level": h.management_level,
    } for h in q.order_by(Harambee.from_date.desc()).all()])


@router.post("/")
def create_harambee(body: HarambeeCreate, db: Session = Depends(get_db)):
    if body.amount <= 0:
        raise HTTPException(400, "Amount must be greater than 0")
    if body.to_date <= body.from_date:
        raise HTTPException(400, "End date must be after start date")
    h = Harambee(**body.dict())
    db.add(h); db.commit(); db.refresh(h)
    return success_response("Harambee recorded", {"id": h.id})


@router.get("/{harambee_id}")
def get_harambee(harambee_id: int, db: Session = Depends(get_db)):
    h = db.query(Harambee).filter(Harambee.id == harambee_id).first()
    if not h:
        raise HTTPException(404, "Harambee not found")
    total_contrib = db.query(func.coalesce(func.sum(HarambeeContribution.amount), 0)).filter(
        HarambeeContribution.harambee_id == harambee_id
    ).scalar()
    return success_response(data={
        "id": h.id, "name": h.name, "description": h.description,
        "from_date": str(h.from_date), "to_date": str(h.to_date),
        "amount": float(h.amount), "total_contributions": float(total_contrib),
    })


@router.post("/contributions")
def record_contribution(body: HarambeeContributionCreate, db: Session = Depends(get_db)):
    if body.amount <= 0:
        raise HTTPException(400, "Amount must be greater than 0")

    from models.members import ChurchMember
    member = db.query(ChurchMember).filter(ChurchMember.id == body.member_id).first()
    if not member:
        raise HTTPException(404, "Member not found")

    contrib = HarambeeContribution(
        harambee_id=body.harambee_id, member_id=body.member_id,
        amount=body.amount, contribution_date=body.contribution_date,
        head_parish_id=body.head_parish_id,
        sub_parish_id=member.sub_parish_id,
        community_id=member.community_id,
        payment_method=body.payment_method,
    )
    db.add(contrib); db.commit(); db.refresh(contrib)
    return success_response("Contribution recorded", {"id": contrib.id})


@router.get("/contributions/{harambee_id}")
def list_contributions(harambee_id: int, db: Session = Depends(get_db)):
    rows = db.query(HarambeeContribution).filter(
        HarambeeContribution.harambee_id == harambee_id
    ).order_by(HarambeeContribution.contribution_date.desc()).all()
    return success_response(data=[{
        "id": c.id, "member_id": c.member_id, "amount": float(c.amount),
        "contribution_date": str(c.contribution_date), "payment_method": c.payment_method,
    } for c in rows])


# ── Groups ───────────────────────────────────────────────────
@router.post("/groups")
def create_harambee_group(body: HarambeeGroupCreate, db: Session = Depends(get_db)):
    g = HarambeeGroup(**body.dict())
    db.add(g); db.commit(); db.refresh(g)
    return success_response("Harambee group created", {"id": g.id})


# ── Targets ──────────────────────────────────────────────────
@router.post("/targets")
def set_harambee_target(body: HarambeeTargetCreate, db: Session = Depends(get_db)):
    existing = db.query(HarambeeTarget).filter(
        HarambeeTarget.harambee_id == body.harambee_id,
        HarambeeTarget.member_id == body.member_id,
    ).first()
    if existing:
        existing.target = body.target
        existing.target_type = body.target_type
    else:
        db.add(HarambeeTarget(**body.dict()))
    db.commit()
    return success_response("Target set")
