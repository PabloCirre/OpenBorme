<?php
/**
 * Clase encargada de coger los Arrays del parser de PDFs y convertirlos en Registros
 * en nuestra base de datos relacional local (SQLite).
 */

require_once __DIR__ . '/../db/Database.php';

class DbIngestor
{

    private $db;
    private $stmtAct;
    private $stmtCompany;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->prepareStatements();
    }

    private function prepareStatements()
    {
        // SQL para inyectar Actos
        // Usamos INSERT OR IGNORE para no duplicar actos si lanzamos el script dos veces el mismo día
        $sqlAct = "INSERT OR IGNORE INTO borme_acts (id, date, section, type, province, company_name, company_uid, raw_text, capital, hash_md5, created_at)
                   VALUES (:id, :date, :section, :type, :province, :company_name, :company_uid, :raw_text, :capital, :hash_md5, CURRENT_TIMESTAMP)";

        $this->stmtAct = $this->db->prepare($sqlAct);

        // SQL para inyectar Empresas (Diccionario Único)
        $sqlComp = "INSERT OR IGNORE INTO company (cif, name, province) 
                    VALUES (:cif, :name, :province)";

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
                // Generamos un HASH MD5 único combinando el texto y la fecha para evitar duplicados
                $hash_md5 = md5($row['Date'] . ($row['id'] ?? '') . ($row['details'] ?? ''));

                $this->stmtAct->execute([
                    ':id' => $row['id'] ?? uniqid(),
                    ':date' => $row['Date'],
                    // Si el acto tiene tipo y nombre, asumimos Sección II, si no Sección I
                    ':section' => ($row['type'] == 'RAW') ? 'B' : 'A',
                    ':type' => $row['type'] ?? 'UNKNOWN',
                    ':province' => $row['province'] ?? 'UNKNOWN',
                    ':company_name' => $row['company'] ?? 'UNKNOWN',
                    ':company_uid' => $row['cif'] ?? null,
                    ':raw_text' => $row['details'] ?? '',
                    ':capital' => $row['capital'] ?? null,
                    ':hash_md5' => $hash_md5
                ]);

                if ($this->stmtAct->rowCount() > 0) {
                    $insertedCount++;
                }

                // Si encontramos un CIF válido, lo añadimos al diccionario de empresas
                if (!empty($row['cif']) && !empty($row['company'])) {
                    $this->stmtCompany->execute([
                        ':cif' => $row['cif'],
                        ':name' => $row['company'],
                        ':province' => $row['province']
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