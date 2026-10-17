-- Database schema (run automatically by install.php)

CREATE TABLE IF NOT EXISTS admins (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username VARCHAR(50) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  failed_attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
  locked_until DATETIME NULL DEFAULT NULL,
  last_login DATETIME NULL DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_admins_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS posts (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(191) NOT NULL,
  excerpt TEXT NULL,
  content MEDIUMTEXT NOT NULL,
  featured_image VARCHAR(255) NULL DEFAULT NULL,
  video_url VARCHAR(500) NULL DEFAULT NULL,
  status ENUM('draft','published') NOT NULL DEFAULT 'draft',
  meta_title VARCHAR(255) NULL DEFAULT NULL,
  meta_description VARCHAR(320) NULL DEFAULT NULL,
  published_at DATETIME NULL DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_posts_slug (slug),
  KEY idx_posts_status_published (status, published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
  setting_key VARCHAR(64) NOT NULL,
  setting_value TEXT NULL,
  PRIMARY KEY (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
  ('site_title', 'JobFost'),
  ('site_tagline', 'Stories, guides and vlogs worth your time'),
  ('site_description', 'Fresh vlogs and practical articles, published regularly.'),
  ('category_name', 'Vlogs'),
  ('site_logo', ''),
  ('posts_per_page', '9'),
  ('footer_text', ''),
  ('ads_enabled', '0'),
  ('monetag_meta', ''),
  ('ads_head_code', ''),
  ('monetag_sw', ''),
  ('ad_home', ''),
  ('ad_post', '');
