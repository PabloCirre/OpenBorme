<?php
// lib/BoeScraper.php - Logic to scrape and parse BOE summaries

class BoeScraper
{
    private $user_agent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36";

    /**
     * Fetches and parses the BORME summary for a specific date.
     * @param string $date YYYYMMDD
     * @return array ['sections' => [...], 'error' => null|string]
     */
    public function getSummary($date)
    {
        $year = substr($date, 0, 4);
        $month = substr($date, 4, 2);
        $day = substr($date, 6, 2);

        $boe_url = "https://www.boe.es/borme/dias/$year/$month/$day/";

        // Cache Logic: Check if we have a local copy for this date
        // Cache directory: /data/summaries/
        $cache_dir = __DIR__ . '/../data/summaries';
        if (!is_dir($cache_dir)) {
            mkdir($cache_dir, 0755, true);
        }

        $cache_file = "$cache_dir/$date.json";

        // Return cached if exists and valid
        if (file_exists($cache_file)) {
            $json = file_get_contents($cache_file);
            $data = json_decode($json, true);
            if ($data && isset($data['sections'])) {
                return $data;
            }
        }

        // Fetch content if not cached
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $boe_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $html = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code != 200 || !$html) {
            return ['sections' => [], 'error' => "No se pudo recuperar el sumario del BOE (HTTP $http_code)."];
        }

        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        $sections = [];

        // Parsing logic copied from sumario.php
        $items_nodes = $xpath->query('//li[.//a[contains(@href, "/borme/dias/")]]');

        foreach ($items_nodes as $node) {
            $link_node = $xpath->query('.//a[contains(@href, "/borme/dias/")]', $node)->item(0);
            if (!$link_node)
                continue;

            $href = $link_node->getAttribute('href');
            $text = trim($link_node->nodeValue);

            if (preg_match('/(BORME-[A-Z]-\d{4}-\d+-\d+)/', $href, $matches) || preg_match('/(BORME-[A-Z]-\d{4}-\d+)/', $href, $matches)) {
                $id = $matches[1];

                // Extract Label (Company Name)
                $p_nodes = $xpath->query('.//p', $node);
                $label = "";

                if ($p_nodes->length > 0) {
                    $label = trim($p_nodes->item(0)->nodeValue);
                }

                if (!$label || stripos($label, 'PDF (') !== false) {
                    $label = trim($node->textContent);
                    $label = str_replace($text, '', $label);
                    $label = trim($label);
                }

                $label = preg_replace('/^\d+\.\s*/', '', $label);
                $label = mb_strimwidth($label, 0, 100, "...");

                // Determine Section/Province
                $type = substr($id, 6, 1);

                if ($type == 'A')
                    $section_cat = "SECCIÓN PRIMERA: Empresarios";
                elseif ($type == 'B')
                    $section_cat = "SECCIÓN SEGUNDA: Otros";
                elseif ($type == 'C')
                    $section_cat = "SECCIÓN SEGUNDA: Anuncios";
                else
                    $section_cat = "OTRAS SECCIONES";

                $parts = explode('-', $id);
                $prov_code = end($parts);

                $provinces_map = [
                    '28' => 'MADRID',
                    '08' => 'BARCELONA',
                    '46' => 'VALENCIA',
                    '41' => 'SEVILLA',
                    '29' => 'MALAGA',
                    '48' => 'BIZKAIA',
                    '50' => 'ZARAGOZA',
                    '03' => 'ALICANTE',
                    '15' => 'A CORUÑA',
                    '36' => 'PONTEVEDRA',
                    '30' => 'MURCIA',
                    '07' => 'ILLES BALEARS',
                    '35' => 'LAS PALMAS',
                    '38' => 'S.C. TENERIFE',
                    '01' => 'ARABA/ÁLAVA',
                    '02' => 'ALBACETE',
                    '04' => 'ALMERÍA',
                    '05' => 'ÁVILA',
                    '06' => 'BADAJOZ',
                    '09' => 'BURGOS',
                    '10' => 'CÁCERES',
                    '11' => 'CÁDIZ',
                    '12' => 'CASTELLÓN',
                    '13' => 'CIUDAD REAL',
                    '14' => 'CÓRDOBA',
                    '16' => 'CUENCA',
                    '17' => 'GIRONA',
                    '18' => 'GRANADA',
                    '19' => 'GUADALAJARA',
                    '20' => 'GIPUZKOA',
                    '21' => 'HUELVA',
                    '22' => 'HUESCA',
                    '23' => 'JAÉN',
                    '24' => 'LEÓN',
                    '25' => 'LLEIDA',
                    '26' => 'LA RIOJA',
                    '27' => 'LUGO',
                    '31' => 'NAVARRA',
                    '32' => 'OURENSE',
                    '33' => 'ASTURIAS',
                    '34' => 'PALENCIA',
                    '37' => 'SALAMANCA',
                    '39' => 'CANTABRIA',
                    '40' => 'SEGOVIA',
                    '42' => 'SORIA',
                    '43' => 'TARRAGONA',
                    '44' => 'TERUEL',
                    '45' => 'TOLEDO',
                    '47' => 'VALLADOLID',
                    '49' => 'ZAMORA',
                    '51' => 'CEUTA',
                    '52' => 'MELILLA',
                    '99' => 'VARIOS', // Catch-all for unified/special sections
                    '98' => 'VARIOS'
                ];

                // Handle special logic for higher codes (often dedicated sections)
                if ((int) $prov_code > 52 && !isset($provinces_map[$prov_code])) {
                    $prov_name = "SECCIÓN ESPECIAL ($prov_code)";
                } else {
                    $prov_name = $provinces_map[$prov_code] ?? "PROVINCIA $prov_code";
                }

                $sections[$section_cat][$prov_name][] = [
                    'id' => $id,
                    'label' => $label ?: "Documento $id",
                    'url' => $href
                ];
            }
        }

        $result = ['sections' => $sections, 'error' => null];

        // Save to cache if we found data (don't cache empty unless it's a confirmed empty day like Sunday)
        // For now, caching everything that returns 200 is safer to avoid repeated hits on empty days
        if ($http_code == 200) {
            file_put_contents($cache_file, json_encode($result));
        }

        return $result;
    }
}
?>