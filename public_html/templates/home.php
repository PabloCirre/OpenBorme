<?php require_once __DIR__ . '/../../pipeline/db/Database.php'; ?>
<main class="container" style="padding: var(--space-8) 0;">
    <div class="hero-section" style="text-align: center; max-width: 900px; margin: 0 auto var(--space-8);">
        <h1 style="margin-bottom: var(--space-3); color: var(--brand-dark);">Visualiza el Registro Mercantil <br>como
            nunca antes</h1>
        <p style="font-size: 1.25rem; margin-bottom: var(--space-6); color: var(--text-muted);">
            OpenBorme estructura, normaliza y democratiza el acceso a los datos societarios de España.
            Sin publicidad, sin rastreo, solo datos puros.
        </p>

        <form action="/buscar" method="GET" class="hero-search-form"
            style="display: flex; gap: var(--space-3); max-width: 680px; margin: 0 auto; background: white; padding: var(--space-3); border-radius: var(--radius-md); border: 2px solid var(--brand-primary); box-shadow: var(--shadow-md);">
            <input type="text" name="q" class="input-main" placeholder="Ej: Inditex, construcciones, B81234567..."
                style="flex: 1; border: none; font-size: 1.1rem; outline: none; padding-left: var(--space-4);">
            <button type="submit" class="btn btn-primary"
                style="height: 52px; padding: 0 var(--space-6); border-radius: var(--radius-sm);">BUSCAR</button>
        </form>

        <div
            style="margin-top: var(--space-5); display: flex; flex-wrap: wrap; justify-content: center; gap: var(--space-2);">
            <a href="/borme/dias" class="badge"
                style="padding: 8px 16px; border-radius: var(--radius-sm); border: 1px solid var(--border-strong); background: white;">Hoy</a>
            <a href="/buscar?date=yesterday" class="badge"
                style="padding: 8px 16px; border-radius: var(--radius-sm); border: 1px solid var(--border-strong); background: white;">Ayer</a>
            <a href="/provincias" class="badge"
                style="padding: 8px 16px; border-radius: var(--radius-sm); border: 1px solid var(--border-strong); background: white;">Provincias</a>
        </div>
    </div>

    <div style="margin: var(--space-8) 0;">
        <h2 style="text-align: center; margin-bottom: var(--space-6); color: var(--brand-dark);">Últimos Boletines
            Publicados</h2>

        <div class="results-layout">
            <!-- Calendar Widget -->
            <div style="grid-column: span 4;">
                <div class="calendar-widget">
                    <?php
                    $today_ts = strtotime(date('Y-m-d'));

                    // Selected Date Logic
                    $selected_date_str = $_GET['date'] ?? null; // YYYYMMDD
                    if ($selected_date_str) {
                        // Parse selected date to show its month
                        $sel_year = substr($selected_date_str, 0, 4);
                        $sel_month = substr($selected_date_str, 4, 2);
                        $current_year = $sel_year;
                        $current_month = $sel_month;
                    } else {
                        $current_month = date('n');
                        $current_year = date('Y');
                    }

                    $days_in_month = cal_days_in_month(CAL_GREGORIAN, $current_month, $current_year);
                    $first_day_of_week = date('N', strtotime("$current_year-$current_month-01")); // 1 (Mon) - 7 (Sun)
                    
                    $month_names = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
                    ?>
                    <div class="calendar-header">
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
                        // Empty cells before start
                        for ($i = 1; $i < $first_day_of_week; $i++) {
                            echo '<div class="calendar-day empty"></div>';
                        }

                        // Days
                        for ($d = 1; $d <= $days_in_month; $d++) {
                            $ts = strtotime("$current_year-$current_month-$d");
                            $date_ymd = date('Ymd', $ts);

                            $is_today = ($date_ymd === date('Ymd'));
                            $is_selected = ($date_ymd === $selected_date_str);
                            $weekday = date('N', $ts);
                            $is_weekend = ($weekday >= 7);
                            $is_future = ($ts > $today_ts);

                            $class = "calendar-day";
                            if ($is_today)
                                $class .= " today";
                            if ($is_selected)
                                $class .= " selected";
                            if ($is_future)
                                $class .= " disabled";

                            // Link - Only if valid
                            if (!$is_weekend && !$is_future) {
                                // Link to ?date=YYYYMMDD to reload home with detail
                                echo "<a href='?date=$date_ymd' class='$class'>$d</a>";
                            } else {
                                echo "<div class='$class' style='color: var(--text-muted); opacity: 0.5; cursor: default;'>$d</div>";
                            }
                        }
                        ?>
                    </div>
                    <div style="margin-top: var(--space-3); text-align: center;">
                        <!-- Navigation could be improved, simplification -->
                        <div style="display: flex; justify-content: space-between; font-size: 0.8rem;">
                            <?php
                            $prev_ts = strtotime("$current_year-$current_month-01 -1 month");
                            $next_ts = strtotime("$current_year-$current_month-01 +1 month");
                            ?>
                            <a href="?date=<?= date('Ymd', $prev_ts) ?>" class="btn btn-ghost btn-s">&larr; Ant</a>
                            <?php if ($next_ts < time()): ?>
                                <a href="?date=<?= date('Ymd', $next_ts) ?>" class="btn btn-ghost btn-s">Sig &rarr;</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- List / Detail View -->
            <div style="grid-column: span 8; display: grid; gap: var(--space-4);">
                <?php
                if ($selected_date_str) {
                    // --- DETAIL MODE: Show bulletins for selected date ---
                    require_once __DIR__ . '/../lib/BoeScraper.php';
                    $scraper = new BoeScraper();
                    $summary = $scraper->getSummary($selected_date_str);

                    $sel_day = substr($selected_date_str, 6, 2);
                    $sel_mon = substr($selected_date_str, 4, 2);
                    $sel_yr = substr($selected_date_str, 0, 4);
                    $hum_date = "$sel_day/$sel_mon/$sel_yr";
                    $boe_link = "https://www.boe.es/borme/dias/$sel_yr/$sel_mon/$sel_day/";
                    // Route to SUMARIO template for Full View
                    $url_full = "/borme/sumario/$sel_yr/$sel_mon/$sel_day";

                    echo "<div>";
                    echo "<div style='display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-4);'>";
                    echo "<h2 style='color: var(--brand-dark); margin: 0;'>Boletines del $hum_date</h2>";
                    echo "<a href='$boe_link' target='_blank' class='btn btn-ghost btn-s'>Ver en BOE.es &rarr;</a>";
                    echo "</div>";

                    if (!empty($summary['sections'])) {
                        foreach ($summary['sections'] as $sec_name => $provinces) {
                            echo "<div style='margin-bottom: var(--space-5);'>";
                            echo "<h4 style='color: var(--text-secondary); text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.05em; border-bottom: 2px solid var(--border-subtle); padding-bottom: 8px; margin-bottom: 12px;'>$sec_name</h4>";
                            echo "<div style='display: flex; flex-wrap: wrap; gap: 8px;'>";

                            ksort($provinces); // Alphabetical order
                
                            foreach ($provinces as $prov_name => $items) {
                                // Clean Province Name for Display
                                $clean_name = str_replace(['PROVINCIA ', 'SECCIÓN ESPECIAL '], '', $prov_name);
                                $display_name = ucwords(strtolower($clean_name));
                                $count = count($items);

                                // Create Slug for URL
                                $slug = strtolower(trim($clean_name));
                                $slug = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ', '/'], ['a', 'e', 'i', 'o', 'u', 'n', '-'], $slug);
                                $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
                                $slug = preg_replace('/-+/', '-', $slug);
                                $slug = trim($slug, '-');

                                // Link to PROVINCE view
                                $prov_url = "/borme/provincia/$slug/$sel_yr/$sel_mon/$sel_day";

                                echo "<a href='$prov_url' class='badge' style='background: white; border: 1px solid var(--border-strong); text-decoration: none; color: var(--text-main); padding: 6px 12px; display: flex; align-items: center; gap: 6px; transition: all 0.2s ease;' onmouseover=\"this.style.borderColor='var(--brand-primary)'; this.style.color='var(--brand-primary)';\" onmouseout=\"this.style.borderColor='var(--border-strong)'; this.style.color='var(--text-main)';\">";
                                echo "<span style='font-weight: 600;'>$display_name</span>";
                                echo "<span style='background: var(--bg-tag); font-size: 0.75rem; padding: 2px 6px; border-radius: 4px; color: var(--text-muted);'>$count</span>";
                                echo "</a>";
                            }
                            echo "</div>";
                            echo "</div>";
                        }

                        // Big Button
                        echo "<div style='margin-top: var(--space-4); text-align: center;'>";
                        echo "<a href='$url_full' class='btn btn-primary btn-m' style='width: 100%; justify-content: center;'>Ver Todos los Documentos &rarr;</a>";
                        echo "</div>";

                    } elseif ($summary['error']) {
                        echo "<div class='card' style='padding: var(--space-4); color: var(--error); background: var(--bg-error);'>⚠️ " . $summary['error'] . "</div>";
                    } else {
                        echo "<div class='card' style='padding: var(--space-4);'>No hay datos disponibles para esta fecha.</div>";
                    }
                    echo "</div>";

                } else {
                    // --- DEFAULT MODE: Recent Bulletins ---
                    // Generate last 3 working days (approximate)
                    $dates = [];
                    for ($i = 0; $i < 5; $i++) {
                        $ts = strtotime("-$i days");
                        if (date('N', $ts) < 7) { // Mon-Sat
                            $dates[] = $ts;
                        }
                        if (count($dates) >= 3)
                            break;
                    }

                    foreach ($dates as $ts):
                        $date_str = date('Ymd', $ts);
                        $human_date = date('d/m/Y', $ts);
                        $day_num = date('d', $ts);
                        $is_today = (date('Ymd') === $date_str);

                        // Clean URL Construction
                        $url_clean = "/borme/dias/" . date('Y/m/d', $ts);
                        ?>
                        <a href="<?= $url_clean ?>" class="inst-card date-card-hover"
                            style="text-decoration: none; color: inherit; padding: var(--space-4); display: flex; align-items: center; gap: var(--space-5); transition: transform 0.2s ease, box-shadow 0.2s ease;">

                            <!-- Big Number -->
                            <div
                                style="font-size: 2.5rem; font-weight: 800; line-height: 1; color: <?= $is_today ? 'var(--brand-primary)' : 'var(--text-secondary)' ?>;">
                                <?= $day_num ?>
                            </div>

                            <!-- Date & Title -->
                            <div style="border-left: 2px solid var(--border-subtle); padding-left: var(--space-4);">
                                <div class="mono" style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 2px;">
                                    <?= $human_date ?>
                                </div>
                                <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--brand-dark); margin: 0;">Sumario
                                    del Boletín</h3>
                            </div>

                            <!-- Arrow -->
                            <div style="margin-left: auto; color: var(--brand-primary); font-size: 1.5rem;">
                                &rarr;
                            </div>
                        </a>
                    <?php endforeach;
                }
                ?>
            </div>
        </div>
    </div>

    <!-- PREMIUM FEATURE: Top Capital Injections -->
    <div style="margin: var(--space-8) 0; padding-top: var(--space-6); border-top: 1px solid var(--border-subtle);">
        <h2 style="text-align: center; margin-bottom: var(--space-2); color: var(--brand-dark);">💰 Top Inversiones de
            Capital</h2>
        <p style="text-align: center; color: var(--text-muted); margin-bottom: var(--space-5);">Las mayores inyecciones
            de capital registradas recientemente en España.</p>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: var(--space-4);">
            <?php
            try {
                $db = Database::getInstance();
                $stmt = $db->prepare("
                    SELECT id, date, company_name, province, capital, type 
                    FROM borme_acts 
                    WHERE capital IS NOT NULL AND capital != '' 
                    ORDER BY length(capital) DESC, capital DESC LIMIT 6
                ");
                $stmt->execute();
                $top_acts = $stmt->fetchAll();

                foreach ($top_acts as $act) {
                    $c_date = date('d/m/Y', strtotime($act['date']));
                    // Format Capital String
                    $cap_str = $act['capital'];
                    $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($act['company_name']));
                    echo "<div class='card' style='padding: var(--space-4); border-left: 4px solid var(--brand-primary); transition: transform 0.2s; cursor: pointer;' onmouseover=\"this.style.transform='translateY(-4px)'\" onmouseout=\"this.style.transform='translateY(0)'\">
                        <div style='font-size: 0.8rem; color: var(--text-muted); margin-bottom: 4px; display: flex; justify-content: space-between;'>
                            <span>{$act['province']}</span>
                            <span>$c_date</span>
                        </div>
                        <h4 style='margin-bottom: 8px; color: var(--brand-dark); font-size: 1.1rem;'>
                            <a href='/empresa/$slug' style='text-decoration: none; color: inherit;'>{$act['company_name']}</a>
                        </h4>
                        <div style='font-weight: 700; color: #2e7d32; font-size: 1.25rem; margin-bottom: 4px;'>$cap_str</div>
                        <div style='font-size: 0.85rem; color: var(--text-secondary); background: var(--bg-alt); padding: 2px 6px; border-radius: 4px; display: inline-block;'>{$act['type']}</div>
                    </div>";
                }
            } catch (Exception $e) {
                echo "<p style='color: var(--error); text-align: center;'>Generando estadísticas en tiempo real...</p>";
            }
            ?>
        </div>
        <div style="text-align: center; margin-top: var(--space-4);">
            <a href="/ranking-capital" class="btn btn-ghost">Ver listado completo &rarr;</a>
        </div>
    </div>

    <!-- Feature Cards -->
    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: var(--space-6); border-top: 1px solid var(--border-subtle); padding-top: var(--space-8);">
        <section class="inst-card" style="padding: var(--space-6);">
            <div style="font-size: 2rem; margin-bottom: var(--space-3); color: var(--brand-primary);">🏗️</div>
            <h3 style="margin-bottom: var(--space-3); color: var(--brand-dark);">Ingeniería de Datos</h3>
            <p>
                No solo volcamos texto. OpenBorme normaliza entidades, detecta tipos de actos y vincula eventos
                para crear un historial coherente de cada empresa.
            </p>
        </section>

        <section class="inst-card" style="padding: var(--space-6);">
            <div style="font-size: 2rem; margin-bottom: var(--space-3); color: var(--brand-primary);">⚖️</div>
            <h3 style="margin-bottom: var(--space-3); color: var(--brand-dark);">Ética por Diseño</h3>
            <p>
                Respetamos estrictamente el RGPD. Limitamos la visibilidad de datos personales y evitamos el profiling
                masivo para centrarnos en transparencia corporativa.
            </p>
        </section>

        <section class="inst-card" style="padding: var(--space-6);">
            <div style="font-size: 2rem; margin-bottom: var(--space-3); color: var(--brand-primary);">🚀</div>
            <h3 style="margin-bottom: var(--space-3); color: var(--brand-dark);">API & Datasets</h3>
            <p>
                Construido para desarrolladores e IAs. Accede a dumps masivos en formatos eficientes o integra
                nuestra API en tus flujos de trabajo.
            </p>
            <div style="margin-top: var(--space-4);">
                <a href="/api" class="btn btn-secondary btn-s" style="border-radius: var(--radius-md);">Explorar API
                    &rarr;</a>
            </div>
        </section>
    </div>
</main>