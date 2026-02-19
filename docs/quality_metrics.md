# Métricas de Calidad y Transparencia Técnica

En OpenBorme, la integridad del dato es crítica. Monitorizamos continuamente la calidad de nuestro proceso de extracción automatizado.

## 1. Trazabilidad del Dato

Cada registro en nuestra base de datos mantiene un vínculo inmutable con su fuente:

- **Hash MD5**: Cada acto extraído genera un hash único basado en su contenido.
- **Enlace al Original**: Referencia directa a la URL del PDF/XML en `boe.es`.

## 2. Precisión del OCR

Utilizamos técnicas de extracción híbridas:

- **Nivel 1 (XML Estructurado)**: Precisión del **100%**. Se usa para la Sección II y sumarios.
- **Nivel 2 (PDF Textual)**: Precisión estimada del **99.5%**. Los errores suelen ser caracteres especiales o codificaciones extrañas en los PDFs originales.
- **Nivel 3 (OCR Visual)**: *No utilizado actualmente*. Solo extraemos texto digital nativo.

## 3. Gestión de Erratas

El sistema detecta automáticamente patrones de "Fe de Erratas" publicados en el BORME.

- Cuando una corrección es publicada, el sistema intenta vincularla con el acto original.
- Estado del acto: `Verificado` vs `Corregido`.

## 4. Reporte de Errores

Si detectas un error de extracción:

1. Verifica el PDF original en la web del BOE.
2. Si el PDF es correcto pero OpenBorme no: Repórtalo como **Bug de Pipeline**.
3. Si el PDF original contiene el error: Es un error de la fuente oficial y no podemos alterarlo sin una rectificación oficial.
