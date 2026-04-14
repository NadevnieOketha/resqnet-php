<?php

/**
 * Forum Module - Models
 */

function forum_ensure_schema(): void
{
    static $ensured = false;
    if ($ensured) {
        return;
    }

    db_query(
        "CREATE TABLE IF NOT EXISTS forum_posts (
            post_id INT NOT NULL AUTO_INCREMENT,
            title VARCHAR(180) NOT NULL,
            body TEXT NOT NULL,
            image_path VARCHAR(255) DEFAULT NULL,
            created_by_user_id INT DEFAULT NULL,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (post_id),
            KEY idx_forum_posts_created_at (created_at),
            KEY idx_forum_posts_created_by (created_by_user_id),
            CONSTRAINT fk_forum_posts_user FOREIGN KEY (created_by_user_id) REFERENCES users(user_id) ON DELETE SET NULL
        ) ENGINE=InnoDB"
    );

    $ensured = true;
}

function forum_list_all_posts(): array
{
    forum_ensure_schema();

    return db_fetch_all(
        "SELECT fp.post_id,
                fp.title,
                fp.body,
                fp.image_path,
                fp.created_by_user_id,
                fp.created_at,
                fp.updated_at,
                COALESCE(u.username, 'DMC Officer') AS author_name
         FROM forum_posts fp
         LEFT JOIN users u ON u.user_id = fp.created_by_user_id
         ORDER BY fp.created_at DESC, fp.post_id DESC"
    );
}

function forum_find_post_by_id(int $postId): ?array
{
    if ($postId <= 0) {
        return null;
    }

    forum_ensure_schema();

    return db_fetch(
        'SELECT post_id, title, body, image_path, created_by_user_id, created_at, updated_at
         FROM forum_posts
         WHERE post_id = ?
         LIMIT 1',
        [$postId]
    );
}

function forum_create_post(array $data): int
{
    forum_ensure_schema();

    return (int) db_insert('forum_posts', [
        'title' => (string) ($data['title'] ?? ''),
        'body' => (string) ($data['body'] ?? ''),
        'image_path' => ($data['image_path'] ?? '') !== '' ? (string) ($data['image_path'] ?? '') : null,
        'created_by_user_id' => (int) ($data['created_by_user_id'] ?? 0) > 0 ? (int) ($data['created_by_user_id'] ?? 0) : null,
    ]);
}

function forum_update_post(int $postId, array $data): int
{
    if ($postId <= 0) {
        return 0;
    }

    forum_ensure_schema();

    return db_query(
        'UPDATE forum_posts
         SET title = ?,
             body = ?,
             image_path = ?
         WHERE post_id = ?
         LIMIT 1',
        [
            (string) ($data['title'] ?? ''),
            (string) ($data['body'] ?? ''),
            ($data['image_path'] ?? '') !== '' ? (string) ($data['image_path'] ?? '') : null,
            $postId,
        ]
    )->rowCount();
}

function forum_delete_post(int $postId): int
{
    if ($postId <= 0) {
        return 0;
    }

    forum_ensure_schema();

    return db_query(
        'DELETE FROM forum_posts WHERE post_id = ? LIMIT 1',
        [$postId]
    )->rowCount();
}
