# OpenBorme

**OpenBorme** es una plataforma de ingeniería de datos abierta que estructura, normaliza y publica el **Boletín Oficial del Registro Mercantil (BORME)** de España.

Nuestro objetivo es democratizar el acceso a la información empresarial mediante un pipeline reproducible, transparente y ético.

[![Estado](https://img.shields.io/badge/Estado-Activo-success)](https://openborme.es)
[![Licencia](https://img.shields.io/badge/Licencia-MIT-blue)](LICENSE)
[![Datos](https://img.shields.io/badge/Datos-ODbL-green)](http://opendatacommons.org/licenses/odbl/)

## 🌐 Entornos y Pruebas

Puedes visualizar y probar el proyecto en los siguientes entornos:

* **Producción**: [openborme.es](https://openborme.es/) (Versión estable y pública).
* **Local**: Sigue los pasos de instalación abajo para ejecutar una instancia local en `http://localhost:8000`.

## 🚀 Características

* **Ingesta Automática**: Descarga diaria de sumarios XML y PDFs de la [Agencia Estatal BOE](https://www.boe.es).
* **Extracción OCR**: Procesamiento de documentos PDF (Sección I) para extraer texto plano estructurado.
* **Normalización**: Limpieza de entidades, fechas y tipos de actos.
* **Trazabilidad**: Generación de hashes MD5 para garantizar la integridad del texto extraído frente al original.
* **Base de Datos Flexible**: SQLite por defecto (`openborme.sqlite`) y soporte para PostgreSQL remoto como base definitiva.
* **Frontend Ligero**: Interfaz web PHP sobria y rápida, sin rastreadores ni publicidad.

## 📁 Estructura del Proyecto

* **`pipeline/`**: Motor de extracción y procesamiento (PHP + Python).
* **`public_html/`**: Interfaz web y API (PHP/CSS).
* **`ops/`**: Scripts de despliegue y mantenimiento.
* **`docs/`**: Documentación técnica y estratégica.
* **`pipeline/data/`**: SQLite y datos locales de ejecución.

## 🛠️ Instalación (Local)

1. **Requisitos**: PHP 8.x (con `pdo_sqlite`), Python 3.10+.
2. **Web**: Sirve el directorio `public_html`.

    ```bash
    php -S localhost:8000 -t public_html
    ```

1. Clonar el repositorio:

    ```bash
    git clone https://github.com/PabloCirre/OpenBorme.git
    cd OpenBorme
    ```

4. Instalar dependencias de Python (Pipeline):

    ```bash
    cd pipeline
    pip install -r requirements.txt
    ```

5. Instalar dependencias de PHP (Web/Parsers):

    ```bash
    composer install
    ```

6. Configurar entorno (opcional):

    ```bash
    cp ops/env.example .env
    # Editar variables FTP y de base de datos (SQLite o PostgreSQL)
    ```

## 🗃️ Migración SQLite -> PostgreSQL (Remoto)

1. Crear esquema remoto automáticamente:

    ```bash
    OPENBORME_DB_DRIVER=pgsql \
    OPENBORME_PG_HOST=... OPENBORME_PG_DBNAME=... \
    OPENBORME_PG_USER=... OPENBORME_PG_PASS=... \
    php pipeline/migration/bootstrap_remote.php
    ```

2. Migrar datos desde SQLite local:

    ```bash
    OPENBORME_DB_DRIVER=pgsql \
    OPENBORME_PG_HOST=... OPENBORME_PG_DBNAME=... \
    OPENBORME_PG_USER=... OPENBORME_PG_PASS=... \
    php pipeline/migration/sqlite_to_remote.php --source=pipeline/data/openborme.sqlite --batch=2000
    ```

## 🐍 Builder Python (Recomendado para histórico)

Para cargas grandes (por ejemplo desde 2020), genera la SQLite con Python y deja PHP en lectura:

```bash
python3 pipeline/extract/extractor/build_db.py --start 2020-01-01 --end 2026-02-24 --reset --resume
```

Luego sube `pipeline/data/openborme.sqlite` a producción (FTP) y mantén `OPENBORME_WEB_READ_ONLY=1`.

Pipeline completo (build + upload FTP):

```bash
python3 ops/build_and_upload_sqlite.py --start 2020-01-01 --end 2026-02-24 --reset --resume
```

## 🤝 Contribuciones

Las contribuciones son bienvenidas, especialmente en mejoras de parsers y detección de errores OCR. Por favor lee [CONTRIBUTING.md](CONTRIBUTING.md) antes de enviar un Pull Request.

## 📄 Licencia

* **Código**: MIT License (ver [LICENSE](LICENSE)).
* **Datos Derivados**: Open Database License (ODbL). Debes atribuir a OpenBorme y al BOE al reutilizar los datos.

## 🔒 Privacidad

Este proyecto respeta estrictamente el RGPD. El módulo de búsqueda de personas físicas está **desactivado por defecto**. No se permite el uso del código para la creación de perfiles personales masivos. Ver [docs/MANIFIESTO_TECNICO.md](docs/MANIFIESTO_TECNICO.md).

---
*Desarrollado con ❤️ por Pablo Cirre.*
