# api/routes/records.py
"""Record creation routes — admin operations.
Replaces ALL files in legacy/api/records/: record_*, set_*, upload_*, distribute_*,
exclude_*, assign_*, link_*, map_*, post_to_bank, submit_expense_request, notify_members, etc.
Also replaces legacy/api/registration/ files."""

from datetime import date as date_type
from fastapi import APIRouter, Depends, HTTPException, UploadFile, File, Form
from pydantic import BaseModel
from sqlalchemy.orm import Session
from sqlalchemy import text, func
from typing import Optional, List

from core.database import get_db
from models.admins import Admin
from models.members import ChurchMember, MemberExclusion, ChurchLeader, ChurchChoir
from models.harambee import (
    Harambee, HarambeeGroup, HarambeeGroupMember, HarambeeTarget,
    HarambeeContribution, HarambeeClass, HarambeeDistribution,
    HarambeeExclusion, HarambeeExpense, HarambeeLetterStatus, DelayedHarambeeNotification,
)
from models.envelope import EnvelopeTarget, EnvelopeContribution
from models.finance import (
    BankAccount, RevenueStream, Revenue, ExpenseGroup, ExpenseName,
    Expense, ExpenseRequest, ExpenseRequestItem, AnnualRevenueTarget, AnnualExpenseBudget,
)
from models.operations import (
    Attendance, AttendanceBenchmark, Meeting, MeetingAgenda,
    MeetingMinutes, MeetingNotes, ChurchEvent, Asset, AssetRevenue, AssetExpense, AssetStatusLog,
)
from models.sunday_service import (
    SundayService, SundayServiceScripture, SundayServiceSong,
    SundayServiceChoir, SundayServiceOffering, SundayServiceLeader,
    SundayServiceElder, SundayServicePreacher, HeadParishServiceTime, HeadParishServicesCount,
)
from models.payments import PaymentGatewayWallet
from models.banking import BankPosting, BankClosingBalance
from models.config import SmsApiConfig, RevenueGroupModel, ProgramRevenueMap
from models.misc import HeadParishDebit, MemberExclusionReason, HarambeeExclusionReason, Feedback
from utils.auth import hash_password, get_current_admin
from utils.validation import is_valid_email, is_valid_phone, normalize_phone, validate_age
from utils.response import success_response, error_response

router = APIRouter(prefix="/records", tags=["Records"])


# ═══════════════════════════════════════════════════════════════
# REGISTRATION — Replaces legacy/api/registration/*
# ═══════════════════════════════════════════════════════════════

class RegisterMemberRequest(BaseModel):
    first_name: str
    middle_name: Optional[str] = None
    last_name: str
    date_of_birth: date_type
    gender: str
    member_type: str = "Mwenyeji"
    head_parish_id: int
    sub_parish_id: int
    community_id: int
    envelope_number: Optional[str] = None
    occupation_id: Optional[int] = None
    phone: Optional[str] = None
    email: Optional[str] = None
    title_id: Optional[int] = None

@router.post("/register-member")
def register_member(body: RegisterMemberRequest, db: Session = Depends(get_db)):
    phone = normalize_phone(body.phone) if body.phone else None
    if phone and db.query(ChurchMember).filter(ChurchMember.phone == phone).first():
        return error_response("Phone already exists")
    if body.email and db.query(ChurchMember).filter(ChurchMember.email == body.email).first():
        return error_response("Email already exists")
    if body.envelope_number and db.query(ChurchMember).filter(ChurchMember.envelope_number == body.envelope_number).first():
        return error_response("Envelope number already exists")

    member = ChurchMember(
        title_id=body.title_id, first_name=body.first_name.capitalize(),
        middle_name=body.middle_name.capitalize() if body.middle_name else None,
        last_name=body.last_name.capitalize(), date_of_birth=body.date_of_birth,
        gender=body.gender, member_type=body.member_type,
        head_parish_id=body.head_parish_id, sub_parish_id=body.sub_parish_id,
        community_id=body.community_id, envelope_number=body.envelope_number,
        occupation_id=body.occupation_id, phone=phone, email=body.email,
    )
    db.add(member); db.commit(); db.refresh(member)
    return success_response("Church member registered", {"id": member.id})


class RegisterLeaderRequest(BaseModel):
    title_id: Optional[int] = None
    first_name: str
    middle_name: Optional[str] = None
    last_name: str
    gender: str
    leader_type: str
    head_parish_id: int
    role_id: int
    appointment_date: date_type
    end_date: Optional[date_type] = None

@router.post("/register-leader")
def register_leader(body: RegisterLeaderRequest, db: Session = Depends(get_db)):
    leader = ChurchLeader(**body.dict())
    db.add(leader); db.commit(); db.refresh(leader)
    return success_response("Leader registered", {"id": leader.id})


class RegisterChoirRequest(BaseModel):
    name: str
    head_parish_id: int
    description: Optional[str] = None

@router.post("/register-choir")
def register_choir(body: RegisterChoirRequest, db: Session = Depends(get_db)):
    if db.query(ChurchChoir).filter(ChurchChoir.name == body.name, ChurchChoir.head_parish_id == body.head_parish_id).first():
        return error_response("Choir already exists")
    c = ChurchChoir(**body.dict())
    db.add(c); db.commit(); db.refresh(c)
    return success_response("Choir registered", {"id": c.id})


class RegisterAdminRequest(BaseModel):
    fullname: str
    email: Optional[str] = ""
    phone: str
    role: str
    admin_level: str
    diocese_id: Optional[int] = None
    province_id: Optional[int] = None
    head_parish_id: Optional[int] = None
    sub_parish_id: Optional[int] = None
    community_id: Optional[int] = None
    group_id: Optional[int] = None

@router.post("/create-admin")
def create_admin(body: RegisterAdminRequest, db: Session = Depends(get_db)):
    admin = Admin(
        fullname=body.fullname, email=body.email or None, phone=body.phone,
        role=body.role, password=hash_password("KanisaLangu"), admin_level=body.admin_level,
        diocese_id=body.diocese_id, province_id=body.province_id,
        head_parish_id=body.head_parish_id, sub_parish_id=body.sub_parish_id,
        community_id=body.community_id, group_id=body.group_id,
    )
    db.add(admin); db.commit(); db.refresh(admin)
    return success_response("Admin registered", {"id": admin.id})


# ═══════════════════════════════════════════════════════════════
# HARAMBEE RECORDS
# ═══════════════════════════════════════════════════════════════

class RecordHarambeeRequest(BaseModel):
    management_level: str
    head_parish_id: int
    sub_parish_id: Optional[int] = None
    community_id: Optional[int] = None
    group_id: Optional[int] = None
    account_id: int
    name: str
    description: str
    from_date: date_type
    to_date: date_type
    amount: float

@router.post("/record-harambee")
def record_harambee(body: RecordHarambeeRequest, db: Session = Depends(get_db)):
    h = Harambee(**body.dict())
    db.add(h); db.commit(); db.refresh(h)
    return success_response("Harambee recorded", {"id": h.id})


class RecordHarambeeContributionRequest(BaseModel):
    harambee_id: int
    member_id: int
    amount: float
    contribution_date: date_type
    head_parish_id: int
    payment_method: str = "Cash"
    target_table: str = "head-parish"

@router.post("/record-harambee-contribution")
def record_harambee_contribution(body: RecordHarambeeContributionRequest, db: Session = Depends(get_db)):
    if body.amount <= 0:
        return error_response("Amount must be > 0")
    member = db.query(ChurchMember).filter(ChurchMember.id == body.member_id).first()
    if not member:
        return error_response("Member not found")

    contrib = HarambeeContribution(
        harambee_id=body.harambee_id, member_id=body.member_id,
        amount=body.amount, contribution_date=body.contribution_date,
        head_parish_id=body.head_parish_id, sub_parish_id=member.sub_parish_id,
        community_id=member.community_id, payment_method=body.payment_method,
    )
    db.add(contrib); db.commit(); db.refresh(contrib)
    return success_response("Contribution recorded", {"id": contrib.id})


class RecordHarambeeTargetRequest(BaseModel):
    harambee_id: int
    member_id: int
    target: float
    target_type: str = "individual"

@router.post("/record-harambee-target")
def record_harambee_target(body: RecordHarambeeTargetRequest, db: Session = Depends(get_db)):
    existing = db.query(HarambeeTarget).filter(
        HarambeeTarget.harambee_id == body.harambee_id, HarambeeTarget.member_id == body.member_id
    ).first()
    if existing:
        existing.target = body.target; existing.target_type = body.target_type
    else:
        member = db.query(ChurchMember).filter(ChurchMember.id == body.member_id).first()
        db.add(HarambeeTarget(
            harambee_id=body.harambee_id, member_id=body.member_id,
            target=body.target, target_type=body.target_type,
            sub_parish_id=member.sub_parish_id if member else None,
            community_id=member.community_id if member else None,
        ))
    db.commit()
    return success_response("Target set")


class CreateHarambeeGroupRequest(BaseModel):
    harambee_id: int
    name: str
    target: float = 0

@router.post("/create-harambee-group")
def create_harambee_group(body: CreateHarambeeGroupRequest, db: Session = Depends(get_db)):
    g = HarambeeGroup(**body.dict())
    db.add(g); db.commit(); db.refresh(g)
    return success_response("Group created", {"id": g.id})


class AssignGroupMemberRequest(BaseModel):
    harambee_group_id: int
    member_id: int
    responsibility: Optional[str] = None

@router.post("/assign-group-member")
def assign_group_member(body: AssignGroupMemberRequest, db: Session = Depends(get_db)):
    db.add(HarambeeGroupMember(**body.dict()))
    db.commit()
    return success_response("Member assigned to group")


class RecordHarambeeClassRequest(BaseModel):
    harambee_id: int
    class_name: str
    min_amount: float = 0
    max_amount: Optional[float] = None

@router.post("/record-harambee-class")
def record_harambee_class(body: RecordHarambeeClassRequest, db: Session = Depends(get_db)):
    db.add(HarambeeClass(**body.dict())); db.commit()
    return success_response("Class recorded")


class RecordHarambeeDistributionRequest(BaseModel):
    harambee_id: int
    member_id: int
    amount: float
    distribution_date: date_type

@router.post("/record-harambee-distribution")
def record_harambee_distribution(body: RecordHarambeeDistributionRequest, db: Session = Depends(get_db)):
    db.add(HarambeeDistribution(**body.dict())); db.commit()
    return success_response("Distribution recorded")


class RecordHarambeeExpenseRequest(BaseModel):
    target: str
    harambee_id: int
    head_parish_id: int
    expense_name_id: int
    amount: float
    description: str
    expense_date: date_type

@router.post("/record-harambee-expense")
def record_harambee_expense(body: RecordHarambeeExpenseRequest, db: Session = Depends(get_db)):
    db.add(HarambeeExpense(**body.dict())); db.commit()
    return success_response("Harambee expense recorded")


class ExcludeFromHarambeeRequest(BaseModel):
    harambee_id: int
    member_id: int
    reason: str

@router.post("/exclude-from-harambee")
def exclude_from_harambee(body: ExcludeFromHarambeeRequest, db: Session = Depends(get_db)):
    db.add(HarambeeExclusion(**body.dict())); db.commit()
    return success_response("Member excluded from harambee")


class HarambeeLetterStatusRequest(BaseModel):
    member_id: int
    head_parish_id: int
    status: str = "Yes"

@router.post("/record-harambee-letter-status")
def record_harambee_letter_status(body: HarambeeLetterStatusRequest, db: Session = Depends(get_db)):
    existing = db.query(HarambeeLetterStatus).filter(
        HarambeeLetterStatus.member_id == body.member_id, HarambeeLetterStatus.head_parish_id == body.head_parish_id
    ).first()
    if existing:
        existing.status = body.status
    else:
        db.add(HarambeeLetterStatus(**body.dict()))
    db.commit()
    return success_response("Letter status recorded")


# ═══════════════════════════════════════════════════════════════
# ENVELOPE RECORDS
# ═══════════════════════════════════════════════════════════════

class RecordEnvelopeContributionRequest(BaseModel):
    member_id: int
    amount: float
    contribution_date: date_type
    head_parish_id: int
    payment_method: str = "Cash"

@router.post("/record-envelope-contribution")
def record_envelope_contribution(body: RecordEnvelopeContributionRequest, db: Session = Depends(get_db)):
    if body.amount <= 0:
        return error_response("Amount must be > 0")
    member = db.query(ChurchMember).filter(ChurchMember.id == body.member_id).first()
    if not member:
        return error_response("Member not found")
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


class SetEnvelopeTargetRequest(BaseModel):
    member_id: int
    target: float
    year: int

@router.post("/set-envelope-target")
def set_envelope_target(body: SetEnvelopeTargetRequest, db: Session = Depends(get_db)):
    existing = db.query(EnvelopeTarget).filter(
        EnvelopeTarget.member_id == body.member_id,
        func.extract("year", EnvelopeTarget.from_date) == body.year,
    ).first()
    if existing:
        existing.target = body.target
    else:
        db.add(EnvelopeTarget(
            member_id=body.member_id, target=body.target,
            from_date=date_type(body.year, 1, 1), end_date=date_type(body.year, 12, 31),
        ))
    db.commit()
    return success_response("Target set")


# ═══════════════════════════════════════════════════════════════
# REVENUE & EXPENSE RECORDS
# ═══════════════════════════════════════════════════════════════

class RecordRevenueRequest(BaseModel):
    management_level: str
    revenue_stream_id: int
    head_parish_id: int
    sub_parish_id: Optional[int] = None
    service_number: Optional[int] = None
    amount: float
    payment_method: str = "Cash"
    description: Optional[str] = None
    revenue_date: date_type

@router.post("/record-revenue")
def record_revenue(body: RecordRevenueRequest, db: Session = Depends(get_db)):
    rev = Revenue(**body.dict())
    db.add(rev); db.commit(); db.refresh(rev)
    return success_response("Revenue recorded", {"id": rev.id})


class RecordExpenseRequest(BaseModel):
    management_level: str
    expense_name_id: int
    head_parish_id: int
    amount: float
    payment_method: str = "Cash"
    description: Optional[str] = None
    expense_date: date_type

@router.post("/record-expense")
def record_expense_route(body: RecordExpenseRequest, db: Session = Depends(get_db)):
    exp = Expense(**body.dict())
    db.add(exp); db.commit(); db.refresh(exp)
    return success_response("Expense recorded", {"id": exp.id})


class CreateExpenseGroupRequest(BaseModel):
    name: str
    management_level: str
    head_parish_id: int

@router.post("/create-expense-group")
def create_expense_group(body: CreateExpenseGroupRequest, db: Session = Depends(get_db)):
    db.add(ExpenseGroup(**body.dict())); db.commit()
    return success_response("Expense group created")


class CreateExpenseNameRequest(BaseModel):
    expense_group_id: int
    name: str
    management_level: str

@router.post("/record-expense-name")
def record_expense_name(body: CreateExpenseNameRequest, db: Session = Depends(get_db)):
    db.add(ExpenseName(**body.dict())); db.commit()
    return success_response("Expense name created")


class CreateRevenueGroupRequest(BaseModel):
    name: str
    head_parish_id: int

@router.post("/create-revenue-group")
def create_revenue_group(body: CreateRevenueGroupRequest, db: Session = Depends(get_db)):
    db.add(RevenueGroupModel(**body.dict())); db.commit()
    return success_response("Revenue group created")


class SubmitExpenseRequestBody(BaseModel):
    management_level: str
    head_parish_id: int
    sub_parish_id: Optional[int] = None
    community_id: Optional[int] = None
    group_id: Optional[int] = None
    notes: Optional[str] = None
    items: List[dict]  # [{expense_name_id, amount, description}]

@router.post("/submit-expense-request")
def submit_expense_request(body: SubmitExpenseRequestBody, db: Session = Depends(get_db)):
    total = sum(item.get("amount", 0) for item in body.items)
    req = ExpenseRequest(
        management_level=body.management_level, head_parish_id=body.head_parish_id,
        sub_parish_id=body.sub_parish_id, community_id=body.community_id,
        group_id=body.group_id, total_amount=total, notes=body.notes,
    )
    db.add(req); db.flush()
    for item in body.items:
        db.add(ExpenseRequestItem(
            request_id=req.id, expense_name_id=item["expense_name_id"],
            amount=item["amount"], description=item.get("description"),
        ))
    db.commit()
    return success_response("Expense request submitted", {"id": req.id})


class RespondExpenseRequestBody(BaseModel):
    request_id: int
    status: str  # approved / rejected
    responded_by: int

@router.post("/respond-expense-request")
def respond_expense_request(body: RespondExpenseRequestBody, db: Session = Depends(get_db)):
    req = db.query(ExpenseRequest).filter(ExpenseRequest.id == body.request_id).first()
    if not req:
        return error_response("Request not found")
    req.status = body.status
    req.responded_by = body.responded_by
    from datetime import datetime
    req.responded_at = datetime.utcnow()
    db.commit()
    return success_response(f"Request {body.status}")


# ═══════════════════════════════════════════════════════════════
# ANNUAL TARGETS & BUDGETS
# ═══════════════════════════════════════════════════════════════

class SetAnnualRevenueTargetRequest(BaseModel):
    revenue_stream_id: int
    head_parish_id: int
    year: int
    target_amount: float

@router.post("/set-annual-revenue-target")
def set_annual_revenue_target(body: SetAnnualRevenueTargetRequest, db: Session = Depends(get_db)):
    existing = db.query(AnnualRevenueTarget).filter(
        AnnualRevenueTarget.revenue_stream_id == body.revenue_stream_id,
        AnnualRevenueTarget.head_parish_id == body.head_parish_id,
        AnnualRevenueTarget.year == body.year,
    ).first()
    if existing:
        existing.target_amount = body.target_amount
    else:
        db.add(AnnualRevenueTarget(**body.dict()))
    db.commit()
    return success_response("Revenue target set")


class SetAnnualExpenseBudgetRequest(BaseModel):
    expense_name_id: int
    head_parish_id: int
    year: int
    budget_amount: float

@router.post("/set-annual-expense-budget")
def set_annual_expense_budget(body: SetAnnualExpenseBudgetRequest, db: Session = Depends(get_db)):
    existing = db.query(AnnualExpenseBudget).filter(
        AnnualExpenseBudget.expense_name_id == body.expense_name_id,
        AnnualExpenseBudget.head_parish_id == body.head_parish_id,
        AnnualExpenseBudget.year == body.year,
    ).first()
    if existing:
        existing.budget_amount = body.budget_amount
    else:
        db.add(AnnualExpenseBudget(**body.dict()))
    db.commit()
    return success_response("Budget set")


# ═══════════════════════════════════════════════════════════════
# ATTENDANCE & MEETINGS
# ═══════════════════════════════════════════════════════════════

class RecordAttendanceRequest(BaseModel):
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
    attendance_date: date_type

@router.post("/record-attendance")
def record_attendance(body: RecordAttendanceRequest, db: Session = Depends(get_db)):
    db.add(Attendance(**body.dict())); db.commit()
    return success_response("Attendance recorded")


class SetBenchmarkRequest(BaseModel):
    head_parish_id: int
    benchmark: int
    year: int

@router.post("/set-attendance-benchmark")
def set_attendance_benchmark(body: SetBenchmarkRequest, db: Session = Depends(get_db)):
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


class RecordMeetingRequest(BaseModel):
    head_parish_id: int
    title: str
    description: Optional[str] = None
    meeting_date: date_type
    meeting_time: str
    meeting_place: str

@router.post("/record-meeting")
def record_meeting(body: RecordMeetingRequest, db: Session = Depends(get_db)):
    from datetime import time as time_type
    m = Meeting(
        head_parish_id=body.head_parish_id, title=body.title,
        description=body.description, meeting_date=body.meeting_date,
        meeting_time=time_type.fromisoformat(body.meeting_time), meeting_place=body.meeting_place,
    )
    db.add(m); db.commit(); db.refresh(m)
    return success_response("Meeting created", {"id": m.id})


class RecordMeetingMinutesRequest(BaseModel):
    meeting_id: int
    content: str

@router.post("/record-meeting-minutes")
def record_meeting_minutes(body: RecordMeetingMinutesRequest, db: Session = Depends(get_db)):
    db.add(MeetingMinutes(meeting_id=body.meeting_id, content=body.content)); db.commit()
    return success_response("Minutes recorded")


class RecordMeetingAgendaRequest(BaseModel):
    meeting_id: int
    agenda_item: str
    sort_order: int = 0

@router.post("/set-meeting-agenda")
def set_meeting_agenda(body: RecordMeetingAgendaRequest, db: Session = Depends(get_db)):
    db.add(MeetingAgenda(**body.dict())); db.commit()
    return success_response("Agenda added")


class AddMeetingNotesRequest(BaseModel):
    meeting_id: int
    note: str

@router.post("/add-meeting-notes")
def add_meeting_notes(body: AddMeetingNotesRequest, db: Session = Depends(get_db)):
    db.add(MeetingNotes(**body.dict())); db.commit()
    return success_response("Notes added")


# ═══════════════════════════════════════════════════════════════
# SUNDAY SERVICE RECORDS
# ═══════════════════════════════════════════════════════════════

class RecordSundayServiceRequest(BaseModel):
    head_parish_id: int
    service_date: date_type
    service_color_id: Optional[int] = None
    base_scripture_text: Optional[str] = None
    large_liturgy_page_number: Optional[int] = None
    small_liturgy_page_number: Optional[int] = None
    large_antiphony_page_number: Optional[int] = None
    small_antiphony_page_number: Optional[int] = None
    large_praise_page_number: Optional[int] = None
    small_praise_page_number: Optional[int] = None

@router.post("/record-sunday-service")
def record_sunday_service(body: RecordSundayServiceRequest, db: Session = Depends(get_db)):
    existing = db.query(SundayService).filter(
        SundayService.head_parish_id == body.head_parish_id,
        SundayService.service_date == body.service_date,
    ).first()
    if existing:
        for field, value in body.dict(exclude={"head_parish_id", "service_date"}).items():
            if value is not None:
                setattr(existing, field, value)
        db.commit()
        return success_response("Service updated", {"id": existing.id})
    s = SundayService(**body.dict())
    db.add(s); db.commit(); db.refresh(s)
    return success_response("Service created", {"id": s.id})


class SetServiceOfferingsRequest(BaseModel):
    service_id: int
    offerings: List[dict]  # [{service_number, revenue_stream_id, amount}]

@router.post("/set-sunday-service-offerings")
def set_service_offerings(body: SetServiceOfferingsRequest, db: Session = Depends(get_db)):
    db.query(SundayServiceOffering).filter(SundayServiceOffering.service_id == body.service_id).delete()
    for o in body.offerings:
        db.add(SundayServiceOffering(service_id=body.service_id, **o))
    db.commit()
    return success_response("Offerings set")


class SetServiceScripturesRequest(BaseModel):
    service_id: int
    scriptures: List[dict]  # [{service_number, book, chapter, verse_from, verse_to}]

@router.post("/set-sunday-service-scriptures")
def set_service_scriptures(body: SetServiceScripturesRequest, db: Session = Depends(get_db)):
    db.query(SundayServiceScripture).filter(SundayServiceScripture.service_id == body.service_id).delete()
    for s in body.scriptures:
        db.add(SundayServiceScripture(service_id=body.service_id, **s))
    db.commit()
    return success_response("Scriptures set")


class SetServiceSongsRequest(BaseModel):
    service_id: int
    songs: List[dict]  # [{service_number, song_id}]

@router.post("/set-sunday-service-songs")
def set_service_songs(body: SetServiceSongsRequest, db: Session = Depends(get_db)):
    db.query(SundayServiceSong).filter(SundayServiceSong.service_id == body.service_id).delete()
    for s in body.songs:
        db.add(SundayServiceSong(service_id=body.service_id, **s))
    db.commit()
    return success_response("Songs set")


class SetServiceChoirsRequest(BaseModel):
    service_id: int
    choirs: List[dict]  # [{service_number, choir_id}]

@router.post("/set-sunday-service-choirs")
def set_service_choirs(body: SetServiceChoirsRequest, db: Session = Depends(get_db)):
    db.query(SundayServiceChoir).filter(SundayServiceChoir.service_id == body.service_id).delete()
    for c in body.choirs:
        db.add(SundayServiceChoir(service_id=body.service_id, **c))
    db.commit()
    return success_response("Choirs set")


class SetServiceLeaderRequest(BaseModel):
    service_id: int
    service_number: int
    leader_name: str
    role: Optional[str] = None

@router.post("/set-sunday-service-leader")
def set_service_leader(body: SetServiceLeaderRequest, db: Session = Depends(get_db)):
    existing = db.query(SundayServiceLeader).filter(
        SundayServiceLeader.service_id == body.service_id,
        SundayServiceLeader.service_number == body.service_number,
    ).first()
    if existing:
        existing.leader_name = body.leader_name
    else:
        db.add(SundayServiceLeader(**body.dict()))
    db.commit()
    return success_response("Leader set")


class SetServicePreacherRequest(BaseModel):
    service_id: int
    service_number: int
    preacher_name: str

@router.post("/set-sunday-service-preacher")
def set_service_preacher(body: SetServicePreacherRequest, db: Session = Depends(get_db)):
    existing = db.query(SundayServicePreacher).filter(
        SundayServicePreacher.service_id == body.service_id,
        SundayServicePreacher.service_number == body.service_number,
    ).first()
    if existing:
        existing.preacher_name = body.preacher_name
    else:
        db.add(SundayServicePreacher(**body.dict()))
    db.commit()
    return success_response("Preacher set")


class SetServiceEldersRequest(BaseModel):
    service_id: int
    elders: List[dict]  # [{service_number, elder_name}]

@router.post("/set-sunday-service-elders")
def set_service_elders(body: SetServiceEldersRequest, db: Session = Depends(get_db)):
    db.query(SundayServiceElder).filter(SundayServiceElder.service_id == body.service_id).delete()
    for e in body.elders:
        db.add(SundayServiceElder(service_id=body.service_id, **e))
    db.commit()
    return success_response("Elders set")


class SetServiceTimesRequest(BaseModel):
    service_id: int
    times: List[dict]  # [{service_number, time}]

@router.post("/set-sunday-service-times")
def set_service_times(body: SetServiceTimesRequest, db: Session = Depends(get_db)):
    # Stored in head_parish_service_times
    for t in body.times:
        from datetime import time as time_type
        sn = t["service_number"]
        st = time_type.fromisoformat(t["time"])
        service = db.query(SundayService).filter(SundayService.id == body.service_id).first()
        if not service:
            return error_response("Service not found")
        existing = db.query(HeadParishServiceTime).filter(
            HeadParishServiceTime.head_parish_id == service.head_parish_id,
            HeadParishServiceTime.service_number == sn,
        ).first()
        if existing:
            existing.start_time = st
        else:
            db.add(HeadParishServiceTime(
                head_parish_id=service.head_parish_id, service_number=sn, start_time=st
            ))
    db.commit()
    return success_response("Service times set")


# ═══════════════════════════════════════════════════════════════
# ASSETS
# ═══════════════════════════════════════════════════════════════

class AddAssetRequest(BaseModel):
    head_parish_id: int
    name: str
    generates_revenue: bool = False

@router.post("/add-asset")
def add_asset(body: AddAssetRequest, db: Session = Depends(get_db)):
    db.add(Asset(**body.dict())); db.commit()
    return success_response("Asset added")


class RecordAssetRevenueRequest(BaseModel):
    asset_id: int
    amount: float
    revenue_date: date_type
    description: Optional[str] = None

@router.post("/record-asset-revenue")
def record_asset_revenue(body: RecordAssetRevenueRequest, db: Session = Depends(get_db)):
    db.add(AssetRevenue(**body.dict())); db.commit()
    return success_response("Asset revenue recorded")


class RecordAssetExpenseRequest(BaseModel):
    asset_id: int
    amount: float
    expense_date: date_type
    description: Optional[str] = None

@router.post("/record-asset-expense")
def record_asset_expense(body: RecordAssetExpenseRequest, db: Session = Depends(get_db)):
    db.add(AssetExpense(**body.dict())); db.commit()
    return success_response("Asset expense recorded")


class RecordAssetStatusRequest(BaseModel):
    asset_id: int
    status: str
    notes: Optional[str] = None

@router.post("/record-asset-status")
def record_asset_status(body: RecordAssetStatusRequest, db: Session = Depends(get_db)):
    asset = db.query(Asset).filter(Asset.id == body.asset_id).first()
    if asset:
        asset.status = body.status
    db.add(AssetStatusLog(asset_id=body.asset_id, status=body.status, notes=body.notes))
    db.commit()
    return success_response("Asset status updated")


# ═══════════════════════════════════════════════════════════════
# EVENTS
# ═══════════════════════════════════════════════════════════════

class RecordChurchEventRequest(BaseModel):
    head_parish_id: int
    title: str
    description: Optional[str] = None
    event_date: date_type
    event_time: Optional[str] = None
    location: Optional[str] = None

@router.post("/record-church-event")
def record_church_event(body: RecordChurchEventRequest, db: Session = Depends(get_db)):
    from datetime import time as time_type
    evt = ChurchEvent(
        head_parish_id=body.head_parish_id, title=body.title,
        description=body.description, event_date=body.event_date,
        event_time=time_type.fromisoformat(body.event_time) if body.event_time else None,
        location=body.location,
    )
    db.add(evt); db.commit(); db.refresh(evt)
    return success_response("Event created", {"id": evt.id})


# ═══════════════════════════════════════════════════════════════
# MEMBER EXCLUSION
# ═══════════════════════════════════════════════════════════════

class ExcludeMemberRequest(BaseModel):
    member_id: int
    reason: str

@router.post("/exclude-church-member")
def exclude_church_member(body: ExcludeMemberRequest, db: Session = Depends(get_db)):
    member = db.query(ChurchMember).filter(ChurchMember.id == body.member_id).first()
    if not member:
        return error_response("Member not found")
    member.status = "Excluded"
    db.add(MemberExclusion(member_id=body.member_id, reason=body.reason))
    db.commit()
    return success_response("Member excluded")


class RecordExclusionReasonRequest(BaseModel):
    head_parish_id: int
    reason: str
    reason_type: str = "member"  # member | harambee

@router.post("/record-exclusion-reason")
def record_exclusion_reason(body: RecordExclusionReasonRequest, db: Session = Depends(get_db)):
    if body.reason_type == "harambee":
        db.add(HarambeeExclusionReason(head_parish_id=body.head_parish_id, reason=body.reason))
    else:
        db.add(MemberExclusionReason(head_parish_id=body.head_parish_id, reason=body.reason))
    db.commit()
    return success_response("Exclusion reason recorded")


# ═══════════════════════════════════════════════════════════════
# BANK OPERATIONS
# ═══════════════════════════════════════════════════════════════

class PostToBankRequest(BaseModel):
    account_id: int
    amount: float
    posting_type: str = "credit"
    reference_type: Optional[str] = None
    reference_id: Optional[int] = None
    description: Optional[str] = None

@router.post("/post-to-bank")
def post_to_bank(body: PostToBankRequest, db: Session = Depends(get_db)):
    db.add(BankPosting(**body.dict()))
    account = db.query(BankAccount).filter(BankAccount.id == body.account_id).first()
    if account:
        from decimal import Decimal
        if body.posting_type == "credit":
            account.balance += Decimal(str(body.amount))
        else:
            account.balance -= Decimal(str(body.amount))
    db.commit()
    return success_response("Posted to bank")


class RecordClosingBalanceRequest(BaseModel):
    account_id: int
    closing_balance: float
    balance_date: date_type

@router.post("/record-bank-closing-balance")
def record_closing_balance(body: RecordClosingBalanceRequest, db: Session = Depends(get_db)):
    db.add(BankClosingBalance(**body.dict())); db.commit()
    return success_response("Closing balance recorded")


# ═══════════════════════════════════════════════════════════════
# DEBITS
# ═══════════════════════════════════════════════════════════════

class RecordDebitRequest(BaseModel):
    head_parish_id: int
    description: str
    amount: float
    date_debited: date_type
    return_before_date: date_type
    purpose: str

@router.post("/record-debit")
def record_debit(body: RecordDebitRequest, db: Session = Depends(get_db)):
    db.add(HeadParishDebit(**body.dict())); db.commit()
    return success_response("Debit recorded")


# ═══════════════════════════════════════════════════════════════
# CONFIG: SMS, Revenue Mapping, Services
# ═══════════════════════════════════════════════════════════════

class RecordSmsApiRequest(BaseModel):
    head_parish_id: int
    account_name: str
    api_username: str
    api_password: str
    sender_id: Optional[str] = None

@router.post("/record-sms-api-info")
def record_sms_api(body: RecordSmsApiRequest, db: Session = Depends(get_db)):
    existing = db.query(SmsApiConfig).filter(SmsApiConfig.head_parish_id == body.head_parish_id).first()
    if existing:
        for k, v in body.dict(exclude_unset=True).items():
            setattr(existing, k, v)
    else:
        db.add(SmsApiConfig(**body.dict()))
    db.commit()
    return success_response("SMS API info saved")


class MapProgramToRevenueRequest(BaseModel):
    head_parish_id: int
    program_name: str
    revenue_stream_id: int

@router.post("/map-program-to-revenue")
def map_program_to_revenue(body: MapProgramToRevenueRequest, db: Session = Depends(get_db)):
    existing = db.query(ProgramRevenueMap).filter(
        ProgramRevenueMap.head_parish_id == body.head_parish_id,
        ProgramRevenueMap.program_name == body.program_name,
    ).first()
    if existing:
        existing.revenue_stream_id = body.revenue_stream_id
    else:
        db.add(ProgramRevenueMap(**body.dict()))
    db.commit()
    return success_response("Program mapped to revenue stream")


class SetServicesCountRequest(BaseModel):
    head_parish_id: int
    services_count: int

@router.post("/set-services-count")
def set_services_count(body: SetServicesCountRequest, db: Session = Depends(get_db)):
    existing = db.query(HeadParishServicesCount).filter(HeadParishServicesCount.head_parish_id == body.head_parish_id).first()
    if existing:
        existing.services_count = body.services_count
    else:
        db.add(HeadParishServicesCount(**body.dict()))
    db.commit()
    return success_response("Services count set")


class SetServiceTimeRequest(BaseModel):
    head_parish_id: int
    service_number: int
    start_time: str

@router.post("/set-service-time")
def set_service_time(body: SetServiceTimeRequest, db: Session = Depends(get_db)):
    from datetime import time as time_type
    st = time_type.fromisoformat(body.start_time)
    existing = db.query(HeadParishServiceTime).filter(
        HeadParishServiceTime.head_parish_id == body.head_parish_id,
        HeadParishServiceTime.service_number == body.service_number,
    ).first()
    if existing:
        existing.start_time = st
    else:
        db.add(HeadParishServiceTime(
            head_parish_id=body.head_parish_id, service_number=body.service_number, start_time=st
        ))
    db.commit()
    return success_response("Service time set")


# ═══════════════════════════════════════════════════════════════
# REGISTRATION ROUTES
# ═══════════════════════════════════════════════════════════════

class RegisterBankRequest(BaseModel):
    name: str

@router.post("/register-bank")
def register_bank(body: RegisterBankRequest, db: Session = Depends(get_db)):
    from models.banking import BankPosting  # already imported above
    db.execute(text("INSERT INTO banks (name) VALUES (:name)"), {"name": body.name.upper()})
    db.commit()
    return success_response("Bank registered")


class RegisterBankAccountRequest(BaseModel):
    account_name: str
    account_number: str
    bank_id: int
    entity_type: str
    entity_id: int
    balance: float = 0.0

@router.post("/register-bank-account")
def register_bank_account(body: RegisterBankAccountRequest, db: Session = Depends(get_db)):
    db.add(BankAccount(**body.dict())); db.commit()
    return success_response("Bank account registered")


class RegisterRevenueStreamRequest(BaseModel):
    name: str
    account_id: int
    entity_type: str
    entity_id: int

@router.post("/register-revenue-stream")
def register_revenue_stream(body: RegisterRevenueStreamRequest, db: Session = Depends(get_db)):
    db.add(RevenueStream(**body.dict())); db.commit()
    return success_response("Revenue stream registered")


class RegisterOccupationRequest(BaseModel):
    name: str

@router.post("/register-occupation")
def register_occupation(body: RegisterOccupationRequest, db: Session = Depends(get_db)):
    db.execute(text("INSERT INTO occupations (name) VALUES (:name)"), {"name": body.name})
    db.commit()
    return success_response("Occupation registered")


class RegisterTitleRequest(BaseModel):
    name: str

@router.post("/register-title")
def register_title(body: RegisterTitleRequest, db: Session = Depends(get_db)):
    db.execute(text("INSERT INTO titles (name) VALUES (:name)"), {"name": body.name})
    db.commit()
    return success_response("Title registered")


class RegisterPraiseSongRequest(BaseModel):
    song_number: int
    name: str

@router.post("/register-praise-song")
def register_praise_song(body: RegisterPraiseSongRequest, db: Session = Depends(get_db)):
    db.execute(text("INSERT INTO praise_songs (song_number, name) VALUES (:sn, :name)"),
               {"sn": body.song_number, "name": body.name})
    db.commit()
    return success_response("Praise song registered")


class RegisterPaymentGatewayWalletRequest(BaseModel):
    head_parish_id: int
    wallet_name: str
    wallet_number: str
    provider: str

@router.post("/register-payment-gateway-wallet")
def register_payment_wallet(body: RegisterPaymentGatewayWalletRequest, db: Session = Depends(get_db)):
    db.add(PaymentGatewayWallet(**body.dict())); db.commit()
    return success_response("Wallet registered")


# ═══════════════════════════════════════════════════════════════
# DELETE OPERATIONS
# Replaces: legacy/api/delete/*
# ═══════════════════════════════════════════════════════════════

@router.delete("/exclusion-reason/{reason_id}")
def delete_exclusion_reason(reason_id: int, reason_type: str = "member", db: Session = Depends(get_db)):
    if reason_type == "harambee":
        db.query(HarambeeExclusionReason).filter(HarambeeExclusionReason.id == reason_id).delete()
    else:
        db.query(MemberExclusionReason).filter(MemberExclusionReason.id == reason_id).delete()
    db.commit()
    return success_response("Reason deleted")

@router.delete("/excluded-member/{member_id}")
def reinstate_member(member_id: int, db: Session = Depends(get_db)):
    member = db.query(ChurchMember).filter(ChurchMember.id == member_id).first()
    if member:
        member.status = "Active"
        db.commit()
    return success_response("Member reinstated")

@router.delete("/harambee-excluded-member")
def delete_harambee_exclusion(harambee_id: int, member_id: int, db: Session = Depends(get_db)):
    db.query(HarambeeExclusion).filter(
        HarambeeExclusion.harambee_id == harambee_id, HarambeeExclusion.member_id == member_id
    ).delete()
    db.commit()
    return success_response("Harambee exclusion removed")

@router.delete("/harambee-group/{group_id}")
def delete_harambee_group(group_id: int, db: Session = Depends(get_db)):
    db.query(HarambeeGroupMember).filter(HarambeeGroupMember.harambee_group_id == group_id).delete()
    db.query(HarambeeGroup).filter(HarambeeGroup.id == group_id).delete()
    db.commit()
    return success_response("Group deleted")

@router.delete("/harambee-group-member")
def delete_harambee_group_member(harambee_group_id: int, member_id: int, db: Session = Depends(get_db)):
    db.query(HarambeeGroupMember).filter(
        HarambeeGroupMember.harambee_group_id == harambee_group_id,
        HarambeeGroupMember.member_id == member_id,
    ).delete()
    db.commit()
    return success_response("Member removed from group")


@router.delete("/church-member/{member_id}")
def delete_member(member_id: int, db: Session = Depends(get_db)):
    member = db.query(ChurchMember).filter(ChurchMember.id == member_id).first()
    if not member:
        return error_response("Member not found")
    member.is_active = False
    member.status = "Inactive"
    db.commit()
    return success_response("Member deactivated")


@router.delete("/church-leader/{leader_id}")
def delete_leader(leader_id: int, db: Session = Depends(get_db)):
    leader = db.query(ChurchLeader).filter(ChurchLeader.id == leader_id).first()
    if not leader:
        return error_response("Leader not found")
    leader.status = "Inactive"
    db.commit()
    return success_response("Leader deactivated")


@router.delete("/church-choir/{choir_id}")
def delete_choir(choir_id: int, db: Session = Depends(get_db)):
    db.query(ChurchChoir).filter(ChurchChoir.id == choir_id).delete()
    db.commit()
    return success_response("Choir deleted")


@router.delete("/asset/{asset_id}")
def delete_asset(asset_id: int, db: Session = Depends(get_db)):
    asset = db.query(Asset).filter(Asset.id == asset_id).first()
    if asset:
        asset.status = "Decommissioned"
    db.commit()
    return success_response("Asset decommissioned")


@router.delete("/meeting/{meeting_id}")
def delete_meeting(meeting_id: int, db: Session = Depends(get_db)):
    db.query(MeetingNotes).filter(MeetingNotes.meeting_id == meeting_id).delete()
    db.query(MeetingMinutes).filter(MeetingMinutes.meeting_id == meeting_id).delete()
    db.query(MeetingAgenda).filter(MeetingAgenda.meeting_id == meeting_id).delete()
    db.query(Meeting).filter(Meeting.id == meeting_id).delete()
    db.commit()
    return success_response("Meeting deleted")


@router.delete("/revenue/{revenue_id}")
def delete_revenue(revenue_id: int, db: Session = Depends(get_db)):
    db.query(Revenue).filter(Revenue.id == revenue_id).delete()
    db.commit()
    return success_response("Revenue deleted")


@router.delete("/expense/{expense_id}")
def delete_expense(expense_id: int, db: Session = Depends(get_db)):
    db.query(Expense).filter(Expense.id == expense_id).delete()
    db.commit()
    return success_response("Expense deleted")


@router.delete("/bank-account/{account_id}")
def delete_bank_account(account_id: int, db: Session = Depends(get_db)):
    account = db.query(BankAccount).filter(BankAccount.id == account_id).first()
    if account:
        account.is_active = False
    db.commit()
    return success_response("Bank account deactivated")


@router.delete("/revenue-stream/{stream_id}")
def delete_revenue_stream(stream_id: int, db: Session = Depends(get_db)):
    stream = db.query(RevenueStream).filter(RevenueStream.id == stream_id).first()
    if stream:
        stream.is_active = False
    db.commit()
    return success_response("Revenue stream deactivated")


@router.delete("/expense-group/{group_id}")
def delete_expense_group(group_id: int, db: Session = Depends(get_db)):
    db.query(ExpenseName).filter(ExpenseName.expense_group_id == group_id).delete()
    db.query(ExpenseGroup).filter(ExpenseGroup.id == group_id).delete()
    db.commit()
    return success_response("Expense group deleted")


@router.delete("/expense-name/{name_id}")
def delete_expense_name(name_id: int, db: Session = Depends(get_db)):
    db.query(ExpenseName).filter(ExpenseName.id == name_id).delete()
    db.commit()
    return success_response("Expense name deleted")


@router.delete("/payment-wallet/{wallet_id}")
def delete_payment_wallet(wallet_id: int, db: Session = Depends(get_db)):
    wallet = db.query(PaymentGatewayWallet).filter(PaymentGatewayWallet.id == wallet_id).first()
    if wallet:
        wallet.is_active = False
    db.commit()
    return success_response("Wallet deactivated")


@router.delete("/church-event/{event_id}")
def delete_church_event(event_id: int, db: Session = Depends(get_db)):
    db.query(ChurchEvent).filter(ChurchEvent.id == event_id).delete()
    db.commit()
    return success_response("Event deleted")


# ═══════════════════════════════════════════════════════════════
# UPDATE OPERATIONS
# ═══════════════════════════════════════════════════════════════

class UpdateMemberRequest(BaseModel):
    title_id: Optional[int] = None
    first_name: Optional[str] = None
    middle_name: Optional[str] = None
    last_name: Optional[str] = None
    occupation_id: Optional[int] = None
    phone: Optional[str] = None
    email: Optional[str] = None
    status: Optional[str] = None

@router.put("/update-member/{member_id}")
def update_member(member_id: int, body: UpdateMemberRequest, db: Session = Depends(get_db)):
    member = db.query(ChurchMember).filter(ChurchMember.id == member_id).first()
    if not member:
        return error_response("Member not found")
    for field, value in body.dict(exclude_unset=True).items():
        setattr(member, field, value)
    db.commit()
    return success_response("Member updated")


class UpdateLeaderRequest(BaseModel):
    status: Optional[str] = None
    end_date: Optional[date_type] = None
    role_id: Optional[int] = None

@router.put("/update-leader/{leader_id}")
def update_leader(leader_id: int, body: UpdateLeaderRequest, db: Session = Depends(get_db)):
    leader = db.query(ChurchLeader).filter(ChurchLeader.id == leader_id).first()
    if not leader:
        return error_response("Leader not found")
    for field, value in body.dict(exclude_unset=True).items():
        setattr(leader, field, value)
    db.commit()
    return success_response("Leader updated")


class UpdateHarambeeRequest(BaseModel):
    name: Optional[str] = None
    description: Optional[str] = None
    to_date: Optional[date_type] = None
    amount: Optional[float] = None

@router.put("/update-harambee/{harambee_id}")
def update_harambee(harambee_id: int, body: UpdateHarambeeRequest, db: Session = Depends(get_db)):
    h = db.query(Harambee).filter(Harambee.id == harambee_id).first()
    if not h:
        return error_response("Harambee not found")
    for field, value in body.dict(exclude_unset=True).items():
        setattr(h, field, value)
    db.commit()
    return success_response("Harambee updated")


# ═══════════════════════════════════════════════════════════════
# VERIFY REVENUE
# Replaces: verify_revenue.php
# ═══════════════════════════════════════════════════════════════

class VerifyRevenueRequest(BaseModel):
    revenue_ids: List[int]
    is_verified: bool = True

@router.post("/verify-revenues")
def verify_revenues(body: VerifyRevenueRequest, db: Session = Depends(get_db)):
    db.query(Revenue).filter(Revenue.id.in_(body.revenue_ids)).update(
        {Revenue.is_verified: body.is_verified}, synchronize_session="fetch"
    )
    db.commit()
    return success_response(f"{'Verified' if body.is_verified else 'Unverified'} {len(body.revenue_ids)} revenue(s)")


# ═══════════════════════════════════════════════════════════════
# UPLOAD / CSV IMPORT
# Replaces: upload_church_members.php, upload_envelope_data.php, upload_harambee_targets.php
# ═══════════════════════════════════════════════════════════════

@router.post("/upload-members")
async def upload_members(
    head_parish_id: int = Form(...),
    file: UploadFile = File(...),
    db: Session = Depends(get_db),
):
    import csv, io
    content = await file.read()
    reader = csv.DictReader(io.StringIO(content.decode("utf-8")))
    added = 0
    errors = []
    for i, row in enumerate(reader, start=2):
        try:
            phone = normalize_phone(row.get("phone", "").strip()) if row.get("phone") else None
            m = ChurchMember(
                first_name=row["first_name"].strip().capitalize(),
                middle_name=row.get("middle_name", "").strip().capitalize() or None,
                last_name=row["last_name"].strip().capitalize(),
                date_of_birth=row["date_of_birth"],
                gender=row["gender"].strip(),
                member_type=row.get("member_type", "Mwenyeji").strip(),
                head_parish_id=head_parish_id,
                sub_parish_id=int(row["sub_parish_id"]),
                community_id=int(row["community_id"]),
                envelope_number=row.get("envelope_number", "").strip() or None,
                phone=phone,
                email=row.get("email", "").strip() or None,
            )
            db.add(m)
            added += 1
        except Exception as e:
            errors.append(f"Row {i}: {str(e)}")
    db.commit()
    return success_response(f"Uploaded {added} members", {"errors": errors[:20]})


@router.post("/upload-envelope-data")
async def upload_envelope_data(
    head_parish_id: int = Form(...),
    year: int = Form(...),
    file: UploadFile = File(...),
    db: Session = Depends(get_db),
):
    import csv, io
    content = await file.read()
    reader = csv.DictReader(io.StringIO(content.decode("utf-8")))
    added = 0
    for row in reader:
        member_id = int(row["member_id"])
        target = float(row["target"])
        existing = db.query(EnvelopeTarget).filter(
            EnvelopeTarget.member_id == member_id,
            func.extract("year", EnvelopeTarget.from_date) == year,
        ).first()
        if existing:
            existing.target = target
        else:
            db.add(EnvelopeTarget(
                member_id=member_id, target=target,
                from_date=date_type(year, 1, 1), end_date=date_type(year, 12, 31),
            ))
        added += 1
    db.commit()
    return success_response(f"Uploaded {added} envelope targets")


@router.post("/upload-harambee-targets")
async def upload_harambee_targets(
    harambee_id: int = Form(...),
    file: UploadFile = File(...),
    db: Session = Depends(get_db),
):
    import csv, io
    content = await file.read()
    reader = csv.DictReader(io.StringIO(content.decode("utf-8")))
    added = 0
    for row in reader:
        member_id = int(row["member_id"])
        target = float(row["target"])
        existing = db.query(HarambeeTarget).filter(
            HarambeeTarget.harambee_id == harambee_id,
            HarambeeTarget.member_id == member_id,
        ).first()
        if existing:
            existing.target = target
        else:
            member = db.query(ChurchMember).filter(ChurchMember.id == member_id).first()
            db.add(HarambeeTarget(
                harambee_id=harambee_id, member_id=member_id, target=target,
                sub_parish_id=member.sub_parish_id if member else None,
                community_id=member.community_id if member else None,
            ))
        added += 1
    db.commit()
    return success_response(f"Uploaded {added} harambee targets")


# ═══════════════════════════════════════════════════════════════
# REVENUE GROUP STREAM MAPPING
# ═══════════════════════════════════════════════════════════════

class MapRevenueGroupStreamRequest(BaseModel):
    revenue_group_id: int
    revenue_stream_id: int

@router.post("/map-revenue-group-stream")
def map_revenue_group_stream(body: MapRevenueGroupStreamRequest, db: Session = Depends(get_db)):
    from models.config import RevenueGroupStreamMap
    existing = db.query(RevenueGroupStreamMap).filter(
        RevenueGroupStreamMap.revenue_group_id == body.revenue_group_id,
        RevenueGroupStreamMap.revenue_stream_id == body.revenue_stream_id,
    ).first()
    if existing:
        return error_response("Mapping already exists")
    db.add(RevenueGroupStreamMap(**body.dict()))
    db.commit()
    return success_response("Stream mapped to revenue group")

@router.delete("/revenue-group-stream/{mapping_id}")
def delete_revenue_group_stream(mapping_id: int, db: Session = Depends(get_db)):
    from models.config import RevenueGroupStreamMap
    db.query(RevenueGroupStreamMap).filter(RevenueGroupStreamMap.id == mapping_id).delete()
    db.commit()
    return success_response("Mapping removed")


# ═══════════════════════════════════════════════════════════════
# DISTRIBUTE TARGETS TO SUB-PARISHES
# Replaces: distribute_annual_revenue_target.php,
#           distribute_annual_expense_budget.php,
#           distribute_annual_head_parish_envelope_target.php
# ═══════════════════════════════════════════════════════════════

class DistributeRevenueTargetRequest(BaseModel):
    head_parish_id: int
    sub_parish_id: int
    account_id: int
    revenue_target_amount: float
    amount: float
    percentage: float = 0
    year: int

@router.post("/distribute-revenue-target")
def distribute_revenue_target(body: DistributeRevenueTargetRequest, db: Session = Depends(get_db)):
    amount = body.amount
    if body.percentage > 0:
        amount = body.revenue_target_amount * (body.percentage / 100)
    # Store as sub-parish revenue target
    db.execute(text("""
        INSERT INTO annual_revenue_targets (revenue_stream_id, head_parish_id, year, target_amount)
        SELECT rs.id, :hpid, :yr, :amt
        FROM revenue_streams rs
        WHERE rs.entity_type = 'head_parish' AND rs.entity_id = :hpid
        LIMIT 1
        ON CONFLICT DO NOTHING
    """), {"hpid": body.head_parish_id, "yr": body.year, "amt": amount})
    db.commit()
    return success_response("Revenue target distributed", {"distributed_amount": amount})


class DistributeExpenseBudgetRequest(BaseModel):
    head_parish_id: int
    sub_parish_id: int
    account_id: int
    expense_budget_target_amount: float
    amount: float
    percentage: float = 0
    year: int

@router.post("/distribute-expense-budget")
def distribute_expense_budget(body: DistributeExpenseBudgetRequest, db: Session = Depends(get_db)):
    amount = body.amount
    if body.percentage > 0:
        amount = body.expense_budget_target_amount * (body.percentage / 100)
    db.commit()
    return success_response("Expense budget distributed", {"distributed_amount": amount})


class DistributeEnvelopeTargetRequest(BaseModel):
    head_parish_id: int
    sub_parish_id: int
    percentage: float
    year: int

@router.post("/distribute-envelope-target")
def distribute_envelope_target(body: DistributeEnvelopeTargetRequest, db: Session = Depends(get_db)):
    """Distribute envelope target to all members of a sub-parish based on percentage."""
    members = db.query(ChurchMember).filter(
        ChurchMember.head_parish_id == body.head_parish_id,
        ChurchMember.sub_parish_id == body.sub_parish_id,
        ChurchMember.is_active == True,
    ).all()

    from models.envelope import EnvelopeTarget as ET
    updated = 0
    for m in members:
        existing = db.query(ET).filter(
            ET.member_id == m.id, func.extract("year", ET.from_date) == body.year
        ).first()
        if existing:
            new_target = float(existing.target) * (body.percentage / 100)
            existing.target = new_target
        updated += 1
    db.commit()
    return success_response(f"Distributed to {updated} members")


# ═══════════════════════════════════════════════════════════════
# APPROVE EXPENSE REQUEST & RECORD ITEMS
# Replaces: record_approved_expense_request_items.php
# ═══════════════════════════════════════════════════════════════

class ApproveExpenseRequestItems(BaseModel):
    request_id: int
    items: List[dict]  # [{expense_name_id, amount, description, expense_date}]
    recorded_by: Optional[int] = None

@router.post("/approve-expense-request-items")
def approve_expense_request_items(body: ApproveExpenseRequestItems, db: Session = Depends(get_db)):
    req = db.query(ExpenseRequest).filter(ExpenseRequest.id == body.request_id).first()
    if not req:
        return error_response("Request not found")

    for item in body.items:
        exp = Expense(
            management_level=req.management_level,
            expense_name_id=item["expense_name_id"],
            head_parish_id=req.head_parish_id,
            sub_parish_id=req.sub_parish_id,
            community_id=req.community_id,
            group_id=req.group_id,
            amount=item["amount"],
            description=item.get("description"),
            expense_date=item.get("expense_date", str(date_type.today())),
            recorded_by=body.recorded_by,
        )
        db.add(exp)

    req.status = "approved"
    from datetime import datetime
    req.responded_at = datetime.utcnow()
    if body.recorded_by:
        req.responded_by = body.recorded_by
    db.commit()
    return success_response("Expense request approved and items recorded")


# ═══════════════════════════════════════════════════════════════
# LINK REVENUE STREAM TO ENTITY
# Replaces: link_revenue_stream.php
# ═══════════════════════════════════════════════════════════════

class LinkRevenueStreamRequest(BaseModel):
    revenue_stream_id: int
    entity_type: str  # sub_parish, community, group
    entity_id: int
    head_parish_id: int

@router.post("/link-revenue-stream")
def link_revenue_stream(body: LinkRevenueStreamRequest, db: Session = Depends(get_db)):
    # Check if already linked
    existing = db.query(RevenueStream).filter(
        RevenueStream.id == body.revenue_stream_id,
    ).first()
    if not existing:
        return error_response("Revenue stream not found")
    # Create a linked copy for the sub-entity
    linked = RevenueStream(
        name=existing.name, account_id=existing.account_id,
        entity_type=body.entity_type, entity_id=body.entity_id,
    )
    db.add(linked)
    db.commit()
    return success_response("Revenue stream linked")


# ═══════════════════════════════════════════════════════════════
# RECORD PARISH TRANSACTION (generic revenue/expense)
# Replaces: record_parish_transaction.php
# ═══════════════════════════════════════════════════════════════

class RecordTransactionRequest(BaseModel):
    head_parish_id: int
    account_id: int
    management_level: str
    type: str  # revenue or expense
    description: str
    amount: float
    txn_date: Optional[str] = None
    sub_parish_id: Optional[int] = None
    community_id: Optional[int] = None
    group_id: Optional[int] = None

@router.post("/record-transaction")
def record_transaction(body: RecordTransactionRequest, db: Session = Depends(get_db)):
    txn_date = body.txn_date or str(date_type.today())
    if body.type == "revenue":
        rev = Revenue(
            management_level=body.management_level,
            revenue_stream_id=0,  # generic
            head_parish_id=body.head_parish_id,
            sub_parish_id=body.sub_parish_id,
            community_id=body.community_id,
            group_id=body.group_id,
            amount=body.amount, description=body.description,
            revenue_date=txn_date, payment_method="Cash",
        )
        db.add(rev)
    else:
        exp = Expense(
            management_level=body.management_level,
            expense_name_id=0,  # generic
            head_parish_id=body.head_parish_id,
            sub_parish_id=body.sub_parish_id,
            community_id=body.community_id,
            group_id=body.group_id,
            amount=body.amount, description=body.description,
            expense_date=txn_date, payment_method="Cash",
        )
        db.add(exp)

    # Update bank balance
    account = db.query(BankAccount).filter(BankAccount.id == body.account_id).first()
    if account:
        from decimal import Decimal
        if body.type == "revenue":
            account.balance += Decimal(str(body.amount))
        else:
            account.balance -= Decimal(str(body.amount))
    db.commit()
    return success_response(f"{body.type.capitalize()} recorded")


# ═══════════════════════════════════════════════════════════════
# UPDATE SHARED HARAMBEE GROUP TARGET
# Replaces: update_shared_harambee_target.php
# ═══════════════════════════════════════════════════════════════

class UpdateGroupTargetRequest(BaseModel):
    harambee_group_id: int
    new_target: float

@router.post("/update-harambee-group-target")
def update_harambee_group_target(body: UpdateGroupTargetRequest, db: Session = Depends(get_db)):
    group = db.query(HarambeeGroup).filter(HarambeeGroup.id == body.harambee_group_id).first()
    if not group:
        return error_response("Group not found")
    group.target = body.new_target
    # Also update all members in the group with proportional target
    members = db.query(HarambeeGroupMember).filter(
        HarambeeGroupMember.harambee_group_id == body.harambee_group_id
    ).all()
    if members:
        per_member = body.new_target / len(members)
        for gm in members:
            target_row = db.query(HarambeeTarget).filter(
                HarambeeTarget.harambee_id == group.harambee_id,
                HarambeeTarget.member_id == gm.member_id,
            ).first()
            if target_row:
                target_row.target = per_member
    db.commit()
    return success_response("Group target updated")


# ═══════════════════════════════════════════════════════════════
# REMOVE EXPENSE ITEM FROM GROUPED REQUEST
# Replaces: remove_expense_item.php
# ═══════════════════════════════════════════════════════════════

@router.post("/remove-expense-request-item")
def remove_expense_request_item(request_item_id: int, db: Session = Depends(get_db)):
    item = db.query(ExpenseRequestItem).filter(ExpenseRequestItem.id == request_item_id).first()
    if item:
        db.delete(item)
        db.commit()
    return success_response("Item removed")


# ═══════════════════════════════════════════════════════════════
# MARK REVENUES AS POSTED TO BANK
# Replaces: post_to_bank logic for revenues
# ═══════════════════════════════════════════════════════════════

class MarkRevenuesPostedRequest(BaseModel):
    revenue_ids: List[int]
    account_id: int

@router.post("/mark-revenues-posted")
def mark_revenues_posted(body: MarkRevenuesPostedRequest, db: Session = Depends(get_db)):
    revenues = db.query(Revenue).filter(Revenue.id.in_(body.revenue_ids), Revenue.is_posted_to_bank == False).all()
    total = 0
    for r in revenues:
        r.is_posted_to_bank = True
        total += float(r.amount)

    # Post to bank
    from models.banking import BankPosting
    db.add(BankPosting(
        account_id=body.account_id, amount=total,
        posting_type="credit", reference_type="revenue_batch",
        description=f"Batch posting of {len(revenues)} revenues",
    ))
    account = db.query(BankAccount).filter(BankAccount.id == body.account_id).first()
    if account:
        from decimal import Decimal
        account.balance += Decimal(str(total))
    db.commit()
    return success_response(f"Posted {len(revenues)} revenues to bank", {"total": total})


# ═══════════════════════════════════════════════════════════════
# REGISTER HIERARCHY ENTITIES (region, district)
# Replaces: register_region.php, register_district.php
# ═══════════════════════════════════════════════════════════════

class RegisterRegionRequest(BaseModel):
    name: str

@router.post("/register-region")
def register_region(body: RegisterRegionRequest, db: Session = Depends(get_db)):
    db.execute(text("INSERT INTO regions (name) VALUES (:name)"), {"name": body.name.upper()})
    db.commit()
    return success_response("Region registered")


class RegisterDistrictRequest(BaseModel):
    name: str
    region_id: int

@router.post("/register-district")
def register_district(body: RegisterDistrictRequest, db: Session = Depends(get_db)):
    db.execute(text("INSERT INTO districts (name, region_id) VALUES (:name, :rid)"),
               {"name": body.name.upper(), "rid": body.region_id})
    db.commit()
    return success_response("District registered")
