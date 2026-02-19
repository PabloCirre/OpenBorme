import os
from ftplib import FTP
import sys

# FTP Credentials
FTP_HOST = "ftp.servidor3000.lucusvirtual.es"
FTP_USER = "branvan3000@openborme.es"
FTP_PASS = "5000Razones2.0"

FILES_TO_SYNC = [
    "borme_data.csv"
]

def sync_data():
    print(f"[*] Conectando a {FTP_HOST} para sincronización de datos...")
    try:
        ftp = FTP(FTP_HOST)
        ftp.login(FTP_USER, FTP_PASS)
        
        for filename in FILES_TO_SYNC:
            if os.path.exists(filename):
                print(f"[*] Subiendo {filename}...")
                with open(filename, "rb") as f:
                    # In FTP, we can just put it in the root for now as requested
                    ftp.storbinary(f"STOR {filename}", f)
            else:
                # Try in parent dir if called from scripts/
                alt_path = os.path.join("..", filename)
                if os.path.exists(alt_path):
                    print(f"[*] Subiendo {filename} (desde parent)...")
                    with open(alt_path, "rb") as f:
                        ftp.storbinary(f"STOR {filename}", f)
                else:
                    print(f"[-] Archivo {filename} no encontrado localmente.")
        
        ftp.quit()
        print("[+] Sincronización completada con éxito.")
    except Exception as e:
        print(f"[!] Error en la sincronización: {e}")

if __name__ == "__main__":
    sync_data()
