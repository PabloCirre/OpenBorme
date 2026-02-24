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


def upload_item(ftp: ftplib.FTP, local_path: Path, remote_item: str) -> None:
    normalized = remote_item.replace("\\", "/")
    lower = normalized.lower()
    if ".git" in lower or "__pycache__" in lower or lower.endswith(".pdf") or lower.endswith(".sqlite") or lower.endswith(".db"):
        return

    if local_path.is_dir() and normalized.startswith("pipeline/data"):
        return

    if local_path.is_file():
        parent = normalized.rsplit("/", 1)[0] if "/" in normalized else ""
        if parent:
            ensure_remote_dirs(ftp, parent)
        with local_path.open("rb") as f:
            ftp.storbinary(f"STOR {normalized}", f)
        print(f"Uploaded file: {normalized}")
        return

    if local_path.is_dir():
        ensure_remote_dirs(ftp, normalized)
        for sub_item in sorted(local_path.iterdir()):
            sub_remote = f"{normalized}/{sub_item.name}"
            upload_item(ftp, sub_item, sub_remote)


def deploy() -> None:
    host = env_required("OPENBORME_FTP_HOST")
    user = env_required("OPENBORME_FTP_USER")
    password = env_required("OPENBORME_FTP_PASS")

    repo_root = Path(__file__).resolve().parent.parent
    local_web_root = Path(os.getenv("OPENBORME_LOCAL_WEB_ROOT", str(repo_root / "public_html"))).resolve()
    local_pipeline_root = Path(os.getenv("OPENBORME_LOCAL_PIPELINE_ROOT", str(repo_root / "pipeline"))).resolve()

    if not local_web_root.exists():
        raise RuntimeError(f"No existe local_web_root: {local_web_root}")
    if not local_pipeline_root.exists():
        raise RuntimeError(f"No existe local_pipeline_root: {local_pipeline_root}")

    print(f"Connecting to {host}...")
    ftp = ftplib.FTP(host, timeout=30)
    ftp.login(user, password)
    print("Connected.")

    try:
        print("Deploying public_html -> remote root ...")
        for item in sorted(local_web_root.iterdir()):
            upload_item(ftp, item, item.name)

        print("Deploying pipeline -> remote /pipeline ...")
        ensure_remote_dirs(ftp, "pipeline")
        for item in sorted(local_pipeline_root.iterdir()):
            upload_item(ftp, item, f"pipeline/{item.name}")
    finally:
        ftp.quit()

    print("Deployment successful.")


if __name__ == "__main__":
    try:
        deploy()
    except Exception as e:
        print(f"Error during deployment: {e}", file=sys.stderr)
        sys.exit(1)
