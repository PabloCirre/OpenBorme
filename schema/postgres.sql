-- OpenBorme Core Schema (PostgreSQL)
-- Versión alineada con el runtime PHP (Database.php)
-- Objetivo: compatibilidad 1:1 con SQLite para migraciones simples.

CREATE TABLE IF NOT EXISTS public.company (
    cif VARCHAR(20) PRIMARY KEY,
    name TEXT NOT NULL,
    province VARCHAR(50) NOT NULL
);

CREATE TABLE IF NOT EXISTS public.borme_acts (
    id VARCHAR(64) PRIMARY KEY, -- BORME-A-YYYY-NUM
    legacy_id VARCHAR(64),
    date VARCHAR(8) NOT NULL,   -- YYYYMMDD para mantener compatibilidad con SQLite actual
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

CREATE TABLE IF NOT EXISTS public.ingest_log (
    date VARCHAR(8) PRIMARY KEY,
    status VARCHAR(20) DEFAULT 'pending',
    acts_count INT DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_date ON public.borme_acts(date);
CREATE INDEX IF NOT EXISTS idx_type ON public.borme_acts(type);
CREATE INDEX IF NOT EXISTS idx_company ON public.borme_acts(company_name);
CREATE INDEX IF NOT EXISTS idx_company_uid ON public.borme_acts(company_uid);
CREATE INDEX IF NOT EXISTS idx_legacy_date ON public.borme_acts(legacy_id, date);
CREATE INDEX IF NOT EXISTS idx_event_group ON public.borme_acts(event_group);
CREATE INDEX IF NOT EXISTS idx_is_creation ON public.borme_acts(is_creation);
CREATE INDEX IF NOT EXISTS idx_is_dissolution ON public.borme_acts(is_dissolution);
CREATE INDEX IF NOT EXISTS idx_company_name_norm ON public.borme_acts(company_name_norm);
CREATE INDEX IF NOT EXISTS idx_date_province ON public.borme_acts(date, province);
