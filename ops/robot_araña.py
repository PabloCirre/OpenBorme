import re
import urllib.request
import urllib.parse
from concurrent.futures import ThreadPoolExecutor
from html.parser import HTMLParser
import time

class LinkExtractor(HTMLParser):
    def __init__(self, base_url):
        super().__init__()
        self.base_url = base_url
        self.links = []

    def handle_starttag(self, tag, attrs):
        if tag == 'a':
            for attr, value in attrs:
                if attr == 'href':
                    self.links.append(urllib.parse.urljoin(self.base_url, value))
        elif tag in ['img', 'script', 'iframe', 'source']:
            for attr, value in attrs:
                if attr == 'src':
                    self.links.append(urllib.parse.urljoin(self.base_url, value))
        elif tag == 'link':
            for attr, value in attrs:
                if attr == 'href':
                    self.links.append(urllib.parse.urljoin(self.base_url, value))

class OpenBormeCrawler:
    def __init__(self, start_url, max_threads=10):
        self.start_url = start_url
        self.max_threads = max_threads
        self.visited = set()
        self.to_visit = set([start_url])
        self.broken_links = {} # URL -> Reason
        self.domain = urllib.parse.urlparse(start_url).netloc
        
    def is_internal(self, url):
        return urllib.parse.urlparse(url).netloc == self.domain
        
    def fetch_url(self, url):
        try:
            req = urllib.request.Request(url, headers={'User-Agent': 'OpenBorme-CrawlerBot/1.0'})
            res = urllib.request.urlopen(req, timeout=10)
            content_type = res.getheader('Content-Type') or ''
            
            # Solo analizamos HTML para sacar más enlaces
            if 'text/html' in content_type:
                content = res.read().decode('utf-8', errors='ignore')
                parser = LinkExtractor(url)
                parser.feed(content)
                return 200, parser.links
            return res.getcode(), []
            
        except urllib.error.HTTPError as e:
            return e.code, []
        except urllib.error.URLError as e:
            return "Connection Error", []
        except Exception as e:
            return "Timeout/Error", []

    def crawl(self):
        print(f"[START] Iniciando Robot en: {self.start_url}\n" + "-"*50)
        
        while self.to_visit:
            # Procesar por lotes
            current_batch = list(self.to_visit)[:self.max_threads]
            for url in current_batch:
                self.to_visit.remove(url)
                self.visited.add(url)
                
            print(f"[INFO] Comprobando {len(current_batch)} URLs concurrentes... (Visitadas: {len(self.visited)})")
            
            new_links = set()
            with ThreadPoolExecutor(max_workers=self.max_threads) as executor:
                results = executor.map(self.fetch_url, current_batch)
                
                for url, (status, links_found) in zip(current_batch, results):
                    if status != 200:
                        self.broken_links[url] = status
                    else:
                        for l in links_found:
                            # Ignoramos anclas, mailto y javascript
                            l = l.split('#')[0]
                            if not l.startswith(('http', 'https')):
                                continue
                                
                            if l not in self.visited and l not in self.to_visit:
                                # Si es externo, lo verificamos pero no entramos a extraer URLs de él
                                if not self.is_internal(l):
                                    self.visited.add(l)
                                    status_ext, _ = self.fetch_url(l)
                                    if status_ext != 200:
                                        self.broken_links[l] = f"{status_ext} (Externo)"
                                else:
                                    # Si es interno, lo añadimos a la cola
                                    self.to_visit.add(l)
                                    
            time.sleep(0.5) # Respeto al servidor local
            
        print("\n" + "="*50)
        print(f"[SUCCESS] Robot Finalizado. URLs Totales Rasteadas: {len(self.visited)}")
        
        if not self.broken_links:
            print("[OK] El sistema esta inmaculado. No se han encontrado enlaces rotos 404.")
        else:
            print(f"[WARNING] ATENCION: Se encontraron {len(self.broken_links)} enlaces rotos:")
            for url, err in self.broken_links.items():
                print(f" - [{err}] {url}")

if __name__ == '__main__':
    SERVER_URL = "https://openborme.es"
        
    robot = OpenBormeCrawler(SERVER_URL, max_threads=10)
    robot.crawl()
