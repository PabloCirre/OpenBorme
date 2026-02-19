# Manifiesto Técnico OpenBorme: Estándar de Transparencia y Gobernanza

Este documento establece los principios técnicos y éticos que rigen el proyecto OpenBorme. Su objetivo es garantizar la reproducibilidad, la confianza en los datos estructurados y la claridad en la gestión del proyecto de código abierto.

## 1. Contrato de Datos (Data Contract)

OpenBorme se compromete a mantener un esquema de datos estable y versionado para facilitar la interoperabilidad.

### Modelo de Datos Principal

#### Entidad: Acto (`Publication`)

Unidad atómica de información registral.

| Campo | Tipo | Descripción | Ejemplo |
| :--- | :--- | :--- | :--- |
| `id` | STRING (PK) | Identificador único del BORME | `BORME-A-2026-3024` |
| `date` | DATE | Fecha de publicación oficial | `2026-02-11` |
| `section` | ENUM | Sección del boletín (I/II) | `SECCIÓN PRIMERA` |
| `type` | ENUM | Tipo de acto normalizado | `CONSTITUCIÓN` |
| `company_uid` | STRING | Identificador fiscal (CIF) | `B81234567` |
| `raw_text` | TEXT | Texto original extraído | `...Constitución...` |
| `hash` | STRING (MD5) | Hash de integridad del texto | `e99a18...` |

### Versionado

El esquema sigue Versionado Semántico.

- **v1.0 (Actual)**: Estructura base de actos y sumarios.
- **Migraciones**: Los cambios breaking se anunciarán en `CHANGELOG.md` con scripts de migración asociados en `/schema/migrations`.

## 2. Metodología Reproducible

La construcción del dataset OpenBorme es 100% reproducible siguiendo este pipeline:

1. **Ingesta**: Descarga diaria de XMLs de sumario y PDFs de la API de Datos Abiertos del BOE.
2. **Extracción**: Procesamiento de PDFs mediante `pypdf`/`pdfplumber` para obtener texto plano.
3. **Segmentación**: División del texto continuo en actos individuales usando expresiones regulares de cabecera (`BORME-[A|B]-...`).
4. **Normalización**: Limpieza de saltos de línea, corrección de OCR y estandarización de entidades (Nombres en mayúsculas, fechas ISO).
5. **Quality Assurance (QA)**: Validación cruzada contra el sumario XML original.
6. **Publicación**: Generación de archivos estáticos (JSON/HTML) y cálculo de hashes.

**Gestión de Erratas**: Las correcciones se realizan sobre el parser, no sobre la base de datos, forzando un reprocesamiento del día afectado para mantener la trazabilidad.

## 3. Código Core (Open Source)

Los siguientes módulos del pipeline son totalmente abiertos para auditoría:

- `/pipeline/ingest`: Scripts de descarga y verificación de sumarios.
- `/pipeline/extract`: Lógica de extracción de texto y OCR ligero.
- `/pipeline/normalize`: Reglas de normalización de entidades.
- `/web`: Frontend completo (PHP/JS) para la visualización.

**Nota**: La infraestructura de servidores, claves de API privadas y configuraciones de seguridad (WAF) no forman parte del repositorio público por seguridad.

## 4. Métricas de Calidad y Cobertura

Publicamos reportes de transparencia sobre la salud del dataset:

- **Cobertura**: Porcentaje de días del año con boletín procesado (Objetivo: 100% L-V laborables).
- **Integridad**: Ratio de actos extraídos vs. actos listados en el sumario XML oficial.
- **Anomalías**: Detección de boletines con >5% de actos vacíos o fallidos.

Un panel simplificado de estas métricas está disponible en `/status` y en la documentación técnica.

## 5. Dataset de Muestra (Audit Sample)

Para facilitar la evaluación sin descargar todo el histórico, ofrecemos un dataset de muestra en `/samples` que incluye:

- 3 días completos de publicaciones (Lunes, Miércoles, Viernes).
- Ejemplos representativos de Sección I (Actos) y Sección II (Anuncios).
- Provincias variadas (Madrid, Barcelona, Ávila).

Formatos disponibles: `sample_data.jsonl`, `sample_render.html`.

## 6. Gobernanza del Proyecto

OpenBorme es un proyecto comunitario gestionado con estándares profesionales.

- **Licencia**: Código bajo **MIT License**. Datos estructurados bajo **ODbL** (Open Database License) con atribución obligatoria.
- **Contribuciones**: Se aceptan Pull Requests que mejoren los parsers o la UI. Ver `CONTRIBUTING.md`.
- **Seguridad**: Reporte responsable de vulnerabilidades a `security@openborme.es`. Ver `SECURITY.md`.

### Plantillas de Issues

Favor usar las plantillas predefinidas para:

- 🐛 Bug de UI/Visualización.
- 📉 Error de Parsing (adjuntar ID del acto).
- 💡 Solicitud de nueva funcionalidad.

## 7. Política de Privacidad y RGPD

La transparencia y el respeto a la privacidad son fundamentales.

- **Búsqueda de Personas**: El módulo de búsqueda por nombre de persona física se mantiene **INACTIVO** por defecto en el repositorio público para prevenir la indexación masiva de individuos.
- **No Perfilado**: OpenBorme no genera ni almacena perfiles agregados de personas físicas. La relación es siempre Acto → Empresa.
- **Derecho al Olvido**: Existe un canal formal (`privacidad@openborme.es`) para solicitar la desindexación de actos específicos por motivos legítimos, aunque la fuente original (BOE) permanezca inalterada.

---
*Este manifiesto es un documento vivo. Última actualización: Febrero 2026.*
