# api/routes/meetings.py
"""Meeting management routes."""
from fastapi import APIRouter, Depends, HTTPException
from pydantic import BaseModel
from sqlalchemy.orm import Session
from typing import Optional
from datetime import date

from core.database import get_db
from models.operations import Meeting, MeetingAgenda, MeetingMinutes, MeetingNotes
from utils.response import success_response

router = APIRouter(prefix="/meetings", tags=["Meetings"])


class MeetingCreate(BaseModel):
    head_parish_id: int
    title: str
    description: Optional[str] = None
    meeting_date: date
    meeting_time: str
    meeting_place: str


class AgendaCreate(BaseModel):
    meeting_id: int
    agenda_item: str
    sort_order: int = 0


class MinutesCreate(BaseModel):
    meeting_id: int
    content: str


@router.get("/")
def list_meetings(head_parish_id: int, db: Session = Depends(get_db)):
    rows = db.query(Meeting).filter(Meeting.head_parish_id == head_parish_id).order_by(Meeting.meeting_date.desc()).all()
    return success_response(data=[{
        "id": m.id, "title": m.title, "meeting_date": str(m.meeting_date),
        "meeting_time": str(m.meeting_time), "meeting_place": m.meeting_place,
    } for m in rows])


@router.post("/")
def create_meeting(body: MeetingCreate, db: Session = Depends(get_db)):
    if not body.title.strip():
        raise HTTPException(400, "Meeting title is required")
    from datetime import time as time_type
    meeting_time = time_type.fromisoformat(body.meeting_time)
    m = Meeting(
        head_parish_id=body.head_parish_id, title=body.title,
        description=body.description, meeting_date=body.meeting_date,
        meeting_time=meeting_time, meeting_place=body.meeting_place,
    )
    db.add(m); db.commit(); db.refresh(m)
    return success_response("Meeting created", {"id": m.id})


@router.post("/agendas")
def add_agenda(body: AgendaCreate, db: Session = Depends(get_db)):
    a = MeetingAgenda(**body.dict())
    db.add(a); db.commit()
    return success_response("Agenda added")


@router.post("/minutes")
def add_minutes(body: MinutesCreate, db: Session = Depends(get_db)):
    m = MeetingMinutes(**body.dict())
    db.add(m); db.commit()
    return success_response("Minutes added")
