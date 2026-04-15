<?php

/**
 * Dashboard Module — Controllers
 */

function dashboard_index(): void
{
    $user = auth_user();
    if (!$user) {
        redirect('/login');
    }

    $role = (string) ($user['role'] ?? '');
    $profile = auth_get_profile((int) auth_id(), $role);

    $data = [
        'user' => $user,
        'profile' => $profile,
        'breadcrumb' => 'Overview',
    ];

    switch ($role) {
        case 'general':
            $data['general_snapshot'] = dashboard_general_snapshot((int) auth_id(), (array) $profile);
            $viewName = 'general';
            break;

        case 'volunteer':
            $data['volunteer_snapshot'] = dashboard_volunteer_snapshot((int) auth_id());
            $viewName = 'volunteer';
            break;

        case 'ngo':
            $data['ngo_snapshot'] = dashboard_ngo_snapshot((int) auth_id());
            $viewName = 'ngo';
            break;

        case 'grama_niladhari':
            $gnDivision = trim((string) ($profile['gn_division'] ?? ''));
            $data['gn_snapshot'] = dashboard_gn_snapshot((int) auth_id(), (array) $profile);
            $data['gn_disaster_notifications'] = disaster_reports_list_gn_active_notifications($gnDivision);
            $data['gn_disaster_notification_count'] = count((array) ($data['gn_disaster_notifications'] ?? []));
            $viewName = 'grama_niladhari';
            break;

        case 'dmc':
            $pendingUsers = auth_pending_approval_users();
            $data['pending_users'] = $pendingUsers;
            $data['pending_count'] = count($pendingUsers);
            $data['gn_users'] = auth_list_grama_niladhari_users();
            $data['analytics'] = dashboard_dmc_analytics($data['pending_count']);
            $data['report_districts'] = dashboard_report_available_districts();
            $viewName = 'dmc';
            break;

        default:
            abort(403, 'Unsupported user role.');
    }

    view('dashboard::' . $viewName, $data, 'dashboard');
}

function dashboard_export_district_pdf(): void
{
    $districtInput = trim((string) request_query('district', ''));
    if ($districtInput === '') {
        abort(422, 'District is required for district report export.');
    }

    $availableDistricts = dashboard_report_available_districts();
    $district = '';
    foreach ($availableDistricts as $candidate) {
        if (strcasecmp((string) $candidate, $districtInput) === 0) {
            $district = (string) $candidate;
            break;
        }
    }

    if ($district === '') {
        abort(422, 'Selected district is invalid.');
    }

    $period = dashboard_report_parse_period(
        (string) request_query('from', ''),
        (string) request_query('to', '')
    );

    $report = dashboard_report_generate_district_pdf($district, $period);
    $filename = (string) ($report['filename'] ?? ('district-operational-report-' . date('Ymd_His') . '.pdf'));
    $content = (string) ($report['content'] ?? '');

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($content));
    header('Cache-Control: private, max-age=0, must-revalidate');

    echo $content;
    exit;
}

function dashboard_export_full_pdf(): void
{
    $period = dashboard_report_parse_period(
        (string) request_query('from', ''),
        (string) request_query('to', '')
    );

    $report = dashboard_report_generate_full_pdf($period);
    $filename = (string) ($report['filename'] ?? ('operational-report-full-' . date('Ymd_His') . '.pdf'));
    $content = (string) ($report['content'] ?? '');

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($content));
    header('Cache-Control: private, max-age=0, must-revalidate');

    echo $content;
    exit;
}
