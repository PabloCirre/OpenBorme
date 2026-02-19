import os
import datetime
import pandas as pd
from typing import List, Dict
from borme_downloader import BormeDownloader
from parser_pdf import parse_section_1_pdf
from parser_xml import parse_section_2_xml

PROVINCE_MAP = {
    '01': 'Araba/Álava', '02': 'Albacete', '03': 'Alicante', '04': 'Almería', '05': 'Ávila',
    '06': 'Badajoz', '07': 'Balears (Illes)', '08': 'Barcelona', '09': 'Burgos', '10': 'Cáceres',
    '11': 'Cádiz', '12': 'Castellón', '13': 'Ciudad Real', '14': 'Córdoba', '15': 'A Coruña',
    '16': 'Cuenca', '17': 'Girona', '18': 'Granada', '19': 'Guadalajara', '20': 'Gipuzkoa',
    '21': 'Huelva', '22': 'Huesca', '23': 'Jaén', '24': 'León', '25': 'Lleida', '26': 'La Rioja',
    '27': 'Lugo', '28': 'Madrid', '29': 'Málaga', '30': 'Murcia', '31': 'Navarra', '32': 'Ourense',
    '33': 'Asturias', '34': 'Palencia', '35': 'Las Palmas', '36': 'Pontevedra', '37': 'Salamanca',
    '38': 'Santa Cruz de Tenerife', '39': 'Cantabria', '40': 'Segovia', '41': 'Sevilla',
    '42': 'Soria', '43': 'Tarragona', '44': 'Teruel', '45': 'Toledo', '46': 'Valencia',
    '47': 'Valladolid', '48': 'Bizkaia', '49': 'Zamora', '50': 'Zaragoza', '51': 'Ceuta', '52': 'Melilla'
}

class BormeEngine:
    def __init__(self, data_dir: str = "data", output_csv: str = "borme_data.csv"):
        self.data_dir = data_dir
        self.output_csv = output_csv
        self.downloader = BormeDownloader(self.data_dir)

    def run_backfill(self, start_date_str: str, end_date_str: str):
        """Downloads and parses data for a range of dates."""
        start = datetime.datetime.strptime(start_date_str, "%Y-%m-%d").date()
        end = datetime.datetime.strptime(end_date_str, "%Y-%m-%d").date()
        
        print(f"=== OpenBorme Extraction Engine ===")
        print(f"Target Range: {start} to {end}")
        
        # 1. Download
        self.downloader.download_range(start, end)
        
        # 2. Extract
        self.extract_and_save()

    def extract_and_save(self):
        """Parses all files in the data directory and saves to CSV."""
        all_acts = []
        
        if not os.path.exists(self.data_dir):
            print("No data directory found.")
            return

        dates = sorted([d for d in os.listdir(self.data_dir) if os.path.isdir(os.path.join(self.data_dir, d))])
        print(f"[*] Extracting data from {len(dates)} dates...")

        for date_str in dates:
            date_path = os.path.join(self.data_dir, date_str)
            
            # Section A (PDFs)
            section_a_path = os.path.join(date_path, "section_A")
            if os.path.exists(section_a_path):
                for filename in os.listdir(section_a_path):
                    if filename.endswith(".pdf"):
                        # Extract province from filename BORME-A-YYYY-XX-PP.pdf
                        province_id = filename.split("-")[-1].replace(".pdf", "")
                        province_name = PROVINCE_MAP.get(province_id, f"Province {province_id}")
                        
                        pdf_path = os.path.join(section_a_path, filename)
                        acts = parse_section_1_pdf(pdf_path, province_name)
                        for act in acts:
                            act['Date'] = date_str
                            all_acts.append(act)

            # Section C (XMLs)
            section_c_path = os.path.join(date_path, "section_C")
            if os.path.exists(section_c_path):
                for filename in os.listdir(section_c_path):
                    if filename.endswith(".xml"):
                        xml_path = os.path.join(section_c_path, filename)
                        act = parse_section_2_xml(xml_path)
                        if act:
                            act['Date'] = date_str
                            all_acts.append(act)

        if not all_acts:
            print("[-] No acts extracted.")
            return

        df = pd.DataFrame(all_acts)
        print(f"[*] Saving {len(df)} records to {self.output_csv}...")
        df.to_csv(self.output_csv, index=False, encoding='utf-8-sig')
        print("[+] Process Finished Successfully.")

if __name__ == "__main__":
    import sys
    engine = BormeEngine(data_dir="../../data", output_csv="../../borme_data.csv")
    
    if len(sys.argv) >= 3:
        engine.run_backfill(sys.argv[1], sys.argv[2])
    else:
        print("Usage: python engine.py YYYY-MM-DD YYYY-MM-DD")
        print("Example: python engine.py 2020-01-01 2020-01-10")
