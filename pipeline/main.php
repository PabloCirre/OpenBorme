<?php

require_once 'BormeDownloader.php';
require_once 'ParserPdf.php';
require_once 'ParserXml.php';
require_once __DIR__ . '/db/Database.php';
require_once __DIR__ . '/ingest/DbIngestor.php';

// Configuration
$days_to_process = 7;
$storage_dir = __DIR__ . "/data";
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

// 1. Download Data
$downloader = new BormeDownloader($storage_dir);
if (isset($argv[1])) {
    $downloader->download((int) $argv[1]);
} elseif (isset($_GET['days'])) {
    $downloader->download((int) $_GET['days']);
} else {
    $downloader->download($days_to_process);
}

// 2. Parse Data
$all_data = [];
$pdf_parser = new ParserPdf();
$xml_parser = new ParserXml();

$dates = array_filter(scandir($storage_dir), function ($item) {
    return is_dir("data/$item") && preg_match('/^\d{8}$/', $item);
});

echo "Procesando datos...\n";

foreach ($dates as $date) {
    echo "  Fecha: $date\n";
    $date_dir = "$storage_dir/$date";

    // Section 1
    $sec1_dir = "$date_dir/section_A";
    if (is_dir($sec1_dir)) {
        $pdfs = array_filter(scandir($sec1_dir), function ($f) {
            return strpos($f, '.pdf') !== false;
        });
        foreach ($pdfs as $pdf) {
            $f_path = "$sec1_dir/$pdf";
            // Get province code from filename like BORME-A-2026-32-28.pdf
            preg_match('/-([0-9]{2})\.pdf$/', $pdf, $m);
            $p_code = $m[1] ?? "";
            $p_name = $province_mapping[$p_code] ?? "UNKNOWN";

            $results = $pdf_parser->parse($f_path, $p_name);
            foreach ($results as $r) {
                $r['Date'] = $date;
                $all_data[] = $r;
            }
        }
    }

    // Section 2
    $sec2_dir = "$date_dir/section_C";
    if (is_dir($sec2_dir)) {
        $xmls = array_filter(scandir($sec2_dir), function ($f) {
            return strpos($f, '.xml') !== false;
        });
        foreach ($xmls as $xml_file) {
            $f_path = "$sec2_dir/$xml_file";
            $result = $xml_parser->parse($f_path);
            if ($result)
                $all_data[] = $result;
        }
    }
}

// 3. Insert in Database (In-Memory Processing)
if (!empty($all_data)) {
    echo "💾 Guardando " . count($all_data) . " actos corporativos en la Base de Datos SQLite...\n";

    // Iniciar Motor DB
    $ingestor = new DbIngestor();
    $filas_insertadas = $ingestor->ingestBatch($all_data);

    echo "✅ Éxito! Se han inyectado $filas_insertadas nuevos registros atómicos organizados.\n";
    echo "🗑️ Los archivos PDF descargados temporalmente han cumplido su ciclo.\n";
} else {
    echo "No se encontraron datos listos para inyectar en DB.\n";
}
