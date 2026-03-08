# api/routes/finance.py
"""Financial routes: bank accounts, revenue streams, revenues, expenses, budgets."""
from fastapi import APIRouter, Depends, HTTPException
from pydantic import BaseModel
from sqlalchemy.orm import Session
from typing import Optional
from datetime import date

from core.database import get_db
from models.finance import (
    BankAccount, RevenueStream, Revenue, ExpenseGroup, ExpenseName,
    Expense, ExpenseRequest, ExpenseRequestItem,
    AnnualRevenueTarget, AnnualExpenseBudget,
)
from utils.helpers import update_account_balance, get_account_id_by_revenue_stream
from utils.response import success_response

router = APIRouter(prefix="/finance", tags=["Finance"])


# ══════════════════════════════════════════════════════════════
# BANK ACCOUNTS (polymorphic: diocese, province, head_parish)
# ══════════════════════════════════════════════════════════════
class BankAccountCreate(BaseModel):
    account_name: str
    account_number: str
    bank_id: int
    balance: float = 0.0
    entity_type: str  # diocese, province, head_parish
    entity_id: int

@router.get("/bank-accounts")
def list_bank_accounts(entity_type: str, entity_id: int, db: Session = Depends(get_db)):
    rows = db.query(BankAccount).filter(
        BankAccount.entity_type == entity_type,
        BankAccount.entity_id == entity_id,
        BankAccount.is_active == True,
    ).all()
    return success_response(data=[{
        "id": a.id, "account_name": a.account_name, "account_number": a.account_number,
        "bank_id": a.bank_id, "balance": float(a.balance),
    } for a in rows])

@router.post("/bank-accounts")
def create_bank_account(body: BankAccountCreate, db: Session = Depends(get_db)):
    if body.entity_type not in ("diocese", "province", "head_parish"):
        raise HTTPException(400, "Invalid entity type")
    if db.query(BankAccount).filter(BankAccount.account_number == body.account_number, BankAccount.bank_id == body.bank_id).first():
        raise HTTPException(400, "Account number already exists for this bank")
    acc = BankAccount(**body.dict())
    db.add(acc); db.commit(); db.refresh(acc)
    return success_response("Bank account created", {"id": acc.id})


# ══════════════════════════════════════════════════════════════
# REVENUE STREAMS
# ══════════════════════════════════════════════════════════════
class RevenueStreamCreate(BaseModel):
    name: str
    account_id: int
    entity_type: str
    entity_id: int

@router.get("/revenue-streams")
def list_revenue_streams(entity_type: str, entity_id: int, db: Session = Depends(get_db)):
    rows = db.query(RevenueStream).filter(
        RevenueStream.entity_type == entity_type,
        RevenueStream.entity_id == entity_id,
        RevenueStream.is_active == True,
    ).all()
    return success_response(data=[{"id": r.id, "name": r.name, "account_id": r.account_id} for r in rows])

@router.post("/revenue-streams")
def create_revenue_stream(body: RevenueStreamCreate, db: Session = Depends(get_db)):
    if db.query(RevenueStream).filter(
        RevenueStream.name == body.name,
        RevenueStream.entity_type == body.entity_type,
        RevenueStream.entity_id == body.entity_id,
    ).first():
        raise HTTPException(400, "Revenue stream already exists")
    rs = RevenueStream(**body.dict())
    db.add(rs); db.commit(); db.refresh(rs)
    return success_response("Revenue stream created", {"id": rs.id})


# ══════════════════════════════════════════════════════════════
# REVENUES (unified: head_parish, sub_parish, community, group)
# ══════════════════════════════════════════════════════════════
class RevenueCreate(BaseModel):
    management_level: str
    revenue_stream_id: int
    head_parish_id: int
    sub_parish_id: Optional[int] = None
    community_id: Optional[int] = None
    group_id: Optional[int] = None
    service_number: Optional[int] = None
    amount: float
    payment_method: str = "Cash"
    description: Optional[str] = None
    revenue_date: date

@router.get("/revenues")
def list_revenues(
    head_parish_id: int,
    management_level: Optional[str] = None,
    db: Session = Depends(get_db),
):
    q = db.query(Revenue).filter(Revenue.head_parish_id == head_parish_id)
    if management_level:
        q = q.filter(Revenue.management_level == management_level)
    rows = q.order_by(Revenue.revenue_date.desc()).limit(100).all()
    return success_response(data=[{
        "id": r.id, "management_level": r.management_level,
        "revenue_stream_id": r.revenue_stream_id, "amount": float(r.amount),
        "payment_method": r.payment_method, "revenue_date": str(r.revenue_date),
        "description": r.description,
    } for r in rows])

@router.post("/revenues")
def record_revenue(body: RevenueCreate, db: Session = Depends(get_db)):
    if body.amount <= 0:
        raise HTTPException(400, "Amount must be greater than 0")
    rev = Revenue(**body.dict())
    db.add(rev)

    # Update account balance
    account_id = get_account_id_by_revenue_stream(db, body.revenue_stream_id)
    if account_id:
        from decimal import Decimal
        update_account_balance(db, account_id, Decimal(str(body.amount)))

    db.commit(); db.refresh(rev)
    return success_response("Revenue recorded", {"id": rev.id})


# ══════════════════════════════════════════════════════════════
# EXPENSE GROUPS & NAMES
# ══════════════════════════════════════════════════════════════
class ExpenseGroupCreate(BaseModel):
    name: str
    management_level: str
    head_parish_id: int

@router.get("/expense-groups")
def list_expense_groups(head_parish_id: int, management_level: Optional[str] = None, db: Session = Depends(get_db)):
    q = db.query(ExpenseGroup).filter(ExpenseGroup.head_parish_id == head_parish_id)
    if management_level:
        q = q.filter(ExpenseGroup.management_level == management_level)
    return success_response(data=[{"id": g.id, "name": g.name, "management_level": g.management_level} for g in q.all()])

@router.post("/expense-groups")
def create_expense_group(body: ExpenseGroupCreate, db: Session = Depends(get_db)):
    eg = ExpenseGroup(**body.dict())
    db.add(eg); db.commit(); db.refresh(eg)
    return success_response("Expense group created", {"id": eg.id})


class ExpenseNameCreate(BaseModel):
    expense_group_id: int
    name: str
    management_level: str

@router.post("/expense-names")
def create_expense_name(body: ExpenseNameCreate, db: Session = Depends(get_db)):
    if db.query(ExpenseName).filter(ExpenseName.name == body.name, ExpenseName.expense_group_id == body.expense_group_id).first():
        raise HTTPException(400, "Expense name already exists")
    en = ExpenseName(**body.dict())
    db.add(en); db.commit(); db.refresh(en)
    return success_response("Expense name created", {"id": en.id})


# ══════════════════════════════════════════════════════════════
# EXPENSES
# ══════════════════════════════════════════════════════════════
class ExpenseCreate(BaseModel):
    management_level: str
    expense_name_id: int
    head_parish_id: int
    sub_parish_id: Optional[int] = None
    community_id: Optional[int] = None
    group_id: Optional[int] = None
    amount: float
    payment_method: str = "Cash"
    description: Optional[str] = None
    expense_date: date

@router.post("/expenses")
def record_expense(body: ExpenseCreate, db: Session = Depends(get_db)):
    if body.amount <= 0:
        raise HTTPException(400, "Amount must be greater than 0")
    exp = Expense(**body.dict())
    db.add(exp); db.commit(); db.refresh(exp)
    return success_response("Expense recorded", {"id": exp.id})


# ══════════════════════════════════════════════════════════════
# ANNUAL TARGETS & BUDGETS
# ══════════════════════════════════════════════════════════════
class RevenueTargetCreate(BaseModel):
    revenue_stream_id: int
    head_parish_id: int
    year: int
    target_amount: float

@router.post("/revenue-targets")
def set_revenue_target(body: RevenueTargetCreate, db: Session = Depends(get_db)):
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


class ExpenseBudgetCreate(BaseModel):
    expense_name_id: int
    head_parish_id: int
    year: int
    budget_amount: float

@router.post("/expense-budgets")
def set_expense_budget(body: ExpenseBudgetCreate, db: Session = Depends(get_db)):
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
    return success_response("Expense budget set")
