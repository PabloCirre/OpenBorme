<?php
// viewer.php - Refined Document Viewer

// Mock/Logic to fetch data (already existing in previous version)
$id = $_GET['id'] ?? 'BORME-A-2026-3024';
$formatted_date = "11/02/2026";
$source_url = "https://www.boe.es/diario_borme/txt.php?id=" . $id;

// Simulated data extraction for demonstration
$data = [
    'Company Name' => 'SOLUCIONES TECNOLÓGICAS SL',
    'Act Type' => 'CONSTITUCIÓN',
    'Province' => 'MADRID',
    'Section' => 'SECCIÓN PRIMERA',
    'Details' => "Comienzo de operaciones: 1.01.26. Objeto social: Desarrollo de software y sistemas de inteligencia artificial... Domicilio: C/ Principal 1, Madrid. Capital: 3.000,00 Euros. Nombramientos: Adm. Unico: GARCÍA LÓPEZ MANUEL.",
    'Hash' => 'e99a18c428cb38d5f260853678922e03'
];
?>

<div class="container" style="padding: var(--space-6) 0;">
    <nav class="breadcrumbs">
        <a href="/">Inicio</a> /
        <a href="/sumario">Diario</a> /
        <a href="/buscar?date=2026-02-11">11/02/2026</a> /
        <span><?= $id ?></span>
    </nav>

    <div class="results-layout">
        <!-- Main Content (Grid 9) -->
        <main class="main-content">
            <div style="margin-bottom: var(--space-6);">
                <div style="display: flex; gap: var(--space-2); margin-bottom: var(--space-3);">
                    <span class="badge"><?= $data['Section'] ?></span>
                    <span class="badge"><?= $data['Province'] ?></span>
                </div>
                <h1 style="margin-bottom: var(--space-2);"><?= $data['Company Name'] ?></h1>
                <p class="mono" style="color: var(--text-muted);">BORME ID: <?= $id ?></p>
            </div>

            <article class="inst-card"
                style="line-height: 2; font-size: 1.1rem; white-space: pre-wrap; padding: var(--space-6); background: white;">
                <?= $data['Details'] ?>
            </article>

            <!-- Trazabilidad block (Discrete, as per spec) -->
            <div class="trazabilidad" style="padding-top: var(--space-6); border-top: 2px solid var(--border-subtle);">
                <h5>Trazabilidad</h5>
                <div class="inst-card"
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--space-4); margin-top: var(--space-3); padding: var(--space-4); background: var(--bg-alt);">
                    <div>
                        <p class="meta" style="font-size: 12px; margin-bottom: 4px;">Fuente Oficial</p>
                        <a href="<?= $source_url ?>" target="_blank" class="btn btn-ghost btn-s"
                            style="padding: 0; height: auto;">Agencia Estatal BOE &rarr;</a>
                    </div>
                    <div>
                        <p class="meta" style="font-size: 12px; margin-bottom: 4px;">Fecha de Ingesta</p>
                        <p style="font-size: 13px; font-weight: 600;">11/02/2026 08:34:21</p>
                    </div>
                    <div>
                        <p class="meta" style="font-size: 12px; margin-bottom: 4px;">Integridad del Acto</p>
                        <p class="mono" style="font-size: 11px;"><?= $data['Hash'] ?></p>
                    </div>
                </div>
                <p style="margin-top: var(--space-4); font-size: 12px; color: var(--text-muted); font-style: italic;">
                    Nota: Este texto es una estructuración para consulta técnica. Para efectos legales, consulte la
                    publicación oficial vinculada.
                </p>
            </div>
        </main>

        <!-- Sidebar (Grid 3) -->
        <aside class="sidebar">
            <div style="position: sticky; top: 84px;">
                <div class="card" style="padding: var(--space-4); margin-bottom: var(--space-4);">
                    <h4
                        style="font-size: 12px; text-transform: uppercase; color: var(--text-muted); margin-bottom: var(--space-3);">
                        Acciones</h4>
                    <div style="display: flex; flex-direction: column; gap: var(--space-2);">
                        <button class="btn btn-secondary btn-m"
                            onclick="navigator.clipboard.writeText(window.location.href)">Copiar Enlace</button>
                        <a href="/export?id=<?= $id ?>&format=json" class="btn btn-secondary btn-m">Exportar JSON</a>
                    </div>
                </div>

                <div class="card" style="padding: var(--space-4);">
                    <h4
                        style="font-size: 12px; text-transform: uppercase; color: var(--text-muted); margin-bottom: var(--space-3);">
                        Información Acto</h4>
                    <div
                        style="font-size: 13px; border-bottom: 1px solid var(--border-light); padding-bottom: var(--space-2); margin-bottom: var(--space-2);">
                        <p class="meta">Tipo de Acto</p>
                        <p style="font-weight: 600;"><?= $data['Act Type'] ?></p>
                    </div>
                    <div
                        style="font-size: 13px; border-bottom: 1px solid var(--border-light); padding-bottom: var(--space-2); margin-bottom: var(--space-2);">
                        <p class="meta">Fecha de Publicación</p>
                        <p style="font-weight: 600;"><?= $formatted_date ?></p>
                    </div>
                    <div style="font-size: 13px;">
                        <p class="meta">Boletín Provincial</p>
                        <p style="font-weight: 600;"><?= $data['Province'] ?></p>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>