# api/routes/bible.py
"""Bible reference data routes — books, chapters, verses for scripture selection in Sunday services."""
from fastapi import APIRouter, Depends, Query
from sqlalchemy.orm import Session
from typing import Optional, List

from core.database import get_db
from models.bible import BibleBook, BibleChapter, BibleVerse
from utils.response import success_response

from schemas.base import ApiResponse
from pydantic import BaseModel


class BibleBookOut(BaseModel):
    id: int
    book_number: int
    name_en: str
    name_sw: str
    testament: str
    chapter_count: int


class BibleChapterOut(BaseModel):
    id: int
    chapter_number: int
    verse_count: int


class BibleVerseOut(BaseModel):
    verse_number: int
    text_en: Optional[str] = None
    text_sw: Optional[str] = None


class BibleSearchResult(BaseModel):
    book_id: int
    chapter: int
    verse: int
    text_en: Optional[str] = None
    text_sw: Optional[str] = None


class BibleSearchData(BaseModel):
    verses: List[BibleSearchResult]
    total: int
    page: int


router = APIRouter(prefix="/bible", tags=["Bible"])


@router.get("/books", response_model=ApiResponse[List[BibleBookOut]], summary="List Bible books")
def list_books(testament: Optional[str] = None, db: Session = Depends(get_db)):
    q = db.query(BibleBook)
    if testament:
        q = q.filter(BibleBook.testament == testament)
    rows = q.order_by(BibleBook.book_number).all()
    return success_response(data=[{
        "id": b.id, "book_number": b.book_number,
        "name_en": b.name_en, "name_sw": b.name_sw,
        "testament": b.testament, "chapter_count": b.chapter_count,
    } for b in rows])


@router.get("/chapters", response_model=ApiResponse[List[BibleChapterOut]], summary="List chapters for a book")
def list_chapters(book_id: int, db: Session = Depends(get_db)):
    rows = db.query(BibleChapter).filter(
        BibleChapter.book_id == book_id
    ).order_by(BibleChapter.chapter_number).all()
    return success_response(data=[{
        "id": c.id, "chapter_number": c.chapter_number, "verse_count": c.verse_count,
    } for c in rows])


@router.get("/verses", response_model=ApiResponse[List[BibleVerseOut]], summary="List verses for a chapter")
def list_verses(
    book_id: int, chapter: int,
    verse_from: Optional[int] = None, verse_to: Optional[int] = None,
    db: Session = Depends(get_db),
):
    q = db.query(BibleVerse).filter(BibleVerse.book_id == book_id, BibleVerse.chapter_number == chapter)
    if verse_from:
        q = q.filter(BibleVerse.verse_number >= verse_from)
    if verse_to:
        q = q.filter(BibleVerse.verse_number <= verse_to)
    rows = q.order_by(BibleVerse.verse_number).all()
    return success_response(data=[{
        "verse_number": v.verse_number, "text_en": v.text_en, "text_sw": v.text_sw,
    } for v in rows])


@router.get("/search", response_model=ApiResponse[BibleSearchData], summary="Search Bible verses by text")
def search_verses(
    query: str, lang: str = "sw",
    page: int = Query(1, ge=1), limit: int = Query(20, ge=1, le=100),
    db: Session = Depends(get_db),
):
    search = f"%{query}%"
    col = BibleVerse.text_sw if lang == "sw" else BibleVerse.text_en
    q = db.query(BibleVerse).filter(col.ilike(search))
    total = q.count()
    rows = q.order_by(BibleVerse.book_id, BibleVerse.chapter_number, BibleVerse.verse_number).offset((page - 1) * limit).limit(limit).all()
    return success_response(data={
        "verses": [{
            "book_id": v.book_id, "chapter": v.chapter_number,
            "verse": v.verse_number, "text_en": v.text_en, "text_sw": v.text_sw,
        } for v in rows],
        "total": total, "page": page,
    })
