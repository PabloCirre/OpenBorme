<?php

class BormeDownloader
{
    private $base_url = "https://www.boe.es/datosabiertos/api/borme/sumario/";
    private $storage_dir = "data";

    public function __construct($storage_dir = "data")
    {
        $this->storage_dir = $storage_dir;
        if (!is_dir($this->storage_dir)) {
            mkdir($this->storage_dir, 0777, true);
        }
    }

    public function download($days = 7)
    {
        $dates = [];
        for ($i = 0; $i < $days; $i++) {
            $dates[] = date('Ymd', strtotime("-$i days"));
        }
        $this->downloadDates($dates);
    }

    public function downloadRange($startDate, $endDate)
    {
        $current = strtotime($startDate);
        $last = strtotime($endDate);
        $dates = [];
        while ($current <= $last) {
            $dates[] = date('Ymd', $current);
            $current = strtotime('+1 day', $current);
        }
        $this->downloadDates($dates);
    }

    private function downloadDates($dates)
    {
        echo "Iniciando descarga de " . count($dates) . " días...\n";
        foreach ($dates as $date) {
            $this->processDate($date);
        }
    }

    private function processDate($date)
    {
        $url = $this->base_url . $date;
        $xml_content = $this->fetchUrl($url, ['Accept: application/xml']);

        if (!$xml_content) {
            echo "No hay datos para la fecha: $date\n";
            return;
        }

        $xml = simplexml_load_string($xml_content);
        if (!$xml)
            return;

        $date_dir = "{$this->storage_dir}/$date";
        if (!is_dir($date_dir))
            mkdir($date_dir, 0777, true);

        // Section 1 (PDFs by province)
        $sec1_dir = "$date_dir/section_A";
        if (!is_dir($sec1_dir))
            mkdir($sec1_dir, 0777, true);

        foreach ($xml->diario->seccion as $seccion) {
            if ((string) $seccion['codigo'] === 'A') {
                foreach ($seccion->item as $item) {
                    $id = (string) $item->identificador;
                    $pdf_url = (string) $item->url_pdf;
                    $filename = "$sec1_dir/$id.pdf";
                    if (!file_exists($filename)) {
                        echo "  Descargando PDF: $id\n";
                        $this->downloadFile($pdf_url, $filename);
                    }
                }
            }
        }

        // Section 2 (XML Individual Acts)
        $sec2_dir = "$date_dir/section_C";
        if (!is_dir($sec2_dir))
            mkdir($sec2_dir, 0777, true);

        foreach ($xml->diario->seccion as $seccion) {
            if ((string) $seccion['codigo'] === 'C') {
                foreach ($seccion->apartado as $apartado) {
                    foreach ($apartado->item as $item) {
                        $id = (string) $item->identificador;
                        $act_xml_url = "https://www.boe.es/datosabiertos/api/borme/acta/$id";
                        $filename = "$sec2_dir/$id.xml";
                        if (!file_exists($filename)) {
                            echo "  Descargando XML: $id\n";
                            $this->downloadFile($act_xml_url, $filename, ['Accept: application/xml']);
                        }
                    }
                }
            }
        }
    }

    private function fetchUrl($url, $headers = [])
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERAGENT, 'SuperBorme-PHP/1.0');
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    private function downloadFile($url, $path, $headers = [])
    {
        $fp = fopen($path, 'w+');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERAGENT, 'SuperBorme-PHP/1.0');
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
    }
}
