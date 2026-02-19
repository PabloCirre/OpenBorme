<?php
/**
 * OpenBorme Historical Backfill Script (CLI)
 * Usage: php backfill.php [start_date] [end_date]
 * Example: php backfill.php 2020-01-01 2020-01-31
 */

require_once __DIR__ . '/../core/BormeDownloader.php';

$startDate = $argv[1] ?? '2020-01-01';
$endDate = $argv[2] ?? date('Y-m-d');

echo "--- OpenBorme Backfill Engine ---\n";
echo "Range: $startDate to $endDate\n";
echo "Storage: data/\n\n";

$downloader = new BormeDownloader(__DIR__ . '/../data');
$downloader->downloadRange($startDate, $endDate);

echo "\n--- Backfill Batch Finished ---\n";
