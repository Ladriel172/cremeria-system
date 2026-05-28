from fastapi import APIRouter, Depends, HTTPException, status, Query
from sqlalchemy.orm import Session
from typing import List, Optional
from datetime import datetime
from database import get_db
from app.models import Sale, SaleDetail, Product
from app.schemas import SaleCreate, SaleResponse

router = APIRouter(prefix="/api/sales", tags=["sales"])


@router.post("/", response_model=SaleResponse)
def create_sale(sale: SaleCreate, db: Session = Depends(get_db)):
    """Crear nueva venta"""
    # Verificar que el folio sea único
    existing = db.query(Sale).filter(Sale.folio == sale.folio).first()
    if existing:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="El folio ya existe"
        )

    # Crear venta
    db_sale = Sale(
        folio=sale.folio,
        usuario_id=sale.usuario_id,
        subtotal=sale.subtotal,
        descuento=sale.descuento,
        iva=sale.iva,
        total=sale.total,
        metodo_pago=sale.metodo_pago,
        monto_pagado=sale.monto_pagado,
        cambio=sale.cambio,
        estado=sale.estado,
        notas=sale.notas
    )

    # Agregar detalles y actualizar stock
    for detalle in sale.detalles:
        # Verificar producto existe
        product = db.query(Product).filter(
            Product.id == detalle.producto_id
        ).first()
        if not product:
            raise HTTPException(
                status_code=status.HTTP_404_NOT_FOUND,
                detail=f"Producto {detalle.producto_id} no encontrado"
            )

        # Actualizar stock
        product.stock -= detalle.cantidad

        # Crear detalle
        sale_detail = SaleDetail(
            venta_id=None,  # Se asignará después
            producto_id=detalle.producto_id,
            cantidad=detalle.cantidad,
            precio_unitario=detalle.precio_unitario,
            subtotal=detalle.cantidad * detalle.precio_unitario,
            descuento=detalle.descuento
        )
        db_sale.detalles.append(sale_detail)

    db.add(db_sale)
    db.commit()
    db.refresh(db_sale)
    return db_sale


@router.get("/", response_model=List[SaleResponse])
def list_sales(
    db: Session = Depends(get_db),
    skip: int = Query(0, ge=0),
    limit: int = Query(10, ge=1, le=100),
    usuario_id: Optional[int] = None,
    estado: Optional[str] = None
):
    """Listar ventas"""
    query = db.query(Sale)

    if usuario_id:
        query = query.filter(Sale.usuario_id == usuario_id)

    if estado:
        query = query.filter(Sale.estado == estado)

    return query.order_by(Sale.created_at.desc()).offset(skip).limit(limit).all()


@router.get("/{sale_id}", response_model=SaleResponse)
def get_sale(sale_id: int, db: Session = Depends(get_db)):
    """Obtener venta por ID"""
    sale = db.query(Sale).filter(Sale.id == sale_id).first()
    if not sale:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Venta no encontrada"
        )
    return sale


@router.put("/{sale_id}/cancel")
def cancel_sale(sale_id: int, db: Session = Depends(get_db)):
    """Anular venta"""
    sale = db.query(Sale).filter(Sale.id == sale_id).first()
    if not sale:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Venta no encontrada"
        )

    # Revertir stock
    for detalle in sale.detalles:
        product = db.query(Product).filter(
            Product.id == detalle.producto_id
        ).first()
        if product:
            product.stock += detalle.cantidad

    sale.estado = "anulada"
    db.commit()
    return {"message": "Venta anulada"}
