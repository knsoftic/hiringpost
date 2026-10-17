<?php
require __DIR__ . '/includes/auth.php';
$admin = require_login();

$stats = db()->query(
    "SELECT COUNT(*) AS total,
            COALESCE(SUM(status = 'published'), 0) AS published,
            COALESCE(SUM(status = 'draft'), 0) AS drafts,
            COALESCE(SUM(video_url IS NOT NULL AND video_url <> ''), 0) AS vlogs
     FROM posts"
)->fetch();
$recent = db()->query(
    'SELECT id, title, slug, status, featured_image, video_url, published_at, created_at, updated_at
     FROM posts ORDER BY updated_at DESC LIMIT 6'
)->fetchAll();

$adminTitle = 'Dashboard';
$nav = 'dashboard';
require __DIR__ . '/includes/header.php';
?>
<div class="topbar">
    <div>
        <h1>Welcome back, <?= e($admin['username']) ?> 👋</h1>
        <p class="muted">Here’s what’s happening on your site.</p>
    </div>
    <a class="btn" href="<?= e(url('admin/post-edit.php')) ?>"><?= icon('plus') ?> New Post</a>
</div>

<div class="stats">
    <div class="card stat"><span class="stat-label">Total posts</span><span class="stat-value"><?= (int)$stats['total'] ?></span></div>
    <div class="card stat"><span class="stat-label">Published</span><span class="stat-value text-green"><?= (int)$stats['published'] ?></span></div>
    <div class="card stat"><span class="stat-label">Drafts</span><span class="stat-value text-amber"><?= (int)$stats['drafts'] ?></span></div>
    <div class="card stat"><span class="stat-label">With video</span><span class="stat-value text-accent"><?= (int)$stats['vlogs'] ?></span></div>
</div>

<div class="card category-card">
    <div>
        <span class="stat-label">Site category</span>
        <h2><?= e(setting('category_name', 'Vlogs')) ?></h2>
        <p class="muted">Every post is published under this one category. You can rename it anytime.</p>
    </div>
    <a class="btn btn-light" href="<?= e(url('admin/settings.php')) ?>"><?= icon('edit', 16) ?> Rename</a>
</div>

<div class="card card-flush">
    <div class="card-head">
        <h2>Recently updated</h2>
        <a class="link" href="<?= e(url('admin/posts.php')) ?>">View all</a>
    </div>
    <?php if ($recent): ?>
        <?php $rows = $recent; require __DIR__ . '/includes/post-rows.php'; ?>
    <?php else: ?>
        <div class="empty-box">
            <p>You haven’t written anything yet.</p>
            <a class="btn" href="<?= e(url('admin/post-edit.php')) ?>"><?= icon('plus') ?> Write your first post</a>
        </div>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
