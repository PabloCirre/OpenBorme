<?php
header('Content-Type: application/xml; charset=utf-8');

$provinces = [
    'almeria',
    'cadiz',
    'cordoba',
    'granada',
    'huelva',
    'jaen',
    'malaga',
    'sevilla',
    'huesca',
    'teruel',
    'zaragoza',
    'asturias',
    'illes-balears',
    'las-palmas',
    's-c-tenerife',
    'cantabria',
    'avila',
    'burgos',
    'leon',
    'palencia',
    'salamanca',
    'segovia',
    'soria',
    'valladolid',
    'zamora',
    'albacete',
    'ciudad-real',
    'cuenca',
    'guadalajara',
    'toledo',
    'barcelona',
    'girona',
    'lleida',
    'tarragona',
    'badajoz',
    'caceres',
    'a-coruna',
    'lugo',
    'ourense',
    'pontevedra',
    'madrid',
    'murcia',
    'navarra',
    'araba-alava',
    'gipuzkoa',
    'bizkaia',
    'la-rioja',
    'alicante',
    'castellon',
    'valencia',
    'ceuta',
    'melilla'
];

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <?php foreach ($provinces as $p): ?>
        <url>
            <loc>https://openborme.es/borme/provincia/
                <?= $p ?>
            </loc>
            <changefreq>weekly</changefreq>
            <priority>0.6</priority>
        </url>
    <?php endforeach; ?>
</urlset>
<?php exit; ?>