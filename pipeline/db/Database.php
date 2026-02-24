<?php
/**
 * Capa de conexión de OpenBorme.
 * Soporta SQLite (por defecto) y PostgreSQL remoto.
 */

class Database
{
    private static $instance = null;
    private $pdo;
    private $dbPath;
    private $driver;
    private $dsn;

    private function __construct()
    {
        $this->driver = $this->resolveDriver();

        try {
            if ($this->driver === 'pgsql') {
                $this->connectPostgres();
            } else {
                $this->connectSqlite();
            }

            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->initSchema();
        } catch (PDOException $e) {
            die("❌ Error conectando a {$this->driver}: " . $e->getMessage());
        } catch (RuntimeException $e) {
            die("❌ Error de configuración de BD: " . $e->getMessage());
        }
    }

    private function resolveDriver()
    {
        $fromEnv = strtolower(trim((string) getenv('OPENBORME_DB_DRIVER')));
        if ($fromEnv === 'sqlite' || $fromEnv === 'pgsql') {
            return $fromEnv;
        }

        $dsn = trim((string) getenv('OPENBORME_DB_DSN'));
        if (strpos($dsn, 'pgsql:') === 0) {
            return 'pgsql';
        }

        $pgHost = trim((string) (getenv('OPENBORME_PG_HOST') ?: getenv('OPENBORME_DB_HOST')));
        $pgDb = trim((string) (getenv('OPENBORME_PG_DBNAME') ?: getenv('OPENBORME_DB_NAME')));
        if ($pgHost !== '' && $pgDb !== '') {
            return 'pgsql';
        }

        return 'sqlite';
    }

    private function connectSqlite()
    {
        $envPath = getenv('OPENBORME_SQLITE_PATH');
        if ($envPath !== false && trim($envPath) !== '') {
            $this->dbPath = trim($envPath);
        } else {
            $this->dbPath = __DIR__ . '/../data/openborme.sqlite';
        }

        if (!is_dir(dirname($this->dbPath))) {
            mkdir(dirname($this->dbPath), 0777, true);
        }

        $this->dsn = 'sqlite:' . $this->dbPath;
        $this->pdo = new PDO($this->dsn);
    }

    private function connectPostgres()
    {
        $dsn = trim((string) getenv('OPENBORME_DB_DSN'));
        if ($dsn === '') {
            $host = trim((string) (getenv('OPENBORME_PG_HOST') ?: getenv('OPENBORME_DB_HOST')));
            $port = trim((string) (getenv('OPENBORME_PG_PORT') ?: getenv('OPENBORME_DB_PORT') ?: '5432'));
            $dbName = trim((string) (getenv('OPENBORME_PG_DBNAME') ?: getenv('OPENBORME_DB_NAME')));

            if ($host === '' || $dbName === '') {
                throw new RuntimeException(
                    "Falta OPENBORME_DB_DSN o el par OPENBORME_PG_HOST/OPENBORME_PG_DBNAME."
                );
            }

            $dsn = "pgsql:host={$host};port={$port};dbname={$dbName}";
        }

        $user = (string) (getenv('OPENBORME_DB_USER') ?: getenv('OPENBORME_PG_USER') ?: '');
        $pass = (string) (getenv('OPENBORME_DB_PASS') ?: getenv('OPENBORME_PG_PASS') ?: '');

        $this->dsn = $dsn;
        $this->dbPath = null;
        $this->pdo = new PDO($dsn, $user, $pass);
    }

    private function initSchema()
    {
        if ($this->driver === 'pgsql') {
            $this->initSchemaPostgres();
        } else {
            $this->initSchemaSqlite();
        }

        $this->ensureCompatibilityColumns();
    }

    private function initSchemaSqlite()
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS company (
            cif TEXT PRIMARY KEY,
            name TEXT NOT NULL,
            province TEXT NOT NULL
        );

        CREATE TABLE IF NOT EXISTS borme_acts (
            id TEXT PRIMARY KEY,
            legacy_id TEXT,
            date TEXT NOT NULL,
            section TEXT NOT NULL,
            type TEXT NOT NULL,
            province TEXT NOT NULL,
            company_name TEXT NOT NULL,
            company_uid TEXT,
            raw_text TEXT,
            capital TEXT,
            hash_md5 TEXT UNIQUE NOT NULL,
            normalized_type TEXT DEFAULT 'OTROS',
            event_group TEXT DEFAULT 'OTHER',
            is_creation INTEGER DEFAULT 0,
            is_dissolution INTEGER DEFAULT 0,
            company_name_norm TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS ingest_log (
            date TEXT PRIMARY KEY,
            status TEXT DEFAULT 'pending',
            acts_count INTEGER DEFAULT 0,
            last_updated DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE INDEX IF NOT EXISTS idx_date ON borme_acts(date);
        CREATE INDEX IF NOT EXISTS idx_type ON borme_acts(type);
        CREATE INDEX IF NOT EXISTS idx_company ON borme_acts(company_name);
        CREATE INDEX IF NOT EXISTS idx_company_uid ON borme_acts(company_uid);
        CREATE INDEX IF NOT EXISTS idx_date_province ON borme_acts(date, province);
        ";

        $this->pdo->exec($sql);
    }

    private function initSchemaPostgres()
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS company (
            cif VARCHAR(20) PRIMARY KEY,
            name TEXT NOT NULL,
            province VARCHAR(50) NOT NULL
        );

        CREATE TABLE IF NOT EXISTS borme_acts (
            id VARCHAR(64) PRIMARY KEY,
            legacy_id VARCHAR(64),
            date VARCHAR(8) NOT NULL,
            section CHAR(1) NOT NULL CHECK (section IN ('A', 'B', 'C')),
            type TEXT NOT NULL,
            province VARCHAR(50) NOT NULL,
            company_name TEXT NOT NULL,
            company_uid VARCHAR(20),
            raw_text TEXT,
            capital TEXT,
            hash_md5 VARCHAR(32) UNIQUE NOT NULL,
            normalized_type VARCHAR(64) DEFAULT 'OTROS',
            event_group VARCHAR(16) DEFAULT 'OTHER',
            is_creation SMALLINT DEFAULT 0,
            is_dissolution SMALLINT DEFAULT 0,
            company_name_norm TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS ingest_log (
            date VARCHAR(8) PRIMARY KEY,
            status VARCHAR(20) DEFAULT 'pending',
            acts_count INTEGER DEFAULT 0,
            last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE INDEX IF NOT EXISTS idx_date ON borme_acts(date);
        CREATE INDEX IF NOT EXISTS idx_type ON borme_acts(type);
        CREATE INDEX IF NOT EXISTS idx_company ON borme_acts(company_name);
        CREATE INDEX IF NOT EXISTS idx_company_uid ON borme_acts(company_uid);
        CREATE INDEX IF NOT EXISTS idx_date_province ON borme_acts(date, province);
        ";

        $this->pdo->exec($sql);
    }

    private function ensureCompatibilityColumns()
    {
        if ($this->driver === 'pgsql') {
            $this->ensurePostgresColumn('borme_acts', 'legacy_id', "VARCHAR(64)");
            $this->ensurePostgresColumn('borme_acts', 'normalized_type', "VARCHAR(64) DEFAULT 'OTROS'");
            $this->ensurePostgresColumn('borme_acts', 'event_group', "VARCHAR(16) DEFAULT 'OTHER'");
            $this->ensurePostgresColumn('borme_acts', 'is_creation', "SMALLINT DEFAULT 0");
            $this->ensurePostgresColumn('borme_acts', 'is_dissolution', "SMALLINT DEFAULT 0");
            $this->ensurePostgresColumn('borme_acts', 'company_name_norm', "TEXT");
            $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_legacy_date ON borme_acts(legacy_id, date)");
            $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_event_group ON borme_acts(event_group)");
            $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_is_creation ON borme_acts(is_creation)");
            $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_is_dissolution ON borme_acts(is_dissolution)");
            $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_company_name_norm ON borme_acts(company_name_norm)");
            $this->backfillLegacyIdIfMissing();
            return;
        }

        $this->ensureSqliteColumn('borme_acts', 'legacy_id', "TEXT");
        $this->ensureSqliteColumn('borme_acts', 'normalized_type', "TEXT DEFAULT 'OTROS'");
        $this->ensureSqliteColumn('borme_acts', 'event_group', "TEXT DEFAULT 'OTHER'");
        $this->ensureSqliteColumn('borme_acts', 'is_creation', "INTEGER DEFAULT 0");
        $this->ensureSqliteColumn('borme_acts', 'is_dissolution', "INTEGER DEFAULT 0");
        $this->ensureSqliteColumn('borme_acts', 'company_name_norm', "TEXT");
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_legacy_date ON borme_acts(legacy_id, date)");
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_event_group ON borme_acts(event_group)");
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_is_creation ON borme_acts(is_creation)");
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_is_dissolution ON borme_acts(is_dissolution)");
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_company_name_norm ON borme_acts(company_name_norm)");
        $this->backfillLegacyIdIfMissing();
    }

    private function backfillLegacyIdIfMissing()
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) AS c FROM borme_acts WHERE legacy_id IS NULL OR legacy_id = ''");
        $row = $stmt->fetch();
        $missing = (int) ($row['c'] ?? 0);
        if ($missing > 0) {
            $this->pdo->exec("UPDATE borme_acts SET legacy_id = id WHERE legacy_id IS NULL OR legacy_id = ''");
        }
    }

    private function ensureSqliteColumn($table, $column, $definition)
    {
        $stmt = $this->pdo->query("PRAGMA table_info($table)");
        $columns = [];
        foreach ($stmt->fetchAll() as $row) {
            $columns[] = $row['name'];
        }

        if (!in_array($column, $columns, true)) {
            $this->pdo->exec("ALTER TABLE $table ADD COLUMN $column $definition");
        }
    }

    private function ensurePostgresColumn($table, $column, $definition)
    {
        $stmt = $this->pdo->prepare("
            SELECT 1
            FROM information_schema.columns
            WHERE table_schema = 'public'
              AND table_name = :table_name
              AND column_name = :column_name
            LIMIT 1
        ");
        $stmt->execute([':table_name' => $table, ':column_name' => $column]);
        if ($stmt->fetchColumn()) {
            return;
        }

        $this->pdo->exec("ALTER TABLE $table ADD COLUMN $column $definition");
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }

    public static function getDriver()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->driver;
    }

    public static function getDbPath()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->dbPath;
    }

    public static function getDsn()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->dsn;
    }
}
?>
