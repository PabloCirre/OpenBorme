<footer
    style="margin-top: var(--space-8); border-top: 1px solid var(--border-dark); padding: var(--space-7) 0 var(--space-5);">
    <div class="container"
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: var(--space-6);">
        <div>
            <h4 style="font-size: 14px; color: var(--text-primary); margin-bottom: var(--space-4);">OpenBorme</h4>
            <nav style="display: flex; flex-direction: column; gap: 10px; font-size: 13px;">
                <a href="/metodologia" style="color: var(--text-secondary); text-decoration: none;">Metodología</a>
                <a href="/fuentes" style="color: var(--text-secondary); text-decoration: none;">Fuentes del BOE</a>
                <a href="/api" style="color: var(--text-secondary); text-decoration: none;">API v1</a>
            </nav>
        </div>
        <div>
            <h4 style="font-size: 14px; color: var(--text-primary); margin-bottom: var(--space-4);">Transparencia</h4>
            <nav style="display: flex; flex-direction: column; gap: 10px; font-size: 13px;">
                <a href="/aviso-legal" style="color: var(--text-secondary); text-decoration: none;">Aviso Legal</a>
                <a href="/privacidad" style="color: var(--text-secondary); text-decoration: none;">Privacidad</a>
                <a href="/descargas" style="color: var(--text-secondary); text-decoration: none;">Datos Abiertos</a>
            </nav>
        </div>
        <div>
            <h4 style="font-size: 14px; color: var(--text-primary); margin-bottom: var(--space-4);">Soporte</h4>
            <nav style="display: flex; flex-direction: column; gap: 10px; font-size: 13px;">
                <a href="/contacto" style="color: var(--text-secondary); text-decoration: none;">Contacto</a>
                <a href="https://github.com/pablocirre/openborme" target="_blank"
                    style="color: var(--text-secondary); text-decoration: none;">GitHub Repository</a>
                <a href="https://linkedin.com/in/pablocirre" target="_blank"
                    style="color: var(--text-secondary); text-decoration: none;">LinkedIn</a>
            </nav>
        </div>
        <div>
            <h4 style="font-size: 14px; color: var(--text-primary); margin-bottom: var(--space-4);">Legal</h4>
            <p style="font-size: 13px; color: var(--text-muted); line-height: 1.5;">
                OpenBorme no tiene vinculación oficial con el BOE ni el Registro Mercantil. Los datos se ofrecen con
                fines informativos y de consulta técnica.
            </p>
        </div>
    </div>

    <div class="container"
        style="margin-top: var(--space-6); padding-top: var(--space-4); border-top: 1px solid var(--border-light); display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: var(--text-muted);">
        <span>© <?= date('Y') ?> OpenBorme Project</span>
        <span>Fuentes oficiales: Agencia Estatal BOE</span>
    </div>
</footer>

<!-- Global Shortcuts & Scripts -->
<script>
    document.addEventListener('keydown', function (e) {
        // '/' to focus search
        if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
            e.preventDefault();
            document.getElementById('global-search')?.focus();
        }
        // 'f' to toggle filters (if applicable)
        if (e.key === 'f' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
            const sidebar = document.querySelector('.sidebar');
            if (sidebar) {
                sidebar.style.display = sidebar.style.display === 'none' ? 'block' : 'none';
            }
        }
        // 'Esc' to close
        if (e.key === 'Escape') {
            document.activeElement.blur();
        }
    });
</script>
</body>

</html>