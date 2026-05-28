# Cremeria Francis - FastAPI + Vue 3 + SQLite

Sistema POS moderno para cremería con backend FastAPI y frontend Vue 3 SPA.

## Características

- ✅ **Backend FastAPI** - API REST rápida y moderna
- ✅ **Frontend Vue 3** - Single Page Application sin build
- ✅ **SQLite** - Base de datos portátil (file-based)
- ✅ **Autenticación JWT** - Seguridad con tokens
- ✅ **Roles**: Admin y Vendedor
- ✅ **Gestión de Productos** - CRUD completo
- ✅ **Sistema de Ventas** - POS con carrito
- ✅ **Reportes Básicos** - Dashboard y estadísticas
- ✅ **Responsive Design** - Bootstrap 5

## Estructura del Proyecto

```
cremeria-system/
├── backend/
│   ├── app/
│   │   ├── models/        # Modelos SQLAlchemy
│   │   ├── routes/        # Rutas/Endpoints
│   │   └── schemas/       # Schemas Pydantic
│   ├── main.py            # Punto de entrada FastAPI
│   ├── config.py          # Configuración
│   ├── database.py        # Setup BD
│   ├── requirements.txt   # Dependencias Python
│   └── .env               # Variables de entorno
│
├── frontend/
│   ├── views/             # Páginas Vue
│   ├── components/        # Componentes reutilizables
│   ├── services/          # Servicios API
│   ├── assets/            # Estilos y recursos
│   ├── index.html         # HTML principal
│   ├── main.js            # Punto de entrada Vue
│   ├── App.vue            # Componente raíz
│   ├── router.js          # Configuración de rutas
│   └── package.json       # Info del proyecto
```

## Requisitos

- **Python 3.9+**
- **Navegador moderno** (Chrome, Firefox, Edge, Safari)

## Instalación Rápida

### 1. Backend (FastAPI)

```bash
cd backend

# Crear entorno virtual
python -m venv venv

# Activar entorno
# Windows:
venv\Scripts\activate
# Linux/Mac:
source venv/bin/activate

# Instalar dependencias
pip install -r requirements.txt

# Iniciar servidor
python main.py
```

El backend correrá en `http://localhost:8000`
- Docs interactiva: `http://localhost:8000/docs`
- ReDoc: `http://localhost:8000/redoc`

### 2. Frontend (Vue 3)

```bash
cd frontend

# Opción A: Con Python (sin dependencias)
python -m http.server 3000

# Opción B: Con Node.js (si lo tienes instalado)
npx http-server -p 3000
```

El frontend estará en `http://localhost:3000`

## Credenciales Demo

- **Admin**
  - Email: `admin@cremeria.com`
  - Password: `admin123`

- **Vendedor**
  - Email: `vendedor@cremeria.com`
  - Password: `vendedor123`

## API Endpoints

### Autenticación
- `POST /api/auth/login` - Iniciar sesión
- `POST /api/auth/register` - Registrar usuario
- `GET /api/auth/me` - Obtener usuario actual

### Productos
- `GET /api/products/` - Listar productos (con filtros)
- `POST /api/products/` - Crear producto
- `GET /api/products/{id}` - Obtener producto
- `PUT /api/products/{id}` - Actualizar producto
- `DELETE /api/products/{id}` - Eliminar (desactivar)
- `GET /api/products/stock/bajo` - Productos con stock bajo

### Ventas
- `GET /api/sales/` - Listar ventas
- `POST /api/sales/` - Crear venta
- `GET /api/sales/{id}` - Obtener venta
- `PUT /api/sales/{id}/cancel` - Anular venta

## Base de Datos

La BD SQLite se crea automáticamente en `backend/cremeria.db` al iniciar el backend.

**Tablas:**
- `usuarios` - Usuarios del sistema
- `productos` - Catálogo de productos
- `ventas` - Registro de ventas
- `detalles_venta` - Detalles de cada venta

## Configuración

### Backend (.env)

```env
SECRET_KEY=tu-clave-secreta-super-segura-2024
DATABASE_URL=sqlite:///./cremeria.db
```

### Frontend (services/api.js)

```javascript
const API_URL = 'http://localhost:8000'
```

## Desarrollo

### Agregar un nuevo Modelo (Backend)

1. Crear modelo en `backend/app/models/`
2. Crear schema en `backend/app/schemas/`
3. Crear rutas en `backend/app/routes/`
4. Importar en `backend/main.py`

### Agregar una nueva Vista (Frontend)

1. Crear archivo `.vue` en `frontend/views/`
2. Agregar ruta en `frontend/router.js`
3. Agregar link en `frontend/App.vue`

## Despliegue

Para desplegar en producción:

**Backend:** Deploy FastAPI en Render, Railway, Fly.io
**Frontend:** Deploy en Vercel, Netlify, GitHub Pages

## Notas

- ✅ Sin necesidad de XAMPP o Apache
- ✅ Base de datos portátil (no requiere servidor)
- ✅ Frontend SPA sin necesidad de build
- ✅ Todo funciona localmente sin conexión a internet
- ⚙️ CORS habilitado para desarrollo

## Troubleshooting

**Error: "CORS policy"**
- Asegúrate que el backend esté corriendo en `http://localhost:8000`
- Verifica que frontend esté en `http://localhost:3000`

**Error: "SQLite database is locked"**
- Espera unos segundos y recarga
- Usa una sola sesión de backend

**Error: "Token inválido"**
- Limpia localStorage: F12 → Application → LocalStorage → Clear All
- Vuelve a iniciar sesión

## Licencia

MIT

## Autor

Cremeria Francis System
