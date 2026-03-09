# api/routes/notifications.py
"""Notification routes — push notifications, SMS, delayed harambee notifications."""
from fastapi import APIRouter, Depends
from sqlalchemy.orm import Session

from core.database import get_db
from models.harambee import DelayedHarambeeNotification
from utils.response import success_response

from schemas.base import ApiResponse
from schemas.operations import SendPushNotificationRequest, NotifyMembersRequest

router = APIRouter(prefix="/notifications", tags=["Notifications"])


@router.post("/push", response_model=ApiResponse[None], summary="Send push notification to a head parish topic")
def send_push_notification(body: SendPushNotificationRequest, db: Session = Depends(get_db)):
    # TODO: Integrate FCM push notification
    return success_response("Push notification queued")


@router.post("/notify-members", response_model=ApiResponse[None], summary="Send SMS notification to members")
def notify_members(body: NotifyMembersRequest, db: Session = Depends(get_db)):
    # TODO: Send SMS to selected members or all members
    return success_response("Notification queued")


@router.post("/process-delayed-harambee", response_model=ApiResponse[None], summary="Process pending delayed harambee notifications")
def process_delayed_harambee_notifications(db: Session = Depends(get_db)):
    """Process unprocessed delayed harambee notifications."""
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
