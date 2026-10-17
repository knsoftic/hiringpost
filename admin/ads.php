<?php
require __DIR__ . '/includes/auth.php';
$admin = require_login();

$codeFields = [
    'ads_head_code' => 'Ad codes',
    'monetag_sw'    => 'sw.js content',
    'ad_home'       => 'Homepage ad',
    'ad_post'       => 'Post page ad',
];
$values = [
    'ads_enabled'  => setting('ads_enabled', '0'),
    'monetag_meta' => setting('monetag_meta'),
];
foreach ($codeFields as $key => $label) {
    $values[$key] = setting($key);
}
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $values['ads_enabled'] = !empty($_POST['ads_enabled']) ? '1' : '0';

    // Accept the full <meta name="monetag" content="…"> tag or just the code
    $meta = req_str($_POST, 'monetag_meta');
    if (preg_match('/content\s*=\s*["\']([^"\']*)["\']/i', $meta, $m)) {
        $meta = trim($m[1]);
    }
    if ($meta !== '' && !preg_match('/^[A-Za-z0-9_.\-]{4,128}$/', $meta)) {
        $errors[] = 'Site verification: paste the meta tag from Monetag, e.g. <meta name="monetag" content="abc123…">';
    }
    $values['monetag_meta'] = $meta;

    foreach ($codeFields as $key => $label) {
        $values[$key] = isset($_POST[$key]) && is_string($_POST[$key]) ? trim(str_replace("\r\n", "\n", $_POST[$key])) : '';
        if (strlen($values[$key]) > 20000) {
            $errors[] = "$label is too long.";
        }
    }
    if (preg_match('/<\s*\/?\s*script/i', $values['monetag_sw'])) {
        $errors[] = 'sw.js: paste only the text inside the sw.js file, without any <script> tags.';
    }

    if (!$errors) {
        foreach ($values as $key => $value) {
            save_setting($key, $value);
        }
        if ($values['ads_enabled'] === '1' && $values['ads_head_code'] . $values['ad_home'] . $values['ad_post'] === '') {
            flash('error', 'Ads are switched on, but no ad code has been added yet.');
        }
        flash('success', $values['ads_enabled'] === '1' ? 'Ad settings saved — ads are live.' : 'Ad settings saved — ads are switched off.');
        redirect('admin/ads.php');
    }
}

$swUrl = abs_url('sw.js');
$adminTitle = 'Ads';
$nav = 'ads';
require __DIR__ . '/includes/header.php';
?>
<div class="topbar">
    <div>
        <h1>Ads <span class="status <?= $values['ads_enabled'] === '1' ? 'status-published' : 'status-draft' ?>"><?= $values['ads_enabled'] === '1' ? 'Live' : 'Off' ?></span></h1>
        <p class="muted">Monetize your site with <a class="link" href="https://monetag.com" target="_blank" rel="noopener">Monetag</a> — paste the codes from your Monetag dashboard below.</p>
    </div>
</div>

<?php if ($errors): ?>
    <div class="alert alert-error" role="alert">
        <?php foreach ($errors as $error): ?><div><?= e($error) ?></div><?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="post" class="form-grid">
    <?= csrf_field() ?>

    <div class="form-main">
        <div class="card">
            <h3 class="card-title"><span class="step">1</span> Site verification</h3>
            <div class="field">
                <label for="monetag_meta">Monetag meta tag</label>
                <input type="text" id="monetag_meta" name="monetag_meta" class="code-input"
                       value="<?= $values['monetag_meta'] !== '' ? e('<meta name="monetag" content="' . $values['monetag_meta'] . '">') : '' ?>"
                       placeholder='<meta name="monetag" content="…">'>
                <p class="hint">When you add your site in Monetag, choose the meta tag option and paste it here. It is added to every page, even while ads are off, so you can verify first.</p>
            </div>
        </div>

        <div class="card">
            <h3 class="card-title"><span class="step">2</span> Ad codes</h3>
            <div class="field">
                <label for="ads_head_code">Ad zone scripts</label>
                <textarea id="ads_head_code" name="ads_head_code" rows="7" class="code-input" spellcheck="false"
                          placeholder='<script src="https://…/tag.min.js" data-zone="1234567" async data-cfasync="false"></script>'><?= e($values['ads_head_code']) ?></textarea>
                <p class="hint">Paste the script of each zone you create — <strong>Multitag</strong> (recommended, all formats in one), or individual
                    <strong>OnClick (Popunder)</strong>, <strong>In-Page Push</strong>, <strong>Vignette Banner</strong> or <strong>Interstitial</strong> codes.
                    You can paste several codes, one below the other. They load on every public page.</p>
            </div>
        </div>

        <div class="card">
            <h3 class="card-title"><span class="step">3</span> Push notifications <span class="muted small">(optional)</span></h3>
            <div class="field">
                <label for="monetag_sw">sw.js file content</label>
                <textarea id="monetag_sw" name="monetag_sw" rows="6" class="code-input" spellcheck="false"
                          placeholder="self.options = { … }&#10;importScripts('https://…')"><?= e($values['monetag_sw']) ?></textarea>
                <p class="hint">For the Push Notifications format Monetag gives you a <code>sw.js</code> file. Open it in Notepad, copy everything and paste it here.
                    It will be served at <a class="link" href="<?= e($swUrl) ?>" target="_blank" rel="noopener"><?= e($swUrl) ?></a>.
                    Empty this box to stop push notifications completely.</p>
            </div>
        </div>

        <div class="card">
            <h3 class="card-title"><span class="step">4</span> Ad placements <span class="muted small">(optional)</span></h3>
            <div class="field">
                <label for="ad_home">Homepage — below the featured post</label>
                <textarea id="ad_home" name="ad_home" rows="4" class="code-input" spellcheck="false" placeholder="Banner HTML or ad script"><?= e($values['ad_home']) ?></textarea>
            </div>
            <div class="field">
                <label for="ad_post">Post page — below the content</label>
                <textarea id="ad_post" name="ad_post" rows="4" class="code-input" spellcheck="false" placeholder="Banner HTML or ad script"><?= e($values['ad_post']) ?></textarea>
                <p class="hint">For banner-style ads shown at a fixed spot, e.g. an image linked to your Monetag <strong>Direct Link</strong>:
                    <code>&lt;a href="DIRECT-LINK" target="_blank" rel="nofollow"&gt;&lt;img src="banner.jpg"&gt;&lt;/a&gt;</code></p>
            </div>
        </div>
    </div>

    <aside class="form-side">
        <div class="card">
            <h3 class="card-title">Status</h3>
            <label class="switch-row">
                <span>
                    <strong>Show ads on the website</strong>
                    <small>Switch off to hide all ads instantly without deleting your codes.</small>
                </span>
                <input type="checkbox" name="ads_enabled" value="1" class="switch" <?= $values['ads_enabled'] === '1' ? 'checked' : '' ?>>
            </label>
            <button type="submit" class="btn btn-block">Save ad settings</button>
        </div>

        <div class="card">
            <h3 class="card-title">How to set up Monetag</h3>
            <ol class="steps-list">
                <li>Put your website online on a real domain (Monetag can’t check <code>localhost</code>).</li>
                <li>Sign up at <a class="link" href="https://monetag.com" target="_blank" rel="noopener">monetag.com</a> as a <strong>Publisher</strong> and add your site.</li>
                <li>Paste the verification meta tag in <strong>step 1</strong>, save, then click <em>Verify</em> in Monetag.</li>
                <li>Create ad zones in Monetag and paste their codes in <strong>step 2</strong> (and <strong>step 3</strong> for push).</li>
                <li>Turn on <strong>Show ads</strong> and save. Ads may take a few minutes to start showing.</li>
            </ol>
            <p class="hint">Tip: start with one or two formats. Too many pop-ups can annoy visitors and hurt your traffic.</p>
        </div>
    </aside>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
