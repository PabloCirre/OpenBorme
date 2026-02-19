import subprocess
import os
import shutil

# Configuración
REMOTE_PRIVATE = "private"
REMOTE_PUBLIC = "public"
BRANCH_MAIN = "main"
BRANCH_PUB = "publish-tmp"

# Carpetas a MANTENER en el repo público
KEEP_PATHS = ["public_html", "docs", "README.md", "LICENSE", "CONTRIBUTING.md", ".gitignore", "ops"]

def run_git(cmd):
    print(f"Executing: git {' '.join(cmd)}")
    result = subprocess.run(["git"] + cmd, capture_output=True, text=True)
    if result.returncode != 0:
        print(f"Error: {result.stderr}")
    return result.returncode == 0

def sync():
    # 1. Asegurar que estamos en main y todo está commiteado
    print("[*] Sincronizando repositorio PRIVADO...")
    run_git(["add", "."])
    run_git(["commit", "-m", "chore: sync private repository"])
    run_git(["push", REMOTE_PRIVATE, BRANCH_MAIN])
    
    # 2. Preparar el push PÚBLICO
    print("\n[*] Preparando despliegue PÚBLICO...")
    
    # Crear rama temporal si no existe
    run_git(["checkout", "-B", BRANCH_PUB])
    
    # Eliminar lo que NO queremos publicar
    all_items = os.listdir(".")
    for item in all_items:
        if item.startswith(".git"): continue
        if item not in KEEP_PATHS:
            if os.path.isdir(item):
                shutil.rmtree(item)
            else:
                os.remove(item)
            print(f"Removed for public: {item}")
            
    # Commit de la limpieza
    run_git(["add", "."])
    run_git(["commit", "-m", "chore: publish public content only"])
    
    # Push al repo público (forzado para mantener el historial limpio)
    print("\n[*] Subiendo a OpenBorme (Git Hub Público)...")
    run_git(["push", REMOTE_PUBLIC, f"{BRANCH_PUB}:main", "--force"])
    
    # 3. Volver a main
    print("\n[*] Volviendo a rama principal...")
    run_git(["checkout", BRANCH_MAIN])
    run_git(["branch", "-D", BRANCH_PUB])
    
    print("\n[+] Sincronización dual completada con éxito.")

if __name__ == "__main__":
    sync()
