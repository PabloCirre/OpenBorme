import xml.etree.ElementTree as ET
import os
import re

CIF_PATTERN = re.compile(r'\b[ABCDEFGHJNPQRSUVW][\s\-\.]?\d{2}[\s\-\.]?\d{3}[\s\-\.]?\d{3}\b', re.IGNORECASE)
URL_PATTERN = re.compile(r'\b((?:https?://|www\.)[a-zA-Z0-9\-\.]+\.[a-z]{2,}(?:\/[^\s\),. ]*)?)\b', re.IGNORECASE)
WORKERS_PATTERN = re.compile(r'\b(\d+)\s*(?:trabajadores|empleados|miembros de plantilla)\b', re.IGNORECASE)

def clean_value(val):
    if not val: return ""
    return re.sub(r'[\s\.\-]', '', val).upper() if len(val) >= 9 else val.strip()

def parse_section_2_xml(file_path):
    try:
        tree = ET.parse(file_path)
        root = tree.getroot()
        
        metadata = root.find('metadatos')
        if metadata is None:
            return None
        
        company_name = metadata.findtext('titulo', '').strip()
        act_type = metadata.findtext('departamento', '').strip()
        date = metadata.findtext('fecha_publicacion', '').strip()
        identificador = metadata.findtext('identificador', '').strip()
        
        texto_elem = root.find('texto')
        description_parts = []
        if texto_elem is not None:
            for p in texto_elem.findall('p'):
                if p.text:
                    description_parts.append(p.text.strip())
        
        description = "\n".join(description_parts)
        
        # Enrichment
        cif_match = CIF_PATTERN.search(description)
        if not cif_match: # Try company name or title
             cif_match = CIF_PATTERN.search(company_name)
        cif = clean_value(cif_match.group(0)) if cif_match else ""
        
        url_match = URL_PATTERN.search(description)
        url = url_match.group(1) if url_match else ""
        
        workers_match = WORKERS_PATTERN.search(description)
        workers = workers_match.group(1) if workers_match else ""
        
        return {
            'Date': date,
            'Section': '2',
            'Province': 'National',
            'Company Name': company_name,
            'CIF': cif,
            'Website': url,
            'Capital': '',
            'Address': '',
            'Workers': workers,
            'Act Type': act_type,
            'Details': description,
            'ID': identificador
        }
    except Exception as e:
        print(f"Error parsing {file_path}: {e}")
        return None

if __name__ == "__main__":
    result = parse_section_2_xml("sample_act.xml")
    if result:
        print(f"Company: {result['Company Name']}")
        print(f"Type: {result['Act Type']}")
        print(f"Details length: {len(result['Details'])}")
