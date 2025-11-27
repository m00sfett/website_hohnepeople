<section>
    <h1><?= h(text('links.title', 'Links')) ?></h1>
    <?php if (empty($socialLinks)): ?>
        <p><?= h(text('links.empty', 'No links available right now.')) ?></p>
    <?php else: ?>
        <div class="links-bar">
            <?php foreach ($socialLinks as $link): ?>
                <a href="<?= h($link['url']) ?>" target="_blank" rel="noopener">
                    <span aria-hidden="true"><?= renderIcon($link['icon_key']) ?></span>
                    <span><?= h($link['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
