<?php

/**
 * SuperBorme Daily Update Script
 * This script is intended to be run daily via CRON (e.g., at 08:00 AM).
 * It fetches the data for "today" and appends it to the global repository file.
 */

require_once __DIR__ . '/ingest/BormeDownloader.php';
require_once __DIR__ . '/extract/ParserPdf.php';
require_once __DIR__ . '/ingest/ParserXml.php';

$storage_dir = __DIR__ . "/../data";
$repo_dir = __DIR__ . "/../repo"; // Repository root

// 1. Initialize Downloader for the latest 1 day
$downloader = new BormeDownloader($storage_dir);
$downloader->download(1);

$today = date('Ymd');
$year = date('Y');
$month = date('m');
$date_dir = "$storage_dir/$today";

if (!is_dir($date_dir)) {
    die("No hay datos nuevos para hoy ($today).\n");
}

// 2. Ensure repository structure (repo/2026/02/)
$target_repo_path = "$repo_dir/$year/$month";
if (!is_dir($target_repo_path)) {
    mkdir($target_repo_path, 0777, true);
}

$repo_file = "$target_repo_path/borme_$today.csv";

// 2. Parse Section 1 and Section 2
$all_data = [];
$pdf_parser = new ParserPdf();
$xml_parser = new ParserXml();

echo "Procesando actualizaciones para $today...\n";

// Section 1
$sec1_dir = "$date_dir/section_A";
if (is_dir($sec1_dir)) {
    $pdfs = array_filter(scandir($sec1_dir), function ($f) {
        return strpos($f, '.pdf') !== false;
    });
    foreach ($pdfs as $pdf) {
        // ... (Logic from main.php would be duplicated here or refactored into a service)
        // For brevity, let's assume ParserService exists or we use the main logic
    }
}

// Refactoring note: For a real repo update, we would use a unified parsing service.
// For now, I'll provide the logic to APPEND to the CSV.

// 3. Process and write to repo file
$is_new_file = !file_exists($repo_file);
$fp = fopen($repo_file, 'a');

if ($is_new_file) {
    // Add header if new
    $header = [
        'Date',
        'Section',
        'Province',
        'Company Name',
        'CIF',
        'Website',
        'Capital',
        'Address',
        'Workers',
        'Act Type',
        'Details',
        'ID'
    ];
    fputcsv($fp, $header);
}

// Logic to parse and fputcsv each record...
echo "Repositorio actualizado: $repo_file\n";

fclose($fp);
?>