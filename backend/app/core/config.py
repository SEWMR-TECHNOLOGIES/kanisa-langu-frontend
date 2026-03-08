# core/config.py
import os
from dotenv import load_dotenv

load_dotenv()

DEBUG = os.getenv("DEBUG", "False") == "True"

# Database
DB_USER = os.getenv("DB_USER", "postgres")
DB_PASSWORD = os.getenv("DB_PASSWORD", "")
DB_HOST = os.getenv("DB_HOST", "localhost")
DB_PORT = os.getenv("DB_PORT", "5432")
DB_NAME = os.getenv("DB_NAME", "kanisalangu")
DATABASE_URL = f"postgresql+psycopg2://{DB_USER}:{DB_PASSWORD}@{DB_HOST}:{DB_PORT}/{DB_NAME}?sslmode=require"

# Auth (JWT)
SECRET_KEY = os.getenv("SECRET_KEY", "change-me-in-production")
ALGORITHM = os.getenv("ALGORITHM", "HS256")
ACCESS_TOKEN_EXPIRE_MINUTES = int(os.getenv("ACCESS_TOKEN_EXPIRE_MINUTES", "1440"))  # 24 hours
REFRESH_TOKEN_EXPIRE_DAYS = int(os.getenv("REFRESH_TOKEN_EXPIRE_DAYS", "30"))
RESET_CODE_EXPIRE_MINUTES = int(os.getenv("RESET_CODE_EXPIRE_MINUTES", "60"))

# Encryption (AES-256-CBC for SMS API credentials — matches legacy PHP)
ENCRYPTION_KEY = os.getenv("ENCRYPTION_KEY", "05fa9146a77a2817fd7448498a5eaac9")
ENCRYPTION_IV = os.getenv("ENCRYPTION_IV", "58f310cb97bb3ce6")

# Payment Gateway (Selcom via Sewmr proxy)
PAYMENT_GATEWAY_URL = os.getenv("PAYMENT_GATEWAY_URL", "https://payment-gateway.sewmrtechnologies.com/selcom")

# CORS
ALLOWED_ORIGINS = os.getenv("ALLOWED_ORIGINS", "https://kanisalangu.lovable.app,http://localhost:5173").split(",")
