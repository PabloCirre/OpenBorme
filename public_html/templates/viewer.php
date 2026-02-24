<?php
// viewer.php - Mixed viewer for official BOE docs and local DB acts
require_once __DIR__ . '/../lib/BormeParser.php';

$db_path = file_exists(__DIR__ . '/../../pipeline/db/Database.php')
    ? __DIR__ . '/../../pipeline/db/Database.php'
    : __DIR__ . '/../pipeline/db/Database.php';
if (file_exists($db_path)) {
    require_once $db_path;
}

$id = trim((string) ($_GET['id'] ?? ''));
$date_param = (string) ($_GET['date'] ?? '');

if ($id === '') {
    echo "<div class='container' style='padding: 4rem;'><div class='card'><h3>Error: parámetro ID no proporcionado.</h3></div></div>";
    return;
}

$is_boe_document = (bool) preg_match('/^BORME-[A-Z]-\d{4}-[\d-]+$/', $id);
$acts = [];
$error_msg = '';
$not_found = false;
$pdf_url = '';
$province_name = 'UNKNOWN';
$year = '';
$month = '';
$day = '';
$formatted_date = '';

if (!$is_boe_document) {
    // Local DB act (e.g. PONTEVEDRA-92795)
    if (!class_exists('Database')) {
        echo "<div class='container' style='padding: 4rem;'><div class='card'><h3>Error: no hay acceso a la base de datos local.</h3></div></div>";
        return;
    }

    try {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT id, legacy_id, date, province, company_name, company_uid, capital, raw_text FROM borme_acts WHERE id = :id OR hash_md5 = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) {
            $normalized_date = preg_replace('/\D+/', '', $date_param);
            if (strlen($normalized_date) === 8) {
                $stmt = $db->prepare('SELECT id, legacy_id, date, province, company_name, company_uid, capital, raw_text FROM borme_acts WHERE legacy_id = :legacy_id AND date = :date LIMIT 1');
                $stmt->execute([':legacy_id' => $id, ':date' => $normalized_date]);
            } else {
                $stmt = $db->prepare('SELECT id, legacy_id, date, province, company_name, company_uid, capital, raw_text FROM borme_acts WHERE legacy_id = :legacy_id ORDER BY date DESC LIMIT 1');
                $stmt->execute([':legacy_id' => $id]);
            }
            $row = $stmt->fetch();
        }
    } catch (Exception $e) {
        $row = false;
    }

    if (!$row) {
        echo "<div class='container' style='padding: 4rem;'><div class='card'><h3>Error: acto no encontrado en base de datos local.</h3></div></div>";
        return;
    }

    $date_param = preg_replace('/[^0-9]/', '', (string) ($row['date'] ?? ''));
    if (strlen($date_param) !== 8) {
        $date_param = date('Ymd');
    }

    $year = substr($date_param, 0, 4);
    $month = substr($date_param, 4, 2);
    $day = substr($date_param, 6, 2);
    $formatted_date = "$day/$month/$year";

    $province_name = (string) ($row['province'] ?? 'UNKNOWN');
    $acts[] = [
        'id' => (string) ($row['legacy_id'] ?? $row['id'] ?? $id),
        'company' => (string) ($row['company_name'] ?? 'UNKNOWN'),
        'details' => (string) ($row['raw_text'] ?? ''),
        'cif' => (string) ($row['company_uid'] ?? ''),
        'capital' => (string) ($row['capital'] ?? '')
    ];
} else {
    // Official BOE document (BORME-*)
    if ($date_param === '' && class_exists('Database')) {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare('SELECT date FROM borme_acts WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch();
            if ($row && !empty($row['date'])) {
                $date_param = (string) $row['date'];
            }
        } catch (Exception $e) {
            // Continue and validate date below.
        }
    }

    $date_param = preg_replace('/[^0-9]/', '', $date_param);
    if (strlen($date_param) !== 8) {
        echo "<div class='container' style='padding: 4rem;'><div class='card'><h3>Error: no se pudo determinar la fecha del documento BOE. Usa un enlace con ?date=YYYYMMDD.</h3></div></div>";
        return;
    }

    $year = substr($date_param, 0, 4);
    $month = substr($date_param, 4, 2);
    $day = substr($date_param, 6, 2);
    $formatted_date = "$day/$month/$year";

    $pdf_url = "https://www.boe.es/borme/dias/$year/$month/$day/pdfs/$id.pdf";

    $parts = explode('-', $id);
    $province_id = end($parts);
    $provinces = [
        '28' => 'MADRID', '08' => 'BARCELONA', '46' => 'VALENCIA', '41' => 'SEVILLA',
        '50' => 'ZARAGOZA', '29' => 'MALAGA', '30' => 'MURCIA', '07' => 'BALEARES',
        '35' => 'LAS PALMAS', '48' => 'BIZKAIA', '03' => 'ALICANTE', '14' => 'CORDOBA',
        '47' => 'VALLADOLID', '36' => 'PONTEVEDRA', '33' => 'ASTURIAS'
    ];
    $province_name = $provinces[$province_id] ?? "PROVINCIA $province_id";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $pdf_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Referer: https://www.boe.es/',
        'Accept: application/pdf,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
    ]);
    $pdf_content = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200 || $pdf_content === false || strlen($pdf_content) < 100 || strpos($pdf_content, '%PDF') === false) {
        if ($http_code === 404) {
            $not_found = true;
        } else {
            $error_msg = 'No se pudo descargar el documento original desde BOE.es o el archivo esta dañado.';
        }
    } else {
        $parser = new BormeParser();
        $acts = $parser->parse_pdf($pdf_content, $province_name);
    }
}
?>

<div class="container" style="padding: var(--space-6) 0;">
    <nav class="breadcrumbs">
        <a href="/">Inicio</a> /
        <a href="/borme/dias">Diario</a> /
        <a href="/borme/dias/<?= $year ?>/<?= $month ?>/<?= $day ?>"><?= $formatted_date ?></a> /
        <span><?= htmlspecialchars($id) ?></span>
    </nav>

    <?php if ($not_found): ?>
        <div class="card"
            style="text-align: center; padding: var(--space-8); border: 2px dashed var(--border-strong); max-width: 800px; margin: 0 auto;">
            <div style="font-size: 3rem; margin-bottom: var(--space-4);">⚠️</div>
            <h2 style="color: var(--text-main); margin-bottom: var(--space-3);">Documento No Disponible</h2>
            <p style="font-size: 1.1rem; color: var(--text-muted); margin-bottom: var(--space-5);">
                El documento oficial <strong><?= htmlspecialchars($id) ?></strong> no se encuentra en la sede electrónica del BOE.
            </p>
            <div style="display: flex; gap: var(--space-3); justify-content: center;">
                <?php if ($pdf_url !== ''): ?>
                    <a href="<?= htmlspecialchars($pdf_url) ?>" target="_blank" class="btn btn-secondary">Comprobar en Origen</a>
                <?php endif; ?>
                <a href="/borme/dias/<?= $year ?>/<?= $month ?>/<?= $day ?>" class="btn btn-primary">Volver al Sumario</a>
            </div>
        </div>
    <?php else: ?>

        <div class="viewer-layout">
            <aside class="sticky-sidebar">
                <div style="margin-bottom: var(--space-4);">
                    <input type="text" id="toc-filter" placeholder="Filtrar empresas..."
                        style="width: 100%; padding: 8px; border: 1px solid var(--border-strong); border-radius: var(--radius-sm);">
                </div>

                <div
                    style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); margin-bottom: var(--space-2); letter-spacing: 0.05em;">
                    Indice de Empresas (<?= count($acts) ?>)
                </div>

                <nav id="toc-list">
                    <?php foreach ($acts as $index => $act): ?>
                        <?php
                        $anchor_id = 'act-' . $index;
                        $short_name = mb_strimwidth((string) ($act['company'] ?? ''), 0, 30, '...');
                        ?>
                        <a href="#<?= $anchor_id ?>" class="toc-link" title="<?= htmlspecialchars((string) ($act['company'] ?? '')) ?>">
                            <?= htmlspecialchars($short_name) ?>
                        </a>
                    <?php endforeach; ?>
                </nav>

                <?php if ($pdf_url !== ''): ?>
                    <div
                        style="margin-top: var(--space-6); padding-top: var(--space-4); border-top: 1px solid var(--border-subtle);">
                        <a href="<?= htmlspecialchars($pdf_url) ?>" target="_blank" class="btn btn-secondary btn-s" style="width: 100%;">
                            Ver PDF Original
                        </a>
                    </div>
                <?php endif; ?>
            </aside>

            <main class="main-content">
                <header
                    style="margin-bottom: var(--space-6); border-bottom: 1px solid var(--border-subtle); padding-bottom: var(--space-4);">
                    <div style="display: flex; align-items: center; gap: var(--space-3); margin-bottom: var(--space-2);">
                        <span class="badge"
                            style="background: var(--brand-primary); color: white;"><?= htmlspecialchars($province_name) ?></span>
                        <span class="mono" style="color: var(--text-muted);"><?= htmlspecialchars($id) ?></span>
                    </div>
                    <h1 style="font-size: 1.75rem; color: var(--text-main);">Actos Inscritos</h1>
                </header>

                <?php if ($error_msg !== ''): ?>
                    <div class="card" style="border-left: 4px solid var(--brand-primary); color: var(--brand-primary);">
                        <h3>Error de Procesamiento</h3>
                        <p><?= htmlspecialchars($error_msg) ?></p>
                    </div>
                <?php else: ?>
                    <div id="acts-container">
                        <?php foreach ($acts as $index => $act): ?>
                            <?php $anchor_id = 'act-' . $index; ?>
                            <article id="<?= $anchor_id ?>" class="act-card">
                                <div class="act-header">
                                    <h2 class="act-company"><?= htmlspecialchars((string) ($act['company'] ?? '')) ?></h2>
                                    <span class="badge"><?= htmlspecialchars((string) ($act['id'] ?? '')) ?></span>
                                </div>

                                <div
                                    style="font-size: 1rem; color: var(--text-main); line-height: 1.6; margin-bottom: var(--space-4);">
                                    <?= nl2br(htmlspecialchars((string) ($act['details'] ?? ''))) ?>
                                </div>

                                <?php if (!empty($act['cif']) || !empty($act['capital'])): ?>
                                    <div
                                        style="display: flex; gap: var(--space-4); font-size: 0.9rem; color: var(--text-muted); background: var(--bg-alt); padding: var(--space-2) var(--space-3); border-radius: var(--radius-sm);">
                                        <?php if (!empty($act['cif'])): ?>
                                            <span><strong>CIF:</strong> <span class="mono"><?= htmlspecialchars((string) $act['cif']) ?></span></span>
                                        <?php endif; ?>
                                        <?php if (!empty($act['capital'])): ?>
                                            <span><strong>Capital:</strong> <?= htmlspecialchars((string) $act['capital']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </main>
        </div>

        <script>
            const tocFilter = document.getElementById('toc-filter');
            if (tocFilter) {
                tocFilter.addEventListener('keyup', function (e) {
                    const term = e.target.value.toLowerCase();
                    const links = document.querySelectorAll('.toc-link');
                    links.forEach(link => {
                        const text = link.textContent.toLowerCase();
                        link.style.display = text.includes(term) ? 'block' : 'none';
                    });
                });
            }
        </script>
    <?php endif; ?>
</div>
