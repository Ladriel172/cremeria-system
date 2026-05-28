@echo off
setlocal
cd /d "%~dp0"

set "PHP_BIN=php"
where php >nul 2>nul

"%PHP_BIN%" -v >nul 2>nul
if errorlevel 1 (
    where docker >nul 2>nul
    if not errorlevel 1 (
        echo No se encontro PHP, pero si Docker.
        echo Iniciando Cremeria Francis con Docker en http://localhost:8000
        start "" http://localhost:8000/setup.php
        docker build -t cremeria-francis .
        docker run --rm -p 8000:8000 -v "%cd%\database:/app/database" cremeria-francis
        exit /b %errorlevel%
    )

    echo No se encontro PHP ni Docker.
    echo.
    echo Para ejecutar este proyecto sin XAMPP necesitas UNA de estas opciones:
    echo 1. Instalar PHP 8 o superior y agregarlo al PATH.
    echo 2. Instalar Docker Desktop.
    echo 3. Subir el repositorio a Render/Railway usando el Dockerfile incluido.
    echo.
    echo GitHub Pages no ejecuta PHP; solo sirve archivos HTML estaticos.
    pause
    exit /b 1
)

echo Iniciando Cremeria Francis en http://localhost:8000
start "" http://localhost:8000/setup.php
"%PHP_BIN%" -S localhost:8000
