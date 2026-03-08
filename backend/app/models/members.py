# models/members.py
from sqlalchemy import Column, Integer, String, Boolean, Date, DateTime, ForeignKey, CheckConstraint
from sqlalchemy.sql import func
from core.base import Base


class ChurchMember(Base):
    __tablename__ = "church_members"
    id = Column(Integer, primary_key=True)
    title_id = Column(Integer, ForeignKey("titles.id", ondelete="SET NULL"))
    first_name = Column(String(100), nullable=False)
    middle_name = Column(String(100))
    last_name = Column(String(100), nullable=False)
    date_of_birth = Column(Date, nullable=False)
    gender = Column(String(10), nullable=False)
    member_type = Column(String(20), nullable=False)
    head_parish_id = Column(Integer, ForeignKey("head_parishes.id", ondelete="CASCADE"), nullable=False)
    sub_parish_id = Column(Integer, ForeignKey("sub_parishes.id", ondelete="CASCADE"), nullable=False)
    community_id = Column(Integer, ForeignKey("communities.id", ondelete="CASCADE"), nullable=False)
    envelope_number = Column(String(50), unique=True)
    status = Column(String(20), default="Active", nullable=False)
    occupation_id = Column(Integer, ForeignKey("occupations.id", ondelete="SET NULL"))
    phone = Column(String(20), unique=True)
    email = Column(String(255), unique=True)
    avatar_url = Column(String(500))
    password = Column(String(255))
    recorded_by = Column(Integer, ForeignKey("admins.id", ondelete="SET NULL"))
    is_active = Column(Boolean, default=True, nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now())

    __table_args__ = (
        CheckConstraint("gender IN ('Male', 'Female')", name="ck_member_gender"),
        CheckConstraint("member_type IN ('Mgeni', 'Mwenyeji')", name="ck_member_type"),
        CheckConstraint("status IN ('Active', 'Inactive', 'Excluded')", name="ck_member_status"),
    )


class MemberExclusion(Base):
    __tablename__ = "member_exclusions"
    id = Column(Integer, primary_key=True)
    member_id = Column(Integer, ForeignKey("church_members.id", ondelete="CASCADE"), nullable=False)
    reason = Column(String, nullable=False)
    excluded_by = Column(Integer, ForeignKey("admins.id", ondelete="SET NULL"))
    excluded_at = Column(DateTime(timezone=True), server_default=func.now())


class ChurchLeader(Base):
    __tablename__ = "church_leaders"
    id = Column(Integer, primary_key=True)
    title_id = Column(Integer, ForeignKey("titles.id", ondelete="SET NULL"))
    first_name = Column(String(100), nullable=False)
    middle_name = Column(String(100))
    last_name = Column(String(100), nullable=False)
    gender = Column(String(10), nullable=False)
    leader_type = Column(String(20), nullable=False)
    head_parish_id = Column(Integer, ForeignKey("head_parishes.id", ondelete="CASCADE"), nullable=False)
    role_id = Column(Integer, ForeignKey("church_roles.id", ondelete="RESTRICT"), nullable=False)
    appointment_date = Column(Date, nullable=False)
    end_date = Column(Date)
    status = Column(String(20), default="Active", nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())


class ChurchChoir(Base):
    __tablename__ = "church_choirs"
    id = Column(Integer, primary_key=True)
    name = Column(String(150), nullable=False)
    head_parish_id = Column(Integer, ForeignKey("head_parishes.id", ondelete="CASCADE"), nullable=False)
    description = Column(String)
    created_at = Column(DateTime(timezone=True), server_default=func.now())
