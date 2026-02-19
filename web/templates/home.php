<?php include 'header.php'; ?>

<main class="container" style="padding: var(--space-8) 0;">
    <div class="hero-section" style="text-align: center; max-width: 900px; margin: 0 auto var(--space-8);">
        <h1 style="margin-bottom: var(--space-3); color: var(--brand-dark);">Visualiza el Registro Mercantil <br>como
            nunca antes</h1>
        <p style="font-size: 1.25rem; margin-bottom: var(--space-6); color: var(--text-muted);">
            OpenBorme estructura, normaliza y democratiza el acceso a los datos societarios de España.
            Sin publicidad, sin rastreo, solo datos puros.
        </p>

        <form action="/buscar" method="GET" class="hero-search-form"
            style="display: flex; gap: var(--space-3); max-width: 680px; margin: 0 auto; background: white; padding: var(--space-3); border-radius: var(--radius-md); border: 2px solid var(--brand-primary); box-shadow: var(--shadow-md);">
            <input type="text" name="q" class="input-main" placeholder="Ej: Inditex, construcciones, B81234567..."
                style="flex: 1; border: none; font-size: 1.1rem; outline: none; padding-left: var(--space-4);">
            <button type="submit" class="btn btn-primary"
                style="height: 52px; padding: 0 var(--space-6); border-radius: var(--radius-sm);">BUSCAR</button>
        </form>

        <div
            style="margin-top: var(--space-5); display: flex; flex-wrap: wrap; justify-content: center; gap: var(--space-2);">
            <a href="/sumario" class="badge"
                style="padding: 8px 16px; border-radius: var(--radius-sm); border: 1px solid var(--border-strong); background: white;">Hoy</a>
            <a href="/buscar?date=yesterday" class="badge"
                style="padding: 8px 16px; border-radius: var(--radius-sm); border: 1px solid var(--border-strong); background: white;">Ayer</a>
            <a href="/provincias" class="badge"
                style="padding: 8px 16px; border-radius: var(--radius-sm); border: 1px solid var(--border-strong); background: white;">Provincias</a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: var(--space-6);">
        <section class="inst-card" style="padding: var(--space-6);">
            <div style="font-size: 2rem; margin-bottom: var(--space-3); color: var(--brand-primary);">🏗️</div>
            <h3 style="margin-bottom: var(--space-3); color: var(--brand-dark);">Ingeniería de Datos</h3>
            <p>
                No solo volcamos texto. OpenBorme normaliza entidades, detecta tipos de actos y vincula eventos
                para crear un historial coherente de cada empresa.
            </p>
        </section>

        <section class="inst-card" style="padding: var(--space-6);">
            <div style="font-size: 2rem; margin-bottom: var(--space-3); color: var(--brand-primary);">⚖️</div>
            <h3 style="margin-bottom: var(--space-3); color: var(--brand-dark);">Ética por Diseño</h3>
            <p>
                Respetamos estrictamente el RGPD. Limitamos la visibilidad de datos personales y evitamos el profiling
                masivo para centrarnos en transparencia corporativa.
            </p>
        </section>

        <section class="inst-card" style="padding: var(--space-6);">
            <div style="font-size: 2rem; margin-bottom: var(--space-3); color: var(--brand-primary);">🚀</div>
            <h3 style="margin-bottom: var(--space-3); color: var(--brand-dark);">API & Datasets</h3>
            <p>
                Construido para desarrolladores e IAs. Accede a dumps masivos en formatos eficientes o integra
                nuestra API en tus flujos de trabajo.
            </p>
            <div style="margin-top: var(--space-4);">
                <a href="/api" class="btn btn-secondary btn-s" style="border-radius: var(--radius-md);">Explorar API
                    &rarr;</a>
            </div>
        </section>
    </div>
</main>

<?php include 'footer.php'; ?>