import ftplib
import os
import sys

host = 'ftp.servidor3000.lucusvirtual.es'
user = 'branvan3000@openborme.es'
password = '5000Razones2.0'

def upload_item(ftp, local_base, item_name, remote_path=""):
    local_path = os.path.join(local_base, item_name)
    remote_item = item_name.replace("\\", "/")
    
    if ".git" in item_name or "__pycache__" in item_name or ".pdf" in item_name.lower():
        return
        
    if os.path.isfile(local_path):
        print(f"Uploading file: {item_name}")
        with open(local_path, 'rb') as f:
            ftp.storbinary(f'STOR {remote_item}', f)
    elif os.path.isdir(local_path):
        if item_name == "data": return # Strictly skip data folder
        print(f"Syncing directory: {item_name}")
        try:
            ftp.mkd(remote_item)
        except:
            pass # Already exists
        for sub_item in os.listdir(local_path):
            upload_item(ftp, local_base, os.path.join(item_name, sub_item), remote_path)

def remove_remote_dir(ftp, path):
    try:
        for item in ftp.nlst(path):
            if item in [".", ".."]: continue
            try:
                ftp.delete(item)
            except:
                try:
                    remove_remote_dir(ftp, item)
                except:
                    pass
        ftp.rmd(path)
        print(f"Successfully removed remote dir: {path}")
    except:
        pass

items_to_upload = [
    'index.php', 'admin.php', 'visor.php', 'sumario.php', 
    'search.php', 'api_info.php', 'api.php', '.htaccess',
    'assets', 'core', 'docs', 'scripts', 'legacy', 'templates'
]

local_dir = r'c:\Users\MASTER\Desktop\SuperBorme'

try:
    ftp = ftplib.FTP(host)
    ftp.login(user, password)
    print("Connected to FTP")
    
    # Comprehensive cleanup
    remove_remote_dir(ftp, "data") # Delete all PDFs on server
    
    old_files = [
        'styles.css', 'BormeDownloader.php', 'ParserPdf.php', 'ParserXml.php', 
        'test.php', 'main.py', 'parser_pdf.py', 'parser_xml.py', 
        'borme_downloader.py', 'inspect_pdf.py', 'pdf_inspect.txt',
        'pdf_inspect_2.txt', 'borme_html_sample.html', 'borme_sumario.xml',
        'sample_act.xml', 'madrid_sample.pdf', 'main.php', 'cron.php',
        'start_server.bat', 'start_server.ps1'
    ]
    for old in old_files:
        try:
            ftp.delete(old)
            print(f"Cleaned up legacy file: {old}")
        except:
            pass

    for item in items_to_upload:
        if os.path.exists(os.path.join(local_dir, item)):
            upload_item(ftp, local_dir, item)
            
    ftp.quit()
    print("Deployment and clean restructuring successful.")
except Exception as e:
    print(f"Error: {e}")
    sys.exit(1)
