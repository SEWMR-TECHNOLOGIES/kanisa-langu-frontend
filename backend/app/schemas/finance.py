# schemas/finance.py
"""Financial request & response schemas."""
from pydantic import BaseModel, Field
from typing import Optional, List
from datetime import date


# ── Bank Accounts ─────────────────────────────────────────────

class BankAccountCreate(BaseModel):
    account_name: str
    account_number: str
    bank_id: int
    balance: float = 0.0
    entity_type: str = Field(..., description="diocese, province, or head_parish")
    entity_id: int


class BankAccountOut(BaseModel):
    id: int
    account_name: str
    account_number: str
    bank_id: int
    balance: float


# ── Revenue Streams ───────────────────────────────────────────

class RevenueStreamCreate(BaseModel):
    name: str
    account_id: int
    entity_type: str
    entity_id: int


class RevenueStreamOut(BaseModel):
    id: int
    name: str
    account_id: int


# ── Revenues ──────────────────────────────────────────────────

class RevenueCreate(BaseModel):
    management_level: str = Field(..., description="head_parish, sub_parish, community, or group")
    revenue_stream_id: int
    head_parish_id: int
    sub_parish_id: Optional[int] = None
    community_id: Optional[int] = None
    group_id: Optional[int] = None
    service_number: Optional[int] = None
    amount: float = Field(..., gt=0, description="Amount must be > 0")
    payment_method: str = "Cash"
    description: Optional[str] = None
    revenue_date: date


class RevenueOut(BaseModel):
    id: int
    management_level: str
    revenue_stream_id: int
    amount: float
    payment_method: str
    revenue_date: str
    description: Optional[str] = None


# ── Expenses ──────────────────────────────────────────────────

class ExpenseGroupOut(BaseModel):
    id: int
    name: str
    management_level: str


class ExpenseNameOut(BaseModel):
    id: int
    name: str


class ExpenseCreate(BaseModel):
    management_level: str
    expense_name_id: int
    head_parish_id: int
    sub_parish_id: Optional[int] = None
    community_id: Optional[int] = None
    group_id: Optional[int] = None
    amount: float = Field(..., gt=0)
    payment_method: str = "Cash"
    description: Optional[str] = None
    expense_date: date


# ── Targets & Budgets ─────────────────────────────────────────

class RevenueTargetCreate(BaseModel):
    revenue_stream_id: int
    head_parish_id: int
    year: int
    target_amount: float


class ExpenseBudgetCreate(BaseModel):
    expense_name_id: int
    head_parish_id: int
    year: int
    budget_amount: float


# ── Financial Summary ─────────────────────────────────────────

class FinancialSummaryOut(BaseModel):
    total_revenue: float
    total_expense: float
    balance: float


class RevenueByStreamOut(BaseModel):
    id: int
    stream_name: str
    total: float


class MonthlyAmountOut(BaseModel):
    month: int
    total: float
