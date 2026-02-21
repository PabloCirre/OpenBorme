import os
import sys
import sqlite3
import hashlib
from datetime import datetime
from borme_downloader import BASE_DATA_DIR
from parser_xml import parse_section_2_xml
from parser_pdf import parse_section_1_pdf

PROVINCE_MAP = {
    '01': 'ALAVA', '02': 'ALBACETE', '03': 'ALICANTE', '04': 'ALMERIA', '05': 'AVILA',
    '06': 'BADAJOZ', '07': 'ILLES BALEARS', '08': 'BARCELONA', '09': 'BURGOS', '10': 'CACERES',
    '11': 'CADIZ', '12': 'CASTELLON', '13': 'CIUDAD REAL', '14': 'CORDOBA', '15': 'A CORUÑA',
    '16': 'CUENCA', '17': 'GIRONA', '18': 'GRANADA', '19': 'GUADALAJARA', '20': 'GIPUZKOA',
    '21': 'HUELVA', '22': 'HUESCA', '23': 'JAEN', '24': 'LEON', '25': 'LLEIDA', '26': 'LA RIOJA',
    '27': 'LUGO', '28': 'MADRID', '29': 'MALAGA', '30': 'MURCIA', '31': 'NAVARRA', '32': 'OURENSE',
    '33': 'ASTURIAS', '34': 'PALENCIA', '35': 'LAS PALMAS', '36': 'PONTEVEDRA', '37': 'SALAMANCA',
    '38': 'SANTA CRUZ DE TENERIFE', '39': 'CANTABRIA', '40': 'SEGOVIA', '41': 'SEVILLA',
    '42': 'SORIA', '43': 'TARRAGONA', '44': 'TERUEL', '45': 'TOLEDO', '46': 'VALENCIA',
    '47': 'VALLADOLID', '48': 'BIZKAIA', '49': 'ZAMORA', '50': 'ZARAGOZA', '51': 'CEUTA', '52': 'MELILLA'
}

DB_PATH = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..', 'data', 'openborme.sqlite'))

def init_db():
    os.makedirs(os.path.dirname(DB_PATH), exist_ok=True)
    conn = sqlite3.connect(DB_PATH)
    cursor = conn.cursor()
    
    cursor.execute('''
    CREATE TABLE IF NOT EXISTS company (
        cif TEXT PRIMARY KEY,
        name TEXT NOT NULL,
        province TEXT NOT NULL
    )
    ''')
    
    cursor.execute('''
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
    )
    ''')

    cursor.execute('CREATE INDEX IF NOT EXISTS idx_date ON borme_acts(date)')
    cursor.execute('CREATE INDEX IF NOT EXISTS idx_type ON borme_acts(type)')
    cursor.execute('CREATE INDEX IF NOT EXISTS idx_company ON borme_acts(company_name)')
    
    conn.commit()
    return conn

def main():
    sys.stdout.reconfigure(encoding='utf-8')
    print(f"🗄️ Inicializando Base de Datos Local SQLite en: {DB_PATH}")
    conn = init_db()
    cursor = conn.cursor()
    
    if not os.path.exists(BASE_DATA_DIR):
        print(f"❌ No se encontró el directorio de datos: {BASE_DATA_DIR}")
        return

    dates = os.listdir(BASE_DATA_DIR)
    print(f"📅 Encontrados {len(dates)} días para procesar.")

    total_inserted = 0
    date_count = 0

    try:
        for date_str in sorted(dates):
            date_path = os.path.join(BASE_DATA_DIR, date_str)
            if not os.path.isdir(date_path):
                continue
                
            print(f"⏳ [{date_count+1}/{len(dates)}] Procesando fecha: {date_str}...")
            
            # Use small transactions per day or group of days to avoid massive locks/journals
            if date_count % 10 == 0:
                conn.execute("BEGIN TRANSACTION")

            # Process Section A (PDFs)
            section_a_path = os.path.join(date_path, "section_A")
            if os.path.exists(section_a_path):
                for filename in os.listdir(section_a_path):
                    if filename.endswith(".pdf"):
                        # Extract Province ID
                        province_id = filename.split("-")[-1].replace(".pdf", "")
                        province_name = PROVINCE_MAP.get(province_id, f"UNKNOWN")
                        
                        pdf_path = os.path.join(section_a_path, filename)
                        acts = parse_section_1_pdf(pdf_path, province_name)
                        
                        for act in acts:
                            date_val = date_str
                            id_val = act.get('ID', '')
                            details = act.get('Details', '')
                            
                            # Hash MD5
                            raw_str = f"{date_val}{id_val}{details}"
                            hash_md5 = hashlib.md5(raw_str.encode('utf-8')).hexdigest()
                            
                            type_val = act.get('Act Type', 'UNKNOWN').strip()
                            sect_val = 'B' if type_val == 'RAW' else 'A'
                            cif_val = act.get('CIF', '')
                            comp_name = act.get('Company Name', 'UNKNOWN')
                            prov_name = act.get('Province', 'UNKNOWN')
                            
                            try:
                                cursor.execute('''
                                INSERT OR IGNORE INTO borme_acts 
                                (id, date, section, type, province, company_name, company_uid, raw_text, capital, hash_md5)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                                ''', (
                                    id_val or os.urandom(8).hex(),
                                    date_val,
                                    sect_val,
                                    type_val,
                                    prov_name,
                                    comp_name,
                                    cif_val,
                                    details,
                                    act.get('Capital', ''),
                                    hash_md5
                                ))
                                
                                if cursor.rowcount > 0:
                                    total_inserted += 1

                                if cif_val and comp_name:
                                    cursor.execute('''
                                    INSERT OR IGNORE INTO company (cif, name, province)
                                    VALUES (?, ?, ?)
                                    ''', (cif_val, comp_name, prov_name))
                            except sqlite3.Error as e:
                                print(f"Error insertando acto: {e}")

            date_count += 1
            if date_count % 10 == 0:
                conn.commit()
                print(f"✅ Chunk de 10 días guardado. Total insertados: {total_inserted}")

        conn.commit()
        print(f"✅ ¡Proceso completado! Se han insertado {total_inserted} actos en la Base de Datos SQLite.")
        print(f"🚀 Ya puedes subir el archivo '{DB_PATH}' a tu servidor FTP de Hostinger.")
    except Exception as e:
        try:
            conn.rollback()
        except:
            pass
        print(f"❌ Error catastrófico guardando la información: {e}")
    finally:
        conn.close()

if __name__ == "__main__":
    main()
