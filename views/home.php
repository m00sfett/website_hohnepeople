<section>
    <h2 class="section-title">
        <span><?= h(text('home.latest', 'Latest Video')) ?></span>
        <a href="<?= h(buildUrl('videos')) ?>">All videos</a>
    </h2>
    <?php if (!empty($latest)): ?>
        <?php
        $rawEmbedSrc = $latest['platform'] === 'youtube'
            ? 'https://www.youtube.com/embed/' . rawurlencode($latest['platform_ref']) . '?rel=0'
            : ($latest['primary_url'] ?? null);
        $embedSrc = sanitizeUrl($rawEmbedSrc);
        ?>
        <article class="hero-card">
            <div>
                <?php if ($embedSrc): ?>
                    <iframe src="<?= h($embedSrc) ?>" title="<?= h($latest['title']) ?>" loading="lazy" allowfullscreen></iframe>
                <?php else: ?>
                    <div class="embed-fallback">
                        <?= h(text('video.embedUnavailable', 'We cannot display this video because the link is unsafe or invalid.')) ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="hero-meta">
                <p class="badge"><?= strtoupper($latest['platform']) ?> · <?= formatDate($latest['publish_date']) ?></p>
                <h2><?= h($latest['title']) ?></h2>
                <p><?= h($latest['description']) ?></p>
                <div class="tag-list">
                    <?php foreach (array_filter(array_map('trim', explode(',', (string)$latest['tags']))) as $tag): ?>
                        <span>#<?= h($tag) ?></span>
                    <?php endforeach; ?>
                </div>
                <div class="links-bar">
                    <a href="<?= h(buildUrl('video', ['slug' => $latest['slug']])) ?>">Details</a>
                </div>
            </div>
        </article>
    <?php endif; ?>
</section>

<section>
    <h2 class="section-title">
        <span><?= h(text('home.moreVideos', 'More videos')) ?></span>
        <a href="<?= h(buildUrl('videos')) ?>">Browse</a>
    </h2>
    <div class="video-grid">
        <?php
        $latestSlug = $latest['slug'] ?? null;
        foreach ($recentVideos as $video):
            if ($latestSlug && $video['slug'] === $latestSlug) {
                continue;
            }
        ?>
            <article class="video-card">
                <p class="badge"><?= strtoupper($video['platform']) ?> · <?= formatDate($video['publish_date']) ?></p>
                <h3><a href="<?= h(buildUrl('video', ['slug' => $video['slug']])) ?>"><?= h($video['title']) ?></a></h3>
                <p><?= h($video['description']) ?></p>
                <div class="tag-list">
                    <?php foreach (array_filter(array_map('trim', explode(',', (string)$video['tags']))) as $tag): ?>
                        <span>#<?= h($tag) ?></span>
                    <?php endforeach; ?>
                </div>
                <div class="card-footer">
                    <a href="<?= h(buildUrl('video', ['slug' => $video['slug']])) ?>">Open</a>
                    <?php $externalUrl = sanitizeUrl($video['primary_url'] ?? null); ?>
                    <?php if ($externalUrl): ?>
                        <a href="<?= h($externalUrl) ?>" target="_blank" rel="noopener">External</a>
                    <?php else: ?>
                        <span class="link-fallback">External link unavailable</span>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section>
    <h2 class="section-title">
        <span><?= h(text('home.reviewsTeaser', 'Fresh voices')) ?></span>
        <a href="<?= h(buildUrl('reviews')) ?>">All reviews</a>
    </h2>
    <div class="video-grid">
        <?php foreach ($recentReviews as $review): ?>
            <article class="review-card">
                <p class="badge">Review · <?= formatDate($review['publish_date']) ?></p>
                <h3><a href="<?= h(buildUrl('review', ['slug' => $review['slug']])) ?>"><?= h($review['title']) ?></a></h3>
                <p><?= h($review['excerpt']) ?></p>
                <a href="<?= h(buildUrl('review', ['slug' => $review['slug']])) ?>">Read</a>
            </article>
        <?php endforeach; ?>
    </div>
</section>
