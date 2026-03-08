# models/sunday_service.py
from sqlalchemy import Column, Integer, String, Date, Time, DateTime, ForeignKey
from sqlalchemy.sql import func
from sqlalchemy import Numeric
from core.base import Base


class SundayService(Base):
    __tablename__ = "sunday_services"
    id = Column(Integer, primary_key=True)
    head_parish_id = Column(Integer, ForeignKey("head_parishes.id", ondelete="CASCADE"), nullable=False)
    service_date = Column(Date, nullable=False)
    service_color_id = Column(Integer, ForeignKey("service_colors.id"))
    large_liturgy_page_number = Column(Integer)
    small_liturgy_page_number = Column(Integer)
    large_antiphony_page_number = Column(Integer)
    small_antiphony_page_number = Column(Integer)
    large_praise_page_number = Column(Integer)
    small_praise_page_number = Column(Integer)
    base_scripture_text = Column(String)
    created_at = Column(DateTime(timezone=True), server_default=func.now())
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now())


class HeadParishServiceTime(Base):
    __tablename__ = "head_parish_service_times"
    id = Column(Integer, primary_key=True)
    head_parish_id = Column(Integer, ForeignKey("head_parishes.id", ondelete="CASCADE"), nullable=False)
    service_number = Column(Integer, nullable=False)
    start_time = Column(Time, nullable=False)
    end_time = Column(Time)
    created_at = Column(DateTime(timezone=True), server_default=func.now())


class HeadParishServicesCount(Base):
    __tablename__ = "head_parish_services_count"
    id = Column(Integer, primary_key=True)
    head_parish_id = Column(Integer, ForeignKey("head_parishes.id", ondelete="CASCADE"), nullable=False, unique=True)
    services_count = Column(Integer, default=1, nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())


class SundayServiceScripture(Base):
    __tablename__ = "sunday_service_scriptures"
    id = Column(Integer, primary_key=True)
    service_id = Column(Integer, ForeignKey("sunday_services.id", ondelete="CASCADE"), nullable=False)
    service_number = Column(Integer, nullable=False)
    book = Column(String(100))
    chapter = Column(Integer)
    verse_from = Column(Integer)
    verse_to = Column(Integer)
    created_at = Column(DateTime(timezone=True), server_default=func.now())


class SundayServiceSong(Base):
    __tablename__ = "sunday_service_songs"
    id = Column(Integer, primary_key=True)
    service_id = Column(Integer, ForeignKey("sunday_services.id", ondelete="CASCADE"), nullable=False)
    service_number = Column(Integer, nullable=False)
    song_id = Column(Integer, ForeignKey("praise_songs.id"))
    song_title = Column(String(255))
    created_at = Column(DateTime(timezone=True), server_default=func.now())


class SundayServiceChoir(Base):
    __tablename__ = "sunday_service_choirs"
    id = Column(Integer, primary_key=True)
    service_id = Column(Integer, ForeignKey("sunday_services.id", ondelete="CASCADE"), nullable=False)
    service_number = Column(Integer, nullable=False)
    choir_id = Column(Integer, ForeignKey("church_choirs.id", ondelete="CASCADE"), nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())


class SundayServiceOffering(Base):
    __tablename__ = "sunday_service_offerings"
    id = Column(Integer, primary_key=True)
    service_id = Column(Integer, ForeignKey("sunday_services.id", ondelete="CASCADE"), nullable=False)
    service_number = Column(Integer, nullable=False)
    revenue_stream_id = Column(Integer, ForeignKey("revenue_streams.id"), nullable=False)
    amount = Column(Numeric(15, 2), nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())


class SundayServiceLeader(Base):
    __tablename__ = "sunday_service_leaders"
    id = Column(Integer, primary_key=True)
    service_id = Column(Integer, ForeignKey("sunday_services.id", ondelete="CASCADE"), nullable=False)
    service_number = Column(Integer, nullable=False)
    leader_name = Column(String(200))
    role = Column(String(50))
    created_at = Column(DateTime(timezone=True), server_default=func.now())


class SundayServiceElder(Base):
    __tablename__ = "sunday_service_elders"
    id = Column(Integer, primary_key=True)
    service_id = Column(Integer, ForeignKey("sunday_services.id", ondelete="CASCADE"), nullable=False)
    service_number = Column(Integer, nullable=False)
    elder_name = Column(String(200))
    created_at = Column(DateTime(timezone=True), server_default=func.now())


class SundayServicePreacher(Base):
    __tablename__ = "sunday_service_preachers"
    id = Column(Integer, primary_key=True)
    service_id = Column(Integer, ForeignKey("sunday_services.id", ondelete="CASCADE"), nullable=False)
    service_number = Column(Integer, nullable=False)
    preacher_name = Column(String(200))
    created_at = Column(DateTime(timezone=True), server_default=func.now())
