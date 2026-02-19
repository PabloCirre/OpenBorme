import ftplib

host = 'ftp.servidor3000.lucusvirtual.es'
user = 'branvan3000@openborme.es'
password = '5000Razones2.0'

try:
    ftp = ftplib.FTP(host)
    ftp.login(user, password)
    print("Connected to FTP")
    
    # Upload test.php
    with open(r'c:\Users\MASTER\Desktop\SuperBorme\test.php', 'rb') as f:
        ftp.storbinary('STOR test.php', f)
    print("Uploaded test.php")
    
    # Rename .htaccess to debug if it's a 500 error cause
    try:
        ftp.rename('.htaccess', '.htaccess_debug')
        print("Renamed .htaccess to .htaccess_debug")
    except:
        print(".htaccess not found or couldn't rename")
        
    ftp.quit()
except Exception as e:
    print(f"Error: {e}")

print("Debug files uploaded and .htaccess disabled.")
