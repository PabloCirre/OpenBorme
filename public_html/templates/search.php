<?php
// search.php - Refined Professional Search
$db_path = file_exists(__DIR__ . '/../../pipeline/db/Database.php') ? __DIR__ . '/../../pipeline/db/Database.php' : __DIR__ . '/../pipeline/db/Database.php';
require_once $db_path;


$q = trim($_GET['q'] ?? '');
$province = strtoupper(trim($_GET['provincia'] ?? ''));
$section = strtoupper(trim($_GET['seccion'] ?? ''));
$type = trim($_GET['tipo'] ?? '');
$date_from = trim($_GET['desde'] ?? '');
$date_to = trim($_GET['hasta'] ?? '');
$sort = $_GET['orden'] ?? 'date_desc';
$page = max(1, (int) ($_GET['page'] ?? 1));
$page_size = 50;
$offset = ($page - 1) * $page_size;

$results = [];
$results_count = 0;
$total = 0;

if (strlen($q) >= 3 || $province || $section || $type || ($date_from && $date_to)) {
    try {
        $db = Database::getInstance();
        $conditions = [];
        $params = [];
        if ($q) {
            $conditions[] = "(company_name LIKE :q OR company_uid LIKE :q OR raw_text LIKE :q)";
            $params[':q'] = "%$q%";
        }
        if ($province) {
            $conditions[] = "province = :province";
            $params[':province'] = $province;
        }
        if ($section && in_array($section, ['A', 'B', 'C'])) {
            $conditions[] = "section = :section";
            $params[':section'] = $section;
        }
        if ($type) {
            $conditions[] = "type LIKE :type";
            $params[':type'] = "%$type%";
        }
        if ($date_from) {
            $conditions[] = "date >= :date_from";
            $params[':date_from'] = $date_from;
        }
        if ($date_to) {
            $conditions[] = "date <= :date_to";
            $params[':date_to'] = $date_to;
        }

        $where = $conditions ? ('WHERE ' . implode(' AND ', $conditions)) : '';
        $order = 'ORDER BY date DESC';
        if ($sort === 'date_asc') $order = 'ORDER BY date ASC';

        $count_stmt = $db->prepare("SELECT COUNT(*) as total FROM borme_acts $where");
        $count_stmt->execute($params);
        $total = (int) ($count_stmt->fetch()['total'] ?? 0);

        $stmt = $db->prepare("
            SELECT id, date, type, province, company_name, company_uid, capital, raw_text 
            FROM borme_acts 
            $where
            $order
            LIMIT :limit OFFSET :offset
        ");
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $page_size, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll();
        $results_count = count($results);
    } catch (Exception $e) {
        $results_count = 0;
    }
}
?>

<div class="container section-py">
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

                <form method="GET" action="/buscar">
                    <input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>">

                    <div class="filter-group">
                        <label class="meta filter-label">Rango de Fecha</label>
                        <div class="filter-input-split">
                            <input type="date" name="desde" value="<?= htmlspecialchars($date_from) ?>" class="input-filter" placeholder="Desde" style="width: 50%;">
                            <input type="date" name="hasta" value="<?= htmlspecialchars($date_to) ?>" class="input-filter" placeholder="Hasta" style="width: 50%;">
                        </div>
                    </div>

                    <div class="filter-group">
                        <label class="meta filter-label">Provincia</label>
                        <select class="input-filter" name="provincia">
                            <option value="">Todas las provincias</option>
                            <?php
                            $provincias = ["ALAVA","ALBACETE","ALICANTE","ALMERIA","AVILA","BADAJOZ","ILLES BALEARS","BARCELONA","BURGOS","CACERES","CADIZ","CASTELLON","CIUDAD REAL","CORDOBA","A CORUÑA","CUENCA","GIRONA","GRANADA","GUADALAJARA","GIPUZKOA","HUELVA","HUESCA","JAEN","LEON","LLEIDA","LA RIOJA","LUGO","MADRID","MALAGA","MURCIA","NAVARRA","OURENSE","ASTURIAS","PALENCIA","LAS PALMAS","PONTEVEDRA","SALAMANCA","SANTA CRUZ DE TENERIFE","CANTABRIA","SEGOVIA","SEVILLA","SORIA","TARRAGONA","TERUEL","TOLEDO","VALENCIA","VALLADOLID","BIZKAIA","ZAMORA","ZARAGOZA","CEUTA","MELILLA"];
                            foreach ($provincias as $prov) {
                                $selected = ($province === $prov) ? 'selected' : '';
                                echo "<option value=\"$prov\" $selected>$prov</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="meta filter-label">Sección</label>
                        <div style="display: flex; flex-direction: column; gap: var(--space-2); font-size: 14px;">
                            <label><input type="radio" name="seccion" value="" <?= $section === '' ? 'checked' : '' ?>> Todas</label>
                            <label><input type="radio" name="seccion" value="A" <?= $section === 'A' ? 'checked' : '' ?>> Sección I (Actos)</label>
                            <label><input type="radio" name="seccion" value="B" <?= $section === 'B' ? 'checked' : '' ?>> Sección II (Anuncios)</label>
                        </div>
                    </div>

                    <div class="filter-group">
                        <label class="meta filter-label">Tipo de Acto</label>
                        <select class="input-filter" name="tipo">
                            <option value="">Cualquier tipo</option>
                            <option value="CONSTITU" <?= stripos($type, 'CONSTIT') !== false ? 'selected' : '' ?>>Constitución</option>
                            <option value="NOMBR" <?= stripos($type, 'NOMBR') !== false ? 'selected' : '' ?>>Ceses/Nombramientos</option>
                            <option value="AUMENTO" <?= stripos($type, 'AUMENTO') !== false ? 'selected' : '' ?>>Aumento capital</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-secondary btn-m" style="width: 100%;">Aplicar filtros</button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="search-results-meta">
                <p class="meta"><?= number_format($total, 0, ',', '.') ?> actos encontrados</p>
                <div style="display: flex; align-items: center; gap: var(--space-3);">
                    <span class="meta" style="font-size: 12px;">Ordenar por:</span>
                    <select class="input-filter" name="orden" form="orderForm" style="width: auto; height: 32px; font-size: 12px;">
                        <option value="date_desc" <?= $sort === 'date_desc' ? 'selected' : '' ?>>Fecha (Más reciente)</option>
                        <option value="date_asc" <?= $sort === 'date_asc' ? 'selected' : '' ?>>Fecha (Más antiguo)</option>
                    </select>
                </div>
            </div>

            <form id="orderForm" method="GET" action="/buscar" style="display:none;">
                <input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>">
                <input type="hidden" name="provincia" value="<?= htmlspecialchars($province) ?>">
                <input type="hidden" name="seccion" value="<?= htmlspecialchars($section) ?>">
                <input type="hidden" name="tipo" value="<?= htmlspecialchars($type) ?>">
                <input type="hidden" name="desde" value="<?= htmlspecialchars($date_from) ?>">
                <input type="hidden" name="hasta" value="<?= htmlspecialchars($date_to) ?>">
                <input type="hidden" name="page" value="<?= $page ?>">
            </form>

            <div style="display: flex; flex-direction: column; gap: var(--space-4);">
                <?php if ($results_count === 0 && (strlen($q) >= 3 || $province || $section || $type)): ?>
                    <div class="card" style="padding: var(--space-4); text-align: center;">No se encontraron resultados para
                        "<?= htmlspecialchars($q) ?>".</div>
                <?php elseif (strlen($q) < 3 && !$province && !$section && !$type): ?>
                    <div class="card" style="padding: var(--space-4); text-align: center;">Por favor, introduce al menos 3
                        caracteres para buscar.</div>
                <?php endif; ?>

                <?php foreach ($results as $res): ?>
                    <?php
                    $doc_date = preg_replace('/[^0-9]/', '', (string) ($res['date'] ?? ''));
                    $doc_url = "/borme/doc/" . rawurlencode((string) $res['id']) . ($doc_date ? "?date=" . rawurlencode($doc_date) : "");
                    $share_url = "https://openborme.es" . $doc_url;
                    $excerpt = htmlspecialchars(mb_strimwidth((string) ($res['raw_text'] ?? ''), 0, 200, '...'));
                    if ($q !== '') {
                        $excerpt = preg_replace('/(' . preg_quote($q, '/') . ')/i', '<mark style="background: #fef08a;">$1</mark>', $excerpt);
                    }
                    ?>
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
                            <span class="badge"><?= date('d/m/Y', strtotime($res['date'])) ?></span>
                            <span class="badge badge-type-acto"><?= $res['type'] ?></span>
                        </div>

                        <p class="search-result-excerpt">
                            <?= $excerpt ?>
                        </p>

                        <div class="search-result-footer">
                            <a href="<?= $doc_url ?>" class="btn btn-ghost btn-s" style="padding-left: 0;">Ver
                                Documento</a>
                            <button class="btn btn-ghost btn-s"
                                onclick="navigator.clipboard.writeText('<?= htmlspecialchars($share_url, ENT_QUOTES) ?>')">Copiar
                                Enlace</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="margin-top: var(--space-8); text-align: center;">
                <?php if ($total > $page * $page_size): ?>
                    <a class="btn btn-secondary btn-m" href="<?php
                        $params = $_GET;
                        $params['page'] = $page + 1;
                        echo '/buscar?' . http_build_query($params);
                    ?>">CARGAR MÁS RESULTADOS</a>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>
