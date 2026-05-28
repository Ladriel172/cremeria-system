#!/usr/bin/env sh
set -eu

cd "$(dirname "$0")"

if ! command -v php >/dev/null 2>&1; then
    echo "No se encontro PHP. Instala PHP 8 o superior."
    exit 1
fi

echo "Iniciando Cremeria Francis en http://localhost:8000"
php -S localhost:8000 -t .
