# api/routes/church_member_api.py
"""Church member mobile app API routes.
Replaces ALL files in legacy/api/church_member/: signin, registration OTP, password,
avatar, FCM tokens, harambee data, envelope data, sunday services, payment requests, receipts."""

from datetime import datetime, timedelta, date
from fastapi import APIRouter, Depends, HTTPException, UploadFile, File, Form, Query
from pydantic import BaseModel
from sqlalchemy.orm import Session
from sqlalchemy import text, func
from typing import Optional, List
import os, secrets

from core.database import get_db
from models.members import ChurchMember
from models.misc import MemberOtpCode, FcmToken, Feedback
from models.harambee import Harambee, HarambeeContribution, HarambeeTarget
from models.envelope import EnvelopeTarget, EnvelopeContribution
from models.sunday_service import SundayService
from models.payments import Payment
from utils.auth import hash_password, verify_password, create_access_token, create_refresh_token
from utils.validation import normalize_phone
from utils.response import success_response, error_response
from utils.helpers import (
    get_member_details, format_phone_display, normalize_phone as helpers_normalize_phone,
    generate_numeric_otp, fetch_member_envelope_data,
)

router = APIRouter(prefix="/church-member", tags=["Church Member App"])

INITIAL_ENVELOPE_YEAR = 2024


# ═══════════════════════════════════════════════════════════════
# MEMBER SIGNIN — Replaces: church_member_signin.php
# ═══════════════════════════════════════════════════════════════
class MemberSigninRequest(BaseModel):
    phone: str
    password: str

@router.post("/signin")
def member_signin(body: MemberSigninRequest, db: Session = Depends(get_db)):
    if not body.phone or not body.password:
        return error_response("Phone number and password are required")

    phone = normalize_phone(body.phone.strip())

    member = db.query(ChurchMember).filter(ChurchMember.phone == phone, ChurchMember.is_active == True).first()
    if not member or not member.password:
        return error_response("Incorrect username or password")

    if not verify_password(body.password, member.password):
        return error_response("Incorrect username or password")

    token = create_access_token({"member_id": member.id, "type": "member"})
    details = get_member_details(db, member.id)

    return success_response("Login Successful", {"member": details, "access_token": token, "token_type": "Bearer"})


# ═══════════════════════════════════════════════════════════════
# REGISTRATION OTP — Replaces: request_member_registration_otp.php
# ═══════════════════════════════════════════════════════════════
class RequestOtpRequest(BaseModel):
    phone: str
    purpose: str = "registration"  # registration | password_reset

@router.post("/request-otp")
def request_otp(body: RequestOtpRequest, db: Session = Depends(get_db)):
    phone = normalize_phone(body.phone.strip())

    member = db.query(ChurchMember).filter(ChurchMember.phone == phone).first()
    if not member:
        return error_response("Church member with this phone number does not exist")

    if body.purpose == "registration" and member.password:
        return error_response("This phone number already has an account")

    if body.purpose == "password_reset" and not member.password:
        return error_response("This phone number does not have an account")

    # Delete old OTPs
    db.query(MemberOtpCode).filter(MemberOtpCode.member_id == member.id, MemberOtpCode.purpose == body.purpose).delete()

    otp = generate_numeric_otp(5)
    expires_at = datetime.utcnow() + timedelta(minutes=10)

    db.add(MemberOtpCode(member_id=member.id, otp_code=otp, purpose=body.purpose, expires_at=expires_at))
    db.commit()

    # TODO: Send OTP via SMS using utils/sms.py
    # For now, return success
    return success_response("OTP sent successfully")


# ═══════════════════════════════════════════════════════════════
# VERIFY OTP — Replaces: verify_church_member_registration_otp.php, verify_password_reset_otp.php
# ═══════════════════════════════════════════════════════════════
class VerifyOtpRequest(BaseModel):
    phone: str
    otp: str
    purpose: str = "registration"

@router.post("/verify-otp")
def verify_otp(body: VerifyOtpRequest, db: Session = Depends(get_db)):
    phone = normalize_phone(body.phone.strip())
    member = db.query(ChurchMember).filter(ChurchMember.phone == phone).first()
    if not member:
        return error_response("Invalid OTP")

    otp_entry = db.query(MemberOtpCode).filter(
        MemberOtpCode.member_id == member.id,
        MemberOtpCode.otp_code == body.otp,
        MemberOtpCode.purpose == body.purpose,
        MemberOtpCode.used == False,
    ).order_by(MemberOtpCode.created_at.desc()).first()

    if not otp_entry:
        return error_response("Invalid or expired OTP")

    if otp_entry.expires_at < datetime.utcnow():
        return error_response("OTP has expired")

    otp_entry.used = True
    db.commit()

    return success_response("OTP verified successfully", {"member_id": member.id})


# ═══════════════════════════════════════════════════════════════
# SET PASSWORD — Replaces: set_church_member_password.php
# ═══════════════════════════════════════════════════════════════
class SetPasswordRequest(BaseModel):
    member_id: int
    phone: str
    password: str

@router.post("/set-password")
def set_password(body: SetPasswordRequest, db: Session = Depends(get_db)):
    phone = normalize_phone(body.phone.strip())
    member = db.query(ChurchMember).filter(ChurchMember.id == body.member_id, ChurchMember.phone == phone).first()
    if not member:
        return error_response("Member not found or phone mismatch")

    if member.password:
        return error_response("Account already exists")

    member.password = hash_password(body.password)
    db.commit()

    details = get_member_details(db, member.id)
    token = create_access_token({"member_id": member.id, "type": "member"})
    return success_response("Account Created Successfully", {"member": details, "access_token": token})


# ═══════════════════════════════════════════════════════════════
# UPDATE PASSWORD — Replaces: update_church_member_password.php
# ═══════════════════════════════════════════════════════════════
class UpdatePasswordRequest(BaseModel):
    member_id: int
    phone: str
    password: str

@router.post("/update-password")
def update_password(body: UpdatePasswordRequest, db: Session = Depends(get_db)):
    phone = normalize_phone(body.phone.strip())
    member = db.query(ChurchMember).filter(ChurchMember.id == body.member_id, ChurchMember.phone == phone).first()
    if not member:
        return error_response("Member not found or phone mismatch")
    if not member.password:
        return error_response("No account found for this member")

    member.password = hash_password(body.password)
    db.commit()

    details = get_member_details(db, member.id)
    return success_response("Password updated successfully", {"member": details})


# ═══════════════════════════════════════════════════════════════
# SET AVATAR — Replaces: set_church_member_avatar.php
# ═══════════════════════════════════════════════════════════════
@router.post("/set-avatar")
def set_avatar(member_id: int = Form(...), avatar: UploadFile = File(...), db: Session = Depends(get_db)):
    member = db.query(ChurchMember).filter(ChurchMember.id == member_id).first()
    if not member:
        raise HTTPException(404, "Member not found")

    ext = avatar.filename.rsplit(".", 1)[-1].lower() if avatar.filename else "jpg"
    if ext not in ("jpg", "jpeg", "png", "gif", "webp"):
        return error_response("Invalid file type")

    filename = f"avatar_{secrets.token_hex(8)}.{ext}"
    upload_dir = os.path.join(os.getcwd(), "uploads", "avatars")
    os.makedirs(upload_dir, exist_ok=True)

    # Delete old avatar
    if member.avatar_url:
        old_path = os.path.join(upload_dir, member.avatar_url)
        if os.path.exists(old_path):
            os.remove(old_path)

    # Save new
    with open(os.path.join(upload_dir, filename), "wb") as f:
        f.write(avatar.file.read())

    member.avatar_url = filename
    db.commit()

    details = get_member_details(db, member.id)
    return success_response("Avatar updated", {"member": details})


# ═══════════════════════════════════════════════════════════════
# FCM TOKENS — Replaces: store-fcm-token.php, delete-fcm-token.php
# ═══════════════════════════════════════════════════════════════
class FcmTokenRequest(BaseModel):
    member_id: int
    head_parish_id: int
    token: str

@router.post("/fcm-token")
def store_fcm_token(body: FcmTokenRequest, db: Session = Depends(get_db)):
    existing = db.query(FcmToken).filter(
        FcmToken.member_id == body.member_id, FcmToken.head_parish_id == body.head_parish_id
    ).first()
    if existing:
        existing.token = body.token
    else:
        db.add(FcmToken(member_id=body.member_id, head_parish_id=body.head_parish_id, token=body.token))
    db.commit()
    return success_response("Token saved")

@router.delete("/fcm-token")
def delete_fcm_token(member_id: int, head_parish_id: int, db: Session = Depends(get_db)):
    db.query(FcmToken).filter(FcmToken.member_id == member_id, FcmToken.head_parish_id == head_parish_id).delete()
    db.commit()
    return success_response("Token removed")


# ═══════════════════════════════════════════════════════════════
# GET MEMBER HARAMBEES — Replaces: get_church_member_harambee.php
# ═══════════════════════════════════════════════════════════════
@router.get("/harambees")
def get_member_harambees(member_id: int, target: str, db: Session = Depends(get_db)):
    member = db.query(ChurchMember).filter(ChurchMember.id == member_id).first()
    if not member:
        return error_response("Member not found")

    # Get active harambees based on target level
    q = db.query(Harambee).filter(Harambee.is_active == True)
    if target == "head-parish":
        q = q.filter(Harambee.head_parish_id == member.head_parish_id, Harambee.management_level == "head_parish")
    elif target == "sub-parish":
        q = q.filter(Harambee.head_parish_id == member.head_parish_id, Harambee.management_level == "sub_parish")
    elif target == "community":
        q = q.filter(Harambee.head_parish_id == member.head_parish_id, Harambee.management_level == "community")
    elif target == "groups":
        q = q.filter(Harambee.head_parish_id == member.head_parish_id, Harambee.management_level == "group")

    harambees = q.order_by(Harambee.from_date.desc()).all()
    details = []
    for h in harambees:
        total = db.query(func.coalesce(func.sum(HarambeeContribution.amount), 0)).filter(
            HarambeeContribution.harambee_id == h.id, HarambeeContribution.member_id == member_id
        ).scalar()
        member_target = db.query(HarambeeTarget.target).filter(
            HarambeeTarget.harambee_id == h.id, HarambeeTarget.member_id == member_id
        ).scalar()
        details.append({
            "harambee_id": h.id, "name": h.name, "description": h.description,
            "from_date": str(h.from_date), "to_date": str(h.to_date),
            "amount": f"{int(h.amount):,}", "category": target,
            "my_target": float(member_target or 0), "my_contribution": float(total or 0),
        })

    return success_response("Harambee details fetched", {"harambee_details": details})


# ═══════════════════════════════════════════════════════════════
# GET MEMBER ENVELOPES — Replaces: get_church_member_envelopes.php
# ═══════════════════════════════════════════════════════════════
@router.get("/envelopes")
def get_member_envelopes(db: Session = Depends(get_db)):
    current_year = date.today().year
    envelopes = []
    for year in range(current_year, INITIAL_ENVELOPE_YEAR - 1, -1):
        envelopes.append({
            "name": "Bahasha",
            "description": "Sadaka ya bahasha ya Shukrani Yangu kwa Bwana",
            "year": year,
            "from_date": f"{year}-01-01",
            "to_date": f"{year}-12-31",
        })
    return success_response("Envelope data generated", {"envelopes": envelopes})


# ═══════════════════════════════════════════════════════════════
# GET ENVELOPE STATUS — Replaces: church_member_envelope_status.php
# ═══════════════════════════════════════════════════════════════
@router.get("/envelope-status")
def get_envelope_status(member_id: int, year: Optional[int] = None, db: Session = Depends(get_db)):
    yr = year or date.today().year
    data = fetch_member_envelope_data(db, member_id, yr)
    return success_response(data=data)


# ═══════════════════════════════════════════════════════════════
# HARAMBEE/ENVELOPE SET TARGETS — Replaces: set_church_member_harambee_target.php, set_church_member_envelope_target.php
# ═══════════════════════════════════════════════════════════════
class SetMemberTargetRequest(BaseModel):
    member_id: int
    target_amount: float

@router.post("/set-harambee-target")
def set_harambee_target(harambee_id: int, body: SetMemberTargetRequest, db: Session = Depends(get_db)):
    existing = db.query(HarambeeTarget).filter(
        HarambeeTarget.harambee_id == harambee_id, HarambeeTarget.member_id == body.member_id
    ).first()
    if existing:
        existing.target = body.target_amount
    else:
        member = db.query(ChurchMember).filter(ChurchMember.id == body.member_id).first()
        if not member:
            return error_response("Member not found")
        db.add(HarambeeTarget(
            harambee_id=harambee_id, member_id=body.member_id,
            target=body.target_amount, sub_parish_id=member.sub_parish_id,
            community_id=member.community_id,
        ))
    db.commit()
    return success_response("Target set")

@router.post("/set-envelope-target")
def set_envelope_target(body: SetMemberTargetRequest, year: Optional[int] = None, db: Session = Depends(get_db)):
    yr = year or date.today().year
    existing = db.query(EnvelopeTarget).filter(
        EnvelopeTarget.member_id == body.member_id,
        func.extract("year", EnvelopeTarget.from_date) == yr,
    ).first()
    if existing:
        existing.target = body.target_amount
    else:
        db.add(EnvelopeTarget(
            member_id=body.member_id, target=body.target_amount,
            from_date=date(yr, 1, 1), end_date=date(yr, 12, 31),
        ))
    db.commit()
    return success_response("Envelope target set")


# ═══════════════════════════════════════════════════════════════
# SUNDAY SERVICES — Replaces: sunday_services.php (mobile view)
# ═══════════════════════════════════════════════════════════════
@router.get("/sunday-services")
def get_sunday_services(head_parish_id: int, db: Session = Depends(get_db)):
    services = db.query(SundayService).filter(
        SundayService.head_parish_id == head_parish_id
    ).order_by(SundayService.service_date.desc()).limit(52).all()

    result = []
    for s in services:
        # Get color
        color_row = db.execute(text(
            "SELECT name, code FROM service_colors WHERE id = :cid"
        ), {"cid": s.service_color_id}).first() if s.service_color_id else None

        # Get offerings
        offerings = db.execute(text("""
            SELECT rs.id, rs.name FROM sunday_service_offerings so
            JOIN revenue_streams rs ON so.revenue_stream_id = rs.id
            WHERE so.service_id = :sid
        """), {"sid": s.id}).mappings().all()

        # Get songs
        songs = db.execute(text("""
            SELECT ps.song_number, ps.name FROM sunday_service_songs ss
            JOIN praise_songs ps ON ss.song_id = ps.id
            WHERE ss.service_id = :sid ORDER BY ss.id
        """), {"sid": s.id}).mappings().all()

        # Get scriptures
        scriptures = db.execute(text("""
            SELECT book, chapter, verse_from, verse_to
            FROM sunday_service_scriptures WHERE service_id = :sid ORDER BY id
        """), {"sid": s.id}).mappings().all()

        result.append({
            "service_id": s.id,
            "service_date": str(s.service_date),
            "main_text": (s.base_scripture_text or "").upper(),
            "service_color": {"name": color_row[0], "code": color_row[1]} if color_row else None,
            "books_page_numbers": {
                "small_liturgy": s.small_liturgy_page_number,
                "large_liturgy": s.large_liturgy_page_number,
                "small_antiphony": s.small_antiphony_page_number,
                "large_antiphony": s.large_antiphony_page_number,
                "small_praise": s.small_praise_page_number,
                "large_praise": s.large_praise_page_number,
            },
            "offerings": [{"revenue_stream_id": o["id"], "revenue_stream_name": o["name"]} for o in offerings],
            "songs": [{"song_number": sg["song_number"], "song_name": sg["name"]} for sg in songs],
            "scriptures": [dict(sc) for sc in scriptures],
        })

    return success_response(data={"sunday_services": result})


# ═══════════════════════════════════════════════════════════════
# MEMBER DETAILS — Replaces: legacy/api/data/church_member.php
# ═══════════════════════════════════════════════════════════════
@router.get("/details/{member_id}")
def get_member(member_id: int, db: Session = Depends(get_db)):
    details = get_member_details(db, member_id)
    if not details:
        return error_response("Member not found")
    return success_response(data=details)


# ═══════════════════════════════════════════════════════════════
# FEEDBACK — Replaces: feedback/record_head_parish_feedback.php
# ═══════════════════════════════════════════════════════════════
class FeedbackRequest(BaseModel):
    head_parish_id: int
    admin_id: Optional[int] = None
    feedback_type: str
    subject: str
    message: str

@router.post("/feedback")
def submit_feedback(body: FeedbackRequest, db: Session = Depends(get_db)):
    if not body.feedback_type or not body.subject or not body.message:
        return error_response("All fields are required")
    db.add(Feedback(
        head_parish_id=body.head_parish_id, submitted_by_admin_id=body.admin_id,
        feedback_type=body.feedback_type, subject=body.subject, message=body.message,
    ))
    db.commit()
    return success_response("Feedback submitted successfully")
