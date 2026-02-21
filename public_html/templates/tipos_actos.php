<div class="container" style="padding: var(--space-7) 0;">
    <nav class="breadcrumbs" style="margin-bottom: var(--space-6);">
        <a href="/">Inicio</a> /
        <span>Modelo de Datos</span>
    </nav>

    <div class="taxonomy-header">
        <h1 class="taxonomy-title">Modelo de Datos & Actos</h1>
        <p style="color: var(--text-muted); font-size: 1.15rem; max-width: 800px;">
            Diccionario técnico de los anuncios y eventos mercantiles estructurados por el motor de OpenBorme.
        </p>
    </div>

    <div class="taxonomy-grid">
        <!-- Column 1 -->
        <div class="grid-col-4">
            <section class="card taxonomy-section">
                <h3 class="taxonomy-section-title">
                    <span style="font-size: 1.5rem;">🏗️</span> Constitución
                </h3>
                <ul class="taxonomy-list">
                    <li><a href="/tipo/constitucion" class="toc-link">Constitución de Sociedad</a></li>
                    <li><a href="/tipo/ampliacion-capital" class="toc-link">Ampliación de Capital</a></li>
                    <li><a href="/tipo/reduccion-capital" class="toc-link">Reducción de Capital</a></li>
                    <li><a href="/tipo/desembolso-dividendos" class="toc-link">Dividendo Pasivo</a></li>
                </ul>
            </section>
        </div>

        <!-- Column 2 -->
        <div class="grid-col-4">
            <section class="card taxonomy-section">
                <h3 class="taxonomy-section-title">
                    <span style="font-size: 1.5rem;">👔</span> Administración
                </h3>
                <ul class="taxonomy-list">
                    <li><a href="/tipo/nombramientos" class="toc-link">Nombramientos</a></li>
                    <li><a href="/tipo/ceses-dimisiones" class="toc-link">Ceses / Dimisiones</a></li>
                    <li><a href="/tipo/releccion" class="toc-link">Reelecciones</a></li>
                    <li><a href="/tipo/poderes" class="toc-link">Poderes y Revocaciones</a></li>
                </ul>
            </section>
        </div>

        <!-- Column 3 -->
        <div class="grid-col-4">
            <section class="card taxonomy-section">
                <h3 class="taxonomy-section-title">
                    <span style="font-size: 1.5rem;">🛑</span> Disolución
                </h3>
                <ul class="taxonomy-list">
                    <li><a href="/tipo/cambio-domicilio" class="toc-link">Cambio de Domicilio Social</a></li>
                    <li><a href="/tipo/cambio-objeto-social" class="toc-link">Cambio de Objeto Social</a></li>
                    <li><a href="/tipo/disolucion" class="toc-link">Disolución de Sociedad</a></li>
                    <li><a href="/tipo/extincion" class="toc-link">Extinción</a></li>
                </ul>
            </section>
        </div>
    </div>

    <!-- Technical Note -->
    <div class="taxonomy-footer v3-note-box">
        <h5 style="color: var(--text-main); font-weight: 800;">Normalización Técnica</h5>
        <p style="font-size: 0.9rem; color: var(--text-muted); line-height: 1.6;">
            Cada tipo de acto es detectado automáticamente por nuestro motor de etiquetado semántico.
            Utilizamos un diccionario de más de 200 variaciones terminológicas para agrupar los actos en estas
            categorías principales,
            asegurando una búsqueda coherente independientemente de la provincia del Registro Mercantil.
        </p>
    </div>
</div>