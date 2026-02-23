<?php
$db_path = file_exists(__DIR__ . '/../../pipeline/db/Database.php') ? __DIR__ . '/../../pipeline/db/Database.php' : __DIR__ . '/../pipeline/db/Database.php';
require_once $db_path;
?>
<main class="container">
    <div class="hero-v3">
        <h1 class="hero-title">La infraestructura de datos del <br><span>Registro Mercantil</span>
        </h1>
        <p class="hero-subtitle">
            Normalización y democratización de los datos societarios de la industria española en una plataforma de alto
            rendimiento para analistas y desarrolladores.
        </p>

        <form action="/buscar" method="GET" class="hero-search-wrap">
            <input type="text" name="q" class="hero-search-input" placeholder="Buscar por empresa, NIF o texto...">
            <button type="submit" class="btn btn-primary btn-m">BUSCAR</button>
        </form>

        <div class="hero-badges">
            <a href="/borme/dias" class="badge-outline">Boletín de Hoy</a>
            <a href="/provincias" class="badge-outline">Provincias</a>
            <a href="/manifiesto" class="badge">Manifiesto Técnico</a>
        </div>
    </div>

    <div style="margin: var(--space-9) 0;">
        <div class="section-v3-header">
            <h2 class="section-v3-title">Boletines Recientes</h2>
            <a href="/borme/dias" class="btn btn-ghost btn-s">Histórico Completo</a>
        </div>

        <div class="borme-grid">
            <!-- Global Calendar in Window -->
            <div class="grid-col-4">
                <div class="v4-window" style="padding: var(--space-5);">
                    <?php
                    $current_month = date('n');
                    $current_year = date('Y');
                    $days_in_month = cal_days_in_month(CAL_GREGORIAN, $current_month, $current_year);
                    $first_day = date('N', strtotime("$current_year-$current_month-01"));
                    $month_names = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
                    ?>
                    <div class="calendar-month-label">
                        <?= $month_names[(int) $current_month] ?> <?= $current_year ?>
                    </div>
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
                        for ($d = 1; $d <= $days_in_month; $d++) {
                            $ts = strtotime("$current_year-$current_month-$d");
                            $ymd = date('Ymd', $ts);
                            $class = "calendar-day" . ($ymd === date('Ymd') ? " today" : "");
                            if (date('N', $ts) < 7 && $ts <= time()) {
                                echo "<a href='/borme/dias/" . date('Y/m/d', $ts) . "' class='$class'>$d</a>";
                            } else {
                                echo "<div class='$class' style='opacity: 0.3;'>$d</div>";
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- Recent Activity List -->
            <div class="grid-col-8" style="display: grid; gap: var(--space-4);">
                <?php
                $recent_days = [];
                for ($i = 0; $i < 4; $i++) {
                    $ts = strtotime("-$i days");
                    if (date('N', $ts) < 7)
                        $recent_days[] = $ts;
                    if (count($recent_days) >= 3)
                        break;
                }
                foreach ($recent_days as $ts):
                    $human = date('d/m/Y', $ts);
                    $day = date('d', $ts);
                    ?>
                    <a href="/borme/dias/<?= date('Y/m/d', $ts) ?>" class="v4-inner-card borme-day-card">
                        <div class="day-number">
                            <?= $day ?>
                        </div>
                        <div class="day-info">
                            <span class="meta"><?= $human ?></span>
                            <h4>Boletín Oficial del Registro Mercantil</h4>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="section-soft-bg">
        <div class="container">
            <h2 class="section-v3-title" style="margin-bottom: var(--space-6);">Inyecciones de Capital</h2>
            <div class="borme-grid">
                <?php
                try {
                    $db = Database::getInstance();
                    $stmt = $db->prepare("SELECT company_name, province, capital, type, date FROM borme_acts WHERE capital IS NOT NULL AND capital != '' ORDER BY length(capital) DESC, capital DESC LIMIT 6");
                    $stmt->execute();
                    $top_acts = $stmt->fetchAll();
                    foreach ($top_acts as $index => $act) {
                        $span = ($index < 2) ? 'grid-col-6' : 'grid-col-3';
                        $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($act['company_name']));
                        echo "<div class='v4-inner-card $span' style='display: flex; flex-direction: column; justify-content: space-between; min-height: 160px;'>
                            <div>
                                <span class='meta' style='font-size: 11px;'>{$act['province']} • " . date('d/m/Y', strtotime($act['date'])) . "</span>
                                <h4 style='margin-top: 8px;'><a href='/empresa/$slug' style='text-decoration: none; color: inherit;'>{$act['company_name']}</a></h4>
                            </div>
                            <div>
                                <div style='font-weight: 700; color: var(--brand-primary); font-size: " . ($index < 2 ? '1.5rem' : '1.25rem') . ";'>{$act['capital']}</div>
                                <span class='badge' style='margin-top: 8px;'>{$act['type']}</span>
                            </div>
                        </div>";
                    }
                } catch (Exception $e) {
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Final Features Grid -->
    <div class="borme-grid" style="border-top: 1px solid var(--border-linear); padding-top: var(--space-9);">
        <section class="grid-col-4 v4-inner-card">
            <h3 style="color: #fff; margin-bottom: 12px;">Ingeniería de Datos</h3>
            <p style="font-size: 14px; color: var(--text-muted);">Normalización y estructuración profunda de eventos
                mercantiles.</p>
        </section>
        <section class="grid-col-4 v4-inner-card">
            <h3 style="color: #fff; margin-bottom: 12px;">Ética & RGPD</h3>
            <p style="font-size: 14px; color: var(--text-muted);">Privacidad por diseño para una transparencia
                responsable.</p>
        </section>
        <section class="grid-col-4 v4-inner-card">
            <h3 style="color: #fff; margin-bottom: 12px;">API Centralizada</h3>
            <p style="font-size: 14px; color: var(--text-muted);">Acceso programático de alto rendimiento para
                analistas.</p>
        </section>
    </div>
</main>