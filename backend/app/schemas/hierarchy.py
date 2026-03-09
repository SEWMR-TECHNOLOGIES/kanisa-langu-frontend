# schemas/hierarchy.py
"""Church hierarchy request & response schemas."""
from pydantic import BaseModel, Field
from typing import Optional, List


# ── Requests ──────────────────────────────────────────────────

class DioceseCreate(BaseModel):
    name: str = Field(..., description="Diocese name (will be uppercased)")
    region_id: int
    district_id: int
    address: str
    email: str = Field(..., description="Valid email address")
    phone: str = Field(..., description="Valid phone number")


class ProvinceCreate(BaseModel):
    name: str
    diocese_id: int
    region_id: int
    district_id: int
    address: str
    email: str
    phone: str


class HeadParishCreate(BaseModel):
    name: str
    diocese_id: int
    province_id: int
    region_id: int
    district_id: int
    address: str
    email: str
    phone: str = ""


class SubParishCreate(BaseModel):
    name: str
    head_parish_id: int
    description: Optional[str] = None


class CommunityCreate(BaseModel):
    name: str
    head_parish_id: int
    sub_parish_id: int
    description: Optional[str] = None


class GroupCreate(BaseModel):
    name: str
    head_parish_id: int
    description: Optional[str] = None


# ── Responses ─────────────────────────────────────────────────

class DioceseOut(BaseModel):
    id: int
    name: str
    address: str
    email: str
    phone: str
    region_id: Optional[int] = None
    district_id: Optional[int] = None


class ProvinceOut(BaseModel):
    id: int
    name: str
    diocese_id: int
    address: str
    email: str
    phone: str
    region_id: Optional[int] = None
    district_id: Optional[int] = None


class HeadParishOut(BaseModel):
    id: int
    name: str
    diocese_id: int
    province_id: int
    address: str
    email: str
    phone: Optional[str] = None


class SubParishOut(BaseModel):
    id: int
    name: str
    description: Optional[str] = None


class CommunityOut(BaseModel):
    id: int
    name: str
    sub_parish_id: int


class GroupOut(BaseModel):
    id: int
    name: str
    description: Optional[str] = None
