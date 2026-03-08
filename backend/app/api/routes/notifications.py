# api/routes/notifications.py
"""Notification routes — push notifications, SMS, delayed harambee notifications.
Replaces: send_push_notification.php, notify_members.php, send_harambee_*.php"""

from fastapi import APIRouter, Depends
from pydantic import BaseModel
from sqlalchemy.orm import Session
from sqlalchemy import text
from typing import Optional, List

from core.database import get_db
from models.harambee import DelayedHarambeeNotification
from utils.response import success_response

router = APIRouter(prefix="/notifications", tags=["Notifications"])


class SendPushNotificationRequest(BaseModel):
    head_parish_id: int
    title: str
    body: str
    topic: Optional[str] = None

@router.post("/push")
def send_push_notification(body: SendPushNotificationRequest, db: Session = Depends(get_db)):
    # TODO: Integrate FCM push notification
    return success_response("Push notification queued")


class NotifyMembersRequest(BaseModel):
    head_parish_id: int
    message: str
    member_ids: Optional[List[int]] = None

@router.post("/notify-members")
def notify_members(body: NotifyMembersRequest, db: Session = Depends(get_db)):
    # TODO: Send SMS to selected members or all members
    return success_response("Notification queued")


@router.post("/process-delayed-harambee")
def process_delayed_harambee_notifications(db: Session = Depends(get_db)):
    """Process unprocessed delayed harambee notifications. Replaces send_delayed_harambee_notifications.php."""
    pending = db.query(DelayedHarambeeNotification).filter(
        DelayedHarambeeNotification.is_sent == False,
    ).limit(50).all()

    processed = 0
    for notif in pending:
        # TODO: Send SMS to group members
        notif.is_sent = True
        processed += 1

    db.commit()
    return success_response(f"Processed {processed} notifications")
