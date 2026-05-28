# 🚀 Guía de Despliegue - Cremeria Francis

## Resumen Rápido
Sistema POS completo listo para desplegar en **GitHub Pages** + **Render** con un **único link** funcionando.

---

## 📋 Requisitos
- Cuenta en **GitHub** (gratis)
- Cuenta en **Render** (gratis) - https://render.com
- Navegador web

---

## ⚡ OPCIÓN 1: Deploy Automático (Recomendado)

### Paso 1: Preparar el Repositorio

1. **Fork o clona este repositorio en GitHub**
   ```bash
   git clone https://github.com/tu-usuario/cremeria-system.git
   cd cremeria-system
   ```

2. **Configura el archivo de API URL**
   - Edita `frontend/services/api.js`
   - Reemplaza `https://cremeria-francis-api.onrender.com` con tu URL de Render (la copiarás después)

### Paso 2: Desplegar Backend en Render

1. **Abre https://render.com y crea una cuenta**

2. **Crea un nuevo servicio Web:**
   - Click en "New +" → "Web Service"
   - Conecta tu repositorio de GitHub
   - Selecciona tu rama `main`

3. **Configura:**
   - **Name:** `cremeria-francis-api`
   - **Environment:** `Docker`
   - **Build Command:** (dejar vacío)
   - **Start Command:** `python main.py`
   - **Plan:** Free

4. **Agrega variables de entorno:**
   - `DATABASE_URL`: `sqlite:///./cremeria.db`
   - `SECRET_KEY`: `cremeria-francis-secure-key-2024`
   - `ENVIRONMENT`: `production`

5. **Deploy:**
   - Click en "Create Web Service"
   - Espera a que termine el deploy (5-10 minutos)
   - Copia la URL que te genera (ej: `https://cremeria-francis-api.onrender.com`)

### Paso 3: Configurar GitHub Pages

1. **Actualiza la URL del API en el frontend:**
   - Abre `frontend/services/api.js`
   - Reemplaza:
     ```javascript
     return 'https://cremeria-francis-api.onrender.com' // Tu URL de Render
     ```
   - Haz commit y push:
     ```bash
     git add .
     git commit -m "Configure production API URL"
     git push
     ```

2. **Activa GitHub Pages:**
   - Ve a tu repositorio en GitHub
   - Settings → Pages
   - Source: Deploy from a branch
   - Branch: `main` → `/` (raíz)
   - Click en "Save"

3. **El workflow automático se ejecutará:**
   - Verifica en Actions que el deploy fue exitoso
   - Tu site estará en: `https://tu-usuario.github.io/cremeria-system/`

---

## 🔗 Links Finales

Después de completar los pasos, tendrás:

- **API (Backend):** `https://cremeria-francis-api.onrender.com`
  - Docs interactiva: `https://cremeria-francis-api.onrender.com/docs`
  - Health check: `https://cremeria-francis-api.onrender.com/health`

- **Frontend (Sitio):** `https://tu-usuario.github.io/cremeria-system/`

---

## 🔐 Credenciales de Demo

| Rol | Email | Contraseña |
|-----|-------|-----------|
| Admin | `admin@cremeria.com` | `admin123` |
| Vendedor | `vendedor@cremeria.com` | `vendedor123` |

---

## 📱 Funcionalidades Disponibles

✅ Login/Autenticación  
✅ Dashboard con estadísticas  
✅ CRUD Productos  
✅ Sistema POS (ventas)  
✅ Reportes  
✅ Gestión de usuarios  
✅ Roles (admin/vendedor)  

---

## 🔧 Troubleshooting

### Error: "CORS policy"
- Verifica que la URL del API en `frontend/services/api.js` sea correcta
- Asegúrate que el backend esté desplegado y respondiendo en `/health`

### Error: "Token inválido"
- Limpia el localStorage en el navegador
- Press F12 → Application → LocalStorage → Delete All
- Recarga e inicia sesión nuevamente

### Backend no responde
- Ve a tu servicio en Render y verifica que esté "Running"
- Haz clic en "Manual Deploy" para redesplegarlo

### Frontend no carga
- Verifica que GitHub Pages esté activado en Settings → Pages
- Espera 2-3 minutos después del primer deploy
- Prueba limpiar el caché del navegador (Ctrl+Shift+Del)

---

## 📝 Notas

- La BD SQLite se crea automáticamente en Render
- Los datos persisten entre reinicios
- El plan Free de Render se duerme después de 15 min sin uso
- Para "despertar" el servidor: refresca la página y espera 30 segundos

---

## 🎓 Para el Docente

**Link único para compartir:**
```
https://tu-usuario.github.io/cremeria-system/#/login
```

**Ejemplo completo:**
```
https://juanperez.github.io/cremeria-system/#/login
```

Los estudiantes inician sesión con:
- Email: `admin@cremeria.com`
- Contraseña: `admin123`

---

## ✨ Avanzado: Personalización

### Cambiar credenciales demo
Ejecuta en el backend:
```bash
python seed_db.py
```

### Cambiar nombre/colores
- Logo: Edita `frontend/App.vue`
- Colores: Edita `frontend/assets/style.css`
- Nombre: Busca "Cremeria Francis" en todos los archivos

### Agregar funcionalidades
Estructura fácil de extender:
- Nuevos modelos en `backend/app/models/`
- Nuevas rutas en `backend/app/routes/`
- Nuevas vistas en `frontend/views/`

---

**¿Preguntas?** Revisa la documentación en `README_NUEVA.md`
