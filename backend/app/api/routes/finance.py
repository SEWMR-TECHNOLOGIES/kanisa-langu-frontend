# api/routes/finance.py
"""Financial routes: bank accounts, revenue streams, revenues, expenses, budgets."""
from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.orm import Session
from typing import Optional, List
from datetime import date

from core.database import get_db
from models.finance import (
    BankAccount, RevenueStream, Revenue, ExpenseGroup, ExpenseName,
    Expense, ExpenseRequest, ExpenseRequestItem,
    AnnualRevenueTarget, AnnualExpenseBudget,
)
from utils.helpers import update_account_balance, get_account_id_by_revenue_stream
from utils.response import success_response

from schemas.base import ApiResponse, IdData
from schemas.finance import (
    BankAccountCreate, BankAccountOut, RevenueStreamCreate, RevenueStreamOut,
    RevenueCreate, RevenueOut, ExpenseGroupOut, ExpenseCreate,
    RevenueTargetCreate, ExpenseBudgetCreate,
)

router = APIRouter(prefix="/finance", tags=["Finance"])


# ══════════════════════════════════════════════════════════════
# BANK ACCOUNTS
# ══════════════════════════════════════════════════════════════

@router.get("/bank-accounts", response_model=ApiResponse[List[BankAccountOut]], summary="List bank accounts for an entity")
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

@router.post("/bank-accounts", response_model=ApiResponse[IdData], summary="Create a bank account")
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

@router.get("/revenue-streams", response_model=ApiResponse[List[RevenueStreamOut]], summary="List revenue streams")
def list_revenue_streams(entity_type: str, entity_id: int, db: Session = Depends(get_db)):
    rows = db.query(RevenueStream).filter(
        RevenueStream.entity_type == entity_type,
        RevenueStream.entity_id == entity_id,
        RevenueStream.is_active == True,
    ).all()
    return success_response(data=[{"id": r.id, "name": r.name, "account_id": r.account_id} for r in rows])

@router.post("/revenue-streams", response_model=ApiResponse[IdData], summary="Create a revenue stream")
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
# REVENUES
# ══════════════════════════════════════════════════════════════

@router.get("/revenues", response_model=ApiResponse[List[RevenueOut]], summary="List revenues for a head parish")
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

@router.post("/revenues", response_model=ApiResponse[IdData], summary="Record a revenue entry")
def record_revenue(body: RevenueCreate, db: Session = Depends(get_db)):
    if body.amount <= 0:
        raise HTTPException(400, "Amount must be greater than 0")
    rev = Revenue(**body.dict())
    db.add(rev)

    account_id = get_account_id_by_revenue_stream(db, body.revenue_stream_id)
    if account_id:
        from decimal import Decimal
        update_account_balance(db, account_id, Decimal(str(body.amount)))

    db.commit(); db.refresh(rev)
    return success_response("Revenue recorded", {"id": rev.id})


# ══════════════════════════════════════════════════════════════
# EXPENSE GROUPS & NAMES
# ══════════════════════════════════════════════════════════════

from pydantic import BaseModel

class ExpenseGroupCreate(BaseModel):
    name: str
    management_level: str
    head_parish_id: int

class ExpenseNameCreate(BaseModel):
    expense_group_id: int
    name: str
    management_level: str

@router.get("/expense-groups", response_model=ApiResponse[List[ExpenseGroupOut]], summary="List expense groups")
def list_expense_groups(head_parish_id: int, management_level: Optional[str] = None, db: Session = Depends(get_db)):
    q = db.query(ExpenseGroup).filter(ExpenseGroup.head_parish_id == head_parish_id)
    if management_level:
        q = q.filter(ExpenseGroup.management_level == management_level)
    return success_response(data=[{"id": g.id, "name": g.name, "management_level": g.management_level} for g in q.all()])

@router.post("/expense-groups", response_model=ApiResponse[IdData], summary="Create an expense group")
def create_expense_group(body: ExpenseGroupCreate, db: Session = Depends(get_db)):
    eg = ExpenseGroup(**body.dict())
    db.add(eg); db.commit(); db.refresh(eg)
    return success_response("Expense group created", {"id": eg.id})

@router.post("/expense-names", response_model=ApiResponse[IdData], summary="Create an expense name under a group")
def create_expense_name(body: ExpenseNameCreate, db: Session = Depends(get_db)):
    if db.query(ExpenseName).filter(ExpenseName.name == body.name, ExpenseName.expense_group_id == body.expense_group_id).first():
        raise HTTPException(400, "Expense name already exists")
    en = ExpenseName(**body.dict())
    db.add(en); db.commit(); db.refresh(en)
    return success_response("Expense name created", {"id": en.id})


# ══════════════════════════════════════════════════════════════
# EXPENSES
# ══════════════════════════════════════════════════════════════

@router.post("/expenses", response_model=ApiResponse[IdData], summary="Record an expense")
def record_expense(body: ExpenseCreate, db: Session = Depends(get_db)):
    if body.amount <= 0:
        raise HTTPException(400, "Amount must be greater than 0")
    exp = Expense(**body.dict())
    db.add(exp); db.commit(); db.refresh(exp)
    return success_response("Expense recorded", {"id": exp.id})


# ══════════════════════════════════════════════════════════════
# ANNUAL TARGETS & BUDGETS
# ══════════════════════════════════════════════════════════════

@router.post("/revenue-targets", response_model=ApiResponse[None], summary="Set annual revenue target for a stream")
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


@router.post("/expense-budgets", response_model=ApiResponse[None], summary="Set annual expense budget")
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
