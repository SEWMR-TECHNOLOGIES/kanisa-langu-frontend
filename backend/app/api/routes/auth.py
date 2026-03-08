# api/routes/auth.py
"""Authentication routes for Kanisa Langu admin signin (all levels) + member signin.
Replaces: kanisalangu_admin_signin.php, diocese_admin_signin.php, province_admin_signin.php,
head_parish_admin_signin.php, sub_parish_admin_signin.php, community_admin_signin.php,
group_admin_signin.php, update_admin_password.php, verify_password_reset_code.php,
deleting_account.php"""

from datetime import datetime, timedelta
from fastapi import APIRouter, Depends, Request, Response, HTTPException
from pydantic import BaseModel
from sqlalchemy.orm import Session
from typing import Optional

from core.database import get_db
from models.admins import Admin, SystemAdmin, AdminLogin, PasswordResetCode
from models.members import ChurchMember
from models.misc import MemberOtpCode
from utils.auth import (
    hash_password, verify_password, create_access_token, create_refresh_token,
    decode_token, record_admin_login, check_first_login, update_first_login,
    save_reset_code, verify_reset_code, get_current_admin, get_current_system_admin,
)
from utils.validation import is_valid_email, normalize_phone
from utils.response import success_response, error_response

router = APIRouter(prefix="/auth", tags=["Authentication"])


# ═══════════════════════════════════════════════════════════════
# ADMIN SIGNIN (unified for all levels)
# Replaces: diocese_admin_signin.php, province_admin_signin.php, etc.
# ═══════════════════════════════════════════════════════════════
class AdminSigninRequest(BaseModel):
    credential: str  # email or phone
    password: str
    admin_level: Optional[str] = None  # optional filter

@router.post("/admin/signin")
def admin_signin(body: AdminSigninRequest, request: Request, db: Session = Depends(get_db)):
    if not body.credential.strip():
        return error_response("Email or phone is required")
    if not body.password:
        return error_response("Password is required")

    credential = body.credential.strip()

    # Find admin by email or phone
    admin = db.query(Admin).filter(
        Admin.is_active == True,
        (Admin.email == credential) | (Admin.phone == credential)
    )
    if body.admin_level:
        admin = admin.filter(Admin.admin_level == body.admin_level)
    admin = admin.first()

    if not admin:
        return error_response("Invalid credentials")

    if not verify_password(body.password, admin.password):
        return error_response("Invalid credentials")

    # Check first login
    is_first_login = check_first_login(db, admin)

    # Generate JWT
    token_data = {
        "admin_id": admin.id,
        "admin_level": admin.admin_level,
        "role": admin.role,
    }
    # Add entity IDs based on admin level
    if admin.head_parish_id:
        token_data["head_parish_id"] = admin.head_parish_id
    if admin.diocese_id:
        token_data["diocese_id"] = admin.diocese_id
    if admin.province_id:
        token_data["province_id"] = admin.province_id

    access_token = create_access_token(token_data)
    refresh_token = create_refresh_token(token_data)

    # Record login
    record_admin_login(
        db, admin_id=admin.id,
        ip_address=request.client.host if request.client else None,
        user_agent=request.headers.get("user-agent"),
    )

    # Build response with entity names (like legacy)
    admin_data = {
        "admin_id": admin.id,
        "fullname": admin.fullname,
        "email": admin.email,
        "phone": admin.phone,
        "role": admin.role,
        "admin_level": admin.admin_level,
        "first_time_login": is_first_login,
        "head_parish_id": admin.head_parish_id,
        "diocese_id": admin.diocese_id,
        "province_id": admin.province_id,
        "sub_parish_id": admin.sub_parish_id,
        "community_id": admin.community_id,
        "group_id": admin.group_id,
    }

    if is_first_login:
        # Generate password reset code for first login
        raw_code = save_reset_code(db, admin_id=admin.id)
        admin_data["reset_code"] = raw_code

    return success_response("Login successful", {
        "admin": admin_data,
        "access_token": access_token,
        "refresh_token": refresh_token,
        "token_type": "Bearer",
    })


# ═══════════════════════════════════════════════════════════════
# SYSTEM ADMIN (Kanisa Langu) SIGNIN
# Replaces: kanisalangu_admin_signin.php
# ═══════════════════════════════════════════════════════════════
class SystemAdminSigninRequest(BaseModel):
    username: str
    password: str

@router.post("/system-admin/signin")
def system_admin_signin(body: SystemAdminSigninRequest, request: Request, db: Session = Depends(get_db)):
    if not body.username.strip() or not body.password:
        return error_response("Username and password are required")

    admin = db.query(SystemAdmin).filter(
        SystemAdmin.username == body.username.strip(),
        SystemAdmin.is_active == True,
    ).first()

    if not admin or not verify_password(body.password, admin.password):
        return error_response("Invalid username or password")

    token_data = {"system_admin_id": admin.id, "role": admin.role}
    access_token = create_access_token(token_data)
    refresh_token = create_refresh_token(token_data)

    record_admin_login(
        db, system_admin_id=admin.id,
        ip_address=request.client.host if request.client else None,
        user_agent=request.headers.get("user-agent"),
    )

    return success_response("Login successful", {
        "admin": {"id": admin.id, "username": admin.username, "role": admin.role},
        "access_token": access_token,
        "refresh_token": refresh_token,
        "token_type": "Bearer",
    })


# ═══════════════════════════════════════════════════════════════
# ADMIN PASSWORD UPDATE
# Replaces: update_admin_password.php
# ═══════════════════════════════════════════════════════════════
class AdminPasswordUpdate(BaseModel):
    new_password: str
    confirm_password: str
    reset_code: Optional[str] = None

@router.post("/admin/update-password")
def update_admin_password(body: AdminPasswordUpdate, admin: Admin = Depends(get_current_admin), db: Session = Depends(get_db)):
    if body.new_password != body.confirm_password:
        return error_response("Passwords do not match")

    # Validate password strength
    pwd = body.new_password
    if len(pwd) < 8:
        return error_response("Password must be at least 8 characters")
    import re
    if not re.search(r"[A-Z]", pwd):
        return error_response("Password must contain an uppercase letter")
    if not re.search(r"[a-z]", pwd):
        return error_response("Password must contain a lowercase letter")
    if not re.search(r"[0-9]", pwd):
        return error_response("Password must contain a number")
    if not re.search(r"[\W_]", pwd):
        return error_response("Password must contain a special character")

    # If reset code provided, verify it
    if body.reset_code:
        if not verify_reset_code(db, admin.id, body.reset_code):
            return error_response("Invalid or expired reset code")

    admin.password = hash_password(body.new_password)
    update_first_login(db, admin)
    db.commit()

    return success_response("Password updated successfully")


# ═══════════════════════════════════════════════════════════════
# VERIFY RESET CODE
# Replaces: verify_password_reset_code.php
# ═══════════════════════════════════════════════════════════════
class VerifyResetCodeRequest(BaseModel):
    admin_id: int
    code: str

@router.post("/verify-reset-code")
def verify_reset_code_endpoint(body: VerifyResetCodeRequest, db: Session = Depends(get_db)):
    if verify_reset_code(db, body.admin_id, body.code):
        return success_response("Reset code verified")
    return error_response("Invalid or expired reset code")


# ═══════════════════════════════════════════════════════════════
# REFRESH TOKEN
# ═══════════════════════════════════════════════════════════════
class RefreshTokenRequest(BaseModel):
    refresh_token: str

@router.post("/refresh")
def refresh_token(body: RefreshTokenRequest):
    payload = decode_token(body.refresh_token)
    if not payload or payload.get("type") != "refresh":
        return error_response("Invalid or expired refresh token")

    # Rebuild token data from payload
    new_data = {k: v for k, v in payload.items() if k not in ("exp", "type", "iat")}
    return success_response("Token refreshed", {
        "access_token": create_access_token(new_data),
        "refresh_token": create_refresh_token(new_data),
        "token_type": "Bearer",
    })


# ═══════════════════════════════════════════════════════════════
# GET CURRENT ADMIN
# ═══════════════════════════════════════════════════════════════
@router.get("/me")
def get_me(admin: Admin = Depends(get_current_admin)):
    return success_response(data={
        "admin_id": admin.id, "fullname": admin.fullname,
        "email": admin.email, "phone": admin.phone,
        "role": admin.role, "admin_level": admin.admin_level,
        "head_parish_id": admin.head_parish_id,
        "diocese_id": admin.diocese_id,
        "province_id": admin.province_id,
    })


# ═══════════════════════════════════════════════════════════════
# LOGOUT
# ═══════════════════════════════════════════════════════════════
@router.post("/logout")
def logout():
    return success_response("Logged out successfully")
