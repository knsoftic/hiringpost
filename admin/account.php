<?php
require __DIR__ . '/includes/auth.php';
$admin = require_login();

$errors = [];
$username = $admin['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = req_str($_POST, 'username');
    $current  = isset($_POST['current_password']) && is_string($_POST['current_password']) ? $_POST['current_password'] : '';
    $new      = isset($_POST['new_password']) && is_string($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm  = isset($_POST['confirm_password']) && is_string($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    $stmt = db()->prepare('SELECT password_hash FROM admins WHERE id = ?');
    $stmt->execute([$admin['id']]);
    if (!password_verify($current, (string)$stmt->fetchColumn())) {
        $errors[] = 'Your current password is incorrect.';
    }
    if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $username)) {
        $errors[] = 'Username must be 3–50 characters: letters, numbers, dot, dash or underscore.';
    } else {
        $stmt = db()->prepare('SELECT COUNT(*) FROM admins WHERE username = ? AND id <> ?');
        $stmt->execute([$username, $admin['id']]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'That username is already taken.';
        }
    }
    if ($new !== '' && strlen($new) < 8) {
        $errors[] = 'New password must be at least 8 characters.';
    } elseif ($new !== $confirm) {
        $errors[] = 'New password and confirmation do not match.';
    }

    if (!$errors) {
        if ($new !== '') {
            db()->prepare('UPDATE admins SET username = ?, password_hash = ? WHERE id = ?')
                ->execute([$username, password_hash($new, PASSWORD_DEFAULT), $admin['id']]);
            session_regenerate_id(true);
        } else {
            db()->prepare('UPDATE admins SET username = ? WHERE id = ?')->execute([$username, $admin['id']]);
        }
        flash('success', $new !== '' ? 'Account updated and password changed.' : 'Account updated.');
        redirect('admin/account.php');
    }
}

$adminTitle = 'Account';
$nav = 'account';
require __DIR__ . '/includes/header.php';
?>
<div class="topbar">
    <div>
        <h1>Account</h1>
        <p class="muted">Change your username or password.</p>
    </div>
</div>

<?php if ($errors): ?>
    <div class="alert alert-error" role="alert">
        <?php foreach ($errors as $error): ?><div><?= e($error) ?></div><?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="post" class="card narrow" autocomplete="off">
    <?= csrf_field() ?>
    <div class="field">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?= e($username) ?>" required maxlength="50" autocomplete="username">
    </div>
    <div class="field">
        <label for="new_password">New password</label>
        <input type="password" id="new_password" name="new_password" minlength="8" autocomplete="new-password" placeholder="Leave empty to keep the current one">
    </div>
    <div class="field">
        <label for="confirm_password">Confirm new password</label>
        <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password">
    </div>
    <hr>
    <div class="field">
        <label for="current_password">Current password</label>
        <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
        <p class="hint">Required to save any change.</p>
    </div>
    <button type="submit" class="btn">Save changes</button>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
