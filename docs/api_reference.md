# Referencia de la API

La API de OpenBorme permite la consulta programática de actos mercantiles y datos de empresas.

## Base URL

`https://openborme.es/api.php`

## Endpoints

### 1. Obtener Acto por ID

Retorna la información completa de un acto específico.

- **Método**: `GET`
- **Parámetros**:
  - `action=get_act`: Acción requerida.
  - `id={md5_hash}`: El hash identificador del acto.

**Ejemplo de Petición:**
`GET /api.php?action=get_act&id=a1b2c3d4e5f6...`

### 2. Estado del Sistema

Retorna el estado de los procesos del motor (uso principalmente en local).

- **Método**: `GET`
- **Parámetros**:
  - `action=status`

## Formato de Respuesta (JSON)

```json
{
  "Date": "20240218",
  "Section": "1",
  "Province": "MADRID",
  "Company Name": "EJEMPLO SL",
  "CIF": "B12345678",
  "Act Type": "Constitución",
  "Details": "Texto completo del acto...",
  "MD5": "a1b2c3d4e5f6..."
}
```

---

> [!TIP]
> Para consultas masivas, recomendamos descargar los dumps periódicos en formato CSV disponibles en la sección de [Descargas](https://openborme.es/descargas).
