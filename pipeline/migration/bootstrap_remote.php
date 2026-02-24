<?php
/**
 * Crea/valida el esquema remoto definitivo (PostgreSQL) usando la configuración
 * de entorno de Database.php.
 *
 * Uso:
 *   OPENBORME_DB_DRIVER=pgsql OPENBORME_PG_HOST=... OPENBORME_PG_DBNAME=... \
 *   OPENBORME_PG_USER=... OPENBORME_PG_PASS=... php pipeline/migration/bootstrap_remote.php
 */

require_once __DIR__ . '/../db/Database.php';

$driver = Database::getDriver();
if ($driver !== 'pgsql') {
    fwrite(STDERR, "Este script requiere destino PostgreSQL (OPENBORME_DB_DRIVER=pgsql).\n");
    exit(1);
}

$db = Database::getInstance();

$requiredTables = ['company', 'borme_acts', 'ingest_log'];
$checkStmt = $db->prepare(
    "SELECT 1 FROM pg_tables WHERE schemaname = 'public' AND tablename = :table_name LIMIT 1"
);

$missing = [];
foreach ($requiredTables as $table) {
    $checkStmt->execute([':table_name' => $table]);
    if (!$checkStmt->fetchColumn()) {
        $missing[] = $table;
    }
}

if (empty($missing)) {
    echo "Schema remoto listo. Tablas OK: " . implode(', ', $requiredTables) . "\n";
    exit(0);
}

fwrite(STDERR, "Faltan tablas tras bootstrap: " . implode(', ', $missing) . "\n");
exit(1);

