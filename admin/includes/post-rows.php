<?php /** Posts table used by the dashboard and the posts list. Expects $rows. */ ?>
<div class="table-wrap">
    <table class="table">
        <thead>
        <tr>
            <th>Post</th>
            <th>Status</th>
            <th>Date</th>
            <th class="text-right">Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row): $thumb = post_image($row); ?>
            <tr>
                <td>
                    <div class="post-cell">
                        <?php if ($thumb): ?>
                            <img class="thumb" src="<?= e($thumb) ?>" alt="">
                        <?php else: ?>
                            <span class="thumb thumb-empty"><?= e(mb_strtoupper(mb_substr($row['title'], 0, 1))) ?></span>
                        <?php endif; ?>
                        <div>
                            <a class="post-cell-title" href="<?= e(url('admin/post-edit.php?id=' . $row['id'])) ?>"><?= e($row['title']) ?></a>
                            <span class="post-cell-slug">/<?= e($row['slug']) ?><?= !empty($row['video_url']) ? ' · Video' : '' ?></span>
                        </div>
                    </div>
                </td>
                <td><span class="status status-<?= e($row['status']) ?>"><?= $row['status'] === 'published' ? 'Published' : 'Draft' ?></span></td>
                <td class="nowrap muted"><?= e(post_date($row)) ?></td>
                <td>
                    <div class="row-actions">
                        <a class="icon-btn" href="<?= e(post_url($row) . ($row['status'] === 'published' ? '' : '?preview=1')) ?>" target="_blank" rel="noopener" title="View"><?= icon('eye', 16) ?></a>
                        <a class="icon-btn" href="<?= e(url('admin/post-edit.php?id=' . $row['id'])) ?>" title="Edit"><?= icon('edit', 16) ?></a>
                        <form method="post" action="<?= e(url('admin/post-delete.php')) ?>" data-confirm="Delete “<?= e($row['title']) ?>”? This cannot be undone.">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                            <button type="submit" class="icon-btn icon-btn-danger" title="Delete"><?= icon('trash', 16) ?></button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
