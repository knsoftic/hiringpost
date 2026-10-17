<?php
/* Shared helpers for the public site and the admin panel. */

const RESERVED_SLUGS = ['admin', 'assets', 'uploads', 'includes', 'page', 'search', 'sitemap', 'install', 'index'];

/* ---------- Output, URLs & requests ---------- */

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Read a trimmed string from $_GET / $_POST, ignoring arrays. */
function req_str(array $source, string $key): string
{
    return isset($source[$key]) && is_string($source[$key]) ? trim($source[$key]) : '';
}

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'
        || (string)($_SERVER['SERVER_PORT'] ?? '') === '443';
}

/** URL path of the site root, e.g. '/jobfost' or '' when installed at the domain root. */
function base_path(): string
{
    static $base = null;
    if ($base === null) {
        if (SITE_URL !== '') {
            $base = rtrim((string)parse_url(SITE_URL, PHP_URL_PATH), '/');
        } else {
            $root = str_replace('\\', '/', ROOT_DIR);
            $doc  = str_replace('\\', '/', (string)realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));
            $base = ($doc !== '' && stripos($root, $doc) === 0) ? substr($root, strlen(rtrim($doc, '/'))) : '';
            $base = rtrim($base, '/');
        }
    }
    return $base;
}

function site_url(): string
{
    if (SITE_URL !== '') {
        return rtrim(SITE_URL, '/');
    }
    $host = preg_replace('/[^A-Za-z0-9.\-:\[\]]/', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
    return (is_https() ? 'https' : 'http') . '://' . $host . base_path();
}

/** Root-relative URL for a path inside the site. */
function url(string $path = ''): string
{
    return base_path() . '/' . ltrim($path, '/');
}

/** Absolute URL (for canonical links, Open Graph and the sitemap). Accepts a site path or a url() result. */
function abs_url(string $path = ''): string
{
    if (preg_match('~^https?://~i', $path)) {
        return $path;
    }
    if ($path !== '' && $path[0] === '/') {
        // Already root-relative (includes the base path): prepend scheme + host only
        return preg_replace('~^(https?://[^/]+).*$~i', '$1', site_url()) . $path;
    }
    return site_url() . '/' . $path;
}

/** Static file URL with a cache-busting version. */
function asset(string $path): string
{
    $file = ROOT_DIR . '/' . $path;
    return url($path) . (is_file($file) ? '?v=' . filemtime($file) : '');
}

function redirect(string $path): void
{
    header('Location: ' . (preg_match('~^https?://~i', $path) ? $path : url($path)));
    exit;
}

function like_escape(string $value): string
{
    return addcslashes($value, '%_\\');
}

/* ---------- Database & settings ---------- */

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
        // Keep MySQL timestamps in the same timezone as PHP
        $pdo->exec("SET time_zone = '" . date('P') . "'");
    }
    return $pdo;
}

function settings(): array
{
    static $cache = null;
    if ($cache === null) {
        $cache = db()->query('SELECT setting_key, setting_value FROM settings')->fetchAll(PDO::FETCH_KEY_PAIR);
    }
    return $cache;
}

function setting(string $key, string $default = ''): string
{
    $all = settings();
    return isset($all[$key]) && $all[$key] !== '' ? (string)$all[$key] : $default;
}

function save_setting(string $key, string $value): void
{
    $stmt = db()->prepare(
        'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    );
    $stmt->execute([$key, $value]);
}

/* ---------- Ads (Monetag) ---------- */

function ads_enabled(): bool
{
    return setting('ads_enabled') === '1';
}

/** Ad code for a placement (e.g. 'ad_home', 'ad_post'), wrapped in a labelled box. */
function ad_slot(string $key): string
{
    $code = ads_enabled() ? setting($key) : '';
    return $code === '' ? '' : '<div class="ad-slot ad-slot-' . e(str_replace('ad_', '', $key)) . '">' . $code . '</div>';
}

/* ---------- Posts ---------- */

function post_url(array $post): string
{
    return url($post['slug']);
}

function upload_url(string $file): string
{
    return url('uploads/' . $file);
}

/** Featured image, falling back to the YouTube thumbnail for vlogs. */
function post_image(array $post): string
{
    if (!empty($post['featured_image'])) {
        return upload_url($post['featured_image']);
    }
    if (!empty($post['video_url']) && ($id = youtube_id($post['video_url']))) {
        return 'https://i.ytimg.com/vi/' . $id . '/hqdefault.jpg';
    }
    return '';
}

function post_date(array $post, string $format = 'M j, Y'): string
{
    return date($format, strtotime($post['published_at'] ?: $post['created_at']));
}

function reading_time(string $html): int
{
    $words = preg_split('/\s+/u', trim(strip_tags($html)), -1, PREG_SPLIT_NO_EMPTY);
    return max(1, (int)ceil(count($words) / 200));
}

function make_excerpt(string $html, int $length = 180): string
{
    $text = html_entity_decode(strip_tags(str_replace('<', ' <', $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = trim(preg_replace('/\s+/u', ' ', $text));
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    $cut = mb_substr($text, 0, $length);
    $space = mb_strrpos($cut, ' ');
    return rtrim(mb_substr($cut, 0, $space ?: $length), ' ,.;:-') . '…';
}

function slugify(string $text): string
{
    $text = trim($text);
    if (function_exists('iconv')) {
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if ($ascii !== false) {
            $text = $ascii;
        }
    }
    $text = preg_replace('/[^a-z0-9]+/', '-', strtolower($text));
    return trim(substr(trim($text, '-'), 0, 150), '-');
}

function unique_slug(string $slug, int $ignoreId = 0): string
{
    if ($slug === '') {
        $slug = 'post';
    }
    $base = $slug;
    $i = 2;
    $stmt = db()->prepare('SELECT COUNT(*) FROM posts WHERE slug = ? AND id <> ?');
    while (true) {
        $stmt->execute([$slug, $ignoreId]);
        if (!in_array($slug, RESERVED_SLUGS, true) && (int)$stmt->fetchColumn() === 0) {
            return $slug;
        }
        $slug = $base . '-' . $i++;
    }
}

/* ---------- Video ---------- */

function youtube_id(string $url): string
{
    $pattern = '~(?:youtube(?:-nocookie)?\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/|v/)|youtu\.be/)([A-Za-z0-9_-]{11})~i';
    return preg_match($pattern, $url, $m) ? $m[1] : '';
}

function video_embed_src(string $url): string
{
    if ($id = youtube_id($url)) {
        return 'https://www.youtube-nocookie.com/embed/' . $id;
    }
    if (preg_match('~vimeo\.com/(?:video/)?(\d+)~i', $url, $m)) {
        return 'https://player.vimeo.com/video/' . $m[1];
    }
    return '';
}

function is_video_file(string $url): bool
{
    return (bool)preg_match('~^https?://\S+\.(mp4|webm|ogv|ogg)(\?\S*)?$~i', $url);
}

function valid_video_url(string $url): bool
{
    return $url === '' || video_embed_src($url) !== '' || is_video_file($url);
}

function video_html(string $url, string $title = 'Video'): string
{
    if ($src = video_embed_src($url)) {
        return '<iframe src="' . e($src) . '" title="' . e($title) . '" loading="lazy" '
            . 'allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" '
            . 'allowfullscreen></iframe>';
    }
    if (is_video_file($url)) {
        return '<video src="' . e($url) . '" controls preload="metadata" playsinline></video>';
    }
    return '';
}

/* ---------- HTML sanitizing (post content from the editor) ---------- */

function sanitize_html(string $html): string
{
    $html = trim($html);
    if ($html === '') {
        return '';
    }

    $allowed = [
        'p' => [], 'br' => [], 'h2' => [], 'h3' => [], 'h4' => [],
        'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [], 's' => [], 'strike' => [],
        'sub' => [], 'sup' => [], 'ul' => [], 'ol' => [], 'li' => [], 'blockquote' => [],
        'pre' => [], 'code' => [], 'hr' => [], 'div' => [], 'figure' => [], 'figcaption' => [],
        'a' => ['href', 'title', 'target'],
        'img' => ['src', 'alt', 'title', 'width', 'height', 'loading'],
        'iframe' => ['src', 'title', 'width', 'height', 'allow', 'allowfullscreen'],
        'table' => [], 'thead' => [], 'tbody' => [], 'tr' => [],
        'th' => ['colspan', 'rowspan'], 'td' => ['colspan', 'rowspan'],
    ];
    // Removed completely, including their contents
    $remove = ['script', 'style', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select',
               'link', 'meta', 'base', 'svg', 'math', 'template', 'noscript', 'title', 'head'];

    $doc = new DOMDocument();
    $prev = libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="utf-8"?><div>' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    libxml_use_internal_errors($prev);

    $root = $doc->getElementsByTagName('div')->item(0);
    if (!$root) {
        return '';
    }
    clean_node($root, $allowed, $remove);

    $out = '';
    foreach ($root->childNodes as $child) {
        $out .= $doc->saveHTML($child);
    }
    return trim($out);
}

function clean_node(DOMNode $node, array $allowed, array $remove): void
{
    foreach (iterator_to_array($node->childNodes) as $child) {
        if ($child instanceof DOMText) {
            continue;
        }
        if (!$child instanceof DOMElement) {
            $node->removeChild($child); // comments, processing instructions…
            continue;
        }
        $tag = strtolower($child->nodeName);
        if (in_array($tag, $remove, true)) {
            $node->removeChild($child);
            continue;
        }

        clean_node($child, $allowed, $remove);

        if (!isset($allowed[$tag])) {
            // Unknown tag (span, font…): keep its content, drop the tag
            while ($child->firstChild) {
                $node->insertBefore($child->firstChild, $child);
            }
            $node->removeChild($child);
            continue;
        }

        foreach (iterator_to_array($child->attributes) as $attr) {
            $name = strtolower($attr->nodeName);
            $keep = in_array($name, $allowed[$tag], true);
            if ($keep && ($name === 'href' || $name === 'src')) {
                $keep = is_safe_url($attr->nodeValue, $tag === 'iframe');
            }
            if (!$keep) {
                $child->removeAttribute($attr->nodeName);
            }
        }

        if (($tag === 'iframe' || $tag === 'img') && !$child->hasAttribute('src')) {
            $node->removeChild($child);
            continue;
        }
        if ($tag === 'img') {
            $child->setAttribute('loading', 'lazy');
        }
        if ($tag === 'a' && $child->getAttribute('target') === '_blank') {
            $child->setAttribute('rel', 'noopener noreferrer');
        }
    }
}

function is_safe_url(string $url, bool $embed = false): bool
{
    $url = preg_replace('/[\x00-\x20\x7F]+/', '', $url);
    if ($embed) {
        return (bool)preg_match('~^https://(www\.)?(youtube\.com|youtube-nocookie\.com|player\.vimeo\.com)/~i', $url);
    }
    if (preg_match('~^[a-z][a-z0-9+.\-]*:~i', $url)) {
        return (bool)preg_match('~^(https?|mailto|tel):~i', $url);
    }
    return true; // relative URL
}

/* ---------- Image uploads ---------- */

function has_upload($file): bool
{
    return is_array($file) && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
}

/** Validates and stores an uploaded image. Returns the new file name or throws RuntimeException. */
function upload_image(array $file): string
{
    $limit = MAX_UPLOAD_MB * 1024 * 1024;
    if (in_array($file['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true) || $file['size'] > $limit) {
        throw new RuntimeException('Image is too large (max ' . MAX_UPLOAD_MB . ' MB).');
    }
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Image upload failed. Please try again.');
    }

    $types = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif', IMAGETYPE_WEBP => 'webp'];
    $info = @getimagesize($file['tmp_name']);
    if (!$info || !isset($types[$info[2]])) {
        throw new RuntimeException('Only JPG, PNG, GIF and WEBP images are allowed.');
    }

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
    $name = date('Ym') . '-' . bin2hex(random_bytes(8)) . '.' . $types[$info[2]];
    if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $name)) {
        throw new RuntimeException('Could not save the image. Make sure the uploads folder is writable.');
    }
    return $name;
}

function delete_upload(?string $name): void
{
    if ($name && preg_match('/^[A-Za-z0-9._-]+$/', $name) && is_file(UPLOAD_DIR . $name)) {
        @unlink(UPLOAD_DIR . $name);
    }
}

/* ---------- Pagination ---------- */

/** @param callable $link function(int $page): string */
function pagination(int $current, int $pages, callable $link): string
{
    if ($pages <= 1) {
        return '';
    }
    $out = '<nav class="pagination" aria-label="Pagination">';
    if ($current > 1) {
        $out .= '<a class="page-link" href="' . e($link($current - 1)) . '" rel="prev">&larr; Prev</a>';
    }
    $last = 0;
    for ($i = 1; $i <= $pages; $i++) {
        if ($i !== 1 && $i !== $pages && abs($i - $current) > 2) {
            continue;
        }
        if ($last && $i - $last > 1) {
            $out .= '<span class="page-gap">…</span>';
        }
        $out .= $i === $current
            ? '<span class="page-link is-active" aria-current="page">' . $i . '</span>'
            : '<a class="page-link" href="' . e($link($i)) . '">' . $i . '</a>';
        $last = $i;
    }
    if ($current < $pages) {
        $out .= '<a class="page-link" href="' . e($link($current + 1)) . '" rel="next">Next &rarr;</a>';
    }
    return $out . '</nav>';
}

/* ---------- Sessions, CSRF & flash messages (admin) ---------- */

function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('vlog_admin');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => base_path() . '/',
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_valid(): bool
{
    $token = $_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    return is_string($token) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

function csrf_check(): void
{
    if (csrf_valid()) {
        return;
    }
    http_response_code(400);
    // PHP drops the whole POST body when it exceeds post_max_size
    if (empty($_POST) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        exit('The submitted data is too large (check the image size). Please go back and try again.');
    }
    exit('Your session has expired. Please go back, refresh the page and try again.');
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = [$type, $message];
}

function flashes(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

/* ---------- Icons ---------- */

function icon(string $name, int $size = 18): string
{
    $paths = [
        'grid'     => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
        'file'     => '<path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><path d="M14 3v6h6M8 13h8M8 17h5"/>',
        'plus'     => '<path d="M12 5v14M5 12h14"/>',
        'sliders'  => '<path d="M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3M1 14h6M9 8h6M17 16h6"/>',
        'user'     => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'external' => '<path d="M14 3h7v7M10 14 21 3M19 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h5"/>',
        'logout'   => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
        'edit'     => '<path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>',
        'trash'    => '<path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v6M14 11v6"/>',
        'eye'      => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
        'search'   => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        'video'    => '<rect x="2" y="5" width="15" height="14" rx="2"/><path d="m17 10 5-3v10l-5-3z"/>',
        'arrow'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'back'     => '<path d="M19 12H5M11 18l-6-6 6-6"/>',
        'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'money'    => '<rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/><path d="M6 10v4M18 10v4"/>',
    ];
    return '<svg class="icon" viewBox="0 0 24 24" width="' . $size . '" height="' . $size . '" fill="none" stroke="currentColor" '
        . 'stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? '') . '</svg>';
}

/* ---------- Errors ---------- */

function handle_fatal(Throwable $e): void
{
    error_log((string)$e);
    if (!headers_sent()) {
        http_response_code(500);
    }
    $notInstalled = $e instanceof PDOException && in_array((string)$e->getCode(), ['1049', '42S02'], true);
    $title = $notInstalled ? 'Website not installed yet' : 'Something went wrong';
    $text = $notInstalled
        ? 'The database is missing. Open <a href="' . e(url('install.php')) . '">install.php</a> to set up the website.'
        : 'Please try again in a moment.';
    if (DEBUG) {
        $text .= '<pre style="white-space:pre-wrap;text-align:left">' . e($e->getMessage()) . '</pre>';
    }
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
        . '<title>' . e($title) . '</title></head><body style="font-family:system-ui,sans-serif;display:grid;place-items:center;'
        . 'min-height:100vh;margin:0;background:#f5f6fa;color:#0f172a"><div style="max-width:520px;padding:24px;text-align:center">'
        . '<h1>' . e($title) . '</h1><p>' . $text . '</p></div></body></html>';
}
