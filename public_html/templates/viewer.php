<?php
// viewer.php - On-the-Fly PDF Viewer & Parser
require_once __DIR__ . '/../lib/BormeParser.php';

// 1. Parameters
$id = $_GET['id'] ?? null; // e.g., BORME-A-2026-30-28
$date_param = $_GET['date'] ?? null; // YYYYMMDD from URL

if (!$id || !$date_param) {
    echo "<div class='container' style='padding: 4rem;'><div class='card'><h3>Error: Parámetros incompletos (ID o Fecha)</h3></div></div>";
    return;
}

// Format Date for URL: YYYY/MM/DD
$year = substr($date_param, 0, 4);
$month = substr($date_param, 4, 2);
$day = substr($date_param, 6, 2);
$formatted_date = "$day/$month/$year";

// 2. Fetch PDF from BOE
// URL Pattern: https://www.boe.es/borme/dias/2026/02/11/pdfs/BORME-A-2026-29-28.pdf
$pdf_url = "https://www.boe.es/borme/dias/$year/$month/$day/pdfs/$id.pdf";

// Determine Province from ID (Last part: BORME-A-YYYY-NUM-PROVINCE)
$parts = explode('-', $id);
$province_id = end($parts);

// Map Province ID to Name (Subset for display)
$provinces = [
    '28' => 'MADRID',
    '08' => 'BARCELONA',
    '46' => 'VALENCIA',
    '41' => 'SEVILLA',
    '50' => 'ZARAGOZA',
    '29' => 'MÁLAGA',
    '30' => 'MURCIA',
    '07' => 'BALEARES',
    '35' => 'LAS PALMAS',
    '48' => 'BIZKAIA',
    '03' => 'ALICANTE',
    '14' => 'CÓRDOBA',
    '47' => 'VALLADOLID',
    '36' => 'PONTEVEDRA',
    '33' => 'ASTURIAS'
];
$province_name = $provinces[$province_id] ?? "PROVINCIA $province_id";

// Initialize Variables
$acts = [];
$error_msg = "";
$not_found = false;

// 2. Fetch PDF Content (On-the-Fly)
// -------------------------------------------------------------------------

// Try CURL first (More robust for external fetching vs file_get_contents)
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $pdf_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
// Mimic specific browser + Referer
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Referer: https://www.boe.es/',
    'Accept: application/pdf,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
]);

$pdf_content = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// Validation
if ($http_code !== 200 || $pdf_content === false || strlen($pdf_content) < 100 || strpos($pdf_content, '%PDF') === false) {
    // If CURL failed, unlikely file_get_contents will work, but strictly fallback if CURL is disabled?
    // Usually invalid PDF content means block.

    // Check specifically for 404 (Not Found on BOE) vs 403 (Blocked)
    if ($http_code == 404) {
        $not_found = true;
        // Skip parsing
        goto render_view;
    }

    // Log intent to fallback but for now trust CURL failure or try file_get_contents as last resort
    // simple fallback without complex context for now
    if ($pdf_content === false) {
        $pdf_content = @file_get_contents($pdf_url);
    }
}

// 3. Parse Content
$parser = new BormeParser();
//$acts = []; // Initialized above
//$error_msg = "";
//$not_found = false; 

if ($pdf_content && strlen($pdf_content) > 1000) {
    $acts = $parser->parse_pdf($pdf_content, $province_name);
} else {
    $error_msg = "No se pudo descargar el documento original desde BOE.es o el archivo está dañado.";
}

// 4. Render
render_view:
?>

<div class="container" style="padding: var(--space-6) 0;">
    <nav class="breadcrumbs">
        <a href="/">Inicio</a> /
        <a href="/borme/dias">Diario</a> /
        <a href="/borme/dias/<?= $year ?>/<?= $month ?>/<?= $day ?>"><?= $formatted_date ?></a> /
        <span><?= $id ?></span>
    </nav>

    <?php if (isset($not_found) && $not_found): ?>
        <div class="card"
            style="text-align: center; padding: var(--space-8); border: 2px dashed var(--border-strong); max-width: 800px; margin: 0 auto;">
            <div style="font-size: 3rem; margin-bottom: var(--space-4);">⚠️</div>
            <h2 style="color: var(--text-main); margin-bottom: var(--space-3);">Documento No Disponible</h2>
            <p style="font-size: 1.1rem; color: var(--text-muted); margin-bottom: var(--space-5);">
                El documento oficial <strong><?= $id ?></strong> no se encuentra en la sede electrónica del BOE.
            </p>
            <div style="display: flex; gap: var(--space-3); justify-content: center;">
                <a href="<?= $pdf_url ?>" target="_blank" class="btn btn-secondary">Comprobar en Origen</a>
                <a href="/borme/dias/<?= $year ?>/<?= $month ?>/<?= $day ?>" class="btn btn-primary">Volver al Sumario</a>
            </div>
        </div>
    <?php else: ?>

        <div class="viewer-layout">
            <!-- Sidebar Navigation (Left) -->
            <aside class="sticky-sidebar">
                <div style="margin-bottom: var(--space-4);">
                    <input type="text" id="toc-filter" placeholder="Filtrar empresas..."
                        style="width: 100%; padding: 8px; border: 1px solid var(--border-strong); border-radius: var(--radius-sm);">
                </div>

                <div
                    style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); margin-bottom: var(--space-2); letter-spacing: 0.05em;">
                    Índice de Empresas (<?= count($acts) ?>)
                </div>

                <nav id="toc-list">
                    <?php foreach ($acts as $index => $act):
                        // Create a safe ID anchor
                        $anchor_id = "act-" . $index;
                        $short_name = mb_strimwidth($act['company'], 0, 30, "...");
                        ?>
                        <a href="#<?= $anchor_id ?>" class="toc-link" title="<?= $act['company'] ?>">
                            <?= $short_name ?>
                        </a>
                    <?php endforeach; ?>
                </nav>

                <div
                    style="margin-top: var(--space-6); padding-top: var(--space-4); border-top: 1px solid var(--border-subtle);">
                    <a href="<?= $pdf_url ?>" target="_blank" class="btn btn-secondary btn-s" style="width: 100%;">
                        📄 Ver PDF Original
                    </a>
                </div>
            </aside>

            <!-- Main Content (Right) -->
            <main class="main-content">
                <header
                    style="margin-bottom: var(--space-6); border-bottom: 1px solid var(--border-subtle); padding-bottom: var(--space-4);">
                    <div style="display: flex; align-items: center; gap: var(--space-3); margin-bottom: var(--space-2);">
                        <span class="badge"
                            style="background: var(--brand-primary); color: white;"><?= $province_name ?></span>
                        <span class="mono" style="color: var(--text-muted);"><?= $id ?></span>
                    </div>
                    <h1 style="font-size: 1.75rem; color: var(--text-main);">Actos Inscritos</h1>
                </header>

                <?php if ($error_msg): ?>
                    <div class="card" style="border-left: 4px solid var(--brand-primary); color: var(--brand-primary);">
                        <h3>Error de Procesamiento</h3>
                        <p><?= $error_msg ?></p>
                    </div>
                <?php else: ?>

                    <div id="acts-container">
                        <?php foreach ($acts as $index => $act):
                            $anchor_id = "act-" . $index;
                            ?>
                            <article id="<?= $anchor_id ?>" class="act-card">
                                <div class="act-header">
                                    <h2 class="act-company"><?= $act['company'] ?></h2>
                                    <span class="badge"><?= $act['id'] ?></span>
                                </div>

                                <div
                                    style="font-size: 1rem; color: var(--text-main); line-height: 1.6; margin-bottom: var(--space-4);">
                                    <?= $act['details'] ?>
                                </div>

                                <?php if ($act['cif'] || $act['capital']): ?>
                                    <div
                                        style="display: flex; gap: var(--space-4); font-size: 0.9rem; color: var(--text-muted); background: var(--bg-alt); padding: var(--space-2) var(--space-3); border-radius: var(--radius-sm);">
                                        <?php if ($act['cif']): ?>
                                            <span><strong>CIF:</strong> <span class="mono"><?= $act['cif'] ?></span></span>
                                        <?php endif; ?>
                                        <?php if ($act['capital']): ?>
                                            <span><strong>Capital:</strong> <?= $act['capital'] ?></span>
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
            // Simple JS for TOC filtering
            document.getElementById('toc-filter').addEventListener('keyup', function (e) {
                const term = e.target.value.toLowerCase();
                const links = document.querySelectorAll('.toc-link');

                links.forEach(link => {
                    const text = link.textContent.toLowerCase();
                    if (text.includes(term)) {
                        link.style.display = 'block';
                    } else {
                        link.style.display = 'none';
                    }
                });
            });
        </script>
    <?php endif; ?>
</div>