📱 **Sistema POS - Cremeria Francis**
====================================

**Listo para usar en clase. Un link. Sin instalación.**

---

## 🎯 Para Docentes: Link Único

Después de completar el setup (10 minutos):

```
https://tu-usuario.github.io/cremeria-system/#/login
```

**Credenciales:**
- Admin: `admin@cremeria.com` / `admin123`
- Vendedor: `vendedor@cremeria.com` / `vendedor123`

👉 **Ver `TEACHER.md` para más info**

---

## ⚡ Inicio Rápido (5 minutos)

1. **Fork/Clone este repositorio**
2. **Deploy Backend:** Render.com → 5 min
3. **Deploy Frontend:** GitHub Pages → 2 min
4. **Listo:** Compartir link con estudiantes

👉 **Ver `QUICK_START.md` para pasos**

---

## 📚 Documentación

| Documento | Para quién | Tiempo |
|-----------|-----------|--------|
| **QUICK_START.md** | Todos | 5 min |
| **DEPLOY.md** | Desarrolladores | 15 min |
| **TEACHER.md** | Docentes | 10 min |
| **CHEATSHEET.md** | Referencia rápida | - |
| **PRODUCTION.md** | Técnicos | - |
| **DOCS.md** | Índice completo | - |

👉 **Empieza con `QUICK_START.md`**

---

## 🏗️ Tecnología

```
┌──────────────────────────────────┐
│  Frontend: Vue 3 SPA             │
│  GitHub Pages (free hosting)     │
└──────────────┬───────────────────┘
               │ API REST
┌──────────────▼───────────────────┐
│  Backend: FastAPI (Python)       │
│  Render.com (free hosting)       │
└──────────────┬───────────────────┘
               │
┌──────────────▼───────────────────┐
│  Database: SQLite (portable)     │
└──────────────────────────────────┘
```

---

## ✨ Características

✅ **Autenticación JWT**  
✅ **Roles: Admin / Vendedor**  
✅ **Gestión de Productos** (CRUD)  
✅ **Sistema POS** (ventas + carrito)  
✅ **Reportes** (dashboard)  
✅ **Responsive Design** (mobile-friendly)  
✅ **Sin XAMPP / Sin configuración**  
✅ **Deploy automático**  

---

## 📖 Desarrollo Local

### Backend
```bash
cd backend
python -m venv venv
venv\Scripts\activate  # Windows
pip install -r requirements.txt
python main.py
```
→ http://localhost:8000

### Frontend
```bash
cd frontend
python -m http.server 3000
```
→ http://localhost:3000

---

## 🚀 Deploy a Producción

### Backend (Render - 5 min)
1. https://render.com
2. New Web Service
3. Conecta GitHub
4. Deploy

### Frontend (GitHub Pages - 2 min)
1. Settings → Pages
2. Branch: main / /
3. Save

**Más detalles en `QUICK_START.md`**

---

## 🎓 Estructura del Proyecto

```
cremeria-system/
├── backend/           # FastAPI (Python)
├── frontend/          # Vue 3 (JavaScript)
├── Dockerfile         # Para Render
├── render.yaml        # Config Render
└── .github/workflows/ # CI/CD
```

Documentación completa en `DOCS.md`

---

## 🆘 Ayuda

**Problema:** ¿Cómo hago deploy?  
**Solución:** Lee `QUICK_START.md`

**Problema:** ¿Cómo agrego una función?  
**Solución:** Lee `PRODUCTION.md` sección "Extender"

**Problema:** Código no funciona localmente  
**Solución:** Ver `CHEATSHEET.md` sección "Errores Comunes"

**Problema:** Backend no responde en producción  
**Solución:** Ver `TEACHER.md` sección "Troubleshooting"

---

## 📊 Roadmap de Uso

```
Día 1: Setup inicial
- Fork repo
- Deploy backend
- Deploy frontend
- Compartir link

Día 2+: Uso en clase
- Estudiantes usan sistema
- Agregar datos
- Generar reportes

Semana 2+: Extensiones
- Agregar nuevas funciones
- Customizar diseño
- Integrar con otros sistemas
```

---

## 🎯 Próximos Pasos

1. **Lee:** `QUICK_START.md` (5 min)
2. **Deploy:** Sigue los pasos
3. **Comparte:** Link con docente/estudiantes
4. **Extiende:** Agrega tus funciones

---

**Status:** ✅ Listo para producción  
**Última actualización:** Mayo 2026  
**Versión:** 1.0.0  

---

**¿Necesitas ayuda?** Abre `QUICK_START.md` o `TEACHER.md`
