from pydantic import BaseModel
from typing import Optional
from datetime import datetime


class ProductCreate(BaseModel):
    codigo_barras: Optional[str] = None
    nombre: str
    descripcion: Optional[str] = None
    precio: float
    costo: float
    stock: float
    stock_minimo: float = 5.0
    tipo_medida: str = "pieza"
    categoria: Optional[str] = None
    imagen: Optional[str] = None


class ProductUpdate(BaseModel):
    nombre: Optional[str] = None
    descripcion: Optional[str] = None
    precio: Optional[float] = None
    costo: Optional[float] = None
    stock: Optional[float] = None
    stock_minimo: Optional[float] = None
    tipo_medida: Optional[str] = None
    categoria: Optional[str] = None
    imagen: Optional[str] = None
    activo: Optional[bool] = None


class ProductResponse(BaseModel):
    id: int
    codigo_barras: Optional[str]
    nombre: str
    descripcion: Optional[str]
    precio: float
    costo: float
    stock: float
    stock_minimo: float
    tipo_medida: str
    categoria: Optional[str]
    imagen: Optional[str]
    activo: bool
    created_at: datetime
    updated_at: datetime

    class Config:
        from_attributes = True
