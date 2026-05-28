from pydantic import BaseModel
from typing import Optional, List
from datetime import datetime


class SaleDetailIn(BaseModel):
    producto_id: int
    cantidad: float
    precio_unitario: float
    descuento: float = 0.0


class SaleCreate(BaseModel):
    folio: str
    usuario_id: int
    subtotal: float
    descuento: float = 0.0
    iva: float = 0.0
    total: float
    metodo_pago: str = "efectivo"
    monto_pagado: float
    cambio: float = 0.0
    estado: str = "completada"
    notas: Optional[str] = None
    detalles: List[SaleDetailIn]


class SaleResponse(BaseModel):
    id: int
    folio: str
    usuario_id: int
    subtotal: float
    descuento: float
    iva: float
    total: float
    metodo_pago: str
    monto_pagado: float
    cambio: float
    estado: str
    notas: Optional[str]
    created_at: datetime

    class Config:
        from_attributes = True
