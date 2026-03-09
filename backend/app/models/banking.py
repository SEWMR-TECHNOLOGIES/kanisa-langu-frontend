# models/banking.py
from sqlalchemy import Column, Integer, String, Numeric, Date, DateTime, ForeignKey, CheckConstraint
from sqlalchemy.sql import func
from core.base import Base


class BankPosting(Base):
    __tablename__ = "bank_postings"
    id = Column(Integer, primary_key=True)
    account_id = Column(Integer, ForeignKey("bank_accounts.id", ondelete="RESTRICT"), nullable=False)
    amount = Column(Numeric(15, 2), nullable=False)
    posting_type = Column(String(10), nullable=False)
    reference_type = Column(String(30))
    reference_id = Column(Integer)
    description = Column(String)
    posted_by = Column(Integer, ForeignKey("admins.id", ondelete="SET NULL"))
    posted_at = Column(DateTime(timezone=True), server_default=func.now())

    __table_args__ = (
        CheckConstraint("posting_type IN ('credit','debit')", name="ck_posting_type"),
    )


class BankClosingBalance(Base):
    __tablename__ = "bank_closing_balances"
    id = Column(Integer, primary_key=True)
    account_id = Column(Integer, ForeignKey("bank_accounts.id", ondelete="CASCADE"), nullable=False)
    closing_balance = Column(Numeric(15, 2), nullable=False)
    balance_date = Column(Date, nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())
