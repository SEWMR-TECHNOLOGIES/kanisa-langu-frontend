# main.py — Kanisa Langu FastAPI Application
from fastapi import FastAPI, HTTPException, Request
from fastapi.responses import JSONResponse
from fastapi.middleware.cors import CORSMiddleware

from api.routes import all_routers

app = FastAPI(title="Kanisa Langu API", version="1.0.0")

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

@app.get("/")
def root():
    return {"message": "Welcome to Kanisa Langu API"}

for router in all_routers:
    app.include_router(router, prefix=API_PREFIX)

@app.exception_handler(HTTPException)
async def http_exception_handler(request: Request, exc: HTTPException):
    return JSONResponse(
        status_code=exc.status_code,
        content={"success": False, "message": exc.detail, "data": None},
    )
