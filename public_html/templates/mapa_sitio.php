<?php
// templates/mapa_sitio.php - Human Readable Sitemap

$db_path = file_exists(__DIR__ . '/../../pipeline/db/Database.php') ? __DIR__ . '/../../pipeline/db/Database.php' : __DIR__ . '/../pipeline/db/Database.php';
require_once $db_path;

$provinces = [
    'almeria' => 'Almería',
    'cadiz' => 'Cádiz',
    'cordoba' => 'Córdoba',
    'granada' => 'Granada',
    'huelva' => 'Huelva',
    'jaen' => 'Jaén',
    'malaga' => 'Málaga',
    'sevilla' => 'Sevilla',
    'huesca' => 'Huesca',
    'teruel' => 'Teruel',
    'zaragoza' => 'Zaragoza',
    'asturias' => 'Asturias',
    'illes-balears' => 'Illes Balears',
    'las-palmas' => 'Las Palmas',
    's-c-tenerife' => 'S.C. Tenerife',
    'cantabria' => 'Cantabria',
    'avila' => 'Ávila',
    'burgos' => 'Burgos',
    'leon' => 'León',
    'palencia' => 'Palencia',
    'salamanca' => 'Salamanca',
    'segovia' => 'Segovia',
    'soria' => 'Soria',
    'valladolid' => 'Valladolid',
    'zamora' => 'Zamora',
    'albacete' => 'Albacete',
    'ciudad-real' => 'Ciudad Real',
    'cuenca' => 'Cuenca',
    'guadalajara' => 'Guadalajara',
    'toledo' => 'Toledo',
    'barcelona' => 'Barcelona',
    'girona' => 'Girona',
    'lleida' => 'Lleida',
    'tarragona' => 'Tarragona',
    'badajoz' => 'Badajoz',
    'caceres' => 'Cáceres',
    'a-coruna' => 'A Coruña',
    'lugo' => 'Lugo',
    'ourense' => 'Ourense',
    'pontevedra' => 'Pontevedra',
    'madrid' => 'Madrid',
    'murcia' => 'Murcia',
    'navarra' => 'Navarra',
    'araba-alava' => 'Álava',
    'gipuzkoa' => 'Guipúzcoa',
    'bizkaia' => 'Bizkaia',
    'la-rioja' => 'La Rioja',
    'alicante' => 'Alicante',
    'castellon' => 'Castellón',
    'valencia' => 'Valencia',
    'ceuta' => 'Ceuta',
    'melilla' => 'Melilla'
];

try {
    $db = Database::getInstance();
    $stmt = $db->query("SELECT DISTINCT date FROM borme_acts ORDER BY date DESC LIMIT 20");
    $recent_dates = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $recent_dates = [];
}
?>

<div class="container" style="padding: var(--space-8) 0;">
    <h1 style="margin-bottom: var(--space-6); text-align: center; color: var(--brand-dark);">Mapa del Sitio (Sitemap)
    </h1>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: var(--space-8);">

        <!-- Section: General -->
        <section>
            <h2
                style="font-size: 1.25rem; margin-bottom: var(--space-4); border-bottom: 2px solid var(--brand-primary); padding-bottom: 8px;">
                Navegación General</h2>
            <ul style="list-style: none; display: grid; gap: 12px;">
                <li><a href="/" style="text-decoration: none; color: var(--text-primary); font-weight: 500;">Inicio /
                        Portada</a></li>
                <li><a href="/buscar" style="text-decoration: none; color: var(--text-primary);">Buscador de
                        Empresas</a></li>
                <li><a href="/provincias" style="text-decoration: none; color: var(--text-primary);">Mapa de
                        Provincias</a></li>
                <li><a href="/descargas" style="text-decoration: none; color: var(--text-primary);">Descarga de Datos
                        Abiertos</a></li>
                <li><a href="/api" style="text-decoration: none; color: var(--text-primary);">API para
                        Desarrolladores</a></li>
                <li><a href="/metodologia" style="text-decoration: none; color: var(--text-primary);">Metodología de
                        Extracción</a></li>
                <li><a href="/fuentes" style="text-decoration: none; color: var(--text-primary);">Fuentes del BOE</a>
                </li>
            </ul>
        </section>

        <!-- Section: Provincias -->
        <section>
            <h2
                style="font-size: 1.25rem; margin-bottom: var(--space-4); border-bottom: 2px solid var(--brand-primary); padding-bottom: 8px;">
                Explorar por Provincia</h2>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 0.9rem;">
                <?php foreach ($provinces as $slug => $name): ?>
                    <a href="/borme/provincia/<?= $slug ?>" style="text-decoration: none; color: var(--text-secondary);">
                        <?= $name ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Section: Ultimos Boletines -->
        <section>
            <h2
                style="font-size: 1.25rem; margin-bottom: var(--space-4); border-bottom: 2px solid var(--brand-primary); padding-bottom: 8px;">
                Últimos Boletines</h2>
            <ul style="list-style: none; display: grid; gap: 10px;">
                <?php foreach ($recent_dates as $date):
                    $y = substr($date, 0, 4);
                    $m = substr($date, 4, 2);
                    $d = substr($date, 6, 2);
                    ?>
                    <li><a href="/borme/dias/<?= "$y/$m/$d" ?>" style="text-decoration: none; color:
                        var(--text-primary);">BOE del
                            <?= "$d/$m/$y" ?>
                        </a></li>
                <?php endforeach; ?>
                <li><a href="/borme/dias"
                        style="text-decoration: none; color: var(--brand-primary); font-weight: 600;">Ver histórico
                        completo &rarr;</a></li>
            </ul>

            <h2
                style="font-size: 1.25rem; margin-top: var(--space-7); margin-bottom: var(--space-4); border-bottom: 2px solid var(--brand-primary); padding-bottom: 8px;">
                Legal</h2>
            <ul style="list-style: none; display: grid; gap: 8px; font-size: 0.9rem;">
                <li><a href="/aviso-legal" style="text-decoration: none; color: var(--text-muted);">Aviso Legal</a></li>
                <li><a href="/privacidad" style="text-decoration: none; color: var(--text-muted);">Política de
                        Privacidad</a></li>
                <li><a href="/cookies" style="text-decoration: none; color: var(--text-muted);">Política de Cookies</a>
                </li>
                <li><a href="/manifiesto" style="text-decoration: none; color: var(--text-muted);">Manifiesto
                        Técnico</a></li>
            </ul>
        </section>

    </div>
</div>