# Modelo de Datos (Schema)

OpenBorme utiliza un modelo relacional simplificado sobre **SQLite** (`pipeline/data/openborme.sqlite`) diseñado para búsqueda rápida y análisis temporal.

## Entidades Principales

### 1. Empresa (`company`)

Representa la entidad legal inscrita en el Registro Mercantil.

- **CIF**: Identificador fiscal único (Clave Primaria).
- **Nombre**: Razón social normalizada.
- **Provincia**: Delegación del Registro Mercantil donde está inscrita.

### 2. Acto (`act`)

Representa un evento atómico publicado en el BORME.

- **ID**: Identificador del acto (fuente BORME o ID interno de parsing).
- **Hash MD5**: Huella única de contenido para deduplicación y trazabilidad.
- **Tipo de Acto**: Categoría normalizada (ej: Constitución, Nombramientos, Cese).
- **Fecha**: Fecha de publicación en el BOE (formato `YYYYMMDD` en SQLite actual).
- **Sección**: Sección del BORME (I o II).
- **Detalles**: Texto completo extraído mediante el pipeline OCR.
- **normalized_type**: Tipo canónico (`CONSTITUCION`, `DISOLUCION`, `CESE`, etc.).
- **event_group**: Grupo analítico (`CREATION`, `DISSOLUTION`, `OTHER`, `MIXED`).
- **is_creation / is_dissolution**: Flags para métricas de altas/bajas.
- **company_name_norm**: Nombre social normalizado en mayúsculas para matching.

## Relaciones

Un `company` puede tener múltiples `acts` asociados a lo largo del tiempo, permitiendo reconstruir su historial o "Timeline" de eventos corporativos.

## Formatos de Exportación

Los datos están disponibles para descarga masiva en los siguientes formatos:

- **SQLite**: Base oficial del proyecto (`openborme.sqlite`).
- **CSV**: Exportaciones puntuales.
- **JSON**: Vía API.
