<?php

/**
 * Safe Locations Module - Controllers
 */

function safe_locations_public_index(): void
{
    $district = trim((string) request_query('district', ''));
    $gnDivision = trim((string) request_query('gn_division', ''));
    $availableOnly = (string) request_query('available_only', '0') === '1';
    $hasExplicitFilter = array_key_exists('district', $_GET)
        || array_key_exists('gn_division', $_GET)
        || array_key_exists('available_only', $_GET);

    $defaultDistrict = '';
    $defaultGnDivision = '';

    if (auth_check()) {
        $role = (string) (user_role() ?? '');
        if (in_array($role, ['general', 'volunteer'], true)) {
            $profile = auth_get_profile((int) auth_id(), $role) ?? [];
            $defaultDistrict = trim((string) ($profile['district'] ?? ''));
            $defaultGnDivision = trim((string) ($profile['gn_division'] ?? ''));

            if (!$hasExplicitFilter && $district === '' && $gnDivision === '') {
                $district = $defaultDistrict;
                $gnDivision = $defaultGnDivision;
            }
        }
    }

    $locations = safe_locations_list_for_public(
        $district !== '' ? $district : null,
        $gnDivision !== '' ? $gnDivision : null,
        $availableOnly
    );

    $layout = auth_check() ? 'dashboard' : 'main';

    view('safe_locations::public_map', [
        'breadcrumb' => 'Safe Locations',
        'page_title' => 'Safe Locations',
        'district_map' => safe_locations_district_map(),
        'districts' => safe_locations_district_list(),
        'selected_district' => $district,
        'selected_gn_division' => $gnDivision,
        'available_only' => $availableOnly,
        'default_district' => $defaultDistrict,
        'default_gn_division' => $defaultGnDivision,
        'locations' => $locations,
    ], $layout);
}

function safe_locations_public_data(): void
{
    $district = trim((string) request_query('district', ''));
    $gnDivision = trim((string) request_query('gn_division', ''));
    $availableOnly = (string) request_query('available_only', '0') === '1';
    $hasExplicitFilter = array_key_exists('district', $_GET)
        || array_key_exists('gn_division', $_GET)
        || array_key_exists('available_only', $_GET);

    if (auth_check()) {
        $role = (string) (user_role() ?? '');
        if (in_array($role, ['general', 'volunteer'], true) && !$hasExplicitFilter && $district === '' && $gnDivision === '') {
            $profile = auth_get_profile((int) auth_id(), $role) ?? [];
            $district = trim((string) ($profile['district'] ?? ''));
            $gnDivision = trim((string) ($profile['gn_division'] ?? ''));
        }
    }

    $locations = safe_locations_list_for_public(
        $district !== '' ? $district : null,
        $gnDivision !== '' ? $gnDivision : null,
        $availableOnly
    );

    foreach ($locations as &$location) {
        $location['address_line'] = safe_locations_address_line($location);
    }
    unset($location);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'locations' => $locations,
        'count' => count($locations),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function safe_locations_dmc_manage_index(): void
{
    $locations = safe_locations_list_for_dmc();
    foreach ($locations as &$location) {
        $location['address_line'] = safe_locations_address_line($location);
    }
    unset($location);

    view('safe_locations::dmc_manage', [
        'breadcrumb' => 'Safe Locations',
        'districts' => safe_locations_district_list(),
        'district_map' => safe_locations_district_map(),
        'gn_officers' => safe_locations_list_gn_officers(),
        'locations' => $locations,
    ], 'dashboard');
}

function safe_locations_dmc_create_action(): void
{
    csrf_check();

    $locationName = trim((string) request_input('location_name', ''));
    $houseNo = trim((string) request_input('address_house_no', ''));
    $street = trim((string) request_input('address_street', ''));
    $city = trim((string) request_input('address_city', ''));

    $district = trim((string) request_input('district', ''));
    $districtOther = trim((string) request_input('district_other', ''));
    $gnDivision = trim((string) request_input('gn_division', ''));
    $gnOther = trim((string) request_input('gn_division_other', ''));

    if ($district === '__other__') {
        $district = $districtOther;
    }
    if ($gnDivision === '__other__') {
        $gnDivision = $gnOther;
    }

    $latitude = trim((string) request_input('latitude', ''));
    $longitude = trim((string) request_input('longitude', ''));
    $maxCapacity = (int) request_input('max_capacity', 0);
    $assignedGnUserId = (int) request_input('assigned_gn_user_id', 0);

    $errors = safe_locations_validate_location_payload([
        'location_name' => $locationName,
        'address_street' => $street,
        'address_city' => $city,
        'district' => $district,
        'gn_division' => $gnDivision,
        'latitude' => $latitude,
        'longitude' => $longitude,
        'max_capacity' => $maxCapacity,
        'assigned_gn_user_id' => $assignedGnUserId,
    ]);

    if (!empty($errors)) {
        flash('error', implode(' ', $errors));
        flash_old_input();
        redirect('/dashboard/admin/safe-locations');
    }

    safe_locations_create([
        'location_name' => $locationName,
        'address_house_no' => $houseNo,
        'address_street' => $street,
        'address_city' => $city,
        'district' => $district,
        'gn_division' => $gnDivision,
        'latitude' => (float) $latitude,
        'longitude' => (float) $longitude,
        'max_capacity' => $maxCapacity,
        'assigned_gn_user_id' => $assignedGnUserId,
        'updated_by_user_id' => (int) auth_id(),
    ]);

    clear_old_input();
    flash('success', 'Safe location added successfully.');
    redirect('/dashboard/admin/safe-locations');
}

function safe_locations_dmc_update_action(string $locationId): void
{
    csrf_check();

    $id = (int) $locationId;
    if ($id <= 0) {
        flash('error', 'Invalid safe location id.');
        redirect('/dashboard/admin/safe-locations');
    }

    $locationName = trim((string) request_input('location_name', ''));
    $houseNo = trim((string) request_input('address_house_no', ''));
    $street = trim((string) request_input('address_street', ''));
    $city = trim((string) request_input('address_city', ''));
    $district = trim((string) request_input('district', ''));
    $gnDivision = trim((string) request_input('gn_division', ''));
    $latitude = trim((string) request_input('latitude', ''));
    $longitude = trim((string) request_input('longitude', ''));
    $maxCapacity = (int) request_input('max_capacity', 0);
    $assignedGnUserId = (int) request_input('assigned_gn_user_id', 0);

    $errors = safe_locations_validate_location_payload([
        'location_name' => $locationName,
        'address_street' => $street,
        'address_city' => $city,
        'district' => $district,
        'gn_division' => $gnDivision,
        'latitude' => $latitude,
        'longitude' => $longitude,
        'max_capacity' => $maxCapacity,
        'assigned_gn_user_id' => $assignedGnUserId,
    ]);

    if (!empty($errors)) {
        flash('error', implode(' ', $errors));
        redirect('/dashboard/admin/safe-locations');
    }

    $existingLocation = safe_locations_find_by_id($id);
    if (!$existingLocation) {
        flash('error', 'Safe location not found.');
        redirect('/dashboard/admin/safe-locations');
    }

    if ($maxCapacity < (int) ($existingLocation['current_occupancy'] ?? 0)) {
        flash('error', 'Maximum capacity cannot be lower than current occupancy.');
        redirect('/dashboard/admin/safe-locations');
    }

    $updated = safe_locations_update($id, [
        'location_name' => $locationName,
        'address_house_no' => $houseNo,
        'address_street' => $street,
        'address_city' => $city,
        'district' => $district,
        'gn_division' => $gnDivision,
        'latitude' => (float) $latitude,
        'longitude' => (float) $longitude,
        'max_capacity' => $maxCapacity,
        'assigned_gn_user_id' => $assignedGnUserId,
    ]);

    if ($updated > 0) {
        flash('success', 'Safe location updated successfully.');
    } else {
        flash('warning', 'No changes detected for the selected safe location.');
    }

    redirect('/dashboard/admin/safe-locations');
}

function safe_locations_dmc_delete_action(string $locationId): void
{
    csrf_check();

    $id = (int) $locationId;
    if ($id <= 0) {
        flash('error', 'Invalid safe location id.');
        redirect('/dashboard/admin/safe-locations');
    }

    $deleted = safe_locations_delete($id);
    if ($deleted > 0) {
        flash('success', 'Safe location removed successfully.');
    } else {
        flash('warning', 'Safe location was not found or already removed.');
    }

    redirect('/dashboard/admin/safe-locations');
}

function safe_locations_gn_index(): void
{
    $gnUserId = (int) auth_id();
    $locations = safe_locations_list_for_gn($gnUserId);

    view('safe_locations::gn_manage', [
        'breadcrumb' => 'Safe Locations',
        'locations' => $locations,
    ], 'dashboard');
}

function safe_locations_gn_update_occupancy_action(string $locationId): void
{
    csrf_check();

    $id = (int) $locationId;
    $gnUserId = (int) auth_id();

    if ($id <= 0) {
        flash('error', 'Invalid safe location id.');
        redirect('/dashboard/safe-locations');
    }

    if (!safe_locations_is_assigned_to_gn($id, $gnUserId)) {
        abort(403, 'You can only update occupancy for your assigned locations.');
    }

    $counts = [];
    foreach (safe_locations_occupancy_categories() as $category) {
        $rawValue = request_input($category, 0);
        $stringValue = trim((string) $rawValue);

        if ($stringValue === '' || !preg_match('/^\d+$/', $stringValue)) {
            flash('error', 'Occupancy values must be non-negative whole numbers.');
            redirect('/dashboard/safe-locations');
        }

        $counts[$category] = (int) $stringValue;
    }

    $result = safe_locations_upsert_occupancy($id, $counts, $gnUserId);
    if (!$result['ok']) {
        flash('error', (string) ($result['message'] ?? 'Unable to update occupancy.'));
        redirect('/dashboard/safe-locations');
    }

    flash('success', (string) ($result['message'] ?? 'Occupancy updated successfully.'));
    redirect('/dashboard/safe-locations');
}

function safe_locations_validate_location_payload(array $payload): array
{
    $errors = [];

    if (trim((string) ($payload['location_name'] ?? '')) === '') {
        $errors[] = 'Location name is required.';
    }

    if (trim((string) ($payload['address_street'] ?? '')) === '') {
        $errors[] = 'Street is required.';
    }

    if (trim((string) ($payload['address_city'] ?? '')) === '') {
        $errors[] = 'City is required.';
    }

    if (trim((string) ($payload['district'] ?? '')) === '') {
        $errors[] = 'District is required.';
    }

    if (trim((string) ($payload['gn_division'] ?? '')) === '') {
        $errors[] = 'GN division is required.';
    }

    $latitude = trim((string) ($payload['latitude'] ?? ''));
    $longitude = trim((string) ($payload['longitude'] ?? ''));

    if (!is_numeric($latitude) || (float) $latitude < -90 || (float) $latitude > 90) {
        $errors[] = 'Latitude must be between -90 and 90.';
    }

    if (!is_numeric($longitude) || (float) $longitude < -180 || (float) $longitude > 180) {
        $errors[] = 'Longitude must be between -180 and 180.';
    }

    $maxCapacity = (int) ($payload['max_capacity'] ?? 0);
    if ($maxCapacity <= 0) {
        $errors[] = 'Maximum capacity must be greater than zero.';
    }

    $assignedGnUserId = (int) ($payload['assigned_gn_user_id'] ?? 0);
    if ($assignedGnUserId <= 0 || !safe_locations_find_gn_officer($assignedGnUserId)) {
        $errors[] = 'Please assign a valid Grama Niladhari officer.';
    }

    return $errors;
}
