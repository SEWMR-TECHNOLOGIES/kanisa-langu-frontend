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
