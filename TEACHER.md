# 🎓 Información para el Docente

## Link Único para Estudiantes

Después de completar el deploy:

```
https://tu-usuario.github.io/cremeria-system/#/login
```

### Ejemplo:
```
https://juanperez.github.io/cremeria-system/#/login
```

---

## Credenciales de Demo

```
Admin:
  Email: admin@cremeria.com
  Contraseña: admin123

Vendedor:
  Email: vendedor@cremeria.com
  Contraseña: vendedor123
```

---

## Verificación de Funcionalidad

### Checklist de Testing

- [ ] Página de login carga
- [ ] Login funciona con admin
- [ ] Dashboard muestra datos
- [ ] Crear producto
- [ ] Realizar venta
- [ ] Ver reportes
- [ ] Login con vendedor
- [ ] Vendedor no ve usuarios/reportes (RBAC)

---

## Tecnologías Usadas

- **Backend:** FastAPI (Python 3.11)
- **Frontend:** Vue 3 (Sin build, SPA)
- **Base de Datos:** SQLite (File-based, portable)
- **Hosting Backend:** Render (gratis)
- **Hosting Frontend:** GitHub Pages (gratis)
- **CI/CD:** GitHub Actions (automático)

---

## Arquitectura

```
┌─────────────────────────────────────────────┐
│           GitHub Pages (Frontend)            │
│  Vue 3 SPA - Carga en el navegador           │
└──────────────┬──────────────────────────────┘
               │ HTTP Request
               ▼
┌─────────────────────────────────────────────┐
│     Render (Backend API - FastAPI)          │
│  Python 3.11 - REST API                     │
└──────────────┬──────────────────────────────┘
               │ Read/Write
               ▼
┌─────────────────────────────────────────────┐
│        SQLite Database (Render)             │
│  cremeria.db - Datos Persistentes           │
└─────────────────────────────────────────────┘
```

---

## APIs Disponibles (Documentadas)

**Swagger (Interactivo):**
```
https://tu-api.onrender.com/docs
```

**Endpoints Principales:**
- `POST /api/auth/login` - Autenticación
- `GET /api/products/` - Listar productos
- `POST /api/sales/` - Crear venta
- `GET /api/sales/` - Listar ventas

---

## Características Implementadas

✅ **Autenticación JWT**
- Login/Logout
- Tokens con expiración 24h
- Refresh automático

✅ **Sistema de Roles**
- Admin: Acceso completo
- Vendedor: Solo ventas y dashboard

✅ **Gestión de Productos**
- CRUD completo
- Categorías
- Stock tracking
- Alertas de stock bajo

✅ **Sistema POS**
- Carrito de compra
- Cálculo de totales e IVA
- Métodos de pago
- Cambio automático

✅ **Reportes Básicos**
- Dashboard con estadísticas
- Histórico de ventas
- Usuarios activos

✅ **Seguridad**
- CORS configurado
- Passwords hasheados (bcrypt)
- Validación de datos (Pydantic)
- CSRF protection ready

---

## Notas Importantes

### Render (Backend)
- Plan Free se duerme tras 15 min sin uso
- Para "despertar": visita el sitio (30 seg. espera)
- BD persiste entre reinicios
- Logs disponibles en Render dashboard

### GitHub Pages
- Deploy automático al hacer push
- 2-3 minutos para estar vivo
- Todas las rutas usan hash (`/#/`)
- No requiere servidor, solo archivos estáticos

### SQLite
- No requiere servidor SQL
- BD única archivo: `cremeria.db`
- Perfecto para demostración
- Limitable: ~100 MB para GitHub Pages

---

## Troubleshooting Rápido

| Problema | Solución |
|----------|----------|
| "CORS error" | Verifica URL API en `frontend/services/api.js` |
| "Backend no responde" | Visita `/health` de Render → Manual deploy |
| "Login no funciona" | Limpia localStorage + recarga |
| "Rutas no cargan" | Usa hash mode: `/#/login` no `/login` |
| "Datos no persisten" | Verifica BD en Render logs |

---

## Archivos Importantes

| Archivo | Para qué | Ubicación |
|---------|----------|-----------|
| DEPLOY.md | Instrucciones paso a paso | `/` |
| QUICK_START.md | Resumen rápido 5 min | `/` |
| PRODUCTION.md | Detalles técnicos | `/` |
| README_NUEVA.md | Documentación API | `/` |
| backend/main.py | Punto entrada API | `/backend` |
| frontend/index.html | Página principal | `/frontend` |

---

## Para Extender el Proyecto

### Agregar Nueva Funcionalidad

1. **Backend:** Crear modelo + ruta en FastAPI
2. **Frontend:** Crear vista Vue + llamada API
3. **Commit y Push** → Deploy automático

### Ejemplo: Agregar "Clientes"

**Backend:**
```python
# backend/app/models/customer.py
class Customer(Base):
    __tablename__ = "clientes"
    ...
```

**Frontend:**
```vue
<!-- frontend/views/Clientes.vue -->
<template>...</template>
```

Luego:
```bash
git add .
git commit -m "Add customers feature"
git push
```

---

## Contacto/Soporte

- **Documentación completa:** `README_NUEVA.md`
- **Stack técnico:** Python + Vue.js
- **Deployment:** Cloud (Render + GitHub)
- **Código fuente:** Abierto en GitHub

---

**Última actualización:** Mayo 2026
**Versión:** 1.0.0 (Producción)
**Estado:** ✅ Listo para usar
