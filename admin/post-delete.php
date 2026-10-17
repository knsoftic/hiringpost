<?php
require __DIR__ . '/includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/posts.php');
}
csrf_check();

$id = (int)req_str($_POST, 'id');
$stmt = db()->prepare('SELECT featured_image FROM posts WHERE id = ?');
$stmt->execute([$id]);
$post = $stmt->fetch();

if ($post) {
    db()->prepare('DELETE FROM posts WHERE id = ?')->execute([$id]);
    delete_upload($post['featured_image']);
    flash('success', 'Post deleted.');
} else {
    flash('error', 'Post not found.');
}
redirect('admin/posts.php');
