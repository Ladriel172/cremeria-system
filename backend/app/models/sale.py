from sqlalchemy import Column, Integer, String, Float, DateTime, Enum as SQLEnum, Text, ForeignKey
from sqlalchemy.orm import relationship
from datetime import datetime
import enum
from database import Base


class MetodoPagoEnum(str, enum.Enum):
    EFECTIVO = "efectivo"
    TARJETA = "tarjeta"
    TRANSFERENCIA = "transferencia"
    OTRO = "otro"


class EstadoVentaEnum(str, enum.Enum):
    COMPLETADA = "completada"
    ANULADA = "anulada"
    PENDIENTE = "pendiente"


class Sale(Base):
    __tablename__ = "ventas"

    id = Column(Integer, primary_key=True, index=True)
    folio = Column(String(30), unique=True, nullable=False, index=True)
    usuario_id = Column(Integer, ForeignKey("usuarios.id"), nullable=False, index=True)
    subtotal = Column(Float, default=0.0)
    descuento = Column(Float, default=0.0)
    iva = Column(Float, default=0.0)
    total = Column(Float, default=0.0)
    metodo_pago = Column(SQLEnum(MetodoPagoEnum), default=MetodoPagoEnum.EFECTIVO)
    monto_pagado = Column(Float, default=0.0)
    cambio = Column(Float, default=0.0)
    estado = Column(SQLEnum(EstadoVentaEnum), default=EstadoVentaEnum.COMPLETADA, index=True)
    notas = Column(Text, nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow, index=True)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)

    detalles = relationship("SaleDetail", back_populates="sale", cascade="all, delete-orphan")

    def __repr__(self):
        return f"<Sale {self.folio}>"


class SaleDetail(Base):
    __tablename__ = "detalles_venta"

    id = Column(Integer, primary_key=True, index=True)
    venta_id = Column(Integer, ForeignKey("ventas.id"), nullable=False)
    producto_id = Column(Integer, ForeignKey("productos.id"), nullable=False)
    cantidad = Column(Float, nullable=False)
    precio_unitario = Column(Float, nullable=False)
    subtotal = Column(Float, nullable=False)
    descuento = Column(Float, default=0.0)
    created_at = Column(DateTime, default=datetime.utcnow)

    sale = relationship("Sale", back_populates="detalles")

    def __repr__(self):
        return f"<SaleDetail venta_id={self.venta_id}>"
