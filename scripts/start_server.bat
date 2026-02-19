@echo off
echo Iniciando servidor local de OpenBorme en http://localhost:8000...
php -S localhost:8000
if %errorlevel% neq 0 (
    echo ERROR: Asegúrate de tener PHP instalado y en tu PATH.
    pause
)
