# schemas/envelope.py
"""Envelope contribution & target schemas."""
from pydantic import BaseModel, Field
from typing import Optional, List
from datetime import date


class EnvelopeTargetCreate(BaseModel):
    member_id: int
    target: float = Field(..., gt=0)
    from_date: date
    end_date: date


class EnvelopeTargetOut(BaseModel):
    id: int
    member_id: int
    target: float
    from_date: str
    end_date: str


class EnvelopeContributionCreate(BaseModel):
    member_id: int
    amount: float = Field(..., gt=0)
    contribution_date: date
    head_parish_id: int
    payment_method: str = "Cash"


class EnvelopeContributionOut(BaseModel):
    id: int
    amount: float
    contribution_date: str
    payment_method: str


class EnvelopeContributionsData(BaseModel):
    contributions: List[EnvelopeContributionOut]
    total: float


class EnvelopeSummaryOut(BaseModel):
    total_contributions: float
