# utils/payment_gateway.py
"""Payment gateway integration mirroring legacy PaymentGateway.php (Selcom via Sewmr proxy)."""
import httpx
from typing import Optional
from core.config import PAYMENT_GATEWAY_URL


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


async def request_payment(
    phone: str,
    amount: float,
    description: str,
    buyer_name: str,
    buyer_email: str = "",
) -> dict:
    """Request a payment through Selcom proxy."""
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
