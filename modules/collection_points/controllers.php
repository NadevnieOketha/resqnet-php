<?php

/**
 * Collection Points Module - Controllers
 */

function collection_points_ngo_index(): void
{
    $ngoUserId = (int) auth_id();
    $points = collection_points_list_for_ngo($ngoUserId);

    $editingId = (int) request_query('edit', 0);
    $editingPoint = null;
    if ($editingId > 0) {
        $editingPoint = collection_points_find_for_ngo($editingId, $ngoUserId);
        if (!$editingPoint) {
            flash('error', 'Collection point not found or access denied.');
            redirect('/dashboard/collection-points');
        }
    }

    view('collection_points::manage', [
        'breadcrumb' => 'Collection Points',
        'district_map' => collection_points_district_map(),
        'districts' => collection_points_district_list(),
        'collection_points' => $points,
        'editing_point' => $editingPoint,
    ], 'dashboard');
}

function collection_points_ngo_create_action(): void
{
    csrf_check();

    $ngoUserId = (int) auth_id();
    $resolved = collection_points_resolve_location_inputs($_POST);

    $payload = [
        'name' => trim((string) request_input('name', '')),
        'address_house_no' => trim((string) request_input('address_house_no', '')),
        'address_street' => trim((string) request_input('address_street', '')),
        'address_city' => trim((string) request_input('address_city', '')),
        'district' => $resolved['district'],
        'gn_division' => $resolved['gn_division'],
        'location_landmark' => trim((string) request_input('location_landmark', '')),
        'contact_person' => trim((string) request_input('contact_person', '')),
        'contact_number' => trim((string) request_input('contact_number', '')),
    ];

    $payload['full_address'] = collection_points_compose_full_address($payload);

    $errors = collection_points_validate_payload($payload);
    if (!empty($errors)) {
        flash('error', implode(' ', $errors));
        flash_old_input();
        redirect('/dashboard/collection-points');
    }

    collection_points_create($ngoUserId, $payload);

    clear_old_input();
    flash('success', 'Collection point added successfully.');
    redirect('/dashboard/collection-points');
}

function collection_points_ngo_update_action(string $collectionPointId): void
{
    csrf_check();

    $ngoUserId = (int) auth_id();
    $id = (int) $collectionPointId;
    if ($id <= 0) {
        flash('error', 'Invalid collection point id.');
        redirect('/dashboard/collection-points');
    }

    $point = collection_points_find_for_ngo($id, $ngoUserId);
    if (!$point) {
        flash('error', 'Collection point not found or access denied.');
        redirect('/dashboard/collection-points');
    }

    $resolved = collection_points_resolve_location_inputs($_POST);
    $payload = [
        'name' => trim((string) request_input('name', '')),
        'address_house_no' => trim((string) request_input('address_house_no', '')),
        'address_street' => trim((string) request_input('address_street', '')),
        'address_city' => trim((string) request_input('address_city', '')),
        'district' => $resolved['district'],
        'gn_division' => $resolved['gn_division'],
        'location_landmark' => trim((string) request_input('location_landmark', '')),
        'contact_person' => trim((string) request_input('contact_person', '')),
        'contact_number' => trim((string) request_input('contact_number', '')),
    ];

    $payload['full_address'] = collection_points_compose_full_address($payload);

    $errors = collection_points_validate_payload($payload);
    if (!empty($errors)) {
        flash('error', implode(' ', $errors));
        flash_old_input();
        redirect('/dashboard/collection-points?edit=' . $id);
    }

    $updated = collection_points_update_for_ngo($id, $ngoUserId, $payload);
    if ($updated > 0) {
        flash('success', 'Collection point updated successfully.');
    } else {
        flash('warning', 'No changes detected for the selected collection point.');
    }

    clear_old_input();
    redirect('/dashboard/collection-points');
}

function collection_points_ngo_delete_action(string $collectionPointId): void
{
    csrf_check();

    $ngoUserId = (int) auth_id();
    $id = (int) $collectionPointId;
    if ($id <= 0) {
        flash('error', 'Invalid collection point id.');
        redirect('/dashboard/collection-points');
    }

    $deleted = collection_points_delete_for_ngo($id, $ngoUserId);
    if ($deleted > 0) {
        flash('success', 'Collection point deleted successfully.');
    } else {
        flash('error', 'Collection point not found or access denied.');
    }

    redirect('/dashboard/collection-points');
}
