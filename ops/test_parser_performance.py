
import requests
import time
import sys

# Production URL
BASE_URL = "https://openborme.es"

# Test Cases ( Real Data from 2024 - Business Day Jan 2 )
# IDs follow common BOE pattern: BORME-A-YYYY-NUM-PROVINCE
# We'll try Act 1 for Madrid (28) and Barcelona (08)
TESTS = [
    {
        "id": "BORME-A-2024-1-28",
        "date": "20240102",
        "desc": "Madrid - Acto 1 (2024)"
    },
    {
        "id": "BORME-A-2024-1-08",
        "date": "20240102",
        "desc": "Barcelona - Acto 1 (2024)"
    },
    {
        "id": "BORME-A-2024-1-46",
        "date": "20240102",
        "desc": "Valencia - Acto 1 (2024)"
    },
    {
        "id": "BORME-A-2024-1-41",
        "date": "20240102",
        "desc": "Sevilla - Acto 1 (2024)"
    },
    {
        "id": "BORME-A-2026-3026",
        "date": "20260219",
        "desc": "User Custom Typos (2026)"
    }
]

def run_test():
    print(f"[*] Starting Performance Test on {BASE_URL}...\n")
    
    success_count = 0
    
    for t in TESTS:
        url = f"{BASE_URL}/borme/doc/{t['id']}?date={t['date']}"
        print(f"--- Prueba: {t['desc']} ---")
        print(f"URL: {url}")
        
        start_time = time.time()
        try:
            # Add a user agent to mimic browser/avoid basic blocks
            headers = {
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            }
            response = requests.get(url, headers=headers, timeout=15)
            end_time = time.time()
            
            duration = end_time - start_time
            
            print(f"Status Code: {response.status_code}")
            print(f"Time: {duration:.4f} seconds")
            
            # Check if it was a successful parse (look for unique HTML elements from viewer.php)
            if "Boletín Provincial:" in response.text and "Actos Extraídos" in response.text:
                if "Documento No Disponible" in response.text:
                    print("[+] RESULT: HANDLED 404 (Document Not Found)")
                elif "Documento Completo (Sin Estructura)" in response.text:
                    print("[-] RESULT: FALLBACK TRIGGERED (Parsing Failed)")
                    
                    # Save full response to debug file
                    with open("debug_failed.html", "w", encoding="utf-8") as f:
                        f.write(response.text)
                    print("[*] Saved failed response to debug_failed.html")
                else:
                    print("[+] RESULT: SUCCESSFULLY PARSED")
                    
                # Extract act count if possible
                if "Actos Extraídos" in response.text:
                    try:
                        part = response.text.split("Actos Extraídos")[0].split("•")[-1].strip()
                        print(f"[+] Acts Found: {part}")
                    except:
                        pass
                
                # Extract First Company Name for Validation
                # Look for <h2 ...>COMPANY NAME</h2>
                if '<h2 style="margin: var(--space-3) 0;' in response.text:
                     try:
                         # Rough parsing for validation
                         part = response.text.split('<h2 style="margin: var(--space-3) 0;')[1].split('>')[1].split('<')[0]
                         print(f"[+] First Company: {part}")
                     except:
                         pass
                
                success_count += 1
            elif "Error de Obtención" in response.text:
                print("[-] RESULT: FAILED TO FETCH PDF (BOE Error or Invalid ID)")
            else:
                print("[?] RESULT: UNKNOWN RESPONSE (Check content manually)")
                # Print snippet
                print(f"Snippet: {response.text[:200]}...")

        except Exception as e:
            print(f"[!] ERROR: {e}")
            
        print("\n")

    print(f"[*] Test Complete. {success_count}/{len(TESTS)} success.")

if __name__ == "__main__":
    run_test()
