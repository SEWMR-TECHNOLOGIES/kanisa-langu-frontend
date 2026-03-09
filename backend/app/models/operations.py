# models/operations.py
from sqlalchemy import Column, Integer, String, Boolean, Date, Time, DateTime, ForeignKey, Numeric, UniqueConstraint
from sqlalchemy.sql import func
from core.base import Base


class Attendance(Base):
    __tablename__ = "attendance"
    id = Column(Integer, primary_key=True)
    management_level = Column(String(20), nullable=False)
    event_title = Column(String(200), nullable=False)
    head_parish_id = Column(Integer, ForeignKey("head_parishes.id", ondelete="CASCADE"), nullable=False)
    sub_parish_id = Column(Integer, ForeignKey("sub_parishes.id"))
    community_id = Column(Integer, ForeignKey("communities.id"))
    group_id = Column(Integer, ForeignKey("groups.id"))
    service_number = Column(Integer)
    male_attendance = Column(Integer, default=0, nullable=False)
    female_attendance = Column(Integer, default=0, nullable=False)
    children_attendance = Column(Integer, default=0, nullable=False)
    attendance_date = Column(Date, nullable=False)
    recorded_by = Column(Integer, ForeignKey("admins.id", ondelete="SET NULL"))
    created_at = Column(DateTime(timezone=True), server_default=func.now())


class AttendanceBenchmark(Base):
    __tablename__ = "attendance_benchmarks"
    id = Column(Integer, primary_key=True)
    head_parish_id = Column(Integer, ForeignKey("head_parishes.id", ondelete="CASCADE"), nullable=False)
    benchmark = Column(Integer, default=0, nullable=False)
    year = Column(Integer, nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())

    __table_args__ = (
        UniqueConstraint("head_parish_id", "year", name="uq_attendance_bench_hp_year"),
    )


class Meeting(Base):
    __tablename__ = "meetings"
    id = Column(Integer, primary_key=True)
    head_parish_id = Column(Integer, ForeignKey("head_parishes.id", ondelete="CASCADE"), nullable=False)
    title = Column(String(200), nullable=False)
    description = Column(String)
    meeting_date = Column(Date, nullable=False)
    meeting_time = Column(Time, nullable=False)
    meeting_place = Column(String(200), nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now())


class MeetingAgenda(Base):
    __tablename__ = "meeting_agendas"
    id = Column(Integer, primary_key=True)
    meeting_id = Column(Integer, ForeignKey("meetings.id", ondelete="CASCADE"), nullable=False)
    agenda_item = Column(String, nullable=False)
    sort_order = Column(Integer, default=0, nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())


class MeetingMinutes(Base):
    __tablename__ = "meeting_minutes"
    id = Column(Integer, primary_key=True)
    meeting_id = Column(Integer, ForeignKey("meetings.id", ondelete="CASCADE"), nullable=False)
    content = Column(String, nullable=False)
    recorded_by = Column(Integer, ForeignKey("admins.id", ondelete="SET NULL"))
    created_at = Column(DateTime(timezone=True), server_default=func.now())


class MeetingNotes(Base):
    __tablename__ = "meeting_notes"
    id = Column(Integer, primary_key=True)
    meeting_id = Column(Integer, ForeignKey("meetings.id", ondelete="CASCADE"), nullable=False)
    note = Column(String, nullable=False)
    recorded_by = Column(Integer, ForeignKey("admins.id", ondelete="SET NULL"))
    created_at = Column(DateTime(timezone=True), server_default=func.now())


class ChurchEvent(Base):
    __tablename__ = "church_events"
    id = Column(Integer, primary_key=True)
    head_parish_id = Column(Integer, ForeignKey("head_parishes.id", ondelete="CASCADE"), nullable=False)
    title = Column(String(200), nullable=False)
    description = Column(String)
    event_date = Column(Date, nullable=False)
    event_time = Column(Time)
    location = Column(String(200))
    created_at = Column(DateTime(timezone=True), server_default=func.now())


class Asset(Base):
    __tablename__ = "assets"
    id = Column(Integer, primary_key=True)
    head_parish_id = Column(Integer, ForeignKey("head_parishes.id", ondelete="CASCADE"), nullable=False)
    name = Column(String(200), nullable=False)
    generates_revenue = Column(Boolean, default=False, nullable=False)
    status = Column(String(30), default="Active")
    created_at = Column(DateTime(timezone=True), server_default=func.now())


class AssetRevenue(Base):
    __tablename__ = "asset_revenues"
    id = Column(Integer, primary_key=True)
    asset_id = Column(Integer, ForeignKey("assets.id", ondelete="CASCADE"), nullable=False)
    amount = Column(Numeric(15, 2), nullable=False)
    revenue_date = Column(Date, nullable=False)
    description = Column(String)
    created_at = Column(DateTime(timezone=True), server_default=func.now())


class AssetExpense(Base):
    __tablename__ = "asset_expenses"
    id = Column(Integer, primary_key=True)
    asset_id = Column(Integer, ForeignKey("assets.id", ondelete="CASCADE"), nullable=False)
    amount = Column(Numeric(15, 2), nullable=False)
    expense_date = Column(Date, nullable=False)
    description = Column(String)
    created_at = Column(DateTime(timezone=True), server_default=func.now())


class AssetStatusLog(Base):
    __tablename__ = "asset_status_log"
    id = Column(Integer, primary_key=True)
    asset_id = Column(Integer, ForeignKey("assets.id", ondelete="CASCADE"), nullable=False)
    status = Column(String(30), nullable=False)
    changed_at = Column(DateTime(timezone=True), server_default=func.now())
    notes = Column(String)
