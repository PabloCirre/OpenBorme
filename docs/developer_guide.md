# OpenBorme Developer Guide

Bienvenido a la guía técnica de **OpenBorme**, la plataforma abierta para el acceso estructurado a los datos del Boletín Oficial del Registro Mercantil (BORME).

## 🌍 Arquitectura del Proyecto

OpenBorme utiliza una **Arquitectura Híbrida "On-the-Fly"** para optimizar el rendimiento y minimizar el uso de disco:

1. **Extractor Efímero (Python)**: Ubicado en `pipeline/extract/`, descarga y procesa los PDFs en memoria o archivos temporales que **se destruyen inmediatamente** tras la extracción.
    - **Cero Almacenamiento**: No se guarda copia local de los PDFs (ahora ~50GB).
    - **Solo Datos**: Solo se persiste la información estructurada en `pipeline/data/openborme.sqlite`.
2. **Visualizador Remoto (PHP)**: La interfaz web desplegada en `openborme.es`.
3. **Sincronización**: Subida de código y SQLite vía FTP.

---

## 🐍 Motor de Extracción

El motor principal para histórico se ejecuta en Python (`pipeline/extract/extractor/build_db.py`).
La capa web en PHP queda en modo lectura (`OPENBORME_WEB_READ_ONLY=1`) para consumir la SQLite resultante.

### Instalación de Dependencias

```bash
pip install requests pypdf pandas openpyxl
```

### Uso del Motor

Para construir/actualizar SQLite desde 2020:

```bash
python3 pipeline/extract/extractor/build_db.py --start 2020-01-01 --end 2026-02-24 --reset --resume
```

Para recargas rápidas de ventana corta:

```bash
python3 pipeline/extract/extractor/build_db.py --start 2026-01-01 --end 2026-02-24 --resume
```

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

Utiliza `ops/deploy_prod.py` o `ops/ftp_deploy.py` con variables de entorno.

### Sincronización de Datos

Tras una extracción masiva en local, sincroniza el SQLite:

```bash
python ops/sync_data.py
```

Por defecto sube `pipeline/data/openborme.sqlite` a `pipeline/data/openborme.sqlite` en remoto.

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
