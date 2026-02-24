<?php
/**
 * OpenBorme Central Router
 * Handles all dynamic requests and loads appropriate templates.
 */

// Bootstrap ligero de SQLite: crea archivo/esquema si no existen.
$db_bootstrap_file = null;
$db_bootstrap_candidates = [
    __DIR__ . '/../pipeline/db/Database.php',
    __DIR__ . '/pipeline/db/Database.php',
];
foreach ($db_bootstrap_candidates as $candidate) {
    if (file_exists($candidate)) {
        $db_bootstrap_file = $candidate;
        break;
    }
}

if ($db_bootstrap_file) {
    require_once $db_bootstrap_file;
    try {
        Database::getInstance();
    } catch (Throwable $e) {
        // Deferimos el error a templates/endpoints que realmente usen DB.
    }
}

$request_uri = $_SERVER['REQUEST_URI'];
$base_path = parse_url($request_uri, PHP_URL_PATH);
$path_parts = explode('/', trim($base_path, '/'));

// --- Route Mapping ---
$routes = [
    '' => ['template' => 'home.php', 'index' => 'index'],
    'buscar' => ['template' => 'search.php', 'index' => 'index'],
    'busqueda-avanzada' => ['template' => 'search_advanced.php', 'index' => 'index'],
    'resultados' => ['template' => 'search.php', 'index' => 'noindex'],
    'sitemap.xml' => ['template' => 'sitemap_index.xml.php', 'index' => 'index'],
    'sitemap-estatico.xml' => ['template' => 'sitemap_static.xml.php', 'index' => 'index'],
    'sitemap-provincias.xml' => ['template' => 'sitemap_provincias.xml.php', 'index' => 'index'],
    'sitemap-fechas.xml' => ['template' => 'sitemap_dates.xml.php', 'index' => 'index'],
    'sitemap-empresas.xml' => ['template' => 'sitemap_companies.xml.php', 'index' => 'index'],
    'mapa-del-sitio' => ['template' => 'mapa_sitio.php', 'index' => 'index', 'title' => 'Mapa del Sitio'],
    'explorar' => ['template' => 'home.php', 'index' => 'index', 'title' => 'Explorar BORME'],
    'exportar' => ['template' => 'export.php', 'index' => 'index'],
    'diario_borme' => ['template' => 'home.php', 'index' => 'index'],
    'diario_borme/ayuda.php' => ['template' => 'static.php', 'index' => 'index', 'title' => 'Ayuda del BORME'],
    'seccion/actos-inscritos' => ['template' => 'landing_seo.php', 'index' => 'index', 'title' => 'Actos Inscritos'],
    'seccion/anuncios' => ['template' => 'landing_seo.php', 'index' => 'index', 'title' => 'Anuncios y Avisos'],
    'provincias' => ['template' => 'provincias.php', 'index' => 'index'],
    'nuevas-empresas' => ['template' => 'new_companies.php', 'index' => 'index', 'title' => 'Nuevas Empresas'],
    'tipos-de-actos' => ['template' => 'tipos_actos.php', 'index' => 'index'],
    'secciones' => ['template' => 'tipos_actos.php', 'index' => 'index', 'title' => 'Secciones del BORME'],
    'empresas' => ['template' => 'search.php', 'index' => 'index', 'title' => 'Buscar Empresas'],
    'personas' => ['template' => 'static.php', 'index' => 'noindex', 'title' => 'Búsqueda de Personas'],
    'alertas' => ['template' => 'static.php', 'index' => 'index', 'title' => 'Alertas del BORME'],
    'mi-cuenta/acceso' => ['template' => 'static.php', 'index' => 'noindex', 'title' => 'Acceso'],
    'descargas' => ['template' => 'descargas.php', 'index' => 'index'],
    'api' => ['template' => 'api_landing.php', 'index' => 'index'],
    'api/documentacion' => ['template' => 'api_docs.php', 'index' => 'index'],
    'metodologia' => ['template' => 'static.php', 'index' => 'index', 'title' => 'Metodología de Extracción'],
    'fuentes' => ['template' => 'static.php', 'index' => 'index', 'title' => 'Fuentes de Información'],
    'aviso-legal' => ['template' => 'static.php', 'index' => 'index', 'title' => 'Aviso Legal'],
    'terminos-de-uso' => ['template' => 'static.php', 'index' => 'index', 'title' => 'Términos de Uso'],
    'exencion-responsabilidad' => ['template' => 'static.php', 'index' => 'index', 'title' => 'Exención de Responsabilidad'],
    'privacidad' => ['template' => 'static.php', 'index' => 'index', 'title' => 'Política de Privacidad'],
    'cookies' => ['template' => 'static.php', 'index' => 'index', 'title' => 'Política de Cookies'],
    'faq' => ['template' => 'static.php', 'index' => 'index', 'title' => 'FAQ'],
    'contacto' => ['template' => 'static.php', 'index' => 'index', 'title' => 'Contacto'],
    'calidad-de-datos' => ['template' => 'static.php', 'index' => 'index', 'title' => 'Calidad de Datos'],
    'reutilizacion-y-atribucion' => ['template' => 'static.php', 'index' => 'index', 'title' => 'Reutilización y Atribución'],
    'canal-de-rectificacion' => ['template' => 'static.php', 'index' => 'index', 'title' => 'Canal de Rectificación'],
    'proteccion-de-datos/derechos' => ['template' => 'static.php', 'index' => 'index', 'title' => 'Ejercicio de Derechos'],
    'modelo-de-datos' => ['template' => 'static.php', 'index' => 'index', 'title' => 'Modelo de Datos'],
    'manifiesto' => ['template' => 'static.php', 'index' => 'index', 'title' => 'Manifiesto Técnico'],
    'objetivos' => ['template' => 'static.php', 'index' => 'index', 'title' => 'Objetivos y Roadmap'],
    'status' => ['template' => 'static.php', 'index' => 'index', 'title' => 'Estado del Servicio']
];

// Default Meta
$page_index = "index";
$page_title = "OpenBorme | Boletín Oficial del Registro Mercantil";
$template = 'home.php';

// 1. Check direct matches
$clean_path = trim($base_path, '/');
if (isset($routes[$clean_path])) {
    $template = $routes[$clean_path]['template'];
    $page_index = $routes[$clean_path]['index'];
    if (isset($routes[$clean_path]['title']))
        $page_title = $routes[$clean_path]['title'] . " | OpenBorme";

    // Generate desc based on template
    if ($template === 'search.php' || $template === 'search_advanced.php') {
        $page_description = "Buscador avanzado del Registro Mercantil de España. Filtra por empresas, cargos, CIF o palabras clave.";
    } elseif ($template === 'static.php') {
        $page_description = "Sección de " . ($routes[$clean_path]['title'] ?? 'información') . " de OpenBorme, la plataforma de acceso al Registro Mercantil.";
    }
}

// 2. Complex Routes (Regex-like)

// Sitemap Companies Paging (sitemap-empresas-1.xml, etc.)
if (preg_match('/^sitemap-empresas-(\d+)\.xml$/', $clean_path, $matches)) {
    $_GET['page'] = $matches[1];
    $template = 'sitemap_companies.xml.php';
    $page_index = 'index';
}

// /diario_borme/ultimo.php -> Redirect to latest
if ($clean_path === 'diario_borme/ultimo.php') {
    // Intentamos resolver la fecha más reciente desde posibles rutas de datos
    $dates = [];
    $candidate_dirs = [
        __DIR__ . '/data',
        __DIR__ . '/../pipeline/data',
        __DIR__ . '/../data'
    ];

    foreach ($candidate_dirs as $dir) {
        if (!is_dir($dir)) {
            continue;
        }
        $dates = array_filter(scandir($dir), function ($d) {
            return preg_match('/^\d{8}$/', $d);
        });
        if (!empty($dates)) {
            break;
        }
    }
    rsort($dates);
    $latest = $dates[0] ?? date('Ymd');
    header("Location: /borme/dias/" . date('Y/m/d', strtotime($latest)) . "/");
    exit;
}

// /borme/dias/ (Date landing)
if ($clean_path === 'borme/dias') {
    $template = 'home.php'; // or create a specific calendar template
    $page_title = "BORME por fecha | OpenBorme";
}

// /borme/dias/YYYY/MM/DD/
if (preg_match('/^borme\/dias\/(\d{4})\/(\d{2})\/(\d{2})$/', $clean_path, $matches)) {
    $date = $matches[1] . $matches[2] . $matches[3];
    $_GET['date'] = $date;
    $template = 'home.php'; // Updated to use Home (Preview Mode) instead of Sumario
    $page_title = "BORME del " . $matches[3] . "/" . $matches[2] . "/" . $matches[1];
    $page_description = "Consulta todas las empresas registradas y la actividad del Registro Mercantil publicadas en el boletín del " . $matches[3] . "/" . $matches[2] . "/" . $matches[1] . ".";
}

// /borme/doc/{id}
if (preg_match('/^borme\/doc\/([^\/]+)$/', $clean_path, $matches)) {
    $_GET['id'] = urldecode($matches[1]);
    $template = 'viewer.php';
}

// /borme/provincia/{provincia}
if (preg_match('/^borme\/provincia\/([a-z-]+)$/', $clean_path, $matches)) {
    $_GET['provincia'] = $matches[1];
    $template = 'landing_seo.php';
    $prov_name = ucwords(str_replace('-', ' ', $matches[1]));
    $page_title = "BORME de " . $prov_name;
    $page_description = "Explora los boletines mercantiles e información de sociedades en la provincia de " . $prov_name . ".";
}

// /nuevas-empresas/{provincia}
if (preg_match('/^nuevas-empresas\/([a-z-]+)$/', $clean_path, $matches)) {
    $_GET['province'] = strtoupper(str_replace('-', ' ', $matches[1]));
    $template = 'new_companies.php';
    $page_title = "Nuevas Empresas en " . ucwords(str_replace('-', ' ', $matches[1]));
    $page_description = "Altas y disoluciones de empresas en " . ucwords(str_replace('-', ' ', $matches[1])) . " por semana, mes y año.";
}

// /borme/provincia/{provincia}/{Y}/{M}/{D}
if (preg_match('/^borme\/provincia\/([a-z-]+)\/(\d{4})\/(\d{2})\/(\d{2})$/', $clean_path, $matches)) {
    $_GET['provincia'] = $matches[1];
    $_GET['date'] = $matches[2] . $matches[3] . $matches[4];
    $template = 'sumario.php';
    $page_title = "BORME de " . ucwords($matches[1]) . " - " . $matches[4] . "/" . $matches[3] . "/" . $matches[2];
}

// /borme/sumario/{Y}/{M}/{D} (Full Summary)
if (preg_match('/^borme\/sumario\/(\d{4})\/(\d{2})\/(\d{2})$/', $clean_path, $matches)) {
    $_GET['date'] = $matches[1] . $matches[2] . $matches[3];
    $template = 'sumario.php';
    $page_title = "Sumario BORME " . $matches[3] . "/" . $matches[2] . "/" . $matches[1];
}


// /tipo/{slug}
if (preg_match('/^tipo\/([a-z-]+)$/', $clean_path, $matches)) {
    $_GET['tipo'] = $matches[1];
    $template = 'landing_seo.php';
    $page_title = ucwords(str_replace('-', ' ', $matches[1])) . " | OpenBorme";
}

// /empresa/{id}
if (preg_match('/^empresa\/([a-zA-Z0-9-]+)$/', $clean_path, $matches)) {
    $_GET['id'] = $matches[1];
    $template = 'empresa.php';
    $page_title = "Historial Mercantil: " . $matches[1];
    $page_description = "Historial y actos mercantil registrados para la empresa o entidad societaria con identificador " . $matches[1] . " en el BORME.";
}

// 3. Render Page
$template_file = __DIR__ . "/templates/" . $template;

if (file_exists($template_file)) {
    // If it's a static template, fetch content
    if ($template === 'static.php') {
        require_once __DIR__ . "/templates/content_provider.php";
        $page_content = get_page_content($clean_path);
    }

    include __DIR__ . "/templates/header.php";
    include $template_file;
    include __DIR__ . "/templates/footer.php";
} else {
    // Return HTTP 404 Status Code properly
    header("HTTP/1.0 404 Not Found");

    // If template doesn't exist, use static template if it matches a path for now
    $page_title = "Página no encontrada o en desarrollo | 404";
    $page_description = "La ruta solicitada no se encuentra disponible en OpenBorme.";
    $page_content = "<div style='text-align: center; padding: 4rem 1rem;'>
        <h2>Ups, ruta no encontrada (Error 404)</h2>
        <p style='margin-top: 1rem;'>Esta sección está actualmente en desarrollo o no existe el contenido para <strong>" . htmlspecialchars($base_path) . "</strong>.</p>
        <div style='margin-top: 2rem;'>
            <a href='/' class='btn btn-primary'>Volver a la Portada</a>
        </div>
    </div>";

    include __DIR__ . "/templates/header.php";
    include __DIR__ . "/templates/static.php";
    include __DIR__ . "/templates/footer.php";
}

// Fixed the typo in my thought process while writing
?>
