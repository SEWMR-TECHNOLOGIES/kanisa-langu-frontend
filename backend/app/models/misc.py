# models/misc.py
from sqlalchemy import Column, Integer, String, Boolean, Numeric, Date, DateTime, ForeignKey, CheckConstraint, UniqueConstraint
from sqlalchemy.sql import func
from core.base import Base


class Feedback(Base):
    __tablename__ = "feedback"
    id = Column(Integer, primary_key=True)
    head_parish_id = Column(Integer, ForeignKey("head_parishes.id", ondelete="CASCADE"), nullable=False)
    submitted_by_admin_id = Column(Integer, ForeignKey("admins.id", ondelete="SET NULL"))
    feedback_type = Column(String(50), nullable=False)
    subject = Column(String(200), nullable=False)
    message = Column(String, nullable=False)
    submitted_at = Column(DateTime(timezone=True), server_default=func.now())


class FcmToken(Base):
    __tablename__ = "fcm_tokens"
    id = Column(Integer, primary_key=True)
    member_id = Column(Integer, ForeignKey("church_members.id", ondelete="CASCADE"), nullable=False)
    head_parish_id = Column(Integer, ForeignKey("head_parishes.id", ondelete="CASCADE"), nullable=False)
    token = Column(String(500), nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now())

    __table_args__ = (
        UniqueConstraint("member_id", "head_parish_id", name="uq_fcm_member_hp"),
    )


class AppVersion(Base):
    __tablename__ = "app_versions"
    id = Column(Integer, primary_key=True)
    platform = Column(String(20), nullable=False)
    version = Column(String(20), nullable=False)
    force_update = Column(Boolean, default=False, nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())


class HeadParishDebit(Base):
    __tablename__ = "head_parish_debits"
    id = Column(Integer, primary_key=True)
    head_parish_id = Column(Integer, ForeignKey("head_parishes.id", ondelete="CASCADE"), nullable=False)
    description = Column(String, nullable=False)
    amount = Column(Numeric(15, 2), nullable=False)
    date_debited = Column(Date, nullable=False)
    return_before_date = Column(Date, nullable=False)
    purpose = Column(String, nullable=False)
    is_paid = Column(Boolean, default=False, nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())

    __table_args__ = (
        CheckConstraint("amount > 0", name="ck_debit_amount"),
    )


class UnitOfMeasure(Base):
    __tablename__ = "unit_of_measure"
    id = Column(Integer, primary_key=True)
    unit = Column(String(50), nullable=False, unique=True)
    meaning = Column(String(200))
    created_at = Column(DateTime(timezone=True), server_default=func.now())


class MemberOtpCode(Base):
    __tablename__ = "member_otp_codes"
    id = Column(Integer, primary_key=True)
    member_id = Column(Integer, ForeignKey("church_members.id", ondelete="CASCADE"), nullable=False)
    otp_code = Column(String(10), nullable=False)
    purpose = Column(String(30), nullable=False)
    expires_at = Column(DateTime(timezone=True), nullable=False)
    used = Column(Boolean, default=False, nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())

    __table_args__ = (
        CheckConstraint("purpose IN ('registration', 'password_reset')", name="ck_otp_purpose"),
    )


class MemberExclusionReason(Base):
    __tablename__ = "member_exclusion_reasons"
    id = Column(Integer, primary_key=True)
    head_parish_id = Column(Integer, ForeignKey("head_parishes.id", ondelete="CASCADE"), nullable=False)
    reason = Column(String, nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())


class HarambeeExclusionReason(Base):
    __tablename__ = "harambee_exclusion_reasons"
    id = Column(Integer, primary_key=True)
    head_parish_id = Column(Integer, ForeignKey("head_parishes.id", ondelete="CASCADE"), nullable=False)
    reason = Column(String, nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())
