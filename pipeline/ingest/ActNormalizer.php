<?php

/**
 * Normaliza actos societarios para analítica de altas/bajas.
 */
class ActNormalizer
{
    public static function normalize(array $input)
    {
        $type = (string) ($input['type'] ?? '');
        $details = (string) ($input['details'] ?? '');
        $companyName = (string) ($input['company_name'] ?? '');
        $province = (string) ($input['province'] ?? 'UNKNOWN');

        $typeNormText = self::normalizeText($type);
        $detailsNormText = self::normalizeText($details);
        $combined = trim($typeNormText . ' ' . $detailsNormText);

        $normalizedType = self::normalizedType($combined);
        $isCreation = self::matches($combined, [
            '/\bCONSTITUCION\b/',
            '/\bSOCIEDAD DE NUEVA CREACION\b/',
            '/\bNUEVA SOCIEDAD\b/',
            '/\bCONSTITUIR\b/',
            '/\bINICIO DE ACTIVIDAD\b/',
        ]);
        $isDissolution = self::matches($combined, [
            '/\bDISOLUCION\b/',
            '/\bEXTINCION\b/',
            '/\bLIQUIDACION\b/',
            '/\bCESE\b/',
        ]);

        $eventGroup = 'OTHER';
        if ($isCreation && !$isDissolution) {
            $eventGroup = 'CREATION';
        } elseif ($isDissolution && !$isCreation) {
            $eventGroup = 'DISSOLUTION';
        } elseif ($isCreation && $isDissolution) {
            $eventGroup = 'MIXED';
        }

        return [
            'normalized_type' => $normalizedType,
            'event_group' => $eventGroup,
            'is_creation' => $isCreation ? 1 : 0,
            'is_dissolution' => $isDissolution ? 1 : 0,
            'company_name_norm' => self::normalizeCompanyName($companyName),
            'province_norm' => self::normalizeProvince($province),
        ];
    }

    private static function normalizedType($combined)
    {
        $map = [
            'CONSTITUCION' => '/\bCONSTITUCION\b/',
            'DISOLUCION' => '/\bDISOLUCION\b|\bEXTINCION\b|\bLIQUIDACION\b/',
            'CESE' => '/\bCESE\b/',
            'NOMBRAMIENTO' => '/\bNOMBRAMIENTO\b/',
            'REVOCACION' => '/\bREVOCACION\b/',
            'MODIFICACION' => '/\bMODIFICACION\b/',
            'AMPLIACION_CAPITAL' => '/\bAMPLIACION DE CAPITAL\b/',
            'REDUCCION_CAPITAL' => '/\bREDUCCION DE CAPITAL\b/',
            'TRANSFORMACION' => '/\bTRANSFORMACION\b/',
            'FUSION' => '/\bFUSION\b/',
            'ESCISION' => '/\bESCISION\b/',
            'CAMBIO_DOMICILIO' => '/\bCAMBIO DE DOMICILIO\b/',
            'CONCURSO' => '/\bCONCURSO\b/',
            'REACTIVACION' => '/\bREACTIVACION\b/',
        ];

        foreach ($map as $label => $regex) {
            if (preg_match($regex, $combined)) {
                return $label;
            }
        }

        return 'OTROS';
    }

    private static function normalizeCompanyName($companyName)
    {
        $normalized = self::normalizeText($companyName);
        return preg_replace('/\s+/', ' ', trim($normalized));
    }

    private static function normalizeProvince($province)
    {
        $normalized = self::normalizeText($province);
        return $normalized === '' ? 'UNKNOWN' : $normalized;
    }

    private static function matches($text, array $patterns)
    {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text)) {
                return true;
            }
        }
        return false;
    }

    private static function normalizeText($text)
    {
        $text = (string) $text;
        $text = strtr($text, [
            'á' => 'a',
            'à' => 'a',
            'ä' => 'a',
            'â' => 'a',
            'Á' => 'A',
            'À' => 'A',
            'Ä' => 'A',
            'Â' => 'A',
            'é' => 'e',
            'è' => 'e',
            'ë' => 'e',
            'ê' => 'e',
            'É' => 'E',
            'È' => 'E',
            'Ë' => 'E',
            'Ê' => 'E',
            'í' => 'i',
            'ì' => 'i',
            'ï' => 'i',
            'î' => 'i',
            'Í' => 'I',
            'Ì' => 'I',
            'Ï' => 'I',
            'Î' => 'I',
            'ó' => 'o',
            'ò' => 'o',
            'ö' => 'o',
            'ô' => 'o',
            'Ó' => 'O',
            'Ò' => 'O',
            'Ö' => 'O',
            'Ô' => 'O',
            'ú' => 'u',
            'ù' => 'u',
            'ü' => 'u',
            'û' => 'u',
            'Ú' => 'U',
            'Ù' => 'U',
            'Ü' => 'U',
            'Û' => 'U',
            'ñ' => 'n',
            'Ñ' => 'N',
        ]);

        $text = strtoupper($text);
        return trim(preg_replace('/\s+/', ' ', $text));
    }
}

