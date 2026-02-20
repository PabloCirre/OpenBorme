session_start();
$is_local = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) || $_SERVER['SERVER_NAME'] == 'localhost';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | OpenBorme</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <style>
        .admin-card {
            background: white;
            border: 1px solid var(--border-color);
            padding: 3rem;
            max-width: 800px;
            margin: 4rem auto;
            border-top: 5px solid var(--boe-red);
        }

        .progress-section {
            display: none;
            margin-top: 2rem;
        }

        .progress-bar-container {
            width: 100%;
            height: 8px;
            background: #eee;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 1rem;
        }

        .progress-bar {
            width: 0%;
            height: 100%;
            background: var(--boe-red);
            transition: width 0.3s;
        }

        .log-container {
            background: #1a1a1a;
            color: #00ff00;
            padding: 1rem;
            height: 250px;
            overflow-y: auto;
            font-family: monospace;
            font-size: 0.85rem;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <header class="main-header">
        <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
            <a href="index.php" style="display: flex; align-items: center; gap: 8px;">
                <div style="background: var(--boe-red); color: white; padding: 2px 7px; font-weight: 800;">Open</div>
                <div style="font-weight: 800; color: var(--text-primary);">BORME Admin</div>
            </a>
            <div
                style="padding: 4px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; background: <?= $is_local ? '#e6ffea' : '#fff4e6' ?>; color: <?= $is_local ? '#1a7f37' : '#9a6700' ?>;">
                Entorno: <?= $is_local ? '💻 Local (Extractor)' : '🌐 Producción (Visionado)' ?>
            </div>
            <a href="index.php" style="font-size: 0.85rem; font-weight: 600;">Regresar a la Web</a>
        </div>
    </header>

    <main class="container">
        <div class="admin-card">
            <h1 style="margin-bottom: 1rem;">Panel de Control de Extracción</h1>
            <p style="color: var(--text-muted); margin-bottom: 2.5rem;">Gestiona la descarga y procesamiento de datos
                del BOE para alimentar la base de datos local.</p>

            <div class="controls"
                style="display: flex; gap: 1rem; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 2rem; margin-bottom: 2rem;">
                <label for="days" style="font-weight: 700; font-size: 0.9rem;">Extracción Últimos Días:</label>
                <input type="number" id="days" value="7" min="1" max="365"
                    style="padding: 0.5rem; border: 1px solid var(--border-color); width: 80px;">
                <button class="btn btn-primary" id="start-btn">Iniciar</button>
            </div>

            <div class="controls-history"
                style="background: #f0f7ff; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem; border: 1px solid #cce5ff;">
                <h3 style="color: #004085; font-size: 1rem; margin-bottom: 1rem;">🚀 Extracción de Alto Rendimiento
                    (Python)</h3>
                <p style="font-size: 0.85rem; color: #004085; margin-bottom: 1rem; line-height: 1.4;">
                    Para el <strong>Horizonte 2020 (25GB)</strong>, utiliza el motor de Python integrado. Es 10x más
                    rápido y estable que la interfaz web.
                </p>
                <div
                    style="background: #e1eefc; padding: 1rem; border-radius: 4px; font-family: monospace; font-size: 0.8rem; color: #004085; margin-bottom: 1rem;">
                    cd core/extractor<br>
                    python engine.py 2020-01-01 2020-12-31
                </div>
                <div style="display: flex; gap: 1rem; align-items: center; opacity: 0.6; pointer-events: none;">
                    <input type="date" id="start-date" value="2020-01-01"
                        style="padding: 0.5rem; border: 1px solid var(--border-color);">
                    <input type="date" id="end-date" value="2020-01-31"
                        style="padding: 0.5rem; border: 1px solid var(--border-color);">
                    <button class="btn" style="background: #004085; color: white;" id="history-btn">Cargar Rango (Web
                        Legacy)</button>
                </div>
                <p style="font-size: 0.7rem; color: #004085; mt: 0.5rem;">
                    * La carga vía web está limitada para evitar saturar el navegador. Usa Python para lotes grandes.
                </p>
            </div>

            <?php if (!$is_local): ?>
                <div
                    style="background: #fff4e5; border-left: 4px solid #ff9800; padding: 1.5rem; margin-bottom: 2rem; border-radius: 4px;">
                    <h4 style="color: #663c00; margin-bottom: 0.5rem;">⚠️ Estás en el Servidor de Producción</h4>
                    <p style="font-size: 0.85rem; color: #663c00; line-height: 1.4;">
                        Las extracciones masivas (Horizonte 2020) deben realizarse en <strong>Local</strong> para no agotar
                        el espacio en disco ni los recursos del servidor web. Luego utiliza el script
                        <code>sync_data.py</code> para subir solo los resultados procesados.
                    </p>
                </div>
            <?php endif; ?>

            <div class="progress-section" id="progress-section">
                <div class="progress-bar-container">
                    <div class="progress-bar" id="progress-bar"></div>
                </div>
                <div
                    style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 700; margin-bottom: 1rem;">
                    <span id="status-label">Preparando motores...</span>
                    <span id="progress-percent">0%</span>
                </div>
                <div class="log-container" id="log-container"></div>
            </div>

            <a href="#" id="download-btn" class="btn"
                style="display:none; width: 100%; margin-top: 2rem; border: 1px solid var(--boe-red);">DESCARGAR DATOS
                COMPLETOS</a>
        </div>
    </main>

    <script>
        const startBtn = document.getElementById('start-btn');
        const historyBtn = document.getElementById('history-btn');
        const daysInput = document.getElementById('days');
        const progressSection = document.getElementById('progress-section');
        const progressBar = document.getElementById('progress-bar');
        const progressPercent = document.getElementById('progress-percent');
        const statusLabel = document.getElementById('status-label');
        const logArea = document.getElementById('log-container');
        const downloadBtn = document.getElementById('download-btn');

        let isExtracting = false;

        function addLog(msg) {
            const div = document.createElement('div');
            div.style.marginBottom = '4px';
            div.innerText = `> ${msg}`;
            logArea.appendChild(div);
            logArea.scrollTop = logArea.scrollHeight;
        }

        async function pollStatus() {
            try {
                const res = await fetch('api.php?action=status');
                const data = await res.json();

                progressBar.style.width = `${data.progress}%`;
                progressPercent.innerText = `${data.progress}%`;
                statusLabel.innerText = data.current_task || 'Procesando...';

                logArea.innerHTML = '';
                data.logs.forEach(msg => addLog(msg));

                if (data.status === 'done') {
                    isExtracting = false;
                    statusLabel.innerText = 'PROCESO COMPLETADO';
                    startBtn.disabled = false;
                    historyBtn.disabled = false;
                    startBtn.innerText = 'Iniciar';
                    historyBtn.innerText = 'Cargar Rango';
                    downloadBtn.style.display = 'block';
                    return;
                }

                if (isExtracting) {
                    setTimeout(pollStatus, 1500);
                    // Trigger step processing
                    fetch('api.php?action=process_step');
                }
            } catch (e) {
                console.error("Polling error:", e);
                if (isExtracting) setTimeout(pollStatus, 3000);
            }
        }

        async function startExtraction(params = {}) {
            if (isExtracting) return;

            isExtracting = true;
            startBtn.disabled = true;
            historyBtn.disabled = true;

            progressSection.style.display = 'block';
            logArea.innerHTML = '<div style="color: #666;">Iniciando petición...</div>';

            let url = 'api.php?action=start';
            if (params.start) {
                url += `&start=${params.start}&end=${params.end}`;
                historyBtn.innerText = 'Procesando...';
            } else {
                url += `&days=${daysInput.value}`;
                startBtn.innerText = 'Procesando...';
            }

            try {
                const res = await fetch(url);
                const data = await res.json();
                if (data.status === 'started') {
                    pollStatus();
                } else {
                    alert("Error: " + data.message);
                    isExtracting = false;
                    startBtn.disabled = false;
                    historyBtn.disabled = false;
                }
            } catch (e) {
                alert("Error de conexión con la API");
                isExtracting = false;
                startBtn.disabled = false;
                historyBtn.disabled = false;
            }
        }

        startBtn.addEventListener('click', () => startExtraction());
        historyBtn.addEventListener('click', () => {
            const start = document.getElementById('start-date').value;
            const end = document.getElementById('end-date').value;
            startExtraction({ start, end });
        });
    </script>
</body>

</html>