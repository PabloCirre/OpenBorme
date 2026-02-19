# Metodología de Extracción

OpenBorme utiliza un pipeline de procesamiento avanzado para transformar los boletines oficiales en datos útiles y legibles.

## 1. Ingesta de Datos

Cada día, nuestro sistema se conecta a la API de datos abiertos de la Agencia Estatal BOE para obtener el sumario del Boletín Oficial del Registro Mercantil. Descargamos tanto los sumarios en XML como los documentos individuales en PDF y XML (Secciones I y II).

## 2. Parsing y Estructuración

Utilizamos dos motores especializados:

- **Motor PDF (Sección I)**: Extrae texto plano de los boletines provinciales y utiliza expresiones regulares para identificar actos inscritos, empresas y CIFs.
- **Motor XML (Sección II)**: Procesa los anuncios legales estructurados directamente desde la fuente XML oficial.

## 3. Normalización

Los datos extraídos se normalizan para corregir errores comunes en la fuente original (como formatos de CIF inconsistentes) y se preparan para su indexación en nuestra base de datos derivada.

---

> [!NOTE]
> Todo este proceso se realiza de forma automatizada y con trazabilidad completa a los documentos originales del BOE a través de hashes MD5.
