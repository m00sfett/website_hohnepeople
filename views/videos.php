<section>
    <h1>Videos</h1>
    <div class="video-grid">
        <?php foreach ($videos as $video): ?>
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
                    <span><?= formatDate($video['publish_date']) ?></span>
                    <a href="<?= h(buildUrl('video', ['slug' => $video['slug']])) ?>">Details</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php
            $base = buildUrl('videos');
            $join = str_contains($base, '?') ? '&' : '?';
            for ($i = 1; $i <= $totalPages; $i++):
                $href = $base . ($i > 1 ? $join . 'p=' . $i : '');
            ?>
                <a class="<?= $i === $pageNumber ? 'active' : '' ?>" href="<?= h($href) ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</section>
