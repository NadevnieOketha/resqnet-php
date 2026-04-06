<?php

/**
 * Make a Donation Module - Controllers
 */

function donations_current_submission_role(): string
{
    if (!auth_check()) {
        return 'guest';
    }

    $role = (string) (user_role() ?? '');
    if (in_array($role, ['general', 'volunteer'], true)) {
        return $role;
    }

    return 'guest';
}

function donations_validate_date(string $date): bool
{
    $dt = DateTime::createFromFormat('Y-m-d', $date);
    if (!$dt || $dt->format('Y-m-d') !== $date) {
        return false;
    }

    $today = new DateTime('today');
    return $dt >= $today;
}

function donations_build_guest_manage_link(string $token): string
{
    return base_url('/donations/guest/' . rawurlencode($token));
}

function donations_send_guest_confirmation_email(array $donation, string $token): bool
{
    $email = trim((string) ($donation['email'] ?? ''));
    if ($email === '') {
        return false;
    }

    $link = donations_build_guest_manage_link($token);
    $subject = 'ResQnet Donation Confirmation';

    $html = '<p>Thank you for supporting disaster relief through ResQnet.</p>'
        . '<p>Your donation reference is <strong>#' . (int) ($donation['donation_id'] ?? 0) . '</strong>.</p>'
        . '<p>You can track status or cancel this donation (while pending) using this link:</p>'
        . '<p><a href="' . e($link) . '">' . e($link) . '</a></p>'
        . '<p>Collection Point: ' . e((string) ($donation['collection_point_name'] ?? '-')) . '</p>'
        . '<p>Requested Date: ' . e((string) ($donation['collection_date'] ?? '-')) . ' (' . e((string) ($donation['time_slot'] ?? '-')) . ')</p>';

    $text = "Thank you for your donation.\n"
        . 'Reference: #' . (int) ($donation['donation_id'] ?? 0) . "\n"
        . 'Track or cancel: ' . $link . "\n"
        . 'Collection Point: ' . (string) ($donation['collection_point_name'] ?? '-') . "\n";

    return mail_send($email, $subject, $html, $text);
}

function donations_send_guest_delivered_email(array $donation): bool
{
    $email = trim((string) ($donation['email'] ?? ''));
    if ($email === '') {
        return false;
    }

    $token = trim((string) ($donation['public_access_token'] ?? ''));
    $link = $token !== '' ? donations_build_guest_manage_link($token) : base_url('/make-donation');

    $subject = 'ResQnet Donation Delivered';
    $html = '<p>Your donation has been marked as delivered by the receiving NGO.</p>'
        . '<p>Reference: <strong>#' . (int) ($donation['donation_id'] ?? 0) . '</strong></p>'
        . '<p>Collection Point: ' . e((string) ($donation['collection_point_name'] ?? '-')) . '</p>'
        . '<p>You can still view your donation details here:</p>'
        . '<p><a href="' . e($link) . '">' . e($link) . '</a></p>';

    $text = "Your donation has been marked as delivered.\n"
        . 'Reference: #' . (int) ($donation['donation_id'] ?? 0) . "\n"
        . 'Details: ' . $link;

    return mail_send($email, $subject, $html, $text);
}

function donations_make_form(): void
{
    $submitRole = donations_current_submission_role();
    $isLoggedDonor = auth_check() && in_array($submitRole, ['general', 'volunteer'], true);

    if (auth_check() && !$isLoggedDonor) {
        abort(403, 'Only General users, Volunteers, or guests can submit donations.');
    }

    $districtMap = (array) config('auth_options.gn_divisions', []);
    $districts = (array) config('auth_options.districts', []);

    $selectedDistrict = '';
    $selectedGn = '';
    $defaults = [
        'name' => '',
        'contact_number' => '',
        'email' => '',
        'address' => '',
        'district' => '',
        'gn_division' => '',
    ];

    if ($isLoggedDonor) {
        $defaults = donations_logged_donor_defaults((int) auth_id(), $submitRole);
        $selectedDistrict = (string) ($defaults['district'] ?? '');
        $selectedGn = (string) ($defaults['gn_division'] ?? '');
    } else {
        $selectedDistrict = trim((string) request_query('district', (string) ($_SESSION['_old_input']['district'] ?? '')));
        $selectedGn = trim((string) request_query('gn_division', (string) ($_SESSION['_old_input']['gn_division'] ?? '')));
    }

    $collectionPoints = [];
    if ($isLoggedDonor) {
        if ($selectedDistrict !== '' && $selectedGn !== '') {
            $collectionPoints = donations_list_collection_points($selectedDistrict, $selectedGn);
        }
    } elseif ($selectedDistrict !== '' && $selectedGn !== '') {
        $collectionPoints = donations_list_collection_points($selectedDistrict, $selectedGn);
    }

    $layout = $isLoggedDonor ? 'dashboard' : 'main';

    view('donations::make_form', [
        'breadcrumb' => 'Make a Donation',
        'submit_role' => $submitRole,
        'is_logged_donor' => $isLoggedDonor,
        'defaults' => $defaults,
        'districts' => $districts,
        'district_map' => $districtMap,
        'selected_district' => $selectedDistrict,
        'selected_gn' => $selectedGn,
        'location_profile_complete' => ($selectedDistrict !== '' && $selectedGn !== ''),
        'collection_points' => $collectionPoints,
        'catalog_grouped' => donations_catalog_grouped(),
        'time_slots' => donations_time_slots(),
    ], $layout);
}

function donations_store(): void
{
    csrf_check();

    $submitRole = donations_current_submission_role();
    $isLoggedDonor = auth_check() && in_array($submitRole, ['general', 'volunteer'], true);

    if (auth_check() && !$isLoggedDonor) {
        abort(403, 'Only General users, Volunteers, or guests can submit donations.');
    }

    $redirectUrl = '/make-donation';

    $district = trim((string) request_input('district', ''));
    $gnDivision = trim((string) request_input('gn_division', ''));
    $errors = [];

    $defaults = [
        'name' => '',
        'contact_number' => '',
        'email' => '',
        'address' => '',
        'district' => '',
        'gn_division' => '',
    ];

    if ($isLoggedDonor) {
        $defaults = donations_logged_donor_defaults((int) auth_id(), $submitRole);
        $district = trim((string) ($defaults['district'] ?? ''));
        $gnDivision = trim((string) ($defaults['gn_division'] ?? ''));
        $redirectUrl = '/make-donation';

        if ($district === '' || $gnDivision === '') {
            $errors[] = 'Update your profile district and GN division before submitting a donation.';
        }
    } else {
        if ($district !== '' || $gnDivision !== '') {
            $redirectUrl .= '?district=' . rawurlencode($district) . '&gn_division=' . rawurlencode($gnDivision);
        }
    }

    $collectionPointId = (int) request_input('collection_point_id', 0);
    $name = trim((string) request_input('name', (string) ($defaults['name'] ?? '')));
    $contactNumber = trim((string) request_input('contact_number', (string) ($defaults['contact_number'] ?? '')));
    $email = trim((string) request_input('email', (string) ($defaults['email'] ?? '')));
    $address = trim((string) request_input('address', (string) ($defaults['address'] ?? '')));
    $collectionDate = trim((string) request_input('collection_date', ''));
    $timeSlot = trim((string) request_input('time_slot', ''));
    $specialNotes = trim((string) request_input('special_notes', ''));
    $confirmed = request_input('confirmation', '') === '1';

    if (!$isLoggedDonor) {
        if ($district === '') {
            $errors[] = 'District is required for guest donations.';
        }

        if ($gnDivision === '') {
            $errors[] = 'GN Division is required for guest donations.';
        }
    }

    if ($collectionPointId <= 0) {
        $errors[] = 'Please select a collection point.';
    }

    if ($name === '') {
        $errors[] = 'Name is required.';
    }

    if ($contactNumber === '') {
        $errors[] = 'Contact number is required.';
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email is required.';
    }

    if ($address === '') {
        $errors[] = 'Address is required.';
    }

    if (!donations_validate_date($collectionDate)) {
        $errors[] = 'Collection date must be today or a future date.';
    }

    if (!in_array($timeSlot, donations_time_slots(), true)) {
        $errors[] = 'Please choose a valid pickup time slot.';
    }

    if (!$confirmed) {
        $errors[] = 'You must confirm the donation details before submitting.';
    }

    $point = null;
    if ($collectionPointId > 0) {
        $point = donations_find_collection_point_by_id($collectionPointId);
        if (!$point) {
            $errors[] = 'Selected collection point is not valid.';
        } else {
            if ($district !== '' && strcasecmp((string) ($point['district'] ?? ''), $district) !== 0) {
                $errors[] = 'Selected collection point does not match the chosen district.';
            }
            if ($gnDivision !== '' && strcasecmp((string) ($point['gn_division'] ?? ''), $gnDivision) !== 0) {
                $errors[] = 'Selected collection point does not match the chosen GN Division.';
            }
        }
    }

    $rawItems = request_input('items', []);
    $rawItems = is_array($rawItems) ? $rawItems : [];

    $catalogById = donations_catalog_items_indexed();
    $itemQuantities = [];

    foreach ($rawItems as $itemId => $qtyRaw) {
        $itemId = (int) $itemId;
        $qty = (int) $qtyRaw;

        if ($itemId <= 0 || $qty <= 0) {
            continue;
        }

        if (!isset($catalogById[$itemId])) {
            continue;
        }

        $itemQuantities[$itemId] = $qty;
    }

    if (empty($itemQuantities)) {
        $errors[] = 'Add at least one donation item with quantity.';
    }

    if (!empty($errors)) {
        flash('error', implode(' ', $errors));
        flash_old_input();
        redirect($redirectUrl);
    }

    $authId = auth_check() ? (int) auth_id() : null;
    $publicToken = $submitRole === 'guest' ? bin2hex(random_bytes(24)) : null;

    $payload = [
        'user_id' => $submitRole === 'general' ? $authId : null,
        'submitted_by_user_id' => $isLoggedDonor ? $authId : null,
        'submitted_by_role' => $submitRole,
        'public_access_token' => $publicToken,
        'collection_point_id' => $collectionPointId,
        'name' => $name,
        'contact_number' => $contactNumber,
        'email' => $email,
        'address' => $address,
        'collection_date' => $collectionDate,
        'time_slot' => $timeSlot,
        'special_notes' => $specialNotes,
    ];

    try {
        $created = donations_create($payload, $itemQuantities);
    } catch (Throwable $e) {
        flash('error', 'Unable to submit donation right now. Please try again.');
        flash_old_input();
        redirect($redirectUrl);
    }

    clear_old_input();

    if ($submitRole === 'guest') {
        $token = (string) ($created['public_access_token'] ?? '');
        $donation = $token !== '' ? donations_find_by_public_token($token) : null;
        $emailSent = $donation ? donations_send_guest_confirmation_email($donation, $token) : false;

        flash('success', $emailSent
            ? 'Donation submitted. We sent a confirmation email with your tracking and cancellation link.'
            : 'Donation submitted. Save your tracking link to view or cancel while pending.');

        redirect('/donations/guest/' . rawurlencode($token));
    }

    flash('success', 'Donation submitted successfully. You can track it from My Donations.');
    redirect('/dashboard/my-donations');
}

function donations_my_index(): void
{
    $role = (string) (user_role() ?? '');
    if (!in_array($role, ['general', 'volunteer'], true)) {
        abort(403, 'Only General users and Volunteers can view this page.');
    }

    $donations = donations_list_for_donor((int) auth_id(), $role);

    view('donations::my_donations', [
        'breadcrumb' => 'My Donations',
        'donations' => $donations,
    ], 'dashboard');
}

function donations_my_cancel(string $donationId): void
{
    csrf_check();

    $role = (string) (user_role() ?? '');
    if (!in_array($role, ['general', 'volunteer'], true)) {
        abort(403, 'Only General users and Volunteers can cancel donations here.');
    }

    $id = (int) $donationId;
    if ($id <= 0) {
        flash('error', 'Invalid donation id.');
        redirect('/dashboard/my-donations');
    }

    $updated = donations_cancel_for_donor($id, (int) auth_id(), $role);
    if ($updated > 0) {
        flash('success', 'Donation cancelled successfully.');
    } else {
        flash('error', 'Donation not found or already processed.');
    }

    redirect('/dashboard/my-donations');
}

function donations_guest_view(string $token): void
{
    $token = trim($token);
    if ($token === '') {
        abort(404, 'Donation tracking link is invalid.');
    }

    $donation = donations_find_by_public_token($token);
    if (!$donation) {
        abort(404, 'Donation not found for the given tracking link.');
    }

    view('donations::guest_track', [
        'breadcrumb' => 'Track Donation',
        'donation' => $donation,
        'tracking_token' => $token,
    ], 'main');
}

function donations_guest_cancel(string $token): void
{
    csrf_check();

    $token = trim($token);
    if ($token === '') {
        abort(404, 'Donation tracking link is invalid.');
    }

    $updated = donations_cancel_by_public_token($token);
    if ($updated > 0) {
        flash('success', 'Donation cancelled successfully.');
    } else {
        flash('error', 'Donation cannot be cancelled because it is already processed or invalid.');
    }

    redirect('/donations/guest/' . rawurlencode($token));
}

function donations_ngo_manage(): void
{
    $ngoUserId = (int) auth_id();

    $tab = strtolower(trim((string) request_query('tab', 'pending')));
    $tabMap = [
        'pending' => 'Pending',
        'received' => 'Received',
        'cancelled' => 'Cancelled',
    ];

    if (!array_key_exists($tab, $tabMap)) {
        $tab = 'pending';
    }

    $donations = donations_list_for_ngo($ngoUserId, $tabMap[$tab]);
    $counts = donations_ngo_counts($ngoUserId);

    view('donations::ngo_manage', [
        'breadcrumb' => 'Received Donations',
        'current_tab' => $tab,
        'counts' => $counts,
        'donations' => $donations,
    ], 'dashboard');
}

function donations_ngo_mark_received(string $donationId): void
{
    csrf_check();

    $ngoUserId = (int) auth_id();
    $id = (int) $donationId;

    if ($id <= 0) {
        flash('error', 'Invalid donation id.');
        redirect('/dashboard/ngo/donations?tab=pending');
    }

    try {
        $result = donations_mark_received_and_sync_inventory($id, $ngoUserId);
    } catch (Throwable $e) {
        flash('error', 'Unable to mark donation as received right now. Please try again.');
        redirect('/dashboard/ngo/donations?tab=pending');
    }

    if (empty($result['ok'])) {
        flash('error', (string) ($result['message'] ?? 'Unable to update donation.'));
        redirect('/dashboard/ngo/donations?tab=pending');
    }

    $donation = (array) ($result['donation'] ?? []);
    $isGuestDonation = (string) ($donation['submitted_by_role'] ?? '') === 'guest';
    if ($isGuestDonation) {
        donations_send_guest_delivered_email($donation);
    }

    flash('success', (string) ($result['message'] ?? 'Donation marked as received.'));
    redirect('/dashboard/ngo/donations?tab=pending');
}
