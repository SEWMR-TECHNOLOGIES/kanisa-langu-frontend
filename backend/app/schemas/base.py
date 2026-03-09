# schemas/base.py
"""Generic API response envelope + common patterns."""
from __future__ import annotations

from typing import Generic, TypeVar, Optional, List
from pydantic import BaseModel, Field
from pydantic.generics import GenericModel

T = TypeVar("T")


class ApiResponse(GenericModel, Generic[T]):
    """Standard API response wrapper used across all endpoints."""
    success: bool = Field(True, description="Whether the request was successful")
    message: str = Field("Success", description="Human-readable status message")
    data: Optional[T] = Field(None, description="Response payload")

    class Config:
        json_schema_extra = {
            "example": {"success": True, "message": "Success", "data": None}
        }


class PaginatedData(GenericModel, Generic[T]):
    """Paginated list wrapper."""
    items: List[T] = Field(default_factory=list, alias="members")
    total: int = Field(0, description="Total records matching the query")
    page: int = Field(1, description="Current page number")
    total_pages: int = Field(1, description="Total number of pages")


class IdData(BaseModel):
    """Returned when a new resource is created."""
    id: int = Field(..., description="ID of the newly created resource")


class MessageResponse(BaseModel):
    """Simple success/error response with no data payload."""
    success: bool = True
    message: str = "Success"
    data: Optional[dict] = None
