<?php
$video ??= null;
$videoLinks ??= [];
$relatedVideos ??= [];
$linkedReviews ??= [];
?>
<section>
    <?php if (empty($video)): ?>
        <h1><?= h(text('video.notFound', 'Video not found')) ?></h1>
        <p><?= h(text('video.notFoundBody', 'We could not find details for this video.')) ?></p>
    <?php else: ?>
        <?php
        $rawEmbedSrc = $video['platform'] === 'youtube'
            ? 'https://www.youtube.com/embed/' . rawurlencode($video['platform_ref']) . '?rel=0'
            : ($video['primary_url'] ?? null);
        $embedSrc = sanitizeUrl($rawEmbedSrc);
        ?>
        <div class="video-hero">
            <div class="video-frame">
                <?php if ($embedSrc): ?>
                    <iframe src="<?= h($embedSrc) ?>" title="<?= h($video['title']) ?>" loading="lazy" allowfullscreen></iframe>
                <?php else: ?>
                    <div class="embed-fallback">
                        <?= h(text('video.embedUnavailable', 'We cannot display this video because the link is unsafe or invalid.')) ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="hero-meta">
                <p class="badge"><?= strtoupper($video['platform']) ?> · <?= formatDate($video['publish_date']) ?></p>
                <h1><?= h($video['title']) ?></h1>
                <p><?= h($video['description']) ?></p>
                <div class="tag-list">
                    <?php foreach (array_filter(array_map('trim', explode(',', (string)$video['tags']))) as $tag): ?>
                        <span>#<?= h($tag) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php if (!empty($videoLinks)): ?>
                    <div class="links-bar">
                        <?php foreach ($videoLinks as $link): ?>
                            <?php $safeLink = sanitizeUrl($link['url'] ?? null); ?>
                            <?php if ($safeLink): ?>
                                <a href="<?= h($safeLink) ?>" target="_blank" rel="noopener">
                                    <?= h($link['label'] ?: text('video.link', 'Link')) ?>
                                </a>
                            <?php else: ?>
                                <span class="link-fallback"><?= h($link['label'] ?: text('video.link', 'Link')) ?></span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</section>

<?php if (!empty($relatedVideos)): ?>
    <section>
        <h2 class="section-title">
            <span><?= h(text('video.related', 'Related videos')) ?></span>
            <a href="<?= h(buildUrl('videos')) ?>"><?= h(text('video.allVideos', 'All videos')) ?></a>
        </h2>
        <div class="video-grid">
            <?php foreach ($relatedVideos as $item): ?>
                <article class="video-card">
                    <p class="badge"><?= strtoupper($item['platform']) ?> · <?= formatDate($item['publish_date']) ?></p>
                    <h3><a href="<?= h(buildUrl('video', ['slug' => $item['slug']])) ?>"><?= h($item['title']) ?></a></h3>
                    <p><?= h($item['description']) ?></p>
                    <div class="tag-list">
                        <?php foreach (array_filter(array_map('trim', explode(',', (string)$item['tags']))) as $tag): ?>
                            <span>#<?= h($tag) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="card-footer">
                        <a href="<?= h(buildUrl('video', ['slug' => $item['slug']])) ?>">Details</a>
                        <?php $relatedUrl = sanitizeUrl($item['primary_url'] ?? null); ?>
                        <?php if ($relatedUrl): ?>
                            <a href="<?= h($relatedUrl) ?>" target="_blank" rel="noopener">External</a>
                        <?php else: ?>
                            <span class="link-fallback">External link unavailable</span>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<?php if (!empty($linkedReviews)): ?>
    <section>
        <h2 class="section-title">
            <span><?= h(text('video.reviews', 'Reviews mentioning this video')) ?></span>
            <a href="<?= h(buildUrl('reviews')) ?>"><?= h(text('video.allReviews', 'All reviews')) ?></a>
        </h2>
        <div class="video-grid">
            <?php foreach ($linkedReviews as $item): ?>
                <article class="review-card">
                    <p class="badge">Review · <?= formatDate($item['publish_date']) ?></p>
                    <h3><a href="<?= h(buildUrl('review', ['slug' => $item['slug']])) ?>"><?= h($item['title']) ?></a></h3>
                    <p><?= h($item['excerpt']) ?></p>
                    <a href="<?= h(buildUrl('review', ['slug' => $item['slug']])) ?>">Read</a>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>
