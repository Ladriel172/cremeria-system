# Cremeria Francis

Sistema POS para cremeria con usuarios, productos, ventas y reportes.

## Ejecutar sin XAMPP

1. Extrae el ZIP o clona el repositorio.
2. Instala PHP 8 o superior y agregalo al PATH.
3. Da doble clic en `start.bat`.
4. Se abrira `http://localhost:8000/setup.php`.

El sistema usa SQLite, asi que no necesitas XAMPP, Apache ni MySQL. La base se genera automaticamente en `database/cremeria.db`.

## GitHub

GitHub sirve para subir y compartir el codigo. Importante: GitHub Pages no ejecuta PHP, por eso este proyecto no puede funcionar como pagina estatica en `github.io`.

Para demo en vivo desde GitHub, conecta el repositorio a un hosting que ejecute Docker/PHP, por ejemplo Render, Railway o Fly.io. Este proyecto incluye `Dockerfile` y `render.yaml`.

En Render:

1. Sube el proyecto a GitHub.
2. En Render crea un nuevo `Blueprint`.
3. Selecciona el repositorio.
4. Render detectara `render.yaml` y levantara la app.

El comando interno del contenedor es:

```bash
php -S 0.0.0.0:${PORT:-8000} -t .
```

En Linux o macOS tambien puedes correr:

```bash
sh start.sh
```

## Credenciales demo

- Admin: `admin@cremeria.com` / `admin123`
- Vendedor: `vendedor@cremeria.com` / `vendedor123`

## Subir el codigo

Sube todo el proyecto excepto los archivos ignorados por `.gitignore`. En otra computadora basta con descargar el ZIP y ejecutar `start.bat`.
