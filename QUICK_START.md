# ⚡ Inicio Rápido - 5 Minutos

## Versión Corta del Deploy

### 1️⃣ Backend en Render (2 minutos)

1. Abre **https://render.com** → Sign Up
2. Click "New +" → "Web Service"  
3. Conecta tu repositorio GitHub
4. **Nombre:** `cremeria-api`
5. **Environment:** Docker
6. Click "Create" → Espera 5 min
7. **Copia la URL** que te da (ej: `https://cremeria-api.onrender.com`)

### 2️⃣ Configurar Frontend (1 minuto)

1. Edita `frontend/services/api.js`
2. Reemplaza:
   ```javascript
   return 'https://cremeria-api.onrender.com' // Tu URL
   ```
3. Commit y push:
   ```bash
   git add .
   git commit -m "API URL"
   git push
   ```

### 3️⃣ Frontend en GitHub Pages (2 minutos)

1. GitHub → Settings → Pages
2. Branch: `main` → `/` 
3. Save
4. Tu sitio: `https://tuusuario.github.io/cremeria-system/`

---

## ✅ Listo!

**Link para compartir:**
```
https://tuusuario.github.io/cremeria-system/#/login
```

**Credenciales:**
- admin@cremeria.com / admin123
- vendedor@cremeria.com / vendedor123

---

**¿Necesitas ayuda?** Ver `DEPLOY.md`
