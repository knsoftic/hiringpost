<?php
require __DIR__ . '/includes/auth.php';

if (current_admin()) {
    redirect('admin/');
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = req_str($_POST, 'username');
    $password = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';

    $stmt = db()->prepare('SELECT * FROM admins WHERE username = ?');
    $stmt->execute([$username]);
    $row = $stmt->fetch();

    if ($row && $row['locked_until'] && strtotime($row['locked_until']) > time()) {
        $minutes = (int)ceil((strtotime($row['locked_until']) - time()) / 60);
        $error = "Too many failed attempts. Please try again in $minutes minute(s).";
    } elseif ($row && password_verify($password, $row['password_hash'])) {
        $sql = 'UPDATE admins SET failed_attempts = 0, locked_until = NULL, last_login = NOW()';
        $params = [];
        if (password_needs_rehash($row['password_hash'], PASSWORD_DEFAULT)) {
            $sql .= ', password_hash = ?';
            $params[] = password_hash($password, PASSWORD_DEFAULT);
        }
        $params[] = $row['id'];
        db()->prepare($sql . ' WHERE id = ?')->execute($params);

        login_admin((int)$row['id']);
        redirect('admin/');
    } else {
        if ($row) {
            $fails = (int)$row['failed_attempts'] + 1;
            if ($fails >= MAX_LOGIN_ATTEMPTS) {
                db()->prepare('UPDATE admins SET failed_attempts = 0, locked_until = DATE_ADD(NOW(), INTERVAL ' . LOCKOUT_MINUTES . ' MINUTE) WHERE id = ?')
                    ->execute([$row['id']]);
            } else {
                db()->prepare('UPDATE admins SET failed_attempts = ? WHERE id = ?')->execute([$fails, $row['id']]);
            }
        } else {
            // Same work as a real check so response time doesn't reveal valid usernames
            password_verify($password, '$2y$10$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG');
        }
        usleep(400000);
        $error = 'Invalid username or password.';
    }
}

$siteTitle = setting('site_title', 'My Vlog');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Log in · <?= e($siteTitle) ?></title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="<?= e(asset('admin/assets/admin.css')) ?>">
</head>
<body class="auth-page">
<div class="auth-card">
    <div class="auth-brand">
        <span class="brand-mark" aria-hidden="true"></span>
        <span><?= e($siteTitle) ?></span>
    </div>
    <h1>Welcome back</h1>
    <p class="muted">Log in to manage your vlogs and articles.</p>

    <?php foreach (flashes() as [$type, $message]): ?>
        <div class="alert alert-<?= e($type) ?>"><?= e($message) ?></div>
    <?php endforeach; ?>
    <?php if ($error): ?>
        <div class="alert alert-error" role="alert"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" autocomplete="on">
        <?= csrf_field() ?>
        <div class="field">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= e($username) ?>" required autofocus autocomplete="username">
        </div>
        <div class="field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">
        </div>
        <button type="submit" class="btn btn-block">Log in</button>
    </form>
    <a class="auth-back" href="<?= e(url()) ?>">&larr; Back to website</a>
</div>
</body>
</html>
