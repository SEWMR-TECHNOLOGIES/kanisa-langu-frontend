# models/bible.py
from sqlalchemy import Column, Integer, String, Text, CheckConstraint, UniqueConstraint, ForeignKey
from core.base import Base


class BibleBook(Base):
    __tablename__ = "bible_books"
    id = Column(Integer, primary_key=True)
    book_number = Column(Integer, nullable=False, unique=True)
    name_en = Column(String(100), nullable=False)
    name_sw = Column(String(100), nullable=False)
    testament = Column(String(10), nullable=False)
    chapter_count = Column(Integer, nullable=False, default=1)

    __table_args__ = (
        CheckConstraint("testament IN ('OT', 'NT')", name="ck_bible_testament"),
    )


class BibleChapter(Base):
    __tablename__ = "bible_chapters"
    id = Column(Integer, primary_key=True)
    book_id = Column(Integer, ForeignKey("bible_books.id", ondelete="CASCADE"), nullable=False)
    chapter_number = Column(Integer, nullable=False)
    verse_count = Column(Integer, nullable=False, default=1)

    __table_args__ = (
        UniqueConstraint("book_id", "chapter_number", name="uq_bible_chapter"),
    )


class BibleVerse(Base):
    __tablename__ = "bible_verses"
    id = Column(Integer, primary_key=True)
    book_id = Column(Integer, ForeignKey("bible_books.id", ondelete="CASCADE"), nullable=False)
    chapter_number = Column(Integer, nullable=False)
    verse_number = Column(Integer, nullable=False)
    text_en = Column(Text)
    text_sw = Column(Text)

    __table_args__ = (
        UniqueConstraint("book_id", "chapter_number", "verse_number", name="uq_bible_verse"),
    )
