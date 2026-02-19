-- OpenBorme Core Schema (PostgreSQL)
-- Version: 1.0.0

CREATE TABLE IF NOT EXISTS public.borme_acts (
    id VARCHAR(64) PRIMARY KEY, -- BORME-A-YYYY-NUM
    date DATE NOT NULL,
    section CHAR(1) NOT NULL CHECK (section IN ('A', 'B', 'C')), -- A=Section I, B=Section II
    type VARCHAR(100) NOT NULL, -- CONSTITUCION, NOMBRAMIENTO...
    province VARCHAR(50) NOT NULL,
    company_name TEXT NOT NULL,
    company_uid VARCHAR(20), -- CIF
    raw_text TEXT,
    hash_md5 VARCHAR(32) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_borme_date ON public.borme_acts(date);
CREATE INDEX idx_borme_company ON public.borme_acts(company_name);
CREATE INDEX idx_borme_uid ON public.borme_acts(company_uid);

-- Table for tracking ingestion status per day
CREATE TABLE IF NOT EXISTS public.ingest_log (
    date DATE PRIMARY KEY,
    status VARCHAR(20) DEFAULT 'pending', -- pending, processing, done, error
    acts_count INT DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
