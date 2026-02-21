<?php
// templates/landing_seo.php - SEO Landing Page for Provinces and Act Types
// Simplified and unified for V3

$provincia_slug = $_GET['provincia'] ?? null;
$tipo_slug = $_GET['tipo'] ?? null;

// Determine Context
$context = "general";
$title = "Explora el BORME";

if ($provincia_slug) {
    $context = "provincia";
    $prov_name = ucwords(str_replace('-', ' ', $provincia_slug));
    $title = "BORME de " . $prov_name;
} elseif ($tipo_slug) {
    $context = "tipo";
    $tipo_name = ucwords(str_replace('-', ' ', $tipo_slug));
    $title = "Actos de Tipo: " . $tipo_name;
}
?>

<div class="container" style="padding: var(--space-8) 0;">
    <nav class="breadcrumbs">
        <a href="/">Inicio</a> /
        <?php if ($context === 'provincia'): ?>
            <a href="/provincias">Provincias</a> / <span><?= $prov_name ?></span>
        <?php else: ?>
            <a href="/tipos-de-actos">Tipos</a> / <span><?= $tipo_name ?? 'General' ?></span>
        <?php endif; ?>
    </nav>

    <div class="v3-landing-header">
        <h1 class="v3-landing-title"><?= $title ?></h1>
        <p class="hero-subtitle">
            Infraestructura de datos abierta del Registro Mercantil para
            <strong><?= $prov_name ?? $tipo_name ?? 'España' ?></strong>.
            Consulta actos, empresas e historial societario normalizado.
        </p>
    </div>

    <div class="borme-grid">
        <!-- MAIN CONTENT: CALENDAR & RECENT -->
        <main class="grid-col-8">
            <?php if ($context === 'provincia'): ?>
                <section style="margin-bottom: var(--space-8);">
                    <h2 class="section-v3-title" style="margin-bottom: var(--space-4);">📅 Calendario de Publicaciones</h2>
                    <p style="margin-bottom: var(--space-5); color: var(--text-muted);">Selecciona un día para ver los actos
                        inscritos en la provincia de <?= $prov_name ?>.</p>

                    <div class="calendar-widget">
                        <?php
                        $current_month = date('n');
                        $current_year = date('Y');
                        $days_in_month = cal_days_in_month(CAL_GREGORIAN, $current_month, $current_year);
                        $first_day = date('N', strtotime("$current_year-$current_month-01"));
                        $month_names = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
                        ?>
                        <div class="calendar-header"><?= $month_names[(int) $current_month] ?>     <?= $current_year ?></div>
                        <div class="calendar-grid">
                            <div class="calendar-day-head">L</div>
                            <div class="calendar-day-head">M</div>
                            <div class="calendar-day-head">X</div>
                            <div class="calendar-day-head">J</div>
                            <div class="calendar-day-head">V</div>
                            <div class="calendar-day-head">S</div>
                            <div class="calendar-day-head">D</div>
                            <?php
                            for ($i = 1; $i < $first_day; $i++)
                                echo '<div class="calendar-day empty"></div>';
                            $today_ymd = date('Ymd');
                            for ($d = 1; $d <= $days_in_month; $d++) {
                                $ts = strtotime("$current_year-$current_month-$d");
                                $ymd = date('Ymd', $ts);
                                $is_future = ($ymd > $today_ymd);
                                $is_weekend = (date('N', $ts) >= 6);
                                $class = "calendar-day" . ($ymd === $today_ymd ? " today" : "") . ($is_future ? " disabled" : "");
                                if (!$is_future && !$is_weekend) {
                                    $url = "/borme/provincia/$provincia_slug/" . date('Y/m/d', $ts);
                                    echo "<a href='$url' class='$class' style='color: var(--brand-primary); font-weight: 700;'>$d</a>";
                                } else {
                                    echo "<div class='$class' style='opacity: 0.5;'>$d</div>";
                                }
                            }
                            ?>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <section>
                <h2 class="section-v3-title" style="margin-bottom: var(--space-4);">Información Registral</h2>
                <p>El Boletín Oficial del Registro Mercantil (BORME) publica diariamente los actos de obligada
                    inscripción de las empresas de <strong><?= $prov_name ?? 'España' ?></strong>. Esto incluye
                    constituciones, ampliaciones de capital, nombramientos y ceses de administradores, y disoluciones.
                </p>
            </section>
        </main>

        <!-- SIDEBAR -->
        <aside class="grid-col-4">
            <div class="inst-card">
                <h3 style="font-size: 1.1rem; margin-bottom: var(--space-3);">Otras Provincias</h3>
                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                    <?php
                    $others = ["Madrid", "Barcelona", "Valencia", "Sevilla", "Zaragoza", "Málaga"];
                    foreach ($others as $p):
                        $s = strtolower(str_replace(' ', '-', $p));
                        if ($s === $provincia_slug)
                            continue;
                        ?>
                        <a href="/borme/provincia/<?= $s ?>" class="badge-outline"
                            style="padding: 4px 10px; font-size: 0.8rem;"><?= $p ?></a>
                    <?php endforeach; ?>
                    <a href="/provincias" class="badge"
                        style="font-size: 0.8rem; background: var(--bg-alt); color: var(--brand-primary);">Ver Todas
                        &rarr;</a>
                </div>
            </div>

            <div class="inst-card" style="margin-top: var(--space-6);">
                <h3 style="font-size: 1.1rem; margin-bottom: var(--space-3);">Buscar Empresa</h3>
                <form action="/buscar" method="GET">
                    <input type="text" name="q" placeholder="Nombre o CIF..." class="header-search-input"
                        style="height: 36px; font-size: 14px; margin-bottom: 8px;">
                    <?php if ($provincia_slug): ?>
                        <input type="hidden" name="provincia" value="<?= $provincia_slug ?>">
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary btn-s" style="width: 100%;">Buscar en
                        <?= $prov_name ?? 'Global' ?></button>
                </form>
            </div>
        </aside>
    </div>
</div>