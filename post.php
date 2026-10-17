<?php
require_once __DIR__ . '/includes/bootstrap.php';

$stmt = db()->prepare('SELECT * FROM posts WHERE slug = ? LIMIT 1');
$stmt->execute([req_str($_GET, 'slug')]);
$post = $stmt->fetch() ?: null;

// Drafts are only visible to a logged-in admin using the preview link
$isPreview = false;
if ($post && $post['status'] !== 'published') {
    $isPreview = isset($_GET['preview']);
    if ($isPreview) {
        start_session();
        $isPreview = !empty($_SESSION['admin_id']);
    }
    if (!$isPreview) {
        $post = null;
    }
}

if (!$post) {
    require __DIR__ . '/404.php';
    exit;
}

$siteTitle    = setting('site_title', 'My Vlog');
$categoryName = setting('category_name', 'Vlogs');
$image        = post_image($post);
$video        = $post['video_url'] ? video_html($post['video_url'], $post['title']) : '';

$more = db()->prepare(
    "SELECT id, title, slug, excerpt, featured_image, video_url, published_at, created_at
     FROM posts WHERE status = 'published' AND id <> ? ORDER BY published_at DESC, id DESC LIMIT 3"
);
$more->execute([$post['id']]);
$morePosts = $more->fetchAll();

// SEO
$pageTitle       = $post['meta_title'] ?: $post['title'] . ' | ' . $siteTitle;
$metaDescription = $post['meta_description'] ?: (string)$post['excerpt'];
$canonical       = abs_url($post['slug']);
$ogType          = 'article';
$noindex         = $isPreview;
$hideAds         = $isPreview;
if ($image) {
    $ogImage = abs_url($image);
}
$metaKeywords = $categoryName;
$globalKw = setting('meta_keywords');
if ($globalKw !== '') {
    $metaKeywords .= ', ' . $globalKw;
}
$activeNav = 'category';

require __DIR__ . '/includes/header.php';
?>

<?php if ($isPreview): ?>
    <div class="preview-bar">Preview mode — this post is a <strong>draft</strong> and is not visible to visitors.</div>
<?php endif; ?>

<article class="post">
    <header class="post-header container">
        <div class="meta">
            <a class="badge" href="<?= e(url('#latest')) ?>"><?= e($categoryName) ?></a>
            <time datetime="<?= e(post_date($post, 'Y-m-d')) ?>"><?= e(post_date($post, 'F j, Y')) ?></time>
            <?php if ($video): ?>
                <span class="tag-video"><?= icon('video', 14) ?> Video</span>
            <?php else: ?>
                <span class="tag-video"><?= icon('clock', 14) ?> <?= reading_time($post['content']) ?> min read</span>
            <?php endif; ?>
        </div>
        <h1 class="post-title"><?= e($post['title']) ?></h1>
        <?php if (!empty($post['excerpt']) && $post['excerpt'] !== make_excerpt($post['content'])): /* skip auto-generated excerpts */ ?>
            <p class="post-lead"><?= e($post['excerpt']) ?></p>
        <?php endif; ?>
    </header>

    <div class="container">
        <?php if ($video): ?>
            <div class="post-media post-video"><?= $video ?></div>
        <?php elseif (!empty($post['featured_image'])): ?>
            <figure class="post-media"><img src="<?= e($image) ?>" alt="<?= e($post['title']) ?>"></figure>
        <?php endif; ?>

        <?php if (trim($post['content']) !== ''): ?>
            <div class="post-content">
                <?= $post['content'] /* sanitized on save */ ?>
            </div>
        <?php endif; ?>

        <?= $isPreview ? '' : ad_slot('ad_post') ?>

        <div class="post-footer">
            <a class="back-link" href="<?= e(url()) ?>"><?= icon('back', 16) ?> Back to <?= e($categoryName) ?></a>
            <span>Updated <?= e(date('M j, Y', strtotime($post['updated_at']))) ?></span>
        </div>
    </div>
</article>

<?php if ($morePosts): ?>
<section class="related">
    <div class="container">
        <div class="section-head">
            <h2 class="section-title">More <?= e($categoryName) ?></h2>
            <a class="link-arrow" href="<?= e(url('#latest')) ?>">View all <?= icon('arrow', 16) ?></a>
        </div>
        <div class="grid">
            <?php foreach ($morePosts as $p) { include __DIR__ . '/includes/card.php'; } ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php
$ld = [
    '@context'         => 'https://schema.org',
    '@type'            => $video ? 'VideoObject' : 'BlogPosting',
    'headline'         => $post['title'],
    'name'             => $post['title'],
    'description'      => $metaDescription,
    'datePublished'    => post_date($post, 'c'),
    'dateModified'     => date('c', strtotime($post['updated_at'])),
    'mainEntityOfPage' => $canonical,
    'publisher'        => ['@type' => 'Organization', 'name' => $siteTitle],
];
if ($video) {
    $ld['uploadDate'] = $ld['datePublished'];
    $ld['embedUrl'] = video_embed_src($post['video_url']) ?: $post['video_url'];
    unset($ld['headline']);
}
if ($image) {
    $ld[$video ? 'thumbnailUrl' : 'image'] = abs_url($image);
}
?>
<script type="application/ld+json"><?= json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>

<?php require __DIR__ . '/includes/footer.php'; ?>
