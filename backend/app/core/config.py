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

# Auth
SECRET_KEY = os.getenv("SECRET_KEY", "change-me-in-production")
ALGORITHM = os.getenv("ALGORITHM", "HS256")
ACCESS_TOKEN_EXPIRE_MINUTES = int(os.getenv("ACCESS_TOKEN_EXPIRE_MINUTES", "1440"))
REFRESH_TOKEN_EXPIRE_DAYS = int(os.getenv("REFRESH_TOKEN_EXPIRE_DAYS", "30"))
RESET_CODE_EXPIRE_MINUTES = int(os.getenv("RESET_CODE_EXPIRE_MINUTES", "10"))

# SMS
SEWMR_SMS_BASE_URL = os.getenv("SEWMR_SMS_BASE_URL", "https://api.sewmrsms.co.tz/api/v1/")
SEWMR_SMS_ACCESS_TOKEN = os.getenv("SEWMR_SMS_ACCESS_TOKEN", "")
SEWMR_SMS_DEFAULT_SENDER_ID = os.getenv("SEWMR_SMS_DEFAULT_SENDER_ID", "")

# Uploads
UPLOAD_SERVICE_URL = os.getenv("UPLOAD_SERVICE_URL", "https://data.sewmrtechnologies.com/handle-file-uploads")
DELETE_SERVICE_URL = os.getenv("DELETE_SERVICE_URL", "https://data.sewmrtechnologies.com/delete-file.php")
MAX_IMAGE_SIZE = 500 * 1024  # 0.5MB
ALLOWED_IMAGE_EXTENSIONS = {"jpg", "jpeg", "png", "webp"}

# Encryption (for SMS API credentials)
ENCRYPTION_KEY = os.getenv("ENCRYPTION_KEY", "05fa9146a77a2817fd7448498a5eaac9")
ENCRYPTION_IV = os.getenv("ENCRYPTION_IV", "58f310cb97bb3ce6")

# Payment Gateway
PAYMENT_GATEWAY_URL = os.getenv("PAYMENT_GATEWAY_URL", "https://payment-gateway.sewmrtechnologies.com/selcom")
