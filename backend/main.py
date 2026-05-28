from fastapi import FastAPI, Depends, HTTPException, status, Header
from fastapi.middleware.cors import CORSMiddleware
from sqlalchemy.orm import Session
import os

from config import API_TITLE, API_VERSION, API_DESCRIPTION
from database import init_db, get_db
from app.models import User
from app.routes import auth_router, products_router, sales_router
from app.routes.auth import verify_token

# Crear app
app = FastAPI(
    title=API_TITLE,
    version=API_VERSION,
    description=API_DESCRIPTION
)

# CORS - Permitir desarrollo y producción
ALLOWED_ORIGINS = [
    "http://localhost:3000",
    "http://localhost:5173",
    "http://localhost:8000",
    "http://127.0.0.1:3000",
    "http://127.0.0.1:8000",
    "https://ladriel172.github.io",
]

# Agregar dominio de GitHub Pages si existe en env
github_pages_url = os.getenv("GITHUB_PAGES_URL", "")
if github_pages_url:
    ALLOWED_ORIGINS.append(github_pages_url)

# En producción, permitir cualquier origen que venga del FRONTEND_URL
frontend_url = os.getenv("FRONTEND_URL", "")
if frontend_url:
    ALLOWED_ORIGINS.append(frontend_url)

app.add_middleware(
    CORSMiddleware,
    allow_origins=ALLOWED_ORIGINS,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)


# Inicializar BD al arrancar
@app.on_event("startup")
def startup():
    init_db()
    print("✅ Base de datos inicializada")
    print(f"📍 CORS Origins: {ALLOWED_ORIGINS}")


# Rutas
app.include_router(auth_router)
app.include_router(products_router)
app.include_router(sales_router)


# Root
@app.get("/")
def read_root():
    return {
        "mensaje": "Bienvenido a Cremeria Francis API",
        "version": API_VERSION,
        "docs": "/docs"
    }


# Health check
@app.get("/health")
def health_check():
    return {"status": "ok"}


# Middleware para verificar token en rutas protegidas
async def verify_auth(authorization: str = Header(None)):
    if not authorization:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Token no proporcionado"
        )
    
    try:
        scheme, token = authorization.split()
        if scheme.lower() != "bearer":
            raise HTTPException(
                status_code=status.HTTP_401_UNAUTHORIZED,
                detail="Scheme inválido"
            )
        return verify_token(token)
    except ValueError:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Formato de token inválido"
        )


if __name__ == "__main__":
    import uvicorn
    port = int(os.getenv("PORT", 8000))
    uvicorn.run(
        "main:app",
        host="0.0.0.0",
        port=port
    )
