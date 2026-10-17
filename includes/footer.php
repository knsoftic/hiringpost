</main>
<footer class="site-footer">
    <div class="container footer-inner">
        <div>
            <a class="brand brand-sm" href="<?= e(url()) ?>">
                <span class="brand-mark" aria-hidden="true"></span>
                <span class="brand-name"><?= e(setting('site_title', 'My Vlog')) ?></span>
            </a>
            <?php if (setting('site_tagline') !== ''): ?>
                <p class="footer-tagline"><?= e(setting('site_tagline')) ?></p>
            <?php endif; ?>
        </div>
        <p class="footer-copy">
            <?= e(setting('footer_text', '© ' . date('Y') . ' ' . setting('site_title', 'My Vlog') . '. All rights reserved.')) ?>
        </p>
    </div>
</footer>
</body>
</html>
