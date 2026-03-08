# api/routes/payments.py
"""Payment gateway routes (Selcom via Sewmr proxy)."""
from fastapi import APIRouter, Depends, HTTPException
from pydantic import BaseModel
from sqlalchemy.orm import Session
from datetime import date

from core.database import get_db
from models.payments import Payment
from utils.payment_gateway import request_payment, check_payment_status
from utils.response import success_response

router = APIRouter(prefix="/payments", tags=["Payments"])


class PaymentRequest(BaseModel):
    member_id: int
    head_parish_id: int
    amount: float
    phone: str
    payment_reason: str  # harambee, envelope, offering
    buyer_name: str
    buyer_email: str = ""
    description: str = ""
    payment_date: date
    harambee_id: int | None = None
    service_id: int | None = None
    revenue_stream_id: int | None = None


class PaymentStatusCheck(BaseModel):
    order_id: str


@router.post("/request")
async def make_payment_request(body: PaymentRequest, db: Session = Depends(get_db)):
    result = await request_payment(
        phone=body.phone,
        amount=body.amount,
        description=body.description or body.payment_reason,
        buyer_name=body.buyer_name,
        buyer_email=body.buyer_email,
    )

    if not result.get("status"):
        raise HTTPException(400, result.get("message", "Payment request failed"))

    response_data = result.get("response", {})
    payment = Payment(
        member_id=body.member_id,
        head_parish_id=body.head_parish_id,
        amount=body.amount,
        payment_reason=body.payment_reason,
        payment_date=body.payment_date,
        checkout_request_id=response_data.get("order_id"),
        merchant_request_id=response_data.get("order_id"),
        transaction_reference=response_data.get("wallet_payment_response", {}).get("transid"),
        harambee_id=body.harambee_id,
        service_id=body.service_id,
        revenue_stream_id=body.revenue_stream_id,
    )
    db.add(payment); db.commit(); db.refresh(payment)
    return success_response("Payment initiated", {"payment_id": payment.id, "order_id": response_data.get("order_id")})


@router.post("/check-status")
async def check_status(body: PaymentStatusCheck, db: Session = Depends(get_db)):
    result = await check_payment_status(body.order_id)

    # Update payment record if completed
    if result.get("status"):
        payment = db.query(Payment).filter(Payment.checkout_request_id == body.order_id).first()
        if payment:
            payment.payment_status = "Completed"
            db.commit()

    return success_response(data=result)


@router.get("/")
def list_payments(member_id: int | None = None, head_parish_id: int | None = None, db: Session = Depends(get_db)):
    q = db.query(Payment)
    if member_id:
        q = q.filter(Payment.member_id == member_id)
    if head_parish_id:
        q = q.filter(Payment.head_parish_id == head_parish_id)
    rows = q.order_by(Payment.created_at.desc()).limit(100).all()
    return success_response(data=[{
        "id": p.id, "amount": float(p.amount), "payment_reason": p.payment_reason,
        "payment_status": p.payment_status, "payment_date": str(p.payment_date),
    } for p in rows])
