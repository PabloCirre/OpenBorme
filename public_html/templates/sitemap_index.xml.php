<?php
header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <sitemap>
        <loc>https://openborme.es/sitemap-estatico.xml</loc>
    </sitemap>
    <sitemap>
        <loc>https://openborme.es/sitemap-provincias.xml</loc>
    </sitemap>
    <sitemap>
        <loc>https://openborme.es/sitemap-fechas.xml</loc>
    </sitemap>
    <sitemap>
        <loc>https://openborme.es/sitemap-empresas.xml</loc>
    </sitemap>
</sitemapindex>
<?php exit; ?>