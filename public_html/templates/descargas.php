<div class="container" style="padding: var(--space-6) 0;">
    <nav class="breadcrumbs">
        <a href="/">Inicio</a> /
        <span>Descargas</span>
    </nav>

    <div class="v3-landing-header">
        <h1 class="taxonomy-title">Sincronización de Datos & Descargas</h1>
        <p style="color: var(--text-muted); font-size: 1.15rem; max-width: 800px;">
            Acceso a los archivos de datos masivos estructurados de alta fidelidad para análisis técnico a gran escala.
        </p>
    </div>

    <div class="borme-grid">
        <!-- Parquet Dataset -->
        <div class="card grid-col-6" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div
                    style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-4);">
                    <h2 style="font-size: 1.25rem; color: var(--brand-dark);">BORME Histórico (Parquet)</h2>
                    <span class="badge"
                        style="background: var(--bg-soft); color: var(--accent); border: 1px solid var(--accent-faded);">OPTIMIZADO</span>
                </div>
                <p style="font-size: 14px; margin-bottom: var(--space-5);">
                    Archivo de alta compresión ideal para procesamiento masivo con Spark, Python (Pandas/Polars) o
                    BigQuery.
                </p>
                <div
                    style="background: #f9fafb; padding: var(--space-4); border-radius: var(--radius-sm); font-size: 12px; margin-bottom: var(--space-5);">
                    <p style="margin-bottom: 4px;"><strong>Tamaño:</strong> ~420 MB (Compreso)</p>
                    <p style="margin-bottom: 4px;"><strong>Estructura:</strong> Esquema tipado (Acto, Fecha, CIF, Texto)
                    </p>
                    <p class="mono" style="font-size: 10px; color: var(--text-muted); margin-top: 8px;">SHA256:
                        4e9a...a1c2</p>
                </div>
            </div>
            <button class="btn btn-secondary btn-m" disabled>SOLICITAR ACCESO</button>
        </div>

        <!-- CSV Dataset -->
        <div class="card grid-col-6" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div
                    style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-4);">
                    <h2 style="font-size: 20px;">BORME 2026 YTD (CSV)</h2>
                    <span class="badge">ACTUALIZADO</span>
                </div>
                <p style="font-size: 14px; margin-bottom: var(--space-5);">
                    Listado consolidado de todos los actos publicados en 2026. Compatible con Excel y bases de datos
                    SQL.
                </p>
                <div
                    style="background: #f9fafb; padding: var(--space-4); border-radius: var(--radius-sm); font-size: 12px; margin-bottom: var(--space-5);">
                    <p style="margin-bottom: 4px;"><strong>Registros:</strong> +90.800</p>
                    <p style="margin-bottom: 4px;"><strong>Formato:</strong> Comma Separated Values (UTF-8)</p>
                    <p class="mono" style="font-size: 10px; color: var(--text-muted); margin-top: 8px;">SHA256:
                        b812...e409</p>
                </div>
            </div>
            <a href="/exportar" class="btn btn-primary btn-m" style="width: 100%;">DESCARGAR CSV</a>
        </div>
    </div>

    <div class="taxonomy-footer v3-note-box">
        <h5 style="color: var(--text-main); font-weight: 800;">Condiciones de Reutilización</h5>
        <p style="font-size: 13px; margin-bottom: var(--space-3);">
            Estos datos se distribuyen bajo los términos de la Ley 37/2007 de reutilización de la información del sector
            público.
            Es obligatorio citar a <strong>OpenBorme</strong> como fuente de estructuración.
        </p>
        <p style="font-size: 13px; color: var(--text-muted);">
            Para acceso a datos históricos anteriores a 2020 o formatos personalizados, por favor contacte con soporte.
        </p>
    </div>
</div>