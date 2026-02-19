<?php include 'header.php'; ?>

<main class="container" style="padding: var(--space-8) var(--space-5);">
    <div style="text-align: center; max-width: 800px; margin: 0 auto var(--space-8);">
        <h1 style="margin-bottom: var(--space-4); letter-spacing: -0.03em;">Buscar en el BORME por empresa o texto</h1>
        <p class="meta" style="font-size: 18px; margin-bottom: var(--space-6);">Consulta rápida de actos registrales,
            nombramientos y anuncios legales.</p>

        <form action="/buscar" method="GET"
            style="display: flex; gap: var(--space-3); max-width: 600px; margin: 0 auto;">
            <input type="text" name="q" class="input-main" placeholder="Ej: Inditex, construcciones, B81234567..."
                style="flex: 1; font-size: 16px;">
            <button type="submit" class="btn btn-primary btn-l">BUSCAR</button>
        </form>

        <div
            style="margin-top: var(--space-5); display: flex; flex-wrap: wrap; justify-content: center; gap: var(--space-3);">
            <a href="/sumario" class="badge" style="padding: 6px 12px; text-decoration: none;">Boletín de Hoy</a>
            <a href="/buscar?date=yesterday" class="badge" style="padding: 6px 12px; text-decoration: none;">Ayer</a>
            <a href="/buscar?period=7days" class="badge" style="padding: 6px 12px; text-decoration: none;">Últimos 7
                días</a>
            <a href="/provincias" class="badge" style="padding: 6px 12px; text-decoration: none;">Por Provincias</a>
            <a href="/fuentes" class="badge" style="padding: 6px 12px; text-decoration: none;">Secciones</a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: var(--space-6);">
        <section class="card">
            <h3 style="margin-bottom: var(--space-3);">Qué es OpenBorme</h3>
            <p class="meta">
                Es una plataforma técnica de datos abiertos que estructura la información del <a
                    href="https://www.boe.es/diario_borme/" target="_blank">BORME</a> para facilitar su consulta
                profesional.
            </p>
        </section>

        <section class="card">
            <h3 style="margin-bottom: var(--space-3);">Fuente Oficial</h3>
            <p class="meta">
                OpenBorme reutiliza información de la Agencia Estatal BOE. Los datos aquí mostrados son una
                representación estructurada para consulta rápida.
            </p>
        </section>

        <section class="card">
            <h3 style="margin-bottom: var(--space-3);">Acceso para Desarrolladores</h3>
            <p class="meta">
                Descarga de datasets masivos y documentación de la API v1 para integraciones técnicas.
            </p>
            <div style="margin-top: var(--space-4);">
                <a href="/api" class="btn btn-ghost btn-s">Documentación API &rarr;</a>
            </div>
        </section>
    </div>
</main>

<?php include 'footer.php'; ?>