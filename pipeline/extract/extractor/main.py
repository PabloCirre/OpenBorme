import os
import pandas as pd
from borme_downloader import BASE_DATA_DIR
from parser_xml import parse_section_2_xml
from parser_pdf import parse_section_1_pdf

PROVINCE_MAP = {
    '01': 'Araba/Álava', '02': 'Albacete', '03': 'Alicante', '04': 'Almería', '05': 'Ávila',
    '06': 'Badajoz', '07': 'Balears (Illes)', '08': 'Barcelona', '09': 'Burgos', '10': 'Cáceres',
    '11': 'Cádiz', '12': 'Castellón', '13': 'Ciudad Real', '14': 'Córdoba', '15': 'A Coruña',
    '16': 'Cuenca', '17': 'Girona', '18': 'Granada', '19': 'Guadalajara', '20': 'Gipuzkoa',
    '21': 'Huelva', '22': 'Huesca', '23': 'Jaén', '24': 'León', '25': 'Lleida', '26': 'La Rioja',
    '27': 'Lugo', '28': 'Madrid', '29': 'Málaga', '30': 'Murcia', '31': 'Navarra', '32': 'Ourense',
    '33': 'Asturias', '34': 'Palencia', '35': 'Las Palmas', '36': 'Pontevedra', '37': 'Salamanca',
    '38': 'Santa Cruz de Tenerife', '39': 'Cantabria', '40': 'Segovia', '41': 'Sevilla',
    '42': 'Soria', '43': 'Tarragona', '44': 'Teruel', '45': 'Toledo', '46': 'Valencia',
    '47': 'Valladolid', '48': 'Bizkaia', '49': 'Zamora', '50': 'Zaragoza', '51': 'Ceuta', '52': 'Melilla'
}

def main():
    all_data = []
    
    if not os.path.exists(BASE_DATA_DIR):
        print(f"No data directory found at {BASE_DATA_DIR}")
        return

    dates = os.listdir(BASE_DATA_DIR)
    print(f"Found {len(dates)} dates to process.")

    for date_str in sorted(dates):
        date_path = os.path.join(BASE_DATA_DIR, date_str)
        if not os.path.isdir(date_path):
            continue
            
        print(f"Processing date: {date_str}")
        
        # Process Section A (PDFs)
        section_a_path = os.path.join(date_path, "section_A")
        if os.path.exists(section_a_path):
            for filename in os.listdir(section_a_path):
                if filename.endswith(".pdf"):
                    # Province name is often at the end or we can map it?
                    # The downloader doesn't store the province name explicitly in the filename,
                    # but Section A acts are usually named like BORME-A-YYYY-XX-PP.pdf
                    # We can try to extract the province ID and maybe map it or just use the filename.
                    province_id = filename.split("-")[-1].replace(".pdf", "")
                    province_name = PROVINCE_MAP.get(province_id, f"Province {province_id}")
                    
                    pdf_path = os.path.join(section_a_path, filename)
                    acts = parse_section_1_pdf(pdf_path, province_name)
                    for act in acts:
                        act['Date'] = date_str
                        all_data.append(act)

        # Process Section C (XMLs)
        section_c_path = os.path.join(date_path, "section_C")
        if os.path.exists(section_c_path):
            for filename in os.listdir(section_c_path):
                if filename.endswith(".xml"):
                    xml_path = os.path.join(section_c_path, filename)
                    act = parse_section_2_xml(xml_path)
                    if act:
                        act['Date'] = date_str
                        all_data.append(act)

    if not all_data:
        print("No data extracted.")
        return

    df = pd.DataFrame(all_data)
    
    # Export to CSV
    output_file = "../../borme_data.csv"
    print(f"Exporting {len(df)} records to {output_file}...")
    
    try:
        df.to_csv(output_file, index=False, sep=',', encoding='utf-8')
        print("Export successful.")
    except Exception as e:
        print(f"Error exporting to CSV: {e}")

if __name__ == "__main__":
    main()
