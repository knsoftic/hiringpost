<?php
require_once __DIR__ . '/../../includes/bootstrap.php';

const SESSION_IDLE_LIMIT = 7200;   // log out after 2 hours of inactivity
const MAX_LOGIN_ATTEMPTS = 5;
const LOCKOUT_MINUTES    = 15;

start_session();
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');

/** The logged-in admin row, or null. */
function current_admin(): ?array
{
    static $admin = false;
    if ($admin !== false) {
        return $admin;
    }
    $admin = null;
    if (!empty($_SESSION['admin_id'])) {
        if (time() - (int)($_SESSION['last_seen'] ?? 0) > SESSION_IDLE_LIMIT) {
            logout_admin();
            flash('error', 'Your session expired. Please log in again.');
        } else {
            $stmt = db()->prepare('SELECT id, username FROM admins WHERE id = ?');
            $stmt->execute([$_SESSION['admin_id']]);
            $admin = $stmt->fetch() ?: null;
            $_SESSION['last_seen'] = time();
        }
    }
    return $admin;
}

function require_login(): array
{
    $admin = current_admin();
    if (!$admin) {
        redirect('admin/login.php');
    }
    return $admin;
}

function login_admin(int $id): void
{
    session_regenerate_id(true);
    $_SESSION['admin_id'] = $id;
    $_SESSION['last_seen'] = time();
}

function logout_admin(): void
{
    $_SESSION = [];
    session_regenerate_id(true);
}
