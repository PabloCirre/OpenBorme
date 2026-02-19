<article class="static-content" style="max-width: 800px; margin: 0 auto; line-height: 1.8;">
    <h1
        style="color: var(--boe-red); margin-bottom: 2rem; border-bottom: 3px solid var(--boe-red); padding-bottom: 0.5rem;">
        Metodología de Extracción</h1>

    <p style="font-size: 1.1rem; margin-bottom: 2rem;">OpenBorme utiliza un pipeline de procesamiento avanzado para
        transformar los boletines oficiales en datos útiles y legibles.</p>

    <section style="margin-bottom: 3rem;">
        <h2 style="font-size: 1.4rem; margin-bottom: 1rem; color: var(--text-primary);">1. Ingesta de Datos</h2>
        <p>Cada día, nuestro sistema se conecta a la API de datos abiertos de la Agencia Estatal BOE para obtener el
            sumario del Boletín Oficial del Registro Mercantil. Descargamos tanto los sumarios en XML como los
            documentos individuales en PDF y XML (Secciones I y II).</p>
    </section>

    <section style="margin-bottom: 3rem;">
        <h2 style="font-size: 1.4rem; margin-bottom: 1rem; color: var(--text-primary);">2. Parsing y Estructuración</h2>
        <p>Utilizamos dos motores especializados:</p>
        <ul style="margin-left: 2rem; margin-top: 1rem;">
            <li><strong>Motor PDF (Sección I)</strong>: Extrae texto plano de los boletines provinciales y utiliza
                expresiones regulares para identificar actos inscritos, empresas y CIFs.</li>
            <li><strong>Motor XML (Sección II)</strong>: Procesa los anuncios legales estructurados directamente desde
                la fuente XML oficial.</li>
        </ul>
    </section>

    <section style="margin-bottom: 3rem;">
        <h2 style="font-size: 1.4rem; margin-bottom: 1rem; color: var(--text-primary);">3. Normalización</h2>
        <p>Los datos extraídos se normalizan para corregir errores comunes en la fuente original (como formatos de CIF
            inconsistentes) y se preparan para su indexación en nuestra base de datos derivada.</p>
    </section>

    <div style="background: var(--bg-light); padding: 2rem; border-left: 5px solid var(--boe-red); margin-top: 4rem;">
        <p style="font-size: 0.9rem; font-style: italic;">Nota: Todo este proceso se realiza de forma automatizada y con
            trazabilidad completa a los documentos originales del BOE.</p>
    </div>
</article>