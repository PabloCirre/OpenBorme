#!/usr/bin/env python3
"""
Orquestador:
1) Construye SQLite con builder Python.
2) Sube la SQLite a producción por FTP (ops/sync_data.py).
"""

from __future__ import annotations

import argparse
import subprocess
import sys
from pathlib import Path


def run(cmd: list[str], cwd: Path) -> int:
    print("$", " ".join(cmd))
    return subprocess.call(cmd, cwd=str(cwd))


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--start", default="2020-01-01")
    parser.add_argument("--end", default="2026-02-24")
    parser.add_argument("--resume", action="store_true")
    parser.add_argument("--reset", action="store_true")
    parser.add_argument("--no-upload", action="store_true")
    args = parser.parse_args()

    repo = Path(__file__).resolve().parent.parent
    builder = repo / "pipeline" / "extract" / "extractor" / "build_db.py"
    sync = repo / "ops" / "sync_data.py"

    cmd = [
        sys.executable,
        str(builder),
        "--start",
        args.start,
        "--end",
        args.end,
    ]
    if args.resume:
        cmd.append("--resume")
    if args.reset:
        cmd.append("--reset")

    rc = run(cmd, repo)
    if rc != 0:
        print(f"Builder falló con código {rc}")
        return rc

    if args.no_upload:
        print("Build completado. Upload omitido (--no-upload).")
        return 0

    rc = run([sys.executable, str(sync)], repo)
    if rc != 0:
        print(f"Upload falló con código {rc}")
        return rc

    print("Build + upload completados.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())

