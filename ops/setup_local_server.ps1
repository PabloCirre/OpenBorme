# setup_local_server.ps1 
# Descarga, configura y ejecuta un servidor PHP Portable Automático para Windows sin requerir instalación manual.

$phpDir = "$PSScriptRoot\..\.bin\php"
$phpZip = "$PSScriptRoot\..\.bin\php.zip"
$phpExe = "$phpDir\php.exe"
$phpIni = "$phpDir\php.ini"

# 1. Crear directorio oculto para binarios si no existe
if (!(Test-Path -Path "$PSScriptRoot\..\.bin")) {
    New-Item -ItemType Directory -Path "$PSScriptRoot\..\.bin" | Out-Null
}

# 2. Descargar PHP x64 NTS para Windows si no existe
if (!(Test-Path -Path $phpExe)) {
    Write-Host ">>> [1/3] Descargando Servidor Web Motor PHP Portable (vs16 x64)..." -ForegroundColor Cyan
    # Versión estable 8.2.x Non-Thread Safe (Mejor para servidores internos)
    $url = "https://windows.php.net/downloads/releases/archives/php-8.2.14-nts-Win32-vs16-x64.zip"
    Invoke-WebRequest -Uri $url -OutFile $phpZip
    
    Write-Host ">>> [2/3] Extrayendo archivos..." -ForegroundColor Cyan
    Expand-Archive -Path $phpZip -DestinationPath $phpDir -Force
    Remove-Item -Path $phpZip
    
    # 3. Configurar PHP.ini para habilitar SQLite (Requerido para el Buscador OpenBorme)
    Write-Host ">>> [3/3] Configurando Motor Base de Datos SQLite (php.ini)..." -ForegroundColor Cyan
    Copy-Item "$phpDir\php.ini-development" $phpIni
    
    # Habilitar extensión dir
    (Get-Content $phpIni) -replace ';extension_dir = "ext"', 'extension_dir = "ext"' | Set-Content $phpIni
    # Habilitar PDO y SQLite3
    (Get-Content $phpIni) -replace ';extension=pdo_sqlite', 'extension=pdo_sqlite' | Set-Content $phpIni
    (Get-Content $phpIni) -replace ';extension=sqlite3', 'extension=sqlite3' | Set-Content $phpIni
    (Get-Content $phpIni) -replace ';extension=mbstring', 'extension=mbstring' | Set-Content $phpIni
    (Get-Content $phpIni) -replace ';extension=fileinfo', 'extension=fileinfo' | Set-Content $phpIni
    
    Write-Host ">>> Instalacion Completada con Exito." -ForegroundColor Green
} else {
    Write-Host ">>> Servidor PHP Portable ya detectado en el sistema." -ForegroundColor Green
}

# 4. Iniciar Servidor apuntando a la carpeta de la web (public_html)
$webRoot = "$PSScriptRoot\..\public_html"
Write-Host "==========================================================" -ForegroundColor Yellow
Write-Host " Arrancando OpenBorme Local Web Server en el puerto 8000" -ForegroundColor Yellow
Write-Host " AVISO: Manten esta ventana abierta mientras uses la web." -ForegroundColor Yellow
Write-Host "==========================================================" -ForegroundColor Yellow

Set-Location "$PSScriptRoot\.."

# Start the built in server targeting the public folder
& $phpExe -S localhost:8000 -t public_html
