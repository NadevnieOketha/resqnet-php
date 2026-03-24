<?php

/**
 * Donations Module — Controllers
 */

function donations_public_index(): void
{
    try {
        $requests = donations_recent_requests(100);
    } catch (\PDOException) {
        $requests = [];
    }

    view('donations::public', ['requests' => $requests], 'main');
}

function donations_public_show(string $id): void
{
    try {
        $request = donations_find_request((int) $id);
        $contributions = donations_request_contributions((int) $id, 30);
    } catch (\PDOException) {
        abort(500, 'Database error. Please run database/schema.sql.');
    }

    if (!$request) {
        abort(404, 'Donation request not found.');
    }

    view('donations::show', [
        'request' => $request,
        'contributions' => $contributions,
    ], 'main');
}

function donations_contribute(string $id): void
{
    csrf_check();

    $request = donations_find_request((int) $id);
    if (!$request) {
        abort(404, 'Donation request not found.');
    }

    if ($request['status'] !== 'open') {
        flash('error', 'This donation request is not open for contributions.');
        redirect('/donations/' . $id);
    }

    $amount = (float) request_input('amount', 0);
    $message = trim(request_input('message', ''));

    $donorNameInput = trim(request_input('donor_name', ''));
    $donorEmailInput = trim(request_input('donor_email', ''));

    $donorName = auth_check() ? auth_user()['name'] : $donorNameInput;
    $donorEmail = auth_check() ? (auth_user()['email'] ?? null) : $donorEmailInput;

    $errors = [];
    if ($amount <= 0) $errors[] = 'Contribution amount must be greater than zero.';
    if ($donorName === '') $errors[] = 'Donor name is required for guest contributions.';
    if (!empty($donorEmail) && !filter_var($donorEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid donor email format.';
    }

    if (!empty($errors)) {
        flash('error', implode(' ', $errors));
        flash_old_input();
        redirect('/donations/' . $id);
    }

    donations_add_contribution((int) $id, [
        'donor_id' => auth_id(),
        'donor_name' => $donorName,
        'donor_email' => $donorEmail,
        'amount' => $amount,
        'message' => $message,
    ]);

    clear_old_input();
    flash('success', 'Thank you for your contribution.');
    redirect('/donations/' . $id);
}

function donations_manage_index(): void
{
    $ngoUserId = is_role('ngo') ? (int) auth_id() : null;
    $requests = donations_manage_requests($ngoUserId);

    view('donations::manage', ['requests' => $requests], 'dashboard');
}

function donations_create_form(): void
{
    $ngoUsers = is_role('dmc_admin') ? donations_ngo_users() : [];
    view('donations::create', ['ngoUsers' => $ngoUsers], 'dashboard');
}

function donations_store(): void
{
    csrf_check();

    $title = trim(request_input('title', ''));
    $description = trim(request_input('description', ''));
    $neededLocation = trim(request_input('needed_location', ''));
    $targetAmount = (float) request_input('target_amount', 0);
    $status = trim(request_input('status', 'open'));

    $assignedNgoInput = request_input('assigned_ngo', '');
    $assignedNgo = $assignedNgoInput === '' ? null : (int) $assignedNgoInput;

    if (is_role('ngo')) {
        $assignedNgo = (int) auth_id();
    }

    $errors = [];
    $allowedStatus = ['open', 'closed', 'fulfilled'];

    if ($title === '') $errors[] = 'Title is required.';
    if ($description === '') $errors[] = 'Description is required.';
    if ($neededLocation === '') $errors[] = 'Affected location is required.';
    if ($targetAmount <= 0) $errors[] = 'Target amount must be greater than zero.';
    if (!in_array($status, $allowedStatus, true)) $errors[] = 'Invalid status.';

    if (!empty($errors)) {
        flash('error', implode(' ', $errors));
        flash_old_input();
        redirect('/dashboard/donations/create');
    }

    donations_create_request([
        'title' => $title,
        'description' => $description,
        'needed_location' => $neededLocation,
        'target_amount' => $targetAmount,
        'status' => $status,
        'created_by' => auth_id(),
        'assigned_ngo' => $assignedNgo,
    ]);

    clear_old_input();
    flash('success', 'Donation appeal created successfully.');
    redirect('/dashboard/donations/manage');
}

function donations_edit_form(string $id): void
{
    $request = donations_find_request((int) $id);
    if (!$request) {
        abort(404, 'Donation request not found.');
    }

    if (!donations_can_manage_request($request)) {
        abort(403, 'You can only edit donation appeals assigned to you.');
    }

    $ngoUsers = is_role('dmc_admin') ? donations_ngo_users() : [];
    view('donations::edit', [
        'request' => $request,
        'ngoUsers' => $ngoUsers,
    ], 'dashboard');
}

function donations_update_action(string $id): void
{
    csrf_check();

    $request = donations_find_request((int) $id);
    if (!$request) {
        abort(404, 'Donation request not found.');
    }

    if (!donations_can_manage_request($request)) {
        abort(403, 'You can only update donation appeals assigned to you.');
    }

    $title = trim(request_input('title', ''));
    $description = trim(request_input('description', ''));
    $neededLocation = trim(request_input('needed_location', ''));
    $targetAmount = (float) request_input('target_amount', 0);
    $status = trim(request_input('status', 'open'));

    $assignedNgoInput = request_input('assigned_ngo', '');
    $assignedNgo = $assignedNgoInput === '' ? null : (int) $assignedNgoInput;
    if (is_role('ngo')) {
        $assignedNgo = (int) auth_id();
    }

    $errors = [];
    $allowedStatus = ['open', 'closed', 'fulfilled'];

    if ($title === '') $errors[] = 'Title is required.';
    if ($description === '') $errors[] = 'Description is required.';
    if ($neededLocation === '') $errors[] = 'Affected location is required.';
    if ($targetAmount <= 0) $errors[] = 'Target amount must be greater than zero.';
    if (!in_array($status, $allowedStatus, true)) $errors[] = 'Invalid status.';

    if (!empty($errors)) {
        flash('error', implode(' ', $errors));
        flash_old_input();
        redirect('/dashboard/donations/' . $id . '/edit');
    }

    donations_update_request((int) $id, [
        'title' => $title,
        'description' => $description,
        'needed_location' => $neededLocation,
        'target_amount' => $targetAmount,
        'status' => $status,
        'assigned_ngo' => $assignedNgo,
    ]);

    clear_old_input();
    flash('success', 'Donation appeal updated successfully.');
    redirect('/dashboard/donations/manage');
}

function donations_delete_action(string $id): void
{
    csrf_check();

    $request = donations_find_request((int) $id);
    if (!$request) {
        abort(404, 'Donation request not found.');
    }

    if (!donations_can_manage_request($request)) {
        abort(403, 'You can only delete donation appeals assigned to you.');
    }

    donations_delete_request((int) $id);
    flash('success', 'Donation appeal deleted successfully.');
    redirect('/dashboard/donations/manage');
}

function donations_can_manage_request(array $request): bool
{
    if (is_role('dmc_admin')) {
        return true;
    }

    if (is_role('ngo')) {
        $currentUserId = (int) auth_id();
        return (int) ($request['created_by'] ?? 0) === $currentUserId
            || (int) ($request['assigned_ngo'] ?? 0) === $currentUserId;
    }

    return false;
}
