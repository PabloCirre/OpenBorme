<?php
/**
 * OpenBorme Central Router
 * Handles all dynamic requests and loads appropriate templates.
 */

$request_uri = $_SERVER['REQUEST_URI'];
$base_path = parse_url($request_uri, PHP_URL_PATH);
$path_parts = explode('/', trim($base_path, '/'));

// --- Route Mapping ---
$routes = [
    '' => ['template' => 'home.php', 'index' => 'index'],
    'buscar' => ['template' => 'search.php', 'index' => 'index'],
    'busqueda-avanzada' => ['template' => 'search_advanced.php', 'index' => 'index'],
    'resultados' => ['template' => 'search.php', 'index' => 'noindex'],
    'sitemap.xml' => ['template' => 'sitemap.xml.php', 'index' => 'index'],
    'exportar' => ['template' => 'export.php', 'index' => 'index'],
    'diario_borme' => ['template' => 'home.php', 'index' => 'index'],
    'diario_borme/ayuda.php' => ['template' => 'static.php', 'index' => 'index', 'title' => 'Ayuda del BORME'],
    'seccion/actos-inscritos' => ['template' => 'landing_seo.php', 'index' => 'index', 'title' => 'Actos Inscritos'],
    'seccion/anuncios' => ['template' => 'landing_seo.php', 'index' => 'index', 'title' => 'Anuncios y Avisos'],
    'provincias' => ['template' => 'provincias.php', 'index' => 'index'],
    'tipos-de-actos' => ['template' => 'tipos_actos.php', 'index' => 'index'],
    'empresas' => ['template' => 'search.php', 'index' => 'index', 'title' => 'Buscar Empresas'],
    'personas' => ['template' => 'static.php', 'index' => 'noindex', 'title' => 'Búsqueda de Personas'],
    'alertas' => ['template' => 'static.php', 'index' => 'index', 'title' => 'Alertas del BORME'],
    'mi-cuenta/acceso' => ['template' => 'static.php', 'index' => 'noindex', 'title' => 'Acceso'],
    'descargas' => ['template' => 'descargas.php', 'index' => 'index'],
    'api' => ['template' => 'api_landing.php', 'index' => 'index'],
    'api/documentacion' => ['template' => 'api_docs.php', 'index' => 'index'],
    'metodologia' => ['template' => 'metodologia.php', 'index' => 'index'],
    'fuentes' => ['template' => 'fuentes.php', 'index' => 'index'],
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
}

// 2. Complex Routes (Regex-like)

// /diario_borme/ultimo.php -> Redirect to latest
if ($clean_path === 'diario_borme/ultimo.php') {
    // Logic to find latest folder in /data/
    $dates = array_filter(scandir(__DIR__ . '/data'), function ($d) {
        return preg_match('/^\d{8}$/', $d);
    });
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
    $template = 'sumario.php';
    $page_title = "BORME del " . $matches[3] . "/" . $matches[2] . "/" . $matches[1];
}

// /borme/doc/{id} or /diario_borme/txt.php?id=...
if (preg_match('/(BORME-[A-Z]-[0-9]{4}-[0-9]+-[0-9]+)/', $base_path, $matches)) {
    $_GET['id'] = $matches[1];
    $template = 'viewer.php';
}

// /borme/provincia/{provincia}
if (preg_match('/^borme\/provincia\/([a-z-]+)$/', $clean_path, $matches)) {
    $_GET['provincia'] = $matches[1];
    $template = 'landing_seo.php';
    $page_title = "BORME de " . ucwords(str_replace('-', ' ', $matches[1]));
}

// /borme/provincia/{provincia}/{Y}/{M}/{D}
if (preg_match('/^borme\/provincia\/([a-z-]+)\/(\d{4})\/(\d{2})\/(\d{2})$/', $clean_path, $matches)) {
    $_GET['provincia'] = $matches[1];
    $_GET['date'] = $matches[2] . $matches[3] . $matches[4];
    $template = 'sumario.php';
    $page_title = "BORME de " . ucwords($matches[1]) . " - " . $matches[4] . "/" . $matches[3] . "/" . $matches[2];
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
    $page_title = "Historial: " . $matches[1];
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
    // If template doesn't exist, use static template if it matches a path for now
    $page_title = ucwords(str_replace('-', ' ', trim($base_path, '/')));
    $page_content = "<p>Esta sección está actualmente en desarrollo. Pronto estará disponible el contenido para <strong>" . htmlspecialchars($base_path) . "</strong>.</p>";
    include __DIR__ . "/templates/header.php";
    include __DIR__ . "/templates/static.php";
    include __DIR__ . "/templates/footer.php";
}

// Fixed the typo in my thought process while writing
?>