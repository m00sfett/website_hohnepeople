<?php
$review ??= null;
$reviewVideos ??= [];
?>
<section>
    <?php if (empty($review)): ?>
        <h1><?= h(text('review.notFound', 'Review not found')) ?></h1>
        <p><?= h(text('review.notFoundBody', 'We could not find this review.')) ?></p>
    <?php else: ?>
        <p class="badge">Review · <?= formatDate($review['publish_date']) ?></p>
        <h1><?= h($review['title']) ?></h1>
        <?php if (!empty($review['excerpt'])): ?>
            <p><?= h($review['excerpt']) ?></p>
        <?php endif; ?>
        <div class="rich-text">
            <?= safeHtml($review['body'] ?? '') ?>
        </div>
    <?php endif; ?>
</section>

<?php if (!empty($reviewVideos)): ?>
    <section>
        <h2 class="section-title">
            <span><?= h(text('review.related', 'Featured videos')) ?></span>
            <a href="<?= h(buildUrl('videos')) ?>"><?= h(text('review.allVideos', 'All videos')) ?></a>
        </h2>
        <div class="video-grid">
            <?php foreach ($reviewVideos as $video): ?>
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
                        <a href="<?= h(buildUrl('video', ['slug' => $video['slug']])) ?>">Details</a>
                        <?php $reviewVideoUrl = sanitizeUrl($video['primary_url'] ?? null); ?>
                        <?php if ($reviewVideoUrl): ?>
                            <a href="<?= h($reviewVideoUrl) ?>" target="_blank" rel="noopener">External</a>
                        <?php else: ?>
                            <span class="link-fallback">External link unavailable</span>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>
