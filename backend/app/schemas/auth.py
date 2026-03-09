# schemas/auth.py
"""Authentication request & response schemas."""
from pydantic import BaseModel, Field
from typing import Optional


# ── Requests ──────────────────────────────────────────────────

class AdminSigninRequest(BaseModel):
    """Unified admin sign-in for all hierarchy levels."""
    credential: str = Field(..., description="Email or phone number", examples=["admin@kanisa.com", "+255712345678"])
    password: str = Field(..., min_length=1, description="Account password")
    admin_level: Optional[str] = Field(None, description="Filter by level: diocese, province, head_parish, sub_parish, community, group")


class SystemAdminSigninRequest(BaseModel):
    """Kanisa Langu system super-admin sign-in."""
    username: str = Field(..., min_length=1, description="System admin username")
    password: str = Field(..., min_length=1, description="System admin password")


class AdminPasswordUpdate(BaseModel):
    """Update password (first login or manual change)."""
    new_password: str = Field(..., min_length=8, description="New password (min 8 chars, must include upper, lower, digit, special)")
    confirm_password: str = Field(..., description="Must match new_password")
    reset_code: Optional[str] = Field(None, description="Required if resetting via code")


class VerifyResetCodeRequest(BaseModel):
    admin_id: int = Field(..., description="ID of the admin whose code to verify")
    code: str = Field(..., description="6-digit reset code")


class RefreshTokenRequest(BaseModel):
    refresh_token: str = Field(..., description="Refresh JWT token")


# ── Responses ─────────────────────────────────────────────────

class AdminProfile(BaseModel):
    """Admin profile returned on login and /me."""
    admin_id: int
    fullname: str
    email: Optional[str] = None
    phone: Optional[str] = None
    role: str
    admin_level: str
    first_time_login: Optional[bool] = None
    head_parish_id: Optional[int] = None
    diocese_id: Optional[int] = None
    province_id: Optional[int] = None
    sub_parish_id: Optional[int] = None
    community_id: Optional[int] = None
    group_id: Optional[int] = None
    reset_code: Optional[str] = Field(None, description="Only present on first-time login")


class TokenPair(BaseModel):
    """JWT access + refresh tokens."""
    access_token: str
    refresh_token: str
    token_type: str = "Bearer"


class AdminSigninResponse(BaseModel):
    """Full sign-in response payload."""
    admin: AdminProfile
    access_token: str
    refresh_token: str
    token_type: str = "Bearer"


class SystemAdminProfile(BaseModel):
    id: int
    username: str
    role: str


class SystemAdminSigninResponse(BaseModel):
    admin: SystemAdminProfile
    access_token: str
    refresh_token: str
    token_type: str = "Bearer"


class CurrentAdminResponse(BaseModel):
    admin_id: int
    fullname: str
    email: Optional[str] = None
    phone: Optional[str] = None
    role: str
    admin_level: str
    head_parish_id: Optional[int] = None
    diocese_id: Optional[int] = None
    province_id: Optional[int] = None
