# models/hierarchy.py
from sqlalchemy import Column, Integer, String, Boolean, DateTime, ForeignKey
from sqlalchemy.orm import relationship
from sqlalchemy.sql import func
from core.base import Base


class Diocese(Base):
    __tablename__ = "dioceses"
    id = Column(Integer, primary_key=True)
    name = Column(String(150), unique=True, nullable=False)
    address = Column(String(255))
    email = Column(String(150))
    phone = Column(String(50))
    region_id = Column(Integer, ForeignKey("regions.id", ondelete="SET NULL"))
    district_id = Column(Integer, ForeignKey("districts.id", ondelete="SET NULL"))
    website = Column(String(255))
    is_active = Column(Boolean, default=True, nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now())

    provinces = relationship("Province", back_populates="diocese", cascade="all, delete-orphan")


class Province(Base):
    __tablename__ = "provinces"
    id = Column(Integer, primary_key=True)
    name = Column(String(150), nullable=False)
    diocese_id = Column(Integer, ForeignKey("dioceses.id", ondelete="CASCADE"), nullable=False)
    region_id = Column(Integer, ForeignKey("regions.id", ondelete="SET NULL"))
    district_id = Column(Integer, ForeignKey("districts.id", ondelete="SET NULL"))
    address = Column(String(255))
    email = Column(String(150))
    phone = Column(String(50))
    is_active = Column(Boolean, default=True, nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now())

    diocese = relationship("Diocese", back_populates="provinces")
    head_parishes = relationship("HeadParish", back_populates="province", cascade="all, delete-orphan")


class HeadParish(Base):
    __tablename__ = "head_parishes"
    id = Column(Integer, primary_key=True)
    name = Column(String(150), nullable=False)
    diocese_id = Column(Integer, ForeignKey("dioceses.id", ondelete="CASCADE"), nullable=False)
    province_id = Column(Integer, ForeignKey("provinces.id", ondelete="CASCADE"), nullable=False)
    region_id = Column(Integer, ForeignKey("regions.id", ondelete="SET NULL"))
    district_id = Column(Integer, ForeignKey("districts.id", ondelete="SET NULL"))
    address = Column(String(255), nullable=False)
    email = Column(String(150), nullable=False)
    phone = Column(String(50))
    website = Column(String(255))
    is_active = Column(Boolean, default=True, nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now())

    province = relationship("Province", back_populates="head_parishes")
    sub_parishes = relationship("SubParish", back_populates="head_parish", cascade="all, delete-orphan")
    groups = relationship("Group", back_populates="head_parish", cascade="all, delete-orphan")


class SubParish(Base):
    __tablename__ = "sub_parishes"
    id = Column(Integer, primary_key=True)
    name = Column(String(150), nullable=False)
    head_parish_id = Column(Integer, ForeignKey("head_parishes.id", ondelete="CASCADE"), nullable=False)
    description = Column(String)
    is_active = Column(Boolean, default=True, nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now())

    head_parish = relationship("HeadParish", back_populates="sub_parishes")
    communities = relationship("Community", back_populates="sub_parish", cascade="all, delete-orphan")


class Community(Base):
    __tablename__ = "communities"
    id = Column(Integer, primary_key=True)
    name = Column(String(150), nullable=False)
    head_parish_id = Column(Integer, ForeignKey("head_parishes.id", ondelete="CASCADE"), nullable=False)
    sub_parish_id = Column(Integer, ForeignKey("sub_parishes.id", ondelete="CASCADE"), nullable=False)
    description = Column(String)
    is_active = Column(Boolean, default=True, nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now())

    sub_parish = relationship("SubParish", back_populates="communities")


class Group(Base):
    __tablename__ = "groups"
    id = Column(Integer, primary_key=True)
    name = Column(String(150), nullable=False)
    head_parish_id = Column(Integer, ForeignKey("head_parishes.id", ondelete="CASCADE"), nullable=False)
    description = Column(String)
    is_active = Column(Boolean, default=True, nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now())

    head_parish = relationship("HeadParish", back_populates="groups")
