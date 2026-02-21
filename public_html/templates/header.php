<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'OpenBorme | Registro Mercantil') ?></title>
    <meta name="description"
        content="<?= htmlspecialchars($page_description ?? 'Buscador abierto y gratuito del Boletín Oficial del Registro Mercantil (BORME). Consulta empresas, cargos e historial mercantil.') ?>">
    <meta name="google-site-verification" content="M3iTd6WAiraIsdVMp6nReHFN5VaULFXhB6hv-54f2vM" />

    <!-- Open Graph for Social Media Sharing -->
    <meta property="og:title" content="<?= htmlspecialchars($page_title ?? 'OpenBorme | Registro Mercantil') ?>">
    <meta property="og:description"
        content="<?= htmlspecialchars($page_description ?? 'Explora los registros del BORME de manera rápida y sencilla.') ?>">
    <meta property="og:type" content="website">

    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="icon" type="image/png" href="/assets/img/favicon.png">
</head>

<body>
    <header class="inst-header" style="height: 72px; position: sticky; top: 0; z-index: 1000;">
        <div class="container"
            style="height: 100%; display: flex; align-items: center; justify-content: space-between;">
            <a href="/" class="logo-container">
                <span class="logo-open">Open</span>
                <span class="logo-borme-box">Borme</span>
            </a>

            <div class="header-search-wrap"
                style="flex: 1; max-width: 520px; margin: 0 var(--space-6); position: relative;">
                <form action="/buscar" method="GET">
                    <input type="text" name="q" id="global-search" class="input-main"
                        style="border-radius: var(--radius-sm); border: 1px solid var(--border-strong); background: var(--bg-alt);"
                        placeholder="Buscar por empresa o texto (presiona /)"
                        value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                </form>
            </div>

            <button class="mobile-menu-btn" onclick="document.getElementById('nav-menu').classList.toggle('active')">
                ☰
            </button>

            <nav id="nav-menu" class="header-nav" style="display: flex; gap: var(--space-2);">
                <a href="/borme/dias" class="btn btn-ghost btn-s">Diario</a>
                <a href="/provincias" class="btn btn-ghost btn-s">Provincias</a>
                <a href="/empresas" class="btn btn-ghost btn-s">Empresas</a>
                <a href="/descargas" class="btn btn-ghost btn-s">Descargas</a>
                <a href="/api" class="btn btn-primary btn-s" style="border-radius: var(--radius-sm);">API</a>
            </nav>
        </div>
    </header>