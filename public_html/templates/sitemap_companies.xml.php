<?php
header('Content-Type: application/xml; charset=utf-8');

$db_path = file_exists(__DIR__ . '/../../pipeline/db/Database.php') ? __DIR__ . '/../../pipeline/db/Database.php' : __DIR__ . '/../pipeline/db/Database.php';
require_once $db_path;

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <?php
    try {
        $db = Database::getInstance();
        // Limit to 5000 to avoid performance/size issues in this phase
        $stmt = $db->query("SELECT DISTINCT slug FROM borme_acts WHERE slug IS NOT NULL AND slug != '' ORDER BY date DESC LIMIT 5000");
        $slugs = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($slugs as $slug) {
            $url = "https://openborme.es/empresa/$slug";
            echo "    <url>\n";
            echo "        <loc>$url</loc>\n";
            echo "        <changefreq>monthly</changefreq>\n";
            echo "        <priority>0.4</priority>\n";
            echo "    </url>\n";
        }
    } catch (Exception $e) {
        // Silently fail or log
    }
    ?>
</urlset>
<?php exit; ?>