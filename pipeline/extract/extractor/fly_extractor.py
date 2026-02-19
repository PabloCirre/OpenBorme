import os
import tempfile
import shutil
import pandas as pd
from datetime import date
from typing import List, Dict
from borme_downloader import BormeDownloader
from parser_pdf import parse_section_1_pdf
from parser_xml import parse_section_2_xml

# Map of province codes to names
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

class FlyExtractor:
    def __init__(self, output_csv: str = "borme_data.csv"):
        self.output_csv = output_csv
        # Using a temporary directory that cleans up after itself is safer, 
        # but for simplicity we'll use a specific temp folder we control
        self.temp_dir = os.path.join(tempfile.gettempdir(), "openborme_fly")
        if os.path.exists(self.temp_dir):
            shutil.rmtree(self.temp_dir)
        os.makedirs(self.temp_dir, exist_ok=True)
        
        self.downloader = BormeDownloader(storage_dir=self.temp_dir)

    def process_date(self, target_date: date):
        date_str = target_date.strftime("%Y%m%d")
        print(f"[*] [FlyMode] Processing {date_str}...")

        # 1. Get Summary
        summary_xml = self.downloader.get_summary(date_str)
        if not summary_xml:
            print(f"    [-] No bulletin found for {date_str}")
            return

        daily_acts = []
        
        # 2. Identify PDFs (Section A - Provincial)
        if '<seccion codigo="A"' in summary_xml:
            try:
                section_a = summary_xml.split('<seccion codigo="A"')[1].split('</seccion>')[0]
                pdf_parts = [part for part in section_a.split('<url_pdf') if '</url_pdf>' in part]
                
                print(f"    [+] Found {len(pdf_parts)} PDFs to process.")
                
                for part in pdf_parts:
                    url = part.split('>')[-1].split('<')[0]
                    if not url.startswith("http"): continue
                    
                    filename = url.split("/")[-1]
                    temp_pdf_path = os.path.join(self.temp_dir, filename)
                    
                    # A. Download to Temp
                    # print(f"      -> Downloading {filename}...")
                    if self.downloader.download_file_to_path(url, temp_pdf_path):
                        
                        # B. Extract Text
                        province_id = filename.split("-")[-1].replace(".pdf", "")
                        province_name = PROVINCE_MAP.get(province_id, f"Province {province_id}")
                        
                        try:
                            acts = parse_section_1_pdf(temp_pdf_path, province_name)
                            for act in acts:
                                act['Date'] = date_str
                                daily_acts.append(act)
                        except Exception as e:
                            print(f"      [!] Extraction error in {filename}: {e}")
                        
                        # C. DELETE FILE IMMEDIATELY
                        os.remove(temp_pdf_path)
                        # print(f"      -> Deleted {filename}")
                        
            except Exception as e:
                print(f"    [!] Error parsing Section A: {e}")

        # 3. Identify XMLs (Section C - Mercantil) - Optional as per user focus on PDFs, but good to have
        # (Skipping XML for now to focus on the PDF transition request, but easy to add)

        # 4. Append to CSV
        if daily_acts:
            print(f"    [+] Extracted {len(daily_acts)} acts. Appending to database...")
            df = pd.DataFrame(daily_acts)
            
            # Append mode with header only if file doesn't exist
            header = not os.path.exists(self.output_csv)
            df.to_csv(self.output_csv, mode='a', header=header, index=False, encoding='utf-8-sig')
        else:
            print("    [-] No acts extracted for this date.")

    def run_range(self, start_date: date, end_date: date):
        import datetime
        current = start_date
        while current <= end_date:
            self.process_date(current)
            current += datetime.timedelta(days=1)
            
        # Cleanup final temp dir
        if os.path.exists(self.temp_dir):
            shutil.rmtree(self.temp_dir)
            print("[*] Temp directory cleaned.")

if __name__ == "__main__":
    import sys
    import datetime
    
    extractor = FlyExtractor(output_csv="../../borme_data.csv")
    
    if len(sys.argv) >= 3:
        start = datetime.datetime.strptime(sys.argv[1], "%Y-%m-%d").date()
        end = datetime.datetime.strptime(sys.argv[2], "%Y-%m-%d").date()
        extractor.run_range(start, end)
    else:
        print("Usage: python fly_extractor.py YYYY-MM-DD YYYY-MM-DD")
