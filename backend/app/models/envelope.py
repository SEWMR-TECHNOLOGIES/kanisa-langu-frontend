# models/envelope.py
from sqlalchemy import Column, Integer, String, Numeric, Date, DateTime, ForeignKey
from sqlalchemy.sql import func
from core.base import Base


class EnvelopeTarget(Base):
    __tablename__ = "envelope_targets"
    id = Column(Integer, primary_key=True)
    member_id = Column(Integer, ForeignKey("church_members.id", ondelete="CASCADE"), nullable=False)
    target = Column(Numeric(15, 2), nullable=False)
    from_date = Column(Date, nullable=False)
    end_date = Column(Date, nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())


class EnvelopeContribution(Base):
    __tablename__ = "envelope_contributions"
    id = Column(Integer, primary_key=True)
    member_id = Column(Integer, ForeignKey("church_members.id", ondelete="CASCADE"), nullable=False)
    amount = Column(Numeric(15, 2), nullable=False)
    contribution_date = Column(Date, nullable=False)
    head_parish_id = Column(Integer, ForeignKey("head_parishes.id", ondelete="CASCADE"), nullable=False)
    sub_parish_id = Column(Integer, ForeignKey("sub_parishes.id", ondelete="SET NULL"))
    community_id = Column(Integer, ForeignKey("communities.id", ondelete="SET NULL"))
    payment_method = Column(String(30), default="Cash", nullable=False)
    recorded_by = Column(Integer, ForeignKey("admins.id", ondelete="SET NULL"))
    local_timestamp = Column(DateTime(timezone=True))
    created_at = Column(DateTime(timezone=True), server_default=func.now())
