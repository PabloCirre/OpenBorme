# Script para iniciar el servidor local de OpenBorme
Write-Host "Iniciando servidor local de OpenBorme en http://localhost:8000..." -ForegroundColor Cyan

# Comprobrar si PHP está instalado
if (!(Get-Command php -ErrorAction SilentlyContinue)) {
    Write-Host "ERROR: PHP no está instalado o no está en el PATH." -ForegroundColor Red
    pause
    exit
}

# Iniciar servidor interno de PHP
php -S localhost:8000
