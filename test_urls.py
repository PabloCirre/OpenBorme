import urllib.request
import urllib.error
import time

BASE_URL = "https://openborme.es"

endpoints = [
    "/",
    "/buscar",
    "/busqueda-avanzada",
    "/resultados",
    "/sitemap.xml",
    "/exportar",
    "/diario_borme",
    "/diario_borme/ayuda.php",
    "/seccion/actos-inscritos",
    "/seccion/anuncios",
    "/provincias",
    "/tipos-de-actos",
    "/empresas",
    "/personas",
    "/alertas",
    "/mi-cuenta/acceso",
    "/descargas",
    "/api",
    "/api/documentacion",
    "/metodologia",
    "/fuentes",
    "/aviso-legal",
    "/terminos-de-uso",
    "/exencion-responsabilidad",
    "/privacidad",
    "/cookies",
    "/faq",
    "/contacto",
    "/calidad-de-datos",
    "/reutilizacion-y-atribucion",
    "/canal-de-rectificacion",
    "/proteccion-de-datos/derechos",
    "/modelo-de-datos",
    "/manifiesto",
    "/objetivos",
    "/status",
    "/borme/dias/2024/02/10/",
    "/borme/doc/BORME-A-2024-21-28",
    "/borme/provincia/madrid",
    "/borme/provincia/madrid/2024/02/10",
    "/borme/sumario/2024/02/10",
    "/tipo/constitucion",
    "/empresa/b12345678"
]

print(f"{'URL':<45} | {'Status':<10}")
print("-" * 60)

for ep in endpoints:
    url = BASE_URL + ep
    try:
        req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
        response = urllib.request.urlopen(req, timeout=10)
        status = response.getcode()
        print(f"{ep:<45} | {status} OK")
    except urllib.error.HTTPError as e:
        print(f"{ep:<45} | {e.code} Error")
    except Exception as e:
        print(f"{ep:<45} | ERROR: {str(e)}")
    
    time.sleep(0.5) # Be gentle to the server
