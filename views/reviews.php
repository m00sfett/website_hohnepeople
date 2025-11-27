<?php $reviews ??= []; ?>
<section>
    <h1><?= h(text('reviews.title', 'Reviews')) ?></h1>
    <?php if (empty($reviews)): ?>
        <p><?= h(text('reviews.empty', 'No reviews available yet.')) ?></p>
    <?php else: ?>
        <div class="video-grid">
            <?php foreach ($reviews as $review): ?>
                <article class="review-card">
                    <p class="badge">Review · <?= formatDate($review['publish_date']) ?></p>
                    <h3><a href="<?= h(buildUrl('review', ['slug' => $review['slug']])) ?>"><?= h($review['title']) ?></a></h3>
                    <p><?= h($review['excerpt']) ?></p>
                    <a href="<?= h(buildUrl('review', ['slug' => $review['slug']])) ?>">Read</a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
