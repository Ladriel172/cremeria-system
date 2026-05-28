from sqlalchemy import Column, Integer, String, Float, DateTime, Enum as SQLEnum, Text, Boolean
from datetime import datetime
import enum
from database import Base


class UnidadEnum(str, enum.Enum):
    PIEZA = "pieza"
    GRAMOS = "gramos"
    LITROS = "litros"
    KG = "kg"
    ML = "ml"


class Product(Base):
    __tablename__ = "productos"

    id = Column(Integer, primary_key=True, index=True)
    codigo_barras = Column(String(50), unique=True, nullable=True, index=True)
    nombre = Column(String(150), nullable=False, index=True)
    descripcion = Column(Text, nullable=True)
    precio = Column(Float, default=0.0)
    costo = Column(Float, default=0.0)
    stock = Column(Float, default=0.0, index=True)
    stock_minimo = Column(Float, default=5.0)
    tipo_medida = Column(SQLEnum(UnidadEnum), default=UnidadEnum.PIEZA)
    categoria = Column(String(80), nullable=True, index=True)
    imagen = Column(String(255), nullable=True)
    activo = Column(Boolean, default=True, index=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)

    def __repr__(self):
        return f"<Product {self.nombre}>"
