# schemas/harambee.py
"""Harambee (fundraising) request & response schemas."""
from pydantic import BaseModel, Field
from typing import Optional, List
from datetime import date


class HarambeeCreate(BaseModel):
    management_level: str
    head_parish_id: int
    sub_parish_id: Optional[int] = None
    community_id: Optional[int] = None
    group_id: Optional[int] = None
    account_id: int
    name: str = Field(..., min_length=1)
    description: str
    from_date: date
    to_date: date
    amount: float = Field(..., gt=0, description="Fundraising target amount")


class HarambeeOut(BaseModel):
    id: int
    name: str
    description: str
    from_date: str
    to_date: str
    amount: float
    management_level: str


class HarambeeDetailOut(BaseModel):
    id: int
    name: str
    description: str
    from_date: str
    to_date: str
    amount: float
    total_contributions: float


class HarambeeContributionCreate(BaseModel):
    harambee_id: int
    member_id: int
    amount: float = Field(..., gt=0)
    contribution_date: date
    head_parish_id: int
    payment_method: str = "Cash"


class ContributionOut(BaseModel):
    id: int
    member_id: Optional[int] = None
    amount: float
    contribution_date: str
    payment_method: str


class HarambeeProgressOut(BaseModel):
    harambee_id: int
    name: str
    target: float
    total_contributions: float
    unique_contributors: int
    percentage: float


class HarambeeSummaryOut(BaseModel):
    total_contributions: float
    unique_contributors: int


class MemberHarambeeStatusOut(BaseModel):
    target: float
    total_contribution: float
    balance: float
    percentage: float
    balance_text: str
