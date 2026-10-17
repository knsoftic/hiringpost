<?php
require __DIR__ . '/includes/auth.php';
$admin = require_login();

$fields = [
    'site_title'       => 100,
    'site_tagline'     => 160,
    'category_name'    => 60,
    'site_description' => 320,
    'meta_keywords'    => 255,
    'footer_text'      => 255,
];
$values = [];
foreach ($fields as $key => $max) {
    $values[$key] = setting($key);
}
$values['posts_per_page'] = setting('posts_per_page', '9');
$logo = setting('site_logo');
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    foreach ($fields as $key => $max) {
        $values[$key] = req_str($_POST, $key);
        if (mb_strlen($values[$key]) > $max) {
            $errors[] = ucfirst(str_replace('_', ' ', $key)) . " must be $max characters or fewer.";
        }
    }
    if ($values['site_title'] === '') {
        $errors[] = 'Site title is required.';
    }
    if ($values['category_name'] === '') {
        $errors[] = 'Category name is required.';
    }
    $values['posts_per_page'] = req_str($_POST, 'posts_per_page');
    $perPage = (int)$values['posts_per_page'];
    if ($perPage < 1 || $perPage > 50) {
        $errors[] = 'Posts per page must be between 1 and 50.';
    }

    $newLogo = $logo;
    if (!$errors && has_upload($_FILES['site_logo'] ?? null)) {
        try {
            $newLogo = upload_image($_FILES['site_logo']);
        } catch (RuntimeException $ex) {
            $errors[] = 'Logo: ' . $ex->getMessage();
        }
    } elseif (!empty($_POST['remove_logo'])) {
        $newLogo = '';
    }

    if (!$errors) {
        foreach ($fields as $key => $max) {
            save_setting($key, $values[$key]);
        }
        save_setting('posts_per_page', (string)$perPage);
        save_setting('site_logo', $newLogo);
        if ($logo && $logo !== $newLogo) {
            delete_upload($logo);
        }
        flash('success', 'Settings saved.');
        redirect('admin/settings.php');
    }
}

$adminTitle = 'Settings';
$nav = 'settings';
require __DIR__ . '/includes/header.php';
?>
<div class="topbar">
    <div>
        <h1>Settings</h1>
        <p class="muted">Site name, logo, category and SEO defaults.</p>
    </div>
</div>

<?php if ($errors): ?>
    <div class="alert alert-error" role="alert">
        <?php foreach ($errors as $error): ?><div><?= e($error) ?></div><?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="form-grid">
    <?= csrf_field() ?>
    <div class="form-main">
        <div class="card">
            <h3 class="card-title">General</h3>
            <div class="field">
                <label for="site_title">Site title</label>
                <input type="text" id="site_title" name="site_title" value="<?= e($values['site_title']) ?>" maxlength="100" required>
            </div>
            <div class="field">
                <label for="site_tagline">Tagline</label>
                <input type="text" id="site_tagline" name="site_tagline" value="<?= e($values['site_tagline']) ?>" maxlength="160">
                <p class="hint">Shown as the big headline on the homepage.</p>
            </div>
            <div class="field">
                <label for="footer_text">Footer text</label>
                <input type="text" id="footer_text" name="footer_text" value="<?= e($values['footer_text']) ?>" maxlength="255" placeholder="© <?= date('Y') ?> <?= e($values['site_title']) ?>. All rights reserved.">
            </div>
            <div class="field">
                <label for="posts_per_page">Posts per page</label>
                <input type="number" id="posts_per_page" name="posts_per_page" value="<?= e($values['posts_per_page']) ?>" min="1" max="50" required class="input-narrow">
            </div>
        </div>

        <div class="card">
            <h3 class="card-title">Category</h3>
            <div class="field">
                <label for="category_name">Category name</label>
                <input type="text" id="category_name" name="category_name" value="<?= e($values['category_name']) ?>" maxlength="60" required>
                <p class="hint">The site has one main category — every vlog and article is published under it. Shown in the menu, headings and post badges.</p>
            </div>
        </div>

        <div class="card">
            <h3 class="card-title">SEO</h3>
            <div class="field">
                <label for="site_description">Homepage meta description <span class="counter" data-counter-for="site_description" data-max="160"></span></label>
                <textarea id="site_description" name="site_description" rows="3" maxlength="320"><?= e($values['site_description']) ?></textarea>
                <p class="hint">Used by search engines and shown under the homepage headline.</p>
            </div>
            <div class="field">
                <label for="meta_keywords">Meta keywords</label>
                <input type="text" id="meta_keywords" name="meta_keywords" value="<?= e($values['meta_keywords']) ?>" maxlength="255" placeholder="blog, vlogs, articles, entertainment">
                <p class="hint">Comma-separated keywords for search engines. Applied site-wide; post pages also include the category name.</p>
            </div>
        </div>
    </div>

    <aside class="form-side">
        <div class="card">
            <h3 class="card-title">Logo</h3>
            <div class="img-preview img-preview-logo <?= $logo ? '' : 'is-empty' ?>" data-preview-box>
                <img src="<?= $logo ? e(upload_url($logo)) : '' ?>" alt="" data-preview-img>
                <span>No logo — the default icon is used</span>
            </div>
            <input type="file" name="site_logo" accept="image/jpeg,image/png,image/gif,image/webp" data-preview-input>
            <?php if ($logo): ?>
                <label class="check"><input type="checkbox" name="remove_logo" value="1"> Remove logo</label>
            <?php endif; ?>
            <p class="hint">Square PNG or WEBP works best. Also used as the browser icon.</p>
        </div>
        <div class="card">
            <button type="submit" class="btn btn-block">Save settings</button>
        </div>
    </aside>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
