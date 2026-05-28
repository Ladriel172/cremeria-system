@echo off
REM Cremeria Francis - Script de inicio para Windows

echo ========================================
echo   Cremeria Francis - Sistema POS
echo ========================================
echo.

REM Verificar Python
python --version >nul 2>&1
if errorlevel 1 (
    echo ❌ Python no está instalado o no está en el PATH
    pause
    exit /b 1
)

REM Iniciar Backend
echo Iniciando Backend FastAPI...
cd backend
if not exist "venv" (
    echo Creando entorno virtual...
    python -m venv venv
)
call venv\Scripts\activate.bat
pip install -r requirements.txt -q

REM Crear BD si no existe
python -c "from database import init_db; init_db(); print('✅ Base de datos lista')" 

REM Iniciar en nueva ventana
echo Iniciando servidor en http://localhost:8000
start cmd /k "cd backend && venv\Scripts\activate.bat && python main.py"

timeout /t 3

REM Iniciar Frontend
cd ..\frontend
echo Iniciando Frontend Vue 3 en http://localhost:3000
start cmd /k "cd frontend && python -m http.server 3000"

echo.
echo ✅ Sistema iniciado!
echo.
echo 📍 Backend: http://localhost:8000
echo 📍 Frontend: http://localhost:3000
echo 📍 Docs API: http://localhost:8000/docs
echo.
echo 🔐 Credenciales:
echo    Admin: admin@cremeria.com / admin123
echo    Vendedor: vendedor@cremeria.com / vendedor123
echo.
pause
