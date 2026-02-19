# Objetivos de OpenBorme

Este documento detalla los fallos del sistema BORME actual y establece la hoja de ruta para que OpenBorme se convierta en una plataforma de datos abiertos "perfecta".

## Objetivo Técnico: Horizonte 2020

- **Horizonte Temporal**: Consolidación y extracción completa de datos desde el **1 de enero de 2020**.
- **Estimación**: ~25 GB de datos acumulados para este periodo.

## Arquitectura "Fly Mode" (Zero-Storage)

- **Local (Procesador Efímero)**: Actúa como el **Extractor al Vuelo**.
  - Descarga el PDF a `/tmp`.
  - Extrae el texto y normaliza.
  - **Borra el PDF**.
  - Guarda el registro en la BBDD.
- **Ventaja**: Elimina la necesidad de 50GB+ de almacenamiento local.
- **Remoto (openborme.es)**: Visualizador optimizado de los datos extraídos.

## Fallos posibles del BORME (como producto, como dato y como “infra”)

### 1) Buscabilidad incompleta (el mayor fallo práctico)

- No hay un buscador “por empresa” unificado para todo lo publicado en “Actos inscritos”.
- La base de datos “Anuncios BORME” solo cubre la Sección Segunda.
- Alternativas comunitarias como LibreBORME han surgido para llenar este vacío.

### 2) Demasiada dependencia del PDF

- El formato “jurídicamente oficial” es PDF, lo que obliga a tareas de parseo frágiles y costosas.
- Falta un modelo de datos oficial consumible.

### 3) API insuficiente para reutilización seria

- La API actual solo ofrece una llamada para el sumario del día.
- Es insuficiente para consultas por empresa, series temporales o descargas masivas estructuradas.

### 4) Cobertura temporal con “escalones”

- Disponibilidad irregular de datos anteriores a 2009.
- Huecos y formatos cambiantes según el año.

### 5) Falta de “entidades” y enlaces

- No existen fichas oficiales de empresa con historial o relaciones entre personas/órganos.
- Ausencia de IDs estables de entidad.

### 6) Calidad del dato y normalización

- Desafíos con fe de erratas y variabilidad lingüística (abreviaturas, formatos de importes, etc.).

### 7) Licencia y “apertura” con condiciones

- Marco legal restrictivo que puede frenar la innovación masiva.

### 8) Transparencia económica

- BORME es publicidad oficial, pero no un "registro abierto estructurado".

### 9) Accesibilidad, UX y productividad

- Falta de alertas, exportaciones y paneles profesionales.

### 10) Riesgo operativo para reutilizadores

- El "scraping de PDFs" implica altos costes de mantenimiento ante cambios editoriales.

---

## Qué debería tener OpenBorme para ser “perfecto y abierto”

### A) Principios no negociables

- **Trazabilidad absoluta** a la fuente oficial (PDF original).
- **Separación** entre “hecho publicado” y “estado consolidado”.
- **Reutilización clara** y segura (atribución impecable).

### B) Producto: Lo que un profesional necesita

- **Buscador único** (Empresa/Persona/Texto) en Sección I y II.
- **Ficha de empresa (Core)**: Timeline de eventos y relaciones vinculadas.
- **Ficha de persona/órgano**: Cargos cruzados.
- **Alertas y Exportaciones**: CSV, XLSX, JSON.

### C) Datos: Modelo relacional serio

- Publicación de un **schema estable**.
- Entidades mínimas: `publication`, `document`, `notice/event`, `company`, `person`, `role`, `event_type`.
- Dos capas: `raw_text` y `parsed_fields`.

### D) Ingesta: Pipeline robusto

- Extracción automatizada, normalización y deduplicación (gestión de erratas).
- QA automático y métricas de calidad.

### E) API de verdad (No solo sumarios)

- Endpoints por entidad: `/companies`, `/persons`, `/events`.
- OpenAPI, versionado y rate limits claros.

### F) Descarga masiva y formatos eficientes

- Dumps periódicos en **Parquet**, **JSONL** y **CSV**.

### G) Licenciamiento bien planteado

- Respeto a la fuente oficial + Licencia abierta para datos derivados (CC BY / ODC-BY).

### H) Privacidad y cumplimiento

- Cumplimiento estricto de RGPD con canales de soporte/takedown claros.

### I) Gobernanza y transparencia técnica

- Código Open Source, Data Contract y Issue Tracker público.

---

## Checks de Perfección (10 Puntos)

1. [ ] Buscador único de Sección Primera + Segunda.
2. [ ] Ficha de empresa con timeline navegable.
3. [ ] Ficha de persona con cargos cruzados.
4. [ ] Evento atómico con enlace a PDF oficial.
5. [ ] Erratas modeladas como “correcciones”.
6. [ ] API con endpoints por entidad.
7. [ ] Dumps masivos en Parquet/JSONL.
8. [ ] Licencia y atribución impecables.
9. [ ] Métricas de calidad de extracción públicas.
10. [ ] Código y schema abiertos, versionados y estables.
