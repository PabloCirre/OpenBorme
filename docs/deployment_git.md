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

Hemos configurado `credential.helper manager-core`. Esto significa que:

- La primera vez que hagas push, Git te pedirá las credenciales (si no están guardadas).
- Una vez introducidas, **Windows las recordará de forma segura**.
- En los siguientes despliegues, no volverá a aparecer ninguna ventana interactiva.

---

> [!CAUTION]
> **Seguridad**: Nunca desactives el `.gitignore` antes de subir al repositorio público. El script de sincronización está diseñado para evitar que archivos sensibles como `config.php` o bases de datos locales (`.db`) se filtren a `OpenBorme`.
