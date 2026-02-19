<div style="margin-bottom: 3rem; border-bottom: 2px solid var(--border-color); padding-bottom: 2rem;">
    <h1 style="font-size: 2rem; color: var(--boe-red);">
        <?= $page_title ?>
    </h1>
    <p style="color: var(--text-secondary); font-size: 1.1rem; max-width: 800px;">
        Accede a toda la información pública del Registro Mercantil categorizada por
        <?= strtolower($page_title) ?>.
    </p>
</div>

<div style="display: grid; grid-template-columns: 1fr 300px; gap: 3rem;">
    <section>
        <div class="card" style="padding: 2.5rem; line-height: 1.8;">
            <h2 style="font-size: 1.4rem; margin-bottom: 1.5rem;">Explora los últimos actos</h2>
            <p>Esta sección recopila todos los eventos publicados en el BORME bajo la categoría de <strong>
                    <?= strtolower($page_title) ?>
                </strong>.</p>
            <p style="margin-top: 1rem;">Puedes filtrar por fecha o provincia para encontrar exactamente lo que buscas
                en nuestro histórico desde 2020.</p>

            <div
                style="margin-top: 3rem; background: var(--bg-light); padding: 2rem; border-radius: 4px; text-align: center;">
                <p style="margin-bottom: 1.5rem;">¿Buscas una empresa específica?</p>
                <a href="/buscar" class="btn btn-primary">IR AL BUSCADOR GLOBAL</a>
            </div>
        </div>
    </section>

    <aside>
        <div class="card">
            <h3 style="font-size: 1rem; margin-bottom: 1.5rem; text-transform: uppercase; color: var(--boe-red);">
                Relacionados</h3>
            <ul style="list-style: none; padding: 0; line-height: 2.5; font-size: 0.9rem;">
                <li><a href="/provincias">Búsqueda por Provincia</a></li>
                <li><a href="/tipos-de-actos">Tipos de Actos</a></li>
                <li><a href="/diario_borme/">Últimos Diarios</a></li>
            </ul>
        </div>
    </aside>
</div>