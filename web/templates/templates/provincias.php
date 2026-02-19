<div style="margin-bottom: 3rem; border-bottom: 2px solid var(--border-color); padding-bottom: 2rem;">
    <h1 style="font-size: 2rem; color: var(--boe-red);">BORME por Provincias</h1>
    <p style="color: var(--text-secondary); font-size: 1.1rem;">Acceso directo a las publicaciones del Registro
        Mercantil de cada provincia española.</p>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1.5rem;">
    <?php
    $provincias = [
        "Madrid",
        "Barcelona",
        "Valencia",
        "Sevilla",
        "Zaragoza",
        "Málaga",
        "Murcia",
        "Palma",
        "Las Palmas",
        "Bilbao",
        "Alicante",
        "Córdoba",
        "Valladolid",
        "Vigo",
        "Gijón",
        "Hospitalet",
        "Vitoria",
        "Granada",
        "Elche",
        "Oviedo",
        "Badalona",
        "Terrassa",
        "Cartagena",
        "Jerez",
        "Sabadell",
        "Móstoles",
        "Pamplona",
        "Almería",
        "Alcalá",
        "Fuenlabrada"
    ];
    sort($provincias);
    foreach ($provincias as $p):
        $slug = strtolower(str_replace(' ', '-', $p));
        ?>
        <a href="/borme/provincia/<?= $slug ?>" class="card"
            style="text-align: center; padding: 1.5rem; text-decoration: none; font-weight: 700;">
            <?= $p ?>
        </a>
    <?php endforeach; ?>
</div>

<div style="margin-top: 4rem; background: var(--bg-light); padding: 3rem; border-radius: 4px; text-align: center;">
    <h3 style="margin-bottom: 1rem;">¿Buscas datos a nivel nacional?</h3>
    <p style="margin-bottom: 2rem; color: var(--text-secondary);">Usa nuestro buscador global para encontrar empresas en
        cualquier rincón de España.</p>
    <a href="/buscar" class="btn btn-primary">IR AL BUSCADOR GLOBAL</a>
</div>