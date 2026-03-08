# api/routes/assets.py
"""Asset management routes."""
from fastapi import APIRouter, Depends, HTTPException
from pydantic import BaseModel
from sqlalchemy.orm import Session
from typing import Optional
from datetime import date

from core.database import get_db
from models.operations import Asset, AssetRevenue, AssetExpense, AssetStatusLog
from utils.response import success_response

router = APIRouter(prefix="/assets", tags=["Assets"])


class AssetCreate(BaseModel):
    head_parish_id: int
    name: str
    generates_revenue: bool = False


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


@router.get("/")
def list_assets(head_parish_id: int, db: Session = Depends(get_db)):
    rows = db.query(Asset).filter(Asset.head_parish_id == head_parish_id).order_by(Asset.name).all()
    return success_response(data=[{
        "id": a.id, "name": a.name, "generates_revenue": a.generates_revenue, "status": a.status,
    } for a in rows])


@router.post("/")
def create_asset(body: AssetCreate, db: Session = Depends(get_db)):
    if db.query(Asset).filter(Asset.name == body.name, Asset.head_parish_id == body.head_parish_id).first():
        raise HTTPException(400, "Asset already exists")
    a = Asset(**body.dict())
    db.add(a); db.commit(); db.refresh(a)
    return success_response("Asset added", {"id": a.id})


@router.post("/revenues")
def record_asset_revenue(body: AssetRevenueCreate, db: Session = Depends(get_db)):
    r = AssetRevenue(**body.dict())
    db.add(r); db.commit()
    return success_response("Asset revenue recorded")


@router.post("/expenses")
def record_asset_expense(body: AssetExpenseCreate, db: Session = Depends(get_db)):
    e = AssetExpense(**body.dict())
    db.add(e); db.commit()
    return success_response("Asset expense recorded")
