# utils/payment_gateway.py
"""Payment gateway integration — complete port of legacy PaymentGateway.php
(Selcom via Sewmr proxy + SasaPay) with DB insert methods."""
import logging
from datetime import date
from typing import Optional

import httpx
from sqlalchemy import text
from sqlalchemy.orm import Session

logger = logging.getLogger(__name__)

PAYMENT_GATEWAY_URL = "https://payment-gateway.sewmrtechnologies.com/selcom"

NETWORK_MAP = {
    "76": "VODACOM", "75": "VODACOM", "74": "VODACOM",
    "65": "TIGO", "71": "TIGO", "77": "TIGO", "67": "TIGO",
    "69": "AIRTEL", "68": "AIRTEL", "78": "AIRTEL",
    "61": "HALOPESA", "62": "HALOPESA",
}


def identify_network(phone: str) -> str:
    """Identify mobile network from phone number (255xxx format)."""
    cleaned = phone[3:] if phone.startswith("255") else phone
    prefix = cleaned[:2]
    return NETWORK_MAP.get(prefix, "UNKNOWN")


def generate_merchant_request_id(db: Session) -> str:
    """Generate MerchantRequestID based on last payment ID."""
    row = db.execute(text("SELECT COALESCE(MAX(payment_id), 0) FROM harambee_payments")).first()
    last_id = row[0] if row else 0
    new_id = last_id + 1
    return f"KL_{date.today().year}_{date.today().month:02d}_{new_id}"


# ── Payment request via Selcom proxy ──────────────────────────────────────

async def request_payment(phone: str, amount: float, description: str,
                          buyer_name: str, buyer_email: str = "") -> dict:
    """Request a payment through Selcom proxy."""
    network = identify_network(phone)
    if network == "UNKNOWN":
        return {"status": False, "message": "Invalid network for the given phone number"}

    payload = {
        "amount": amount,
        "buyer_phone": phone,
        "buyer_email": buyer_email,
        "buyer_name": buyer_name,
        "buyer_remarks": description,
        "merchant_remarks": description,
        "no_of_items": 1,
    }
    try:
        async with httpx.AsyncClient(timeout=60) as client:
            resp = await client.post(
                f"{PAYMENT_GATEWAY_URL}/make-payment.php",
                data=payload,
                headers={"Content-Type": "application/x-www-form-urlencoded"},
            )
            if resp.status_code != 200:
                return {"status": False, "message": "Payment request failed"}
            data = resp.json()
            return {"status": True, "response": data, "message": "Payment processed"}
    except Exception as e:
        return {"status": False, "message": str(e)}


async def check_payment_status(order_id: str) -> dict:
    """Check payment status through Selcom proxy."""
    try:
        async with httpx.AsyncClient(timeout=30) as client:
            resp = await client.post(
                f"{PAYMENT_GATEWAY_URL}/check-payment-status.php",
                data={"order_id": order_id},
                headers={"Content-Type": "application/x-www-form-urlencoded"},
            )
            data = resp.json()
            payment_status = data.get("payment_status", "").upper()
            if payment_status == "COMPLETED":
                return {"status": True, "message": "Payment Successful", "payment_status": payment_status}
            return {"status": False, "message": f"Status: {payment_status}", "payment_status": payment_status}
    except Exception as e:
        return {"status": False, "message": str(e)}


# ── DB insert methods (mirrors legacy PaymentGateway PHP class) ───────────

def insert_harambee_payment_data(db: Session, member_id: int, harambee_id: int,
                                  head_parish_id: int, response_data: dict,
                                  amount: float, payment_reason: str, payment_date: str,
                                  target: str = "head-parish", payment_status: str = "Pending") -> bool:
    """Insert harambee payment record. Mirrors insertHarambeePaymentData."""
    order_resp = response_data.get("response", {})
    checkout_request_id = order_resp.get("order_id")
    transaction_reference = (order_resp.get("wallet_payment_response") or {}).get("transid")
    merchant_request_id = order_resp.get("order_id")

    try:
        db.execute(text("""
            INSERT INTO harambee_payments
            (member_id, harambee_id, head_parish_id, PaymentGateway, MerchantRequestID,
             CheckoutRequestID, TransactionReference, amount_paid, payment_reason,
             target, payment_date, payment_status)
            VALUES (:mid, :hid, :hpid, 'SELCOM', :mrid, :crid, :tref, :amt, :pr, :tgt, :pd, :ps)
        """), {
            "mid": member_id, "hid": harambee_id, "hpid": head_parish_id,
            "mrid": merchant_request_id, "crid": checkout_request_id,
            "tref": transaction_reference, "amt": amount, "pr": payment_reason,
            "tgt": target, "pd": payment_date, "ps": payment_status,
        })
        db.commit()
        return True
    except Exception as e:
        logger.error(f"insert_harambee_payment_data error: {e}")
        db.rollback()
        return False


def insert_envelope_payment_data(db: Session, member_id: int, head_parish_id: int,
                                  response_data: dict, amount: float,
                                  payment_reason: str, payment_date: str,
                                  payment_status: str = "Pending") -> bool:
    """Insert envelope payment record. Mirrors insertEnvelopePaymentData."""
    order_resp = response_data.get("response", {})
    checkout_request_id = order_resp.get("order_id")
    transaction_reference = (order_resp.get("wallet_payment_response") or {}).get("transid")
    merchant_request_id = order_resp.get("order_id")

    try:
        db.execute(text("""
            INSERT INTO envelope_payments
            (member_id, head_parish_id, PaymentGateway, MerchantRequestID,
             CheckoutRequestID, TransactionReference, amount_paid,
             payment_reason, payment_date, payment_status)
            VALUES (:mid, :hpid, 'SELCOM', :mrid, :crid, :tref, :amt, :pr, :pd, :ps)
        """), {
            "mid": member_id, "hpid": head_parish_id,
            "mrid": merchant_request_id, "crid": checkout_request_id,
            "tref": transaction_reference, "amt": amount,
            "pr": payment_reason, "pd": payment_date, "ps": payment_status,
        })
        db.commit()
        return True
    except Exception as e:
        logger.error(f"insert_envelope_payment_data error: {e}")
        db.rollback()
        return False


def insert_sunday_service_payment_data(db: Session, member_id: int, head_parish_id: int,
                                        response_data: dict, amount: float,
                                        payment_reason: str, payment_date: str,
                                        service_id: int, revenue_stream_id: int,
                                        service_date: str,
                                        payment_status: str = "Pending") -> bool:
    """Insert sunday service payment record. Mirrors insertSundayServicePaymentData."""
    order_resp = response_data.get("response", {})
    checkout_request_id = order_resp.get("order_id")
    transaction_reference = (order_resp.get("wallet_payment_response") or {}).get("transid")
    merchant_request_id = order_resp.get("order_id")

    try:
        db.execute(text("""
            INSERT INTO sunday_service_payments
            (member_id, head_parish_id, PaymentGateway, MerchantRequestID,
             CheckoutRequestID, TransactionReference, revenue_stream_id,
             service_id, amount_paid, payment_reason, payment_date,
             service_date, payment_status)
            VALUES (:mid, :hpid, 'SELCOM', :mrid, :crid, :tref, :rsid,
                    :sid, :amt, :pr, :pd, :sd, :ps)
        """), {
            "mid": member_id, "hpid": head_parish_id,
            "mrid": merchant_request_id, "crid": checkout_request_id,
            "tref": transaction_reference, "rsid": revenue_stream_id,
            "sid": service_id, "amt": amount, "pr": payment_reason,
            "pd": payment_date, "sd": service_date, "ps": payment_status,
        })
        db.commit()
        return True
    except Exception as e:
        logger.error(f"insert_sunday_service_payment_data error: {e}")
        db.rollback()
        return False
