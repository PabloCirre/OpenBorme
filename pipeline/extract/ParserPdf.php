<?php

require_once __DIR__ . '/../../vendor/autoload.php';

class ParserPdf
{
    private $cif_pattern = '/\b[ABCDEFGHJNPQRSUVW][\s\-\.]?\d{2}[\s\-\.]?\d{3}[\s\-\.]?\d{3}\b/i';
    private $url_pattern = '/\b((? :https?:\/\/|www\.)[a-zA-Z0-9\-\.]+\.[a-z]{2,}(?:\/[^\s\),. ]*) ?)\b/i';
    private $capital_pattern = '/Capital:\s*([\d\.,]+\s*Euros)/i';
    private $address_pattern = '/Domicilio:\s*(.*?)\.\s/i';
    private $workers_pattern = '/\b(\d+)\s*(? :trabajadores|empleados|miembros de plantilla)\b/i';

    public function parse($file_path, $province_name)
    {
        $acts = [];
        if (!file_exists($file_path))
            return $acts;

        try {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($file_path);
            $text = $pdf->getText();

            // Split by company names (BOLD usually, but here we look for common markers)
            // Section 1 acts usually start with a number followed by the company name
            $parts = preg_split('/\n(\d+)\s+-\s+/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

            for ($i = 1; $i < count($parts); $i += 2) {
                $act_id = $parts[$i];
                $content = $parts[$i + 1] ?? "";

                $lines = explode("\n", trim($content));
                $company_name = $lines[0];

                // Enrichment
                $cif = "";
                if (preg_match($this->cif_pattern, $content, $matches)) {
                    $cif = $this->cleanValue($matches[0]);
                }

                $url = "";
                if (preg_match($this->url_pattern, $content, $matches)) {
                    $url = $matches[1];
                }

                $capital = "";
                if (preg_match($this->capital_pattern, $content, $matches)) {
                    $capital = $matches[1];
                }

                $address = "";
                if (preg_match($this->address_pattern, $content, $matches)) {
                    $address = $matches[1];
                }

                $workers = "";
                if (preg_match($this->workers_pattern, $content, $matches)) {
                    $workers = $matches[1];
                }

                $act_type = explode('.', $content)[0] ?? "Other";

                $acts[] = [
                    'Date' => '',
                    'Section' => '1',
                    'Province' => $province_name,
                    'Company Name' => $company_name,
                    'CIF' => $cif,
                    'Website' => $url,
                    'Capital' => $capital,
                    'Address' => $address,
                    'Workers' => $workers,
                    'Act Type' => $act_type,
                    'Details' => substr($content, 0, 500) . "...",
                    'ID' => "$province_name-$act_id"
                ];
            }
        } catch (\Exception $e) {
            echo "Error parseando PDF $file_path: " . $e->getMessage() . "\n";
        }

        return $acts;
    }

    private function cleanValue($val)
    {
        if (!$val)
            return "";
        $clean = preg_replace('/[\s\.\-]/', '', $val);
        return (strlen($clean) >= 9) ? strtoupper($clean) : trim($val);
    }
}
