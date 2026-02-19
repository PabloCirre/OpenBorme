# Modelo de Datos (Schema)

OpenBorme utiliza un modelo relacional simplificado diseñado para la búsqueda rápida y el análisis de series temporales de actos mercantiles.

## Entidades Principales

### 1. Empresa (`company`)

Representa la entidad legal inscrita en el Registro Mercantil.

- **CIF**: Identificador fiscal único (Clave Primaria).
- **Nombre**: Razón social normalizada.
- **Provincia**: Delegación del Registro Mercantil donde está inscrita.

### 2. Acto (`act`)

Representa un evento atómico publicado en el BORME.

- **ID**: Hash MD5 generado a partir del contenido del acto para garantizar trazabilidad.
- **Tipo de Acto**: Categoría normalizada (ej: Constitución, Nombramientos, Cese).
- **Fecha**: Fecha de publicación en el BOE (YYYY-MM-DD).
- **Sección**: Sección del BORME (I o II).
- **Detalles**: Texto completo extraído mediante el pipeline OCR.

## Relaciones

Un `company` puede tener múltiples `acts` asociados a lo largo del tiempo, permitiendo reconstruir su historial o "Timeline" de eventos corporativos.

## Formatos de Exportación

Los datos están disponibles para descarga masiva en los siguientes formatos:

- **CSV**: Para análisis rápido en Excel/Pandas.
- **JSON**: Para integración en aplicaciones.
- **Parquet**: *[PRÓXIMAMENTE]* Para procesamiento Big Data.
