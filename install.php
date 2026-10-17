<?php
/**
 * One-time installer: creates the database tables and the first admin account.
 * It locks itself once an admin exists. Delete this file after installing.
 */
require_once __DIR__ . '/includes/bootstrap.php';
start_session();

$fatal = '';
$installed = false;
$done = false;
$errors = [];
$form = ['site_title' => 'JobFost', 'category_name' => 'Vlogs', 'username' => 'admin'];

try {
    try {
        $server = new PDO('mysql:host=' . DB_HOST . ';charset=utf8mb4', DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $server->exec('CREATE DATABASE IF NOT EXISTS `' . str_replace('`', '``', DB_NAME) . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    } catch (PDOException $e) {
        // No CREATE privilege (common on shared hosting) — the database may already exist.
    }
    $schema = preg_replace('/^--.*$/m', '', (string)file_get_contents(__DIR__ . '/includes/schema.sql'));
    foreach (preg_split('/;\s*(?:\r?\n|$)/', $schema) as $sql) {
        if (trim($sql) !== '') {
            db()->exec($sql);
        }
    }
    $installed = (int)db()->query('SELECT COUNT(*) FROM admins')->fetchColumn() > 0;
} catch (PDOException $e) {
    $fatal = $e->getMessage();
}

if (!$fatal && !$installed && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $form = [
        'site_title'    => req_str($_POST, 'site_title'),
        'category_name' => req_str($_POST, 'category_name'),
        'username'      => req_str($_POST, 'username'),
    ];
    $password = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';
    $confirm  = isset($_POST['confirm']) && is_string($_POST['confirm']) ? $_POST['confirm'] : '';

    if ($form['site_title'] === '' || $form['category_name'] === '') {
        $errors[] = 'Site title and category name are required.';
    }
    if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $form['username'])) {
        $errors[] = 'Username must be 3–50 characters: letters, numbers, dot, dash or underscore.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$errors) {
        db()->prepare('INSERT INTO admins (username, password_hash) VALUES (?, ?)')
            ->execute([$form['username'], password_hash($password, PASSWORD_DEFAULT)]);
        save_setting('site_title', mb_substr($form['site_title'], 0, 100));
        save_setting('category_name', mb_substr($form['category_name'], 0, 60));

        if ((int)db()->query('SELECT COUNT(*) FROM posts')->fetchColumn() === 0) {
            $content = '<p>Welcome to your new website! This is a sample post — edit or delete it from the admin dashboard.</p>'
                . '<h2>What you can do</h2>'
                . '<ul><li>Write articles with headings, lists, quotes, links and images.</li>'
                . '<li>Publish vlogs by adding a YouTube or Vimeo link to any post.</li>'
                . '<li>Save posts as drafts and preview them before publishing.</li></ul>'
                . '<blockquote>Keep it simple, publish often.</blockquote>'
                . '<p>Rename the category, upload a logo and update SEO details from <strong>Settings</strong>.</p>';
            db()->prepare(
                "INSERT INTO posts (title, slug, excerpt, content, status, published_at) VALUES (?, ?, ?, ?, 'published', NOW())"
            )->execute(['Welcome to your new vlog', 'welcome-to-your-new-vlog', make_excerpt($content), $content]);
        }
        $done = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Install</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="<?= e(asset('admin/assets/admin.css')) ?>">
</head>
<body class="auth-page">
<div class="auth-card auth-card-wide">
    <div class="auth-brand"><span class="brand-mark" aria-hidden="true"></span><span>Website setup</span></div>

    <?php if ($fatal): ?>
        <h1>Database connection failed</h1>
        <div class="alert alert-error"><?= e($fatal) ?></div>
        <ol>
            <li>Make sure MySQL is running.</li>
            <li>Check <code>DB_HOST</code>, <code>DB_NAME</code>, <code>DB_USER</code> and <code>DB_PASS</code> in <code>config.php</code>.</li>
            <li>On shared hosting, create the database in your control panel first.</li>
        </ol>
        <a class="btn btn-block" href="<?= e(url('install.php')) ?>">Try again</a>

    <?php elseif ($done || $installed): ?>
        <h1><?= $done ? 'All set! 🎉' : 'Already installed' ?></h1>
        <p class="muted"><?= $done ? 'Your website and admin account are ready.' : 'An admin account already exists, so the installer is locked.' ?></p>
        <div class="alert alert-error">For security, delete <code>install.php</code> from the server now.</div>
        <div class="actions">
            <a class="btn" href="<?= e(url('admin/login.php')) ?>">Go to admin login</a>
            <a class="btn btn-light" href="<?= e(url()) ?>">View website</a>
        </div>

    <?php else: ?>
        <h1>Install your website</h1>
        <p class="muted">Tables were created in the <code><?= e(DB_NAME) ?></code> database. Now set up your site and admin account.</p>
        <?php if ($errors): ?>
            <div class="alert alert-error"><?php foreach ($errors as $error): ?><div><?= e($error) ?></div><?php endforeach; ?></div>
        <?php endif; ?>
        <form method="post">
            <?= csrf_field() ?>
            <div class="form-row">
                <div class="field">
                    <label for="site_title">Site title</label>
                    <input type="text" id="site_title" name="site_title" value="<?= e($form['site_title']) ?>" required maxlength="100">
                </div>
                <div class="field">
                    <label for="category_name">Category name</label>
                    <input type="text" id="category_name" name="category_name" value="<?= e($form['category_name']) ?>" required maxlength="60">
                </div>
            </div>
            <hr>
            <div class="field">
                <label for="username">Admin username</label>
                <input type="text" id="username" name="username" value="<?= e($form['username']) ?>" required maxlength="50" autocomplete="username">
            </div>
            <div class="form-row">
                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password">
                </div>
                <div class="field">
                    <label for="confirm">Confirm password</label>
                    <input type="password" id="confirm" name="confirm" required minlength="8" autocomplete="new-password">
                </div>
            </div>
            <button type="submit" class="btn btn-block">Install website</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
