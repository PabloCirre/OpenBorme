<?php
$db_path = file_exists(__DIR__ . '/../../pipeline/db/Database.php') ? __DIR__ . '/../../pipeline/db/Database.php' : __DIR__ . '/../pipeline/db/Database.php';
require_once $db_path;

$db = Database::getInstance();
$stmt = $db->prepare("SELECT DISTINCT province FROM borme_acts WHERE province IS NOT NULL AND province != '' ORDER BY province ASC");
$stmt->execute();
$provinces = $stmt->fetchAll(PDO::FETCH_COLUMN);

$provinceAliasMap = [];
foreach ($provinces as $provinceName) {
    $alias = strtoupper(strtr((string) $provinceName, [
        'Á' => 'A',
        'À' => 'A',
        'Ä' => 'A',
        'Â' => 'A',
        'É' => 'E',
        'È' => 'E',
        'Ë' => 'E',
        'Ê' => 'E',
        'Í' => 'I',
        'Ì' => 'I',
        'Ï' => 'I',
        'Î' => 'I',
        'Ó' => 'O',
        'Ò' => 'O',
        'Ö' => 'O',
        'Ô' => 'O',
        'Ú' => 'U',
        'Ù' => 'U',
        'Ü' => 'U',
        'Û' => 'U',
        'Ñ' => 'N',
    ]));
    $provinceAliasMap[$alias] = $provinceName;
}

$selectedProvince = strtoupper(trim((string) ($_GET['province'] ?? 'ALL')));
$selectedProvinceAlias = strtr($selectedProvince, ['-' => ' ']);
if ($selectedProvinceAlias !== 'ALL') {
    if (isset($provinceAliasMap[$selectedProvinceAlias])) {
        $selectedProvince = $provinceAliasMap[$selectedProvinceAlias];
    } elseif (!in_array($selectedProvince, $provinces, true)) {
        $selectedProvince = 'ALL';
    }
}
?>

<style>
    .newco-wrap {
        display: grid;
        gap: var(--space-5);
    }

    .newco-controls {
        display: flex;
        flex-wrap: wrap;
        gap: var(--space-3);
        align-items: center;
        justify-content: space-between;
        padding: var(--space-4);
        border: 1px solid var(--border-linear);
        border-radius: var(--radius-md);
        background: #fff;
    }

    .newco-kpis {
        display: grid;
        gap: var(--space-3);
        grid-template-columns: repeat(3, minmax(200px, 1fr));
    }

    .newco-kpi {
        border: 1px solid var(--border-linear);
        border-radius: var(--radius-md);
        padding: var(--space-4);
        background: #fff;
    }

    .newco-kpi h3 {
        margin: 0 0 8px 0;
        font-size: 14px;
        color: var(--text-muted);
    }

    .newco-kpi .kpi-main {
        font-size: 28px;
        font-weight: 700;
        color: var(--brand-dark);
    }

    .newco-kpi .kpi-sub {
        font-size: 13px;
        color: var(--text-muted);
    }

    .newco-panel {
        border: 1px solid var(--border-linear);
        border-radius: var(--radius-md);
        background: #fff;
        padding: var(--space-4);
    }

    .newco-chart svg {
        width: 100%;
        height: 260px;
        display: block;
        background: linear-gradient(180deg, rgba(0, 0, 0, 0.02), rgba(0, 0, 0, 0));
        border-radius: var(--radius-sm);
    }

    .newco-grid {
        display: grid;
        gap: var(--space-4);
        grid-template-columns: 1fr;
    }

    .newco-table-wrap {
        overflow-x: auto;
    }

    .newco-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    .newco-table th,
    .newco-table td {
        text-align: left;
        padding: 10px 8px;
        border-bottom: 1px solid var(--border-linear);
        white-space: nowrap;
    }

    .newco-export {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    @media (max-width: 900px) {
        .newco-kpis {
            grid-template-columns: 1fr;
        }
    }
</style>

<main class="container section-py">
    <div class="newco-wrap">
        <section>
            <h1 style="margin-bottom: 8px;">Nuevas Empresas y Disoluciones</h1>
            <p style="color: var(--text-muted); margin: 0;">
                Seguimiento por provincia desde 2020 de constituciones y disoluciones societarias.
            </p>
        </section>

        <section class="newco-controls">
            <form id="province-form" method="GET" action="/nuevas-empresas" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <label for="province-select"><strong>Provincia:</strong></label>
                <select id="province-select" name="province" class="form-input">
                    <option value="ALL" <?= $selectedProvince === 'ALL' ? 'selected' : '' ?>>Todas</option>
                    <?php foreach ($provinces as $province): ?>
                        <option value="<?= htmlspecialchars($province) ?>" <?= $selectedProvince === $province ? 'selected' : '' ?>>
                            <?= htmlspecialchars($province) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary btn-s">Aplicar</button>
            </form>

            <div class="newco-export" id="export-links"></div>
        </section>

        <section class="newco-kpis" id="kpi-cards"></section>

        <section class="newco-panel newco-chart">
            <h2 style="margin-top:0;">Evolución mensual (altas vs disoluciones)</h2>
            <svg id="newco-chart-svg" viewBox="0 0 900 260" aria-label="Gráfico de evolución"></svg>
        </section>

        <section class="newco-grid" id="period-sections"></section>
    </div>
</main>

<script>
    (function () {
        const province = <?= json_encode($selectedProvince, JSON_UNESCAPED_UNICODE) ?>;
        const periods = ['week', 'month', 'year'];
        const periodLabel = {week: 'Esta semana', month: 'Este mes', year: 'Este año'};

        function esc(v) {
            return String(v ?? '').replace(/[&<>"']/g, (c) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            }[c]));
        }

        function exportUrl(mode, period, format) {
            return `/api.php?action=export_new_companies&province=${encodeURIComponent(province)}&mode=${encodeURIComponent(mode)}&period=${encodeURIComponent(period)}&format=${encodeURIComponent(format)}`;
        }

        function renderExports() {
            const holder = document.getElementById('export-links');
            const blocks = periods.map((period) => {
                return `
                    <a class="btn btn-ghost btn-s" href="${exportUrl('creation', period, 'csv')}">CSV ${periodLabel[period]}</a>
                    <a class="btn btn-ghost btn-s" href="${exportUrl('creation', period, 'excel')}">Excel ${periodLabel[period]}</a>
                `;
            }).join('');
            holder.innerHTML = blocks;
        }

        function renderKpis(snapshot) {
            const container = document.getElementById('kpi-cards');
            container.innerHTML = periods.map((period) => {
                const row = snapshot.periods[period] || {};
                return `
                    <article class="newco-kpi">
                        <h3>${periodLabel[period]}</h3>
                        <div class="kpi-main">${Number(row.creations || 0).toLocaleString('es-ES')}</div>
                        <div class="kpi-sub">Nuevas empresas</div>
                        <div class="kpi-sub">Disoluciones: ${Number(row.dissolutions || 0).toLocaleString('es-ES')} | Neto: ${Number(row.net || 0).toLocaleString('es-ES')}</div>
                    </article>
                `;
            }).join('');
        }

        function renderPeriodTables(snapshot) {
            const root = document.getElementById('period-sections');
            root.innerHTML = periods.map((period) => {
                const data = snapshot.periods[period] || {};
                const rows = data.new_companies || [];
                const disRows = data.dissolved_companies || [];

                const newRowsHtml = rows.length ? rows.map((r) => `
                    <tr>
                        <td>${esc(r.date)}</td>
                        <td>${esc(r.company_name)}</td>
                        <td>${esc(r.company_uid || '')}</td>
                        <td>${esc(r.province)}</td>
                        <td>${esc(r.normalized_type || r.type || '')}</td>
                    </tr>
                `).join('') : `<tr><td colspan="5">Sin nuevas empresas en este periodo.</td></tr>`;

                const disRowsHtml = disRows.length ? disRows.slice(0, 50).map((r) => `
                    <tr>
                        <td>${esc(r.date)}</td>
                        <td>${esc(r.company_name)}</td>
                        <td>${esc(r.company_uid || '')}</td>
                        <td>${esc(r.province)}</td>
                        <td>${esc(r.normalized_type || r.type || '')}</td>
                    </tr>
                `).join('') : `<tr><td colspan="5">Sin disoluciones en este periodo.</td></tr>`;

                return `
                    <section class="newco-panel">
                        <h2 style="margin-top:0;">${periodLabel[period]}</h2>
                        <div class="newco-table-wrap">
                            <h3 style="margin:0 0 8px 0;">Altas</h3>
                            <table class="newco-table">
                                <thead>
                                    <tr><th>Fecha</th><th>Empresa</th><th>CIF</th><th>Provincia</th><th>Tipo</th></tr>
                                </thead>
                                <tbody>${newRowsHtml}</tbody>
                            </table>
                        </div>
                        <div class="newco-table-wrap" style="margin-top:14px;">
                            <h3 style="margin:0 0 8px 0;">Disoluciones (top 50)</h3>
                            <table class="newco-table">
                                <thead>
                                    <tr><th>Fecha</th><th>Empresa</th><th>CIF</th><th>Provincia</th><th>Tipo</th></tr>
                                </thead>
                                <tbody>${disRowsHtml}</tbody>
                            </table>
                        </div>
                    </section>
                `;
            }).join('');
        }

        function renderChart(series) {
            const svg = document.getElementById('newco-chart-svg');
            const width = 900;
            const height = 260;
            const padLeft = 44;
            const padRight = 20;
            const padTop = 16;
            const padBottom = 28;
            const chartW = width - padLeft - padRight;
            const chartH = height - padTop - padBottom;
            const points = Array.isArray(series) ? series : [];
            if (!points.length) {
                svg.innerHTML = `<text x="20" y="40" fill="#777" font-size="14">Sin datos para la provincia seleccionada.</text>`;
                return;
            }

            const maxY = Math.max(1, ...points.map(p => Math.max(Number(p.creations || 0), Number(p.dissolutions || 0))));
            const xStep = points.length > 1 ? chartW / (points.length - 1) : 0;
            const yScale = chartH / maxY;

            const toPoint = (i, value) => {
                const x = padLeft + i * xStep;
                const y = padTop + chartH - (Number(value || 0) * yScale);
                return `${x.toFixed(2)},${y.toFixed(2)}`;
            };

            const crePath = points.map((p, i) => toPoint(i, p.creations)).join(' ');
            const disPath = points.map((p, i) => toPoint(i, p.dissolutions)).join(' ');

            const yTicks = [0, Math.round(maxY * 0.25), Math.round(maxY * 0.5), Math.round(maxY * 0.75), maxY];
            const yGrid = yTicks.map((v) => {
                const y = padTop + chartH - (v * yScale);
                return `<line x1="${padLeft}" y1="${y}" x2="${width - padRight}" y2="${y}" stroke="rgba(0,0,0,0.1)" stroke-width="1"/>`;
            }).join('');

            const xLabels = [0, Math.floor(points.length / 2), points.length - 1]
                .filter((v, i, arr) => arr.indexOf(v) === i)
                .map((idx) => {
                    const x = padLeft + idx * xStep;
                    const label = points[idx]?.bucket || '';
                    return `<text x="${x}" y="${height - 8}" font-size="12" fill="#666" text-anchor="middle">${esc(label)}</text>`;
                }).join('');

            svg.innerHTML = `
                ${yGrid}
                <polyline fill="none" stroke="#0b7285" stroke-width="2.5" points="${crePath}"></polyline>
                <polyline fill="none" stroke="#b02a37" stroke-width="2.5" points="${disPath}"></polyline>
                <text x="${padLeft}" y="14" font-size="12" fill="#0b7285">Altas</text>
                <text x="${padLeft + 70}" y="14" font-size="12" fill="#b02a37">Disoluciones</text>
                ${xLabels}
            `;
        }

        async function load() {
            renderExports();
            const [statsRes, snapshotRes] = await Promise.all([
                fetch(`/api.php?action=new_companies_stats&province=${encodeURIComponent(province)}&granularity=month&from=20200101`),
                fetch(`/api.php?action=new_companies_snapshot&province=${encodeURIComponent(province)}&limit=250`)
            ]);

            const stats = await statsRes.json();
            const snapshot = await snapshotRes.json();

            renderKpis(snapshot);
            renderPeriodTables(snapshot);
            renderChart(stats.series || []);
        }

        load().catch((err) => {
            const panel = document.getElementById('period-sections');
            panel.innerHTML = `<section class="newco-panel"><p>Error cargando métricas: ${esc(err.message || err)}</p></section>`;
        });
    })();
</script>
