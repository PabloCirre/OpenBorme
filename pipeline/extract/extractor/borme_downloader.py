import os
import requests
import datetime
import time
from typing import Optional

BASE_API_URL = "https://www.boe.es/datosabiertos/api/borme/sumario/"
BASE_DATA_DIR = "data"

class BormeDownloader:
    def __init__(self, storage_dir: str = "data"):
        self.storage_dir = storage_dir
        os.makedirs(self.storage_dir, exist_ok=True)
        self.session = requests.Session()
        self.session.headers.update({"User-Agent": "OpenBorme-Extractor/1.0"})

    def download_file(self, url: str, target_path: str) -> bool:
        if os.path.exists(target_path):
            return True
        
        try:
            response = self.session.get(url, stream=True, timeout=30)
            response.raise_for_status()
            os.makedirs(os.path.dirname(target_path), exist_ok=True)
            with open(target_path, "wb") as f:
                for chunk in response.iter_content(chunk_size=8192):
                    f.write(chunk)
            return True
        except Exception as e:
            print(f"  [!] Error descargando {url}: {e}")
            return False

    def download_file_to_path(self, url: str, target_path: str) -> bool:
        """Downloads a file to a specific path, overriding the default storage structure."""
        if os.path.exists(target_path):
            return True
        try:
            response = self.session.get(url, stream=True, timeout=30)
            response.raise_for_status()
            os.makedirs(os.path.dirname(target_path), exist_ok=True)
            with open(target_path, "wb") as f:
                for chunk in response.iter_content(chunk_size=8192):
                    f.write(chunk)
            return True
        except Exception as e:
            print(f"  [!] Error descargando {url}: {e}")
            return False

    def get_summary(self, date_str: str) -> Optional[str]:
        url = f"{BASE_API_URL}{date_str}"
        try:
            response = self.session.get(url, headers={"Accept": "application/xml"}, timeout=30)
            if response.status_code == 404:
                return None
            response.raise_for_status()
            return response.text
        except Exception as e:
            print(f"  [!] Error obteniendo sumario para {date_str}: {e}")
            return None

    def process_date(self, date_obj: datetime.date):
        date_str = date_obj.strftime("%Y%m%d")
        print(f"[*] Procesando BORME del {date_str}...")
        
        summary_xml = self.get_summary(date_str)
        if not summary_xml:
            print(f"    [-] No hay boletín disponible.")
            return

        # Section A (PDFs)
        if '<seccion codigo="A"' in summary_xml:
            try:
                section_a = summary_xml.split('<seccion codigo="A"')[1].split('</seccion>')[0]
                pdf_urls = [part.split('</url_pdf>')[0] for part in section_a.split('<url_pdf') if '</url_pdf>' in part]
                for part in pdf_urls:
                    url = part.split('>')[-1]
                    if url.startswith("http"):
                        filename = url.split("/")[-1]
                        target = os.path.join(self.storage_dir, date_str, "section_A", filename)
                        self.download_file(url, target)
            except Exception as e:
                print(f"    [!] Error en Sección A: {e}")

        # Section C (XMLs)
        if '<seccion codigo="C"' in summary_xml:
            try:
                section_c = summary_xml.split('<seccion codigo="C"')[1].split('</seccion>')[0]
                xml_urls = [part.split('</url_xml>')[0] for part in section_c.split('<url_xml') if '</url_xml>' in part]
                for part in xml_urls:
                    url = part.split('>')[-1]
                    if url.startswith("http"):
                        filename = url.split("=")[-1] + ".xml"
                        target = os.path.join(self.storage_dir, date_str, "section_C", filename)
                        self.download_file(url, target)
            except Exception as e:
                print(f"    [!] Error en Sección C: {e}")

    def download_range(self, start_date: datetime.date, end_date: datetime.date):
        current = start_date
        while current <= end_date:
            self.process_date(current)
            current += datetime.timedelta(days=1)
            time.sleep(0.1)

if __name__ == "__main__":
    import sys
    
    downloader = BormeDownloader(storage_dir=BASE_DATA_DIR)
    
    if len(sys.argv) >= 3:
        # Range: python borme_downloader.py 2023-01-01 2023-01-31
        start_str = sys.argv[1]
        end_str = sys.argv[2]
        start_date = datetime.datetime.strptime(start_str, "%Y-%m-%d").date()
        end_date = datetime.datetime.strptime(end_str, "%Y-%m-%d").date()
        print(f"[*] Descargando BORME desde {start_date} hasta {end_date}...")
        downloader.download_range(start_date, end_date)
    else:
        # Days from today: python borme_downloader.py 30
        days = int(sys.argv[1]) if len(sys.argv) > 1 else 7
        end_date = datetime.date.today()
        start_date = end_date - datetime.timedelta(days=days)
        print(f"[*] Descargando BORME de los últimos {days} días (desde {start_date})...")
        downloader.download_range(start_date, end_date)
