# api/routes/data.py
"""Data/reference lookup routes.
Replaces legacy/api/data/ files: titles, occupations, colors, regions, districts, banks,
church_members, church_leaders, church_choirs, church_roles, harambee_groups,
meetings, expense_groups, revenue_streams, praise_songs, harambee_details,
attendance_benchmark, envelope_targets, debits, etc."""

from fastapi import APIRouter, Depends, Query
from sqlalchemy.orm import Session
from sqlalchemy import text, func
from typing import Optional
from datetime import date

from core.database import get_db
from models.banking import BankPosting, BankClosingBalance
from models.config import SmsApiConfig, RevenueGroupModel, ProgramRevenueMap
from models.finance import BankAccount, RevenueStream, ExpenseGroup, ExpenseName
from models.harambee import Harambee, HarambeeGroup, HarambeeClass, HarambeeContribution, HarambeeTarget
from models.members import ChurchMember, ChurchLeader, ChurchChoir, MemberExclusion
from models.operations import Meeting, Attendance, AttendanceBenchmark
from models.envelope import EnvelopeTarget
from models.misc import HeadParishDebit, UnitOfMeasure
from utils.response import success_response

router = APIRouter(prefix="/data", tags=["Data & Reference"])


# ── Reference tables ──────────────────────────────────────────
@router.get("/titles")
def list_titles(db: Session = Depends(get_db)):
    rows = db.execute(text("SELECT id, name FROM titles ORDER BY name")).mappings().all()
    return success_response(data=[dict(r) for r in rows])

@router.get("/occupations")
def list_occupations(db: Session = Depends(get_db)):
    rows = db.execute(text("SELECT id, name FROM occupations ORDER BY name")).mappings().all()
    return success_response(data=[dict(r) for r in rows])

@router.get("/colors")
def list_colors(db: Session = Depends(get_db)):
    rows = db.execute(text("SELECT id, name, code FROM service_colors ORDER BY name")).mappings().all()
    return success_response(data=[dict(r) for r in rows])

@router.get("/regions")
def list_regions(db: Session = Depends(get_db)):
    rows = db.execute(text("SELECT id, name FROM regions ORDER BY name")).mappings().all()
    return success_response(data=[dict(r) for r in rows])

@router.get("/districts")
def list_districts(region_id: Optional[int] = None, db: Session = Depends(get_db)):
    sql = "SELECT id, name, region_id FROM districts"
    params = {}
    if region_id:
        sql += " WHERE region_id = :rid"
        params["rid"] = region_id
    rows = db.execute(text(sql + " ORDER BY name"), params).mappings().all()
    return success_response(data=[dict(r) for r in rows])

@router.get("/banks")
def list_banks(db: Session = Depends(get_db)):
    rows = db.execute(text("SELECT id, name FROM banks ORDER BY name")).mappings().all()
    return success_response(data=[dict(r) for r in rows])

@router.get("/church-roles")
def list_church_roles(db: Session = Depends(get_db)):
    rows = db.execute(text("SELECT id, name FROM church_roles ORDER BY name")).mappings().all()
    return success_response(data=[dict(r) for r in rows])

@router.get("/praise-songs")
def list_praise_songs(db: Session = Depends(get_db)):
    rows = db.execute(text("SELECT id, song_number, name FROM praise_songs ORDER BY song_number")).mappings().all()
    return success_response(data=[dict(r) for r in rows])

@router.get("/unit-of-measures")
def list_unit_of_measures(db: Session = Depends(get_db)):
    rows = db.query(UnitOfMeasure).order_by(UnitOfMeasure.unit).all()
    return success_response(data=[{"id": u.id, "unit": u.unit, "meaning": u.meaning} for u in rows])


# ── Church Members listing ────────────────────────────────────
@router.get("/church-members")
def list_church_members(
    head_parish_id: int,
    sub_parish_id: Optional[int] = None,
    community_id: Optional[int] = None,
    search: Optional[str] = None,
    page: int = Query(1, ge=1),
    limit: int = Query(50, ge=1, le=500),
    db: Session = Depends(get_db),
):
    q = db.query(ChurchMember).filter(ChurchMember.head_parish_id == head_parish_id, ChurchMember.is_active == True)
    if sub_parish_id:
        q = q.filter(ChurchMember.sub_parish_id == sub_parish_id)
    if community_id:
        q = q.filter(ChurchMember.community_id == community_id)
    if search:
        s = f"%{search}%"
        from sqlalchemy import or_
        q = q.filter(or_(
            ChurchMember.first_name.ilike(s), ChurchMember.middle_name.ilike(s),
            ChurchMember.last_name.ilike(s), ChurchMember.phone.ilike(s),
            ChurchMember.envelope_number.ilike(s),
        ))

    total = q.count()
    members = q.order_by(ChurchMember.envelope_number).offset((page - 1) * limit).limit(limit).all()
    return success_response(data={
        "members": [{
            "member_id": m.id, "first_name": m.first_name, "middle_name": m.middle_name,
            "last_name": m.last_name, "envelope_number": m.envelope_number,
            "phone": m.phone, "email": m.email, "gender": m.gender,
            "member_type": m.member_type, "sub_parish_id": m.sub_parish_id,
            "community_id": m.community_id,
        } for m in members],
        "total": total, "page": page, "total_pages": -(-total // limit),
    })


@router.get("/church-members/accounts")
def list_member_accounts(head_parish_id: int, db: Session = Depends(get_db)):
    """Members that have app accounts (password set)."""
    members = db.query(ChurchMember).filter(
        ChurchMember.head_parish_id == head_parish_id,
        ChurchMember.is_active == True,
        ChurchMember.password != None,
    ).order_by(ChurchMember.first_name).all()
    return success_response(data=[{
        "member_id": m.id, "first_name": m.first_name, "last_name": m.last_name,
        "phone": m.phone, "envelope_number": m.envelope_number,
    } for m in members])


@router.get("/excluded-members")
def list_excluded_members(head_parish_id: int, db: Session = Depends(get_db)):
    members = db.query(ChurchMember).filter(
        ChurchMember.head_parish_id == head_parish_id, ChurchMember.status == "Excluded"
    ).order_by(ChurchMember.first_name).all()
    return success_response(data=[{
        "member_id": m.id, "first_name": m.first_name, "last_name": m.last_name,
        "envelope_number": m.envelope_number,
    } for m in members])


# ── Church Leaders & Choirs ───────────────────────────────────
@router.get("/church-leaders")
def list_leaders(head_parish_id: int, db: Session = Depends(get_db)):
    rows = db.query(ChurchLeader).filter(ChurchLeader.head_parish_id == head_parish_id).order_by(ChurchLeader.first_name).all()
    return success_response(data=[{
        "id": l.id, "first_name": l.first_name, "middle_name": l.middle_name,
        "last_name": l.last_name, "role_id": l.role_id, "status": l.status,
    } for l in rows])

@router.get("/church-choirs")
def list_choirs(head_parish_id: int, db: Session = Depends(get_db)):
    rows = db.query(ChurchChoir).filter(ChurchChoir.head_parish_id == head_parish_id).order_by(ChurchChoir.name).all()
    return success_response(data=[{"id": c.id, "name": c.name} for c in rows])


# ── Harambee data ─────────────────────────────────────────────
@router.get("/harambee-details/{harambee_id}")
def get_harambee_details(harambee_id: int, db: Session = Depends(get_db)):
    h = db.query(Harambee).filter(Harambee.id == harambee_id).first()
    if not h:
        return success_response(data=None)
    total = db.query(func.coalesce(func.sum(HarambeeContribution.amount), 0)).filter(
        HarambeeContribution.harambee_id == harambee_id
    ).scalar()
    return success_response(data={
        "id": h.id, "name": h.name, "description": h.description,
        "from_date": str(h.from_date), "to_date": str(h.to_date),
        "amount": float(h.amount), "total_contributions": float(total),
        "management_level": h.management_level,
    })

@router.get("/harambee-groups")
def list_harambee_groups(harambee_id: int, db: Session = Depends(get_db)):
    groups = db.query(HarambeeGroup).filter(HarambeeGroup.harambee_id == harambee_id).all()
    result = []
    for g in groups:
        members = db.execute(text("""
            SELECT cm.id, CONCAT(cm.first_name,' ',COALESCE(cm.middle_name,''),' ',cm.last_name) AS full_name, cm.phone
            FROM harambee_group_members hgm
            JOIN church_members cm ON hgm.member_id = cm.id
            WHERE hgm.harambee_group_id = :gid
        """), {"gid": g.id}).mappings().all()
        result.append({
            "harambee_group_id": g.id, "name": g.name,
            "target": float(g.target), "members_count": len(members),
            "members": [dict(m) for m in members],
        })
    return success_response(data=result)

@router.get("/harambee-classes")
def list_harambee_classes(harambee_id: int, db: Session = Depends(get_db)):
    rows = db.query(HarambeeClass).filter(HarambeeClass.harambee_id == harambee_id).order_by(HarambeeClass.min_amount).all()
    return success_response(data=[{
        "id": c.id, "class_name": c.class_name,
        "min_amount": float(c.min_amount), "max_amount": float(c.max_amount) if c.max_amount else None,
    } for c in rows])


# ── Envelope data ─────────────────────────────────────────────
@router.get("/envelope-targets")
def list_envelope_targets(head_parish_id: int, year: Optional[int] = None, db: Session = Depends(get_db)):
    q = db.query(EnvelopeTarget).join(ChurchMember).filter(ChurchMember.head_parish_id == head_parish_id)
    if year:
        q = q.filter(func.extract("year", EnvelopeTarget.from_date) == year)
    rows = q.all()
    return success_response(data=[{
        "id": t.id, "member_id": t.member_id, "target": float(t.target),
        "from_date": str(t.from_date), "end_date": str(t.end_date),
    } for t in rows])

@router.get("/available-envelope-years")
def available_envelope_years(db: Session = Depends(get_db)):
    rows = db.execute(text("SELECT DISTINCT EXTRACT(YEAR FROM from_date)::int AS yr FROM envelope_targets ORDER BY yr DESC")).all()
    return success_response(data=[r[0] for r in rows])


# ── Financial data ────────────────────────────────────────────
@router.get("/bank-accounts")
def list_bank_accounts(entity_type: str, entity_id: int, db: Session = Depends(get_db)):
    rows = db.query(BankAccount).filter(
        BankAccount.entity_type == entity_type, BankAccount.entity_id == entity_id, BankAccount.is_active == True
    ).all()
    return success_response(data=[{
        "id": a.id, "account_name": a.account_name, "account_number": a.account_number,
        "bank_id": a.bank_id, "balance": float(a.balance),
    } for a in rows])

@router.get("/revenue-streams")
def list_revenue_streams(entity_type: str, entity_id: int, db: Session = Depends(get_db)):
    rows = db.query(RevenueStream).filter(
        RevenueStream.entity_type == entity_type, RevenueStream.entity_id == entity_id, RevenueStream.is_active == True
    ).all()
    return success_response(data=[{"id": r.id, "name": r.name, "account_id": r.account_id} for r in rows])

@router.get("/expense-groups")
def list_expense_groups(head_parish_id: int, management_level: Optional[str] = None, db: Session = Depends(get_db)):
    q = db.query(ExpenseGroup).filter(ExpenseGroup.head_parish_id == head_parish_id)
    if management_level:
        q = q.filter(ExpenseGroup.management_level == management_level)
    return success_response(data=[{"id": g.id, "name": g.name, "management_level": g.management_level} for g in q.all()])

@router.get("/expense-names")
def list_expense_names(expense_group_id: int, db: Session = Depends(get_db)):
    rows = db.query(ExpenseName).filter(ExpenseName.expense_group_id == expense_group_id).all()
    return success_response(data=[{"id": n.id, "name": n.name} for n in rows])

@router.get("/revenue-groups")
def list_revenue_groups(head_parish_id: int, db: Session = Depends(get_db)):
    rows = db.query(RevenueGroupModel).filter(RevenueGroupModel.head_parish_id == head_parish_id).all()
    return success_response(data=[{"id": r.id, "name": r.name} for r in rows])


# ── Attendance & Meetings ─────────────────────────────────────
@router.get("/attendance-benchmark")
def get_attendance_benchmark(head_parish_id: int, db: Session = Depends(get_db)):
    b = db.query(AttendanceBenchmark).filter(AttendanceBenchmark.head_parish_id == head_parish_id).order_by(AttendanceBenchmark.year.desc()).first()
    if b:
        return success_response(data={"benchmark": b.benchmark, "year": b.year})
    return success_response(data=None)

@router.get("/meetings")
def list_meetings(head_parish_id: int, db: Session = Depends(get_db)):
    rows = db.query(Meeting).filter(Meeting.head_parish_id == head_parish_id).order_by(Meeting.meeting_date.desc()).all()
    return success_response(data=[{
        "id": m.id, "title": m.title, "meeting_date": str(m.meeting_date),
        "meeting_time": str(m.meeting_time), "meeting_place": m.meeting_place,
    } for m in rows])


# ── Debits ────────────────────────────────────────────────────
@router.get("/debits")
def list_debits(head_parish_id: int, db: Session = Depends(get_db)):
    rows = db.query(HeadParishDebit).filter(HeadParishDebit.head_parish_id == head_parish_id).order_by(HeadParishDebit.date_debited.desc()).all()
    return success_response(data=[{
        "id": d.id, "description": d.description, "amount": float(d.amount),
        "date_debited": str(d.date_debited), "return_before_date": str(d.return_before_date),
        "purpose": d.purpose, "is_paid": d.is_paid,
    } for d in rows])


# ── Exclusion reasons ─────────────────────────────────────────
@router.get("/member-exclusion-reasons")
def list_member_exclusion_reasons(head_parish_id: int, db: Session = Depends(get_db)):
    rows = db.execute(text("SELECT id, reason FROM member_exclusion_reasons WHERE head_parish_id = :hpid"), {"hpid": head_parish_id}).mappings().all()
    return success_response(data=[dict(r) for r in rows])

@router.get("/harambee-exclusion-reasons")
def list_harambee_exclusion_reasons(head_parish_id: int, db: Session = Depends(get_db)):
    rows = db.execute(text("SELECT id, reason FROM harambee_exclusion_reasons WHERE head_parish_id = :hpid"), {"hpid": head_parish_id}).mappings().all()
    return success_response(data=[dict(r) for r in rows])


# ── SMS API Config ────────────────────────────────────────────
@router.get("/sms-api-config")
def get_sms_api_config(head_parish_id: int, db: Session = Depends(get_db)):
    from models.config import SmsApiConfig
    cfg = db.query(SmsApiConfig).filter(SmsApiConfig.head_parish_id == head_parish_id).first()
    if not cfg:
        return success_response(data=None)
    return success_response(data={
        "id": cfg.id, "account_name": cfg.account_name,
        "api_username": cfg.api_username, "sender_id": cfg.sender_id,
    })


# ── Payment Gateway Wallets ──────────────────────────────────
@router.get("/payment-wallets")
def list_payment_wallets(head_parish_id: int, db: Session = Depends(get_db)):
    from models.payments import PaymentGatewayWallet
    rows = db.query(PaymentGatewayWallet).filter(
        PaymentGatewayWallet.head_parish_id == head_parish_id,
        PaymentGatewayWallet.is_active == True,
    ).all()
    return success_response(data=[{
        "id": w.id, "wallet_name": w.wallet_name, "wallet_number": w.wallet_number,
        "provider": w.provider, "is_active": w.is_active,
    } for w in rows])


# ── Church Events ─────────────────────────────────────────────
@router.get("/church-events")
def list_church_events(head_parish_id: int, db: Session = Depends(get_db)):
    from models.operations import ChurchEvent
    rows = db.query(ChurchEvent).filter(
        ChurchEvent.head_parish_id == head_parish_id
    ).order_by(ChurchEvent.event_date.desc()).limit(100).all()
    return success_response(data=[{
        "id": e.id, "title": e.title, "description": e.description,
        "event_date": str(e.event_date), "event_time": str(e.event_time) if e.event_time else None,
        "location": e.location,
    } for e in rows])


# ── Bank Postings & Closing Balances ─────────────────────────
@router.get("/bank-postings")
def list_bank_postings(account_id: int, db: Session = Depends(get_db)):
    rows = db.query(BankPosting).filter(
        BankPosting.account_id == account_id
    ).order_by(BankPosting.posted_at.desc()).limit(200).all()
    return success_response(data=[{
        "id": p.id, "amount": float(p.amount), "posting_type": p.posting_type,
        "reference_type": p.reference_type, "description": p.description,
        "posted_at": str(p.posted_at),
    } for p in rows])

@router.get("/bank-closing-balances")
def list_closing_balances(account_id: int, db: Session = Depends(get_db)):
    rows = db.query(BankClosingBalance).filter(
        BankClosingBalance.account_id == account_id
    ).order_by(BankClosingBalance.balance_date.desc()).limit(50).all()
    return success_response(data=[{
        "id": b.id, "closing_balance": float(b.closing_balance),
        "balance_date": str(b.balance_date),
    } for b in rows])


# ── Harambee Distributions ────────────────────────────────────
@router.get("/harambee-distributions")
def list_harambee_distributions(harambee_id: int, db: Session = Depends(get_db)):
    from models.harambee import HarambeeDistribution
    rows = db.query(HarambeeDistribution).filter(
        HarambeeDistribution.harambee_id == harambee_id
    ).order_by(HarambeeDistribution.distribution_date.desc()).all()
    return success_response(data=[{
        "id": d.id, "member_id": d.member_id, "amount": float(d.amount),
        "distribution_date": str(d.distribution_date),
    } for d in rows])


# ── Harambee Expenses ─────────────────────────────────────────
@router.get("/harambee-expenses")
def list_harambee_expenses(harambee_id: int, db: Session = Depends(get_db)):
    from models.harambee import HarambeeExpense
    rows = db.query(HarambeeExpense).filter(
        HarambeeExpense.harambee_id == harambee_id
    ).order_by(HarambeeExpense.expense_date.desc()).all()
    return success_response(data=[{
        "id": e.id, "expense_name_id": e.expense_name_id,
        "amount": float(e.amount), "description": e.description,
        "expense_date": str(e.expense_date),
    } for e in rows])


# ── Harambee Excluded Members ─────────────────────────────────
@router.get("/harambee-excluded-members")
def list_harambee_excluded_members(harambee_id: int, db: Session = Depends(get_db)):
    from models.harambee import HarambeeExclusion
    rows = db.execute(text("""
        SELECT he.id, he.member_id, he.reason,
               cm.first_name, cm.middle_name, cm.last_name, cm.envelope_number
        FROM harambee_exclusions he
        JOIN church_members cm ON he.member_id = cm.id
        WHERE he.harambee_id = :hid
    """), {"hid": harambee_id}).mappings().all()
    return success_response(data=[dict(r) for r in rows])


# ── Harambee Letter Statuses ──────────────────────────────────
@router.get("/harambee-letter-statuses")
def list_harambee_letter_statuses(head_parish_id: int, db: Session = Depends(get_db)):
    from models.harambee import HarambeeLetterStatus
    rows = db.execute(text("""
        SELECT hls.member_id, hls.status,
               cm.first_name, cm.last_name, cm.envelope_number
        FROM harambee_letter_statuses hls
        JOIN church_members cm ON hls.member_id = cm.id
        WHERE hls.head_parish_id = :hpid
    """), {"hpid": head_parish_id}).mappings().all()
    return success_response(data=[dict(r) for r in rows])


# ── Harambee Targets ──────────────────────────────────────────
@router.get("/harambee-targets")
def list_harambee_targets(harambee_id: int, db: Session = Depends(get_db)):
    rows = db.execute(text("""
        SELECT ht.id, ht.member_id, ht.target, ht.target_type,
               cm.first_name, cm.last_name, cm.envelope_number,
               COALESCE(SUM(hc.amount), 0) AS total_contributed
        FROM harambee_targets ht
        JOIN church_members cm ON ht.member_id = cm.id
        LEFT JOIN harambee_contributions hc ON hc.harambee_id = ht.harambee_id AND hc.member_id = ht.member_id
        WHERE ht.harambee_id = :hid
        GROUP BY ht.id, cm.first_name, cm.last_name, cm.envelope_number
        ORDER BY cm.first_name
    """), {"hid": harambee_id}).mappings().all()
    return success_response(data=[dict(r) for r in rows])


# ── Harambee Contributions by member ─────────────────────────
@router.get("/harambee-contributions")
def list_harambee_contributions(
    harambee_id: int,
    member_id: Optional[int] = None,
    db: Session = Depends(get_db),
):
    from models.harambee import HarambeeContribution
    q = db.query(HarambeeContribution).filter(HarambeeContribution.harambee_id == harambee_id)
    if member_id:
        q = q.filter(HarambeeContribution.member_id == member_id)
    rows = q.order_by(HarambeeContribution.contribution_date.desc()).all()
    return success_response(data=[{
        "id": c.id, "member_id": c.member_id, "amount": float(c.amount),
        "contribution_date": str(c.contribution_date), "payment_method": c.payment_method,
    } for c in rows])


# ── Envelope Contributions ────────────────────────────────────
@router.get("/envelope-contributions")
def list_envelope_contributions(
    head_parish_id: int,
    member_id: Optional[int] = None,
    year: Optional[int] = None,
    db: Session = Depends(get_db),
):
    from models.envelope import EnvelopeContribution
    q = db.query(EnvelopeContribution).filter(EnvelopeContribution.head_parish_id == head_parish_id)
    if member_id:
        q = q.filter(EnvelopeContribution.member_id == member_id)
    if year:
        q = q.filter(func.extract("year", EnvelopeContribution.contribution_date) == year)
    rows = q.order_by(EnvelopeContribution.contribution_date.desc()).limit(500).all()
    return success_response(data=[{
        "id": c.id, "member_id": c.member_id, "amount": float(c.amount),
        "contribution_date": str(c.contribution_date), "payment_method": c.payment_method,
    } for c in rows])


# ── App Version ───────────────────────────────────────────────
@router.get("/app-version")
def get_app_version(platform: str = "android", db: Session = Depends(get_db)):
    from models.misc import AppVersion
    ver = db.query(AppVersion).filter(
        AppVersion.platform == platform
    ).order_by(AppVersion.created_at.desc()).first()
    if not ver:
        return success_response(data=None)
    return success_response(data={
        "version": ver.version, "force_update": ver.force_update, "platform": ver.platform,
    })


# ── Revenue Group Stream Mapping ──────────────────────────────
@router.get("/revenue-group-streams")
def list_revenue_group_streams(revenue_group_id: int, db: Session = Depends(get_db)):
    from models.config import RevenueGroupStreamMap
    rows = db.execute(text("""
        SELECT rgsm.id, rgsm.revenue_stream_id, rs.name AS stream_name
        FROM revenue_group_stream_map rgsm
        JOIN revenue_streams rs ON rgsm.revenue_stream_id = rs.id
        WHERE rgsm.revenue_group_id = :rgid
    """), {"rgid": revenue_group_id}).mappings().all()
    return success_response(data=[dict(r) for r in rows])


# ── Program Revenue Mapping ───────────────────────────────────
@router.get("/program-revenue-map")
def list_program_revenue_map(head_parish_id: int, db: Session = Depends(get_db)):
    rows = db.execute(text("""
        SELECT prm.id, prm.program_name, prm.revenue_stream_id, rs.name AS stream_name
        FROM program_revenue_map prm
        JOIN revenue_streams rs ON prm.revenue_stream_id = rs.id
        WHERE prm.head_parish_id = :hpid
    """), {"hpid": head_parish_id}).mappings().all()
    return success_response(data=[dict(r) for r in rows])


# ── Expense Requests ──────────────────────────────────────────
@router.get("/expense-requests")
def list_expense_requests(
    head_parish_id: int,
    status: Optional[str] = None,
    management_level: Optional[str] = None,
    db: Session = Depends(get_db),
):
    from models.finance import ExpenseRequest, ExpenseRequestItem
    q = db.query(ExpenseRequest).filter(ExpenseRequest.head_parish_id == head_parish_id)
    if status:
        q = q.filter(ExpenseRequest.status == status)
    if management_level:
        q = q.filter(ExpenseRequest.management_level == management_level)
    requests = q.order_by(ExpenseRequest.created_at.desc()).limit(100).all()
    result = []
    for r in requests:
        items = db.query(ExpenseRequestItem).filter(ExpenseRequestItem.request_id == r.id).all()
        result.append({
            "id": r.id, "management_level": r.management_level,
            "status": r.status, "total_amount": float(r.total_amount),
            "notes": r.notes, "created_at": str(r.created_at),
            "items": [{"expense_name_id": i.expense_name_id, "amount": float(i.amount), "description": i.description} for i in items],
        })
    return success_response(data=result)


# ── Annual Revenue Targets ────────────────────────────────────
@router.get("/annual-revenue-targets")
def list_annual_revenue_targets(head_parish_id: int, year: Optional[int] = None, db: Session = Depends(get_db)):
    from models.finance import AnnualRevenueTarget
    q = db.query(AnnualRevenueTarget).filter(AnnualRevenueTarget.head_parish_id == head_parish_id)
    if year:
        q = q.filter(AnnualRevenueTarget.year == year)
    rows = q.all()
    return success_response(data=[{
        "id": t.id, "revenue_stream_id": t.revenue_stream_id,
        "year": t.year, "target_amount": float(t.target_amount),
    } for t in rows])


# ── Annual Expense Budgets ────────────────────────────────────
@router.get("/annual-expense-budgets")
def list_annual_expense_budgets(head_parish_id: int, year: Optional[int] = None, db: Session = Depends(get_db)):
    from models.finance import AnnualExpenseBudget
    q = db.query(AnnualExpenseBudget).filter(AnnualExpenseBudget.head_parish_id == head_parish_id)
    if year:
        q = q.filter(AnnualExpenseBudget.year == year)
    rows = q.all()
    return success_response(data=[{
        "id": b.id, "expense_name_id": b.expense_name_id,
        "year": b.year, "budget_amount": float(b.budget_amount),
    } for b in rows])


# ── Revenues listing ──────────────────────────────────────────
@router.get("/revenues")
def list_revenues(
    head_parish_id: int,
    management_level: Optional[str] = None,
    from_date: Optional[str] = None,
    to_date: Optional[str] = None,
    is_verified: Optional[bool] = None,
    db: Session = Depends(get_db),
):
    from models.finance import Revenue
    q = db.query(Revenue).filter(Revenue.head_parish_id == head_parish_id)
    if management_level:
        q = q.filter(Revenue.management_level == management_level)
    if from_date:
        q = q.filter(Revenue.revenue_date >= from_date)
    if to_date:
        q = q.filter(Revenue.revenue_date <= to_date)
    if is_verified is not None:
        q = q.filter(Revenue.is_verified == is_verified)
    rows = q.order_by(Revenue.revenue_date.desc()).limit(500).all()
    return success_response(data=[{
        "id": r.id, "management_level": r.management_level,
        "revenue_stream_id": r.revenue_stream_id, "amount": float(r.amount),
        "payment_method": r.payment_method, "description": r.description,
        "revenue_date": str(r.revenue_date), "is_verified": r.is_verified,
        "is_posted_to_bank": r.is_posted_to_bank, "service_number": r.service_number,
    } for r in rows])


# ── Expenses listing ──────────────────────────────────────────
@router.get("/expenses")
def list_expenses(
    head_parish_id: int,
    management_level: Optional[str] = None,
    from_date: Optional[str] = None,
    to_date: Optional[str] = None,
    db: Session = Depends(get_db),
):
    from models.finance import Expense
    q = db.query(Expense).filter(Expense.head_parish_id == head_parish_id)
    if management_level:
        q = q.filter(Expense.management_level == management_level)
    if from_date:
        q = q.filter(Expense.expense_date >= from_date)
    if to_date:
        q = q.filter(Expense.expense_date <= to_date)
    rows = q.order_by(Expense.expense_date.desc()).limit(500).all()
    return success_response(data=[{
        "id": r.id, "management_level": r.management_level,
        "expense_name_id": r.expense_name_id, "amount": float(r.amount),
        "payment_method": r.payment_method, "description": r.description,
        "expense_date": str(r.expense_date),
    } for r in rows])


# ── Attendance listing ────────────────────────────────────────
@router.get("/attendance")
def list_attendance_data(
    head_parish_id: int,
    management_level: Optional[str] = None,
    from_date: Optional[str] = None,
    to_date: Optional[str] = None,
    db: Session = Depends(get_db),
):
    q = db.query(Attendance).filter(Attendance.head_parish_id == head_parish_id)
    if management_level:
        q = q.filter(Attendance.management_level == management_level)
    if from_date:
        q = q.filter(Attendance.attendance_date >= from_date)
    if to_date:
        q = q.filter(Attendance.attendance_date <= to_date)
    rows = q.order_by(Attendance.attendance_date.desc()).limit(500).all()
    return success_response(data=[{
        "id": a.id, "event_title": a.event_title,
        "male": a.male_attendance, "female": a.female_attendance,
        "children": a.children_attendance,
        "total": a.male_attendance + a.female_attendance + a.children_attendance,
        "date": str(a.attendance_date), "service_number": a.service_number,
        "management_level": a.management_level,
    } for a in rows])


# ── Sunday Services data ──────────────────────────────────────
@router.get("/sunday-services")
def list_sunday_services_data(head_parish_id: int, db: Session = Depends(get_db)):
    from models.sunday_service import SundayService
    rows = db.query(SundayService).filter(
        SundayService.head_parish_id == head_parish_id
    ).order_by(SundayService.service_date.desc()).limit(52).all()
    return success_response(data=[{
        "id": s.id, "service_date": str(s.service_date),
        "service_color_id": s.service_color_id,
        "base_scripture_text": s.base_scripture_text,
    } for s in rows])


# ── Service Times ─────────────────────────────────────────────
@router.get("/service-times")
def list_service_times(head_parish_id: int, db: Session = Depends(get_db)):
    from models.sunday_service import HeadParishServiceTime
    rows = db.query(HeadParishServiceTime).filter(
        HeadParishServiceTime.head_parish_id == head_parish_id
    ).order_by(HeadParishServiceTime.service_number).all()
    return success_response(data=[{
        "service_number": t.service_number,
        "start_time": str(t.start_time), "end_time": str(t.end_time) if t.end_time else None,
    } for t in rows])


# ── Services Count ────────────────────────────────────────────
@router.get("/services-count")
def get_services_count(head_parish_id: int, db: Session = Depends(get_db)):
    from models.sunday_service import HeadParishServicesCount
    row = db.query(HeadParishServicesCount).filter(
        HeadParishServicesCount.head_parish_id == head_parish_id
    ).first()
    return success_response(data={"services_count": row.services_count if row else 0})


# ── Assets listing ────────────────────────────────────────────
@router.get("/assets")
def list_assets_data(head_parish_id: int, db: Session = Depends(get_db)):
    from models.operations import Asset
    rows = db.query(Asset).filter(Asset.head_parish_id == head_parish_id).order_by(Asset.name).all()
    return success_response(data=[{
        "id": a.id, "name": a.name, "generates_revenue": a.generates_revenue, "status": a.status,
    } for a in rows])


# ── Feedback listing (for system admin) ───────────────────────
@router.get("/feedback")
def list_feedback(head_parish_id: Optional[int] = None, db: Session = Depends(get_db)):
    from models.misc import Feedback
    q = db.query(Feedback)
    if head_parish_id:
        q = q.filter(Feedback.head_parish_id == head_parish_id)
    rows = q.order_by(Feedback.submitted_at.desc()).limit(100).all()
    return success_response(data=[{
        "id": f.id, "head_parish_id": f.head_parish_id,
        "feedback_type": f.feedback_type, "subject": f.subject,
        "message": f.message, "submitted_at": str(f.submitted_at),
    } for f in rows])
