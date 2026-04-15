<?php
$posts = is_array($posts ?? null) ? $posts : [];
?>

<style>
    .forum-wrap { display:grid; gap:1rem; }
    .forum-hero {
        border:1px solid var(--color-border);
        border-radius:var(--radius-lg);
        background:#fff;
        padding:1rem;
    }
    .forum-hero h1 { margin:0 0 0.35rem; }
    .forum-hero p { margin:0; color:var(--color-text-subtle); }
    .forum-list { display:grid; gap:0.8rem; }
    .forum-card {
        border:1px solid var(--color-border);
        border-radius:var(--radius-lg);
        background:#fff;
        padding:0.95rem;
    }
    .forum-title { margin:0 0 0.35rem; }
    .forum-meta { color:#64748b; font-size:0.72rem; margin-bottom:0.65rem; }
    .forum-body { white-space:pre-wrap; line-height:1.55; }
    .forum-image {
        margin-top:0.7rem;
        max-width:100%;
        border-radius:10px;
        border:1px solid var(--color-border);
    }
    .forum-empty {
        border:1px dashed var(--color-border);
        border-radius:var(--radius-lg);
        padding:1rem;
        background:#fff;
        color:var(--color-text-subtle);
    }
</style>

<section class="forum-wrap" aria-label="Public forum posts">
    <div class="forum-hero">
        <h1>Forum</h1>
        <p>Official public updates posted by DMC officers.</p>
    </div>

    <?php if (empty($posts)): ?>
        <div class="forum-empty">No forum posts yet.</div>
    <?php else: ?>
        <div class="forum-list">
            <?php foreach ($posts as $post): ?>
                <article class="forum-card">
                    <h2 class="forum-title"><?= e((string) ($post['title'] ?? 'Untitled')) ?></h2>
                    <div class="forum-meta">
                        Posted: <?= e((string) ($post['created_at'] ?? '-')) ?>
                    </div>
                    <div class="forum-body"><?= e((string) ($post['body'] ?? '')) ?></div>
                    <?php if (!empty($post['image_path'])): ?>
                        <img
                            src="<?= e((string) $post['image_path']) ?>"
                            alt="Forum post image"
                            class="forum-image"
                            loading="lazy"
                        >
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
