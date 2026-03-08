# api/routes/sunday_service.py
"""Sunday service routes."""
from fastapi import APIRouter, Depends, HTTPException
from pydantic import BaseModel
from sqlalchemy.orm import Session
from typing import Optional
from datetime import date

from core.database import get_db
from models.sunday_service import SundayService, HeadParishServiceTime, HeadParishServicesCount
from utils.response import success_response

router = APIRouter(prefix="/sunday-services", tags=["Sunday Services"])


class SundayServiceCreate(BaseModel):
    head_parish_id: int
    service_date: date
    service_color_id: Optional[int] = None
    large_liturgy_page_number: Optional[int] = None
    small_liturgy_page_number: Optional[int] = None
    large_antiphony_page_number: Optional[int] = None
    small_antiphony_page_number: Optional[int] = None
    large_praise_page_number: Optional[int] = None
    small_praise_page_number: Optional[int] = None
    base_scripture_text: Optional[str] = None


@router.get("/")
def list_services(head_parish_id: int, db: Session = Depends(get_db)):
    rows = db.query(SundayService).filter(
        SundayService.head_parish_id == head_parish_id
    ).order_by(SundayService.service_date.desc()).limit(52).all()
    return success_response(data=[{
        "id": s.id, "service_date": str(s.service_date),
        "service_color_id": s.service_color_id,
        "base_scripture_text": s.base_scripture_text,
    } for s in rows])


@router.post("/")
def create_or_update_service(body: SundayServiceCreate, db: Session = Depends(get_db)):
    existing = db.query(SundayService).filter(
        SundayService.head_parish_id == body.head_parish_id,
        SundayService.service_date == body.service_date,
    ).first()

    if existing:
        for field, value in body.dict(exclude={"head_parish_id", "service_date"}).items():
            if value is not None:
                setattr(existing, field, value)
        db.commit()
        return success_response("Sunday service updated")
    else:
        s = SundayService(**body.dict())
        db.add(s); db.commit(); db.refresh(s)
        return success_response("Sunday service created", {"id": s.id})


@router.get("/{service_id}")
def get_service(service_id: int, db: Session = Depends(get_db)):
    s = db.query(SundayService).filter(SundayService.id == service_id).first()
    if not s:
        raise HTTPException(404, "Service not found")
    return success_response(data={
        "id": s.id, "service_date": str(s.service_date),
        "service_color_id": s.service_color_id,
        "large_liturgy_page_number": s.large_liturgy_page_number,
        "small_liturgy_page_number": s.small_liturgy_page_number,
        "base_scripture_text": s.base_scripture_text,
    })


# ── Service Times Config ────────────────────────────────────
class ServiceTimeCreate(BaseModel):
    head_parish_id: int
    service_number: int
    start_time: str
    end_time: Optional[str] = None

@router.post("/service-times")
def set_service_time(body: ServiceTimeCreate, db: Session = Depends(get_db)):
    from datetime import time as time_type
    start = time_type.fromisoformat(body.start_time)
    end = time_type.fromisoformat(body.end_time) if body.end_time else None

    existing = db.query(HeadParishServiceTime).filter(
        HeadParishServiceTime.head_parish_id == body.head_parish_id,
        HeadParishServiceTime.service_number == body.service_number,
    ).first()
    if existing:
        existing.start_time = start
        existing.end_time = end
    else:
        db.add(HeadParishServiceTime(
            head_parish_id=body.head_parish_id,
            service_number=body.service_number,
            start_time=start, end_time=end,
        ))
    db.commit()
    return success_response("Service time set")


# ── Services Count Config ───────────────────────────────────
class ServicesCountCreate(BaseModel):
    head_parish_id: int
    services_count: int

@router.post("/services-count")
def set_services_count(body: ServicesCountCreate, db: Session = Depends(get_db)):
    existing = db.query(HeadParishServicesCount).filter(
        HeadParishServicesCount.head_parish_id == body.head_parish_id
    ).first()
    if existing:
        existing.services_count = body.services_count
    else:
        db.add(HeadParishServicesCount(**body.dict()))
    db.commit()
    return success_response("Services count set")
