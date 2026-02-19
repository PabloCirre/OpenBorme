<div style="margin-bottom: 3rem; border-bottom: 2px solid var(--border-color); padding-bottom: 2rem;">
    <h1 style="font-size: 2rem; color: var(--boe-red);">Búsqueda Avanzada</h1>
    <p style="color: var(--text-secondary);">Refina tu consulta en el histórico del Registro Mercantil con filtros
        precisos.</p>
</div>

<form action="/resultados" method="GET" class="card" style="padding: 3rem; background: var(--bg-light);">
    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-bottom: 3rem;">
        <div>
            <label style="display: block; font-weight: 700; margin-bottom: 0.75rem;">Texto o Empresa</label>
            <input type="text" name="q" placeholder="Ej: Mercadona, B12345678..."
                style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px;">
        </div>
        <div>
            <label style="display: block; font-weight: 700; margin-bottom: 0.75rem;">Provincia</label>
            <select name="provincia"
                style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px;">
                <option value="">Todas las provincias</option>
                <option value="madrid">Madrid</option>
                <option value="barcelona">Barcelona</option>
                <option value="valencia">Valencia</option>
                <!-- More options will be populated dynamically -->
            </select>
        </div>
        <div>
            <label style="display: block; font-weight: 700; margin-bottom: 0.75rem;">Desde fecha</label>
            <input type="date" name="desde"
                style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px;">
        </div>
        <div>
            <label style="display: block; font-weight: 700; margin-bottom: 0.75rem;">Hasta fecha</label>
            <input type="date" name="hasta"
                style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px;">
        </div>
    </div>

    <div
        style="border-top: 1px solid var(--border-color); padding-top: 2rem; display: flex; justify-content: space-between; align-items: center;">
        <div style="font-size: 0.9rem; color: var(--text-muted);">
            <input type="checkbox" id="solo_inscritos"> <label for="solo_inscritos">Solo actos inscritos (Sección
                I)</label>
        </div>
        <button type="submit" class="btn btn-primary" style="padding: 0 3rem;">EJECUTAR BÚSQUEDA</button>
    </div>
</form>

<div
    style="margin-top: 4rem; padding: 2rem; border: 1px dashed var(--border-color); text-align: center; color: var(--text-muted);">
    <p>¿Necesitas exportaciones masivas o acceso vía API?</p>
    <div style="margin-top: 1rem; display: flex; gap: 15px; justify-content: center;">
        <a href="/api" style="font-weight: 700;">Documentación API</a>
        <span>|</span>
        <a href="/descargas" style="font-weight: 700;">Datasets Completos</a>
    </div>
</div>