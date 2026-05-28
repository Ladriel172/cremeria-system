📊 ARQUITECTURA - Cremeria Francis
===================================

## 🏗️ Diagrama General

```
                    ESTUDIANTE/DOCENTE
                          │
                          ▼
            ┌─────────────────────────┐
            │   GitHub Pages (SPA)    │
            │  https://user.io/...    │
            │   Vue 3 Frontend        │
            └────────────┬────────────┘
                         │ HTTP REST API
                         ▼
            ┌─────────────────────────┐
            │  Render (Docker)        │
            │  FastAPI Backend        │
            │  https://...render.com  │
            └────────────┬────────────┘
                         │ Read/Write
                         ▼
            ┌─────────────────────────┐
            │   SQLite Database       │
            │   cremeria.db (Render)  │
            └─────────────────────────┘
```

---

## 📦 Stack Técnico

### Frontend
```
HTML/CSS/JavaScript
    ↓
Vue 3 (Framework)
    ↓
Vue Router (Routing)
    ↓
Fetch API (HTTP)
    ↓
Bootstrap 5 (UI)
```

### Backend
```
FastAPI (Framework)
    ↓
Uvicorn (Server)
    ↓
SQLAlchemy (ORM)
    ↓
SQLite (Database)
    ↓
Pydantic (Validation)
    ↓
Python 3.11
```

---

## 🔄 Flujo de una Transacción (Login)

```
1. Usuario escribe email/password
           │
           ▼
2. Frontend (Vue) captura datos
           │
           ▼
3. JavaScript envía POST a /api/auth/login
           │
           ▼ (HTTP CORS)
           │
4. Backend (FastAPI) recibe request
           │
           ▼
5. Valida credenciales (Pydantic)
           │
           ▼
6. Busca usuario en SQLite
           │
           ▼
7. Verifica password (bcrypt)
           │
           ▼
8. Genera JWT token
           │
           ▼ (HTTP JSON)
           │
9. Frontend recibe token
           │
           ▼
10. Almacena token en localStorage
           │
           ▼
11. Guarda en localStorage user info
           │
           ▼
12. Router navega a /dashboard
           │
           ▼
13. Vue renderiza Dashboard.vue
```

---

## 📱 Componentes Frontend

```
App.vue (Raíz)
    ├── Navbar (Header)
    ├── Sidebar (Menú)
    └── Router View
            ├── Login.vue
            ├── Dashboard.vue
            ├── Productos.vue
            ├── Ventas.vue
            ├── Usuarios.vue
            └── Reportes.vue
```

---

## 🔌 Endpoints API

```
/                          GET   → Info API
/health                    GET   → Status
/docs                      GET   → Swagger UI
/redoc                     GET   → ReDoc

/api/auth/login            POST  → Autenticación
/api/auth/register         POST  → Registro
/api/auth/me               GET   → Usuario actual

/api/products/             GET   → Listar productos
/api/products/             POST  → Crear producto
/api/products/{id}         GET   → Obtener producto
/api/products/{id}         PUT   → Actualizar
/api/products/{id}         DELETE → Eliminar
/api/products/stock/bajo   GET   → Stock bajo

/api/sales/                GET   → Listar ventas
/api/sales/                POST  → Crear venta
/api/sales/{id}            GET   → Obtener venta
/api/sales/{id}/cancel     PUT   → Cancelar venta
```

---

## 🗄️ Modelos de Base de Datos

```
usuarios (tabla)
├── id
├── nombre
├── correo (unique)
├── password (hashed)
├── rol (admin/vendedor)
├── estado (activo/inactivo)
├── created_at
└── updated_at

productos (tabla)
├── id
├── codigo_barras
├── nombre
├── descripcion
├── precio
├── costo
├── stock
├── stock_minimo
├── tipo_medida
├── categoria
├── imagen
├── activo
├── created_at
└── updated_at

ventas (tabla)
├── id
├── folio (unique)
├── usuario_id (FK → usuarios.id)
├── subtotal
├── descuento
├── iva
├── total
├── metodo_pago
├── monto_pagado
├── cambio
├── estado
├── notas
├── created_at
└── updated_at

detalles_venta (tabla)
├── id
├── venta_id (FK → ventas.id)
├── producto_id (FK → productos.id)
├── cantidad
├── precio_unitario
├── subtotal
├── descuento
└── created_at
```

---

## 🔐 Seguridad

```
Password:
  Plain text → hash(bcrypt) → Almacenado en BD

Autenticación:
  Email + Password → JWT Token
  Token almacenado en localStorage
  Token enviado en cada request

CORS:
  localhost:3000         (desarrollo)
  localhost:8000         (desarrollo)
  github.io              (producción)
  Configurado en main.py

Roles:
  admin    → Acceso total
  vendedor → Solo ventas
  Verificado en cada ruta
```

---

## 🚀 Flujo de Deploy

```
                 Tu Computadora
                       │
              git push a GitHub
                       │
                       ▼
         ┌─────────────────────────┐
         │ GitHub (Main Repository)│
         └────────┬────────────────┘
                  │
         ┌────────┴────────┐
         │                 │
         ▼                 ▼
    ┌────────┐        ┌──────────┐
    │ Render │        │  Pages   │
    │ Deploy │        │  Deploy  │
    └────────┘        └──────────┘
         │                 │
         ▼                 ▼
    Backend API       Frontend SPA
    (Python)          (HTML/Vue)
```

---

## 🔄 Ciclo de Vida de una Venta

```
Frontend (Vue)
    │ Click "Nueva Venta"
    ▼
Buscar productos (GET /api/products/)
    │ Mostrar lista
    ▼
Seleccionar productos
    │ Agregar al carrito
    ▼
Ingresar cantidad y método pago
    │
    ▼
POST /api/sales/ con detalles
    │
Backend (FastAPI)
    ▼
Validar datos (Pydantic)
    ▼
Crear venta en BD
    ▼
Crear detalles de venta
    ▼
Actualizar stock de productos
    ▼
Retornar ID de venta
    │
Frontend
    ▼
Mostrar confirmación
    ▼
Limpiar carrito
    ▼
Redirigir a listado de ventas
```

---

## 📊 Estadísticas del Proyecto

```
Archivos Backend:        ~10
Archivos Frontend:       ~15
Documentación:           ~8 archivos
Modelos BD:              4 tablas
Endpoints API:           15+
Componentes Vue:         6 vistas
Líneas de código:        ~2000
Tiempo de setup:         5 minutos
```

---

## ♻️ Actualización de Código

```
1. Editar archivos
           │
           ▼
2. git add .
           │
           ▼
3. git commit -m "cambio"
           │
           ▼
4. git push
           │
           ▼
Frontend:
  ├─ GitHub Actions ejecuta
  ├─ Copia archivos a Pages
  └─ Deploy automático (2-3 min)

Backend:
  ├─ Render detecta push
  ├─ Rebuild Docker image
  └─ Deploy automático (5-10 min)
```

---

## 🎓 Cómo Extensible

**Agregar nueva tabla:**
```
1. models/nueva_tabla.py  → Crear modelo
2. schemas/nueva.py       → Crear schema
3. routes/nueva.py        → Crear rutas
4. Importar en main.py
5. API automáticamente en /docs
```

**Agregar nueva vista:**
```
1. views/Nueva.vue        → Crear componente
2. router.js              → Agregar ruta
3. App.vue                → Agregar link
4. services/api.js        → Agregar servicio
5. Push a main → Deploy automático
```

---

**Este documento es una referencia visual de la arquitectura completa.**

Para más detalles, ver documentación específica en otros archivos .md
