# models/payments.py
from sqlalchemy import Column, Integer, String, Boolean, Numeric, Date, DateTime, ForeignKey
from sqlalchemy.sql import func
from core.base import Base


class Payment(Base):
    __tablename__ = "payments"
    id = Column(Integer, primary_key=True)
    member_id = Column(Integer, ForeignKey("church_members.id", ondelete="CASCADE"), nullable=False)
    head_parish_id = Column(Integer, ForeignKey("head_parishes.id", ondelete="CASCADE"), nullable=False)
    payment_gateway = Column(String(30), default="SELCOM", nullable=False)
    merchant_request_id = Column(String(100))
    checkout_request_id = Column(String(100))
    transaction_reference = Column(String(100))
    amount = Column(Numeric(15, 2), nullable=False)
    payment_reason = Column(String(50), nullable=False)
    payment_status = Column(String(20), default="Pending", nullable=False)
    harambee_id = Column(Integer, ForeignKey("harambees.id", ondelete="SET NULL"))
    service_id = Column(Integer, ForeignKey("sunday_services.id", ondelete="SET NULL"))
    revenue_stream_id = Column(Integer, ForeignKey("revenue_streams.id", ondelete="SET NULL"))
    payment_date = Column(Date, nullable=False)
    service_date = Column(Date)
    target = Column(String(20), default="head-parish")
    created_at = Column(DateTime(timezone=True), server_default=func.now())


class PaymentGatewayWallet(Base):
    __tablename__ = "payment_gateway_wallets"
    id = Column(Integer, primary_key=True)
    head_parish_id = Column(Integer, ForeignKey("head_parishes.id", ondelete="CASCADE"), nullable=False)
    wallet_name = Column(String(100), nullable=False)
    wallet_number = Column(String(50), nullable=False)
    provider = Column(String(50), nullable=False)
    is_active = Column(Boolean, default=True, nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())
