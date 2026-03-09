# api/routes/auth.py
"""Authentication routes for Kanisa Langu admin signin (all levels) + member signin."""

from datetime import datetime, timedelta
from fastapi import APIRouter, Depends, Request, Response, HTTPException
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

from schemas.base import ApiResponse
from schemas.auth import (
    AdminSigninRequest, SystemAdminSigninRequest, AdminPasswordUpdate,
    VerifyResetCodeRequest, RefreshTokenRequest,
    AdminSigninResponse, SystemAdminSigninResponse, CurrentAdminResponse, TokenPair,
)

router = APIRouter(prefix="/auth", tags=["Authentication"])


# ═══════════════════════════════════════════════════════════════
# ADMIN SIGNIN (unified for all levels)
# ═══════════════════════════════════════════════════════════════

@router.post(
    "/admin/signin",
    response_model=ApiResponse[AdminSigninResponse],
    summary="Admin sign-in",
    description="Authenticate any admin (diocese, province, head parish, sub parish, community, group) with email/phone + password.",
)
def admin_signin(body: AdminSigninRequest, request: Request, db: Session = Depends(get_db)):
    if not body.credential.strip():
        return error_response("Email or phone is required")
    if not body.password:
        return error_response("Password is required")

    credential = body.credential.strip()

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

    is_first_login = check_first_login(db, admin)

    token_data = {
        "admin_id": admin.id,
        "admin_level": admin.admin_level,
        "role": admin.role,
    }
    if admin.head_parish_id:
        token_data["head_parish_id"] = admin.head_parish_id
    if admin.diocese_id:
        token_data["diocese_id"] = admin.diocese_id
    if admin.province_id:
        token_data["province_id"] = admin.province_id

    access_token = create_access_token(token_data)
    refresh_token = create_refresh_token(token_data)

    record_admin_login(
        db, admin_id=admin.id,
        ip_address=request.client.host if request.client else None,
        user_agent=request.headers.get("user-agent"),
    )

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
# ═══════════════════════════════════════════════════════════════

@router.post(
    "/system-admin/signin",
    response_model=ApiResponse[SystemAdminSigninResponse],
    summary="System admin sign-in",
    description="Authenticate the Kanisa Langu super-admin.",
)
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
# ═══════════════════════════════════════════════════════════════

@router.post(
    "/admin/update-password",
    response_model=ApiResponse[None],
    summary="Update admin password",
    description="Update password after first login or manual reset. Requires strong password.",
)
def update_admin_password(body: AdminPasswordUpdate, admin: Admin = Depends(get_current_admin), db: Session = Depends(get_db)):
    if body.new_password != body.confirm_password:
        return error_response("Passwords do not match")

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

    if body.reset_code:
        if not verify_reset_code(db, admin.id, body.reset_code):
            return error_response("Invalid or expired reset code")

    admin.password = hash_password(body.new_password)
    update_first_login(db, admin)
    db.commit()

    return success_response("Password updated successfully")


# ═══════════════════════════════════════════════════════════════
# VERIFY RESET CODE
# ═══════════════════════════════════════════════════════════════

@router.post(
    "/verify-reset-code",
    response_model=ApiResponse[None],
    summary="Verify password reset code",
)
def verify_reset_code_endpoint(body: VerifyResetCodeRequest, db: Session = Depends(get_db)):
    if verify_reset_code(db, body.admin_id, body.code):
        return success_response("Reset code verified")
    return error_response("Invalid or expired reset code")


# ═══════════════════════════════════════════════════════════════
# REFRESH TOKEN
# ═══════════════════════════════════════════════════════════════

@router.post(
    "/refresh",
    response_model=ApiResponse[TokenPair],
    summary="Refresh JWT tokens",
    description="Exchange a valid refresh token for a new access + refresh token pair.",
)
def refresh_token(body: RefreshTokenRequest):
    payload = decode_token(body.refresh_token)
    if not payload or payload.get("type") != "refresh":
        return error_response("Invalid or expired refresh token")

    new_data = {k: v for k, v in payload.items() if k not in ("exp", "type", "iat")}
    return success_response("Token refreshed", {
        "access_token": create_access_token(new_data),
        "refresh_token": create_refresh_token(new_data),
        "token_type": "Bearer",
    })


# ═══════════════════════════════════════════════════════════════
# GET CURRENT ADMIN
# ═══════════════════════════════════════════════════════════════

@router.get(
    "/me",
    response_model=ApiResponse[CurrentAdminResponse],
    summary="Get current admin profile",
    description="Returns the profile of the currently authenticated admin.",
)
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

@router.post(
    "/logout",
    response_model=ApiResponse[None],
    summary="Logout",
    description="Invalidate the current session (client-side token discard).",
)
def logout():
    return success_response("Logged out successfully")
