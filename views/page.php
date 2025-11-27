<?php $page ??= null; ?>
<section>
    <?php if (empty($page)): ?>
        <h1><?= h(text('page.notFound', 'Page not found')) ?></h1>
        <p><?= h(text('page.notFoundBody', 'The requested page could not be located.')) ?></p>
    <?php else: ?>
        <h1><?= h($page['title'] ?? text('page.untitled', 'Untitled')) ?></h1>
        <div class="rich-text">
            <?= safeHtml($page['body'] ?? '') ?>
        </div>
    <?php endif; ?>
</section>
