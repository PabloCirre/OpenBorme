import pypdf
import re
import os

# CIF/NIF pattern: More flexible (B12345678, B-12345678, etc.)
CIF_PATTERN = re.compile(r'\b[ABCDEFGHJNPQRSUVW][\s\-\.]?\d{2}[\s\-\.]?\d{3}[\s\-\.]?\d{3}\b', re.IGNORECASE)
# URL pattern
URL_PATTERN = re.compile(r'\b((?:https?://|www\.)[a-zA-Z0-9\-\.]+\.[a-z]{2,}(?:\/[^\s\),. ]*)?)\b', re.IGNORECASE)
# Capital pattern (e.g., 3.100,00 Euros)
CAPITAL_PATTERN = re.compile(r'Capital:\s*([\d\.,]+\s*Euros)', re.IGNORECASE)
# Address pattern (e.g., Domicilio: C/ GOYA 143 (MADRID))
ADDRESS_PATTERN = re.compile(r'Domicilio:\s*(.*?)\.\s', re.IGNORECASE)
# Headcount pattern (looking for "X trabajadores" or "X empleados" or "plantilla de X")
WORKERS_PATTERN = re.compile(r'\b(\d+)\s*(?:trabajadores|empleados|miembros de plantilla)\b', re.IGNORECASE)

def clean_value(val):
    if not val: return ""
    return re.sub(r'[\s\.\-]', '', val).upper() if len(val) >= 9 else val.strip()

def parse_section_1_pdf(file_path, province_name):
    try:
        reader = pypdf.PdfReader(file_path)
        full_text = ""
        for page in reader.pages:
            full_text += page.extract_text() + "\n"
        
        # ACT Pattern: Number - Company Name
        # Looking for lines like "82337 - BOSPEC PARTNERS SL."
        # Sometimes there's page info in between, so we join lines but keep an eye on patterns
        
        # Pre-process: Remove header/footer noise if possible, but regex might be enough
        # The acts seem to start with a number followed by " - " and the company name
        
        acts = []
        # Regex to find starting of an act: digits - NAME
        # Using re.MULTILINE to find it at the start of lines
        pattern = re.compile(r'^(\d+) - (.*?)\.\s*$', re.MULTILINE)
        
        matches = list(pattern.finditer(full_text))
        
        for i in range(len(matches)):
            start_m = matches[i]
            act_id = start_m.group(1)
            company_name = start_m.group(2).strip()
            
            # The content is from the end of this match to the start of the next match
            start_pos = start_m.end()
            end_pos = matches[i+1].start() if i+1 < len(matches) else len(full_text)
            
            content = full_text[start_pos:end_pos].strip()
            
            # Enrichment extraction
            cif_match = CIF_PATTERN.search(content)
            cif = clean_value(cif_match.group(0)) if cif_match else ""
            
            url_match = URL_PATTERN.search(content)
            url = url_match.group(1) if url_match else ""
            
            capital_match = CAPITAL_PATTERN.search(content)
            capital = capital_match.group(1) if capital_match else ""
            
            address_match = ADDRESS_PATTERN.search(content)
            address = address_match.group(1) if address_match else ""
            
            workers_match = WORKERS_PATTERN.search(content)
            workers = workers_match.group(1) if workers_match else ""
            
            # First line often contains more info
            act_type = content.split('.')[0] if '.' in content else "Other"
            
            acts.append({
                'Date': '',
                'Section': '1',
                'Province': province_name,
                'Company Name': company_name,
                'CIF': cif,
                'Website': url,
                'Capital': capital,
                'Address': address,
                'Workers': workers,
                'Act Type': act_type,
                'Details': content,
                'ID': f"{province_name}-{act_id}"
            })
            
        return acts
    except Exception as e:
        print(f"Error parsing PDF {file_path}: {e}")
        return []

if __name__ == "__main__":
    test_file = "madrid_sample.pdf"
    if os.path.exists(test_file):
        results = parse_section_1_pdf(test_file, "MADRID")
        print(f"Extracted {len(results)} acts from {test_file}")
        if results:
            print(f"First Company: {results[0]['Company Name']}")
            print(f"First Act Type: {results[0]['Act Type']}")
