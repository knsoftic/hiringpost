<?php
/**
 * Public site header. Optional variables set by the page:
 * $pageTitle, $metaDescription, $canonical, $ogImage, $ogType, $noindex, $activeNav, $searchQuery, $hideAds
 */
$siteTitle       = setting('site_title', 'My Vlog');
$categoryName    = setting('category_name', 'Vlogs');
$logo            = setting('site_logo');
$pageTitle       = $pageTitle ?? $siteTitle;
$metaDescription = $metaDescription ?? setting('site_description');
$canonical       = $canonical ?? '';
$ogImage         = $ogImage ?? ($logo ? abs_url(upload_url($logo)) : '');
$ogType          = $ogType ?? 'website';
$activeNav       = $activeNav ?? '';
$searchQuery     = $searchQuery ?? '';
$favicon = $logo
    ? upload_url($logo)
    : "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='9' fill='%23f43f5e'/%3E%3Cpath d='M13 10l9 6-9 6z' fill='white'/%3E%3C/svg%3E";
$metaKeywords    = $metaKeywords ?? setting('meta_keywords');
// X-Robots-Tag HTTP header
if (!headers_sent()) {
    if (!empty($noindex)) {
        header('X-Robots-Tag: noindex, follow');
    } else {
        header('X-Robots-Tag: index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <?php if ($metaDescription !== ''): ?>
    <meta name="description" content="<?= e($metaDescription) ?>">
    <?php endif; ?>
    <?php if (!empty($noindex)): ?>
    <meta name="robots" content="noindex, follow">
    <?php else: ?>
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <?php endif; ?>
    <meta name="publisher" content="<?= e($siteTitle) ?>">
    <?php if ($metaKeywords !== ''): ?>
    <meta name="keywords" content="<?= e($metaKeywords) ?>">
    <?php endif; ?>
    <?php if ($canonical): ?>
    <link rel="canonical" href="<?= e($canonical) ?>">
    <meta property="og:url" content="<?= e($canonical) ?>">
    <?php endif; ?>
    <meta property="og:site_name" content="<?= e($siteTitle) ?>">
    <meta property="og:type" content="<?= e($ogType) ?>">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <?php if ($metaDescription !== ''): ?>
    <meta property="og:description" content="<?= e($metaDescription) ?>">
    <?php endif; ?>
    <?php if ($ogImage): ?>
    <meta property="og:image" content="<?= e($ogImage) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <?php endif; ?>
    <link rel="icon" href="<?= e($favicon) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="<?= e(asset('assets/css/style.css')) ?>">
    <?php if (setting('monetag_meta') !== ''): ?>
    <meta name="monetag" content="<?= e(setting('monetag_meta')) ?>">
    <?php endif; ?>
    <?php if (ads_enabled() && empty($hideAds)): ?>
    <?= setting('ads_head_code') /* Monetag ad scripts, added by the admin in Admin → Ads */ ?>
    <?php endif; ?>
</head>
<body>
<a class="skip-link" href="#content">Skip to content</a>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="<?= e(url()) ?>">
            <?php if ($logo): ?>
                <img src="<?= e(upload_url($logo)) ?>" alt="">
            <?php else: ?>
                <span class="brand-mark" aria-hidden="true"></span>
            <?php endif; ?>
            <span class="brand-name"><?= e($siteTitle) ?></span>
        </a>

        <input type="checkbox" id="nav-toggle" class="nav-toggle" aria-label="Toggle menu">
        <label for="nav-toggle" class="nav-burger" aria-hidden="true"><span></span></label>

        <nav class="site-nav" aria-label="Main">
            <a href="<?= e(url()) ?>" class="<?= $activeNav === 'home' ? 'active' : '' ?>">Home</a>
            <a href="<?= e(url('#latest')) ?>" class="<?= $activeNav === 'category' ? 'active' : '' ?>"><?= e($categoryName) ?></a>
            <form class="nav-search" action="<?= e(url('search')) ?>" method="get" role="search">
                <label for="nav-q" class="visually-hidden">Search posts</label>
                <input type="search" id="nav-q" name="q" placeholder="Search posts…" value="<?= e($searchQuery) ?>">
                <button type="submit" aria-label="Search"><?= icon('search') ?></button>
            </form>
        </nav>
    </div>
</header>
<main id="content">
