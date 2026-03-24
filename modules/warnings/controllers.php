<?php

/**
 * Warnings Module — Controllers
 */

function warnings_public_index(): void
{
    try {
        $warnings = warnings_all('published');
    } catch (\PDOException) {
        $warnings = [];
    }

    view('warnings::public', ['warnings' => $warnings], 'main');
}

function warnings_manage_index(): void
{
    try {
        $warnings = warnings_all();
    } catch (\PDOException) {
        $warnings = [];
    }

    view('warnings::manage', ['warnings' => $warnings], 'dashboard');
}

function warnings_create_form(): void
{
    view('warnings::create', [], 'dashboard');
}

function warnings_store(): void
{
    csrf_check();

    $title = trim(request_input('title', ''));
    $message = trim(request_input('message', ''));
    $location = trim(request_input('location', ''));
    $severity = trim(request_input('severity', 'medium'));
    $status = trim(request_input('status', 'draft'));

    $errors = [];
    $allowedSeverity = ['low', 'medium', 'high', 'critical'];
    $allowedStatus = ['draft', 'published'];

    if ($title === '') $errors[] = 'Warning title is required.';
    if ($message === '') $errors[] = 'Warning message is required.';
    if ($location === '') $errors[] = 'Location is required.';
    if (!in_array($severity, $allowedSeverity, true)) $errors[] = 'Invalid severity.';
    if (!in_array($status, $allowedStatus, true)) $errors[] = 'Invalid status.';

    if (!empty($errors)) {
        flash('error', implode(' ', $errors));
        flash_old_input();
        redirect('/dashboard/warnings/create');
    }

    warnings_create([
        'title' => $title,
        'message' => $message,
        'location' => $location,
        'severity' => $severity,
        'status' => $status,
        'issued_by' => auth_id(),
        'issued_at' => $status === 'published' ? date('Y-m-d H:i:s') : null,
    ]);

    clear_old_input();
    flash('success', 'Warning created successfully.');
    redirect('/dashboard/warnings');
}

function warnings_edit_form(string $id): void
{
    $warning = warnings_find((int) $id);
    if (!$warning) {
        abort(404, 'Warning not found.');
    }

    if (is_role('grama_niladhari') && (int) $warning['issued_by'] !== (int) auth_id()) {
        abort(403, 'You can edit only warnings issued by you.');
    }

    view('warnings::edit', ['warning' => $warning], 'dashboard');
}

function warnings_update_action(string $id): void
{
    csrf_check();

    $warning = warnings_find((int) $id);
    if (!$warning) {
        abort(404, 'Warning not found.');
    }

    if (is_role('grama_niladhari') && (int) $warning['issued_by'] !== (int) auth_id()) {
        abort(403, 'You can edit only warnings issued by you.');
    }

    $title = trim(request_input('title', ''));
    $message = trim(request_input('message', ''));
    $location = trim(request_input('location', ''));
    $severity = trim(request_input('severity', 'medium'));
    $status = trim(request_input('status', 'draft'));

    $errors = [];
    $allowedSeverity = ['low', 'medium', 'high', 'critical'];
    $allowedStatus = ['draft', 'published'];

    if ($title === '') $errors[] = 'Warning title is required.';
    if ($message === '') $errors[] = 'Warning message is required.';
    if ($location === '') $errors[] = 'Location is required.';
    if (!in_array($severity, $allowedSeverity, true)) $errors[] = 'Invalid severity.';
    if (!in_array($status, $allowedStatus, true)) $errors[] = 'Invalid status.';

    if (!empty($errors)) {
        flash('error', implode(' ', $errors));
        flash_old_input();
        redirect('/dashboard/warnings/' . $id . '/edit');
    }

    $issuedAt = $warning['issued_at'];
    if ($status === 'published' && empty($issuedAt)) {
        $issuedAt = date('Y-m-d H:i:s');
    }

    warnings_update_data((int) $id, [
        'title' => $title,
        'message' => $message,
        'location' => $location,
        'severity' => $severity,
        'status' => $status,
        'issued_at' => $issuedAt,
    ]);

    clear_old_input();
    flash('success', 'Warning updated successfully.');
    redirect('/dashboard/warnings');
}

function warnings_delete_action(string $id): void
{
    csrf_check();

    $warning = warnings_find((int) $id);
    if (!$warning) {
        abort(404, 'Warning not found.');
    }

    if (is_role('grama_niladhari') && (int) $warning['issued_by'] !== (int) auth_id()) {
        abort(403, 'You can delete only warnings issued by you.');
    }

    warnings_delete_by_id((int) $id);

    flash('success', 'Warning deleted successfully.');
    redirect('/dashboard/warnings');
}
