🎯 CHEAT SHEET - Comandos y Links
==================================

## 📋 URLs Importantes

### Desarrollo Local
```
Frontend:  http://localhost:3000
Backend:   http://localhost:8000
API Docs:  http://localhost:8000/docs
```

### Producción (después del deploy)
```
Backend API:     https://cremeria-francis-api.onrender.com
API Docs:        https://cremeria-francis-api.onrender.com/docs
Frontend:        https://tu-usuario.github.io/cremeria-system/
Login:           https://tu-usuario.github.io/cremeria-system/#/login
```

---

## 🔐 Credenciales Demo

```
ADMIN:
  Email: admin@cremeria.com
  Pass:  admin123

VENDEDOR:
  Email: vendedor@cremeria.com
  Pass:  vendedor123
```

---

## 🛠️ Comandos Desarrollo Local

### Setup Backend
```bash
cd backend
python -m venv venv
venv\Scripts\activate  # Windows
# source venv/bin/activate  # Linux/Mac
pip install -r requirements.txt
python main.py
```

### Setup Frontend
```bash
cd frontend
python -m http.server 3000
```

### Inicializar BD con Datos
```bash
cd backend
venv\Scripts\activate  # Windows
python seed_db.py
```

---

## 🚀 Deploy a Producción

### 1. Backend en Render
```
1. https://render.com → Sign Up
2. New Web Service
3. Conectar GitHub
4. Select repo + main branch
5. Configurar env vars (ver DEPLOY.md)
6. Esperar 5-10 min
7. Copiar URL (ej: cremeria-api.onrender.com)
```

### 2. Actualizar URL Frontend
```bash
# backend/services/api.js línea 12
return 'https://cremeria-francis-api.onrender.com'

git add .
git commit -m "Update API URL"
git push
```

### 3. GitHub Pages
```
1. GitHub repo → Settings
2. Pages → Branch: main / /
3. Save
4. Esperar 2-3 min
5. Tu sitio: https://usuario.github.io/cremeria-system/
```

---

## 📦 APIs Disponibles

### Auth
```
POST /api/auth/login
  body: { correo, password }
  response: { access_token, user }

POST /api/auth/register
  body: { nombre, correo, password, rol }
  response: { user }

GET /api/auth/me
  headers: { Authorization: Bearer TOKEN }
  response: { user }
```

### Productos
```
GET /api/products/?skip=0&limit=10
  response: [ { id, nombre, precio, stock, ... } ]

POST /api/products/
  body: { nombre, precio, costo, stock, ... }

PUT /api/products/{id}
  body: { nombre?, precio?, ... }

DELETE /api/products/{id}

GET /api/products/stock/bajo
  response: [ productos con stock bajo ]
```

### Ventas
```
GET /api/sales/?skip=0&limit=10
  response: [ { folio, total, estado, ... } ]

POST /api/sales/
  body: { folio, usuario_id, total, detalles: [...] }

GET /api/sales/{id}

PUT /api/sales/{id}/cancel
```

---

## 🔄 Git Workflow

```bash
# Clonar repo
git clone https://github.com/usuario/cremeria-system.git
cd cremeria-system

# Crear rama (opcional)
git checkout -b feature/mi-feature

# Hacer cambios
# ... editar archivos ...

# Commit
git add .
git commit -m "Descripción clara del cambio"

# Push
git push origin main
# o: git push origin feature/mi-feature

# Pull request (si usas rama)
# → GitHub → Compare & pull request
```

---

## 🐛 Debugging

### Ver logs Backend (Render)
```
1. https://dashboard.render.com
2. Select service
3. Logs tab
```

### Ver logs Frontend (GitHub Pages)
```
1. GitHub repo → Actions tab
2. Last workflow run
3. Click en "deploy-frontend"
```

### Testing API Local
```bash
# En otra terminal
curl http://localhost:8000/health
curl http://localhost:8000/docs

# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"correo":"admin@cremeria.com","password":"admin123"}'
```

---

## 📊 Archivos Principales

### Backend
- `main.py` - Punto entrada
- `config.py` - Configuración
- `database.py` - BD setup
- `app/models/` - Modelos
- `app/routes/` - APIs
- `requirements.txt` - Dependencias

### Frontend
- `index.html` - HTML
- `main.js` - Entrada Vue
- `App.vue` - Layout
- `router.js` - Rutas
- `services/api.js` - Cliente HTTP
- `views/` - Páginas

### Config
- `Dockerfile` - Para Render
- `render.yaml` - Config Render
- `.github/workflows/` - CI/CD
- `.env` - Vars (NO compartir)

---

## 📝 Documentación Completa

| Doc | Contenido |
|-----|-----------|
| `QUICK_START.md` | 5 min deploy |
| `DEPLOY.md` | Paso a paso |
| `PRODUCTION.md` | Detalles técnicos |
| `TEACHER.md` | Info docentes |
| `README_NUEVA.md` | Docs general |
| `DOCS.md` | Índice de todo |

---

## ✅ Checklist Rápido

```
Desarrollo:
[ ] cd backend && python main.py
[ ] cd frontend && python -m http.server 3000
[ ] Visitar http://localhost:3000
[ ] Login con admin@cremeria.com / admin123

Deploy:
[ ] Backend en Render
[ ] Copiar URL API
[ ] Actualizar frontend/services/api.js
[ ] Commit y push
[ ] Frontend en GitHub Pages
[ ] Esperar 2-3 min
[ ] Visitar https://usuario.github.io/cremeria-system/
[ ] Test login

Compartir:
[ ] Docente: https://usuario.github.io/cremeria-system/#/login
[ ] Credenciales: admin@cremeria.com / admin123
```

---

## 🆘 Errores Comunes

```
CORS error
→ Verificar URL en frontend/services/api.js
→ Verificar que backend responde en /health

Token inválido
→ F12 → Application → LocalStorage → Clear All
→ Recarga e inicia sesión

Backend no responde
→ Visita Render dashboard
→ Manual deploy
→ Espera 30 seg

Frontend no carga
→ Verifica GitHub Pages en Settings
→ Ctrl+Shift+Del (limpiar caché)
→ Espera 2-3 min después de push
```

---

## 🎓 Para Docentes

**Link para clase:**
```
https://usuario.github.io/cremeria-system/#/login
```

**Cómo verificar:**
1. Abre el link
2. Inicia sesión con admin
3. Verifica que todas las funciones funcionen

**Si falla:**
1. Verifica backend en Render (/health)
2. Limpia caché del navegador
3. Revisa la URL del API

---

**Última actualización:** Mayo 2026
**Rápido referencia:** ⭐ Bookmark este archivo
