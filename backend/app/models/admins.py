# models/admins.py
from sqlalchemy import Column, Integer, String, Boolean, DateTime, ForeignKey, CheckConstraint
from sqlalchemy.sql import func
from core.base import Base


class SystemAdmin(Base):
    __tablename__ = "system_admins"
    id = Column(Integer, primary_key=True)
    username = Column(String(100), unique=True, nullable=False)
    password = Column(String(255), nullable=False)
    role = Column(String(20), nullable=False)
    is_active = Column(Boolean, default=True, nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())

    __table_args__ = (
        CheckConstraint("role IN ('super_admin', 'admin')", name="ck_system_admin_role"),
    )


class Admin(Base):
    __tablename__ = "admins"
    id = Column(Integer, primary_key=True)
    fullname = Column(String(150), nullable=False)
    email = Column(String(255))
    phone = Column(String(50), nullable=False)
    role = Column(String(30), nullable=False)
    password = Column(String(255), nullable=False)
    signature_path = Column(String(255))
    admin_level = Column(String(20), nullable=False)
    diocese_id = Column(Integer, ForeignKey("dioceses.id", ondelete="CASCADE"))
    province_id = Column(Integer, ForeignKey("provinces.id", ondelete="CASCADE"))
    head_parish_id = Column(Integer, ForeignKey("head_parishes.id", ondelete="CASCADE"))
    sub_parish_id = Column(Integer, ForeignKey("sub_parishes.id", ondelete="CASCADE"))
    community_id = Column(Integer, ForeignKey("communities.id", ondelete="CASCADE"))
    group_id = Column(Integer, ForeignKey("groups.id", ondelete="CASCADE"))
    is_active = Column(Boolean, default=True, nullable=False)
    first_login = Column(Boolean, default=True, nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now())

    __table_args__ = (
        CheckConstraint(
            "admin_level IN ('diocese','province','head_parish','sub_parish','community','group')",
            name="ck_admin_level",
        ),
        CheckConstraint(
            "role IN ('admin','bishop','secretary','chairperson','accountant','clerk','pastor','evangelist','elder')",
            name="ck_admin_role",
        ),
    )


class AdminLogin(Base):
    __tablename__ = "admin_logins"
    id = Column(Integer, primary_key=True)
    admin_id = Column(Integer, ForeignKey("admins.id", ondelete="CASCADE"))
    system_admin_id = Column(Integer, ForeignKey("system_admins.id", ondelete="CASCADE"))
    login_time = Column(DateTime(timezone=True), server_default=func.now())
    ip_address = Column(String(45))
    user_agent = Column(String(500))


class PasswordResetCode(Base):
    __tablename__ = "password_reset_codes"
    id = Column(Integer, primary_key=True)
    admin_id = Column(Integer, ForeignKey("admins.id", ondelete="CASCADE"))
    system_admin_id = Column(Integer, ForeignKey("system_admins.id", ondelete="CASCADE"))
    reset_code = Column(String(255), nullable=False)
    expires_at = Column(DateTime(timezone=True), nullable=False)
    used = Column(Boolean, default=False, nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())
