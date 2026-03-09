# schemas/reports.py
"""Report response schemas for OpenAPI documentation."""
from pydantic import BaseModel
from typing import Optional, List


class FinancialSummary(BaseModel):
    total_revenue: float
    total_expense: float
    balance: float


class EnvelopeSummary(BaseModel):
    total_contributions: float


class HarambeeSummary(BaseModel):
    total_contributions: float
    unique_contributors: int


class AttendanceSummary(BaseModel):
    total_male: int
    total_female: int
    total_children: int
    total_records: int


class RevenueByStream(BaseModel):
    id: int
    stream_name: str
    total: float


class ExpenseByGroup(BaseModel):
    id: int
    group_name: str
    total: float


class MonthlyAmount(BaseModel):
    month: int
    total: float


class MemberStats(BaseModel):
    total: int
    male: int
    female: int
    excluded: int


class HarambeeClassReport(BaseModel):
    class_name: str
    min_amount: float
    max_amount: Optional[float] = None
    contributors: int
    total: float


class HarambeeProgress(BaseModel):
    harambee_id: int
    name: str
    target: float
    total_contributions: float
    unique_contributors: int
    percentage: float


class CommunityContribution(BaseModel):
    community_id: int
    community_name: str
    member_count: int
    cash: float
    bank_transfer: float
    mobile_payment: float
    total: float


class SubParishDailySummary(BaseModel):
    sub_parish_id: int
    sub_parish_name: str
    communities: List[CommunityContribution]


class MemberHarambeeStatus(BaseModel):
    target: float
    total_contribution: float
    balance: float
    percentage: float
    balance_text: str


class ContributionDetail(BaseModel):
    id: int
    amount: float
    contribution_date: str
    payment_method: str


class MemberHarambeeContributions(BaseModel):
    contributions: List[ContributionDetail]
    summary: MemberHarambeeStatus
