# api/routes/envelope.py
"""Envelope contribution and target routes."""
from fastapi import APIRouter, Depends, HTTPException
from pydantic import BaseModel
from sqlalchemy.orm import Session
from sqlalchemy import func
from typing import Optional
from datetime import date

from core.database import get_db
from models.envelope import EnvelopeTarget, EnvelopeContribution
from models.members import ChurchMember
from utils.response import success_response

router = APIRouter(prefix="/envelope", tags=["Envelope"])


class EnvelopeTargetCreate(BaseModel):
    member_id: int
    target: float
    from_date: date
    end_date: date


class EnvelopeContributionCreate(BaseModel):
    member_id: int
    amount: float
    contribution_date: date
    head_parish_id: int
    payment_method: str = "Cash"


@router.get("/targets")
def list_targets(head_parish_id: int, year: Optional[int] = None, db: Session = Depends(get_db)):
    q = db.query(EnvelopeTarget).join(ChurchMember).filter(ChurchMember.head_parish_id == head_parish_id)
    if year:
        q = q.filter(func.extract("year", EnvelopeTarget.from_date) == year)
    return success_response(data=[{
        "id": t.id, "member_id": t.member_id, "target": float(t.target),
        "from_date": str(t.from_date), "end_date": str(t.end_date),
    } for t in q.all()])


@router.post("/targets")
def set_envelope_target(body: EnvelopeTargetCreate, db: Session = Depends(get_db)):
    existing = db.query(EnvelopeTarget).filter(
        EnvelopeTarget.member_id == body.member_id,
        EnvelopeTarget.from_date == body.from_date,
        EnvelopeTarget.end_date == body.end_date,
    ).first()
    if existing:
        existing.target = body.target
    else:
        db.add(EnvelopeTarget(**body.dict()))
    db.commit()
    return success_response("Envelope target set")


@router.post("/contributions")
def record_contribution(body: EnvelopeContributionCreate, db: Session = Depends(get_db)):
    if body.amount <= 0:
        raise HTTPException(400, "Amount must be greater than 0")

    member = db.query(ChurchMember).filter(ChurchMember.id == body.member_id).first()
    if not member:
        raise HTTPException(404, "Member not found")

    contrib = EnvelopeContribution(
        member_id=body.member_id, amount=body.amount,
        contribution_date=body.contribution_date,
        head_parish_id=body.head_parish_id,
        sub_parish_id=member.sub_parish_id,
        community_id=member.community_id,
        payment_method=body.payment_method,
    )
    db.add(contrib); db.commit(); db.refresh(contrib)
    return success_response("Contribution recorded", {"id": contrib.id})


@router.get("/contributions")
def list_contributions(member_id: int, db: Session = Depends(get_db)):
    rows = db.query(EnvelopeContribution).filter(
        EnvelopeContribution.member_id == member_id,
    ).order_by(EnvelopeContribution.contribution_date.desc()).all()
    total = sum(float(r.amount) for r in rows)
    return success_response(data={
        "contributions": [{
            "id": c.id, "amount": float(c.amount),
            "contribution_date": str(c.contribution_date),
            "payment_method": c.payment_method,
        } for c in rows],
        "total": total,
    })
