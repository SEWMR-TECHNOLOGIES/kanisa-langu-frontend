# schemas/members.py
"""Church member request & response schemas."""
from pydantic import BaseModel, Field
from typing import Optional, List
from datetime import date


class MemberCreate(BaseModel):
    title_id: Optional[int] = None
    first_name: str = Field(..., min_length=1)
    middle_name: Optional[str] = None
    last_name: str = Field(..., min_length=1)
    date_of_birth: date = Field(..., description="Must be at least 5 years ago")
    gender: str = Field(..., description="Male or Female")
    member_type: str = Field("Mwenyeji", description="Mgeni or Mwenyeji")
    head_parish_id: int
    sub_parish_id: int
    community_id: int
    envelope_number: Optional[str] = None
    occupation_id: Optional[int] = None
    phone: Optional[str] = None
    email: Optional[str] = None


class MemberUpdate(BaseModel):
    title_id: Optional[int] = None
    first_name: Optional[str] = None
    middle_name: Optional[str] = None
    last_name: Optional[str] = None
    occupation_id: Optional[int] = None
    phone: Optional[str] = None
    email: Optional[str] = None
    status: Optional[str] = None


class MemberOut(BaseModel):
    id: int
    first_name: str
    middle_name: Optional[str] = None
    last_name: str
    date_of_birth: str
    gender: str
    member_type: str
    envelope_number: Optional[str] = None
    phone: Optional[str] = None
    email: Optional[str] = None
    sub_parish_id: Optional[int] = None
    community_id: Optional[int] = None
    status: Optional[str] = None


class MemberListOut(BaseModel):
    members: List[MemberOut]
    total: int
    page: int
    total_pages: int


class MemberBrief(BaseModel):
    """Short member record for lists."""
    id: int
    first_name: str
    middle_name: Optional[str] = None
    last_name: str
    envelope_number: Optional[str] = None
    phone: Optional[str] = None
    gender: Optional[str] = None
    member_type: Optional[str] = None
    sub_parish_id: Optional[int] = None
    community_id: Optional[int] = None


class LeaderOut(BaseModel):
    id: int
    first_name: str
    last_name: str
    role_id: int
    status: Optional[str] = None
    appointment_date: str


class ChoirOut(BaseModel):
    id: int
    name: str


class MemberStatsOut(BaseModel):
    total: int
    male: int
    female: int
    excluded: int
