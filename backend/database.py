from sqlalchemy import create_engine
from sqlalchemy.orm import declarative_base, sessionmaker
from config import DATABASE_URL

# Crear motor PostgreSQL
engine = create_engine(
    DATABASE_URL,
    pool_pre_ping=True
)

# Sesión
SessionLocal = sessionmaker(
    autocommit=False,
    autoflush=False,
    bind=engine
)

# Base para modelos
Base = declarative_base()


def get_db():
    """
    Dependency para inyectar sesión de BD en rutas
    """
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()


def init_db():
    """
    Crear todas las tablas
    """
    Base.metadata.create_all(bind=engine)