#!/bin/bash

echo "========================================"
echo "  Cremeria Francis - Sistema POS"
echo "========================================"
echo ""

# Verificar Python
if ! command -v python3 &> /dev/null; then
    echo "❌ Python3 no está instalado"
    exit 1
fi

# Backend
echo "Iniciando Backend FastAPI..."
cd backend

if [ ! -d "venv" ]; then
    echo "Creando entorno virtual..."
    python3 -m venv venv
fi

source venv/bin/activate

pip install -r requirements.txt -q

# Crear BD
python3 -c "from database import init_db; init_db(); print('✅ Base de datos lista')"

# Iniciar en background
echo "Iniciando servidor en http://localhost:8000"
python3 main.py &
BACKEND_PID=$!

sleep 3

# Frontend
cd ../frontend
echo "Iniciando Frontend Vue 3 en http://localhost:3000"
python3 -m http.server 3000 &
FRONTEND_PID=$!

echo ""
echo "✅ Sistema iniciado!"
echo ""
echo "📍 Backend: http://localhost:8000"
echo "📍 Frontend: http://localhost:3000"
echo "📍 Docs API: http://localhost:8000/docs"
echo ""
echo "🔐 Credenciales:"
echo "   Admin: admin@cremeria.com / admin123"
echo "   Vendedor: vendedor@cremeria.com / vendedor123"
echo ""
echo "Presiona Ctrl+C para detener..."

wait
