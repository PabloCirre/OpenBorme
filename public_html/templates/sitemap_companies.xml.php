<?php
header('Content-Type: application/xml; charset=utf-8');

$db_path = file_exists(__DIR__ . '/../../pipeline/db/Database.php') ? __DIR__ . '/../../pipeline/db/Database.php' : __DIR__ . '/../pipeline/db/Database.php';
require_once $db_path;

function createSlug($text)
{
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    if (empty($text))
        return 'n-a';
    return $text;
}

$per_page = 10000;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1)
    $page = 1;
$offset = ($page - 1) * $per_page;

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <?php
    try {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT DISTINCT company_name FROM borme_acts WHERE company_name IS NOT NULL AND company_name != '' ORDER BY date DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $names = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($names as $name) {
            $slug = createSlug($name);
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