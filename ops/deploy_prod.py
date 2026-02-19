import ftplib
import os
import sys

# FTP Credentials (from ops/sync_data.py)
host = 'ftp.servidor3000.lucusvirtual.es'
user = 'branvan3000@openborme.es'
password = '5000Razones2.0'

# Paths
LOCAL_WEB_ROOT = r'd:\Pycharm\OpenBorme\public_html'

def upload_item(ftp, local_path, remote_item):
    if ".git" in remote_item or "__pycache__" in remote_item or ".pdf" in remote_item.lower():
        return
        
    if os.path.isfile(local_path):
        print(f"Uploading file: {remote_item}")
        with open(local_path, 'rb') as f:
            ftp.storbinary(f'STOR {remote_item}', f)
    elif os.path.isdir(local_path):
        print(f"Syncing directory: {remote_item}")
        try:
            ftp.mkd(remote_item)
        except:
            pass # Already exists
        for sub_item in os.listdir(local_path):
            upload_item(ftp, os.path.join(local_path, sub_item), remote_item + "/" + sub_item)

def deploy():
    try:
        print(f"Connecting to {host}...")
        ftp = ftplib.FTP(host)
        ftp.login(user, password)
        print("Connected successfuly.")
        
        # 1. Upload everything from public_html/ to root
        print("Deploying public files...")
        for item in os.listdir(LOCAL_WEB_ROOT):
            local_path = os.path.join(LOCAL_WEB_ROOT, item)
            upload_item(ftp, local_path, item)
                
        ftp.quit()
        print("Deployment successful.")
    except Exception as e:
        print(f"Error during deployment: {e}")
        sys.exit(1)

if __name__ == "__main__":
    deploy()
