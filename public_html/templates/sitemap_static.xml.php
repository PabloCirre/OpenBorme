<?php
header('Content-Type: application/xml; charset=utf-8');

$routes_to_list = [
    '',
    'buscar',
    'busqueda-avanzada',
    'provincias',
    'tipos-de-actos',
    'descargas',
    'api',
    'api/documentacion',
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
    'modelo-de-datos',
    'manifiesto',
    'objetivos',
    'mapa-del-sitio'
];

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <?php foreach ($routes_to_list as $r): ?>
        <url>
            <loc>https://openborme.es/
                <?= $r ?>
            </loc>
            <changefreq>monthly</changefreq>
            <priority>
                <?= ($r === '') ? '1.0' : '0.7' ?>
            </priority>
        </url>
    <?php endforeach; ?>
</urlset>
<?php exit; ?>