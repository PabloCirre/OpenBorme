<?php

/**
 * OpenBorme Daily Update Script
 * Intended to run by CRON for a daily ingest + CSV append.
 */

require_once __DIR__ . '/ingest/BormeDownloader.php';
require_once __DIR__ . '/extract/ParserPdf.php';
require_once __DIR__ . '/ingest/ParserXml.php';
require_once __DIR__ . '/ingest/DbIngestor.php';

$storage_dir = __DIR__ . '/data';
$repo_dir = __DIR__ . '/../repo';

$province_mapping = [
    "01" => "ALAVA", "02" => "ALBACETE", "03" => "ALICANTE", "04" => "ALMERIA",
    "05" => "AVILA", "06" => "BADAJOZ", "07" => "ILLES BALEARS", "08" => "BARCELONA",
    "09" => "BURGOS", "10" => "CACERES", "11" => "CADIZ", "12" => "CASTELLON",
    "13" => "CIUDAD REAL", "14" => "CORDOBA", "15" => "A CORUÑA", "16" => "CUENCA",
    "17" => "GIRONA", "18" => "GRANADA", "19" => "GUADALAJARA", "20" => "GIPUZKOA",
    "21" => "HUELVA", "22" => "HUESCA", "23" => "JAEN", "24" => "LEON",
    "25" => "LLEIDA", "26" => "LA RIOJA", "27" => "LUGO", "28" => "MADRID",
    "29" => "MALAGA", "30" => "MURCIA", "31" => "NAVARRA", "32" => "OURENSE",
    "33" => "ASTURIAS", "34" => "PALENCIA", "35" => "LAS PALMAS", "36" => "PONTEVEDRA",
    "37" => "SALAMANCA", "38" => "SANTA CRUZ DE TENERIFE", "39" => "CANTABRIA",
    "40" => "SEGOVIA", "41" => "SEVILLA", "42" => "SORIA", "43" => "TARRAGONA",
    "44" => "TERUEL", "45" => "TOLEDO", "46" => "VALENCIA", "47" => "VALLADOLID",
    "48" => "BIZKAIA", "49" => "ZAMORA", "50" => "ZARAGOZA", "51" => "CEUTA", "52" => "MELILLA"
];

$downloader = new BormeDownloader($storage_dir);
$downloader->download(1);

$today = date('Ymd');
$year = date('Y');
$month = date('m');
$date_dir = "$storage_dir/$today";

if (!is_dir($date_dir)) {
    die("No hay datos nuevos para hoy ($today).\n");
}

$target_repo_path = "$repo_dir/$year/$month";
if (!is_dir($target_repo_path)) {
    mkdir($target_repo_path, 0777, true);
}
$repo_file = "$target_repo_path/borme_$today.csv";

$all_data = [];
$pdf_parser = new ParserPdf();
$xml_parser = new ParserXml();

echo "Procesando actualizaciones para $today...\n";

$sec1_dir = "$date_dir/section_A";
if (is_dir($sec1_dir)) {
    $pdfs = array_filter(scandir($sec1_dir), function ($f) {
        return strpos($f, '.pdf') !== false;
    });

    foreach ($pdfs as $pdf) {
        $f_path = "$sec1_dir/$pdf";
        preg_match('/-([0-9]{2})\.pdf$/', $pdf, $m);
        $p_code = $m[1] ?? "";
        $p_name = $province_mapping[$p_code] ?? "UNKNOWN";
        $acts = $pdf_parser->parse($f_path, $p_name);
        foreach ($acts as $act) {
            $act['Date'] = $today;
            $all_data[] = $act;
        }
    }
}

$sec2_dir = "$date_dir/section_C";
if (is_dir($sec2_dir)) {
    $xmls = array_filter(scandir($sec2_dir), function ($f) {
        return strpos($f, '.xml') !== false;
    });

    foreach ($xmls as $xml_file) {
        $f_path = "$sec2_dir/$xml_file";
        $act = $xml_parser->parse($f_path);
        if ($act) {
            $act['Date'] = $act['Date'] ?? $today;
            $all_data[] = $act;
        }
    }
}

$ingestor = new DbIngestor();
if (!empty($all_data)) {
    echo "Guardando " . count($all_data) . " actos...\n";
    $ingestor->ingestBatch($all_data);
}

$is_new_file = !file_exists($repo_file);
$fp = fopen($repo_file, 'a');

if ($is_new_file) {
    $header = ['Date', 'Section', 'Province', 'Company Name', 'CIF', 'Website', 'Capital', 'Address', 'Workers', 'Act Type', 'Details', 'ID'];
    fputcsv($fp, $header);
}

foreach ($all_data as $row) {
    fputcsv($fp, [
        $row['Date'] ?? '',
        $row['Section'] ?? '',
        $row['Province'] ?? '',
        $row['Company Name'] ?? '',
        $row['CIF'] ?? '',
        $row['Website'] ?? '',
        $row['Capital'] ?? '',
        $row['Address'] ?? '',
        $row['Workers'] ?? '',
        $row['Act Type'] ?? '',
        $row['Details'] ?? '',
        $row['ID'] ?? ''
    ]);
}

fclose($fp);
echo "Repositorio actualizado: $repo_file\n";

