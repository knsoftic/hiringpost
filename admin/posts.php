<?php
require __DIR__ . '/includes/auth.php';
$admin = require_login();

$perPage = 15;
$status  = in_array(req_str($_GET, 'status'), ['published', 'draft'], true) ? req_str($_GET, 'status') : '';
$q       = req_str($_GET, 'q');
$page    = max(1, (int)req_str($_GET, 'page'));

$where = [];
$params = [];
if ($status !== '') {
    $where[] = 'status = ?';
    $params[] = $status;
}
if ($q !== '') {
    $where[] = '(title LIKE ? OR slug LIKE ?)';
    $like = '%' . like_escape($q) . '%';
    array_push($params, $like, $like);
}
$sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = db()->prepare("SELECT COUNT(*) FROM posts $sqlWhere");
$stmt->execute($params);
$total  = (int)$stmt->fetchColumn();
$pages  = max(1, (int)ceil($total / $perPage));
$page   = min($page, $pages);
$offset = ($page - 1) * $perPage;

$stmt = db()->prepare(
    "SELECT id, title, slug, status, featured_image, video_url, published_at, created_at, updated_at
     FROM posts $sqlWhere ORDER BY created_at DESC, id DESC LIMIT $perPage OFFSET $offset"
);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$counts = db()->query("SELECT status, COUNT(*) FROM posts GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
$tabs = [
    ''          => ['All', array_sum($counts)],
    'published' => ['Published', $counts['published'] ?? 0],
    'draft'     => ['Drafts', $counts['draft'] ?? 0],
];

$listUrl = function (array $changes) use ($status, $q): string {
    $query = array_filter(array_merge(['status' => $status, 'q' => $q], $changes), 'strlen');
    return url('admin/posts.php') . ($query ? '?' . http_build_query($query) : '');
};

$adminTitle = 'All Posts';
$nav = 'posts';
require __DIR__ . '/includes/header.php';
?>
<div class="topbar">
    <div>
        <h1>Posts</h1>
        <p class="muted">All vlogs and articles in <strong><?= e(setting('category_name', 'Vlogs')) ?></strong>.</p>
    </div>
    <a class="btn" href="<?= e(url('admin/post-edit.php')) ?>"><?= icon('plus') ?> New Post</a>
</div>

<div class="toolbar">
    <div class="tabs">
        <?php foreach ($tabs as $key => [$label, $count]): ?>
            <a href="<?= e($listUrl(['status' => $key, 'page' => ''])) ?>" class="tab <?= $status === $key ? 'active' : '' ?>">
                <?= e($label) ?> <span class="tab-count"><?= (int)$count ?></span>
            </a>
        <?php endforeach; ?>
    </div>
    <form class="search-inline" method="get">
        <?php if ($status !== ''): ?><input type="hidden" name="status" value="<?= e($status) ?>"><?php endif; ?>
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search posts…" aria-label="Search posts">
        <button type="submit" class="btn btn-light"><?= icon('search', 16) ?></button>
    </form>
</div>

<div class="card card-flush">
    <?php if ($rows): ?>
        <?php require __DIR__ . '/includes/post-rows.php'; ?>
    <?php else: ?>
        <div class="empty-box">
            <p><?= $q !== '' ? 'No posts match “' . e($q) . '”.' : 'No posts here yet.' ?></p>
            <a class="btn" href="<?= e(url('admin/post-edit.php')) ?>"><?= icon('plus') ?> New Post</a>
        </div>
    <?php endif; ?>
</div>

<?= pagination($page, $pages, function (int $n) use ($listUrl): string {
    return $listUrl(['page' => $n > 1 ? (string)$n : '']);
}) ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
