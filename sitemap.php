<?php
require_once __DIR__ . '/includes/bootstrap.php';

$posts = db()->query("SELECT slug, updated_at FROM posts WHERE status = 'published' ORDER BY published_at DESC")->fetchAll();

header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url><loc><?= e(abs_url()) ?></loc><changefreq>daily</changefreq><priority>1.0</priority></url>
<?php foreach ($posts as $p): ?>
    <url><loc><?= e(abs_url($p['slug'])) ?></loc><lastmod><?= date('Y-m-d', strtotime($p['updated_at'])) ?></lastmod></url>
<?php endforeach; ?>
</urlset>
