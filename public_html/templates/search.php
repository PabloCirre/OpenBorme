<?php
// search.php - Refined Professional Search
$db_path = file_exists(__DIR__ . '/../../pipeline/db/Database.php') ? __DIR__ . '/../../pipeline/db/Database.php' : __DIR__ . '/../pipeline/db/Database.php';
require_once $db_path;


$q = trim($_GET['q'] ?? '');
$results = [];
$results_count = 0;

if (strlen($q) >= 3) {
    try {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT id, date, type, province, company_name, company_uid, capital, raw_text 
            FROM borme_acts 
            WHERE company_name LIKE :q 
               OR company_uid LIKE :q 
               OR raw_text LIKE :q
            ORDER BY date DESC LIMIT 100
        ");
        $stmt->execute([':q' => "%$q%"]);
        $results = $stmt->fetchAll();
        $results_count = count($results);
    } catch (Exception $e) {
        $results_count = 0;
    }
}
?>

<div class="container" style="padding: var(--space-6) 0;">
    <div class="search-header">
        <p class="meta">Resultados de búsqueda</p>
        <h1>Buscando "<?= htmlspecialchars($q) ?>"</h1>
    </div>

    <div class="results-layout">
        <!-- Sidebar Filters -->
        <aside class="sidebar">
            <div class="card search-filters-card">
                <div
                    style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-5);">
                    <h3 class="meta" style="text-transform: uppercase;">Filtros</h3>
                    <a href="/buscar?q=<?= htmlspecialchars($q) ?>"
                        style="font-size: 12px; color: var(--accent); text-decoration: none;">Limpiar</a>
                </div>

                <div class="filter-group">
                    <label class="meta filter-label">Rango de Fecha</label>
                    <div class="filter-input-split">
                        <input type="text" class="input-filter" placeholder="Desde" style="width: 50%;">
                        <input type="text" class="input-filter" placeholder="Hasta" style="width: 50%;">
                    </div>
                </div>

                <div class="filter-group">
                    <label class="meta filter-label">Provincia</label>
                    <select class="input-filter">
                        <option>Todas las provincias</option>
                        <option>MADRID</option>
                        <option>BARCELONA</option>
                        <option>SEVILLA</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="meta filter-label">Sección</label>
                    <div style="display: flex; flex-direction: column; gap: var(--space-2); font-size: 14px;">
                        <label><input type="checkbox" checked> Sección I (Actos)</label>
                        <label><input type="checkbox" checked> Sección II (Anuncios)</label>
                    </div>
                </div>

                <div class="filter-group">
                    <label class="meta filter-label">Tipo de Acto</label>
                    <select class="input-filter">
                        <option>Cualquier tipo</option>
                        <option>CONSTITUCIÓN</option>
                        <option>CESES/NOMBRAMIENTOS</option>
                        <option>AUMENTO CAPITAL</option>
                    </select>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="search-results-meta">
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
                <?php if ($results_count === 0 && strlen($q) >= 3): ?>
                    <div class="card" style="padding: var(--space-4); text-align: center;">No se encontraron resultados para
                        "<?= htmlspecialchars($q) ?>".</div>
                <?php elseif (strlen($q) < 3): ?>
                    <div class="card" style="padding: var(--space-4); text-align: center;">Por favor, introduce al menos 3
                        caracteres para buscar.</div>
                <?php endif; ?>

                <?php foreach ($results as $res): ?>
                    <div class="card search-result-card">
                        <div class="search-result-header">
                            <a href="/empresa/<?= preg_replace('/[^a-z0-9]+/i', '-', strtolower($res['company_name'])) ?>"
                                class="search-result-title">
                                <?= htmlspecialchars($res['company_name']) ?>
                            </a>
                            <span class="mono" style="font-size: 11px; color: var(--text-muted);"><?= $res['id'] ?></span>
                        </div>

                        <div class="search-result-tags">
                            <span class="badge"><?= $res['province'] ?></span>
                            <span class="badge"
                                style="background: var(--border-light);"><?= date('d/m/Y', strtotime($res['date'])) ?></span>
                            <span class="badge"
                                style="background: #e0f2fe; color: #0369a1; border: none;"><?= $res['type'] ?></span>
                        </div>

                        <p class="search-result-excerpt">
                            <?= preg_replace('/(' . preg_quote($q, '/') . ')/i', '<mark style="background: #fef08a;">$1</mark>', htmlspecialchars(mb_strimwidth($res['raw_text'], 0, 200, '...'))) ?>
                        </p>

                        <div class="search-result-footer">
                            <a href="/borme/doc/<?= $res['id'] ?>" class="btn btn-ghost btn-s" style="padding-left: 0;">Ver
                                Documento &rarr;</a>
                            <button class="btn btn-ghost btn-s"
                                onclick="navigator.clipboard.writeText('https://openborme.es/borme/doc/<?= $res['id'] ?>')">Copiar
                                Enlace</button>
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