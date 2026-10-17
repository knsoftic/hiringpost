# Vlog & Article Website (PHP + MySQL)

A lightweight vlog/article site with one admin-editable category. Plain PHP 7.4+/8.x, MySQL/MariaDB, HTML5, CSS3 — no frameworks.

## Install (XAMPP / localhost)

1. Start **Apache** and **MySQL** in XAMPP.
2. Check the database settings in `config.php` (XAMPP default: user `root`, empty password).
3. Open **http://localhost/jobfost/install.php**, choose a site title, category name and your admin username/password.
4. Delete `install.php` afterwards.
5. Admin panel: **http://localhost/jobfost/admin/**

## Install (live hosting)

1. Upload all files (including the `.htaccess` files) to your hosting.
2. Create a MySQL database + user in your control panel and put the details in `config.php`.
3. Optionally set `SITE_URL` in `config.php` (e.g. `https://example.com`).
4. Make sure the `uploads/` folder is writable, open `/install.php`, then delete it.

Requires Apache with `mod_rewrite` (for SEO-friendly URLs) and the PHP `pdo_mysql`, `mbstring`, `dom` and `gd`/`fileinfo` extensions (standard on most hosts).

## URLs

| URL | Page |
| --- | --- |
| `/` | Homepage — featured latest post + post grid |
| `/page/2` | Pagination |
| `/search?q=word` | Search |
| `/post-slug` | Single post |
| `/sitemap.xml` | XML sitemap for search engines |
| `/sw.js` | Monetag push-notification service worker (when set in Admin → Ads) |
| `/admin/` | Admin panel |

## Ads (Monetag)

Admin → **Ads** has everything needed for [Monetag](https://monetag.com):

1. **Site verification** — paste the `<meta name="monetag" …>` tag (added to every page, even while ads are off).
2. **Ad codes** — paste zone scripts (Multitag, OnClick/Popunder, In-Page Push, Vignette, Interstitial). Loaded in `<head>` of every public page.
3. **Push notifications** — paste the content of Monetag's `sw.js`; it is served at `/sw.js`.
4. **Placements** — optional HTML/script below the featured post on the homepage and below the content on post pages (e.g. a banner using your Direct Link).

A master switch turns all ads on/off without deleting the codes. Ads never appear in the admin panel or in draft previews. Monetag can only verify a live site on a real domain — not `localhost`.

## Folder structure

```
config.php            Database + site settings
index.php             Homepage, listing, pagination, search
post.php              Single post page
404.php               Not-found page
sitemap.php           XML sitemap (served as /sitemap.xml)
sw.php                Monetag push service worker (served as /sw.js)
install.php           One-time installer (delete after use)
includes/             Bootstrap, helpers, layout templates, schema.sql (not web-accessible)
assets/css/style.css  Public site styles
admin/                Dashboard, posts, editor, settings, account
admin/assets/         Admin CSS + editor JavaScript
uploads/              Uploaded images (scripts blocked by .htaccess)
```

## Database

- `admins` — admin accounts (bcrypt password hashes, login lockout fields)
- `posts` — title, slug, excerpt, content, featured image, video URL, status, SEO fields, dates
- `settings` — key/value site settings (title, tagline, category name, logo, …)

## Security

- PDO prepared statements for every query
- `password_hash()` / `password_verify()`, session ID regenerated on login, HttpOnly + SameSite cookies
- Account locked for 15 minutes after 5 failed logins; 2-hour idle session timeout
- CSRF token on every admin form and AJAX upload
- Post HTML sanitized with an allow-list (no scripts, event handlers or `javascript:` links; iframes only from YouTube/Vimeo)
- Uploads checked as real images, renamed randomly, and PHP execution is disabled in `uploads/`
- `includes/` and `admin/includes/` blocked from direct web access
