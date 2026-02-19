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

// Fetch Content (with fallback)
$pdf_content = @file_get_contents($pdf_url);
if ($pdf_content === false) {
    // Try CURL if file_get_contents fails
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $pdf_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For compatibility
    $pdf_content = curl_exec($ch);
    curl_close($ch);
}

// 3. Parse Content
$parser = new BormeParser();
$acts = [];
$error_msg = "";

if ($pdf_content && strlen($pdf_content) > 1000) {
    $acts = $parser->parse_pdf($pdf_content, $province_name);
} else {
    $error_msg = "No se pudo descargar el documento original desde BOE.es o el archivo está dañado.";
}

// 4. Render
?>

<div class="container" style="padding: var(--space-6) 0;">
    <nav class="breadcrumbs">
        <a href="/">Inicio</a> /
        <a href="/diario">Diario</a> /
        <a href="/borme/dias/<?= $year ?>/<?= $month ?>/<?= $day ?>"><?= $formatted_date ?></a> /
        <span><?= $id ?></span>
    </nav>

    <div class="results-layout">
        <!-- Main Content -->
        <main class="main-content">
            <div style="margin-bottom: var(--space-6);">
                <div style="display: flex; gap: var(--space-2); margin-bottom: var(--space-3);">
                    <span class="badge">SECCIÓN PRIMERA</span>
                    <span class="badge"><?= $province_name ?></span>
                </div>
                <h1 style="margin-bottom: var(--space-2);">Boletín Provincial: <?= $province_name ?></h1>
                <p class="mono" style="color: var(--text-muted);"><?= $id ?> • <?= count($acts) ?> Actos Extraídos</p>
            </div>

            <?php if ($error_msg): ?>
                <div class="card" style="border-left: 4px solid var(--boe-red); color: var(--boe-red);">
                    <h3>Error de Obtención</h3>
                    <p><?= $error_msg ?></p>
                    <p><a href="<?= $pdf_url ?>" target="_blank" style="text-decoration: underline;">Intentar descargar
                            manualmente</a></p>
                </div>
            <?php else: ?>

                <?php foreach ($acts as $act): ?>
                    <article class="inst-card"
                        style="margin-bottom: var(--space-6); background: white; padding: var(--space-6);">
                        <div
                            style="border-bottom: 1px solid var(--border-subtle); padding-bottom: var(--space-3); margin-bottom: var(--space-4);">
                            <span class="badge"
                                style="background: var(--bg-alt); color: var(--text-main); border: 1px solid var(--border-color);"><?= $act['id'] ?></span>
                            <h2 style="margin: var(--space-3) 0; font-size: 1.5rem; color: var(--brand-primary);">
                                <?= $act['company'] ?></h2>
                        </div>

                        <div style="line-height: 1.8; font-size: 1.05rem; white-space: pre-wrap; color: var(--text-main);">
                            <?= $act['details'] ?>
                        </div>

                        <?php if ($act['cif'] || $act['capital']): ?>
                            <div
                                style="margin-top: var(--space-4); padding-top: var(--space-4); border-top: 1px dashed var(--border-color); display: flex; gap: var(--space-4); font-size: 0.9rem;">
                                <?php if ($act['cif']): ?>
                                    <div><strong>CIF:</strong> <span class="mono"><?= $act['cif'] ?></span></div>
                                <?php endif; ?>
                                <?php if ($act['capital']): ?>
                                    <div><strong>Capital:</strong> <?= $act['capital'] ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>

            <?php endif; ?>

            <!-- Trazabilidad block -->
            <div class="trazabilidad" style="padding-top: var(--space-6); border-top: 2px solid var(--border-subtle);">
                <h5>Trazabilidad del Documento</h5>
                <div class="inst-card"
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--space-4); margin-top: var(--space-3); padding: var(--space-4); background: var(--bg-alt);">
                    <div>
                        <p class="meta" style="font-size: 12px; margin-bottom: 4px;">Fuente Oficial</p>
                        <a href="<?= $pdf_url ?>" target="_blank" class="btn btn-ghost btn-s"
                            style="padding: 0; height: auto;">Descargar PDF Original (BOE) &rarr;</a>
                    </div>
                    <div>
                        <p class="meta" style="font-size: 12px; margin-bottom: 4px;">Procesado</p>
                        <p style="font-size: 13px; font-weight: 600;">En tiempo real (Fly Mode)</p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Sidebar -->
        <aside class="sidebar">
            <div style="position: sticky; top: 84px;">
                <div class="card" style="padding: var(--space-4);">
                    <h4
                        style="font-size: 12px; text-transform: uppercase; color: var(--text-muted); margin-bottom: var(--space-3);">
                        Navegación
                    </h4>
                    <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 1rem;">
                        Este documento contiene <strong><?= count($acts) ?></strong> actos empresariales inscritos en
                        <strong><?= $province_name ?></strong>.
                    </p>
                    <a href="<?= $pdf_url ?>" class="btn btn-primary btn-full">VER PDF ORIGINAL</a>
                </div>
            </div>
        </aside>
    </div>
</div>