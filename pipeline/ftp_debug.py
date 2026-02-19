import ftplib

host = 'ftp.servidor3000.lucusvirtual.es'
user = 'branvan3000@openborme.es'
password = '5000Razones2.0'

try:
    ftp = ftplib.FTP(host)
    ftp.login(user, password)
    print("Connected to FTP")
    print(f"Current Directory: {ftp.pwd()}")
    
    print("\nFile list:")
    ftp.retrlines('LIST')
    
    # Check for public_html or similar
    items = ftp.nlst()
    if 'public_html' in items:
        print("\nWARNING: Found public_html directory. Files might need to be moved there.")
    elif 'www' in items:
        print("\nWARNING: Found www directory. Files might need to be moved there.")
    
    # Try to read error_log if exists
    if 'error_log' in items:
        print("\n--- error_log content ---")
        ftp.retrlines('RETR error_log')
        
    ftp.quit()
except Exception as e:
    print(f"Error: {e}")
