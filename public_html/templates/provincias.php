<?php
// templates/provincias.php - Interactive Map of Spain
require_once __DIR__ . '/../../pipeline/db/Database.php';
?>

<style>
    .map-container svg {
        width: 100%;
        height: auto;
        max-height: 800px;
    }

    .provincia {
        fill: #e0e0e0;
        stroke: #ffffff;
        stroke-width: 0.5;
        cursor: pointer;
        transition: fill 0.3s ease, transform 0.2s ease;
    }

    .provincia:hover {
        fill: var(--brand-primary);
        transform: translateY(-2px);
        filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
        z-index: 10;
    }

    .tooltip {
        position: absolute;
        background: #333;
        color: white;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 12px;
        pointer-events: none;
        display: none;
        z-index: 100;
        white-space: nowrap;
    }
</style>

<div class="container" style="padding: var(--space-8) 0;">
    <div style="text-align: center; margin-bottom: var(--space-6);">
        <h1 style="color: var(--brand-dark);">Mapa Interactivo del BORME</h1>
        <p style="color: var(--text-muted);">Selecciona una provincia para acceder a su histórico de publicaciones.</p>
    </div>

    <div class="map-container"
        style="position: relative; max-width: 900px; margin: 0 auto; background: var(--bg-alt); border-radius: var(--radius-lg); padding: var(--space-4);">
        <div id="tooltip" class="tooltip"></div>

        <?php
        // Map Paths (Simplified for common provinces)
        // IDs match strict slug logic: alicante, valencia, etc.
        ?>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 800">
            <!-- Andalucia -->
            <a href="/borme/provincia/almeria">
                <path id="almeria" title="Almería" class="provincia" d="M635,660 l30,10 l-10,30 l-30,-10 z" />
            </a>
            <a href="/borme/provincia/cadiz">
                <path id="cadiz" title="Cádiz" class="provincia" d="M380,720 l40,-10 l10,40 l-50,-10 z" />
            </a>
            <a href="/borme/provincia/cordoba">
                <path id="cordoba" title="Córdoba" class="provincia" d="M430,620 l50,10 l-10,50 l-50,-10 z" />
            </a>
            <a href="/borme/provincia/granada">
                <path id="granada" title="Granada" class="provincia" d="M530,660 l60,10 l-10,40 l-50,-30 z" />
            </a>
            <a href="/borme/provincia/huelva">
                <path id="huelva" title="Huelva" class="provincia" d="M320,660 l40,10 l-10,50 l-40,-30 z" />
            </a>
            <a href="/borme/provincia/jaen">
                <path id="jaen" title="Jaén" class="provincia" d="M500,600 l50,20 l-20,50 l-40,-30 z" />
            </a>
            <a href="/borme/provincia/malaga">
                <path id="malaga" title="Málaga" class="provincia" d="M480,700 l50,10 l-10,30 l-60,-20 z" />
            </a>
            <a href="/borme/provincia/sevilla">
                <path id="sevilla" title="Sevilla" class="provincia" d="M370,640 l50,10 l10,50 l-50,-20 z" />
            </a>

            <!-- Aragon -->
            <a href="/borme/provincia/huesca">
                <path id="huesca" title="Huesca" class="provincia" d="M680,260 l60,20 l-20,60 l-50,-20 z" />
            </a>
            <a href="/borme/provincia/teruel">
                <path id="teruel" title="Teruel" class="provincia" d="M650,350 l50,20 l-10,60 l-60,-20 z" />
            </a>
            <a href="/borme/provincia/zaragoza">
                <path id="zaragoza" title="Zaragoza" class="provincia" d="M600,300 l60,10 l10,60 l-60,-20 z" />
            </a>

            <!-- Asturias -->
            <a href="/borme/provincia/asturias">
                <path id="asturias" title="Asturias" class="provincia" d="M320,80 l80,10 l-10,40 l-90,-10 z" />
            </a>

            <!-- Baleares -->
            <a href="/borme/provincia/illes-balears">
                <path id="illes-balears" title="Illes Balears" class="provincia"
                    d="M850,450 l50,-20 l10,40 l-50,20 z" />
            </a>

            <!-- Canarias (Inset) -->
            <g transform="translate(100, 650) scale(1.5)">
                <rect x="0" y="0" width="120" height="80" fill="none" stroke="#ccc" stroke-dasharray="2,2" />
                <a href="/borme/provincia/las-palmas">
                    <path id="las-palmas" title="Las Palmas" class="provincia" d="M60,20 l40,10 l-10,30 l-20,-10 z" />
                </a>
                <a href="/borme/provincia/s-c-tenerife">
                    <path id="s-c-tenerife" title="S.C. Tenerife" class="provincia"
                        d="M10,30 l40,-10 l10,30 l-40,10 z" />
                </a>
            </g>

            <!-- Cantabria -->
            <a href="/borme/provincia/cantabria">
                <path id="cantabria" title="Cantabria" class="provincia" d="M410,90 l50,10 l-5,30 l-45,-10 z" />
            </a>

            <!-- Castilla y Leon -->
            <a href="/borme/provincia/avila">
                <path id="avila" title="Ávila" class="provincia" d="M400,360 l40,10 l-10,40 l-40,-20 z" />
            </a>
            <a href="/borme/provincia/burgos">
                <path id="burgos" title="Burgos" class="provincia" d="M460,220 l60,20 l-20,60 l-50,-20 z" />
            </a>
            <a href="/borme/provincia/leon">
                <path id="leon" title="León" class="provincia" d="M300,180 l60,20 l-20,80 l-50,-40 z" />
            </a>
            <a href="/borme/provincia/palencia">
                <path id="palencia" title="Palencia" class="provincia" d="M380,200 l40,10 l-5,60 l-40,-10 z" />
            </a>
            <a href="/borme/provincia/salamanca">
                <path id="salamanca" title="Salamanca" class="provincia" d="M280,300 l60,20 l-10,60 l-60,-40 z" />
            </a>
            <a href="/borme/provincia/segovia">
                <path id="segovia" title="Segovia" class="provincia" d="M420,320 l40,10 l-10,40 l-40,-10 z" />
            </a>
            <a href="/borme/provincia/soria">
                <path id="soria" title="Soria" class="provincia" d="M530,280 l40,10 l-10,50 l-40,-20 z" />
            </a>
            <a href="/borme/provincia/valladolid">
                <path id="valladolid" title="Valladolid" class="provincia" d="M380,280 l60,10 l-10,40 l-60,-20 z" />
            </a>
            <a href="/borme/provincia/zamora">
                <path id="zamora" title="Zamora" class="provincia" d="M320,260 l50,10 l-10,50 l-50,-30 z" />
            </a>

            <!-- Castilla La Mancha -->
            <a href="/borme/provincia/albacete">
                <path id="albacete" title="Albacete" class="provincia" d="M580,500 l60,20 l-20,60 l-60,-30 z" />
            </a>
            <a href="/borme/provincia/ciudad-real">
                <path id="ciudad-real" title="Ciudad Real" class="provincia" d="M450,450 l80,20 l-10,80 l-90,-40 z" />
            </a>
            <a href="/borme/provincia/cuenca">
                <path id="cuenca" title="Cuenca" class="provincia" d="M550,400 l60,30 l-20,70 l-60,-40 z" />
            </a>
            <a href="/borme/provincia/guadalajara">
                <path id="guadalajara" title="Guadalajara" class="provincia" d="M520,340 l50,20 l-20,50 l-50,-20 z" />
            </a>
            <a href="/borme/provincia/toledo">
                <path id="toledo" title="Toledo" class="provincia" d="M450,400 l60,20 l-20,40 l-60,-20 z" />
            </a>

            <!-- Cataluña -->
            <a href="/borme/provincia/barcelona">
                <path id="barcelona" title="Barcelona" class="provincia" d="M780,300 l40,10 l-20,60 l-40,-20 z" />
            </a>
            <a href="/borme/provincia/girona">
                <path id="girona" title="Girona" class="provincia" d="M800,240 l50,20 l-10,40 l-60,-20 z" />
            </a>
            <a href="/borme/provincia/lleida">
                <path id="lleida" title="Lleida" class="provincia" d="M720,280 l50,20 l-20,40 l-60,-20 z" />
            </a>
            <a href="/borme/provincia/tarragona">
                <path id="tarragona" title="Tarragona" class="provincia" d="M740,340 l50,20 l-10,40 l-50,-20 z" />
            </a>

            <!-- Extremadura -->
            <a href="/borme/provincia/badajoz">
                <path id="badajoz" title="Badajoz" class="provincia" d="M250,500 l80,20 l-10,80 l-80,-40 z" />
            </a>
            <a href="/borme/provincia/caceres">
                <path id="caceres" title="Cáceres" class="provincia" d="M260,400 l80,20 l-10,80 l-80,-40 z" />
            </a>

            <!-- Galicia -->
            <a href="/borme/provincia/a-coruna">
                <path id="a-coruna" title="A Coruña" class="provincia" d="M150,80 l60,10 l-10,60 l-60,-20 z" />
            </a>
            <a href="/borme/provincia/lugo">
                <path id="lugo" title="Lugo" class="provincia" d="M220,100 l50,10 l-10,60 l-50,-20 z" />
            </a>
            <a href="/borme/provincia/ourense">
                <path id="ourense" title="Ourense" class="provincia" d="M210,180 l50,10 l-10,40 l-50,-20 z" />
            </a>
            <a href="/borme/provincia/pontevedra">
                <path id="pontevedra" title="Pontevedra" class="provincia" d="M140,160 l50,10 l-10,50 l-50,-20 z" />
            </a>

            <!-- Madrid -->
            <a href="/borme/provincia/madrid">
                <path id="madrid" title="Madrid" class="provincia" d="M460,350 l40,10 l10,40 l-40,10 z"
                    style="fill:#ffe082;" />
            </a>

            <!-- Murcia -->
            <a href="/borme/provincia/murcia">
                <path id="murcia" title="Murcia" class="provincia" d="M600,580 l50,10 l-10,50 l-50,-20 z" />
            </a>

            <!-- Navarra -->
            <a href="/borme/provincia/navarra">
                <path id="navarra" title="Navarra" class="provincia" d="M580,200 l40,20 l-10,60 l-40,-40 z" />
            </a>

            <!-- Pais Vasco -->
            <a href="/borme/provincia/araba-alava">
                <path id="araba-alava" title="Álava" class="provincia" d="M520,180 l30,10 l-5,30 l-30,-10 z" />
            </a>
            <a href="/borme/provincia/gipuzkoa">
                <path id="gipuzkoa" title="Guipúzcoa" class="provincia" d="M560,160 l20,10 l-5,20 l-20,-10 z" />
            </a>
            <a href="/borme/provincia/bizkaia">
                <path id="bizkaia" title="Bizkaia" class="provincia" d="M510,140 l30,10 l-5,20 l-30,-10 z" />
            </a>

            <!-- La Rioja -->
            <a href="/borme/provincia/la-rioja">
                <path id="la-rioja" title="La Rioja" class="provincia" d="M500,220 l40,10 l-5,20 l-40,-10 z" />
            </a>

            <!-- Valencia -->
            <a href="/borme/provincia/alicante">
                <path id="alicante" title="Alicante" class="provincia" d="M650,520 l40,20 l-20,40 l-40,-20 z" />
            </a>
            <a href="/borme/provincia/castellon">
                <path id="castellon" title="Castellón" class="provincia" d="M660,400 l40,20 l-20,60 l-40,-30 z" />
            </a>
            <a href="/borme/provincia/valencia">
                <path id="valencia" title="Valencia" class="provincia" d="M630,460 l50,20 l-10,50 l-50,-20 z" />
            </a>

            <!-- Ceuta Melilla -->
            <a href="/borme/provincia/ceuta">
                <circle cx="400" cy="780" r="10" title="Ceuta" class="provincia" />
            </a>
            <a href="/borme/provincia/melilla">
                <circle cx="550" cy="780" r="10" title="Melilla" class="provincia" />
            </a>

        </svg>

        <div style="margin-top: 1rem; text-align: center; color: var(--text-muted); font-size: 0.9rem;">
            * Mapa esquemático interactivo.
        </div>
    </div>

    <!-- PREMIUM FEATURE: Radar Geográfico de Provincias -->
    <div
        style="margin: var(--space-8) auto; max-width: 900px; padding-top: var(--space-6); border-top: 1px solid var(--border-subtle);">
        <h2 style="text-align: center; margin-bottom: var(--space-2); color: var(--brand-dark);">📡 Pulso Empresarial
            por Provincia</h2>
        <p style="text-align: center; color: var(--text-muted); margin-bottom: var(--space-5);">Métricas de actividad
            registradas recientemente.</p>

        <div class="card" style="overflow-x: auto; padding: 0;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: var(--bg-alt); border-bottom: 2px solid var(--border-strong);">
                        <th style="padding: var(--space-3) var(--space-4);">Provincia</th>
                        <th style="padding: var(--space-3) var(--space-4); text-align: right;">Total Actos</th>
                        <th style="padding: var(--space-3) var(--space-4); text-align: right; color: var(--success);">
                            Constituciones / Ampliaciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        $db = Database::getInstance();
                        $stmt = $db->prepare("
                            SELECT province, COUNT(*) as total_acts,
                                   SUM(CASE WHEN type LIKE '%Constitu%' OR type LIKE '%Ampliaci%' THEN 1 ELSE 0 END) as inyecciones
                            FROM borme_acts
                            GROUP BY province
                            ORDER BY total_acts DESC
                            LIMIT 15
                        ");
                        $stmt->execute();
                        $province_stats = $stmt->fetchAll();

                        foreach ($province_stats as $stat) {
                            $slug = strtolower($stat['province']);
                            echo "<tr style='border-bottom: 1px solid var(--border-light); transition: background 0.2s;' onmouseover=\"this.style.background='var(--bg-tag)'\" onmouseout=\"this.style.background='transparent'\">
                                <td style='padding: var(--space-3) var(--space-4); font-weight: 600;'><a href='/borme/provincia/$slug' style='text-decoration: none; color: var(--brand-dark);'>{$stat['province']}</a></td>
                                <td style='padding: var(--space-3) var(--space-4); text-align: right; font-variant-numeric: tabular-nums;'>{$stat['total_acts']}</td>
                                <td style='padding: var(--space-3) var(--space-4); text-align: right; font-variant-numeric: tabular-nums; color: var(--success); font-weight: 700;'>{$stat['inyecciones']}</td>
                            </tr>";
                        }
                    } catch (Exception $e) {
                        echo "<tr><td colspan='4' style='padding: var(--space-4); text-align: center; color: var(--error);'>Cargando radares de actividad...</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.querySelectorAll('.provincia').forEach(item => {
            item.addEventListener('mouseenter', e => {
                const tooltip = document.getElementById('tooltip');
                tooltip.textContent = e.target.getAttribute('title');
                tooltip.style.display = 'block';
                tooltip.style.left = e.pageX + 'px';
                tooltip.style.top = (e.pageY - 30) + 'px';
            });
            item.addEventListener('mouseleave', () => {
                document.getElementById('tooltip').style.display = 'none';
            });
        });
    </script>
</div>