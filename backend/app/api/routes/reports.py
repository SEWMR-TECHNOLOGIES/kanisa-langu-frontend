# api/routes/reports.py
"""Reporting & data aggregation routes."""
from fastapi import APIRouter, Depends
from sqlalchemy.orm import Session
from sqlalchemy import func
from typing import Optional

from core.database import get_db
from models.finance import Revenue, Expense
from models.harambee import HarambeeContribution
from models.envelope import EnvelopeContribution
from models.operations import Attendance
from utils.response import success_response

router = APIRouter(prefix="/reports", tags=["Reports"])


@router.get("/financial-summary")
def financial_summary(head_parish_id: int, management_level: Optional[str] = None, db: Session = Depends(get_db)):
    rev_q = db.query(func.coalesce(func.sum(Revenue.amount), 0)).filter(Revenue.head_parish_id == head_parish_id)
    exp_q = db.query(func.coalesce(func.sum(Expense.amount), 0)).filter(Expense.head_parish_id == head_parish_id)
    if management_level:
        rev_q = rev_q.filter(Revenue.management_level == management_level)
        exp_q = exp_q.filter(Expense.management_level == management_level)

    total_rev = float(rev_q.scalar() or 0)
    total_exp = float(exp_q.scalar() or 0)
    return success_response(data={
        "total_revenue": total_rev,
        "total_expense": total_exp,
        "balance": total_rev - total_exp,
    })


@router.get("/envelope-summary")
def envelope_summary(head_parish_id: int, year: Optional[int] = None, db: Session = Depends(get_db)):
    q = db.query(func.coalesce(func.sum(EnvelopeContribution.amount), 0)).filter(
        EnvelopeContribution.head_parish_id == head_parish_id
    )
    if year:
        q = q.filter(func.extract("year", EnvelopeContribution.contribution_date) == year)
    return success_response(data={"total_contributions": float(q.scalar() or 0)})


@router.get("/harambee-summary")
def harambee_summary(harambee_id: int, db: Session = Depends(get_db)):
    total = db.query(func.coalesce(func.sum(HarambeeContribution.amount), 0)).filter(
        HarambeeContribution.harambee_id == harambee_id
    ).scalar()
    count = db.query(func.count(func.distinct(HarambeeContribution.member_id))).filter(
        HarambeeContribution.harambee_id == harambee_id
    ).scalar()
    return success_response(data={
        "total_contributions": float(total or 0),
        "unique_contributors": count or 0,
    })


@router.get("/attendance-summary")
def attendance_summary(head_parish_id: int, year: Optional[int] = None, db: Session = Depends(get_db)):
    q = db.query(
        func.sum(Attendance.male_attendance),
        func.sum(Attendance.female_attendance),
        func.sum(Attendance.children_attendance),
        func.count(Attendance.id),
    ).filter(Attendance.head_parish_id == head_parish_id)
    if year:
        q = q.filter(func.extract("year", Attendance.attendance_date) == year)
    male, female, children, count = q.first()
    return success_response(data={
        "total_male": int(male or 0),
        "total_female": int(female or 0),
        "total_children": int(children or 0),
        "total_records": int(count or 0),
    })
