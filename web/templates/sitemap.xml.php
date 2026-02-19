<?php
header('Content-Type: application/xml; charset=utf-8');

// Require the routes to stay in sync
// In a real app, you might want to share this array differently
$routes_to_list = [
    '',
    'buscar',
    'busqueda-avanzada',
    'diario_borme',
    'provincias',
    'tipos-de-actos',
    'descargas',
    'api',
    'metodologia',
    'fuentes',
    'aviso-legal',
    'terminos-de-uso',
    'exencion-responsabilidad',
    'privacidad',
    'cookies',
    'faq',
    'contacto',
    'calidad-de-datos',
    'reutilizacion-y-atribucion',
    'canal-de-rectificacion',
    'proteccion-de-datos/derechos',
    'status'
];

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <?php foreach ($routes_to_list as $r): ?>
        <url>
            <loc>https://openborme.es/
                <?= $r ?>
            </loc>
            <changefreq>daily</changefreq>
            <priority>
                <?= ($r === '') ? '1.0' : '0.8' ?>
            </priority>
        </url>
    <?php endforeach; ?>
</urlset>
<?php exit; ?>