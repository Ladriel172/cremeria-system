import os
from dotenv import load_dotenv

load_dotenv()

# Database
DATABASE_URL = os.getenv("DATABASE_URL", "sqlite:///./cremeria.db")

# JWT
SECRET_KEY = os.getenv("SECRET_KEY", "tu-clave-secreta-super-segura-2024")
ALGORITHM = "HS256"
ACCESS_TOKEN_EXPIRE_MINUTES = 1440  # 24 horas

# API
API_TITLE = "Cremeria Francis API"
API_VERSION = "1.0.0"
API_DESCRIPTION = "Sistema POS para cremeria"
