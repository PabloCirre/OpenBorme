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
        $stmt = $db->query("SELECT DISTINCT date FROM borme_acts ORDER BY date DESC");
        $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($dates as $date) {
            // date format YYYYMMDD
            $y = substr($date, 0, 4);
            $m = substr($date, 4, 2);
            $d = substr($date, 6, 2);
            $url = "https://openborme.es/borme/dias/$y/$m/$d";
            echo "    <url>\n";
            echo "        <loc>$url</loc>\n";
            echo "        <changefreq>never</changefreq>\n";
            echo "        <priority>0.5</priority>\n";
            echo "    </url>\n";
        }
    } catch (Exception $e) {
        // Silently fail or log
    }
    ?>
</urlset>
<?php exit; ?>