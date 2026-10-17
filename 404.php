<?php
require_once __DIR__ . '/includes/bootstrap.php';

http_response_code(404);
$pageTitle = 'Page not found | ' . setting('site_title', 'My Vlog');
$noindex = true;

require __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="container">
        <div class="empty notfound">
            <div class="notfound-code">404</div>
            <h1>Page not found</h1>
            <p>The page you are looking for doesn’t exist or has been moved.</p>
            <form class="search-big" action="<?= e(url('search')) ?>" method="get" role="search">
                <input type="search" name="q" placeholder="Search posts…" aria-label="Search posts">
                <button class="btn" type="submit">Search</button>
            </form>
            <p><a class="link-arrow" href="<?= e(url()) ?>"><?= icon('back', 16) ?> Back to homepage</a></p>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
