# api/routes/reports.py
"""Reporting & data aggregation routes."""
from fastapi import APIRouter, Depends, Query
from sqlalchemy.orm import Session
from sqlalchemy import func, text
from typing import Optional
from datetime import date

from core.database import get_db
from models.finance import Revenue, Expense, BankAccount, RevenueStream, ExpenseGroup, ExpenseName, AnnualRevenueTarget, AnnualExpenseBudget
from models.harambee import Harambee, HarambeeContribution, HarambeeTarget, HarambeeClass, HarambeeDistribution
from models.envelope import EnvelopeTarget, EnvelopeContribution
from models.operations import Attendance
from models.members import ChurchMember
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


@router.get("/revenue-by-stream")
def revenue_by_stream(head_parish_id: int, year: Optional[int] = None, management_level: Optional[str] = None, db: Session = Depends(get_db)):
    from models.finance import RevenueStream
    sql = """
        SELECT rs.id, rs.name AS stream_name,
               COALESCE(SUM(r.amount), 0) AS total
        FROM revenue_streams rs
        LEFT JOIN revenues r ON r.revenue_stream_id = rs.id
            AND r.head_parish_id = :hpid
    """
    params = {"hpid": head_parish_id}
    if year:
        sql += " AND EXTRACT(YEAR FROM r.revenue_date) = :yr"
        params["yr"] = year
    if management_level:
        sql += " AND r.management_level = :ml"
        params["ml"] = management_level
    sql += " WHERE rs.entity_type = 'head_parish' AND rs.entity_id = :hpid GROUP BY rs.id, rs.name ORDER BY total DESC"
    from sqlalchemy import text
    rows = db.execute(text(sql), params).mappings().all()
    return success_response(data=[dict(r) for r in rows])


@router.get("/expense-by-group")
def expense_by_group(head_parish_id: int, year: Optional[int] = None, management_level: Optional[str] = None, db: Session = Depends(get_db)):
    from sqlalchemy import text
    sql = """
        SELECT eg.id, eg.name AS group_name,
               COALESCE(SUM(e.amount), 0) AS total
        FROM expense_groups eg
        LEFT JOIN expense_names en ON en.expense_group_id = eg.id
        LEFT JOIN expenses e ON e.expense_name_id = en.id AND e.head_parish_id = :hpid
    """
    params = {"hpid": head_parish_id}
    if year:
        sql += " AND EXTRACT(YEAR FROM e.expense_date) = :yr"
        params["yr"] = year
    if management_level:
        sql += " AND e.management_level = :ml"
        params["ml"] = management_level
    sql += " WHERE eg.head_parish_id = :hpid GROUP BY eg.id, eg.name ORDER BY total DESC"
    rows = db.execute(text(sql), params).mappings().all()
    return success_response(data=[dict(r) for r in rows])


@router.get("/monthly-revenue")
def monthly_revenue(head_parish_id: int, year: int, management_level: Optional[str] = None, db: Session = Depends(get_db)):
    from sqlalchemy import text
    sql = """
        SELECT EXTRACT(MONTH FROM revenue_date)::int AS month,
               COALESCE(SUM(amount), 0) AS total
        FROM revenues
        WHERE head_parish_id = :hpid AND EXTRACT(YEAR FROM revenue_date) = :yr
    """
    params = {"hpid": head_parish_id, "yr": year}
    if management_level:
        sql += " AND management_level = :ml"
        params["ml"] = management_level
    sql += " GROUP BY month ORDER BY month"
    rows = db.execute(text(sql), params).mappings().all()
    return success_response(data=[dict(r) for r in rows])


@router.get("/monthly-expense")
def monthly_expense(head_parish_id: int, year: int, management_level: Optional[str] = None, db: Session = Depends(get_db)):
    from sqlalchemy import text
    sql = """
        SELECT EXTRACT(MONTH FROM expense_date)::int AS month,
               COALESCE(SUM(amount), 0) AS total
        FROM expenses
        WHERE head_parish_id = :hpid AND EXTRACT(YEAR FROM expense_date) = :yr
    """
    params = {"hpid": head_parish_id, "yr": year}
    if management_level:
        sql += " AND management_level = :ml"
        params["ml"] = management_level
    sql += " GROUP BY month ORDER BY month"
    rows = db.execute(text(sql), params).mappings().all()
    return success_response(data=[dict(r) for r in rows])


@router.get("/member-stats")
def member_stats(head_parish_id: int, db: Session = Depends(get_db)):
    from models.members import ChurchMember
    total = db.query(func.count(ChurchMember.id)).filter(
        ChurchMember.head_parish_id == head_parish_id, ChurchMember.is_active == True
    ).scalar()
    male = db.query(func.count(ChurchMember.id)).filter(
        ChurchMember.head_parish_id == head_parish_id, ChurchMember.is_active == True, ChurchMember.gender == "Male"
    ).scalar()
    female = db.query(func.count(ChurchMember.id)).filter(
        ChurchMember.head_parish_id == head_parish_id, ChurchMember.is_active == True, ChurchMember.gender == "Female"
    ).scalar()
    excluded = db.query(func.count(ChurchMember.id)).filter(
        ChurchMember.head_parish_id == head_parish_id, ChurchMember.status == "Excluded"
    ).scalar()
    return success_response(data={
        "total": int(total or 0), "male": int(male or 0), "female": int(female or 0),
        "excluded": int(excluded or 0),
    })


@router.get("/harambee-by-class")
def harambee_by_class(harambee_id: int, db: Session = Depends(get_db)):
    """Harambee contributions grouped by contribution class."""
    from models.harambee import HarambeeClass
    from sqlalchemy import text
    classes = db.query(HarambeeClass).filter(HarambeeClass.harambee_id == harambee_id).order_by(HarambeeClass.min_amount).all()
    result = []
    for cls in classes:
        sql = """
            SELECT COUNT(DISTINCT hc.member_id) AS contributors,
                   COALESCE(SUM(hc.amount), 0) AS total
            FROM harambee_contributions hc
            WHERE hc.harambee_id = :hid
        """
        params = {"hid": harambee_id, "min": float(cls.min_amount)}
        sql += " AND hc.amount >= :min"
        if cls.max_amount:
            sql += " AND hc.amount <= :max"
            params["max"] = float(cls.max_amount)
        row = db.execute(text(sql), params).first()
        result.append({
            "class_name": cls.class_name,
            "min_amount": float(cls.min_amount),
            "max_amount": float(cls.max_amount) if cls.max_amount else None,
            "contributors": int(row[0] or 0),
            "total": float(row[1] or 0),
        })
    return success_response(data=result)


@router.get("/harambee-progress")
def harambee_progress(harambee_id: int, db: Session = Depends(get_db)):
    from models.harambee import Harambee
    h = db.query(Harambee).filter(Harambee.id == harambee_id).first()
    if not h:
        return success_response(data=None)
    total = float(db.query(func.coalesce(func.sum(HarambeeContribution.amount), 0)).filter(
        HarambeeContribution.harambee_id == harambee_id
    ).scalar())
    contributors = db.query(func.count(func.distinct(HarambeeContribution.member_id))).filter(
        HarambeeContribution.harambee_id == harambee_id
    ).scalar()
    return success_response(data={
        "harambee_id": h.id, "name": h.name, "target": float(h.amount),
        "total_contributions": total, "unique_contributors": int(contributors or 0),
        "percentage": round((total / float(h.amount)) * 100, 1) if h.amount else 0,
    })
