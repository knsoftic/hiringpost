<?php
/** AJAX endpoint used by the editor's "Image" button. Returns JSON: {url} or {error}. */
require __DIR__ . '/includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

function json_out(int $code, array $data): void
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    exit;
}

if (!current_admin()) {
    json_out(401, ['error' => 'Your session has expired. Please log in again.']);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid()) {
    json_out(400, ['error' => 'Invalid request. Please refresh the page and try again.']);
}
if (!has_upload($_FILES['image'] ?? null)) {
    json_out(400, ['error' => 'No image received.']);
}

try {
    json_out(200, ['url' => upload_url(upload_image($_FILES['image']))]);
} catch (RuntimeException $ex) {
    json_out(422, ['error' => $ex->getMessage()]);
}
