# models/misc.py
from sqlalchemy import Column, Integer, String, Boolean, Numeric, Date, DateTime, ForeignKey, CheckConstraint
from sqlalchemy.sql import func
from core.base import Base


class Feedback(Base):
    __tablename__ = "feedback"
    # ... keep existing code


class FcmToken(Base):
    __tablename__ = "fcm_tokens"
    # ... keep existing code


class AppVersion(Base):
    __tablename__ = "app_versions"
    # ... keep existing code


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
