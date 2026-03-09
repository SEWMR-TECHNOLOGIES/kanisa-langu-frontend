# models/finance.py
from sqlalchemy import Column, Integer, String, Boolean, Numeric, Date, DateTime, ForeignKey, CheckConstraint, UniqueConstraint
from sqlalchemy.sql import func
from core.base import Base


class BankAccount(Base):
    __tablename__ = "bank_accounts"
    id = Column(Integer, primary_key=True)
    account_name = Column(String(150), nullable=False)
    account_number = Column(String(50), nullable=False)
    bank_id = Column(Integer, ForeignKey("banks.id", ondelete="RESTRICT"), nullable=False)
    balance = Column(Numeric(15, 2), default=0, nullable=False)
    entity_type = Column(String(20), nullable=False)
    entity_id = Column(Integer, nullable=False)
    is_active = Column(Boolean, default=True, nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now())

    __table_args__ = (
        CheckConstraint("entity_type IN ('diocese','province','head_parish')", name="ck_ba_entity_type"),
    )


class RevenueStream(Base):
    __tablename__ = "revenue_streams"
    id = Column(Integer, primary_key=True)
    name = Column(String(150), nullable=False)
    account_id = Column(Integer, ForeignKey("bank_accounts.id", ondelete="RESTRICT"), nullable=False)
    entity_type = Column(String(20), nullable=False)
    entity_id = Column(Integer, nullable=False)
    is_active = Column(Boolean, default=True, nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())


class Revenue(Base):
    __tablename__ = "revenues"
    id = Column(Integer, primary_key=True)
    management_level = Column(String(20), nullable=False)
    revenue_stream_id = Column(Integer, ForeignKey("revenue_streams.id", ondelete="RESTRICT"), nullable=False)
    head_parish_id = Column(Integer, ForeignKey("head_parishes.id", ondelete="CASCADE"), nullable=False)
    sub_parish_id = Column(Integer, ForeignKey("sub_parishes.id", ondelete="SET NULL"))
    community_id = Column(Integer, ForeignKey("communities.id", ondelete="SET NULL"))
    group_id = Column(Integer, ForeignKey("groups.id", ondelete="SET NULL"))
    service_number = Column(Integer)
    amount = Column(Numeric(15, 2), nullable=False)
    payment_method = Column(String(30), default="Cash", nullable=False)
    description = Column(String)
    revenue_date = Column(Date, nullable=False)
    recorded_by = Column(Integer, ForeignKey("admins.id", ondelete="SET NULL"))
    recorded_from = Column(String(10), default="web")
    is_verified = Column(Boolean, default=False, nullable=False)
    is_posted_to_bank = Column(Boolean, default=False, nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())


class ExpenseGroup(Base):
    __tablename__ = "expense_groups"
    id = Column(Integer, primary_key=True)
    name = Column(String(150), nullable=False)
    management_level = Column(String(20), nullable=False)
    head_parish_id = Column(Integer, ForeignKey("head_parishes.id", ondelete="CASCADE"), nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())


class ExpenseName(Base):
    __tablename__ = "expense_names"
    id = Column(Integer, primary_key=True)
    expense_group_id = Column(Integer, ForeignKey("expense_groups.id", ondelete="CASCADE"), nullable=False)
    name = Column(String(200), nullable=False)
    management_level = Column(String(20), nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())


class Expense(Base):
    __tablename__ = "expenses"
    id = Column(Integer, primary_key=True)
    management_level = Column(String(20), nullable=False)
    expense_name_id = Column(Integer, ForeignKey("expense_names.id", ondelete="RESTRICT"), nullable=False)
    head_parish_id = Column(Integer, ForeignKey("head_parishes.id", ondelete="CASCADE"), nullable=False)
    sub_parish_id = Column(Integer, ForeignKey("sub_parishes.id", ondelete="SET NULL"))
    community_id = Column(Integer, ForeignKey("communities.id", ondelete="SET NULL"))
    group_id = Column(Integer, ForeignKey("groups.id", ondelete="SET NULL"))
    amount = Column(Numeric(15, 2), nullable=False)
    payment_method = Column(String(30), default="Cash", nullable=False)
    description = Column(String)
    expense_date = Column(Date, nullable=False)
    recorded_by = Column(Integer, ForeignKey("admins.id", ondelete="SET NULL"))
    created_at = Column(DateTime(timezone=True), server_default=func.now())


class ExpenseRequest(Base):
    __tablename__ = "expense_requests"
    id = Column(Integer, primary_key=True)
    management_level = Column(String(20), nullable=False)
    head_parish_id = Column(Integer, ForeignKey("head_parishes.id", ondelete="CASCADE"), nullable=False)
    sub_parish_id = Column(Integer, ForeignKey("sub_parishes.id"))
    community_id = Column(Integer, ForeignKey("communities.id"))
    group_id = Column(Integer, ForeignKey("groups.id"))
    requested_by = Column(Integer, ForeignKey("admins.id", ondelete="SET NULL"))
    status = Column(String(20), default="pending", nullable=False)
    total_amount = Column(Numeric(15, 2), default=0, nullable=False)
    notes = Column(String)
    responded_by = Column(Integer, ForeignKey("admins.id", ondelete="SET NULL"))
    responded_at = Column(DateTime(timezone=True))
    created_at = Column(DateTime(timezone=True), server_default=func.now())

    __table_args__ = (
        CheckConstraint("status IN ('pending','approved','rejected')", name="ck_expense_req_status"),
    )


class ExpenseRequestItem(Base):
    __tablename__ = "expense_request_items"
    id = Column(Integer, primary_key=True)
    request_id = Column(Integer, ForeignKey("expense_requests.id", ondelete="CASCADE"), nullable=False)
    expense_name_id = Column(Integer, ForeignKey("expense_names.id", ondelete="RESTRICT"), nullable=False)
    amount = Column(Numeric(15, 2), nullable=False)
    description = Column(String)


class AnnualRevenueTarget(Base):
    __tablename__ = "annual_revenue_targets"
    id = Column(Integer, primary_key=True)
    revenue_stream_id = Column(Integer, ForeignKey("revenue_streams.id", ondelete="CASCADE"), nullable=False)
    head_parish_id = Column(Integer, ForeignKey("head_parishes.id", ondelete="CASCADE"), nullable=False)
    year = Column(Integer, nullable=False)
    target_amount = Column(Numeric(15, 2), nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())

    __table_args__ = (
        UniqueConstraint("revenue_stream_id", "head_parish_id", "year", name="uq_rev_target_stream_hp_year"),
    )


class AnnualExpenseBudget(Base):
    __tablename__ = "annual_expense_budgets"
    id = Column(Integer, primary_key=True)
    expense_name_id = Column(Integer, ForeignKey("expense_names.id", ondelete="CASCADE"), nullable=False)
    head_parish_id = Column(Integer, ForeignKey("head_parishes.id", ondelete="CASCADE"), nullable=False)
    year = Column(Integer, nullable=False)
    budget_amount = Column(Numeric(15, 2), nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())

    __table_args__ = (
        UniqueConstraint("expense_name_id", "head_parish_id", "year", name="uq_exp_budget_name_hp_year"),
    )
