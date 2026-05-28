from pydantic import BaseModel, EmailStr
from typing import Optional
from datetime import datetime


class UserCreate(BaseModel):
    nombre: str
    correo: EmailStr
    password: str
    rol: str = "vendedor"


class UserLogin(BaseModel):
    correo: EmailStr
    password: str


class UserResponse(BaseModel):
    id: int
    nombre: str
    correo: str
    rol: str
    estado: str
    created_at: datetime

    class Config:
        from_attributes = True
