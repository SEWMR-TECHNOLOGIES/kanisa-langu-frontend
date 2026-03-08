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


# ═══════════════════════════════════════════════════════════════
# DAILY HARAMBEE SUMMARY (by sub-parish/community)
# Replaces: daily_harambee_summary.php
# ═══════════════════════════════════════════════════════════════
@router.get("/daily-harambee-summary")
def daily_harambee_summary(
    harambee_id: int,
    contribution_date: Optional[str] = None,
    db: Session = Depends(get_db),
):
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
        GROUP BY sp.id, sp.name, c.id, c.name
        ORDER BY sp.name, c.name
    """), {"hid": harambee_id, "dt": dt}).mappings().all()

    # Group by sub-parish
    sub_parishes = {}
    for r in rows:
        spid = r["sub_parish_id"]
        if spid not in sub_parishes:
            sub_parishes[spid] = {
                "sub_parish_id": spid, "sub_parish_name": r["sub_parish_name"],
                "communities": [],
            }
        sub_parishes[spid]["communities"].append({
            "community_id": r["community_id"], "community_name": r["community_name"],
            "member_count": r["member_count"],
            "cash": float(r["cash_total"]), "bank_transfer": float(r["bank_total"]),
            "mobile_payment": float(r["mobile_total"]), "total": float(r["total"]),
        })
    return success_response(data=list(sub_parishes.values()))


# ═══════════════════════════════════════════════════════════════
# MEMBER HARAMBEE STATUS
# Replaces: get_member_harambee_status.php
# ═══════════════════════════════════════════════════════════════
@router.get("/member-harambee-status")
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


# ═══════════════════════════════════════════════════════════════
# MEMBER HARAMBEE CONTRIBUTION DETAIL
# Replaces: get_member_harambee_contribution_summary.php
# ═══════════════════════════════════════════════════════════════
@router.get("/member-harambee-contributions")
def member_harambee_contributions(harambee_id: int, member_id: int, db: Session = Depends(get_db)):
    contributions = db.query(HarambeeContribution).filter(
        HarambeeContribution.harambee_id == harambee_id,
        HarambeeContribution.member_id == member_id,
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
            "target": target_amount, "total_contribution": total,
            "balance": balance,
            "percentage": round((total / target_amount) * 100, 2) if target_amount > 0 else 0,
        },
    })


# ═══════════════════════════════════════════════════════════════
# MEMBER HARAMBEE SUMMARY ON DATE
# Replaces: member_harambee_summary_on_date.php (complex grouped report)
# ═══════════════════════════════════════════════════════════════
@router.get("/harambee-summary-on-date")
def harambee_summary_on_date(
    harambee_id: int,
    from_date: str,
    to_date: Optional[str] = None,
    sub_parish_id: Optional[int] = None,
    db: Session = Depends(get_db),
):
    end = to_date or from_date
    # Get contributions on date grouped by sub-parish/community
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
        WHERE hc.harambee_id = :hid
        AND (:spid IS NULL OR cm.sub_parish_id = :spid)
        GROUP BY sp.id, sp.name, c.id, c.name
        ORDER BY sp.name, c.name
    """), {"hid": harambee_id, "from_dt": from_date, "to_dt": end, "spid": sub_parish_id}).mappings().all()

    sub_parishes = {}
    for r in rows:
        spid = r["sub_parish_id"]
        if spid not in sub_parishes:
            # Get distributed amount for sub-parish
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
            "total_before_date": float(r["before_total"]),
            "total_on_date": float(r["on_date_total"]),
            "total_contributed_up_to_date": float(r["cumulative_total"]),
        })
    return success_response(data=list(sub_parishes.values()))


# ═══════════════════════════════════════════════════════════════
# REVENUES TO POST TO BANK
# Replaces: revenues_to_post_to_bank.php
# ═══════════════════════════════════════════════════════════════
@router.get("/revenues-to-post")
def revenues_to_post_to_bank(
    head_parish_id: int,
    management_level: Optional[str] = None,
    db: Session = Depends(get_db),
):
    q = db.query(Revenue).filter(
        Revenue.head_parish_id == head_parish_id,
        Revenue.is_posted_to_bank == False,
    )
    if management_level:
        q = q.filter(Revenue.management_level == management_level)
    rows = q.order_by(Revenue.revenue_date).all()

    # Group by revenue_stream + date
    grouped = {}
    for r in rows:
        key = f"{r.revenue_stream_id}_{r.revenue_date}"
        if key not in grouped:
            stream = db.query(RevenueStream).filter(RevenueStream.id == r.revenue_stream_id).first()
            grouped[key] = {
                "revenue_ids": [], "revenue_stream_id": r.revenue_stream_id,
                "revenue_stream_name": stream.name if stream else "",
                "account_id": stream.account_id if stream else None,
                "revenue_date": str(r.revenue_date), "total_amount": 0,
                "management_level": r.management_level,
            }
        grouped[key]["revenue_ids"].append(r.id)
        grouped[key]["total_amount"] += float(r.amount)
    return success_response(data=list(grouped.values()))


# ═══════════════════════════════════════════════════════════════
# EXPENSE BUDGET SUMMARY (budget vs actual by expense name)
# Replaces: expense_name_budget_summary.php
# ═══════════════════════════════════════════════════════════════
@router.get("/expense-budget-summary")
def expense_budget_summary(
    head_parish_id: int,
    expense_name_id: int,
    as_of_date: Optional[str] = None,
    management_level: str = "head_parish",
    db: Session = Depends(get_db),
):
    dt = as_of_date or str(date.today())
    year = int(dt[:4])

    budget = db.query(AnnualExpenseBudget).filter(
        AnnualExpenseBudget.head_parish_id == head_parish_id,
        AnnualExpenseBudget.expense_name_id == expense_name_id,
        AnnualExpenseBudget.year == year,
    ).first()
    budget_amount = float(budget.budget_amount) if budget else 0

    total_spent = float(db.query(func.coalesce(func.sum(Expense.amount), 0)).filter(
        Expense.head_parish_id == head_parish_id,
        Expense.expense_name_id == expense_name_id,
        Expense.management_level == management_level,
        Expense.expense_date <= dt,
    ).scalar())

    return success_response(data={
        "expense_name_id": expense_name_id, "year": year,
        "budget": budget_amount, "total_spent": total_spent,
        "balance": budget_amount - total_spent,
        "percentage": round((total_spent / budget_amount) * 100, 1) if budget_amount else 0,
    })


# ═══════════════════════════════════════════════════════════════
# OGO REPORT (Expense Budget vs Actual — full summary)
# Replaces: generate_ogo_report.php
# ═══════════════════════════════════════════════════════════════
@router.get("/ogo")
def ogo_report(
    head_parish_id: int,
    account_id: int,
    year: int,
    management_level: str = "head_parish",
    order_by: str = "name",
    order_dir: str = "asc",
    db: Session = Depends(get_db),
):
    rows = db.execute(text("""
        SELECT en.id AS expense_name_id, en.name AS expense_name,
               eg.name AS group_name,
               COALESCE(aeb.budget_amount, 0) AS budget,
               COALESCE(SUM(e.amount), 0) AS total_expense
        FROM expense_names en
        JOIN expense_groups eg ON en.expense_group_id = eg.id
        LEFT JOIN annual_expense_budgets aeb
            ON aeb.expense_name_id = en.id AND aeb.head_parish_id = :hpid AND aeb.year = :yr
        LEFT JOIN expenses e
            ON e.expense_name_id = en.id AND e.head_parish_id = :hpid
            AND EXTRACT(YEAR FROM e.expense_date) = :yr
            AND e.management_level = :ml
        WHERE eg.head_parish_id = :hpid AND eg.management_level = :ml
        GROUP BY en.id, en.name, eg.name, aeb.budget_amount
        ORDER BY eg.name, en.name
    """), {"hpid": head_parish_id, "yr": year, "ml": management_level}).mappings().all()

    result = []
    for r in rows:
        budget = float(r["budget"])
        spent = float(r["total_expense"])
        result.append({
            "expense_name_id": r["expense_name_id"],
            "expense_name": r["expense_name"],
            "group_name": r["group_name"],
            "budget": budget, "total_expense": spent,
            "balance": budget - spent,
            "percentage": round((spent / budget) * 100, 1) if budget else 0,
        })
    return success_response(data=result)


# ═══════════════════════════════════════════════════════════════
# ENVELOPE USAGE REPORT
# Replaces: envelope_usage.php
# ═══════════════════════════════════════════════════════════════
@router.get("/envelope-usage")
def envelope_usage(
    head_parish_id: int,
    as_of_date: Optional[str] = None,
    year: Optional[int] = None,
    db: Session = Depends(get_db),
):
    yr = year or date.today().year
    dt = as_of_date or str(date.today())

    rows = db.execute(text("""
        SELECT cm.id AS member_id,
               CONCAT(cm.first_name, ' ', COALESCE(cm.middle_name, ''), ' ', cm.last_name) AS full_name,
               cm.envelope_number,
               sp.name AS sub_parish_name, co.name AS community_name,
               COALESCE(et.target, 0) AS target,
               COALESCE(SUM(ec.amount), 0) AS total_contributed
        FROM church_members cm
        JOIN sub_parishes sp ON cm.sub_parish_id = sp.id
        JOIN communities co ON cm.community_id = co.id
        LEFT JOIN envelope_targets et ON et.member_id = cm.id
            AND EXTRACT(YEAR FROM et.from_date) = :yr
        LEFT JOIN envelope_contributions ec ON ec.member_id = cm.id
            AND ec.contribution_date <= :dt
            AND EXTRACT(YEAR FROM ec.contribution_date) = :yr
        WHERE cm.head_parish_id = :hpid AND cm.is_active = true
        GROUP BY cm.id, cm.first_name, cm.middle_name, cm.last_name,
                 cm.envelope_number, sp.name, co.name, et.target
        HAVING COALESCE(et.target, 0) > 0
        ORDER BY cm.envelope_number
    """), {"hpid": head_parish_id, "yr": yr, "dt": dt}).mappings().all()

    result = []
    for r in rows:
        target = float(r["target"])
        contributed = float(r["total_contributed"])
        result.append({
            "member_id": r["member_id"], "full_name": r["full_name"],
            "envelope_number": r["envelope_number"],
            "sub_parish": r["sub_parish_name"], "community": r["community_name"],
            "target": target, "contributed": contributed,
            "balance": target - contributed,
            "percentage": round((contributed / target) * 100, 1) if target else 0,
        })
    return success_response(data=result)


# ═══════════════════════════════════════════════════════════════
# ENVELOPE MEMBER TARGET
# Replaces: get_envelope_target_amount.php
# ═══════════════════════════════════════════════════════════════
@router.get("/envelope-target")
def get_envelope_target(member_id: int, year: Optional[int] = None, db: Session = Depends(get_db)):
    yr = year or date.today().year
    target = db.query(EnvelopeTarget).filter(
        EnvelopeTarget.member_id == member_id,
        func.extract("year", EnvelopeTarget.from_date) == yr,
    ).first()
    return success_response(data={
        "target": float(target.target) if target else 0,
        "year": yr,
    })


# ═══════════════════════════════════════════════════════════════
# HARAMBEE CONTRIBUTION REPORT LINKS (download URLs)
# Replaces: download_harambee_report_*.php, download_harambee_summary_*.php
# ═══════════════════════════════════════════════════════════════
@router.get("/harambee-contribution-by-community")
def harambee_contribution_by_community(harambee_id: int, db: Session = Depends(get_db)):
    rows = db.execute(text("""
        SELECT sp.id AS sub_parish_id, sp.name AS sub_parish_name,
               c.id AS community_id, c.name AS community_name,
               COUNT(DISTINCT hc.member_id) AS contributors,
               COALESCE(SUM(hc.amount), 0) AS total
        FROM harambee_contributions hc
        JOIN church_members cm ON hc.member_id = cm.id
        JOIN sub_parishes sp ON cm.sub_parish_id = sp.id
        JOIN communities c ON cm.community_id = c.id
        WHERE hc.harambee_id = :hid
        GROUP BY sp.id, sp.name, c.id, c.name
        ORDER BY sp.name, c.name
    """), {"hid": harambee_id}).mappings().all()
    return success_response(data=[dict(r) for r in rows])


@router.get("/harambee-non-participants")
def harambee_non_participants(harambee_id: int, head_parish_id: int, db: Session = Depends(get_db)):
    rows = db.execute(text("""
        SELECT cm.id AS member_id,
               CONCAT(cm.first_name, ' ', COALESCE(cm.middle_name, ''), ' ', cm.last_name) AS full_name,
               cm.envelope_number, sp.name AS sub_parish_name, co.name AS community_name
        FROM church_members cm
        JOIN sub_parishes sp ON cm.sub_parish_id = sp.id
        JOIN communities co ON cm.community_id = co.id
        WHERE cm.head_parish_id = :hpid AND cm.is_active = true
        AND cm.id NOT IN (
            SELECT DISTINCT member_id FROM harambee_contributions WHERE harambee_id = :hid
        )
        AND cm.id NOT IN (
            SELECT DISTINCT member_id FROM harambee_exclusions WHERE harambee_id = :hid
        )
        ORDER BY sp.name, co.name, cm.first_name
    """), {"hpid": head_parish_id, "hid": harambee_id}).mappings().all()
    return success_response(data=[dict(r) for r in rows])


@router.get("/harambee-clerks-report")
def harambee_clerks_report(harambee_id: int, db: Session = Depends(get_db)):
    """Summary by sub-parish for clerks."""
    rows = db.execute(text("""
        SELECT sp.id AS sub_parish_id, sp.name AS sub_parish_name,
               COUNT(DISTINCT hc.member_id) AS contributors,
               COALESCE(SUM(hc.amount), 0) AS total
        FROM harambee_contributions hc
        JOIN church_members cm ON hc.member_id = cm.id
        JOIN sub_parishes sp ON cm.sub_parish_id = sp.id
        WHERE hc.harambee_id = :hid
        GROUP BY sp.id, sp.name
        ORDER BY sp.name
    """), {"hid": harambee_id}).mappings().all()
    return success_response(data=[dict(r) for r in rows])
