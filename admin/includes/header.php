<?php
/** Admin layout header. Expects $admin; optional $adminTitle and $nav. */
$adminTitle = $adminTitle ?? 'Dashboard';
$nav = $nav ?? '';
$siteTitle = setting('site_title', 'My Vlog');
$navItems = [
    'dashboard' => ['admin/', 'grid', 'Dashboard'],
    'posts'     => ['admin/posts.php', 'file', 'All Posts'],
    'new'       => ['admin/post-edit.php', 'plus', 'Add New'],
    'settings'  => ['admin/settings.php', 'sliders', 'Settings'],
    'ads'       => ['admin/ads.php', 'money', 'Ads'],
    'account'   => ['admin/account.php', 'user', 'Account'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($adminTitle) ?> · <?= e($siteTitle) ?> Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="<?= e(asset('admin/assets/admin.css')) ?>">
</head>
<body>
<div class="admin">
    <aside class="sidebar">
        <input type="checkbox" id="side-toggle" class="side-toggle" aria-label="Toggle menu">
        <div class="sidebar-top">
            <a class="side-brand" href="<?= e(url('admin/')) ?>">
                <span class="brand-mark" aria-hidden="true"></span>
                <span><?= e($siteTitle) ?></span>
            </a>
            <label for="side-toggle" class="side-burger" aria-hidden="true"><span></span></label>
        </div>
        <nav class="side-nav" aria-label="Admin">
            <?php foreach ($navItems as $key => [$href, $ico, $label]): ?>
                <a href="<?= e(url($href)) ?>" class="<?= $nav === $key ? 'active' : '' ?>"><?= icon($ico) ?> <?= e($label) ?></a>
            <?php endforeach; ?>
            <a href="<?= e(url()) ?>" target="_blank" rel="noopener"><?= icon('external') ?> View Site</a>
        </nav>
        <div class="side-foot">
            <div class="side-user">
                <span class="avatar"><?= e(mb_strtoupper(mb_substr($admin['username'], 0, 1))) ?></span>
                <span>Signed in as<br><strong><?= e($admin['username']) ?></strong></span>
            </div>
            <form method="post" action="<?= e(url('admin/logout.php')) ?>">
                <?= csrf_field() ?>
                <button type="submit" class="side-logout"><?= icon('logout') ?> Log out</button>
            </form>
        </div>
    </aside>

    <main class="main">
        <?php foreach (flashes() as [$type, $message]): ?>
            <div class="alert alert-<?= e($type) ?>" role="status"><?= e($message) ?></div>
        <?php endforeach; ?>
