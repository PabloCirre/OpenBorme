#!/usr/bin/env python3
"""
Estudio de URLs de OpenBorme.

- Verifica estado HTTP, redirecciones, latencia y tipo de contenido.
- Toma muestras reales de actos/provincias desde SQLite para medir cobertura.
- Genera informe Markdown + JSON en docs/.
"""

from __future__ import annotations

import argparse
import json
import sqlite3
import time
import urllib.error
import urllib.parse
import urllib.request
from datetime import datetime
from pathlib import Path
from typing import Dict, List


def slugify(value: str) -> str:
    txt = value.lower()
    repl = {
        "á": "a",
        "à": "a",
        "ä": "a",
        "â": "a",
        "é": "e",
        "è": "e",
        "ë": "e",
        "ê": "e",
        "í": "i",
        "ì": "i",
        "ï": "i",
        "î": "i",
        "ó": "o",
        "ò": "o",
        "ö": "o",
        "ô": "o",
        "ú": "u",
        "ù": "u",
        "ü": "u",
        "û": "u",
        "ñ": "n",
        " ": "-",
    }
    for k, v in repl.items():
        txt = txt.replace(k, v)
    out = []
    for ch in txt:
        if ch.isalnum() or ch in "-":
            out.append(ch)
        else:
            out.append("-")
    while "--" in "".join(out):
        out = list("".join(out).replace("--", "-"))
    return "".join(out).strip("-")


def fetch(url: str, timeout: int = 20) -> Dict[str, object]:
    req = urllib.request.Request(
        url,
        headers={
            "User-Agent": "OpenBorme-URL-Study/1.0",
            "Accept": "text/html,application/json,application/xml;q=0.9,*/*;q=0.8",
        },
    )
    started = time.perf_counter()
    try:
        with urllib.request.urlopen(req, timeout=timeout) as resp:
            body = resp.read(512).decode("utf-8", errors="ignore")
            elapsed = (time.perf_counter() - started) * 1000
            return {
                "url": url,
                "final_url": resp.geturl(),
                "status": resp.getcode(),
                "latency_ms": round(elapsed, 2),
                "content_type": resp.headers.get("Content-Type", ""),
                "sample": body.replace("\n", " ")[:180],
            }
    except urllib.error.HTTPError as e:
        elapsed = (time.perf_counter() - started) * 1000
        return {
            "url": url,
            "final_url": url,
            "status": e.code,
            "latency_ms": round(elapsed, 2),
            "content_type": e.headers.get("Content-Type", "") if e.headers else "",
            "sample": "",
            "error": str(e),
        }
    except Exception as e:
        elapsed = (time.perf_counter() - started) * 1000
        return {
            "url": url,
            "final_url": url,
            "status": 0,
            "latency_ms": round(elapsed, 2),
            "content_type": "",
            "sample": "",
            "error": str(e),
        }


def build_url_set(base_url: str, db_path: Path, max_acts: int) -> List[str]:
    core = [
        "/",
        "/buscar",
        "/busqueda-avanzada",
        "/nuevas-empresas",
        "/provincias",
        "/api",
        "/api/documentacion",
        "/sitemap.xml",
        "/sitemap-estatico.xml",
        "/descargas",
        "/manifiesto",
    ]
    urls = {urllib.parse.urljoin(base_url, p) for p in core}

    if db_path.exists():
        con = sqlite3.connect(str(db_path))
        cur = con.cursor()

        cur.execute("SELECT DISTINCT province FROM borme_acts WHERE province != '' ORDER BY province")
        for (province,) in cur.fetchall():
            slug = slugify(str(province))
            if slug:
                urls.add(urllib.parse.urljoin(base_url, f"/borme/provincia/{slug}"))
                urls.add(urllib.parse.urljoin(base_url, f"/nuevas-empresas/{slug}"))

        cur.execute(
            "SELECT id, date FROM borme_acts WHERE id IS NOT NULL AND id != '' ORDER BY date DESC LIMIT ?",
            (max_acts,),
        )
        for act_id, date in cur.fetchall():
            act_id = str(act_id)
            date = str(date or "")
            if act_id:
                q = f"?date={date}" if len(date) == 8 else ""
                urls.add(urllib.parse.urljoin(base_url, f"/borme/doc/{urllib.parse.quote(act_id)}{q}"))

        con.close()

    return sorted(urls)


def write_report(results: List[Dict[str, object]], output_md: Path, output_json: Path) -> None:
    now = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    total = len(results)
    ok_2xx = sum(1 for r in results if 200 <= int(r.get("status", 0)) < 300)
    ok_3xx = sum(1 for r in results if 300 <= int(r.get("status", 0)) < 400)
    err_4xx = sum(1 for r in results if 400 <= int(r.get("status", 0)) < 500)
    err_5xx = sum(1 for r in results if 500 <= int(r.get("status", 0)) < 600)
    err_0 = sum(1 for r in results if int(r.get("status", 0)) == 0)
    avg_latency = round(sum(float(r.get("latency_ms", 0)) for r in results) / max(total, 1), 2)

    slow = sorted(results, key=lambda x: float(x.get("latency_ms", 0)), reverse=True)[:15]
    bad = [r for r in results if int(r.get("status", 0)) >= 400 or int(r.get("status", 0)) == 0]

    lines = [
        "# Estudio de URLs OpenBorme",
        "",
        f"- Fecha: `{now}`",
        f"- URLs analizadas: `{total}`",
        f"- 2xx: `{ok_2xx}` | 3xx: `{ok_3xx}` | 4xx: `{err_4xx}` | 5xx: `{err_5xx}` | errores de red: `{err_0}`",
        f"- Latencia media: `{avg_latency} ms`",
        "",
        "## URLs con error",
    ]

    if bad:
        lines.append("| status | url | final_url | latency_ms |")
        lines.append("|---:|---|---|---:|")
        for r in bad[:80]:
            lines.append(
                f"| {r.get('status')} | {r.get('url')} | {r.get('final_url')} | {r.get('latency_ms')} |"
            )
    else:
        lines.append("Sin errores en la muestra.")

    lines.extend(
        [
            "",
            "## URLs más lentas",
            "| status | url | latency_ms | content_type |",
            "|---:|---|---:|---|",
        ]
    )
    for r in slow:
        lines.append(
            f"| {r.get('status')} | {r.get('url')} | {r.get('latency_ms')} | {str(r.get('content_type', '')).replace('|', ' / ')} |"
        )

    output_md.write_text("\n".join(lines) + "\n", encoding="utf-8")
    output_json.write_text(json.dumps(results, ensure_ascii=False, indent=2), encoding="utf-8")


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--base-url", default="https://openborme.es")
    parser.add_argument("--db-path", default="pipeline/data/openborme.sqlite")
    parser.add_argument("--max-acts", type=int, default=120)
    parser.add_argument("--timeout", type=int, default=20)
    args = parser.parse_args()

    repo = Path(__file__).resolve().parent.parent
    db_path = (repo / args.db_path).resolve()
    docs = repo / "docs"
    docs.mkdir(parents=True, exist_ok=True)

    date_tag = datetime.now().strftime("%Y%m%d")
    output_md = docs / f"url_study_{date_tag}.md"
    output_json = docs / f"url_study_{date_tag}.json"

    urls = build_url_set(args.base_url.rstrip("/"), db_path, args.max_acts)
    results = [fetch(url, timeout=args.timeout) for url in urls]
    write_report(results, output_md, output_json)

    print(f"URLs analizadas: {len(results)}")
    print(f"Markdown: {output_md}")
    print(f"JSON: {output_json}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())

