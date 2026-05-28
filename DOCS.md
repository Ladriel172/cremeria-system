🎯 Índice de Documentación - Cremeria Francis
==============================================

## 📚 Guías de Instalación y Despliegue

| Documento | Público | Contenido |
|-----------|---------|-----------|
| **QUICK_START.md** | ✅ | Deploy en 5 minutos (para apurados) |
| **DEPLOY.md** | ✅ | Guía paso a paso completa con screenshots |
| **TEACHER.md** | ✅ | Info específica para docentes |
| **PRODUCTION.md** | ✅ | Detalles técnicos y arquitectura |
| **README_NUEVA.md** | ✅ | Documentación general del proyecto |

## 📖 Estructura del Código

```
cremeria-system/
│
├── backend/                 # FastAPI (Python)
│   ├── app/
│   │   ├── models/         # Modelos BD (User, Product, Sale)
│   │   ├── routes/         # APIs (auth, products, sales)
│   │   └── schemas/        # Validación Pydantic
│   ├── main.py             # Punto entrada
│   ├── config.py           # Configuración
│   ├── database.py         # SQLAlchemy setup
│   ├── requirements.txt    # pip install
│   ├── Procfile            # Para Render
│   ├── .env                # Variables (NO compartir)
│   └── .env.example        # Template (SÍ compartir)
│
├── frontend/               # Vue 3 SPA
│   ├── views/              # Páginas
│   │   ├── Login.vue
│   │   ├── Dashboard.vue
│   │   ├── Productos.vue
│   │   ├── Ventas.vue
│   │   ├── Usuarios.vue
│   │   └── Reportes.vue
│   ├── components/         # Componentes
│   ├── services/
│   │   └── api.js          # Cliente HTTP (detecta prod/dev)
│   ├── assets/
│   │   └── style.css       # Estilos globales
│   ├── index.html          # Punto entrada
│   ├── main.js             # Config Vue
│   ├── App.vue             # Layout
│   ├── router.js           # Hash mode para GH Pages
│   ├── .nojekyll           # Para GitHub Pages
│   └── 404.html            # Manejo rutas
│
├── .github/
│   └── workflows/
│       └── deploy-frontend.yml  # GitHub Actions
│
├── Dockerfile              # Para Render
├── render.yaml             # Config Render
├── docker-compose.yml      # Para desarrollo
│
├── DEPLOY.md               # 👈 Empieza aquí
├── QUICK_START.md          # Para apurados
├── PRODUCTION.md           # Detalles técnicos
├── TEACHER.md              # Para docentes
├── README_NUEVA.md         # Docs general
└── DOCS.md                 # Este archivo
```

## 🚀 Flujo de Deploy Recomendado

1. **Lee:** `QUICK_START.md` (5 min)
2. **Si necesitas detalle:** `DEPLOY.md` (15 min)
3. **Deploy Backend:** Render (10 min)
4. **Deploy Frontend:** GitHub Pages (5 min)
5. **Test:** `https://tuusuario.github.io/cremeria-system/`

## 🔧 Para Desarrolladores

### Agregar Nueva Funcionalidad

1. **Backend:**
   - Modelo en `backend/app/models/`
   - Schema en `backend/app/schemas/`
   - Ruta en `backend/app/routes/`
   - API automáticamente disponible en `/docs`

2. **Frontend:**
   - Servicio en `frontend/services/api.js`
   - Vista en `frontend/views/`
   - Ruta en `frontend/router.js`

3. **Deploy:**
   ```bash
   git add .
   git commit -m "Descripción"
   git push
   ```
   → GitHub Actions redeploya automáticamente

### Testing Local

```bash
# Terminal 1 - Backend
cd backend
python main.py

# Terminal 2 - Frontend
cd frontend
python -m http.server 3000
```

Visita: http://localhost:3000

## 📊 APIs Disponibles

**Documentación interactiva:**
- Swagger: `https://tu-api.onrender.com/docs`
- ReDoc: `https://tu-api.onrender.com/redoc`

**Endpoints principales:**
- Auth: `/api/auth/login`, `/api/auth/register`
- Products: `/api/products/` (CRUD)
- Sales: `/api/sales/` (CRUD)
- Health: `/health`

## 🎓 Para Docentes

**Link para compartir con estudiantes:**
```
https://tuusuario.github.io/cremeria-system/#/login
```

**Credenciales demo:**
- Admin: `admin@cremeria.com` / `admin123`
- Vendedor: `vendedor@cremeria.com` / `vendedor123`

Ver `TEACHER.md` para más información.

## 🐛 Troubleshooting

### Error: "CORS policy"
→ Ver DEPLOY.md sección Troubleshooting

### Error: "Backend no responde"
→ Ver PRODUCTION.md sección Keep-Alive

### Error: "Frontend no carga"
→ Ver DEPLOY.md sección GitHub Pages

## 📞 Soporte

1. **Documentación:** Los archivos .md están completos
2. **Código comentado:** Revisa los archivos .py y .vue
3. **GitHub Issues:** Si encuentras bugs

## 🏆 Checklist de Deploy

- [ ] Cuenta en GitHub
- [ ] Cuenta en Render
- [ ] Backend desplegado en Render
- [ ] Frontend desplegado en GitHub Pages
- [ ] URL de API en `frontend/services/api.js`
- [ ] Push a main
- [ ] Test: `https://usuario.github.io/cremeria-system/`
- [ ] Comparte link con docente

## 📝 Cambios desde Versión PHP

| Aspecto | Antes (PHP) | Ahora (FastAPI+Vue) |
|---------|-----------|-------------------|
| Backend | PHP CLI | FastAPI |
| BD | SQLite | SQLite (igual) |
| Frontend | HTML + PHP | Vue 3 SPA |
| Hosting | Render | Render + GitHub |
| Servidor Local | XAMPP | Python |
| Deploy | Manual | Automático |

---

**Última actualización:** Mayo 2026
**Versión:** 1.0.0 - Producción Ready
**Status:** ✅ Listo para compartir con docentes
