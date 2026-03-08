# api/routes/attendance.py
"""Attendance recording — unified for all management levels."""
from fastapi import APIRouter, Depends, HTTPException
from pydantic import BaseModel
from sqlalchemy.orm import Session
from typing import Optional
from datetime import date

from core.database import get_db
from models.operations import Attendance, AttendanceBenchmark
from utils.response import success_response

router = APIRouter(prefix="/attendance", tags=["Attendance"])


class AttendanceCreate(BaseModel):
    management_level: str
    event_title: str
    head_parish_id: int
    sub_parish_id: Optional[int] = None
    community_id: Optional[int] = None
    group_id: Optional[int] = None
    service_number: Optional[int] = None
    male_attendance: int = 0
    female_attendance: int = 0
    children_attendance: int = 0
    attendance_date: date


@router.get("/")
def list_attendance(head_parish_id: int, management_level: Optional[str] = None, db: Session = Depends(get_db)):
    q = db.query(Attendance).filter(Attendance.head_parish_id == head_parish_id)
    if management_level:
        q = q.filter(Attendance.management_level == management_level)
    rows = q.order_by(Attendance.attendance_date.desc()).limit(100).all()
    return success_response(data=[{
        "id": a.id, "event_title": a.event_title,
        "male": a.male_attendance, "female": a.female_attendance,
        "children": a.children_attendance,
        "total": a.male_attendance + a.female_attendance + a.children_attendance,
        "date": str(a.attendance_date),
    } for a in rows])


@router.post("/")
def record_attendance(body: AttendanceCreate, db: Session = Depends(get_db)):
    if not body.event_title.strip():
        raise HTTPException(400, "Event title is required")
    a = Attendance(**body.dict())
    db.add(a); db.commit(); db.refresh(a)
    return success_response("Attendance recorded", {"id": a.id})


class BenchmarkCreate(BaseModel):
    head_parish_id: int
    benchmark: int
    year: int

@router.post("/benchmarks")
def set_benchmark(body: BenchmarkCreate, db: Session = Depends(get_db)):
    existing = db.query(AttendanceBenchmark).filter(
        AttendanceBenchmark.head_parish_id == body.head_parish_id,
        AttendanceBenchmark.year == body.year,
    ).first()
    if existing:
        existing.benchmark = body.benchmark
    else:
        db.add(AttendanceBenchmark(**body.dict()))
    db.commit()
    return success_response("Benchmark set")
