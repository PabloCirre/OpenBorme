<?php
/**
 * Clase encargada de coger los Arrays del parser de PDFs y convertirlos en Registros
 * en nuestra base de datos relacional local (SQLite).
 */

require_once __DIR__ . '/../db/Database.php';
require_once __DIR__ . '/ActNormalizer.php';

class DbIngestor
{

    private $db;
    private $stmtAct;
    private $stmtCompany;
    private $driver;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->driver = Database::getDriver();
        $this->prepareStatements();
    }

    private function prepareStatements()
    {
        if ($this->driver === 'pgsql') {
            $sqlAct = "INSERT INTO borme_acts (
                            id, legacy_id, date, section, type, province, company_name, company_uid, raw_text, capital, hash_md5, created_at,
                            normalized_type, event_group, is_creation, is_dissolution, company_name_norm
                       )
                       VALUES (
                            :id, :legacy_id, :date, :section, :type, :province, :company_name, :company_uid, :raw_text, :capital, :hash_md5, CURRENT_TIMESTAMP,
                            :normalized_type, :event_group, :is_creation, :is_dissolution, :company_name_norm
                       )
                       ON CONFLICT (id) DO UPDATE SET
                            legacy_id = EXCLUDED.legacy_id,
                            date = EXCLUDED.date,
                            section = EXCLUDED.section,
                            type = EXCLUDED.type,
                            province = EXCLUDED.province,
                            company_name = EXCLUDED.company_name,
                            company_uid = EXCLUDED.company_uid,
                            raw_text = EXCLUDED.raw_text,
                            capital = EXCLUDED.capital,
                            hash_md5 = EXCLUDED.hash_md5,
                            normalized_type = EXCLUDED.normalized_type,
                            event_group = EXCLUDED.event_group,
                            is_creation = EXCLUDED.is_creation,
                            is_dissolution = EXCLUDED.is_dissolution,
                            company_name_norm = EXCLUDED.company_name_norm";

            $sqlComp = "INSERT INTO company (cif, name, province) 
                        VALUES (:cif, :name, :province)
                        ON CONFLICT (cif) DO NOTHING";
        } else {
            // SQLite: INSERT OR IGNORE para evitar duplicados.
            $sqlAct = "INSERT OR IGNORE INTO borme_acts (
                            id, legacy_id, date, section, type, province, company_name, company_uid, raw_text, capital, hash_md5, created_at,
                            normalized_type, event_group, is_creation, is_dissolution, company_name_norm
                       )
                       VALUES (
                            :id, :legacy_id, :date, :section, :type, :province, :company_name, :company_uid, :raw_text, :capital, :hash_md5, CURRENT_TIMESTAMP,
                            :normalized_type, :event_group, :is_creation, :is_dissolution, :company_name_norm
                       )";

            $sqlComp = "INSERT OR IGNORE INTO company (cif, name, province) 
                        VALUES (:cif, :name, :province)";
        }

        $this->stmtAct = $this->db->prepare($sqlAct);

        $this->stmtCompany = $this->db->prepare($sqlComp);
    }

    /**
     * Ingiere un lote (Array) de actos procedentes del pdf_parser y xml_parser
     */
    public function ingestBatch(array $records)
    {
        if (empty($records))
            return 0;

        $insertedCount = 0;

        // Arrancamos una Transacción SQL para que al inyectar miles de registros 
        // tarde 0.1 segundos en vez de 10 minutos (Optimización In-Memory)
        $this->db->beginTransaction();

        try {
            foreach ($records as $row) {
                // Normalizamos claves desde los parsers (que vienen con Mayúsculas y espacios)
                $id = $row['id'] ?? $row['ID'] ?? uniqid();
                $date = $row['Date'] ?? $row['date'] ?? null;
                $type = $row['type'] ?? $row['Act Type'] ?? 'UNKNOWN';
                $province = $row['province'] ?? $row['Province'] ?? 'UNKNOWN';
                $company = $row['company'] ?? $row['Company Name'] ?? 'UNKNOWN';
                $cif = $row['cif'] ?? $row['CIF'] ?? null;
                $details = $row['details'] ?? $row['Details'] ?? '';
                $capital = $row['capital'] ?? $row['Capital'] ?? null;
                $legacyId = $id;
                if ($legacyId === '') {
                    $legacyId = uniqid();
                }
                // Evita colisiones históricas de ID de PDF (PROVINCIA-numero) entre fechas.
                if (!preg_match('/^BORME-[A-Z]-/i', $legacyId) && !preg_match('/^\d{8}-/', $legacyId) && preg_match('/^\d{8}$/', (string) $date)) {
                    $id = $date . '-' . $legacyId;
                } else {
                    $id = $legacyId;
                }

                // Generamos un HASH MD5 único combinando fecha, id y texto para evitar duplicados reales
                $hash_md5 = md5(($date ?? '') . $id . $details);
                $norm = ActNormalizer::normalize([
                    'type' => $type,
                    'details' => $details,
                    'company_name' => $company,
                    'province' => $province,
                ]);

                $this->stmtAct->execute([
                    ':id' => $id,
                    ':legacy_id' => $legacyId,
                    ':date' => $date,
                    // Si el acto viene marcado como RAW, asumimos sección B (anuncios); por defecto sección A
                    ':section' => ($type === 'RAW') ? 'B' : 'A',
                    ':type' => $type,
                    ':province' => $province,
                    ':company_name' => $company,
                    ':company_uid' => $cif,
                    ':raw_text' => $details,
                    ':capital' => $capital,
                    ':hash_md5' => $hash_md5,
                    ':normalized_type' => $norm['normalized_type'],
                    ':event_group' => $norm['event_group'],
                    ':is_creation' => $norm['is_creation'],
                    ':is_dissolution' => $norm['is_dissolution'],
                    ':company_name_norm' => $norm['company_name_norm'],
                ]);

                if ($this->stmtAct->rowCount() > 0) {
                    $insertedCount++;
                }

                // Si encontramos un CIF válido, lo añadimos al diccionario de empresas
                if (!empty($cif) && !empty($company)) {
                    $this->stmtCompany->execute([
                        ':cif' => $cif,
                        ':name' => $company,
                        ':province' => $province
                    ]);
                }
            }

            $this->db->commit();

        } catch (Exception $e) {
            $this->db->rollBack();
            die("❌ Error en la Base de Datos durante el Ingest: " . $e->getMessage() . "\n");
        }

        return $insertedCount;
    }
}
?>
