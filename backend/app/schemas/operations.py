# schemas/operations.py
"""Attendance, meetings, assets, sunday services, notifications schemas."""
from pydantic import BaseModel, Field
from typing import Optional, List
from datetime import date


# ── Attendance ────────────────────────────────────────────────

class AttendanceCreate(BaseModel):
    management_level: str
    event_title: str = Field(..., min_length=1)
    head_parish_id: int
    sub_parish_id: Optional[int] = None
    community_id: Optional[int] = None
    group_id: Optional[int] = None
    service_number: Optional[int] = None
    male_attendance: int = 0
    female_attendance: int = 0
    children_attendance: int = 0
    attendance_date: date


class AttendanceOut(BaseModel):
    id: int
    event_title: str
    male: int
    female: int
    children: int
    total: int
    date: str


class AttendanceSummaryOut(BaseModel):
    total_male: int
    total_female: int
    total_children: int
    total_records: int


class BenchmarkCreate(BaseModel):
    head_parish_id: int
    benchmark: int
    year: int


# ── Meetings ──────────────────────────────────────────────────

class MeetingCreate(BaseModel):
    head_parish_id: int
    title: str = Field(..., min_length=1)
    description: Optional[str] = None
    meeting_date: date
    meeting_time: str = Field(..., description="ISO format time string HH:MM")
    meeting_place: str


class MeetingOut(BaseModel):
    id: int
    title: str
    meeting_date: str
    meeting_time: str
    meeting_place: str


# ── Assets ────────────────────────────────────────────────────

class AssetCreate(BaseModel):
    head_parish_id: int
    name: str
    generates_revenue: bool = False


class AssetOut(BaseModel):
    id: int
    name: str
    generates_revenue: bool
    status: Optional[str] = None


class AssetRevenueCreate(BaseModel):
    asset_id: int
    amount: float
    revenue_date: date
    description: Optional[str] = None


class AssetExpenseCreate(BaseModel):
    asset_id: int
    amount: float
    expense_date: date
    description: Optional[str] = None


# ── Sunday Services ───────────────────────────────────────────

class SundayServiceCreate(BaseModel):
    head_parish_id: int
    service_date: date
    service_color_id: Optional[int] = None
    large_liturgy_page_number: Optional[int] = None
    small_liturgy_page_number: Optional[int] = None
    large_antiphony_page_number: Optional[int] = None
    small_antiphony_page_number: Optional[int] = None
    large_praise_page_number: Optional[int] = None
    small_praise_page_number: Optional[int] = None
    base_scripture_text: Optional[str] = None


class SundayServiceOut(BaseModel):
    id: int
    service_date: str
    service_color_id: Optional[int] = None
    base_scripture_text: Optional[str] = None


# ── Notifications ─────────────────────────────────────────────

class SendPushNotificationRequest(BaseModel):
    head_parish_id: int
    title: str
    body: str
    topic: Optional[str] = None


class NotifyMembersRequest(BaseModel):
    head_parish_id: int
    message: str
    member_ids: Optional[List[int]] = None


# ── Payments ──────────────────────────────────────────────────

class PaymentRequest(BaseModel):
    member_id: int
    head_parish_id: int
    amount: float = Field(..., gt=0)
    phone: str
    payment_reason: str = Field(..., description="harambee, envelope, or offering")
    buyer_name: str
    buyer_email: str = ""
    description: str = ""
    payment_date: date
    harambee_id: Optional[int] = None
    service_id: Optional[int] = None
    revenue_stream_id: Optional[int] = None


class PaymentOut(BaseModel):
    id: int
    amount: float
    payment_reason: str
    payment_status: Optional[str] = None
    payment_date: str
