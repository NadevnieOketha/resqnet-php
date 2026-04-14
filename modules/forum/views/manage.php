<?php
$posts = is_array($posts ?? null) ? $posts : [];
?>

<style>
    .forum-admin-grid { display:grid; gap:1rem; }
    .forum-admin-card {
        border:1px solid var(--color-border);
        border-radius:var(--radius-lg);
        background:#fff;
        padding:1rem;
    }
    .forum-admin-card h1,
    .forum-admin-card h2 { margin-top:0; }
    .forum-admin-form-grid { display:grid; gap:0.75rem; }
    .forum-table-shell { border:1px solid var(--color-border); border-radius:var(--radius-lg); overflow:auto; background:#fff; }
    table.forum-table { width:100%; border-collapse:collapse; font-size:0.72rem; min-width:980px; }
    table.forum-table thead th { text-align:left; padding:0.75rem 0.85rem; background:#fafafa; border-bottom:1px solid var(--color-border); }
    table.forum-table tbody td { padding:0.75rem 0.85rem; border-bottom:1px solid var(--color-border); vertical-align:top; }
    table.forum-table tbody tr:last-child td { border-bottom:none; }
    .forum-image-thumb { width:92px; height:64px; object-fit:cover; border-radius:8px; border:1px solid var(--color-border); }
    .forum-post-body { white-space:pre-wrap; max-width:420px; }
    .forum-actions { display:grid; gap:0.45rem; }
    details.forum-edit-box summary { cursor:pointer; color:#1f4f96; }
    .forum-edit-form { display:grid; gap:0.45rem; margin-top:0.45rem; }
</style>

<section class="forum-admin-grid" aria-label="DMC forum post management">
    <div class="forum-admin-card">
        <h1>Forum Posts</h1>
        <p class="muted">Create updates that go live immediately on the public forum page.</p>

        <h2>New Post</h2>
        <form method="POST" action="/dashboard/admin/forum-posts/create" enctype="multipart/form-data" class="forum-admin-form-grid">
            <?= csrf_field() ?>

            <div class="form-group" style="margin:0;">
                <label for="new_title">Title</label>
                <input id="new_title" class="input" type="text" name="title" maxlength="180" value="<?= old('title') ?>" required>
            </div>

            <div class="form-group" style="margin:0;">
                <label for="new_body">Body</label>
                <textarea id="new_body" class="input" name="body" rows="5" required><?= old('body') ?></textarea>
            </div>

            <div class="form-group" style="margin:0;">
                <label for="new_image">Image (optional)</label>
                <input id="new_image" class="input" type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
            </div>

            <div>
                <button type="submit" class="btn btn-primary">Publish Post</button>
            </div>
        </form>
    </div>

    <div class="forum-table-shell">
        <table class="forum-table">
            <thead>
                <tr>
                    <th>Post</th>
                    <th>Body</th>
                    <th>Image</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($posts)): ?>
                    <tr><td colspan="5" class="muted">No forum posts yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <tr>
                            <td>
                                <strong><?= e((string) ($post['title'] ?? 'Untitled')) ?></strong><br>
                                <span class="muted">#<?= (int) ($post['post_id'] ?? 0) ?></span>
                            </td>
                            <td><div class="forum-post-body"><?= e((string) ($post['body'] ?? '')) ?></div></td>
                            <td>
                                <?php if (!empty($post['image_path'])): ?>
                                    <img src="<?= e((string) $post['image_path']) ?>" alt="Forum image" class="forum-image-thumb">
                                <?php else: ?>
                                    <span class="muted">No image</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= e((string) ($post['created_at'] ?? '-')) ?><br>
                                <span class="muted">Updated: <?= e((string) ($post['updated_at'] ?? '-')) ?></span>
                            </td>
                            <td>
                                <div class="forum-actions">
                                    <details class="forum-edit-box">
                                        <summary>Edit</summary>
                                        <form method="POST" action="/dashboard/admin/forum-posts/<?= (int) ($post['post_id'] ?? 0) ?>/update" enctype="multipart/form-data" class="forum-edit-form">
                                            <?= csrf_field() ?>
                                            <input class="input" type="text" name="title" maxlength="180" value="<?= e((string) ($post['title'] ?? '')) ?>" required>
                                            <textarea class="input" name="body" rows="4" required><?= e((string) ($post['body'] ?? '')) ?></textarea>
                                            <input class="input" type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
                                            <?php if (!empty($post['image_path'])): ?>
                                                <label class="form-check" style="margin:0;">
                                                    <input type="checkbox" name="remove_image" value="1">
                                                    Remove current image
                                                </label>
                                            <?php endif; ?>
                                            <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
                                        </form>
                                    </details>

                                    <form method="POST" action="/dashboard/admin/forum-posts/<?= (int) ($post['post_id'] ?? 0) ?>/delete" onsubmit="return confirm('Delete this post permanently?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-outline btn-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
