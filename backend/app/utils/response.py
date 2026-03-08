# utils/response.py
"""Standardized API response helpers."""
from typing import Any


def success_response(message: str = "Success", data: Any = None) -> dict:
    return {"success": True, "message": message, "data": data}


def error_response(message: str = "An error occurred", data: Any = None) -> dict:
    return {"success": False, "message": message, "data": data}
