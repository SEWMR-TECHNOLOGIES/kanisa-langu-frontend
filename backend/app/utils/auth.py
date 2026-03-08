# utils/auth.py
"""Authentication utilities for Kanisa Langu admin & member auth."""
import secrets
import hashlib
from datetime import datetime, timedelta
from typing import Optional

import jwt
from passlib.context import CryptContext
from sqlalchemy.orm import Session
from fastapi import Depends, HTTPException, Request
from fastapi.security import HTTPBearer, HTTPAuthorizationCredentials

from core.config import SECRET_KEY, ALGORITHM, ACCESS_TOKEN_EXPIRE_MINUTES, REFRESH_TOKEN_EXPIRE_DAYS
from core.database import get_db
from models.admins import Admin, SystemAdmin, AdminLogin, PasswordResetCode

pwd_context = CryptContext(schemes=["bcrypt"], deprecated="auto")
security = HTTPBearer()
security_optional = HTTPBearer(auto_error=False)


# ── Password hashing (bcrypt, matching PHP password_hash) ──────────────────

def hash_password(plain: str) -> str:
    return pwd_context.hash(plain)


def verify_password(plain: str, hashed: str) -> bool:
    return pwd_context.verify(plain, hashed)


# ── JWT token management ───────────────────────────────────────────────────

def create_access_token(data: dict, expires_delta: Optional[timedelta] = None) -> str:
    to_encode = data.copy()
    expire = datetime.utcnow() + (expires_delta or timedelta(minutes=ACCESS_TOKEN_EXPIRE_MINUTES))
    to_encode.update({"exp": expire, "type": "access"})
    return jwt.encode(to_encode, SECRET_KEY, algorithm=ALGORITHM)


def create_refresh_token(data: dict, expires_delta: Optional[timedelta] = None) -> str:
    to_encode = data.copy()
    expire = datetime.utcnow() + (expires_delta or timedelta(days=REFRESH_TOKEN_EXPIRE_DAYS))
    to_encode.update({"exp": expire, "type": "refresh"})
    return jwt.encode(to_encode, SECRET_KEY, algorithm=ALGORITHM)


def decode_token(token: str) -> Optional[dict]:
    try:
        return jwt.decode(token, SECRET_KEY, algorithms=[ALGORITHM])
    except (jwt.ExpiredSignatureError, jwt.InvalidTokenError):
        return None


# ── Current user dependency ────────────────────────────────────────────────

def get_current_admin(
    credentials: HTTPAuthorizationCredentials = Depends(security),
    db: Session = Depends(get_db),
) -> Admin:
    """Extract current admin from JWT Bearer token."""
    payload = decode_token(credentials.credentials)
    if not payload:
        raise HTTPException(status_code=401, detail="Invalid or expired token")

    admin_id = payload.get("admin_id")
    admin_level = payload.get("admin_level")

    if not admin_id:
        raise HTTPException(status_code=401, detail="Invalid token payload")

    admin = db.query(Admin).filter(Admin.id == admin_id, Admin.is_active == True).first()
    if not admin:
        raise HTTPException(status_code=401, detail="Admin not found or inactive")

    return admin


def get_current_system_admin(
    credentials: HTTPAuthorizationCredentials = Depends(security),
    db: Session = Depends(get_db),
) -> SystemAdmin:
    """Extract current system admin from JWT Bearer token."""
    payload = decode_token(credentials.credentials)
    if not payload:
        raise HTTPException(status_code=401, detail="Invalid or expired token")

    system_admin_id = payload.get("system_admin_id")
    if not system_admin_id:
        raise HTTPException(status_code=401, detail="Not a system admin token")

    admin = db.query(SystemAdmin).filter(
        SystemAdmin.id == system_admin_id, SystemAdmin.is_active == True
    ).first()
    if not admin:
        raise HTTPException(status_code=401, detail="System admin not found or inactive")

    return admin


def get_optional_admin(
    credentials: Optional[HTTPAuthorizationCredentials] = Depends(security_optional),
    db: Session = Depends(get_db),
) -> Optional[Admin]:
    """Like get_current_admin but returns None instead of 401."""
    if not credentials:
        return None
    payload = decode_token(credentials.credentials)
    if not payload:
        return None
    admin_id = payload.get("admin_id")
    if not admin_id:
        return None
    return db.query(Admin).filter(Admin.id == admin_id, Admin.is_active == True).first()


# ── Admin login tracking ──────────────────────────────────────────────────

def record_admin_login(
    db: Session,
    admin_id: Optional[int] = None,
    system_admin_id: Optional[int] = None,
    ip_address: Optional[str] = None,
    user_agent: Optional[str] = None,
):
    """Record admin login for audit trail."""
    login = AdminLogin(
        admin_id=admin_id,
        system_admin_id=system_admin_id,
        ip_address=ip_address,
        user_agent=user_agent[:500] if user_agent else None,
    )
    db.add(login)
    db.commit()


def check_first_login(db: Session, admin: Admin) -> bool:
    """Check if this is admin's first login."""
    return admin.first_login


def update_first_login(db: Session, admin: Admin):
    """Mark admin as having completed first login."""
    if admin.first_login:
        admin.first_login = False
        db.commit()


# ── Password reset codes ──────────────────────────────────────────────────

def generate_reset_code() -> str:
    """Generate a 32-char hex reset code (matches legacy PHP bin2hex(random_bytes(16)))."""
    return secrets.token_hex(16)


def save_reset_code(
    db: Session,
    admin_id: Optional[int] = None,
    system_admin_id: Optional[int] = None,
    expire_minutes: int = 60,
) -> str:
    """Generate and save a password reset code. Returns the raw code."""
    raw_code = generate_reset_code()
    reset = PasswordResetCode(
        admin_id=admin_id,
        system_admin_id=system_admin_id,
        reset_code=hash_password(raw_code),
        expires_at=datetime.utcnow() + timedelta(minutes=expire_minutes),
    )
    db.add(reset)
    db.commit()
    return raw_code


def verify_reset_code(db: Session, admin_id: int, code: str) -> bool:
    """Verify a password reset code for an admin."""
    resets = (
        db.query(PasswordResetCode)
        .filter(
            PasswordResetCode.admin_id == admin_id,
            PasswordResetCode.used == False,
            PasswordResetCode.expires_at > datetime.utcnow(),
        )
        .order_by(PasswordResetCode.created_at.desc())
        .limit(5)
        .all()
    )
    for reset in resets:
        if verify_password(code, reset.reset_code):
            reset.used = True
            db.commit()
            return True
    return False


# ── Member auth (for mobile app) ──────────────────────────────────────────

def get_member_by_credential(db, credential: str):
    """Find church member by phone, email, or envelope number."""
    from models.members import ChurchMember
    return db.query(ChurchMember).filter(
        (ChurchMember.phone == credential) |
        (ChurchMember.email == credential) |
        (ChurchMember.envelope_number == credential)
    ).first()
