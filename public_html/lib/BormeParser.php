<?php
// lib/BormeParser.php - Structural Analysis for OpenBorme

// Logic ported from pipeline/extract/extractor/parser_pdf.py
require_once __DIR__ . '/PdfParser.php';

class BormeParser
{

    private $parser;

    public function __construct()
    {
        $this->parser = new PdfParser();
    }

    public function parse_pdf($file_content, $province_name = "UNK")
    {
        // 1. Extract Raw Text
        $full_text = $this->parser->parseContent($file_content);

        // 2. Structural Analysis (Regex)
        $acts = [];

        // Regex to find starting of an act: digits - NAME
        // ported from: r'^(\d+) - (.*?)\.\s*$'

        // We look for patterns like: "1234 - NOMBRE EMPRESA."
        // Using fewer constraints to be robust against extraction noise
        $regex = '/(\d+)\s+-\s+([^\.]+?)\./';

        if (preg_match_all($regex, $full_text, $matches, PREG_OFFSET_CAPTURE)) {

            $count = count($matches[0]);

            for ($i = 0; $i < $count; $i++) {
                $act_id = $matches[1][$i][0];
                $company_name = trim($matches[2][$i][0]);

                // Content start/end
                $match_end_pos = $matches[0][$i][1] + strlen($matches[0][$i][0]);

                // End is start of next match or end of string
                $end_pos = ($i + 1 < $count) ? $matches[0][$i + 1][1] : strlen($full_text);

                $details = substr($full_text, $match_end_pos, $end_pos - $match_end_pos);
                $details = trim($details);

                // 3. Enrichment (Regex Fields)
                $act = [
                    'id' => "$province_name-$act_id",
                    'company' => $company_name,
                    'details' => $details,
                    'type' => 'OTROS',
                    'cif' => '',
                    'capital' => '',
                    'province' => $province_name
                ];

                // Act Type (Heuristic: First sentence or keyword)
                if (preg_match('/^(.*?)(?:\.|:)/', $details, $m)) {
                    $act['type'] = strtoupper(trim($m[1]));
                }

                // CIF
                if (preg_match('/[ABCDEFGHJNPQRSUVW][\s\-\.]?\d{2}[\s\-\.]?\d{3}[\s\-\.]?\d{3}/i', $details, $m)) {
                    $act['cif'] = strtoupper(str_replace([' ', '-', '.'], '', $m[0]));
                }

                // Capital
                if (preg_match('/Capital:\s*([\d\.,]+\s*Euros)/i', $details, $m)) {
                    $act['capital'] = $m[1];
                }

                $acts[] = $act;
            }
        }

        // Fallback: If minimal regex failed (maybe text is messy), return raw
        if (empty($acts)) {
            return [
                [
                    'id' => '0000',
                    'company' => 'Documento Completo (Sin Estructura)',
                    'details' => $full_text,
                    'type' => 'RAW',
                    'cif' => '',
                    'capital' => '',
                    'province' => $province_name
                ]
            ];
        }

        return $acts;
    }
}
