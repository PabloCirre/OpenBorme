<?php
// templates/manifiesto.php - The Technical Manifesto & UI Style Expo
?>

<div class="container section-py">
    <!-- Hero Section -->
    <header style="margin-bottom: var(--space-9);">
        <nav class="breadcrumbs" style="margin-bottom: var(--space-4);">
            <a href="/">Inicio</a> / <span>Manifiesto</span>
        </nav>
        <h1 class="v3-landing-title" style="font-size: 3.5rem; max-width: 900px;">
            El estándar de <span class="accent-malva">transparencia</span> para los datos societarios de España.
        </h1>
        <p class="hero-subtitle" style="max-width: 700px;">
            OpenBorme no es solo una base de datos; es una infraestructura diseñada para la precisión, la auditabilidad
            y la elegancia técnica.
        </p>
    </header>

    <!-- UI EXPO SECTION (The Gallery) -->
    <section class="expo-section">
        <h2 class="section-v3-title">UI Style Expo (V4 Linear)</h2>
        <p class="meta" style="margin-bottom: var(--space-6);">Nuestra capa visual está inspirada en la eficiencia y
            pulcritud de las herramientas de ingeniería modernas.</p>

        <div class="expo-grid">
            <!-- Window Component -->
            <div class="v4-window" style="padding: var(--space-6); min-height: 200px;">
                <h4 style="margin-bottom: 12px; color: var(--accent-linear);">Internal Window</h4>
                <p style="font-size: 14px; color: var(--text-muted);">Un contenedor con bordes sutiles de alta precisión
                    y profundidad mínima.</p>
                <div class="v4-inner-card" style="margin-top: 20px;">
                    <span class="badge v3-api-badge" style="margin-bottom: 8px;">INSIGHT</span>
                    <p style="font-size: 13px; font-weight: 500;">Dato estructurado de alta fidelidad.</p>
                </div>
            </div>

            <!-- Code Window -->
            <div class="v4-code-window">
                <div class="v4-window-controls">
                    <div class="v4-dot dot-r"></div>
                    <div class="v4-dot dot-y"></div>
                    <div class="v4-dot dot-g"></div>
                </div>
                <pre style="margin: 0;"><code>{
  "project": "OpenBorme",
  "version": "4.0.0",
  "engine": "Linear-V4",
  "status": "Production"
}</code></pre>
            </div>

            <!-- Tokens and Actions -->
            <div class="v4-inner-card" style="display: flex; flex-direction: column; gap: var(--space-4);">
                <h4 class="meta">Acciones y Tokens</h4>
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <button class="btn btn-primary btn-s"
                        style="background: var(--accent-linear); color: #000;">PRIMARY</button>
                    <button class="btn btn-secondary btn-s">GHOST</button>
                    <span class="badge-outline">TAG ELEMENT</span>
                    <span class="badge" style="background: #10b981; color: white; border: none;">ONLINE</span>
                </div>
                <div style="margin-top: 10px;">
                    <label class="meta" style="display: block; margin-bottom: 4px;">Search Interface</label>
                    <input type="text" class="header-search-input" placeholder="Cmd + K to search..." readonly>
                </div>
            </div>
        </div>
    </section>

    <!-- Manifesto Content -->
    <section style="margin-top: var(--space-9);">
        <div class="borme-grid">
            <div class="grid-col-8">
                <article class="static-article" style="font-size: 1.1rem; line-height: 1.7; color: var(--text-main);">
                    <h2 class="section-v3-title" style="margin-bottom: var(--space-6);">Principios Fundamentales</h2>

                    <h3 style="margin-top: var(--space-7);">1. Contrato de Datos</h3>
                    <p>OpenBorme garantiza esquemas estables y versionados. No solo mostramos información,
                        proporcionamos una estructura en la que las empresas y desarrolladores pueden confiar.</p>

                    <h3 style="margin-top: var(--space-7);">2. Reproducibilidad 100%</h3>
                    <p>Nuestra metodología de extracción es transparente. Desde la ingesta de XML oficiales hasta el
                        parsing de PDF, cada paso es auditable y reproducible.</p>

                    <h3 style="margin-top: var(--space-7);">3. Ética y Privacidad (RGPD)</h3>
                    <p>Respetamos la privacidad por diseño. Mantenemos inactivos los módulos de perfilado de personas y
                        enfocamos toda nuestra potencia en la transparencia societaria.</p>
                </article>
            </div>

            <aside class="grid-col-4">
                <div class="v3-legal-warning" style="margin-top: 0;">
                    <h4 style="margin-bottom: 12px;">Auditoría Técnica</h4>
                    <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 20px;">
                        El Core de OpenBorme está disponible para revisión técnica bajo licencia MIT.
                    </p>
                    <a href="https://github.com/PabloCirre/OpenBorme" class="btn btn-primary btn-m"
                        style="width: 100%;">REVISAR CÓDIGO</a>
                </div>
            </aside>
        </div>
    </section>
</div>