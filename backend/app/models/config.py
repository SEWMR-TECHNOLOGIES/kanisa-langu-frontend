# models/config.py
from sqlalchemy import Column, Integer, String, DateTime, ForeignKey
from sqlalchemy.sql import func
from core.base import Base


class SmsApiConfig(Base):
    __tablename__ = "sms_api_config"
    id = Column(Integer, primary_key=True)
    head_parish_id = Column(Integer, ForeignKey("head_parishes.id", ondelete="CASCADE"), nullable=False, unique=True)
    account_name = Column(String(100), nullable=False)
    api_username = Column(String(100), nullable=False)
    api_password = Column(String(500), nullable=False)
    api_token = Column(String(500))
    sender_id = Column(String(50))
    created_at = Column(DateTime(timezone=True), server_default=func.now())
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now())


class RevenueGroupModel(Base):
    __tablename__ = "revenue_groups"
    id = Column(Integer, primary_key=True)
    name = Column(String(150), nullable=False)
    head_parish_id = Column(Integer, ForeignKey("head_parishes.id", ondelete="CASCADE"), nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())


class RevenueGroupStreamMap(Base):
    __tablename__ = "revenue_group_stream_map"
    id = Column(Integer, primary_key=True)
    revenue_group_id = Column(Integer, ForeignKey("revenue_groups.id", ondelete="CASCADE"), nullable=False)
    revenue_stream_id = Column(Integer, ForeignKey("revenue_streams.id", ondelete="CASCADE"), nullable=False)


class ProgramRevenueMap(Base):
    __tablename__ = "program_revenue_map"
    id = Column(Integer, primary_key=True)
    head_parish_id = Column(Integer, ForeignKey("head_parishes.id", ondelete="CASCADE"), nullable=False)
    program_name = Column(String(100), nullable=False)
    revenue_stream_id = Column(Integer, ForeignKey("revenue_streams.id", ondelete="CASCADE"), nullable=False)
