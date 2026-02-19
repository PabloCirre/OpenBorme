<?php
// search.php - Refined Professional Search
include 'header.php';

// Mock Search Logic
$q = $_GET['q'] ?? '';
$results_count = 1250;
$results = [
    [
        'id' => 'BORME-A-2026-3024',
        'title' => 'SOLUCIONES TECNOLÓGICAS SL',
        'details' => '...Constitución de la sociedad. Nombramiento de GARCÍA LÓPEZ MANUEL como administrador único...',
        'province' => 'MADRID',
        'section' => 'SECCIÓN I',
        'date' => '11/02/2026',
        'type' => 'CONSTITUCIÓN'
    ],
    [
        'id' => 'BORME-B-2026-5521',
        'title' => 'AGROEXPORTACIONES DEL SUR SA',
        'details' => '...Aumento de capital social de 50.000€ a 120.000€. Reelección de auditores externos...',
        'province' => 'SEVILLA',
        'section' => 'SECCIÓN I',
        'date' => '10/02/2026',
        'type' => 'AUMENTO CAPITAL'
    ]
];
?>

<div class="container" style="padding: var(--space-6) 0;">
    <div style="margin-bottom: var(--space-6);">
        <p class="meta">Resultados de búsqueda</p>
        <h1>Buscando "<?= htmlspecialchars($q) ?>"</h1>
    </div>

    <div class="results-layout">
        <!-- Sidebar Filters (Grid 3) -->
        <aside class="sidebar">
            <div class="card" style="padding: var(--space-4);">
                <div
                    style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-5);">
                    <h3 style="font-size: 14px; text-transform: uppercase; color: var(--text-muted);">Filtros</h3>
                    <a href="/buscar?q=<?= htmlspecialchars($q) ?>"
                        style="font-size: 12px; color: var(--accent); text-decoration: none;">Limpiar</a>
                </div>

                <div style="margin-bottom: var(--space-4);">
                    <label class="meta" style="display: block; margin-bottom: var(--space-2);">Rango de Fecha</label>
                    <div style="display: flex; gap: var(--space-2);">
                        <input type="text" class="input-filter" placeholder="Desde" style="width: 50%;">
                        <input type="text" class="input-filter" placeholder="Hasta" style="width: 50%;">
                    </div>
                </div>

                <div style="margin-bottom: var(--space-4);">
                    <label class="meta" style="display: block; margin-bottom: var(--space-2);">Provincia</label>
                    <select class="input-filter">
                        <option>Todas las provincias</option>
                        <option>MADRID</option>
                        <option>BARCELONA</option>
                        <option>SEVILLA</option>
                    </select>
                </div>

                <div style="margin-bottom: var(--space-4);">
                    <label class="meta" style="display: block; margin-bottom: var(--space-2);">Sección</label>
                    <div style="display: flex; flex-direction: column; gap: var(--space-2); font-size: 14px;">
                        <label><input type="checkbox" checked> Sección I (Actos)</label>
                        <label><input type="checkbox" checked> Sección II (Anuncios)</label>
                    </div>
                </div>

                <div style="margin-bottom: var(--space-4);">
                    <label class="meta" style="display: block; margin-bottom: var(--space-2);">Tipo de Acto</label>
                    <select class="input-filter">
                        <option>Cualquier tipo</option>
                        <option>CONSTITUCIÓN</option>
                        <option>CESES/NOMBRAMIENTOS</option>
                        <option>AUMENTO CAPITAL</option>
                    </select>
                </div>
            </div>
        </aside>

        <!-- Main Content (Grid 9) -->
        <main class="main-content">
            <div
                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-4);">
                <p class="meta"><?= number_format($results_count, 0, ',', '.') ?> actos encontrados</p>
                <div style="display: flex; align-items: center; gap: var(--space-3);">
                    <span class="meta" style="font-size: 12px;">Ordenar por:</span>
                    <select class="input-filter" style="width: auto; height: 32px; font-size: 12px;">
                        <option>Relevancia</option>
                        <option>Fecha (Más reciente)</option>
                        <option>Fecha (Más antiguo)</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: var(--space-4);">
                <?php foreach ($results as $res): ?>
                    <div class="card"
                        style="padding: var(--space-4); display: flex; flex-direction: column; gap: var(--space-2);">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <a href="/borme/doc/<?= $res['id'] ?>"
                                style="font-size: 18px; font-weight: 700; color: var(--text-primary); text-decoration: none;">
                                <?= htmlspecialchars($res['title']) ?>
                            </a>
                            <span class="mono" style="font-size: 11px; color: var(--text-muted);"><?= $res['id'] ?></span>
                        </div>

                        <div style="display: flex; gap: var(--space-2); flex-wrap: wrap;">
                            <span class="badge"><?= $res['section'] ?></span>
                            <span class="badge"><?= $res['province'] ?></span>
                            <span class="badge" style="background: var(--border-light);"><?= $res['date'] ?></span>
                            <span class="badge"
                                style="background: #e0f2fe; color: #0369a1; border: none;"><?= $res['type'] ?></span>
                        </div>

                        <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.5;">
                            ...<?= str_replace($q, '<mark style="background: #fef08a;">' . $q . '</mark>', htmlspecialchars($res['details'])) ?>...
                        </p>

                        <div
                            style="display: flex; gap: var(--space-4); margin-top: var(--space-2); padding-top: var(--space-2); border-top: 1px solid var(--border-light);">
                            <a href="/borme/doc/<?= $res['id'] ?>" class="btn btn-ghost btn-s" style="padding-left: 0;">Ver
                                Documento &rarr;</a>
                            <button class="btn btn-ghost btn-s"
                                onclick="navigator.clipboard.writeText('https://openborme.es/borme/doc/<?= $res['id'] ?>')">Copiar
                                Enlace</button>
                            <a href="/export?id=<?= $res['id'] ?>&format=json" class="btn btn-ghost btn-s">Exportar JSON</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="margin-top: var(--space-8); text-align: center;">
                <button class="btn btn-secondary btn-m">CARGAR MÁS RESULTADOS</button>
            </div>
        </main>
    </div>
</div>

<?php include 'footer.php'; ?>