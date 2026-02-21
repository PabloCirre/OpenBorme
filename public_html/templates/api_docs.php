<div class="container" style="padding: var(--space-6) 0;">
    <nav class="breadcrumbs">
        <a href="/">Inicio</a> /
        <span>Documentación API</span>
    </nav>

    <div class="v3-landing-header">
        <h1 class="taxonomy-title">API del BORME para Desarrolladores</h1>
        <p style="color: var(--text-muted); font-size: 1.15rem; max-width: 800px;">
            Guía técnica para la integración masiva y el acceso programático a eventos registrales de la industria
            española.
        </p>
    </div>

    <div class="borme-grid">
        <!-- Sidebar (Status & Limits) -->
        <aside class="grid-col-4">
            <div class="card" style="padding: var(--space-4); margin-bottom: var(--space-4);">
                <h4 class="meta" style="text-transform: uppercase; margin-bottom: var(--space-3);">Estado del Servicio
                </h4>
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: var(--space-4);">
                    <div style="width: 10px; height: 10px; border-radius: 50%; background: #10b981;"></div>
                    <span style="font-size: 14px; font-weight: 600;">Operativa v1.0</span>
                </div>

                <h4 class="meta" style="text-transform: uppercase; margin-bottom: var(--space-2);">Rate Limit</h4>
                <p style="font-size: 13px;">60 peticiones / minuto</p>
                <p class="meta" style="font-size: 11px; margin-top: 4px;">Público, sin registro.</p>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="grid-col-8">
            <section class="card" style="margin-bottom: var(--space-6);">
                <h2 style="font-size: 20px; margin-bottom: var(--space-3);">Búsqueda Programática</h2>
                <div style="display: flex; gap: var(--space-3); align-items: center; margin-bottom: var(--space-3);">
                    <span class="badge v3-api-badge">GET</span>
                    <code class="mono" style="font-weight: 700;">/api.php?action=search</code>
                </div>
                <p style="font-size: 14px; margin-bottom: var(--space-4);">Endpoint actualmente en desarrollo. Para
                    búsquedas masivas, recomendamos el uso de los volcados diarios.</p>
            </section>

            <section class="card" style="margin-bottom: var(--space-6);">
                <h2 style="font-size: 20px; margin-bottom: var(--space-3);">Detalle de un Acto</h2>
                <div style="display: flex; gap: var(--space-3); align-items: center; margin-bottom: var(--space-3);">
                    <span class="badge v3-api-badge">GET</span>
                    <code class="mono" style="font-weight: 700;">/api.php?action=get_act&id={ID}</code>
                </div>
                <p style="font-size: 14px; margin-bottom: var(--space-4);">Obtén el objeto JSON completo de un evento
                    individual identificado por su ID BORME (Hash MD5 o Identificador de Acto).</p>

                <div class="v3-code-block">
                    /api.php?action=get_act&id=BORME-A-2024-12-28-1234
                </div>
            </section>

            <div class="taxonomy-footer v3-note-box">
                <h5 style="color: var(--text-main); font-weight: 800;">Atribución Obligatoria</h5>
                <p style="font-size: 13px;">
                    El uso de esta API requiere la mención de <strong>OpenBorme</strong> como motor de estructuración y
                    al <strong>BOE</strong> como fuente oficial.
                    Para accesos comerciales o soporte premium, contacte con <a href="mailto:admin@openborme.es">nuestro
                        equipo</a>.
                </p>
            </div>
        </main>
    </div>
</div>