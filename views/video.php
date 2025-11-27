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
        $embedSrc = $video['platform'] === 'youtube'
            ? 'https://www.youtube.com/embed/' . rawurlencode($video['platform_ref']) . '?rel=0'
            : $video['primary_url'];
        ?>
        <div class="video-hero">
            <div class="video-frame">
                <iframe src="<?= h($embedSrc) ?>" title="<?= h($video['title']) ?>" loading="lazy" allowfullscreen></iframe>
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
                            <a href="<?= h($link['url']) ?>" target="_blank" rel="noopener">
                                <?= h($link['label'] ?: text('video.link', 'Link')) ?>
                            </a>
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
                        <a href="<?= h($item['primary_url']) ?>" target="_blank" rel="noopener">External</a>
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
