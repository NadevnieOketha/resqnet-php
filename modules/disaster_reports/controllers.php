<?php

/**
 * Disaster Reports Module - Controllers
 */

function disaster_reports_create_form(): void
{
    view('disaster_reports::report_form', [
        'breadcrumb' => 'Report a Disaster',
        'districts' => disaster_reports_district_list(),
        'district_map' => disaster_reports_district_map(),
    ], 'dashboard');
}

function disaster_reports_store_action(): void
{
    csrf_check();

    $role = (string) (user_role() ?? '');
    if (!in_array($role, ['general', 'volunteer'], true)) {
        abort(403, 'Only general users and volunteers can submit reports.');
    }

    $reporterName = trim((string) request_input('reporter_name', ''));
    $contactNumber = trim((string) request_input('contact_number', ''));
    $disasterType = trim((string) request_input('disaster_type', ''));
    $otherDisasterType = trim((string) request_input('other_disaster_type', ''));
    $disasterDatetime = trim((string) request_input('disaster_datetime', ''));
    $district = trim((string) request_input('district', ''));
    $districtOther = trim((string) request_input('district_other', ''));
    $gnDivision = trim((string) request_input('gn_division', ''));
    $gnDivisionOther = trim((string) request_input('gn_division_other', ''));
    $location = trim((string) request_input('location', ''));
    $description = trim((string) request_input('description', ''));
    $confirmed = (string) request_input('confirmation', '0') === '1';
    $normalizedDisasterDatetime = '';

    if ($district === '__other__') {
        $district = $districtOther;
    }
    if ($gnDivision === '__other__') {
        $gnDivision = $gnDivisionOther;
    }

    $allowedTypes = ['Flood', 'Landslide', 'Fire', 'Earthquake', 'Tsunami', 'Other'];

    $errors = [];
    if ($reporterName === '') $errors[] = 'Reporter name is required.';
    if ($contactNumber === '') $errors[] = 'Contact number is required.';
    if (!in_array($disasterType, $allowedTypes, true)) $errors[] = 'Please select a valid disaster type.';
    if ($disasterType === 'Other' && $otherDisasterType === '') $errors[] = 'Please specify the disaster type.';
    if ($disasterDatetime === '') {
        $errors[] = 'Date and time are required.';
    } else {
        $timestamp = strtotime($disasterDatetime);
        if ($timestamp === false) {
            $errors[] = 'Invalid disaster date/time.';
        } else {
            $normalizedDisasterDatetime = date('Y-m-d H:i:s', $timestamp);
        }
    }
    if ($district === '') $errors[] = 'District is required.';
    if ($gnDivision === '') $errors[] = 'GN division is required.';
    if (!$confirmed) $errors[] = 'You must confirm that the information is accurate.';

    $proofImagePath = '';
    $uploadResult = disaster_reports_handle_image_upload('proof_image');
    if (!empty($uploadResult['error'])) {
        $errors[] = (string) $uploadResult['error'];
    } else {
        $proofImagePath = (string) ($uploadResult['path'] ?? '');
    }

    if (!empty($errors)) {
        flash('error', implode(' ', $errors));
        flash_old_input();
        redirect('/report-disaster');
    }

    try {
        disaster_reports_ensure_reporter_relation(
            (int) auth_id(),
            $reporterName,
            $contactNumber,
            $district,
            $gnDivision
        );

        disaster_reports_insert([
            'user_id' => (int) auth_id(),
            'reporter_name' => $reporterName,
            'contact_number' => $contactNumber,
            'disaster_type' => $disasterType,
            'other_disaster_type' => $otherDisasterType,
            'disaster_datetime' => $normalizedDisasterDatetime,
            'district' => $district,
            'gn_division' => $gnDivision,
            'location' => $location,
            'description' => $description,
            'proof_image_path' => $proofImagePath,
        ]);
    } catch (Throwable $e) {
        flash('error', 'Unable to submit report right now. Please try again.');
        flash_old_input();
        redirect('/report-disaster');
    }

    clear_old_input();
    flash('success', 'Disaster report submitted successfully. It is now pending DMC review.');
    redirect('/dashboard');
}

function disaster_reports_review_index(): void
{
    view('disaster_reports::review', [
        'breadcrumb' => 'Disaster Reports',
        'pending_reports' => disaster_reports_list_pending(),
        'approved_reports' => disaster_reports_list_approved(),
    ], 'dashboard');
}

function disaster_reports_verify_action(string $reportId): void
{
    csrf_check();

    $id = (int) $reportId;
    if ($id <= 0) {
        flash('error', 'Invalid report id.');
        redirect('/dashboard/reports');
    }

    $updated = disaster_reports_update_status($id, 'Approved');
    if ($updated > 0) {
        flash('success', 'Report verified successfully.');
    } else {
        flash('warning', 'Unable to verify this report. It may already be reviewed.');
    }

    redirect('/dashboard/reports');
}

function disaster_reports_reject_action(string $reportId): void
{
    csrf_check();

    $id = (int) $reportId;
    if ($id <= 0) {
        flash('error', 'Invalid report id.');
        redirect('/dashboard/reports');
    }

    $updated = disaster_reports_update_status($id, 'Rejected');
    if ($updated > 0) {
        flash('success', 'Report rejected.');
    } else {
        flash('warning', 'Unable to reject this report. It may already be reviewed.');
    }

    redirect('/dashboard/reports');
}

function disaster_reports_handle_image_upload(string $fieldName): array
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

    $uploadDir = BASE_PATH . '/public/uploads/disaster_reports';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
        return ['error' => 'Unable to create upload directory.'];
    }

    $filename = 'report_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $allowed[$mime];
    $targetPath = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($tmpName, $targetPath)) {
        return ['error' => 'Failed to save uploaded image.'];
    }

    return ['path' => '/uploads/disaster_reports/' . $filename];
}
