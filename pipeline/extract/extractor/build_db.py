#!/usr/bin/env python3
"""
OpenBorme Python DB Builder

Construye/actualiza `pipeline/data/openborme.sqlite` directamente desde BOE
siguiendo el mismo modelo que el runtime PHP (SQLite + normalización de actos).

Uso:
  python3 pipeline/extract/extractor/build_db.py --start 2020-01-01 --end 2026-02-24 --resume

Opciones:
  --start YYYY-MM-DD
  --end YYYY-MM-DD
  --db /ruta/openborme.sqlite
  --checkpoint /ruta/checkpoint.txt
  --resume
  --sleep-ms 0
  --skip-pdf
  --skip-xml
"""

from __future__ import annotations

import argparse
import datetime as dt
import hashlib
import json
import os
import re
import sqlite3
import tempfile
import time
import xml.etree.ElementTree as ET
from dataclasses import dataclass
from pathlib import Path
from typing import Dict, Iterable, List, Optional, Tuple

import pypdf
import requests


PROVINCE_MAP = {
    "01": "ALAVA",
    "02": "ALBACETE",
    "03": "ALICANTE",
    "04": "ALMERIA",
    "05": "AVILA",
    "06": "BADAJOZ",
    "07": "ILLES BALEARS",
    "08": "BARCELONA",
    "09": "BURGOS",
    "10": "CACERES",
    "11": "CADIZ",
    "12": "CASTELLON",
    "13": "CIUDAD REAL",
    "14": "CORDOBA",
    "15": "A CORUÑA",
    "16": "CUENCA",
    "17": "GIRONA",
    "18": "GRANADA",
    "19": "GUADALAJARA",
    "20": "GIPUZKOA",
    "21": "HUELVA",
    "22": "HUESCA",
    "23": "JAEN",
    "24": "LEON",
    "25": "LLEIDA",
    "26": "LA RIOJA",
    "27": "LUGO",
    "28": "MADRID",
    "29": "MALAGA",
    "30": "MURCIA",
    "31": "NAVARRA",
    "32": "OURENSE",
    "33": "ASTURIAS",
    "34": "PALENCIA",
    "35": "LAS PALMAS",
    "36": "PONTEVEDRA",
    "37": "SALAMANCA",
    "38": "SANTA CRUZ DE TENERIFE",
    "39": "CANTABRIA",
    "40": "SEGOVIA",
    "41": "SEVILLA",
    "42": "SORIA",
    "43": "TARRAGONA",
    "44": "TERUEL",
    "45": "TOLEDO",
    "46": "VALENCIA",
    "47": "VALLADOLID",
    "48": "BIZKAIA",
    "49": "ZAMORA",
    "50": "ZARAGOZA",
    "51": "CEUTA",
    "52": "MELILLA",
}

SUMMARY_URL = "https://www.boe.es/datosabiertos/api/borme/sumario/{date}"
ACTA_URL = "https://www.boe.es/datosabiertos/api/borme/acta/{id}"


CIF_PATTERN = re.compile(r"\b[ABCDEFGHJNPQRSUVW][\s\-\.]?\d{2}[\s\-\.]?\d{3}[\s\-\.]?\d{3}\b", re.IGNORECASE)
URL_PATTERN = re.compile(r"\b((?:https?://|www\.)[a-zA-Z0-9\-\.]+\.[a-z]{2,}(?:/[^\s\),. ]*)?)\b", re.IGNORECASE)
CAPITAL_PATTERN = re.compile(r"Capital:\s*([\d\.,]+\s*Euros)", re.IGNORECASE)
ADDRESS_PATTERN = re.compile(r"Domicilio:\s*(.*?)\.\s", re.IGNORECASE | re.DOTALL)
WORKERS_PATTERN = re.compile(r"\b(\d+)\s*(?:trabajadores|empleados|miembros de plantilla)\b", re.IGNORECASE)
ACT_SPLIT_PATTERN = re.compile(r"\n(\d+)\s+-\s+")
ISO_DATE_PATTERN = re.compile(r"^\d{4}-\d{2}-\d{2}$")
RAW_DATE_PATTERN = re.compile(r"^\d{8}$")
PDF_IDENT_PROVINCE_PATTERN = re.compile(r"^BORME-[A-Z]-\d{4}-\d{1,3}-(\d{2})$", re.IGNORECASE)

NORMALIZE_TRANSLATION_TABLE = str.maketrans(
    {
        "á": "a",
        "à": "a",
        "ä": "a",
        "â": "a",
        "Á": "A",
        "À": "A",
        "Ä": "A",
        "Â": "A",
        "é": "e",
        "è": "e",
        "ë": "e",
        "ê": "e",
        "É": "E",
        "È": "E",
        "Ë": "E",
        "Ê": "E",
        "í": "i",
        "ì": "i",
        "ï": "i",
        "î": "i",
        "Í": "I",
        "Ì": "I",
        "Ï": "I",
        "Î": "I",
        "ó": "o",
        "ò": "o",
        "ö": "o",
        "ô": "o",
        "Ó": "O",
        "Ò": "O",
        "Ö": "O",
        "Ô": "O",
        "ú": "u",
        "ù": "u",
        "ü": "u",
        "û": "u",
        "Ú": "U",
        "Ù": "U",
        "Ü": "U",
        "Û": "U",
        "ñ": "n",
        "Ñ": "N",
    }
)

NORMALIZED_TYPE_PATTERNS: List[Tuple[str, re.Pattern[str]]] = [
    ("CONSTITUCION", re.compile(r"\bCONSTITUCION\b")),
    ("DISOLUCION", re.compile(r"\bDISOLUCION\b|\bEXTINCION\b|\bLIQUIDACION\b")),
    ("CESE", re.compile(r"\bCESE\b")),
    ("NOMBRAMIENTO", re.compile(r"\bNOMBRAMIENTO\b")),
    ("REVOCACION", re.compile(r"\bREVOCACION\b")),
    ("MODIFICACION", re.compile(r"\bMODIFICACION\b")),
    ("AMPLIACION_CAPITAL", re.compile(r"\bAMPLIACION DE CAPITAL\b")),
    ("REDUCCION_CAPITAL", re.compile(r"\bREDUCCION DE CAPITAL\b")),
    ("TRANSFORMACION", re.compile(r"\bTRANSFORMACION\b")),
    ("FUSION", re.compile(r"\bFUSION\b")),
    ("ESCISION", re.compile(r"\bESCISION\b")),
    ("CAMBIO_DOMICILIO", re.compile(r"\bCAMBIO DE DOMICILIO\b")),
    ("CONCURSO", re.compile(r"\bCONCURSO\b")),
    ("REACTIVACION", re.compile(r"\bREACTIVACION\b")),
]

CREATION_PATTERNS: List[re.Pattern[str]] = [
    re.compile(r"\bCONSTITUCION\b"),
    re.compile(r"\bSOCIEDAD DE NUEVA CREACION\b"),
    re.compile(r"\bNUEVA SOCIEDAD\b"),
    re.compile(r"\bCONSTITUIR\b"),
    re.compile(r"\bINICIO DE ACTIVIDAD\b"),
]

DISSOLUTION_PATTERNS: List[re.Pattern[str]] = [
    re.compile(r"\bDISOLUCION\b"),
    re.compile(r"\bEXTINCION\b"),
    re.compile(r"\bLIQUIDACION\b"),
    re.compile(r"\bCESE\b"),
]


def clean_cif(value: str) -> str:
    if not value:
        return ""
    cleaned = re.sub(r"[\s\.\-]", "", value).upper().strip()
    return cleaned if len(cleaned) >= 9 else value.strip().upper()


def normalize_text(text: str) -> str:
    return re.sub(r"\s+", " ", text.translate(NORMALIZE_TRANSLATION_TABLE)).strip().upper()


def normalized_type(combined: str) -> str:
    for label, pattern in NORMALIZED_TYPE_PATTERNS:
        if pattern.search(combined):
            return label
    return "OTROS"


def classify_event(type_text: str, details_text: str) -> Dict[str, object]:
    combined = normalize_text(f"{type_text} {details_text}")
    is_creation = any(p.search(combined) for p in CREATION_PATTERNS)
    is_dissolution = any(p.search(combined) for p in DISSOLUTION_PATTERNS)

    if is_creation and not is_dissolution:
        group = "CREATION"
    elif is_dissolution and not is_creation:
        group = "DISSOLUTION"
    elif is_creation and is_dissolution:
        group = "MIXED"
    else:
        group = "OTHER"

    return {
        "normalized_type": normalized_type(combined),
        "event_group": group,
        "is_creation": 1 if is_creation else 0,
        "is_dissolution": 1 if is_dissolution else 0,
    }


@dataclass
class ActRecord:
    id: str
    legacy_id: str
    date: str
    section: str
    type: str
    province: str
    company_name: str
    company_uid: Optional[str]
    raw_text: str
    capital: Optional[str]
    hash_md5: str
    normalized_type: str
    event_group: str
    is_creation: int
    is_dissolution: int
    company_name_norm: str


class OpenBormeBuilder:
    def __init__(
        self,
        db_path: Path,
        checkpoint: Path,
        sleep_ms: int = 0,
        skip_pdf: bool = False,
        skip_xml: bool = False,
        reset: bool = False,
    ):
        self.db_path = db_path
        self.checkpoint = checkpoint
        self.sleep_ms = max(0, sleep_ms)
        self.skip_pdf = skip_pdf
        self.skip_xml = skip_xml
        self.reset = reset

        self.session = requests.Session()
        self.session.headers.update({"User-Agent": "OpenBorme-PythonBuilder/2.0"})
        self.conn = self._init_db()
        self.tmp_dir = Path(tempfile.gettempdir()) / "openborme_python_builder"
        self.tmp_dir.mkdir(parents=True, exist_ok=True)

    def close(self) -> None:
        self.conn.close()
        self.session.close()

    def _init_db(self) -> sqlite3.Connection:
        self.db_path.parent.mkdir(parents=True, exist_ok=True)
        conn = sqlite3.connect(str(self.db_path))
        conn.row_factory = sqlite3.Row
        conn.execute("PRAGMA journal_mode=WAL")
        conn.execute("PRAGMA synchronous=NORMAL")
        conn.execute("PRAGMA temp_store=MEMORY")
        conn.execute("PRAGMA cache_size=-200000")
        if self.reset:
            conn.executescript(
                """
                DROP TABLE IF EXISTS borme_acts;
                DROP TABLE IF EXISTS company;
                DROP TABLE IF EXISTS ingest_log;
                """
            )
            conn.commit()
        conn.executescript(
            """
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
            CREATE INDEX IF NOT EXISTS idx_legacy_date ON borme_acts(legacy_id, date);
            CREATE INDEX IF NOT EXISTS idx_event_group ON borme_acts(event_group);
            CREATE INDEX IF NOT EXISTS idx_is_creation ON borme_acts(is_creation);
            CREATE INDEX IF NOT EXISTS idx_is_dissolution ON borme_acts(is_dissolution);
            CREATE INDEX IF NOT EXISTS idx_company_name_norm ON borme_acts(company_name_norm);
            CREATE INDEX IF NOT EXISTS idx_date_province ON borme_acts(date, province);
            """
        )
        existing = {row["name"] for row in conn.execute("PRAGMA table_info(borme_acts)").fetchall()}
        if "legacy_id" not in existing:
            conn.execute("ALTER TABLE borme_acts ADD COLUMN legacy_id TEXT")
        if "normalized_type" not in existing:
            conn.execute("ALTER TABLE borme_acts ADD COLUMN normalized_type TEXT DEFAULT 'OTROS'")
        if "event_group" not in existing:
            conn.execute("ALTER TABLE borme_acts ADD COLUMN event_group TEXT DEFAULT 'OTHER'")
        if "is_creation" not in existing:
            conn.execute("ALTER TABLE borme_acts ADD COLUMN is_creation INTEGER DEFAULT 0")
        if "is_dissolution" not in existing:
            conn.execute("ALTER TABLE borme_acts ADD COLUMN is_dissolution INTEGER DEFAULT 0")
        if "company_name_norm" not in existing:
            conn.execute("ALTER TABLE borme_acts ADD COLUMN company_name_norm TEXT")
        conn.execute("CREATE INDEX IF NOT EXISTS idx_legacy_date ON borme_acts(legacy_id, date)")
        conn.execute("CREATE INDEX IF NOT EXISTS idx_event_group ON borme_acts(event_group)")
        conn.execute("CREATE INDEX IF NOT EXISTS idx_is_creation ON borme_acts(is_creation)")
        conn.execute("CREATE INDEX IF NOT EXISTS idx_is_dissolution ON borme_acts(is_dissolution)")
        conn.execute("CREATE INDEX IF NOT EXISTS idx_company_name_norm ON borme_acts(company_name_norm)")
        conn.execute("UPDATE borme_acts SET legacy_id = id WHERE legacy_id IS NULL OR legacy_id = ''")
        conn.commit()
        return conn

    def _get_summary_xml(self, ymd: str) -> Optional[ET.Element]:
        url = SUMMARY_URL.format(date=ymd)
        try:
            resp = self.session.get(url, headers={"Accept": "application/xml"}, timeout=35)
            if resp.status_code == 404:
                return None
            resp.raise_for_status()
            return ET.fromstring(resp.text)
        except Exception:
            return None

    def _download_to_temp(self, url: str, suffix: str) -> Optional[Path]:
        try:
            resp = self.session.get(url, stream=True, timeout=35)
            resp.raise_for_status()
        except Exception:
            return None

        fd, path = tempfile.mkstemp(prefix="openborme_", suffix=suffix, dir=str(self.tmp_dir))
        os.close(fd)
        p = Path(path)
        try:
            with p.open("wb") as f:
                for chunk in resp.iter_content(chunk_size=1024 * 64):
                    if chunk:
                        f.write(chunk)
            return p
        except Exception:
            try:
                p.unlink(missing_ok=True)
            except Exception:
                pass
            return None

    def _parse_pdf_acts(self, pdf_path: Path, province_name: str, date_ymd: str) -> List[ActRecord]:
        try:
            reader = pypdf.PdfReader(str(pdf_path))
        except Exception:
            return []

        full_text_parts: List[str] = []
        for page in reader.pages:
            try:
                full_text_parts.append(page.extract_text() or "")
            except Exception:
                continue
        full_text = "\n".join(full_text_parts)
        if not full_text.strip():
            return []

        acts: List[ActRecord] = []
        parts = ACT_SPLIT_PATTERN.split(full_text)
        if len(parts) < 3:
            return acts

        for i in range(1, len(parts), 2):
            act_num = (parts[i] or "").strip()
            content = parts[i + 1] if i + 1 < len(parts) else ""
            content = content.strip()
            if not act_num or not content:
                continue

            lines = [ln.strip() for ln in content.splitlines() if ln.strip()]
            company_name = lines[0] if lines else "UNKNOWN"

            cif_match = CIF_PATTERN.search(content)
            url_match = URL_PATTERN.search(content)
            capital_match = CAPITAL_PATTERN.search(content)
            details = content[:500] + ("..." if len(content) > 500 else "")

            act_type = content.split(".", 1)[0].strip() if "." in content else "Other"
            event = classify_event(act_type, details)
            legacy_id = f"{province_name}-{act_num}"
            act_id = f"{date_ymd}-{legacy_id}"
            hash_md5 = hashlib.md5(f"{date_ymd}{act_id}{details}".encode("utf-8")).hexdigest()

            acts.append(
                ActRecord(
                    id=act_id,
                    legacy_id=legacy_id,
                    date=date_ymd,
                    section="A",
                    type=act_type,
                    province=province_name,
                    company_name=company_name,
                    company_uid=clean_cif(cif_match.group(0)) if cif_match else None,
                    raw_text=details,
                    capital=(capital_match.group(1).strip() if capital_match else None),
                    hash_md5=hash_md5,
                    normalized_type=event["normalized_type"],
                    event_group=event["event_group"],
                    is_creation=event["is_creation"],
                    is_dissolution=event["is_dissolution"],
                    company_name_norm=normalize_text(company_name),
                )
            )

        return acts

    def _parse_xml_act(self, xml_text: str, date_ymd: str) -> Optional[ActRecord]:
        try:
            root = ET.fromstring(xml_text)
        except Exception:
            return None

        metadata = root.find("metadatos")
        if metadata is None:
            return None

        identificador = (metadata.findtext("identificador") or "").strip()
        company_name = (metadata.findtext("titulo") or "").strip() or "UNKNOWN"
        act_type = (metadata.findtext("departamento") or "").strip() or "UNKNOWN"
        date_raw = (metadata.findtext("fecha_publicacion") or "").strip()
        if not RAW_DATE_PATTERN.match(date_raw):
            date_raw = date_ymd

        description_parts: List[str] = []
        texto = root.find("texto")
        if texto is not None:
            for p in texto.findall("p"):
                val = (p.text or "").strip()
                if val:
                    description_parts.append(val)
        description = "\n".join(description_parts)
        details = description[:500] + ("..." if len(description) > 500 else "")

        cif_match = CIF_PATTERN.search(description) or CIF_PATTERN.search(company_name)
        capital_match = CAPITAL_PATTERN.search(description)
        event = classify_event(act_type, details)

        act_id = identificador or hashlib.md5((company_name + date_raw + act_type).encode("utf-8")).hexdigest()[:24]
        hash_md5 = hashlib.md5(f"{date_raw}{act_id}{details}".encode("utf-8")).hexdigest()

        return ActRecord(
            id=act_id,
            legacy_id=act_id,
            date=date_raw,
            section="C",
            type=act_type,
            province="NATIONAL",
            company_name=company_name,
            company_uid=clean_cif(cif_match.group(0)) if cif_match else None,
            raw_text=details,
            capital=(capital_match.group(1).strip() if capital_match else None),
            hash_md5=hash_md5,
            normalized_type=event["normalized_type"],
            event_group=event["event_group"],
            is_creation=event["is_creation"],
            is_dissolution=event["is_dissolution"],
            company_name_norm=normalize_text(company_name),
        )

    def _iter_summary_docs(self, root: ET.Element) -> Tuple[List[Tuple[str, str]], List[str]]:
        diarios = root.findall(".//diario")
        if not diarios:
            return [], []

        pdf_docs: List[Tuple[str, str]] = []
        xml_ids: List[str] = []

        for diario in diarios:
            for seccion in diario.findall("seccion"):
                codigo = (seccion.attrib.get("codigo") or "").strip().upper()
                if codigo == "A":
                    for item in seccion.findall("item"):
                        ident = (item.findtext("identificador") or "").strip()
                        url_pdf = (item.findtext("url_pdf") or "").strip()
                        if ident and url_pdf.startswith("http"):
                            pdf_docs.append((ident, url_pdf))
                elif codigo == "C":
                    for apartado in seccion.findall("apartado"):
                        for item in apartado.findall("item"):
                            ident = (item.findtext("identificador") or "").strip()
                            if ident:
                                xml_ids.append(ident)
        return pdf_docs, xml_ids

    def _upsert_day(self, ymd: str, records: List[ActRecord]) -> None:
        cur = self.conn.cursor()
        cur.execute(
            """
            INSERT INTO ingest_log(date, status, acts_count, last_updated)
            VALUES (?, 'processing', ?, CURRENT_TIMESTAMP)
            ON CONFLICT(date) DO UPDATE SET
                status = 'processing',
                acts_count = excluded.acts_count,
                last_updated = CURRENT_TIMESTAMP
            """,
            (ymd, len(records)),
        )

        for rec in records:
            cur.execute(
                """
                INSERT INTO borme_acts (
                    id, legacy_id, date, section, type, province, company_name, company_uid, raw_text, capital, hash_md5,
                    normalized_type, event_group, is_creation, is_dissolution, company_name_norm, created_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP
                )
                ON CONFLICT(id) DO UPDATE SET
                    legacy_id = excluded.legacy_id,
                    date = excluded.date,
                    section = excluded.section,
                    type = excluded.type,
                    province = excluded.province,
                    company_name = excluded.company_name,
                    company_uid = excluded.company_uid,
                    raw_text = excluded.raw_text,
                    capital = excluded.capital,
                    hash_md5 = excluded.hash_md5,
                    normalized_type = excluded.normalized_type,
                    event_group = excluded.event_group,
                    is_creation = excluded.is_creation,
                    is_dissolution = excluded.is_dissolution,
                    company_name_norm = excluded.company_name_norm
                """,
                (
                    rec.id,
                    rec.legacy_id,
                    rec.date,
                    rec.section,
                    rec.type,
                    rec.province,
                    rec.company_name,
                    rec.company_uid,
                    rec.raw_text,
                    rec.capital,
                    rec.hash_md5,
                    rec.normalized_type,
                    rec.event_group,
                    rec.is_creation,
                    rec.is_dissolution,
                    rec.company_name_norm,
                ),
            )

            if rec.company_uid:
                cur.execute(
                    """
                    INSERT INTO company (cif, name, province)
                    VALUES (?, ?, ?)
                    ON CONFLICT(cif) DO UPDATE SET
                        name = excluded.name,
                        province = excluded.province
                    """,
                    (rec.company_uid, rec.company_name, rec.province),
                )

        cur.execute(
            """
            INSERT INTO ingest_log(date, status, acts_count, last_updated)
            VALUES (?, 'done', ?, CURRENT_TIMESTAMP)
            ON CONFLICT(date) DO UPDATE SET
                status = 'done',
                acts_count = excluded.acts_count,
                last_updated = CURRENT_TIMESTAMP
            """,
            (ymd, len(records)),
        )
        self.conn.commit()

    def _mark_error(self, ymd: str, message: str) -> None:
        cur = self.conn.cursor()
        cur.execute(
            """
            INSERT INTO ingest_log(date, status, acts_count, last_updated)
            VALUES (?, 'error', 0, CURRENT_TIMESTAMP)
            ON CONFLICT(date) DO UPDATE SET
                status = 'error',
                last_updated = CURRENT_TIMESTAMP
            """,
            (ymd,),
        )
        self.conn.commit()
        print(f"[{ymd}] error: {message}")

    def process_date(self, date_obj: dt.date) -> Dict[str, object]:
        ymd = date_obj.strftime("%Y%m%d")
        summary_root = self._get_summary_xml(ymd)
        if summary_root is None:
            self._upsert_day(ymd, [])
            return {"date": ymd, "acts": 0, "status": "empty"}

        pdf_docs, xml_ids = self._iter_summary_docs(summary_root)
        all_records: List[ActRecord] = []

        if not self.skip_pdf:
            for ident, url_pdf in pdf_docs:
                province_code_match = PDF_IDENT_PROVINCE_PATTERN.search(ident)
                province_code = province_code_match.group(1) if province_code_match else ""
                province = PROVINCE_MAP.get(province_code, "UNKNOWN")

                tmp_pdf = self._download_to_temp(url_pdf, ".pdf")
                if not tmp_pdf:
                    continue
                try:
                    all_records.extend(self._parse_pdf_acts(tmp_pdf, province, ymd))
                finally:
                    try:
                        tmp_pdf.unlink(missing_ok=True)
                    except Exception:
                        pass

        if not self.skip_xml:
            for act_id in xml_ids:
                url_xml = ACTA_URL.format(id=act_id)
                try:
                    resp = self.session.get(url_xml, headers={"Accept": "application/xml"}, timeout=35)
                    resp.raise_for_status()
                    rec = self._parse_xml_act(resp.text, ymd)
                    if rec:
                        all_records.append(rec)
                except Exception:
                    continue

        self._upsert_day(ymd, all_records)
        return {"date": ymd, "acts": len(all_records), "status": "ok"}

    def is_done(self, ymd: str) -> bool:
        row = self.conn.execute("SELECT status FROM ingest_log WHERE date = ?", (ymd,)).fetchone()
        return bool(row and row["status"] == "done")

    def total_acts(self) -> int:
        row = self.conn.execute("SELECT COUNT(*) AS c FROM borme_acts").fetchone()
        return int(row["c"])

    def update_checkpoint(self, iso_date: str) -> None:
        self.checkpoint.parent.mkdir(parents=True, exist_ok=True)
        self.checkpoint.write_text(iso_date + "\n", encoding="utf-8")

    def read_checkpoint(self) -> Optional[str]:
        if not self.checkpoint.exists():
            return None
        value = self.checkpoint.read_text(encoding="utf-8").strip()
        if ISO_DATE_PATTERN.match(value):
            return value
        return None


def iter_dates(start: dt.date, end: dt.date) -> Iterable[dt.date]:
    cur = start
    while cur <= end:
        yield cur
        cur += dt.timedelta(days=1)


def parse_args() -> argparse.Namespace:
    repo_root = Path(__file__).resolve().parents[3]
    default_db = repo_root / "pipeline" / "data" / "openborme.sqlite"
    default_checkpoint = repo_root / "pipeline" / "data" / "python_build_checkpoint.txt"

    parser = argparse.ArgumentParser()
    parser.add_argument("--start", default="2020-01-01")
    parser.add_argument("--end", default=dt.date.today().strftime("%Y-%m-%d"))
    parser.add_argument("--db", default=str(default_db))
    parser.add_argument("--checkpoint", default=str(default_checkpoint))
    parser.add_argument("--resume", action="store_true")
    parser.add_argument("--sleep-ms", type=int, default=0)
    parser.add_argument("--skip-pdf", action="store_true")
    parser.add_argument("--skip-xml", action="store_true")
    parser.add_argument("--reset", action="store_true")
    return parser.parse_args()


def ensure_date(value: str) -> dt.date:
    try:
        return dt.datetime.strptime(value, "%Y-%m-%d").date()
    except ValueError as exc:
        raise SystemExit(f"Fecha inválida: {value}. Usa YYYY-MM-DD") from exc


def main() -> int:
    args = parse_args()
    start = ensure_date(args.start)
    end = ensure_date(args.end)
    if start > end:
        raise SystemExit("Rango inválido: start > end")

    builder = OpenBormeBuilder(
        db_path=Path(args.db).resolve(),
        checkpoint=Path(args.checkpoint).resolve(),
        sleep_ms=args.sleep_ms,
        skip_pdf=args.skip_pdf,
        skip_xml=args.skip_xml,
        reset=args.reset,
    )

    try:
        if args.reset and builder.checkpoint.exists():
            builder.checkpoint.unlink(missing_ok=True)
            args.resume = False

        if args.resume:
            ck = builder.read_checkpoint()
            if ck:
                ck_date = ensure_date(ck)
                next_day = ck_date + dt.timedelta(days=1)
                if next_day <= end and next_day > start:
                    start = next_day

        total_days = (end - start).days + 1
        print("=== OpenBorme Python DB Builder ===")
        print(f"DB: {builder.db_path}")
        print(f"Rango: {start} -> {end} ({total_days} días)")
        print(f"Checkpoint: {builder.checkpoint}")
        print(f"Flags: resume={args.resume} skip_pdf={args.skip_pdf} skip_xml={args.skip_xml}")

        processed = 0
        inserted_day_total = 0
        started = time.time()

        for day in iter_dates(start, end):
            iso = day.strftime("%Y-%m-%d")
            ymd = day.strftime("%Y%m%d")

            if args.resume and builder.is_done(ymd):
                processed += 1
                builder.update_checkpoint(iso)
                if processed % 50 == 0:
                    print(f"[skip] {processed}/{total_days} días ya procesados")
                continue

            try:
                result = builder.process_date(day)
                inserted_day_total += int(result.get("acts", 0))
                print(f"[{iso}] {result.get('status')} acts={result.get('acts')}")
            except Exception as exc:
                builder._mark_error(ymd, str(exc))
                print(f"[{iso}] fallo no controlado: {exc}")

            builder.update_checkpoint(iso)
            processed += 1

            if processed % 25 == 0:
                elapsed = round(time.time() - started, 1)
                print(
                    json.dumps(
                        {
                            "progress_days": f"{processed}/{total_days}",
                            "inserted_in_run": inserted_day_total,
                            "total_acts_db": builder.total_acts(),
                            "elapsed_s": elapsed,
                        },
                        ensure_ascii=False,
                    )
                )

            if builder.sleep_ms > 0:
                time.sleep(builder.sleep_ms / 1000.0)

        elapsed = round(time.time() - started, 1)
        print("=== Finalizado ===")
        print(f"Insertados/actualizados en ejecución: {inserted_day_total}")
        print(f"Total borme_acts: {builder.total_acts()}")
        print(f"Tiempo: {elapsed}s")
        print(f"SQLite listo: {builder.db_path}")
        return 0
    finally:
        builder.close()


if __name__ == "__main__":
    raise SystemExit(main())
