<?php
require __DIR__ . '/includes/auth.php';
$admin = require_login();

$post = [
    'id' => 0, 'title' => '', 'slug' => '', 'excerpt' => '', 'content' => '', 'featured_image' => '',
    'video_url' => '', 'status' => 'draft', 'meta_title' => '', 'meta_description' => '', 'published_at' => null,
];

$id = (int)req_str($_GET, 'id');
if ($id) {
    $stmt = db()->prepare('SELECT * FROM posts WHERE id = ?');
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if (!$found) {
        flash('error', 'Post not found.');
        redirect('admin/posts.php');
    }
    $post = $found;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $post['title']            = req_str($_POST, 'title');
    $post['slug']             = req_str($_POST, 'slug');
    $post['excerpt']          = req_str($_POST, 'excerpt');
    $post['content']          = sanitize_html(isset($_POST['content']) && is_string($_POST['content']) ? $_POST['content'] : '');
    $post['video_url']        = req_str($_POST, 'video_url');
    $post['status']           = req_str($_POST, 'status') === 'published' ? 'published' : 'draft';
    $post['meta_title']       = req_str($_POST, 'meta_title');
    $post['meta_description'] = req_str($_POST, 'meta_description');

    if ($post['title'] === '') {
        $errors[] = 'Please enter a title.';
    } elseif (mb_strlen($post['title']) > 255) {
        $errors[] = 'Title must be 255 characters or fewer.';
    }
    if (!valid_video_url($post['video_url'])) {
        $errors[] = 'Video URL must be a YouTube or Vimeo link, or a direct .mp4 / .webm file URL.';
    }
    if (mb_strlen($post['meta_title']) > 255) {
        $errors[] = 'Meta title must be 255 characters or fewer.';
    }
    if (mb_strlen($post['meta_description']) > 320) {
        $errors[] = 'Meta description must be 320 characters or fewer.';
    }

    $oldImage = $post['featured_image'] ?: null;
    $image = $oldImage;
    if (!$errors && has_upload($_FILES['featured_image'] ?? null)) {
        try {
            $image = upload_image($_FILES['featured_image']);
        } catch (RuntimeException $ex) {
            $errors[] = $ex->getMessage();
        }
    } elseif (!empty($_POST['remove_image'])) {
        $image = null;
    }

    if (!$errors) {
        $slug = unique_slug(slugify($post['slug'] !== '' ? $post['slug'] : $post['title']), (int)$post['id']);
        $publishedAt = $post['published_at'];
        if ($post['status'] === 'published' && !$publishedAt) {
            $publishedAt = date('Y-m-d H:i:s');
        }

        $data = [
            $post['title'],
            $slug,
            $post['excerpt'] !== '' ? $post['excerpt'] : make_excerpt($post['content']),
            $post['content'],
            $image,
            $post['video_url'] !== '' ? $post['video_url'] : null,
            $post['status'],
            $post['meta_title'] !== '' ? $post['meta_title'] : null,
            $post['meta_description'] !== '' ? $post['meta_description'] : null,
            $publishedAt,
        ];

        if ($post['id']) {
            $data[] = $post['id'];
            db()->prepare(
                'UPDATE posts SET title = ?, slug = ?, excerpt = ?, content = ?, featured_image = ?, video_url = ?,
                 status = ?, meta_title = ?, meta_description = ?, published_at = ? WHERE id = ?'
            )->execute($data);
            $savedId = (int)$post['id'];
        } else {
            db()->prepare(
                'INSERT INTO posts (title, slug, excerpt, content, featured_image, video_url, status, meta_title, meta_description, published_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            )->execute($data);
            $savedId = (int)db()->lastInsertId();
        }

        if ($oldImage && $oldImage !== $image) {
            delete_upload($oldImage);
        }

        flash('success', $post['status'] === 'published' ? 'Post published.' : 'Draft saved.');
        redirect('admin/post-edit.php?id=' . $savedId);
    }
}

$isNew      = !$post['id'];
$previewUrl = $isNew ? '' : url($post['slug']) . ($post['status'] === 'published' ? '' : '?preview=1');
$imageUrl   = $post['featured_image'] ? upload_url($post['featured_image']) : '';
// Leave the field empty while the excerpt is still auto-generated, so it keeps following the content
$excerptValue = (string)$post['excerpt'] === make_excerpt($post['content']) ? '' : (string)$post['excerpt'];

$adminTitle = $isNew ? 'Add New Post' : 'Edit Post';
$nav = $isNew ? 'new' : 'posts';
require __DIR__ . '/includes/header.php';
?>
<div class="topbar">
    <div>
        <h1><?= e($adminTitle) ?></h1>
        <p class="muted">
            <?php if ($isNew): ?>
                Publishing in <strong><?= e(setting('category_name', 'Vlogs')) ?></strong>
            <?php else: ?>
                Last updated <?= e(date('M j, Y \a\t g:i a', strtotime($post['updated_at']))) ?>
            <?php endif; ?>
        </p>
    </div>
    <a class="btn btn-light" href="<?= e(url('admin/posts.php')) ?>"><?= icon('back', 16) ?> All posts</a>
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
            <div class="field">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" class="input-title" value="<?= e($post['title']) ?>"
                       maxlength="255" required placeholder="Enter a catchy title">
            </div>
            <div class="field">
                <label for="slug">URL slug</label>
                <div class="input-prefix">
                    <span><bdi><?= e(site_url()) ?>/</bdi></span>
                    <input type="text" id="slug" name="slug" value="<?= e($post['slug']) ?>" maxlength="150" placeholder="auto-generated-from-title">
                </div>
            </div>

            <div class="field">
                <label>Content</label>
                <div class="editor" data-editor data-upload="<?= e(url('admin/upload-image.php')) ?>" data-csrf="<?= e(csrf_token()) ?>">
                    <div class="editor-toolbar" role="toolbar" aria-label="Formatting">
                        <button type="button" data-cmd="formatBlock" data-value="p" title="Paragraph">¶</button>
                        <button type="button" data-cmd="formatBlock" data-value="h2" title="Heading">H2</button>
                        <button type="button" data-cmd="formatBlock" data-value="h3" title="Subheading">H3</button>
                        <span class="sep"></span>
                        <button type="button" data-cmd="bold" title="Bold (Ctrl+B)"><b>B</b></button>
                        <button type="button" data-cmd="italic" title="Italic (Ctrl+I)"><i>I</i></button>
                        <button type="button" data-cmd="underline" title="Underline (Ctrl+U)"><u>U</u></button>
                        <button type="button" data-cmd="strikeThrough" title="Strikethrough"><s>S</s></button>
                        <span class="sep"></span>
                        <button type="button" data-cmd="insertUnorderedList" title="Bulleted list">• List</button>
                        <button type="button" data-cmd="insertOrderedList" title="Numbered list">1. List</button>
                        <button type="button" data-cmd="formatBlock" data-value="blockquote" title="Quote">❝ Quote</button>
                        <button type="button" data-cmd="formatBlock" data-value="pre" title="Code block">{ }</button>
                        <span class="sep"></span>
                        <button type="button" data-cmd="link" title="Insert link">Link</button>
                        <button type="button" data-cmd="unlink" title="Remove link">Unlink</button>
                        <button type="button" data-cmd="image" title="Upload an image">Image</button>
                        <button type="button" data-cmd="video" title="Embed a YouTube or Vimeo video">Video</button>
                        <button type="button" data-cmd="insertHorizontalRule" title="Divider">―</button>
                        <span class="sep"></span>
                        <button type="button" data-cmd="undo" title="Undo (Ctrl+Z)">↶</button>
                        <button type="button" data-cmd="redo" title="Redo (Ctrl+Y)">↷</button>
                        <button type="button" data-cmd="removeFormat" title="Clear formatting">Clear</button>
                        <button type="button" data-cmd="source" class="push-right" title="Edit HTML source">&lt;/&gt;</button>
                    </div>
                    <div class="editor-area" contenteditable="true" data-placeholder="Write your story…"></div>
                    <textarea class="editor-source" name="content" hidden spellcheck="false"><?= e($post['content']) ?></textarea>
                    <input type="file" class="editor-file" accept="image/jpeg,image/png,image/gif,image/webp" hidden>
                    <div class="editor-status" hidden>Uploading image…</div>
                </div>
            </div>

            <div class="field">
                <label for="excerpt">Short description</label>
                <textarea id="excerpt" name="excerpt" rows="3" placeholder="Shown on post cards. Leave empty to generate it from the content."><?= e($excerptValue) ?></textarea>
            </div>
        </div>
    </div>

    <aside class="form-side">
        <div class="card">
            <h3 class="card-title">Publish</h3>
            <div class="field">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="draft" <?= $post['status'] === 'draft' ? 'selected' : '' ?>>Draft — hidden from visitors</option>
                    <option value="published" <?= $post['status'] === 'published' ? 'selected' : '' ?>>Published — live on the site</option>
                </select>
            </div>
            <?php if (!empty($post['published_at'])): ?>
                <p class="hint">First published <?= e(date('M j, Y', strtotime($post['published_at']))) ?></p>
            <?php endif; ?>
            <div class="actions">
                <button type="submit" class="btn">Save post</button>
                <?php if ($previewUrl): ?>
                    <a class="btn btn-light" href="<?= e($previewUrl) ?>" target="_blank" rel="noopener"><?= icon('eye', 16) ?> <?= $post['status'] === 'published' ? 'View' : 'Preview' ?></a>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <h3 class="card-title">Featured image</h3>
            <div class="img-preview <?= $imageUrl ? '' : 'is-empty' ?>" data-preview-box>
                <img src="<?= e($imageUrl) ?>" alt="" data-preview-img>
                <span>No image selected</span>
            </div>
            <input type="file" name="featured_image" accept="image/jpeg,image/png,image/gif,image/webp" data-preview-input>
            <?php if ($imageUrl): ?>
                <label class="check"><input type="checkbox" name="remove_image" value="1"> Remove current image</label>
            <?php endif; ?>
            <p class="hint">JPG, PNG, GIF or WEBP · max <?= (int)MAX_UPLOAD_MB ?> MB · 1280×720 works best.</p>
        </div>

        <div class="card">
            <h3 class="card-title">Video (vlog)</h3>
            <div class="field">
                <label for="video_url">Video URL</label>
                <input type="url" id="video_url" name="video_url" value="<?= e($post['video_url']) ?>" placeholder="https://www.youtube.com/watch?v=…">
            </div>
            <p class="hint">Optional. YouTube, Vimeo or a direct .mp4 link. Shown at the top of the post; YouTube thumbnails are used when there’s no featured image.</p>
        </div>

        <div class="card">
            <h3 class="card-title">SEO</h3>
            <div class="field">
                <label for="meta_title">Meta title <span class="counter" data-counter-for="meta_title" data-max="60"></span></label>
                <input type="text" id="meta_title" name="meta_title" value="<?= e($post['meta_title']) ?>" maxlength="255" placeholder="Defaults to the post title">
            </div>
            <div class="field">
                <label for="meta_description">Meta description <span class="counter" data-counter-for="meta_description" data-max="160"></span></label>
                <textarea id="meta_description" name="meta_description" rows="3" maxlength="320" placeholder="Defaults to the short description"><?= e($post['meta_description']) ?></textarea>
            </div>
        </div>
    </aside>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
