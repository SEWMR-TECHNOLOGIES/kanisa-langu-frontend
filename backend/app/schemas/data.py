# schemas/data.py
"""Reference data response schemas."""
from pydantic import BaseModel
from typing import Optional


class NameItem(BaseModel):
    """Generic id + name lookup item."""
    id: int
    name: str


class DistrictItem(BaseModel):
    id: int
    name: str
    region_id: int


class ColorItem(BaseModel):
    id: int
    name: str
    code: Optional[str] = None


class PraiseSongItem(BaseModel):
    id: int
    song_number: int
    name: str


class UnitOfMeasureItem(BaseModel):
    id: int
    unit: str
    meaning: Optional[str] = None
