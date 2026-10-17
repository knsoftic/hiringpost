<?php
/** Serves the Monetag push-notification service worker as /sw.js (content set in Admin → Ads). */
require_once __DIR__ . '/includes/bootstrap.php';

$code = setting('monetag_sw');
if ($code === '') {
    require __DIR__ . '/404.php';
    exit;
}

header('Content-Type: application/javascript; charset=utf-8');
header('Cache-Control: public, max-age=3600');
echo $code;
