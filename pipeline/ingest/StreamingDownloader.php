<?php

require_once __DIR__ . '/BormeDownloader.php';
require_once __DIR__ . '/../extract/ParserPdf.php';
require_once __DIR__ . '/ParserXml.php';
require_once __DIR__ . '/DbIngestor.php';

/**
 * Extiende el downloader para procesar en streaming:
 *  - No persiste ficheros en disco (usa tmpfile).
 *  - Cada PDF/XML se parsea al vuelo y se ingesta en la DB.
 */
class StreamingDownloader extends BormeDownloader
{
    private $pdfParser;
    private $xmlParser;
    private $ingestor;
    private $dateContext;

    public function __construct($storage_dir = 'data')
    {
        parent::__construct($storage_dir, true); // stream_only = true
        $this->pdfParser = new ParserPdf();
        $this->xmlParser = new ParserXml();
        $this->ingestor = new DbIngestor();
    }

    /**
     * Hook llamado por la clase base tras descargar un fichero.
     */
    protected function onDocumentDownloaded($path, $type, $date, $id)
    {
        if ($type === 'pdf') {
            $acts = $this->pdfParser->parse($path, $this->guessProvinceFromId($id));
            // Inyectamos fecha
            foreach ($acts as &$a) {
                $a['Date'] = $date;
            }
            if (!empty($acts)) {
                $this->ingestor->ingestBatch($acts);
            }
        } elseif ($type === 'xml') {
            $act = $this->xmlParser->parse($path);
            if ($act) {
                if (empty($act['Date'])) {
                    $act['Date'] = $date;
                }
                $this->ingestor->ingestBatch([$act]);
            }
        }
    }

    /**
     * Los IDs de PDF tienen la forma BORME-A-YYYY-PROV-XX, usamos PROV para mapear.
     */
    private function guessProvinceFromId($id)
    {
        if (preg_match('/BORME-[A-Z]-\\d{4}-(\\d{2})-\\d+/', $id, $m)) {
            $map = [
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
            return $map[$m[1]] ?? 'UNKNOWN';
        }
        return 'UNKNOWN';
    }
}

