<?php
/**
 * Genera y mantiene una conexión a una base de datos local SQLite.
 * Esta solución es ideal porque no requiere instalar ningún motor local de DB como MySQL/Postgres.
 * OpenBorme puede almacenar millones de registros directamente en este archivo local.
 */

class Database
{
    private static $instance = null;
    private $pdo;
    private $dbPath;

    private function __construct()
    {
        // La DB se guardará en la carpeta /pipeline/data/openborme.sqlite
        $this->dbPath = __DIR__ . '/../data/openborme.sqlite';

        // Aseguramos que existe el directorio data
        if (!is_dir(dirname($this->dbPath))) {
            mkdir(dirname($this->dbPath), 0777, true);
        }

        try {
            $this->pdo = new PDO('sqlite:' . $this->dbPath);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->initSchema();
        } catch (PDOException $e) {
            die("❌ Error catastrófico conectando a SQLite: " . $e->getMessage());
        }
    }

    private function initSchema()
    {
        // Creamos la estructura automáticamente la primera vez
        $sql = "
        CREATE TABLE IF NOT EXISTS company (
            cif TEXT PRIMARY KEY,
            name TEXT NOT NULL,
            province TEXT NOT NULL
        );

        CREATE TABLE IF NOT EXISTS borme_acts (
            id TEXT PRIMARY KEY,
            date TEXT NOT NULL,
            section TEXT NOT NULL,
            type TEXT NOT NULL,
            province TEXT NOT NULL,
            company_name TEXT NOT NULL,
            company_uid TEXT,
            raw_text TEXT,
            capital TEXT,
            hash_md5 TEXT UNIQUE NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        -- Índices para que las consultas vuelen
        CREATE INDEX IF NOT EXISTS idx_date ON borme_acts(date);
        CREATE INDEX IF NOT EXISTS idx_type ON borme_acts(type);
        CREATE INDEX IF NOT EXISTS idx_company ON borme_acts(company_name);
        ";

        $this->pdo->exec($sql);
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }
}
?>