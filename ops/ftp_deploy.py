import ftplib
import os
import sys
from pathlib import Path


def env_required(name: str) -> str:
    value = os.getenv(name, "").strip()
    if not value:
        raise RuntimeError(f"Falta variable de entorno requerida: {name}")
    return value


def ensure_remote_dirs(ftp: ftplib.FTP, remote_dir: str) -> None:
    parts = [p for p in remote_dir.replace("\\", "/").split("/") if p]
    current = ""
    for part in parts:
        current = f"{current}/{part}" if current else part
        try:
            ftp.mkd(current)
        except ftplib.error_perm as exc:
            if not str(exc).startswith("550"):
                raise


def upload_item(ftp: ftplib.FTP, local_base: Path, item_name: str) -> None:
    local_path = (local_base / item_name).resolve()
    remote_item = item_name.replace("\\", "/")
    lower = remote_item.lower()

    if ".git" in lower or "__pycache__" in lower or lower.endswith(".pdf") or lower.endswith(".sqlite") or lower.endswith(".db"):
        return
    if local_path.is_dir() and (item_name == "data" or remote_item.startswith("pipeline/data")):
        return

    if local_path.is_file():
        parent = remote_item.rsplit("/", 1)[0] if "/" in remote_item else ""
        if parent:
            ensure_remote_dirs(ftp, parent)
        with local_path.open("rb") as f:
            ftp.storbinary(f"STOR {remote_item}", f)
        print(f"Uploading file: {remote_item}")
        return

    if local_path.is_dir():
        ensure_remote_dirs(ftp, remote_item)
        for sub_item in sorted(local_path.iterdir()):
            rel = f"{item_name}/{sub_item.name}" if item_name else sub_item.name
            upload_item(ftp, local_base, rel)


def main() -> int:
    host = env_required("OPENBORME_FTP_HOST")
    user = env_required("OPENBORME_FTP_USER")
    password = env_required("OPENBORME_FTP_PASS")

    repo_root = Path(__file__).resolve().parent.parent
    local_dir = Path(os.getenv("OPENBORME_LEGACY_DEPLOY_DIR", str(repo_root))).resolve()
    items_raw = os.getenv(
        "OPENBORME_LEGACY_DEPLOY_ITEMS",
        "public_html,pipeline,docs,ops,README.md,LICENSE,CONTRIBUTING.md",
    )
    items_to_upload = [item.strip() for item in items_raw.split(",") if item.strip()]

    ftp = ftplib.FTP(host, timeout=30)
    ftp.login(user, password)
    print("Connected to FTP")

    try:
        for item in items_to_upload:
            if (local_dir / item).exists():
                upload_item(ftp, local_dir, item)
            else:
                print(f"Skipping missing item: {item}")
    finally:
        ftp.quit()

    print("Deployment successful.")
    return 0


if __name__ == "__main__":
    try:
        raise SystemExit(main())
    except Exception as e:
        print(f"Error: {e}", file=sys.stderr)
        raise SystemExit(1)
