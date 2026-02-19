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
* **Frontend Ligero**: Interfaz web PHP sobria y rápida, sin rastreadores ni publicidad.

## 🛠️ Estructura del Proyecto

```bash
/pipeline   # Scripts de ingestión, extracción y normalización (Core)
/web        # Frontend PHP (Interfaz pública)
/docs       # Manifiesto técnico, métricas y guías
/schema     # Modelos de datos y migraciones SQL
/ops        # Scripts de despliegue y sincronización
/samples    # Datos de muestra para auditoría y pruebas
```

## 📦 Instalación

### Requisitos

* PHP 8.0+
* Python 3.9+
* Pipenv o virtualenv
* Composer

### Configuración Local

1. Clonar el repositorio:

    ```bash
    git clone https://github.com/PabloCirre/OpenBorme.git
    cd OpenBorme
    ```

2. Instalar dependencias de Python (Pipeline):

    ```bash
    cd pipeline
    pip install -r requirements.txt
    ```

3. Instalar dependencias de PHP (Web/Parsers):

    ```bash
    composer install
    ```

4. Configurar entorno:

    ```bash
    cp ops/env.example .env
    # Editar .env con tus rutas locales
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
