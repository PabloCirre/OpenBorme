<?php
// static.php - Standardized template for legal and institutional content
?>
<div class="container static-page-header">
    <nav class="breadcrumbs" style="margin-bottom: var(--space-6);">
        <a href="/">Inicio</a> /
        <span><?= htmlspecialchars(str_replace(' | OpenBorme', '', $page_title)) ?></span>
    </nav>

    <article class="static-article">
        <h1 class="hero-title" style="margin-bottom: var(--space-6);">
            <?= $page_title ?>
        </h1>

        <div class="static-body">
            <?= $page_content ?>
        </div>
    </article>
</div>