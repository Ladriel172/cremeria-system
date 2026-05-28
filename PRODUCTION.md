# 📦 Estructura Proyecto para Producción

## Backend (Python/FastAPI)

```
backend/
├── main.py                 # App principal (detecta PORT y ENV)
├── config.py              # Configuración
├── database.py            # SQLAlchemy + SQLite
├── Procfile               # Heroku/Render config
├── requirements.txt       # pip install -r
└── app/
    ├── models/            # Modelos DB
    ├── routes/            # APIs
    └── schemas/           # Validación Pydantic
```

**Cambios para producción:**
- ✅ `main.py` lee `PORT` de env
- ✅ `main.py` lee `ENVIRONMENT` (development/production)
- ✅ CORS configurado para GitHub Pages
- ✅ `.env` con variables de configuración

---

## Frontend (Vue 3/SPA)

```
frontend/
├── index.html              # HTML principal
├── main.js                 # Entrada Vue
├── App.vue                 # Layout
├── router.js               # Hash mode para GitHub Pages
├── services/api.js         # Detecta dev/prod
├── .nojekyll              # Para GitHub Pages
├── 404.html               # Manejo de rutas
└── views/                 # Componentes
```

**Cambios para producción:**
- ✅ `router.js` usa `createWebHashHistory()`
- ✅ `api.js` detecta si está en GitHub Pages
- ✅ `.nojekyll` evita que GitHub procese los archivos

---

## CI/CD (GitHub Actions)

```
.github/workflows/
└── deploy-frontend.yml    # Deploy automático a GitHub Pages
```

**Cómo funciona:**
1. Push a `main` en `/frontend`
2. GitHub Actions ejecuta
3. Copia `/frontend` a GitHub Pages
4. Sitio vivo en 2-3 minutos

---

## Archivos Clave para Deploy

| Archivo | Propósito | Estado |
|---------|----------|--------|
| `Dockerfile` | Build en Render | ✅ Configurado |
| `render.yaml` | Config Render | ✅ Configurado |
| `backend/Procfile` | Comando inicio | ✅ Configurado |
| `backend/requirements.txt` | Dependencias | ✅ Actualizado |
| `frontend/router.js` | Hash mode | ✅ Configurado |
| `frontend/services/api.js` | Detect env | ✅ Configurado |
| `.github/workflows/` | Auto deploy | ✅ Creado |

---

## Variables de Entorno

### Backend (Render)

```env
DATABASE_URL=sqlite:///./cremeria.db
SECRET_KEY=cremeria-francis-secure-key-2024
ENVIRONMENT=production
```

### Frontend (services/api.js)

```javascript
// Auto-detecta:
// - localhost:8000 → desarrollo local
// - github.io → producción Render
```

---

## URLs en Producción

**Backend (Render - Docker):**
```
https://cremeria-francis-api.onrender.com
├── /health
├── /docs (Swagger)
└── /redoc (ReDoc)
```

**Frontend (GitHub Pages):**
```
https://usuario.github.io/cremeria-system
├── /#/login
├── /#/dashboard
└── /#/productos
```

---

## Testing Antes de Deploy

### Local
```bash
# Backend
cd backend
python main.py

# Frontend (otra ventana)
cd frontend
python -m http.server 3000
```

Accede a: http://localhost:3000

### Producción
1. Deploy a Render ✓
2. Deploy a GitHub Pages ✓
3. Prueba el link: https://usuario.github.io/cremeria-system/

---

## Flujo de Deploy

```
┌─────────────────────┐
│   Tu Computadora    │
└──────────┬──────────┘
           │ git push
           ▼
┌─────────────────────┐
│  GitHub (Main Repo) │
└──────────┬──────────┘
           │
      ┌────┴────┐
      ▼         ▼
  ┌────────┐ ┌──────────┐
  │ Render │ │  Pages   │
  └────────┘ └──────────┘
     API     Frontend
```

---

## Keep-Alive para Render (Opcional)

Para evitar que el backend se duerma:

```bash
# En tu máquina, ejecuta cada 30 minutos:
curl https://cremeria-francis-api.onrender.com/health
```

O configura un servicio externo como **UptimeRobot** (gratis).

---

## Rollback/Revert

Si algo falla:

**Backend (Render):**
- Va a Render dashboard
- Click en "Manual Deploy" con versión anterior

**Frontend (GitHub Pages):**
- Revert en GitHub
- Push a main
- GitHub Actions redeploya automáticamente

---

## 📊 Comparativa: Local vs Producción

| Aspecto | Local | Producción |
|---------|-------|-----------|
| Backend | localhost:8000 | Render (cloud) |
| Frontend | localhost:3000 | GitHub Pages |
| BD | Local SQLite | Render SQLite |
| CORS | Localhost | GitHub Pages URL |
| Router | History mode | Hash mode |
| Deploy | Manual | Automático |

---

**Referencia:** Ver `DEPLOY.md` para instrucciones paso a paso detalladas.
