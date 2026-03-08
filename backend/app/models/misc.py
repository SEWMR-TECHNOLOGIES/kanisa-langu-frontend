# models/misc.py
from sqlalchemy import Column, Integer, String, Boolean, DateTime, ForeignKey, CheckConstraint
from sqlalchemy.sql import func
from core.base import Base


class Feedback(Base):
    __tablename__ = "feedback"
    id = Column(Integer, primary_key=True)
    head_parish_id = Column(Integer, ForeignKey("head_parishes.id", ondelete="CASCADE"), nullable=False)
    member_id = Column(Integer, ForeignKey("church_members.id", ondelete="SET NULL"))
    subject = Column(String(200))
    message = Column(String, nullable=False)
    status = Column(String(20), default="pending", nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())


class FcmToken(Base):
    __tablename__ = "fcm_tokens"
    id = Column(Integer, primary_key=True)
    member_id = Column(Integer, ForeignKey("church_members.id", ondelete="CASCADE"), nullable=False)
    token = Column(String, nullable=False)
    device_type = Column(String(20))
    created_at = Column(DateTime(timezone=True), server_default=func.now())


class AppVersion(Base):
    __tablename__ = "app_versions"
    id = Column(Integer, primary_key=True)
    platform = Column(String(20), nullable=False)
    version = Column(String(20), nullable=False)
    force_update = Column(Boolean, default=False, nullable=False)
    release_notes = Column(String)
    created_at = Column(DateTime(timezone=True), server_default=func.now())

    __table_args__ = (
        CheckConstraint("platform IN ('android', 'ios', 'web')", name="ck_app_version_platform"),
    )
