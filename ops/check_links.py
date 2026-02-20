import os
import re
import urllib.request
import urllib.error
import urllib.parse
from concurrent.futures import ThreadPoolExecutor

PUBLIC_HTML_DIR = r"d:\Pycharm\OpenBorme\public_html"
INDEX_PHP_PATH = os.path.join(PUBLIC_HTML_DIR, "index.php")

# Regex to find routes in index.php (e.g., 'buscar' => ...)
STATIC_ROUTE_PATTERN = re.compile(r"'(.*?)'\s*=>\s*\[")
# Regex for dynamic routes checking in index.php
DYNAMIC_ROUTES = [
    re.compile(r"^diario_borme/ultimo\.php$"),
    re.compile(r"^borme/dias$"),
    re.compile(r"^borme/dias/\d{4}/\d{2}/\d{2}/?$"),
    re.compile(r"^(BORME-[A-Z]-\d{4}-[\d-]+)$"), # Wait, index.php does: /(BORME-[A-Z]-\d{4}-[\d-]+)/
    re.compile(r"^borme/provincia/[a-z-]+$"),
    re.compile(r"^borme/provincia/[a-z-]+/\d{4}/\d{2}/\d{2}$"),
    re.compile(r"^borme/sumario/\d{4}/\d{2}/\d{2}$"),
    re.compile(r"^tipo/[a-z-]+$"),
    re.compile(r"^empresa/[a-zA-Z0-9-]+$"),
    re.compile(r"^export\?id=.*") # Additional known route handled maybe elsewhere or as query param
]

# Regex to find href and src in files
HREF_PATTERN = re.compile(r'href\s*=\s*["\'](.*?)["\']', re.IGNORECASE)
SRC_PATTERN = re.compile(r'src\s*=\s*["\'](.*?)["\']', re.IGNORECASE)

def load_static_routes():
    routes = set(['', '/'])
    if os.path.exists(INDEX_PHP_PATH):
        with open(INDEX_PHP_PATH, 'r', encoding='utf-8') as f:
            content = f.read()
            # Find the $routes array
            match = re.search(r'\$routes\s*=\s*\[(.*?)\];', content, re.DOTALL)
            if match:
                routes_block = match.group(1)
                for r_match in STATIC_ROUTE_PATTERN.finditer(routes_block):
                    route = r_match.group(1).strip('/')
                    routes.add('/' + route)
                    routes.add(route)
    return routes

STATIC_ROUTES = load_static_routes()

def is_valid_internal(url):
    # Remove query params and fragments
    parsed = urllib.parse.urlparse(url)
    clean_path = parsed.path.strip('/')
    
    # 1. Check if it's a known static route
    if clean_path in STATIC_ROUTES or '/' + clean_path in STATIC_ROUTES:
        return True, "Static Route Match"
        
    # 2. Check if it's a dynamic route
    for regex in DYNAMIC_ROUTES:
        if regex.match(clean_path):
            return True, "Dynamic Route Match"
            
    # 3. Check if it handles php partials inside
    if "<?=" in clean_path or "<?php" in clean_path:
        # It's a template string, let's assume it maps to one of the dynamic routes if the prefix matches
        prefix = clean_path.split('<?')[0].strip('/')
        if prefix in ('borme/dias', 'borme/doc', 'borme/provincia', 'borme/sumario', 'tipo', 'empresa', 'export'):
            return True, "Template Dynamic Path"
        if not prefix: # something like href="<?= $url ?>"
            return True, "Template Variable Path"
            
    # 4. Check if it's a physical file
    physical_path = os.path.join(PUBLIC_HTML_DIR, clean_path.replace('/', os.sep))
    if os.path.exists(physical_path):
        return True, "Physical File Exists"
        
    return False, "Not Found"

def check_external(url):
    try:
        req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
        res = urllib.request.urlopen(req, timeout=5)
        return res.getcode() < 400
    except Exception as e:
        return False

def main():
    files_to_check = []
    for root, _, files in os.walk(PUBLIC_HTML_DIR):
        for f in files:
            if f.endswith('.php') or f.endswith('.html'):
                files_to_check.append(os.path.join(root, f))
                
    internal_links = set()
    external_links = set()
    
    for filepath in files_to_check:
        with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()
            for match in HREF_PATTERN.finditer(content):
                url = match.group(1).strip()
                if not url or url.startswith('#') or url.startswith('mailto:') or url.startswith('javascript:'):
                    continue
                if url.startswith('http://') or url.startswith('https://'):
                    external_links.add(url)
                else:
                    internal_links.add(url)
            for match in SRC_PATTERN.finditer(content):
                url = match.group(1).strip()
                if not url or url.startswith('data:'):
                    continue
                if url.startswith('http://') or url.startswith('https://'):
                    external_links.add(url)
                else:
                    internal_links.add(url)
                    
    print(f"Found {len(internal_links)} internal links and {len(external_links)} external links.")
    
    errors = []
    
    # Check internal links
    print("\nChecking internal links...")
    for url in sorted(internal_links):
        is_valid, reason = is_valid_internal(url)
        if not is_valid:
            errors.append(f"[INTERNAL ERROR] Broken link: {url}")
            
    # Check external links
    print(f"\nChecking external links... ({len(external_links)})")
    def test_ext(url):
        if not check_external(url):
            return f"[EXTERNAL ERROR] Broken or timeout: {url}"
        return None
        
    with ThreadPoolExecutor(max_workers=10) as executor:
        results = executor.map(test_ext, external_links)
        for res in results:
            if res:
                errors.append(res)
                
    print("\n--- RESULTS ---")
    if not errors:
        print("PERFECT! All links are valid.")
    else:
        print(f"Found {len(errors)} issues:")
        for err in errors:
            print(err)

if __name__ == '__main__':
    main()
