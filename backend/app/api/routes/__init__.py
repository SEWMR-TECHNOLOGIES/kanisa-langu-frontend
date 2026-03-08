# api/routes/__init__.py
"""Kanisa Langu API Routes — organized by admin level + shared services."""

# ── Role-scoped admin routes ──────────────────────────────────
from .system_admin import router as system_admin_router
from .diocese_admin import router as diocese_admin_router
from .province_admin import router as province_admin_router
from .head_parish_admin import router as head_parish_admin_router
from .sub_parish_admin import router as sub_parish_admin_router

# ── Auth ──────────────────────────────────────────────────────
from .auth import router as auth_router

# ── Shared services (used across levels) ──────────────────────
from .hierarchy import router as hierarchy_router
from .admins import router as admins_router
from .bible import router as bible_router
from .members import router as members_router
from .finance import router as finance_router
from .envelope import router as envelope_router
from .harambee import router as harambee_router
from .sunday_service import router as sunday_service_router
from .attendance import router as attendance_router
from .meetings import router as meetings_router
from .assets import router as assets_router
from .payments import router as payments_router
from .reports import router as reports_router
from .data import router as data_router
from .records import router as records_router
from .notifications import router as notifications_router

# ── Church member mobile app ─────────────────────────────────
from .church_member_api import router as church_member_router

all_routers = [
    # Auth (must be first)
    auth_router,                # /auth/...

    # Role-scoped admin routes
    system_admin_router,        # /system-admin/... (Kanisa Langu super admin)
    diocese_admin_router,        # /diocese/... (diocese-level admin)
    province_admin_router,       # /province/... (province-level admin)
    head_parish_admin_router,    # /head-parish/... (head parish admin — core ops)
    sub_parish_admin_router,     # /sub-level/... (sub-parish, community, group admins)

    # Shared CRUD & data services
    hierarchy_router,           # /dioceses, /provinces, /head-parishes, etc.
    admins_router,              # /admins/...
    members_router,             # /members/...
    finance_router,             # /finance/...
    envelope_router,            # /envelope/...
    harambee_router,            # /harambee/...
    sunday_service_router,      # /sunday-services/...
    attendance_router,          # /attendance/...
    meetings_router,            # /meetings/...
    assets_router,              # /assets/...
    payments_router,            # /payments/...
    reports_router,             # /reports/...
    data_router,                # /data/... (reference data & lookups)
    records_router,             # /records/... (legacy-compatible record operations)
    notifications_router,       # /notifications/...

    # Mobile app
    church_member_router,       # /church-member/...
]
