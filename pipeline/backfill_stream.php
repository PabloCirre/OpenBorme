<?php
/**
 * Backfill histórico en streaming (ingesta directa en SQLite/DB remota).
 *
 * Uso:
 *   php pipeline/backfill_stream.php --start=2020-01-01 --end=2026-02-24 --resume
 *
 * Flags:
 *   --start=YYYY-MM-DD
 *   --end=YYYY-MM-DD
 *   --checkpoint=/ruta/archivo.txt
 *   --resume
 *   --sleep-ms=0
 */

require_once __DIR__ . '/ingest/StreamingDownloader.php';
require_once __DIR__ . '/db/Database.php';

date_default_timezone_set('Europe/Madrid');

function cli_opt(array $opts, $name, $default = null)
{
    return array_key_exists($name, $opts) ? $opts[$name] : $default;
}

function valid_date($value)
{
    $dt = DateTime::createFromFormat('Y-m-d', $value);
    return $dt && $dt->format('Y-m-d') === $value;
}

function ymd_count(PDO $db)
{
    $stmt = $db->query("SELECT COUNT(*) AS c FROM borme_acts");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return (int) ($row['c'] ?? 0);
}

$opts = getopt('', ['start::', 'end::', 'checkpoint::', 'resume', 'sleep-ms::']);

$start = (string) cli_opt($opts, 'start', '2020-01-01');
$end = (string) cli_opt($opts, 'end', date('Y-m-d'));
$checkpoint = (string) cli_opt($opts, 'checkpoint', __DIR__ . '/data/backfill_checkpoint.txt');
$sleepMs = max(0, (int) cli_opt($opts, 'sleep-ms', 0));
$resume = array_key_exists('resume', $opts);

if (!valid_date($start) || !valid_date($end)) {
    fwrite(STDERR, "Formato de fecha inválido. Usa YYYY-MM-DD.\n");
    exit(1);
}

if (strtotime($start) > strtotime($end)) {
    fwrite(STDERR, "El rango es inválido: start > end.\n");
    exit(1);
}

if ($resume && file_exists($checkpoint)) {
    $last = trim((string) @file_get_contents($checkpoint));
    if (valid_date($last)) {
        $next = date('Y-m-d', strtotime($last . ' +1 day'));
        if (strtotime($next) <= strtotime($end)) {
            $start = $next;
        }
    }
}

$tmpDir = sys_get_temp_dir() . '/openborme_backfill_tmp';
if (!is_dir($tmpDir)) {
    mkdir($tmpDir, 0777, true);
}

$db = Database::getInstance();
$driver = Database::getDriver();
$downloader = new StreamingDownloader($tmpDir);

$totalDays = (int) floor((strtotime($end) - strtotime($start)) / 86400) + 1;
$processedDays = 0;
$totalInserted = 0;

echo "=== OpenBorme Backfill Streaming ===\n";
echo "Driver: {$driver}\n";
echo "Rango: {$start} -> {$end} ({$totalDays} días)\n";
echo "Checkpoint: {$checkpoint}\n";

$currentTs = strtotime($start);
$endTs = strtotime($end);

while ($currentTs <= $endTs) {
    $day = date('Y-m-d', $currentTs);
    $before = ymd_count($db);
    $error = null;

    echo "[{$day}] iniciando...\n";

    try {
        $downloader->downloadRange($day, $day);
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }

    $after = ymd_count($db);
    $inserted = max(0, $after - $before);
    $totalInserted += $inserted;
    $processedDays++;

    if ($error) {
        echo "[{$day}] error: {$error}\n";
    } else {
        echo "[{$day}] ok, +{$inserted} actos (total: {$after})\n";
    }

    @file_put_contents($checkpoint, $day . "\n");

    if ($sleepMs > 0) {
        usleep($sleepMs * 1000);
    }

    if (($processedDays % 25) === 0) {
        $progress = round(($processedDays / $totalDays) * 100, 2);
        echo "Progreso: {$processedDays}/{$totalDays} ({$progress}%), insertados {$totalInserted}\n";
    }

    $currentTs = strtotime('+1 day', $currentTs);
}

echo "=== Backfill finalizado ===\n";
echo "Días procesados: {$processedDays}\n";
echo "Insertados en esta ejecución: {$totalInserted}\n";
echo "Total actual en DB: " . ymd_count($db) . "\n";

