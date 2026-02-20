<?php
// sumario.php - Structured Daily Summary
// Fetches and parses the daily index from BOE.es

$date = $_GET['date'] ?? date('Ymd');
$year = substr($date, 0, 4);
$month = substr($date, 4, 2);
$day = substr($date, 6, 2);
$formatted_date = "$day/$month/$year";

// Use shared scraper
require_once __DIR__ . '/../lib/BoeScraper.php';

$scraper = new BoeScraper();
$result = $scraper->getSummary($date);

$sections = $result['sections'];
$error = $result['error'];

// Filter by Province (if requested)
$filter_prov_slug = $_GET['provincia'] ?? null;
if ($filter_prov_slug && !empty($sections)) {
    $filtered_sections = [];
    foreach ($sections as $sec_name => $provinces) {
        foreach ($provinces as $prov_name => $items) {
            // Normalize Prov Name to match slug logic
            $clean_name = str_replace(['PROVINCIA ', 'SECCIÓN ESPECIAL '], '', $prov_name);
            $slug = strtolower(trim($clean_name));
            $slug = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ', '/'], ['a', 'e', 'i', 'o', 'u', 'n', '-'], $slug);
            $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
            $slug = preg_replace('/-+/', '-', $slug);
            $slug = trim($slug, '-');

            if ($slug === $filter_prov_slug) {
                $filtered_sections[$sec_name][$prov_name] = $items;
            }
        }
    }
    // If we found matches, use them. Else show all? Or show empty?
    // Let's show filtered.
    if (!empty($filtered_sections)) {
        $sections = $filtered_sections;
    }
}

// Fallback logic if empty (e.g. weekend or parsing fail)
if (empty($sections)) {
    $sections['AVISO'] = ['INFO' => [['id' => '', 'label' => 'No hay boletines publicados para esta fecha o no se han podido cargar.']]];
}
?>

<div class="container" style="padding: var(--space-6) 0;">
    <nav class="breadcrumbs">
        <a href="/">Inicio</a> /
        <a href="/borme/dias">Diario</a> /
        <span>Boletín de <?= $formatted_date ?></span>
    </nav>

    <div style="margin-bottom: var(--space-7);">
        <h1>BORME del <?= $formatted_date ?></h1>
        <p class="meta">Sumario obtenido en tiempo rea de la Sede Electrónica del BOE.</p>

        <div style="margin-top: var(--space-4);">
            <a href="https://www.boe.es/borme/dias/<?= $year ?>/<?= $month ?>/<?= $day ?>/" target="_blank"
                class="btn btn-secondary btn-s">Ver en BOE.es &rarr;</a>
        </div>
    </div>

    <?php foreach ($sections as $section_name => $provinces): ?>
        <section style="margin-bottom: var(--space-8);">
            <div style="display: flex; align-items: center; gap: var(--space-4); margin-bottom: var(--space-5);">
                <h2 style="font-size: 18px; text-transform: uppercase; color: var(--accent); white-space: nowrap;">
                    <?= $section_name ?>
                </h2>
                <div style="flex: 1; height: 1px; background: var(--border-dark);"></div>
            </div>

            <?php foreach ($provinces as $province => $items): ?>
                <div style="margin-bottom: var(--space-6);">
                    <h3
                        style="font-size: 14px; color: var(--text-muted); text-transform: uppercase; margin-bottom: var(--space-3); border-left: 2px solid var(--accent); padding-left: var(--space-3);">
                        <?= $province ?>
                    </h3>

                    <div
                        style="display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: var(--space-4);">
                        <?php foreach ($items as $item):
                            if (!$item['id']) {
                                echo "<div class='inst-card' style='padding: var(--space-4); color: var(--text-muted);'>" . $item['label'] . "</div>";
                                continue;
                            }
                            ?>
                            <a href="/borme/doc/<?= $item['id'] ?>?date=<?= $date ?>" class="inst-card"
                                style="padding: var(--space-4); display: flex; align-items: center; justify-content: space-between; text-decoration: none;">
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <span class="mono"
                                        style="font-size: 0.75rem; color: var(--text-muted);"><?= $item['id'] ?></span>
                                    <span
                                        style="font-size: 1rem; font-weight: 700; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 280px;"><?= $item['label'] ?></span>
                                </div>
                                <span style="font-size: 0.8rem; color: var(--brand-primary); font-weight: 800;">VER &rarr;</span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>
    <?php endforeach; ?>
</div>