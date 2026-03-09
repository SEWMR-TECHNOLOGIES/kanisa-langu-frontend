# models/harambee.py
from sqlalchemy import Column, Integer, String, Boolean, Numeric, Date, DateTime, ForeignKey, CheckConstraint, UniqueConstraint
from sqlalchemy.sql import func
from core.base import Base


class Harambee(Base):
    __tablename__ = "harambees"
    id = Column(Integer, primary_key=True)
    management_level = Column(String(20), nullable=False)
    head_parish_id = Column(Integer, ForeignKey("head_parishes.id", ondelete="CASCADE"), nullable=False)
    sub_parish_id = Column(Integer, ForeignKey("sub_parishes.id", ondelete="SET NULL"))
    community_id = Column(Integer, ForeignKey("communities.id", ondelete="SET NULL"))
    group_id = Column(Integer, ForeignKey("groups.id", ondelete="SET NULL"))
    account_id = Column(Integer, ForeignKey("bank_accounts.id", ondelete="RESTRICT"), nullable=False)
    name = Column(String(200), nullable=False)
    description = Column(String, nullable=False)
    from_date = Column(Date, nullable=False)
    to_date = Column(Date, nullable=False)
    amount = Column(Numeric(15, 2), nullable=False)
    is_active = Column(Boolean, default=True, nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())

    __table_args__ = (
        CheckConstraint("to_date > from_date", name="ck_harambee_dates"),
        CheckConstraint("amount > 0", name="ck_harambee_amount"),
    )


class HarambeeGroup(Base):
    __tablename__ = "harambee_groups"
    id = Column(Integer, primary_key=True)
    harambee_id = Column(Integer, ForeignKey("harambees.id", ondelete="CASCADE"), nullable=False)
    name = Column(String(150), nullable=False)
    target = Column(Numeric(15, 2), default=0, nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())


class HarambeeGroupMember(Base):
    __tablename__ = "harambee_group_members"
    id = Column(Integer, primary_key=True)
    harambee_group_id = Column(Integer, ForeignKey("harambee_groups.id", ondelete="CASCADE"), nullable=False)
    member_id = Column(Integer, ForeignKey("church_members.id", ondelete="CASCADE"), nullable=False)
    responsibility = Column(String(50))
    created_at = Column(DateTime(timezone=True), server_default=func.now())


class HarambeeTarget(Base):
    __tablename__ = "harambee_targets"
    id = Column(Integer, primary_key=True)
    harambee_id = Column(Integer, ForeignKey("harambees.id", ondelete="CASCADE"), nullable=False)
    member_id = Column(Integer, ForeignKey("church_members.id", ondelete="CASCADE"), nullable=False)
    sub_parish_id = Column(Integer, ForeignKey("sub_parishes.id"))
    community_id = Column(Integer, ForeignKey("communities.id"))
    target_type = Column(String(30), default="individual", nullable=False)
    target = Column(Numeric(15, 2), nullable=False)
    committee_responsibility = Column(String(50))
    created_at = Column(DateTime(timezone=True), server_default=func.now())

    __table_args__ = (
        UniqueConstraint("harambee_id", "member_id", name="uq_harambee_target_member"),
    )


class HarambeeContribution(Base):
    __tablename__ = "harambee_contributions"
    id = Column(Integer, primary_key=True)
    harambee_id = Column(Integer, ForeignKey("harambees.id", ondelete="CASCADE"), nullable=False)
    member_id = Column(Integer, ForeignKey("church_members.id", ondelete="CASCADE"), nullable=False)
    amount = Column(Numeric(15, 2), nullable=False)
    contribution_date = Column(Date, nullable=False)
    head_parish_id = Column(Integer, ForeignKey("head_parishes.id", ondelete="CASCADE"), nullable=False)
    sub_parish_id = Column(Integer, ForeignKey("sub_parishes.id"))
    community_id = Column(Integer, ForeignKey("communities.id"))
    payment_method = Column(String(30), default="Cash", nullable=False)
    recorded_by = Column(Integer, ForeignKey("admins.id", ondelete="SET NULL"))
    local_timestamp = Column(DateTime(timezone=True))
    created_at = Column(DateTime(timezone=True), server_default=func.now())


class HarambeeClass(Base):
    __tablename__ = "harambee_classes"
    id = Column(Integer, primary_key=True)
    harambee_id = Column(Integer, ForeignKey("harambees.id", ondelete="CASCADE"), nullable=False)
    class_name = Column(String(100), nullable=False)
    min_amount = Column(Numeric(15, 2), default=0, nullable=False)
    max_amount = Column(Numeric(15, 2))
    created_at = Column(DateTime(timezone=True), server_default=func.now())


class HarambeeDistribution(Base):
    __tablename__ = "harambee_distributions"
    id = Column(Integer, primary_key=True)
    harambee_id = Column(Integer, ForeignKey("harambees.id", ondelete="CASCADE"), nullable=False)
    member_id = Column(Integer, ForeignKey("church_members.id", ondelete="CASCADE"), nullable=False)
    amount = Column(Numeric(15, 2), nullable=False)
    distribution_date = Column(Date, nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())


class HarambeeExclusion(Base):
    __tablename__ = "harambee_exclusions"
    id = Column(Integer, primary_key=True)
    harambee_id = Column(Integer, ForeignKey("harambees.id", ondelete="CASCADE"), nullable=False)
    member_id = Column(Integer, ForeignKey("church_members.id", ondelete="CASCADE"), nullable=False)
    reason = Column(String, nullable=False)
    excluded_at = Column(DateTime(timezone=True), server_default=func.now())


class DelayedHarambeeNotification(Base):
    __tablename__ = "delayed_harambee_notifications"
    id = Column(Integer, primary_key=True)
    harambee_id = Column(Integer, ForeignKey("harambees.id", ondelete="CASCADE"), nullable=False)
    member_id = Column(Integer, ForeignKey("church_members.id", ondelete="CASCADE"), nullable=False)
    target = Column(String(20), nullable=False)
    contribution_date = Column(Date, nullable=False)
    amount = Column(Numeric(15, 2), nullable=False)
    contributing_member_name = Column(String(100))
    mr_and_mrs_name = Column(String(150))
    is_mr_and_mrs = Column(Boolean, default=False, nullable=False)
    is_sent = Column(Boolean, default=False, nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())


class HarambeeLetterStatus(Base):
    __tablename__ = "harambee_letter_statuses"
    id = Column(Integer, primary_key=True)
    member_id = Column(Integer, ForeignKey("church_members.id", ondelete="CASCADE"), nullable=False)
    head_parish_id = Column(Integer, ForeignKey("head_parishes.id", ondelete="CASCADE"), nullable=False)
    status = Column(String(10), default="No", nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now())

    __table_args__ = (
        UniqueConstraint("member_id", "head_parish_id", name="uq_harambee_letter_member_parish"),
        CheckConstraint("status IN ('Yes', 'No')", name="ck_letter_status"),
    )


class HarambeeExpense(Base):
    __tablename__ = "harambee_expenses"
    id = Column(Integer, primary_key=True)
    target = Column(String(20), nullable=False)
    harambee_id = Column(Integer, ForeignKey("harambees.id", ondelete="CASCADE"), nullable=False)
    head_parish_id = Column(Integer, ForeignKey("head_parishes.id", ondelete="CASCADE"), nullable=False)
    expense_name_id = Column(Integer, ForeignKey("expense_names.id", ondelete="RESTRICT"), nullable=False)
    amount = Column(Numeric(15, 2), nullable=False)
    description = Column(String, nullable=False)
    expense_date = Column(Date, nullable=False)
    recorded_by = Column(Integer, ForeignKey("admins.id", ondelete="SET NULL"))
    created_at = Column(DateTime(timezone=True), server_default=func.now())

    __table_args__ = (
        CheckConstraint("target IN ('head_parish', 'sub_parish', 'community', 'group')", name="ck_harambee_exp_target"),
        CheckConstraint("amount > 0", name="ck_harambee_exp_amount"),
    )
