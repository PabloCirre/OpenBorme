<?php

class ParserXml
{
    private $cif_pattern = '/\b[ABCDEFGHJNPQRSUVW][\s\-\.]?\d{2}[\s\-\.]?\d{3}[\s\-\.]?\d{3}\b/i';
    private $url_pattern = '/\b((?:https?:\/\/|www\.)[a-zA-Z0-9\-\.]+\.[a-z]{2,}(?:\/[^\s\),. ]*)?)\b/i';
    private $workers_pattern = '/\b(\d+)\s*(?:trabajadores|empleados|miembros de plantilla)\b/i';

    public function parse($file_path)
    {
        if (!file_exists($file_path))
            return null;

        $xml = simplexml_load_file($file_path);
        if (!$xml)
            return null;

        $identificador = (string) $xml->metadatos->identificador;
        $date = (string) $xml->metadatos->fecha_publicacion;
        $company_name = (string) $xml->metadatos->titulo;
        $act_type = (string) $xml->metadatos->departamento;

        $description_parts = [];
        if (isset($xml->texto) && isset($xml->texto->p)) {
            foreach ($xml->texto->p as $p) {
                $description_parts[] = (string) $p;
            }
        }
        $description = implode("\n", $description_parts);

        // Enrichment
        $cif = "";
        if (preg_match($this->cif_pattern, $description, $matches)) {
            $cif = $this->cleanValue($matches[0]);
        } elseif (preg_match($this->cif_pattern, $company_name, $matches)) {
            $cif = $this->cleanValue($matches[0]);
        }

        $url = "";
        if (preg_match($this->url_pattern, $description, $matches)) {
            $url = $matches[1];
        }

        $workers = "";
        if (preg_match($this->workers_pattern, $description, $matches)) {
            $workers = $matches[1];
        }

        return [
            'Date' => $date,
            'Section' => '2',
            'Province' => 'National',
            'Company Name' => trim($company_name),
            'CIF' => $cif,
            'Website' => $url,
            'Capital' => '',
            'Address' => '',
            'Workers' => $workers,
            'Act Type' => $act_type,
            'Details' => substr($description, 0, 500) . "...",
            'ID' => $identificador
        ];
    }

    private function cleanValue($val)
    {
        if (!$val)
            return "";
        $clean = preg_replace('/[\s\.\-]/', '', $val);
        return (strlen($clean) >= 9) ? strtoupper($clean) : trim($val);
    }
}
