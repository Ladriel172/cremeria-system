"""
Script para inicializar la base de datos con datos de ejemplo
Ejecutar: python seed_db.py
"""

from backend.database import SessionLocal, init_db
from backend.app.models import User, Product, Sale, SaleDetail
from backend.app.routes.auth import hash_password
from datetime import datetime, timedelta
import random

def seed_database():
    """Cargar datos de ejemplo a la BD"""
    # Inicializar tablas
    init_db()
    
    db = SessionLocal()
    
    try:
        # Verificar si ya hay datos
        if db.query(User).count() > 0:
            print("⚠️  La base de datos ya tiene datos. Abortando...")
            return
        
        # Crear usuarios
        print("📝 Creando usuarios...")
        users = [
            User(
                nombre="Administrador",
                correo="admin@cremeria.com",
                password=hash_password("admin123"),
                rol="admin",
                estado="activo"
            ),
            User(
                nombre="Vendedor Demo",
                correo="vendedor@cremeria.com",
                password=hash_password("vendedor123"),
                rol="vendedor",
                estado="activo"
            )
        ]
        db.add_all(users)
        db.commit()
        
        # Crear productos
        print("📦 Creando productos...")
        productos = [
            Product(
                codigo_barras="001",
                nombre="Helado Vainilla",
                descripcion="Helado de vainilla cremoso",
                precio=5.99,
                costo=2.50,
                stock=50,
                stock_minimo=10,
                tipo_medida="pieza",
                categoria="Helados",
                activo=True
            ),
            Product(
                codigo_barras="002",
                nombre="Helado Chocolate",
                descripcion="Helado de chocolate amargo",
                precio=5.99,
                costo=2.50,
                stock=45,
                stock_minimo=10,
                tipo_medida="pieza",
                categoria="Helados",
                activo=True
            ),
            Product(
                codigo_barras="003",
                nombre="Helado Fresa",
                descripcion="Helado de fresa natural",
                precio=6.99,
                costo=3.00,
                stock=30,
                stock_minimo=10,
                tipo_medida="pieza",
                categoria="Helados",
                activo=True
            ),
            Product(
                codigo_barras="004",
                nombre="Waffle",
                descripcion="Waffle recién hecho",
                precio=4.99,
                costo=2.00,
                stock=20,
                stock_minimo=5,
                tipo_medida="pieza",
                categoria="Acompañamientos",
                activo=True
            ),
            Product(
                codigo_barras="005",
                nombre="Malteada",
                descripcion="Malteada de chocolate",
                precio=7.99,
                costo=3.50,
                stock=15,
                stock_minimo=5,
                tipo_medida="litros",
                categoria="Bebidas",
                activo=True
            )
        ]
        db.add_all(productos)
        db.commit()
        
        # Crear ventas de ejemplo
        print("💰 Creando ventas de ejemplo...")
        for i in range(10):
            fecha = datetime.utcnow() - timedelta(days=random.randint(0, 30))
            
            venta = Sale(
                folio=f"V{fecha.strftime('%Y%m%d')}{i:03d}",
                usuario_id=random.choice([u.id for u in users]),
                subtotal=random.uniform(15, 50),
                descuento=random.uniform(0, 5),
                iva=0,
                total=random.uniform(20, 60),
                metodo_pago=random.choice(["efectivo", "tarjeta", "transferencia"]),
                monto_pagado=random.uniform(20, 100),
                cambio=0,
                estado="completada",
                created_at=fecha
            )
            
            # Agregar detalles
            for _ in range(random.randint(1, 3)):
                producto = random.choice(productos)
                cantidad = random.randint(1, 5)
                precio_unitario = producto.precio
                
                detalle = SaleDetail(
                    producto_id=producto.id,
                    cantidad=cantidad,
                    precio_unitario=precio_unitario,
                    subtotal=cantidad * precio_unitario,
                    descuento=0
                )
                venta.detalles.append(detalle)
            
            db.add(venta)
        
        db.commit()
        
        print("✅ Base de datos inicializada exitosamente!")
        print(f"   - {len(users)} usuarios creados")
        print(f"   - {len(productos)} productos creados")
        print(f"   - 10 ventas de ejemplo creadas")
        
    except Exception as e:
        db.rollback()
        print(f"❌ Error: {e}")
    finally:
        db.close()


if __name__ == "__main__":
    seed_database()
