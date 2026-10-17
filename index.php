<?php
require_once __DIR__ . '/includes/bootstrap.php';

$siteTitle    = setting('site_title', 'My Vlog');
$categoryName = setting('category_name', 'Vlogs');
$perPage      = max(1, min(50, (int)setting('posts_per_page', '9')));
$q            = req_str($_GET, 'q');
$page         = max(1, (int)req_str($_GET, 'page'));
$isSearch     = $q !== '';
$cols         = 'id, title, slug, excerpt, featured_image, video_url, published_at, created_at';
$featured     = null;

if ($isSearch) {
    $like   = '%' . like_escape($q) . '%';
    $where  = "status = 'published' AND (title LIKE ? OR excerpt LIKE ? OR content LIKE ?)";
    $params = [$like, $like, $like];
    $stmt = db()->prepare("SELECT COUNT(*) FROM posts WHERE $where");
    $stmt->execute($params);
    $total  = (int)$stmt->fetchColumn();
    $pages  = max(1, (int)ceil($total / $perPage));
    $offset = ($page - 1) * $perPage;
} else {
    $where  = "status = 'published'";
    $params = [];
    $total  = (int)db()->query("SELECT COUNT(*) FROM posts WHERE $where")->fetchColumn();
    // The newest post is the featured hero on page 1, so the grid starts after it.
    $pages  = max(1, (int)ceil(max(0, $total - 1) / $perPage));
    $offset = 1 + ($page - 1) * $perPage;
    if ($page === 1 && $total > 0) {
        $featured = db()->query("SELECT $cols FROM posts WHERE $where ORDER BY published_at DESC, id DESC LIMIT 1")->fetch();
    }
}

if ($page > $pages) {
    require __DIR__ . '/404.php';
    exit;
}

$stmt = db()->prepare("SELECT $cols FROM posts WHERE $where ORDER BY published_at DESC, id DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$posts = $stmt->fetchAll();

$pageLink = function (int $n) use ($isSearch, $q): string {
    if ($isSearch) {
        return url('search') . '?q=' . rawurlencode($q) . ($n > 1 ? '&page=' . $n : '');
    }
    return $n > 1 ? url('page/' . $n) : url();
};

// SEO
$tagline = setting('site_tagline');
if ($isSearch) {
    $pageTitle = 'Search results for "' . $q . '" | ' . $siteTitle;
    $noindex   = true;
} elseif ($page > 1) {
    $pageTitle = $categoryName . ' – Page ' . $page . ' | ' . $siteTitle;
    $canonical = abs_url('page/' . $page);
} else {
    $pageTitle = $siteTitle . ($tagline !== '' ? ' – ' . $tagline : '');
    $canonical = abs_url();
}
$activeNav   = $isSearch ? '' : 'home';
$searchQuery = $q;

require __DIR__ . '/includes/header.php';
?>

<?php if (!$isSearch && $page === 1): ?>
<section class="intro">
    <div class="container">
        <span class="eyebrow"><?= e($categoryName) ?></span>
        <h1><?= e($tagline !== '' ? $tagline : $siteTitle) ?></h1>
        <?php if (setting('site_description') !== ''): ?>
            <p><?= e(setting('site_description')) ?></p>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<?php if ($featured): $p = $featured; $img = post_image($p); $isVlog = !empty($p['video_url']); ?>
<section class="container">
    <article class="featured">
        <a class="featured-media" href="<?= e(post_url($p)) ?>" tabindex="-1" aria-hidden="true">
            <?php if ($img): ?>
                <img src="<?= e($img) ?>" alt="">
            <?php else: ?>
                <span class="placeholder"><?= e(mb_strtoupper(mb_substr($p['title'], 0, 1))) ?></span>
            <?php endif; ?>
            <?php if ($isVlog): ?><span class="play-badge play-badge-lg"></span><?php endif; ?>
        </a>
        <div class="featured-body">
            <div class="meta">
                <span class="badge badge-solid">Latest</span>
                <span class="badge"><?= e($categoryName) ?></span>
                <time datetime="<?= e(post_date($p, 'Y-m-d')) ?>"><?= e(post_date($p)) ?></time>
            </div>
            <h2 class="featured-title"><a href="<?= e(post_url($p)) ?>"><?= e($p['title']) ?></a></h2>
            <?php if (!empty($p['excerpt'])): ?>
                <p class="featured-excerpt"><?= e($p['excerpt']) ?></p>
            <?php endif; ?>
            <a class="btn" href="<?= e(post_url($p)) ?>"><?= $isVlog ? 'Watch now' : 'Read article' ?> <?= icon('arrow', 16) ?></a>
        </div>
    </article>
    <?= ad_slot('ad_home') ?>
</section>
<?php endif; ?>

<?php if ($posts || !$featured): ?>
<section class="section" id="latest">
    <div class="container">
        <div class="section-head">
            <?php if ($isSearch): ?>
                <div>
                    <h1 class="section-title">Results for “<?= e($q) ?>”</h1>
                    <p class="section-sub"><?= $total ?> <?= $total === 1 ? 'post' : 'posts' ?> found<?= $pages > 1 ? ' · page ' . $page . ' of ' . $pages : '' ?></p>
                </div>
            <?php elseif ($page > 1): ?>
                <div>
                    <h1 class="section-title"><?= e($categoryName) ?></h1>
                    <p class="section-sub">Page <?= $page ?> of <?= $pages ?></p>
                </div>
            <?php else: ?>
                <div>
                    <h2 class="section-title">Latest <?= e($categoryName) ?></h2>
                    <p class="section-sub">Fresh vlogs and articles, newest first.</p>
                </div>
            <?php endif; ?>
        </div>

        <?= $featured ? '' : ad_slot('ad_home') ?>

        <?php if ($posts): ?>
            <div class="grid">
                <?php foreach ($posts as $p) { include __DIR__ . '/includes/card.php'; } ?>
            </div>
            <?= pagination($page, $pages, $pageLink) ?>
        <?php elseif ($isSearch): ?>
            <div class="empty">
                <h2>No posts found</h2>
                <p>Nothing matched “<?= e($q) ?>”. Try a different keyword.</p>
                <form class="search-big" action="<?= e(url('search')) ?>" method="get" role="search">
                    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search posts…" aria-label="Search posts">
                    <button class="btn" type="submit">Search</button>
                </form>
            </div>
        <?php else: ?>
            <div class="empty">
                <h2>No posts yet</h2>
                <p>New <?= e(mb_strtolower($categoryName)) ?> are coming soon. Check back later!</p>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php else: ?>
<div class="spacer"></div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
