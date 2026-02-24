<?php

require_once __DIR__ . '/ingest/BormeDownloader.php';
require_once __DIR__ . '/ingest/StreamingDownloader.php';
require_once __DIR__ . '/db/Database.php';

// Configuration
$days_to_process = 7;
// Usamos un directorio temporal para no dejar descargas en disco
$storage_dir = sys_get_temp_dir() . '/openborme_tmp';
if (!is_dir($storage_dir)) mkdir($storage_dir, 0777, true);
$output_file = __DIR__ . "/borme_data_extract.csv";

// Human-readable province mapping
$province_mapping = [
    "01" => "ALAVA",
    "02" => "ALBACETE",
    "03" => "ALICANTE",
    "04" => "ALMERIA",
    "05" => "AVILA",
    "06" => "BADAJOZ",
    "07" => "ILLES BALEARS",
    "08" => "BARCELONA",
    "09" => "BURGOS",
    "10" => "CACERES",
    "11" => "CADIZ",
    "12" => "CASTELLON",
    "13" => "CIUDAD REAL",
    "14" => "CORDOBA",
    "15" => "A CORUÑA",
    "16" => "CUENCA",
    "17" => "GIRONA",
    "18" => "GRANADA",
    "19" => "GUADALAJARA",
    "20" => "GIPUZKOA",
    "21" => "HUELVA",
    "22" => "HUESCA",
    "23" => "JAEN",
    "24" => "LEON",
    "25" => "LLEIDA",
    "26" => "LA RIOJA",
    "27" => "LUGO",
    "28" => "MADRID",
    "29" => "MALAGA",
    "30" => "MURCIA",
    "31" => "NAVARRA",
    "32" => "OURENSE",
    "33" => "ASTURIAS",
    "34" => "PALENCIA",
    "35" => "LAS PALMAS",
    "36" => "PONTEVEDRA",
    "37" => "SALAMANCA",
    "38" => "SANTA CRUZ DE TENERIFE",
    "39" => "CANTABRIA",
    "40" => "SEGOVIA",
    "41" => "SEVILLA",
    "42" => "SORIA",
    "43" => "TARRAGONA",
    "44" => "TERUEL",
    "45" => "TOLEDO",
    "46" => "VALENCIA",
    "47" => "VALLADOLID",
    "48" => "BIZKAIA",
    "49" => "ZAMORA",
    "50" => "ZARAGOZA",
    "51" => "CEUTA",
    "52" => "MELILLA"
];

// 1. Download + ingest en streaming (no se persisten ficheros)
$downloader = new StreamingDownloader($storage_dir);
if (isset($argv[1])) {
    $downloader->download((int) $argv[1]);
} elseif (isset($_GET['days'])) {
    $downloader->download((int) $_GET['days']);
} else {
    $downloader->download($days_to_process);
}

echo "Ingesta streaming finalizada.\n";
