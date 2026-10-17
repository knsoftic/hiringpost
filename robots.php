<?php
/** Dynamic robots.txt — served at /robots.txt via .htaccess rewrite. */
require_once __DIR__ . '/includes/bootstrap.php';

header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: public, max-age=86400');

echo "User-agent: *\n";
echo "Allow: /\n";
echo "\n";
echo "# Blocked paths\n";
echo "Disallow: /admin/\n";
echo "Disallow: /includes/\n";
echo "Disallow: /install.php\n";
echo "\n";
echo "# Sitemap\n";
echo "Sitemap: " . abs_url('sitemap.xml') . "\n";
