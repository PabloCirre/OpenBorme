<?php
/**
 * Migra datos desde SQLite (openborme.sqlite) a la base remota definitiva.
 * Destino soportado: PostgreSQL (driver pgsql de Database.php).
 *
 * Uso ejemplo:
 *   OPENBORME_DB_DRIVER=pgsql OPENBORME_PG_HOST=... OPENBORME_PG_DBNAME=... \
 *   OPENBORME_PG_USER=... OPENBORME_PG_PASS=... \
 *   php pipeline/migration/sqlite_to_remote.php --source=pipeline/data/openborme.sqlite --batch=2000
 *
 * Flags:
 *   --source=PATH   Ruta al SQLite origen (default: OPENBORME_SQLITE_SOURCE_PATH o pipeline/data/openborme.sqlite)
 *   --batch=N       Tamaño de lote por transacción (default: 1000)
 *   --truncate      Vacía tablas destino antes de migrar
 *   --dry-run       Solo valida conexiones y recuentos
 */

require_once __DIR__ . '/../db/Database.php';

function cli_opt(array $opts, $key, $default = null)
{
    return array_key_exists($key, $opts) ? $opts[$key] : $default;
}

function sqlite_exists($path)
{
    return is_string($path) && $path !== '' && file_exists($path) && is_file($path);
}

function sqlite_count(PDO $db, $table)
{
    $stmt = $db->query("SELECT COUNT(*) AS c FROM {$table}");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return (int) ($row['c'] ?? 0);
}

function remote_count(PDO $db, $table)
{
    $stmt = $db->query("SELECT COUNT(*) AS c FROM {$table}");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return (int) ($row['c'] ?? 0);
}

function sqlite_table_columns(PDO $db, $table)
{
    $stmt = $db->query("PRAGMA table_info($table)");
    $columns = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $columns[] = $row['name'];
    }
    return $columns;
}

function migrate_companies(PDO $source, PDO $dest, $batchSize)
{
    $read = $source->query("SELECT cif, name, province FROM company ORDER BY cif");
    $write = $dest->prepare(
        "INSERT INTO company (cif, name, province)
         VALUES (:cif, :name, :province)
         ON CONFLICT (cif) DO UPDATE SET
            name = EXCLUDED.name,
            province = EXCLUDED.province"
    );

    $processed = 0;
    $upserted = 0;
    $dest->beginTransaction();

    while ($row = $read->fetch(PDO::FETCH_ASSOC)) {
        $cif = trim((string) ($row['cif'] ?? ''));
        if ($cif === '') {
            continue;
        }

        $write->execute([
            ':cif' => $cif,
            ':name' => (string) ($row['name'] ?? ''),
            ':province' => (string) ($row['province'] ?? 'UNKNOWN'),
        ]);

        $processed++;
        $upserted += $write->rowCount();

        if (($processed % $batchSize) === 0) {
            $dest->commit();
            $dest->beginTransaction();
            echo "[company] Procesadas {$processed}\n";
        }
    }

    if ($dest->inTransaction()) {
        $dest->commit();
    }

    return [$processed, $upserted];
}

function migrate_acts(PDO $source, PDO $dest, $batchSize)
{
    $columns = sqlite_table_columns($source, 'borme_acts');
    $has = function ($name) use ($columns) {
        return in_array($name, $columns, true);
    };

    $selectNormalizedType = $has('normalized_type') ? 'normalized_type' : "'OTROS' AS normalized_type";
    $selectEventGroup = $has('event_group') ? 'event_group' : "'OTHER' AS event_group";
    $selectIsCreation = $has('is_creation') ? 'is_creation' : "0 AS is_creation";
    $selectIsDissolution = $has('is_dissolution') ? 'is_dissolution' : "0 AS is_dissolution";
    $selectCompanyNameNorm = $has('company_name_norm') ? 'company_name_norm' : "NULL AS company_name_norm";
    $selectLegacyId = $has('legacy_id') ? 'legacy_id' : "id AS legacy_id";

    $read = $source->query(
        "SELECT id, $selectLegacyId, date, section, type, province, company_name, company_uid, raw_text, capital, hash_md5, created_at,
                $selectNormalizedType,
                $selectEventGroup,
                $selectIsCreation,
                $selectIsDissolution,
                $selectCompanyNameNorm
         FROM borme_acts
         ORDER BY date, id"
    );

    $write = $dest->prepare(
        "INSERT INTO borme_acts (
            id, legacy_id, date, section, type, province, company_name, company_uid, raw_text, capital, hash_md5, created_at,
            normalized_type, event_group, is_creation, is_dissolution, company_name_norm
         ) VALUES (
            :id, :legacy_id, :date, :section, :type, :province, :company_name, :company_uid, :raw_text, :capital, :hash_md5, :created_at,
            :normalized_type, :event_group, :is_creation, :is_dissolution, :company_name_norm
         )
         ON CONFLICT (id) DO UPDATE SET
            legacy_id = EXCLUDED.legacy_id,
            date = EXCLUDED.date,
            section = EXCLUDED.section,
            type = EXCLUDED.type,
            province = EXCLUDED.province,
            company_name = EXCLUDED.company_name,
            company_uid = EXCLUDED.company_uid,
            raw_text = EXCLUDED.raw_text,
            capital = EXCLUDED.capital,
            hash_md5 = EXCLUDED.hash_md5,
            normalized_type = EXCLUDED.normalized_type,
            event_group = EXCLUDED.event_group,
            is_creation = EXCLUDED.is_creation,
            is_dissolution = EXCLUDED.is_dissolution,
            company_name_norm = EXCLUDED.company_name_norm"
    );

    $processed = 0;
    $inserted = 0;
    $dest->beginTransaction();

    while ($row = $read->fetch(PDO::FETCH_ASSOC)) {
        $id = trim((string) ($row['id'] ?? ''));
        if ($id === '') {
            continue;
        }

        $createdAt = trim((string) ($row['created_at'] ?? ''));
        if ($createdAt === '') {
            $createdAt = date('Y-m-d H:i:s');
        }

        $section = trim((string) ($row['section'] ?? 'A'));
        if (!in_array($section, ['A', 'B', 'C'], true)) {
            $section = 'A';
        }

        $write->execute([
            ':id' => $id,
            ':legacy_id' => (string) ($row['legacy_id'] ?? $id),
            ':date' => (string) ($row['date'] ?? ''),
            ':section' => $section,
            ':type' => (string) ($row['type'] ?? 'UNKNOWN'),
            ':province' => (string) ($row['province'] ?? 'UNKNOWN'),
            ':company_name' => (string) ($row['company_name'] ?? 'UNKNOWN'),
            ':company_uid' => ($row['company_uid'] !== null ? (string) $row['company_uid'] : null),
            ':raw_text' => ($row['raw_text'] !== null ? (string) $row['raw_text'] : null),
            ':capital' => ($row['capital'] !== null ? (string) $row['capital'] : null),
            ':hash_md5' => (string) ($row['hash_md5'] ?? md5($id)),
            ':created_at' => $createdAt,
            ':normalized_type' => (string) ($row['normalized_type'] ?? 'OTROS'),
            ':event_group' => (string) ($row['event_group'] ?? 'OTHER'),
            ':is_creation' => (int) ($row['is_creation'] ?? 0),
            ':is_dissolution' => (int) ($row['is_dissolution'] ?? 0),
            ':company_name_norm' => ($row['company_name_norm'] !== null ? (string) $row['company_name_norm'] : null),
        ]);

        $processed++;
        $inserted += $write->rowCount();

        if (($processed % $batchSize) === 0) {
            $dest->commit();
            $dest->beginTransaction();
            echo "[acts] Procesadas {$processed}, insertadas {$inserted}\n";
        }
    }

    if ($dest->inTransaction()) {
        $dest->commit();
    }

    return [$processed, $inserted];
}

$opts = getopt('', ['source::', 'batch::', 'truncate', 'dry-run']);
$defaultSource = getenv('OPENBORME_SQLITE_SOURCE_PATH');
if (!$defaultSource || trim($defaultSource) === '') {
    $defaultSource = __DIR__ . '/../data/openborme.sqlite';
}

$sourcePath = (string) cli_opt($opts, 'source', $defaultSource);
$batchSize = max(100, (int) cli_opt($opts, 'batch', 1000));
$truncate = array_key_exists('truncate', $opts);
$dryRun = array_key_exists('dry-run', $opts);

if (!sqlite_exists($sourcePath)) {
    fwrite(STDERR, "SQLite origen no encontrado: {$sourcePath}\n");
    exit(1);
}

$startedAt = microtime(true);
echo "SQLite origen: {$sourcePath}\n";
echo "Batch size: {$batchSize}\n";
echo "Modo dry-run: " . ($dryRun ? 'sí' : 'no') . "\n";

try {
    $source = new PDO('sqlite:' . $sourcePath);
    $source->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $source->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    fwrite(STDERR, "No se pudo abrir SQLite origen: " . $e->getMessage() . "\n");
    exit(1);
}

$driver = Database::getDriver();
if ($driver !== 'pgsql') {
    fwrite(STDERR, "Destino no válido para este script. Configura OPENBORME_DB_DRIVER=pgsql.\n");
    exit(1);
}

$dest = Database::getInstance(); // genera esquema si está vacío

$sourceActs = sqlite_count($source, 'borme_acts');
$sourceCompanies = sqlite_count($source, 'company');
echo "Fuente: {$sourceActs} actos, {$sourceCompanies} empresas\n";

if ($dryRun) {
    $remoteActs = remote_count($dest, 'borme_acts');
    $remoteCompanies = remote_count($dest, 'company');
    echo "Destino actual: {$remoteActs} actos, {$remoteCompanies} empresas\n";
    echo "Dry-run finalizado sin escritura.\n";
    exit(0);
}

if ($truncate) {
    echo "Truncando destino...\n";
    $dest->beginTransaction();
    $dest->exec("TRUNCATE TABLE borme_acts, company, ingest_log RESTART IDENTITY");
    $dest->commit();
}

list($processedCompanies, $upsertedCompanies) = migrate_companies($source, $dest, $batchSize);
list($processedActs, $insertedActs) = migrate_acts($source, $dest, $batchSize);

$remoteActs = remote_count($dest, 'borme_acts');
$remoteCompanies = remote_count($dest, 'company');
$elapsed = round(microtime(true) - $startedAt, 2);

echo "Migración completada en {$elapsed}s\n";
echo "Company: procesadas {$processedCompanies}, upsert {$upsertedCompanies}, total destino {$remoteCompanies}\n";
echo "Acts: procesadas {$processedActs}, insertadas {$insertedActs}, total destino {$remoteActs}\n";
