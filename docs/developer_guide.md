# OpenBorme Developer Guide

Bienvenido a la guía técnica de **OpenBorme**, la plataforma abierta para el acceso estructurado a los datos del Boletín Oficial del Registro Mercantil (BORME).

## 🌍 Arquitectura del Proyecto

OpenBorme utiliza una **Arquitectura Híbrida** para optimizar el rendimiento y la estabilidad:

1. **Extractor Local (Python)**: Ubicado en `core/extractor/`, es el motor de "fuerza bruta" encargado de descargar PDFs/XMLs y extraer datos estructurados.
2. **Visualizador Remoto (PHP)**: La interfaz web desplegada en `openborme.es` que sirve los datos de forma rápida y accesible.
3. **Sincronización**: Los datos procesados se suben al servidor mediante scripts de FTP (`scripts/sync_data.py`).

---

## 🐍 Motor de Extracción (Python)

El motor se encuentra en `core/extractor/` y utiliza `pypdf` y `requests`.

### Instalación de Dependencias

```bash
pip install requests pypdf pandas openpyxl
```

### Uso del Motor

Para realizar una carga histórica (ej: todo el año 2020):

```bash
cd core/extractor
python engine.py 2020-01-01 2020-12-31
```

### Estructura del Código

- `engine.py`: Coordinador principal.
- `borme_downloader.py`: Gestión de descargas desde la API del BOE.
- `parser_pdf.py`: Extractor de texto para la Sección I (PDF).
- `parser_xml.py`: Extractor de datos para la Sección II (XML).

---

## 🌐 API de OpenBorme

La API permite consultar datos de forma programática.

### Endpoints Principales

- `GET /api.php?action=get_act&id={id}`: Obtiene el JSON estructurado de un acto específico.
- `GET /api.php?action=status`: Estado de los procesos de extracción (en entorno local).

### Formato de Respuesta (Ejemplo)

```json
{
  "Date": "20240218",
  "Section": "1",
  "Province": "MADRID",
  "Company Name": "EJEMPLO SL",
  "CIF": "B12345678",
  "Act Type": "Constitución",
  "Details": "Texto completo del acto..."
}
```

---

## 🚀 Despliegue y Sincronización

### Despliegue de Código (FTP)

Utiliza `scripts/ftp_deploy.py` para sincronizar los cambios de código con el servidor. Configura tus credenciales en el script antes de ejecutarlo.

### Sincronización de Datos

Tras una extracción masiva en local, usa:

```bash
python scripts/sync_data.py
```

Esto subirá el archivo `borme_data.csv` al servidor remoto.

---

## 🧪 Entornos de Prueba

Como desarrollador, dispones de dos entornos principales para verificar cambios:

### 1. Entorno de Producción (Live)

- **URL**: [https://openborme.es/](https://openborme.es/)
- **Uso**: Verificación final de datos sincronizados y rendimiento en servidor real.

### 2. Entorno Local (Desarrollo)

- **URL**: Depende de tu configuración local (ej: `http://localhost:8080`).
- **Uso**: Pruebas de nuevos parsers, cambios en el CSS/UI y depuración del pipeline.
- **Ventaja**: Permite ver los logs de error de PHP y las trazas del motor de extracción en tiempo real.

---

## 🤝 Contribución

1. **Mejora de Parsers**: Si encuentras un formato de acto que no se extrae bien, edita `parser_pdf.py` o `parser_xml.py`.
2. **SEO y UX**: Ayúdanos a mejorar las landing pages en `templates/`.
3. **Seguridad**: Reporta cualquier vulnerabilidad a través de `/seguridad`.

---

> [!NOTE]
> OpenBorme no es una fuente oficial. Todos los datos deben ser verificados en la [Sede Electrónica del BOE](https://www.boe.es).
