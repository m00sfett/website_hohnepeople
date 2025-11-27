    </main>
    <footer class="site-footer">
        <p><?= h(text('site.tagline', 'HohnePeople blends motion, pattern, and club energy.')) ?></p>
        <div class="links-bar" aria-label="Link hub">
            <?php foreach ($socialLinks as $link): ?>
                <a href="<?= h($link['url']) ?>" target="_blank" rel="noopener" title="<?= h($link['label']) ?>">
                    <span aria-hidden="true"><?= renderIcon($link['icon_key']) ?></span>
                    <span><?= h($link['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="social-footer">
            <span><?= h(text('home.reviewsTeaser', 'Voices about HohnePeople')) ?></span>
        </div>
        <nav aria-label="Footer">
            <?php foreach ($footerNav as $item): ?>
                <?php
                $href = $item['path'];
                if ($item['slug'] === 'imprint') {
                    $href = buildUrl('page', ['slug' => 'imprint']);
                } elseif ($item['slug'] === 'privacy') {
                    $href = buildUrl('page', ['slug' => 'privacy']);
                }
                ?>
                <a href="<?= h($href) ?>"><?= h($item['label']) ?></a>
            <?php endforeach; ?>
            <a href="<?= h(buildUrl('sitemap')) ?>">Sitemap</a>
        </nav>
    </footer>
</div>
</body>
</html>
