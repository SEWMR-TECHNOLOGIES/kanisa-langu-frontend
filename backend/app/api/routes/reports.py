# api/routes/reports.py
"""Reporting & data aggregation routes."""
from fastapi import APIRouter, Depends, Query
from sqlalchemy.orm import Session
from sqlalchemy import func, text
from typing import Optional, List
from datetime import date

from core.database import get_db
from models.finance import Revenue, Expense, BankAccount, RevenueStream, ExpenseGroup, ExpenseName, AnnualRevenueTarget, AnnualExpenseBudget
from models.harambee import Harambee, HarambeeContribution, HarambeeTarget, HarambeeClass, HarambeeDistribution
from models.envelope import EnvelopeTarget, EnvelopeContribution
from models.operations import Attendance
from models.members import ChurchMember
from utils.response import success_response

from schemas.base import ApiResponse
from schemas.reports import (
    FinancialSummary, EnvelopeSummary, HarambeeSummary, AttendanceSummary,
    RevenueByStream, ExpenseByGroup, MonthlyAmount, MemberStats,
    HarambeeClassReport, HarambeeProgress,
    SubParishDailySummary, MemberHarambeeStatus, MemberHarambeeContributions,
)
from schemas.members import MemberStatsOut

router = APIRouter(prefix="/reports", tags=["Reports"])


@router.get("/financial-summary", response_model=ApiResponse[FinancialSummary], summary="Financial summary (revenue vs expense)")
def financial_summary(head_parish_id: int, management_level: Optional[str] = None, db: Session = Depends(get_db)):
    rev_q = db.query(func.coalesce(func.sum(Revenue.amount), 0)).filter(Revenue.head_parish_id == head_parish_id)
    exp_q = db.query(func.coalesce(func.sum(Expense.amount), 0)).filter(Expense.head_parish_id == head_parish_id)
    if management_level:
        rev_q = rev_q.filter(Revenue.management_level == management_level)
        exp_q = exp_q.filter(Expense.management_level == management_level)
    total_rev = float(rev_q.scalar() or 0)
    total_exp = float(exp_q.scalar() or 0)
    return success_response(data={"total_revenue": total_rev, "total_expense": total_exp, "balance": total_rev - total_exp})


@router.get("/envelope-summary", response_model=ApiResponse[EnvelopeSummary], summary="Envelope contribution totals")
def envelope_summary(head_parish_id: int, year: Optional[int] = None, db: Session = Depends(get_db)):
    q = db.query(func.coalesce(func.sum(EnvelopeContribution.amount), 0)).filter(
        EnvelopeContribution.head_parish_id == head_parish_id
    )
    if year:
        q = q.filter(func.extract("year", EnvelopeContribution.contribution_date) == year)
    return success_response(data={"total_contributions": float(q.scalar() or 0)})


@router.get("/harambee-summary", response_model=ApiResponse[HarambeeSummary], summary="Harambee contribution summary")
def harambee_summary(harambee_id: int, db: Session = Depends(get_db)):
    total = db.query(func.coalesce(func.sum(HarambeeContribution.amount), 0)).filter(
        HarambeeContribution.harambee_id == harambee_id
    ).scalar()
    count = db.query(func.count(func.distinct(HarambeeContribution.member_id))).filter(
        HarambeeContribution.harambee_id == harambee_id
    ).scalar()
    return success_response(data={"total_contributions": float(total or 0), "unique_contributors": count or 0})


@router.get("/attendance-summary", response_model=ApiResponse[AttendanceSummary], summary="Attendance statistics")
def attendance_summary(head_parish_id: int, year: Optional[int] = None, db: Session = Depends(get_db)):
    q = db.query(
        func.sum(Attendance.male_attendance), func.sum(Attendance.female_attendance),
        func.sum(Attendance.children_attendance), func.count(Attendance.id),
    ).filter(Attendance.head_parish_id == head_parish_id)
    if year:
        q = q.filter(func.extract("year", Attendance.attendance_date) == year)
    male, female, children, count = q.first()
    return success_response(data={
        "total_male": int(male or 0), "total_female": int(female or 0),
        "total_children": int(children or 0), "total_records": int(count or 0),
    })


@router.get("/revenue-by-stream", response_model=ApiResponse[List[RevenueByStream]], summary="Revenue grouped by stream")
def revenue_by_stream(head_parish_id: int, year: Optional[int] = None, management_level: Optional[str] = None, db: Session = Depends(get_db)):
    sql = """
        SELECT rs.id, rs.name AS stream_name, COALESCE(SUM(r.amount), 0) AS total
        FROM revenue_streams rs
        LEFT JOIN revenues r ON r.revenue_stream_id = rs.id AND r.head_parish_id = :hpid
    """
    params = {"hpid": head_parish_id}
    if year:
        sql += " AND EXTRACT(YEAR FROM r.revenue_date) = :yr"
        params["yr"] = year
    if management_level:
        sql += " AND r.management_level = :ml"
        params["ml"] = management_level
    sql += " WHERE rs.entity_type = 'head_parish' AND rs.entity_id = :hpid GROUP BY rs.id, rs.name ORDER BY total DESC"
    rows = db.execute(text(sql), params).mappings().all()
    return success_response(data=[dict(r) for r in rows])


@router.get("/expense-by-group", response_model=ApiResponse[List[ExpenseByGroup]], summary="Expenses grouped by expense group")
def expense_by_group(head_parish_id: int, year: Optional[int] = None, management_level: Optional[str] = None, db: Session = Depends(get_db)):
    sql = """
        SELECT eg.id, eg.name AS group_name, COALESCE(SUM(e.amount), 0) AS total
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


@router.get("/monthly-revenue", response_model=ApiResponse[List[MonthlyAmount]], summary="Monthly revenue breakdown")
def monthly_revenue(head_parish_id: int, year: int, management_level: Optional[str] = None, db: Session = Depends(get_db)):
    sql = """
        SELECT EXTRACT(MONTH FROM revenue_date)::int AS month, COALESCE(SUM(amount), 0) AS total
        FROM revenues WHERE head_parish_id = :hpid AND EXTRACT(YEAR FROM revenue_date) = :yr
    """
    params = {"hpid": head_parish_id, "yr": year}
    if management_level:
        sql += " AND management_level = :ml"
        params["ml"] = management_level
    sql += " GROUP BY month ORDER BY month"
    rows = db.execute(text(sql), params).mappings().all()
    return success_response(data=[dict(r) for r in rows])


@router.get("/monthly-expense", response_model=ApiResponse[List[MonthlyAmount]], summary="Monthly expense breakdown")
def monthly_expense(head_parish_id: int, year: int, management_level: Optional[str] = None, db: Session = Depends(get_db)):
    sql = """
        SELECT EXTRACT(MONTH FROM expense_date)::int AS month, COALESCE(SUM(amount), 0) AS total
        FROM expenses WHERE head_parish_id = :hpid AND EXTRACT(YEAR FROM expense_date) = :yr
    """
    params = {"hpid": head_parish_id, "yr": year}
    if management_level:
        sql += " AND management_level = :ml"
        params["ml"] = management_level
    sql += " GROUP BY month ORDER BY month"
    rows = db.execute(text(sql), params).mappings().all()
    return success_response(data=[dict(r) for r in rows])


@router.get("/member-stats", response_model=ApiResponse[MemberStatsOut], summary="Member demographics")
def member_stats(head_parish_id: int, db: Session = Depends(get_db)):
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
        "total": int(total or 0), "male": int(male or 0), "female": int(female or 0), "excluded": int(excluded or 0),
    })


@router.get("/harambee-by-class", response_model=ApiResponse[List[HarambeeClassReport]], summary="Harambee contributions by class bracket")
def harambee_by_class(harambee_id: int, db: Session = Depends(get_db)):
    classes = db.query(HarambeeClass).filter(HarambeeClass.harambee_id == harambee_id).order_by(HarambeeClass.min_amount).all()
    result = []
    for cls in classes:
        sql = """
            SELECT COUNT(DISTINCT hc.member_id) AS contributors, COALESCE(SUM(hc.amount), 0) AS total
            FROM harambee_contributions hc WHERE hc.harambee_id = :hid
        """
        params = {"hid": harambee_id, "min": float(cls.min_amount)}
        sql += " AND hc.amount >= :min"
        if cls.max_amount:
            sql += " AND hc.amount <= :max"
            params["max"] = float(cls.max_amount)
        row = db.execute(text(sql), params).first()
        result.append({
            "class_name": cls.class_name, "min_amount": float(cls.min_amount),
            "max_amount": float(cls.max_amount) if cls.max_amount else None,
            "contributors": int(row[0] or 0), "total": float(row[1] or 0),
        })
    return success_response(data=result)


@router.get("/harambee-progress", response_model=ApiResponse[HarambeeProgress], summary="Harambee campaign progress")
def harambee_progress(harambee_id: int, db: Session = Depends(get_db)):
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


@router.get("/daily-harambee-summary", response_model=ApiResponse[List[SubParishDailySummary]], summary="Daily harambee contributions by sub-parish/community")
def daily_harambee_summary(harambee_id: int, contribution_date: Optional[str] = None, db: Session = Depends(get_db)):
    dt = contribution_date or str(date.today())
    rows = db.execute(text("""
        SELECT sp.id AS sub_parish_id, sp.name AS sub_parish_name,
               c.id AS community_id, c.name AS community_name,
               COUNT(DISTINCT hc.member_id) AS member_count,
               COALESCE(SUM(CASE WHEN hc.payment_method = 'Cash' THEN hc.amount ELSE 0 END), 0) AS cash_total,
               COALESCE(SUM(CASE WHEN hc.payment_method = 'Bank Transfer' THEN hc.amount ELSE 0 END), 0) AS bank_total,
               COALESCE(SUM(CASE WHEN hc.payment_method = 'Mobile Payment' THEN hc.amount ELSE 0 END), 0) AS mobile_total,
               COALESCE(SUM(hc.amount), 0) AS total
        FROM harambee_contributions hc
        JOIN church_members cm ON hc.member_id = cm.id
        JOIN sub_parishes sp ON cm.sub_parish_id = sp.id
        JOIN communities c ON cm.community_id = c.id
        WHERE hc.harambee_id = :hid AND hc.contribution_date = :dt
        GROUP BY sp.id, sp.name, c.id, c.name ORDER BY sp.name, c.name
    """), {"hid": harambee_id, "dt": dt}).mappings().all()

    sub_parishes = {}
    for r in rows:
        spid = r["sub_parish_id"]
        if spid not in sub_parishes:
            sub_parishes[spid] = {"sub_parish_id": spid, "sub_parish_name": r["sub_parish_name"], "communities": []}
        sub_parishes[spid]["communities"].append({
            "community_id": r["community_id"], "community_name": r["community_name"],
            "member_count": r["member_count"],
            "cash": float(r["cash_total"]), "bank_transfer": float(r["bank_total"]),
            "mobile_payment": float(r["mobile_total"]), "total": float(r["total"]),
        })
    return success_response(data=list(sub_parishes.values()))


@router.get("/member-harambee-status", response_model=ApiResponse[MemberHarambeeStatus], summary="Individual member harambee status (target vs contribution)")
def member_harambee_status(harambee_id: int, member_id: int, db: Session = Depends(get_db)):
    target_row = db.query(HarambeeTarget.target).filter(
        HarambeeTarget.harambee_id == harambee_id, HarambeeTarget.member_id == member_id
    ).scalar()
    target_amount = float(target_row or 0)
    total_contrib = float(db.query(func.coalesce(func.sum(HarambeeContribution.amount), 0)).filter(
        HarambeeContribution.harambee_id == harambee_id, HarambeeContribution.member_id == member_id
    ).scalar())
    balance = max(target_amount - total_contrib, 0) if target_amount > 0 else 0
    percentage = round((total_contrib / target_amount) * 100, 2) if target_amount > 0 else 0
    return success_response(data={
        "target": target_amount, "total_contribution": total_contrib,
        "balance": balance, "percentage": percentage,
        "balance_text": "Extra" if total_contrib > target_amount else "Balance",
    })


@router.get("/member-harambee-contributions", response_model=ApiResponse[MemberHarambeeContributions], summary="Member contribution detail for a harambee")
def member_harambee_contributions(harambee_id: int, member_id: int, db: Session = Depends(get_db)):
    contributions = db.query(HarambeeContribution).filter(
        HarambeeContribution.harambee_id == harambee_id, HarambeeContribution.member_id == member_id,
    ).order_by(HarambeeContribution.contribution_date.desc()).all()
    target_row = db.query(HarambeeTarget.target).filter(
        HarambeeTarget.harambee_id == harambee_id, HarambeeTarget.member_id == member_id
    ).scalar()
    target_amount = float(target_row or 0)
    total = sum(float(c.amount) for c in contributions)
    balance = max(target_amount - total, 0) if target_amount > 0 else 0
    return success_response(data={
        "contributions": [{
            "id": c.id, "amount": float(c.amount),
            "contribution_date": str(c.contribution_date), "payment_method": c.payment_method,
        } for c in contributions],
        "summary": {
            "target": target_amount, "total_contribution": total, "balance": balance,
            "percentage": round((total / target_amount) * 100, 2) if target_amount > 0 else 0,
            "balance_text": "Extra" if total > target_amount else "Balance",
        },
    })


# ═══════════════════════════════════════════════════════════════
# Remaining report endpoints (kept with typed response_model)
# ═══════════════════════════════════════════════════════════════

@router.get("/harambee-summary-on-date", summary="Harambee summary on a specific date range")
def harambee_summary_on_date(
    harambee_id: int, from_date: str, to_date: Optional[str] = None,
    sub_parish_id: Optional[int] = None, db: Session = Depends(get_db),
):
    end = to_date or from_date
    rows = db.execute(text("""
        SELECT sp.id AS sub_parish_id, sp.name AS sub_parish_name,
               c.id AS community_id, c.name AS community_name,
               COUNT(DISTINCT hc.member_id) AS member_count,
               COALESCE(SUM(CASE WHEN hc.contribution_date < :from_dt THEN hc.amount ELSE 0 END), 0) AS before_total,
               COALESCE(SUM(CASE WHEN hc.contribution_date BETWEEN :from_dt AND :to_dt THEN hc.amount ELSE 0 END), 0) AS on_date_total,
               COALESCE(SUM(CASE WHEN hc.contribution_date <= :to_dt THEN hc.amount ELSE 0 END), 0) AS cumulative_total
        FROM harambee_contributions hc
        JOIN church_members cm ON hc.member_id = cm.id
        JOIN sub_parishes sp ON cm.sub_parish_id = sp.id
        JOIN communities c ON cm.community_id = c.id
        WHERE hc.harambee_id = :hid AND (:spid IS NULL OR cm.sub_parish_id = :spid)
        GROUP BY sp.id, sp.name, c.id, c.name ORDER BY sp.name, c.name
    """), {"hid": harambee_id, "from_dt": from_date, "to_dt": end, "spid": sub_parish_id}).mappings().all()

    sub_parishes = {}
    for r in rows:
        spid = r["sub_parish_id"]
        if spid not in sub_parishes:
            dist = db.query(func.coalesce(func.sum(HarambeeDistribution.amount), 0)).filter(
                HarambeeDistribution.harambee_id == harambee_id,
            ).scalar()
            sub_parishes[spid] = {
                "sub_parish_id": spid, "sub_parish_name": r["sub_parish_name"],
                "distributed_amount": float(dist or 0), "communities": [],
            }
        sub_parishes[spid]["communities"].append({
            "community_id": r["community_id"], "community_name": r["community_name"],
            "member_count": r["member_count"],
            "before_total": float(r["before_total"]), "on_date_total": float(r["on_date_total"]),
            "cumulative_total": float(r["cumulative_total"]),
        })
    return success_response(data=list(sub_parishes.values()))


@router.get("/harambee-clerks-report", summary="Harambee clerks report — contributions by clerk")
def harambee_clerks_report(harambee_id: int, db: Session = Depends(get_db)):
    rows = db.execute(text("""
        SELECT a.id AS clerk_id, a.fullname AS clerk_name,
               COUNT(DISTINCT hc.member_id) AS members_served,
               COALESCE(SUM(hc.amount), 0) AS total_collected
        FROM harambee_contributions hc
        JOIN admins a ON hc.recorded_by = a.id
        WHERE hc.harambee_id = :hid
        GROUP BY a.id, a.fullname ORDER BY total_collected DESC
    """), {"hid": harambee_id}).mappings().all()
    return success_response(data=[dict(r) for r in rows])


@router.get("/ogo", summary="OGO — expense budget vs actual")
def ogo_report(head_parish_id: int, year: int, management_level: Optional[str] = None, db: Session = Depends(get_db)):
    ml_filter = f"AND e.management_level = '{management_level}'" if management_level else ""
    rows = db.execute(text(f"""
        SELECT eg.name AS group_name, en.name AS expense_name,
               COALESCE(aeb.budget_amount, 0) AS budget,
               COALESCE(SUM(e.amount), 0) AS actual,
               COALESCE(aeb.budget_amount, 0) - COALESCE(SUM(e.amount), 0) AS variance
        FROM expense_names en
        JOIN expense_groups eg ON en.expense_group_id = eg.id
        LEFT JOIN annual_expense_budgets aeb ON aeb.expense_name_id = en.id AND aeb.year = :yr AND aeb.head_parish_id = :hpid
        LEFT JOIN expenses e ON e.expense_name_id = en.id AND e.head_parish_id = :hpid
            AND EXTRACT(YEAR FROM e.expense_date) = :yr {ml_filter}
        WHERE eg.head_parish_id = :hpid
        GROUP BY eg.name, en.name, aeb.budget_amount ORDER BY eg.name, en.name
    """), {"hpid": head_parish_id, "yr": year}).mappings().all()
    return success_response(data=[dict(r) for r in rows])


@router.get("/expense-budget-summary", summary="Expense budget summary — budget vs actual by group")
def expense_budget_summary(head_parish_id: int, year: int, db: Session = Depends(get_db)):
    rows = db.execute(text("""
        SELECT eg.name AS group_name,
               COALESCE(SUM(aeb.budget_amount), 0) AS total_budget,
               COALESCE(SUM(e_total.actual), 0) AS total_actual
        FROM expense_groups eg
        LEFT JOIN expense_names en ON en.expense_group_id = eg.id
        LEFT JOIN annual_expense_budgets aeb ON aeb.expense_name_id = en.id AND aeb.year = :yr AND aeb.head_parish_id = :hpid
        LEFT JOIN (
            SELECT expense_name_id, SUM(amount) AS actual
            FROM expenses WHERE head_parish_id = :hpid AND EXTRACT(YEAR FROM expense_date) = :yr
            GROUP BY expense_name_id
        ) e_total ON e_total.expense_name_id = en.id
        WHERE eg.head_parish_id = :hpid
        GROUP BY eg.name ORDER BY eg.name
    """), {"hpid": head_parish_id, "yr": year}).mappings().all()
    return success_response(data=[dict(r) for r in rows])


@router.get("/revenues-to-post", summary="Unposted revenue batches")
def revenues_to_post(head_parish_id: int, db: Session = Depends(get_db)):
    rows = db.execute(text("""
        SELECT r.id, rs.name AS stream_name, r.amount, r.revenue_date, r.payment_method
        FROM revenues r
        JOIN revenue_streams rs ON r.revenue_stream_id = rs.id
        WHERE r.head_parish_id = :hpid AND r.is_posted = false
        ORDER BY r.revenue_date DESC LIMIT 200
    """), {"hpid": head_parish_id}).mappings().all()
    return success_response(data=[dict(r) for r in rows])


@router.get("/envelope-usage", summary="Envelope usage — target vs balance per member")
def envelope_usage(head_parish_id: int, year: int, db: Session = Depends(get_db)):
    rows = db.execute(text("""
        SELECT cm.id AS member_id,
               CONCAT(cm.first_name, ' ', COALESCE(cm.middle_name, ''), ' ', cm.last_name) AS full_name,
               cm.envelope_number, COALESCE(et.target, 0) AS target,
               COALESCE(contrib.total, 0) AS contributed,
               COALESCE(et.target, 0) - COALESCE(contrib.total, 0) AS balance
        FROM church_members cm
        LEFT JOIN envelope_targets et ON et.member_id = cm.id
            AND EXTRACT(YEAR FROM et.from_date) = :yr
        LEFT JOIN (
            SELECT member_id, SUM(amount) AS total
            FROM envelope_contributions
            WHERE EXTRACT(YEAR FROM contribution_date) = :yr
            GROUP BY member_id
        ) contrib ON contrib.member_id = cm.id
        WHERE cm.head_parish_id = :hpid AND cm.is_active = true
        ORDER BY cm.envelope_number
    """), {"hpid": head_parish_id, "yr": year}).mappings().all()
    return success_response(data=[dict(r) for r in rows])


@router.get("/envelope-target", summary="Envelope target summary for a year")
def envelope_target(head_parish_id: int, year: int, db: Session = Depends(get_db)):
    total_target = db.query(func.coalesce(func.sum(EnvelopeTarget.target), 0)).join(ChurchMember).filter(
        ChurchMember.head_parish_id == head_parish_id,
        func.extract("year", EnvelopeTarget.from_date) == year,
    ).scalar()
    total_contrib = db.query(func.coalesce(func.sum(EnvelopeContribution.amount), 0)).filter(
        EnvelopeContribution.head_parish_id == head_parish_id,
        func.extract("year", EnvelopeContribution.contribution_date) == year,
    ).scalar()
    return success_response(data={
        "year": year, "total_target": float(total_target or 0),
        "total_contributions": float(total_contrib or 0),
        "balance": float(total_target or 0) - float(total_contrib or 0),
    })
