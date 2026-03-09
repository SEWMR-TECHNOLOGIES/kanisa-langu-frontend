# main.py — Kanisa Langu FastAPI Application
from fastapi import FastAPI, HTTPException, Request
from fastapi.responses import JSONResponse
from fastapi.middleware.cors import CORSMiddleware

from api.routes import all_routers

TAGS_METADATA = [
    {"name": "Authentication", "description": "Admin & member sign-in, JWT tokens, password management."},
    {"name": "System Admin", "description": "Kanisa Langu super-admin — global dioceses, provinces, reference data, app versions."},
    {"name": "Diocese Admin", "description": "Diocese-scoped management — provinces, head parishes, financial overview."},
    {"name": "Province Admin", "description": "Province-scoped management — head parishes, admins, members overview."},
    {"name": "Head Parish Admin", "description": "Core operational level — members, finance, harambee, envelope, attendance, meetings, assets, sunday services."},
    {"name": "Sub Parish / Community / Group Admin", "description": "Lower-level scoped operations — members, revenue, expenses, meetings, harambee within scope."},
    {"name": "Church Hierarchy", "description": "CRUD for dioceses, provinces, head parishes, sub parishes, communities, groups."},
    {"name": "Admins", "description": "Admin user CRUD — create, list, deactivate across all levels."},
    {"name": "Church Members", "description": "Member registration, update, search, exclusion, leaders, choirs."},
    {"name": "Finance", "description": "Bank accounts, revenue streams, revenues, expenses, budgets, targets."},
    {"name": "Envelope", "description": "Envelope contribution targets and member contributions."},
    {"name": "Harambee", "description": "Fundraising campaigns — contributions, groups, targets, classes."},
    {"name": "Sunday Services", "description": "Sunday service records, scripture, songs, offerings, service times."},
    {"name": "Attendance", "description": "Event attendance recording and benchmarks."},
    {"name": "Meetings", "description": "Meeting management — agendas, minutes, notes."},
    {"name": "Assets", "description": "Church asset management — revenues, expenses, status tracking."},
    {"name": "Payments", "description": "Payment gateway integration (Selcom via Sewmr proxy)."},
    {"name": "Reports", "description": "Financial summaries, harambee reports, attendance stats, member analytics."},
    {"name": "Data & Reference", "description": "Lookup data — titles, occupations, regions, districts, banks, colors, praise songs."},
    {"name": "Records", "description": "Bulk/operational record creation — registration, transactions, distributions."},
    {"name": "Notifications", "description": "Push notifications, SMS, delayed harambee notifications."},
    {"name": "Bible", "description": "Scripture reference data — books, chapters, verses, search."},
    {"name": "Church Member App", "description": "Mobile app API — sign-in, OTP, avatar, harambee, envelope, sunday services."},
]

app = FastAPI(
    title="Kanisa Langu API",
    version="1.0.0",
    description=(
        "**Kanisa Langu** — Church Management System API.\n\n"
        "A multi-level hierarchical system supporting diocese → province → head parish → "
        "sub parish → community → group administration.\n\n"
        "### Key Features\n"
        "- 🔐 **JWT Authentication** with role-based access control\n"
        "- ⛪ **Church Hierarchy** CRUD at every administrative level\n"
        "- 👥 **Member Management** — registration, envelopes, exclusions\n"
        "- 💰 **Financial Operations** — revenues, expenses, bank accounts, budgets\n"
        "- 🤝 **Harambee (Fundraising)** — campaigns, groups, targets, contributions\n"
        "- 📊 **Reporting** — financial summaries, attendance, harambee analytics\n"
        "- 📱 **Mobile App API** — member sign-in, OTP, envelope/harambee data\n"
        "- 💳 **Payment Gateway** — Selcom integration for mobile payments\n"
    ),
    openapi_tags=TAGS_METADATA,
    contact={"name": "Sewmr Technologies", "url": "https://sewmrtechnologies.com", "email": "info@sewmrtechnologies.com"},
    license_info={"name": "Proprietary"},
    docs_url="/docs",
    redoc_url="/redoc",
)

API_PREFIX = "/api/v1"

app.add_middleware(
    CORSMiddleware,
    allow_origins=[
        "http://localhost:8080",
        "http://localhost:5173",
        "http://127.0.0.1:8080",
        "https://kanisalangu.lovable.app",
        "https://kanisalangu.sewmrtechnologies.com",
    ],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

@app.get("/", tags=["Health"], summary="Health check", response_description="API status")
def root():
    return {"message": "Welcome to Kanisa Langu API", "version": "1.0.0", "docs": "/docs"}

for router in all_routers:
    app.include_router(router, prefix=API_PREFIX)

@app.exception_handler(HTTPException)
async def http_exception_handler(request: Request, exc: HTTPException):
    return JSONResponse(
        status_code=exc.status_code,
        content={"success": False, "message": exc.detail, "data": None},
    )
