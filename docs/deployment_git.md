# Guía de Despliegue en Git (Doble Repositorio)

Este proyecto utiliza una arquitectura de doble repositorio para proteger el código del pipeline y los datos privados, mientras se mantiene una presencia pública transparente.

## Estructura de Repositorios

1. **Privado (`PrivateBorme`)**: Contiene el 100% del proyecto (Pipeline, Web, Ops, Datos).
2. **Público (`OpenBorme`)**: Contiene únicamente la documentación (`docs/`) y la interfaz web (`public_html/`).

## Configuración de Remotos

Para verificar la configuración actual, ejecuta:

```powershell
git remote -v
```

Deberías ver:

- `private`: <https://github.com/PabloCirre/PrivateBorme.git>
- `public`: <https://github.com/PabloCirre/OpenBorme.git>

## Flujo de Sincronización

### 1. Subir al Repositorio Privado (Todo)

Es el comando estándar que sube todos los archivos del proyecto.

```powershell
git add .
git commit -m "Mensaje descriptivo"
git push private main
```

### 2. Subir al Repositorio Público (Docs y Web)

Para subir solo la parte pública, se utiliza el script automatizado:

```powershell
python ops/git_sync.py
```

*Este script gestiona automáticamente la limpieza de carpetas privadas antes de subir a `OpenBorme`.*

## Gestión de Credenciales (Sin Ventanas de Login)

Hemos configurado el sistema para que sea totalmente silencioso:

1. **Helper Manager**: `git config --global credential.helper manager` (Gestiona las claves en Windows).
2. **Usuario por Defecto**: `git config --global credential.https://github.com.username PabloCirre` (Evita que Git pregunte qué usuario elegir si tienes varios).

### ¿Qué hacer si vuelve a aparecer?

Si la ventana de "Elegir usuario" vuelve a aparecer, asegúrate de seleccionar tu cuenta de GitHub y marcar la casilla de **"Recordar siempre"** o **"Remember me"**. A partir de ahí, el script `ops/git_sync.py` no te volverá a preguntar.

---

> [!CAUTION]
> **Seguridad**: Nunca desactives el `.gitignore` antes de subir al repositorio público. El script de sincronización está diseñado para evitar que archivos sensibles como `config.php` o bases de datos locales (`.db`) se filtren a `OpenBorme`.
