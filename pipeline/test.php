<?php
require_once __DIR__ . '/db/Database.php';
try {
    $db = Database::getInstance();
    $stmt = $db->query("SELECT DISTINCT substr(date, 1, 4) as year FROM borme_acts WHERE date IS NOT NULL AND date != '' ORDER BY year ASC");
    $years = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Years found: " . implode(", ", $years) . "\n";
    echo "Total: " . count($years) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>