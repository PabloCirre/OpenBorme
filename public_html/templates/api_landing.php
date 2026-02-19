<div style="margin-bottom: 3rem; border-bottom: 2px solid var(--border-color); padding-bottom: 2rem;">
    <h1 style="font-size: 2rem; color: var(--boe-red);">API de Datos Abiertos</h1>
    <p style="color: var(--text-secondary); font-size: 1.1rem;">Acceso programático structured y de alta disponibilidad
        al BORME.</p>
</div>

<section style="margin-bottom: 4rem;">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
        <div class="card">
            <h3 style="margin-bottom: 1rem;">JSON Enriquecido</h3>
            <p style="font-size: 0.9rem; color: var(--text-secondary);">Recibe los actos del BORME procesados con
                inteligencia: CIF detectado, capital social, links externos y texto limpio.</p>
        </div>
        <div class="card">
            <h3 style="margin-bottom: 1rem;">Histórico 2020-2026</h3>
            <p style="font-size: 0.9rem; color: var(--text-secondary);">Consulta millones de registros históricos sin
                límites de velocidad agresivos y con trazabilidad al PDF original.</p>
        </div>
        <div class="card">
            <h3 style="margin-bottom: 1rem;">LLM Ready</h3>
            <p style="font-size: 0.9rem; color: var(--text-secondary);">Optimizado para ser consumido por agentes de IA
                y LLMs mediante esquemas semánticos estándar.</p>
        </div>
    </div>
</section>

<section>
    <h2 style="font-size: 1.5rem; margin-bottom: 2rem;">Documentación Técnica</h2>
    <div class="card" style="padding: 2.5rem; background: #222; color: #eee; font-family: monospace;">
        <p style="color: #0f0;">// GET /api/v1/act/{id}</p>
        <p>{</p>
        <p style="padding-left: 20px;">"id": "BORME-A-2026-1234",</p>
        <p style="padding-left: 20px;">"company": "TECH SOLUTIONS SL",</p>
        <p style="padding-left: 20px;">"cif": "B12345678",</p>
        <p style="padding-left: 20px;">"type": "CONSTITUCION",</p>
        <p style="padding-left: 20px;">"capital": 3000.00</p>
        <p>}</p>
    </div>
    <div style="margin-top: 2rem;">
        <a href="/docs/developer_guide.md" class="btn btn-outline">VER GUÍA COMPLETA</a>
    </div>
</section>