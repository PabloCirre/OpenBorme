import ftplib
import os
import sys


def env_required(name: str) -> str:
    value = os.getenv(name, "").strip()
    if not value:
        raise RuntimeError(f"Falta variable de entorno requerida: {name}")
    return value


def main() -> int:
    host = env_required("OPENBORME_FTP_HOST")
    user = env_required("OPENBORME_FTP_USER")
    password = env_required("OPENBORME_FTP_PASS")

    ftp = ftplib.FTP(host, timeout=30)
    ftp.login(user, password)
    print("Connected to FTP")
    print(f"Current Directory: {ftp.pwd()}")

    print("\nFile list:")
    ftp.retrlines("LIST")
    ftp.quit()
    return 0


if __name__ == "__main__":
    try:
        raise SystemExit(main())
    except Exception as e:
        print(f"Error: {e}", file=sys.stderr)
        raise SystemExit(1)
