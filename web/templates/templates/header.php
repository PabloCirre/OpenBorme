<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OpenBorme - Empresa y Registro Mercantil</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="icon" type="image/png" href="/assets/img/favicon.png">
</head>

<body>
    <header
        style="height: 64px; background: white; border-bottom: 1px solid var(--border-dark); position: sticky; top: 0; z-index: 1000;">
        <div class="container"
            style="height: 100%; display: flex; align-items: center; justify-content: space-between;">
            <a href="/"
                style="font-size: 20px; font-weight: 800; color: var(--text-primary); text-decoration: none;">OpenBorme</a>

            <div style="flex: 1; max-width: 480px; margin: 0 var(--space-6); position: relative;"
                class="search-container">
                <form action="/buscar" method="GET">
                    <input type="text" name="q" id="global-search" class="input-main"
                        placeholder="Buscar por empresa o texto (presiona /)"
                        value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                </form>
            </div>

            <nav style="display: flex; gap: var(--space-4);">
                <a href="/sumario" class="btn btn-ghost btn-s">Diario</a>
                <a href="/empresas" class="btn btn-ghost btn-s">Empresas</a>
                <a href="/descargas" class="btn btn-ghost btn-s">Descargas</a>
                <a href="/api" class="btn btn-ghost btn-s">API</a>
            </nav>
        </div>
    </header>