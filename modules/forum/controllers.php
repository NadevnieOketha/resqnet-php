<?php

/**
 * Forum Module - Controllers
 */

function forum_public_index(): void
{
    $posts = forum_list_all_posts();

    view('forum::public', [
        'page_title' => 'Forum',
        'breadcrumb' => 'Forum',
        'posts' => $posts,
    ], 'main');
}

function forum_dashboard_index(): void
{
    $posts = forum_list_all_posts();

    view('forum::public', [
        'page_title' => 'Forum',
        'breadcrumb' => 'Forum',
        'posts' => $posts,
    ], 'dashboard');
}

function forum_dmc_manage_index(): void
{
    $posts = forum_list_all_posts();

    view('forum::manage', [
        'breadcrumb' => 'Forum Posts',
        'posts' => $posts,
    ], 'dashboard');
}

function forum_dmc_create_action(): void
{
    csrf_check();

    $title = trim((string) request_input('title', ''));
    $body = trim((string) request_input('body', ''));

    $errors = forum_validate_payload($title, $body);
    $upload = forum_handle_image_upload('image');
    if (!empty($upload['error'])) {
        $errors[] = (string) $upload['error'];
    }

    if (!empty($errors)) {
        flash('error', implode(' ', $errors));
        flash_old_input();
        redirect('/dashboard/admin/forum-posts');
    }

    forum_create_post([
        'title' => $title,
        'body' => $body,
        'image_path' => (string) ($upload['path'] ?? ''),
        'created_by_user_id' => (int) auth_id(),
    ]);

    clear_old_input();
    flash('success', 'Forum post created and published.');
    redirect('/dashboard/admin/forum-posts');
}

function forum_dmc_update_action(string $postId): void
{
    csrf_check();

    $id = (int) $postId;
    if ($id <= 0) {
        flash('error', 'Invalid forum post id.');
        redirect('/dashboard/admin/forum-posts');
    }

    $existing = forum_find_post_by_id($id);
    if (!$existing) {
        flash('error', 'Forum post not found.');
        redirect('/dashboard/admin/forum-posts');
    }

    $title = trim((string) request_input('title', ''));
    $body = trim((string) request_input('body', ''));
    $removeImage = (string) request_input('remove_image', '0') === '1';

    $errors = forum_validate_payload($title, $body);
    $upload = forum_handle_image_upload('image');
    if (!empty($upload['error'])) {
        $errors[] = (string) $upload['error'];
    }

    if (!empty($errors)) {
        flash('error', implode(' ', $errors));
        redirect('/dashboard/admin/forum-posts');
    }

    $newImagePath = (string) ($existing['image_path'] ?? '');
    $uploadedImagePath = (string) ($upload['path'] ?? '');

    if ($uploadedImagePath !== '') {
        $newImagePath = $uploadedImagePath;
    } elseif ($removeImage) {
        $newImagePath = '';
    }

    forum_update_post($id, [
        'title' => $title,
        'body' => $body,
        'image_path' => $newImagePath,
    ]);

    $oldImagePath = (string) ($existing['image_path'] ?? '');
    if ($uploadedImagePath !== '' && $oldImagePath !== '' && $oldImagePath !== $uploadedImagePath) {
        forum_delete_uploaded_image($oldImagePath);
    }
    if ($removeImage && $oldImagePath !== '' && $uploadedImagePath === '') {
        forum_delete_uploaded_image($oldImagePath);
    }

    flash('success', 'Forum post updated.');
    redirect('/dashboard/admin/forum-posts');
}

function forum_dmc_delete_action(string $postId): void
{
    csrf_check();

    $id = (int) $postId;
    if ($id <= 0) {
        flash('error', 'Invalid forum post id.');
        redirect('/dashboard/admin/forum-posts');
    }

    $existing = forum_find_post_by_id($id);
    if (!$existing) {
        flash('error', 'Forum post not found.');
        redirect('/dashboard/admin/forum-posts');
    }

    $deleted = forum_delete_post($id);
    if ($deleted > 0) {
        $imagePath = (string) ($existing['image_path'] ?? '');
        if ($imagePath !== '') {
            forum_delete_uploaded_image($imagePath);
        }
        flash('success', 'Forum post deleted.');
    } else {
        flash('warning', 'No changes were made.');
    }

    redirect('/dashboard/admin/forum-posts');
}

function forum_validate_payload(string $title, string $body): array
{
    $errors = [];

    if ($title === '') {
        $errors[] = 'Title is required.';
    }
    if (mb_strlen($title) > 180) {
        $errors[] = 'Title must be 180 characters or fewer.';
    }

    if ($body === '') {
        $errors[] = 'Body is required.';
    }

    return $errors;
}

function forum_handle_image_upload(string $fieldName): array
{
    if (!isset($_FILES[$fieldName]) || !is_array($_FILES[$fieldName])) {
        return ['path' => ''];
    }

    $file = $_FILES[$fieldName];
    $errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($errorCode === UPLOAD_ERR_NO_FILE) {
        return ['path' => ''];
    }

    if ($errorCode !== UPLOAD_ERR_OK) {
        return ['error' => 'Image upload failed. Please try again.'];
    }

    $maxBytes = 10 * 1024 * 1024;
    $size = (int) ($file['size'] ?? 0);
    if ($size <= 0 || $size > $maxBytes) {
        return ['error' => 'Image must be 10 MB or smaller.'];
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');
    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        return ['error' => 'Invalid uploaded file.'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = $finfo ? (string) finfo_file($finfo, $tmpName) : '';
    if ($finfo) {
        finfo_close($finfo);
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    if (!isset($allowed[$mime])) {
        return ['error' => 'Only JPG, PNG, WEBP, and GIF images are allowed.'];
    }

    $uploadDir = BASE_PATH . '/public/uploads/forum';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
        return ['error' => 'Unable to create upload directory.'];
    }

    $filename = 'forum_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $allowed[$mime];
    $targetPath = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($tmpName, $targetPath)) {
        return ['error' => 'Failed to save uploaded image.'];
    }

    return ['path' => '/uploads/forum/' . $filename];
}

function forum_delete_uploaded_image(string $imagePath): void
{
    $relative = trim($imagePath);
    if ($relative === '' || !str_starts_with($relative, '/uploads/forum/')) {
        return;
    }

    $absolute = BASE_PATH . '/public' . $relative;
    if (is_file($absolute)) {
        @unlink($absolute);
    }
}
