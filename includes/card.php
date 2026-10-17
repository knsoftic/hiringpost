<?php
/** Post card used in listings. Expects $p (post row). */
$cardImage = post_image($p);
$cardIsVlog = !empty($p['video_url']);
?>
<article class="card">
    <a class="card-media" href="<?= e(post_url($p)) ?>" tabindex="-1" aria-hidden="true">
        <?php if ($cardImage): ?>
            <img src="<?= e($cardImage) ?>" alt="" loading="lazy">
        <?php else: ?>
            <span class="placeholder"><?= e(mb_strtoupper(mb_substr($p['title'], 0, 1))) ?></span>
        <?php endif; ?>
        <?php if ($cardIsVlog): ?><span class="play-badge"></span><?php endif; ?>
    </a>
    <div class="card-body">
        <div class="meta">
            <span class="badge"><?= e(setting('category_name', 'Vlogs')) ?></span>
            <time datetime="<?= e(post_date($p, 'Y-m-d')) ?>"><?= e(post_date($p)) ?></time>
            <?php if ($cardIsVlog): ?><span class="tag-video"><?= icon('video', 14) ?> Video</span><?php endif; ?>
        </div>
        <h3 class="card-title"><a href="<?= e(post_url($p)) ?>"><?= e($p['title']) ?></a></h3>
        <?php if (!empty($p['excerpt'])): ?>
            <p class="card-excerpt"><?= e($p['excerpt']) ?></p>
        <?php endif; ?>
    </div>
</article>
