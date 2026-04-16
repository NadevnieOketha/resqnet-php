<?php

/**
 * Disaster Reports Module - Controllers
 */

function disaster_reports_create_form(): void
{
    $user = auth_user();
    $role = (string) ($user['role'] ?? '');
    $profile = auth_get_profile((int) auth_id(), $role) ?? [];
    $isGn = $role === 'grama_niladhari';

    $lockedGnDivision = $isGn ? trim((string) ($profile['gn_division'] ?? '')) : '';
    $lockedDistrict = $isGn ? disaster_reports_find_district_for_gn_division($lockedGnDivision) : '';

    $prefilledReporterName = trim((string) (
        $profile['name']
        ?? $user['display_name']
        ?? $user['username']
        ?? ''
    ));

    $prefilledContactNumber = trim((string) (
        $profile['contact_number']
        ?? ''
    ));

    view('disaster_reports::report_form', [
        'breadcrumb' => 'Report a Disaster',
        'districts' => disaster_reports_district_list(),
        'district_map' => disaster_reports_district_map(),
        'prefilled_reporter_name' => $prefilledReporterName,
        'prefilled_contact_number' => $prefilledContactNumber,
        'is_gn_area_locked' => $isGn,
        'locked_district' => $lockedDistrict,
        'locked_gn_division' => $lockedGnDivision,
    ], 'dashboard');
}

function disaster_reports_store_action(): void
{
    csrf_check();

    $role = (string) (user_role() ?? '');
    if (!in_array($role, ['general', 'volunteer', 'grama_niladhari', 'dmc'], true)) {
        abort(403, 'Only general users, volunteers, Grama Niladhari users, and DMC users can submit reports.');
    }

    $isDmcReporter = $role === 'dmc';

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

    if ($role === 'grama_niladhari') {
        $profile = auth_get_profile((int) auth_id(), 'grama_niladhari') ?? [];
        $profileGnDivision = trim((string) ($profile['gn_division'] ?? ''));
        $profileDistrict = disaster_reports_find_district_for_gn_division($profileGnDivision);

        $gnDivision = $profileGnDivision;
        $district = $profileDistrict;
        $districtOther = '';
        $gnDivisionOther = '';
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
        } elseif ($timestamp > time()) {
            $errors[] = 'Date and time of occurrence cannot be in the future.';
        } else {
            $normalizedDisasterDatetime = date('Y-m-d H:i:s', $timestamp);
        }
    }
    if ($district === '') $errors[] = 'District is required.';
    if ($gnDivision === '') $errors[] = 'GN division is required.';
    if ($role === 'grama_niladhari' && $gnDivision === '') {
        $errors[] = 'Your GN profile must include a GN division before you can submit reports.';
    }
    if ($role === 'grama_niladhari' && $district === '') {
        $errors[] = 'Unable to map your GN division to a district. Please contact DMC.';
    }
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

        $createdReportId = disaster_reports_insert([
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
            'status' => $isDmcReporter ? 'Approved' : 'Pending',
            'verified_at' => $isDmcReporter ? date('Y-m-d H:i:s') : null,
        ]);

        if ($createdReportId <= 0) {
            throw new RuntimeException('Failed to create disaster report.');
        }

        if ($isDmcReporter) {
            $report = disaster_reports_find_by_id($createdReportId) ?? [];
            $processing = disaster_reports_finalize_approved_report($createdReportId, $report);
            $notifyResult = (array) ($processing['notify_result'] ?? []);
            $assignmentResult = (array) ($processing['assignment_result'] ?? []);
            $volunteerNotified = (int) ($processing['volunteer_notified'] ?? 0);

            $message = 'Disaster report submitted and approved successfully.';
            if ((int) ($notifyResult['sent'] ?? 0) > 0) {
                $message .= ' ' . (int) $notifyResult['sent'] . ' Grama Niladhari contact(s) notified.';
            } else {
                $message .= ' No matching Grama Niladhari contact was notified.';
            }

            $message .= ' ' . (string) ($assignmentResult['message'] ?? '');
            if ($volunteerNotified > 0) {
                $message .= ' ' . $volunteerNotified . ' volunteer notification email(s) sent.';
            }

            clear_old_input();
            flash('success', trim($message));

            if ((int) ($notifyResult['failed'] ?? 0) > 0) {
                flash('warning', (int) $notifyResult['failed'] . ' GN notification email(s) failed to send.');
            }

            $totalAssigned = (int) ($assignmentResult['total_assigned'] ?? 0);
            $requiredMinimum = (int) ($assignmentResult['required_minimum'] ?? 5);
            if ($totalAssigned < $requiredMinimum) {
                flash('warning', 'Automatic assignment is currently below 5 volunteers. You can manually reassign from Volunteer Assignments.');
            }

            redirect('/dashboard/reports');
        }
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
    $pendingReports = disaster_reports_list_pending();
    $approvedReports = disaster_reports_list_approved();
    $assignedCounts = [];
    $approvedIds = [];
    foreach ($approvedReports as $report) {
        $reportId = (int) ($report['report_id'] ?? 0);
        if ($reportId > 0) {
            $assignedCounts[$reportId] = disaster_reports_assigned_volunteer_count($reportId);
            $approvedIds[] = $reportId;
        }
    }

    $assignedVolunteersByReport = disaster_reports_list_assignments_by_report_ids($approvedIds);

    view('disaster_reports::review', [
        'breadcrumb' => 'Disaster Reports',
        'pending_reports' => $pendingReports,
        'pending_reports_grouped' => disaster_reports_group_by_gn_and_disaster_type($pendingReports),
        'approved_reports' => $approvedReports,
        'approved_reports_grouped' => disaster_reports_group_by_gn_and_disaster_type($approvedReports),
        'assigned_counts' => $assignedCounts,
        'assigned_volunteers_by_report' => $assignedVolunteersByReport,
    ], 'dashboard');
}

function disaster_reports_detail(string $reportId): void
{
    $id = (int) $reportId;
    if ($id <= 0) {
        flash('error', 'Invalid disaster report id.');
        redirect('/dashboard/reports');
    }

    $report = disaster_reports_find_by_id($id);
    if (!$report) {
        flash('error', 'Disaster report not found.');
        redirect('/dashboard/reports');
    }

    $assignmentsByReport = disaster_reports_list_assignments_by_report_ids([$id]);
    $assignedVolunteers = (array) ($assignmentsByReport[$id] ?? []);
    $assignedCount = disaster_reports_assigned_volunteer_count($id);

    view('disaster_reports::detail', [
        'breadcrumb' => 'Disaster Report Details',
        'report' => $report,
        'assigned_volunteers' => $assignedVolunteers,
        'assigned_count' => $assignedCount,
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
        $report = disaster_reports_find_by_id($id);
        $processing = disaster_reports_finalize_approved_report($id, (array) ($report ?? []));
        $notifyResult = (array) ($processing['notify_result'] ?? []);
        $assignmentResult = (array) ($processing['assignment_result'] ?? []);
        $volunteerNotified = (int) ($processing['volunteer_notified'] ?? 0);

        $message = 'Report verified successfully.';
        if (($notifyResult['sent'] ?? 0) > 0) {
            $message .= ' ' . (int) $notifyResult['sent'] . ' Grama Niladhari contact(s) notified.';
        } else {
            $message .= ' No matching Grama Niladhari contact was notified.';
        }
        $message .= ' ' . (string) ($assignmentResult['message'] ?? '');
        if ($volunteerNotified > 0) {
            $message .= ' ' . $volunteerNotified . ' volunteer notification email(s) sent.';
        }
        flash('success', $message);

        if (($notifyResult['failed'] ?? 0) > 0) {
            flash('warning', (int) $notifyResult['failed'] . ' GN notification email(s) failed to send.');
        }

        $totalAssigned = (int) ($assignmentResult['total_assigned'] ?? 0);
        $requiredMinimum = (int) ($assignmentResult['required_minimum'] ?? 5);
        if ($totalAssigned < $requiredMinimum) {
            flash('warning', 'Automatic assignment is currently below 5 volunteers. You can manually reassign from Volunteer Assignments.');
        }
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

function disaster_reports_assign_volunteers_action(string $reportId): void
{
    csrf_check();

    $id = (int) $reportId;
    if ($id <= 0) {
        flash('error', 'Invalid report id.');
        redirect('/dashboard/reports');
    }

    $result = disaster_reports_assign_volunteers_to_report($id, 5);
    $assigned = $result['assigned'] ?? [];
    $totalAssigned = (int) ($result['total_assigned'] ?? 0);
    $requiredMinimum = (int) ($result['required_minimum'] ?? 5);

    if (!empty($assigned)) {
        $report = $result['report'] ?? disaster_reports_find_by_id($id);
        $notified = disaster_reports_notify_assigned_volunteers($assigned, (array) $report, $id);

        $message = (string) ($result['message'] ?? 'Volunteers assigned.');
        if ($notified > 0) {
            $message .= ' ' . $notified . ' volunteer notification email(s) sent.';
        }
        flash('success', $message);
    } elseif ($totalAssigned >= $requiredMinimum) {
        flash('success', (string) ($result['message'] ?? 'Minimum volunteer assignment requirement is already satisfied.'));
    } else {
        flash('warning', (string) ($result['message'] ?? 'No volunteers were assigned.'));
    }

    redirect('/dashboard/reports');
}

function disaster_reports_volunteer_tasks_index(): void
{
    $volunteerId = (int) auth_id();
    $tasks = disaster_reports_list_tasks_for_volunteer($volunteerId);

    view('disaster_reports::volunteer_tasks', [
        'breadcrumb' => 'Assigned Tasks',
        'tasks' => $tasks,
        'task_counts' => disaster_reports_task_status_counters($tasks),
    ], 'dashboard');
}

function disaster_reports_volunteer_task_status_action(string $taskId): void
{
    csrf_check();

    $id = (int) $taskId;
    $nextStatus = trim((string) request_input('next_status', ''));
    $note = trim((string) request_input('update_note', ''));

    $allowed = ['Accepted', 'Declined', 'In Progress', 'Completed'];
    if ($id <= 0 || !in_array($nextStatus, $allowed, true)) {
        flash('error', 'Invalid task update request.');
        redirect('/dashboard/volunteer-tasks');
    }

    $result = disaster_reports_update_volunteer_task_status($id, (int) auth_id(), $nextStatus);
    if (!$result['ok']) {
        flash('error', (string) ($result['message'] ?? 'Unable to update task status.'));
        redirect('/dashboard/volunteer-tasks');
    }

    if ($note !== '') {
        disaster_reports_log_field_update($id, (int) auth_id(), $nextStatus, $note);
    }

    flash('success', (string) ($result['message'] ?? 'Task status updated.'));
    redirect('/dashboard/volunteer-tasks');
}

function disaster_reports_dmc_tasks_index(): void
{
    $statusFilter = trim((string) request_query('status', ''));
    $tasks = disaster_reports_list_all_tasks_for_dmc($statusFilter !== '' ? $statusFilter : null);
    $taskIds = array_map(static fn(array $task): int => (int) ($task['id'] ?? 0), $tasks);
    $taskNotes = disaster_reports_list_notes_by_task_ids($taskIds);

    view('disaster_reports::dmc_tasks', [
        'breadcrumb' => 'Volunteer Assignments',
        'tasks' => $tasks,
        'task_notes' => $taskNotes,
        'status_filter' => $statusFilter,
        'task_counts' => disaster_reports_task_status_counters($tasks),
    ], 'dashboard');
}

function disaster_reports_dmc_task_reassign_action(string $taskId): void
{
    csrf_check();

    $id = (int) $taskId;
    $newVolunteerId = (int) request_input('new_volunteer_id', 0);

    if ($id <= 0 || $newVolunteerId <= 0) {
        flash('error', 'Invalid reassignment request.');
        redirect('/dashboard/admin/volunteer-tasks');
    }

    $result = disaster_reports_reassign_task($id, $newVolunteerId);
    if ($result['ok']) {
        $task = db_fetch(
            "SELECT vt.disaster_id,
                    v.user_id,
                    v.name,
                    v.contact_number,
                    u.email
             FROM volunteer_task vt
             INNER JOIN volunteers v ON v.user_id = vt.volunteer_id
             INNER JOIN users u ON u.user_id = v.user_id
             WHERE vt.id = ?
             LIMIT 1",
            [$id]
        );

        if ($task) {
            $reportId = (int) ($task['disaster_id'] ?? 0);
            if ($reportId > 0) {
                $report = disaster_reports_find_by_id($reportId) ?? [];
                disaster_reports_notify_assigned_volunteers([
                    [
                        'user_id' => (int) ($task['user_id'] ?? 0),
                        'name' => (string) ($task['name'] ?? 'Volunteer'),
                        'email' => (string) ($task['email'] ?? ''),
                        'contact_number' => (string) ($task['contact_number'] ?? ''),
                    ],
                ], $report, $reportId);
            }
        }

        flash('success', (string) $result['message']);
    } else {
        flash('error', (string) $result['message']);
    }

    redirect('/dashboard/admin/volunteer-tasks');
}

function disaster_reports_dmc_task_verify_action(string $taskId): void
{
    csrf_check();

    $id = (int) $taskId;
    if ($id <= 0) {
        flash('error', 'Invalid task id.');
        redirect('/dashboard/admin/volunteer-tasks');
    }

    $result = disaster_reports_verify_task_completion($id);
    if ($result['ok']) {
        flash('success', (string) $result['message']);
    } else {
        flash('warning', (string) $result['message']);
    }

    redirect('/dashboard/admin/volunteer-tasks');
}

function disaster_reports_finalize_approved_report(int $reportId, array $report = []): array
{
    $finalReport = $report;
    if (empty($finalReport)) {
        $finalReport = disaster_reports_find_by_id($reportId) ?? [];
    }

    $notifyResult = disaster_reports_notify_grama_niladhari($finalReport);
    $assignmentResult = disaster_reports_assign_volunteers_to_report($reportId, 5);
    $assigned = (array) ($assignmentResult['assigned'] ?? []);
    $volunteerNotified = disaster_reports_notify_assigned_volunteers($assigned, $finalReport, $reportId);

    return [
        'report' => $finalReport,
        'notify_result' => $notifyResult,
        'assignment_result' => $assignmentResult,
        'volunteer_notified' => $volunteerNotified,
    ];
}

function disaster_reports_notify_grama_niladhari(array $report): array
{
    $gnDivision = trim((string) ($report['gn_division'] ?? ''));
    if ($gnDivision === '') {
        return ['sent' => 0, 'failed' => 0];
    }

    $contacts = disaster_reports_find_gn_contacts_for_division($gnDivision);
    if (empty($contacts)) {
        return ['sent' => 0, 'failed' => 0];
    }

    $sent = 0;
    $failed = 0;

    foreach ($contacts as $contact) {
        $email = trim((string) ($contact['email'] ?? ''));
        if ($email === '') {
            continue;
        }

        $subject = 'resqnet disaster report verified for your GN division';
        $html = '<p>Hello ' . e((string) ($contact['name'] ?? 'GN Officer')) . ',</p>'
            . '<p>A disaster report has been verified by DMC for your GN division.</p>'
            . '<p>Report ID: <strong>#' . (int) ($report['report_id'] ?? 0) . '</strong></p>'
            . '<p>Type: <strong>' . e(disaster_reports_disaster_label($report)) . '</strong></p>'
            . '<p>Date/Time: <strong>' . e((string) ($report['disaster_datetime'] ?? '-')) . '</strong></p>'
            . '<p>Location: <strong>' . e((string) (($report['district'] ?? '') . ' / ' . ($report['gn_division'] ?? '') . (($report['location'] ?? '') !== '' ? ' / ' . $report['location'] : ''))) . '</strong></p>';

        if (mail_send($email, $subject, $html)) {
            $sent++;
        } else {
            $failed++;
        }
    }

    return ['sent' => $sent, 'failed' => $failed];
}

function disaster_reports_notify_assigned_volunteers(array $assigned, array $report, int $reportId): int
{
    $notified = 0;

    foreach ($assigned as $volunteer) {
        $email = trim((string) ($volunteer['email'] ?? ''));
        if ($email === '') {
            continue;
        }

        $subject = 'resqnet volunteer task assignment';
        $html = '<p>Hello ' . e((string) ($volunteer['name'] ?? 'Volunteer')) . ',</p>'
            . '<p>You have been assigned a disaster response task.</p>'
            . '<p>Report ID: <strong>#' . (int) $reportId . '</strong></p>'
            . '<p>Type: <strong>' . e(disaster_reports_disaster_label((array) $report)) . '</strong></p>'
            . '<p>Location: <strong>' . e((string) (($report['district'] ?? '') . ' / ' . ($report['gn_division'] ?? ''))) . '</strong></p>'
            . '<p>Please check your dashboard tasks page.</p>';

        if (mail_send($email, $subject, $html)) {
            $notified++;
        }

        disaster_reports_send_assignment_sms($volunteer, $report);
    }

    return $notified;
}

function disaster_reports_send_assignment_sms(array $volunteer, array $report): bool
{
    if (!function_exists('sms_alert_notifylk_is_configured') || !sms_alert_notifylk_is_configured()) {
        return false;
    }

    if (!function_exists('sms_alert_normalize_phone') || !function_exists('sms_alert_notifylk_send')) {
        return false;
    }

    $phoneRaw = trim((string) ($volunteer['contact_number'] ?? ''));
    $volunteerId = (int) ($volunteer['user_id'] ?? 0);
    if ($phoneRaw === '' && $volunteerId > 0) {
        $row = db_fetch('SELECT contact_number FROM volunteers WHERE user_id = ? LIMIT 1', [$volunteerId]);
        $phoneRaw = trim((string) ($row['contact_number'] ?? ''));
    }

    $phone = sms_alert_normalize_phone($phoneRaw);
    if ($phone === '') {
        return false;
    }

    $message = disaster_reports_assignment_sms_message($report);
    $result = sms_alert_notifylk_send($phone, $message);
    return (bool) ($result['success'] ?? false);
}

function disaster_reports_assignment_sms_message(array $report): string
{
    $disasterType = trim((string) disaster_reports_disaster_label($report));
    if ($disasterType === '') {
        $disasterType = 'Disaster';
    }

    $location = disaster_reports_assignment_location_label($report);
    $dateTime = disaster_reports_assignment_datetime_label((string) ($report['disaster_datetime'] ?? ''));
    $contactPerson = trim((string) ($report['reporter_name'] ?? ''));
    $contactNumber = trim((string) ($report['contact_number'] ?? ''));

    if ($contactPerson === '') {
        $contactPerson = 'N/A';
    }
    if ($contactNumber === '') {
        $contactNumber = 'N/A';
    }

    return "ResQnet VOLUNTEER ASSIGNMENT\n"
        . "Disaster: {$disasterType}\n"
        . "Location: {$location}\n"
        . "Date/Time: {$dateTime}\n"
        . "Contact: {$contactPerson} ({$contactNumber})\n"
        . 'Action: Please proceed and update status in app.';
}

function disaster_reports_assignment_location_label(array $report): string
{
    $location = trim((string) ($report['location'] ?? ''));
    $district = trim((string) ($report['district'] ?? ''));
    $gnDivision = trim((string) ($report['gn_division'] ?? ''));

    if ($location !== '') {
        if ($district !== '' || $gnDivision !== '') {
            $area = trim($district . ($gnDivision !== '' ? ' / ' . $gnDivision : ''));
            if ($area !== '') {
                return $location . ' (' . $area . ')';
            }
        }

        return $location;
    }

    $fallback = trim($district . ($gnDivision !== '' ? ' / ' . $gnDivision : ''));
    return $fallback !== '' ? $fallback : 'N/A';
}

function disaster_reports_assignment_datetime_label(string $disasterDateTime): string
{
    $trimmed = trim($disasterDateTime);
    if ($trimmed === '') {
        return 'N/A';
    }

    $timestamp = strtotime($trimmed);
    if ($timestamp === false) {
        return $trimmed;
    }

    return date('Y-m-d H:i', $timestamp);
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
