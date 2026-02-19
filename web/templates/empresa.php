<?php
// empresa.php - Company Detail & Directory
include 'header.php';

$company_id = $_GET['id'] ?? '';
$company_name = "EJEMPLO CORPORATIVO SL"; // Mock

// Timeline Mock Data
$events = [
    ['date' => '11/02/2026', 'act' => 'Aumento de capital', 'id' => 'BORME-A-2026-3024'],
    ['date' => '15/01/2025', 'act' => 'Nombramiento de Auditores', 'id' => 'BORME-A-2025-1234'],
    ['date' => '02/11/2020', 'act' => 'Constitución', 'id' => 'BORME-A-2020-9988'],
];
?>

<div class="container" style="padding: var(--space-6) 0;">
    <nav class="breadcrumbs">
        <a href="/">Inicio</a> /
        <a href="/empresas">Empresas</a> /
        <span><?= htmlspecialchars($company_name) ?></span>
    </nav>

    <?php if ($company_id): ?>
        <div style="margin-bottom: var(--space-7);">
            <p class="meta">Ficha de Empresa</p>
            <h1><?= htmlspecialchars($company_name) ?></h1>
            <p class="meta" style="font-size: 16px;">Historial de publicaciones detectadas en el BORME.</p>
        </div>

        <div class="results-layout">
            <main class="main-content">
                <h2 style="font-size: 18px; margin-bottom: var(--space-5);">Evolución Cronológica</h2>

                <div style="position: relative; padding-left: var(--space-6); border-left: 2px solid var(--border-dark);">
                    <?php foreach ($events as $ev): ?>
                        <div style="margin-bottom: var(--space-6); position: relative;">
                            <div
                                style="position: absolute; left: calc(-1 * var(--space-6) - 7px); top: 6px; width: 12px; height: 12px; background: white; border: 2px solid var(--accent); border-radius: 50%;">
                            </div>
                            <div class="card"
                                style="padding: var(--space-3) var(--space-4); display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <span class="meta"
                                        style="font-size: 12px; color: var(--accent); font-weight: 700;"><?= $ev['date'] ?></span>
                                    <p style="font-weight: 600; margin-top: 2px;"><?= $ev['act'] ?></p>
                                </div>
                                <a href="/borme/doc/<?= $ev['id'] ?>" class="btn btn-ghost btn-s">Ver Acto &rarr;</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </main>

            <aside class="sidebar">
                <div class="card" style="padding: var(--space-4);">
                    <h4
                        style="font-size: 12px; text-transform: uppercase; color: var(--text-muted); margin-bottom: var(--space-3);">
                        Resumen de Actividad</h4>
                    <div style="font-size: 13px; margin-bottom: var(--space-3);">
                        <p class="meta">Primera aparición</p>
                        <p style="font-weight: 600;">02/11/2020</p>
                    </div>
                    <div style="font-size: 13px;">
                        <p class="meta">Última aparición</p>
                        <p style="font-weight: 600;">11/02/2026</p>
                    </div>
                    <div
                        style="margin-top: var(--space-4); padding-top: var(--space-4); border-top: 1px solid var(--border-light);">
                        <a href="/export?empresa=<?= $company_id ?>" class="btn btn-secondary btn-m"
                            style="width: 100%;">Exportar Historial</a>
                    </div>
                </div>
            </aside>
        </div>

    <?php else: ?>
        <div style="max-width: 680px; margin: var(--space-8) auto; text-align: center;">
            <h1 style="margin-bottom: var(--space-4);">Directorio de Empresas</h1>
            <p class="meta" style="margin-bottom: var(--space-6);">Busca el historial completo de publicaciones por nombre o
                identificación fiscal.</p>

            <form action="/empresas" method="GET"
                style="display: flex; gap: var(--space-3); margin-bottom: var(--space-8);">
                <input type="text" name="name" class="input-main" placeholder="Nombre de la empresa o CIF..."
                    style="flex: 1;">
                <button type="submit" class="btn btn-primary btn-l">BUSCAR</button>
            </form>

            <div class="card" style="text-align: left;">
                <h3
                    style="font-size: 14px; text-transform: uppercase; color: var(--text-muted); margin-bottom: var(--space-4);">
                    Sugerencias de Búsqueda</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-3);">
                    <a href="/empresa/1" style="font-size: 14px; color: var(--accent); text-decoration: none;">&bull;
                        Telefónica SA</a>
                    <a href="/empresa/2" style="font-size: 14px; color: var(--accent); text-decoration: none;">&bull;
                        Inditex SA</a>
                    <a href="/empresa/3" style="font-size: 14px; color: var(--accent); text-decoration: none;">&bull; Banco
                        Santander</a>
                    <a href="/empresa/4" style="font-size: 14px; color: var(--accent); text-decoration: none;">&bull; Repsol
                        Petróleo</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>