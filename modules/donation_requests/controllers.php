<?php

/**
 * Donation Requests Module - Controllers
 */

function donation_requests_general_create(): void
{
    $userId = (int) auth_id();
    $locations = donation_requests_list_safe_locations_for_general($userId);
    $profile = donation_requests_find_general_profile($userId) ?? [];

    view('donation_requests::general_create', [
        'breadcrumb' => 'Request a Donation',
        'locations' => $locations,
        'profile' => $profile,
    ], 'dashboard');
}

function donation_requests_general_store(): void
{
    csrf_check();

    $userId = (int) auth_id();
    $locationId = (int) request_input('safe_location_id', 0);

    if ($locationId <= 0) {
        flash('error', 'Please select a safe location before submitting the request.');
        flash_old_input();
        redirect('/donation-requests/create');
    }

    $location = donation_requests_find_safe_location_for_general($userId, $locationId);
    if (!$location) {
        flash('error', 'Selected safe location is not available for your GN division.');
        flash_old_input();
        redirect('/donation-requests/create');
    }

    $requestId = donation_requests_create_general_request($userId, $locationId);
    if ($requestId <= 0) {
        flash('error', 'Unable to submit donation request. Please try again.');
        flash_old_input();
        redirect('/donation-requests/create');
    }

    clear_old_input();
    flash('success', 'Donation request submitted successfully. Your GN officer can now gather requirements for this safe location.');
    redirect('/donation-requests/create');
}

function donation_requests_gn_index(): void
{
    $gnUserId = (int) auth_id();
    $locations = donation_requests_list_location_groups_for_gn($gnUserId);

    foreach ($locations as &$location) {
        $locationId = (int) ($location['location_id'] ?? 0);
        $location['pending_requests'] = donation_requests_list_pending_requests_by_location($locationId);
    }
    unset($location);

    view('donation_requests::gn_index', [
        'breadcrumb' => 'Donation Requests',
        'locations' => $locations,
    ], 'dashboard');
}

function donation_requests_gn_gather_form(string $locationId): void
{
    $gnUserId = (int) auth_id();
    $id = (int) $locationId;

    if ($id <= 0) {
        flash('error', 'Invalid safe location id.');
        redirect('/dashboard/gn/donation-requests');
    }

    $location = donation_requests_find_assigned_location_for_gn($gnUserId, $id);
    if (!$location) {
        abort(403, 'You can only gather requirements for your assigned safe locations.');
    }

    $pendingRequests = donation_requests_list_pending_requests_by_location($id);

    view('donation_requests::gn_gather', [
        'breadcrumb' => 'Gather Requirement',
        'location' => $location,
        'pending_requests' => $pendingRequests,
        'pack_definitions' => donation_requests_pack_definitions(),
        'additional_catalog' => donation_requests_additional_item_catalog(),
        'rainfall_snapshot' => forecast_fetch_river_rainfall_snapshot(),
    ], 'dashboard');
}

function donation_requests_gn_gather_store(string $locationId): void
{
    csrf_check();

    $gnUserId = (int) auth_id();
    $id = (int) $locationId;

    if ($id <= 0) {
        flash('error', 'Invalid safe location id.');
        redirect('/dashboard/gn/donation-requests');
    }

    $location = donation_requests_find_assigned_location_for_gn($gnUserId, $id);
    if (!$location) {
        abort(403, 'You can only gather requirements for your assigned safe locations.');
    }

    $contactNumber = trim((string) request_input('contact_number', ''));
    $situationDescription = trim((string) request_input('situation_description', ''));
    $specialNotes = trim((string) request_input('special_notes', ''));
    $daysCount = max(1, (int) request_input('days_count', 1));

    $packCounts = donation_requests_pack_counts_from_input((array) request_input('packs', []));
    $extras = request_input('extras', []);
    $extras = is_array($extras) ? $extras : [];

    if ($contactNumber === '') {
        flash('error', 'Contact number is required.');
        flash_old_input();
        redirect('/dashboard/gn/donation-requests/' . $id . '/gather');
    }

    if ($situationDescription === '') {
        flash('error', 'Situation description is required.');
        flash_old_input();
        redirect('/dashboard/gn/donation-requests/' . $id . '/gather');
    }

    if (!donation_requests_has_any_requirement_input($packCounts, $extras)) {
        flash('error', 'Add at least one pack count or additional item quantity.');
        flash_old_input();
        redirect('/dashboard/gn/donation-requests/' . $id . '/gather');
    }

    $items = donation_requests_compute_requirement_items($packCounts, $daysCount, $extras);
    if (empty($items)) {
        flash('error', 'Unable to compute requirement totals. Please review your input.');
        flash_old_input();
        redirect('/dashboard/gn/donation-requests/' . $id . '/gather');
    }

    $locationLabel = trim((string) ($location['district'] ?? '') . ' / ' . (string) ($location['gn_division'] ?? ''));

    try {
        donation_requests_create_requirement([
            'location_id' => $id,
            'gn_user_id' => $gnUserId,
            'relief_center_name' => (string) ($location['location_name'] ?? ''),
            'location_label' => $locationLabel,
            'contact_number' => $contactNumber,
            'situation_description' => $situationDescription,
            'special_notes' => $specialNotes,
            'days_count' => $daysCount,
            'packs_toddlers' => (int) $packCounts['toddlers'],
            'packs_children' => (int) $packCounts['children'],
            'packs_adults' => (int) $packCounts['adults'],
            'packs_elderly' => (int) $packCounts['elderly'],
            'packs_pregnant_women' => (int) $packCounts['pregnant_women'],
        ], $items);
    } catch (Throwable $e) {
        flash('error', 'Failed to save gathered requirements. Please try again.');
        flash_old_input();
        redirect('/dashboard/gn/donation-requests/' . $id . '/gather');
    }

    clear_old_input();
    flash('success', 'Requirements gathered successfully. Item-wise totals are now visible to DMC and NGOs.');
    redirect('/dashboard/gn/donation-requests');
}

function donation_requests_gn_mark_fulfilled(string $locationId): void
{
    csrf_check();

    $gnUserId = (int) auth_id();
    $id = (int) $locationId;

    if ($id <= 0) {
        flash('error', 'Invalid safe location id.');
        redirect('/dashboard/gn/donation-requests');
    }

    $location = donation_requests_find_assigned_location_for_gn($gnUserId, $id);
    if (!$location) {
        abort(403, 'You can only update assigned safe locations.');
    }

    if (!donation_requests_has_reserved_requirement_for_location($id)) {
        flash('error', 'This safe location has no NGO reservation yet. Wait for an NGO to reserve the request before marking fulfilled.');
        redirect('/dashboard/gn/donation-requests');
    }

    try {
        donation_requests_mark_location_fulfilled($id, $gnUserId);
    } catch (Throwable $e) {
        flash('error', 'Could not mark safe location as fulfilled.');
        redirect('/dashboard/gn/donation-requests');
    }

    flash('success', 'Safe location marked as fulfilled.');
    redirect('/dashboard/gn/donation-requests');
}

function donation_requests_feed_index(): void
{
    $requirements = donation_requests_list_requirement_feed_summary();

    view('donation_requests::feed', [
        'breadcrumb' => 'Donation Requirements Feed',
        'requirements' => $requirements,
    ], 'dashboard');
}

function donation_requests_feed_details(string $requirementId): void
{
    $id = (int) $requirementId;
    if ($id <= 0) {
        flash('error', 'Invalid donation request id.');
        redirect('/dashboard/donation-requirements');
    }

    $requirement = donation_requests_find_requirement_by_id($id);
    if (!$requirement) {
        flash('error', 'Donation request not found.');
        redirect('/dashboard/donation-requirements');
    }

    view('donation_requests::feed_detail', [
        'breadcrumb' => 'Donation Requirement Details',
        'requirement' => $requirement,
        'can_reserve' => is_role('ngo'),
    ], 'dashboard');
}

function donation_requests_feed_reserve(string $requirementId): void
{
    csrf_check();

    if (!is_role('ngo')) {
        abort(403, 'Only NGOs can reserve donation requests.');
    }

    $id = (int) $requirementId;
    if ($id <= 0) {
        flash('error', 'Invalid donation request id.');
        redirect('/dashboard/donation-requirements');
    }

    $result = donation_requests_reserve_requirement($id, (int) auth_id());
    if (!empty($result['ok'])) {
        flash('success', (string) ($result['message'] ?? 'Donation request reserved successfully.'));
    } else {
        flash('error', (string) ($result['message'] ?? 'Unable to reserve donation request.'));
    }

    redirect('/dashboard/donation-requirements/' . $id);
}
