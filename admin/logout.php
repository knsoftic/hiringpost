<?php
require __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valid()) {
    logout_admin();
    flash('success', 'You have been logged out.');
}
redirect('admin/login.php');
