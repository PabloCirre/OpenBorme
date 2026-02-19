<?php
// sumario.php - Structured Daily Summary
include 'header.php';

$date = $_GET['date'] ?? '2026-02-11';
$formatted_date = date('d/m/Y', strtotime($date));

// Mock Data grouped by Section and Province
$sections = [
    'SECCIÓN PRIMERA: Empresarios - Actos Inscritos' => [
        'MADRID' => [
            ['id' => 'BORME-A-2026-3024', 'label' => 'SOLUCIONES TECNOLÓGICAS SL'],
            ['id' => 'BORME-A-2026-3025', 'label' => 'LOGÍSTICA AVANZADA SA'],
        ],
        'BARCELONA' => [
            ['id' => 'BORME-A-2026-3026', 'label' => 'MEDITERRÁNEA DE SERVICIOS SL'],
        ]
    ],
    'SECCIÓN SEGUNDA: Anuncios y Avisos Legales' => [
        'MADRID' => [
            ['id' => 'BORME-C-2026-407', 'label' => 'ANUNCIO DE FUSIÓN'],
        ]
    ]
];
?>

<div class="container" style="padding: var(--space-6) 0;">
    <nav class="breadcrumbs">
        <a href="/">Inicio</a> /
        <a href="/diario">Diario</a> /
        <span>Boletín de <?= $formatted_date ?></span>
    </nav>

    <div style="margin-bottom: var(--space-7);">
        <h1>BORME del <?= $formatted_date ?></h1>
        <p class="meta">Sumario estructurado de actos y anuncios publicados en el Registro Mercantil.</p>
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
                        Provincia de <?= $province ?>
                    </h3>

                    <div
                        style="display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: var(--space-4);">
                        <?php foreach ($items as $item): ?>
                            <a href="/borme/doc/<?= $item['id'] ?>" class="inst-card"
                                style="padding: var(--space-4); display: flex; align-items: center; justify-content: space-between; text-decoration: none;">
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <span class="mono"
                                        style="font-size: 0.75rem; color: var(--text-muted);"><?= $item['id'] ?></span>
                                    <span
                                        style="font-size: 1rem; font-weight: 700; color: var(--text-main);"><?= $item['label'] ?></span>
                                </div>
                                <span style="font-size: 0.8rem; color: var(--brand-primary); font-weight: 800;">VER &rarr;</span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>
    <?php endforeach; ?>

    <div class="trazabilidad" style="margin-top: var(--space-8);">
        <h5>Información del Sumario</h5>
        <p style="font-size: 13px;">
            Este sumario ha sido generado automáticamente a partir de los datos abiertos de la <a
                href="https://www.boe.es" target="_blank">Agencia Estatal BOE</a>.
            OpenBorme procesa estos datos para ofrecer una visualización técnica estructurada.
        </p>
    </div>
</div>

<?php include 'footer.php'; ?>