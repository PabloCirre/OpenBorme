import ftplib
import os
import sys
from pathlib import Path


def env_required(name: str) -> str:
    value = os.getenv(name, "").strip()
    if not value:
        raise RuntimeError(f"Falta variable de entorno requerida: {name}")
    return value


def main() -> int:
    host = env_required("OPENBORME_FTP_HOST")
    user = env_required("OPENBORME_FTP_USER")
    password = env_required("OPENBORME_FTP_PASS")

    local_file = Path(os.getenv("OPENBORME_FTP_DEBUG_FILE", "test.php")).resolve()
    remote_name = os.getenv("OPENBORME_FTP_DEBUG_REMOTE", local_file.name)

    if not local_file.exists() or not local_file.is_file():
        raise RuntimeError(f"No existe fichero local para debug deploy: {local_file}")

    ftp = ftplib.FTP(host, timeout=30)
    ftp.login(user, password)
    print("Connected to FTP")

    with local_file.open("rb") as f:
        ftp.storbinary(f"STOR {remote_name}", f)
    print(f"Uploaded: {local_file} -> {remote_name}")

    ftp.quit()
    return 0


if __name__ == "__main__":
    try:
        raise SystemExit(main())
    except Exception as e:
        print(f"Error: {e}", file=sys.stderr)
        raise SystemExit(1)
