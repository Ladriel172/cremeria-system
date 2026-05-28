from sqlalchemy import Column, Integer, String, DateTime, Enum as SQLEnum
from datetime import datetime
import enum
from database import Base


class RoleEnum(str, enum.Enum):
    ADMIN = "admin"
    VENDEDOR = "vendedor"


class StatusEnum(str, enum.Enum):
    ACTIVO = "activo"
    INACTIVO = "inactivo"


class User(Base):
    __tablename__ = "usuarios"

    id = Column(Integer, primary_key=True, index=True)
    nombre = Column(String(100), nullable=False)
    correo = Column(String(150), unique=True, nullable=False, index=True)
    password = Column(String(255), nullable=False)
    rol = Column(SQLEnum(RoleEnum), default=RoleEnum.VENDEDOR, index=True)
    estado = Column(SQLEnum(StatusEnum), default=StatusEnum.ACTIVO, index=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)

    def __repr__(self):
        return f"<User {self.correo}>"
