<?php
// static.php - Standardized template for legal and institutional content
?>
<div class="container" style="padding: var(--space-7) var(--space-5);">
    <nav class="breadcrumbs" style="margin-bottom: var(--space-6);">
        <a href="/">Inicio</a> /
        <span><?= htmlspecialchars(str_replace(' | OpenBorme', '', $page_title)) ?></span>
    </nav>

    <article style="max-width: 840px; margin: 0 auto;">
        <h1 style="margin-bottom: var(--space-6); color: var(--text-primary);">
            <?= $page_title ?>
        </h1>

        <div class="static-body" style="color: var(--text-secondary); font-size: 17px; line-height: 1.8;">
            <?= $page_content ?>
        </div>
    </article>
</div>

<style>
    .static-body h2 {
        margin: var(--space-6) 0 var(--space-3);
        color: var(--text-primary);
        font-size: 24px;
    }

    .static-body h3 {
        margin: var(--space-5) 0 var(--space-2);
        color: var(--text-primary);
        font-size: 19px;
    }

    .static-body p {
        margin-bottom: var(--space-4);
    }

    .static-body ul {
        margin: 0 0 var(--space-5) var(--space-5);
    }

    .static-body li {
        margin-bottom: var(--space-2);
    }

    .static-body strong {
        color: var(--text-primary);
    }
</style>