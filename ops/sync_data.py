import os
import sys
from ftplib import FTP
from pathlib import Path


def env_required(name: str) -> str:
    value = os.getenv(name, "").strip()
    if not value:
        raise RuntimeError(f"Falta variable de entorno requerida: {name}")
    return value


def ensure_remote_dirs(ftp: FTP, remote_dir: str) -> None:
    parts = [p for p in remote_dir.replace("\\", "/").split("/") if p]
    current = ""
    for part in parts:
        current = f"{current}/{part}" if current else part
        try:
            ftp.mkd(current)
        except Exception:
            # Normalmente ya existe.
            pass


def sync_data() -> None:
    host = env_required("OPENBORME_FTP_HOST")
    user = env_required("OPENBORME_FTP_USER")
    password = env_required("OPENBORME_FTP_PASS")

    # SQLite-only: por defecto subimos la base SQLite principal.
    files_raw = os.getenv("OPENBORME_SYNC_FILES", "pipeline/data/openborme.sqlite")
    files_to_sync = [f.strip() for f in files_raw.split(",") if f.strip()]
    remote_dir = os.getenv("OPENBORME_SYNC_REMOTE_DIR", "pipeline/data").strip("/ ")

    repo_root = Path(__file__).resolve().parent.parent
    print(f"[*] Conectando a {host} para sincronización de datos...")

    ftp = FTP(host, timeout=30)
    ftp.login(user, password)
    try:
        ensure_remote_dirs(ftp, remote_dir)
        for filename in files_to_sync:
            candidates = [
                Path.cwd() / filename,
                repo_root / filename,
            ]

            local_file = next((p for p in candidates if p.exists() and p.is_file()), None)
            if not local_file:
                print(f"[-] Archivo no encontrado: {filename}")
                continue

            remote_name = local_file.name
            remote_path = f"{remote_dir}/{remote_name}" if remote_dir else remote_name
            print(f"[*] Subiendo {filename} desde {local_file} a {remote_path} ...")
            with local_file.open("rb") as f:
                ftp.storbinary(f"STOR {remote_path}", f)
    finally:
        ftp.quit()

    print("[+] Sincronización completada con éxito.")


if __name__ == "__main__":
    try:
        sync_data()
    except Exception as e:
        print(f"[!] Error en la sincronización: {e}", file=sys.stderr)
        sys.exit(1)
