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

    <!-- Linear Typography: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/assets/css/styles.css?v=4.0.0">
    <script src="/assets/js/design_v3.js" defer></script>
</head>

<body>
    <header class="inst-header">
        <div class="header-container container">
            <a href="/" class="logo-container">
                <span class="logo-open">Open</span><span class="logo-borme-box">Borme</span>
            </a>

            <button class="mobile-menu-btn" aria-label="Menu"
                onclick="document.getElementById('nav-menu').classList.toggle('active')">
                MENU
            </button>

            <nav id="nav-menu" class="header-nav">
                <div class="nav-item has-megamenu">
                    <a href="/explorar" class="nav-link">EXPLORAR</a>
                    <div class="mega-menu">
                        <div class="container mega-menu-grid">
                            <div class="mega-col">
                                <h5>Contenido</h5>
                                <a href="/borme/dias">Boletines Diarios</a>
                                <a href="/provincias">Por Provincias</a>
                                <a href="/secciones">Por Secciones</a>
                            </div>
                            <div class="mega-col">
                                <h5>Herramientas</h5>
                                <a href="/buscar">Buscador Avanzado</a>
                                <a href="/descargas">Datos Abiertos</a>
                                <a href="/api">API para Desarrolladores</a>
                            </div>
                            <div class="mega-col">
                                <h5>Proyecto</h5>
                                <a href="/manifiesto">Manifiesto Técnico</a>
                                <a href="/metodologia">Metodología</a>
                                <a href="/contacto">Contacto</a>
                            </div>
                        </div>
                    </div>
                </div>
                <a href="/descargas" class="nav-link">DATOS</a>
                <a href="/api" class="btn btn-primary btn-s">API</a>
            </nav>
        </div>
    </header>