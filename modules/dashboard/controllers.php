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
            $viewName = 'general';
            break;

        case 'volunteer':
            $viewName = 'volunteer';
            break;

        case 'ngo':
            $viewName = 'ngo';
            break;

        case 'grama_niladhari':
            $gnDivision = trim((string) ($profile['gn_division'] ?? ''));
            $data['gn_disaster_notifications'] = disaster_reports_list_gn_active_notifications($gnDivision);
            $data['gn_disaster_notification_count'] = count((array) ($data['gn_disaster_notifications'] ?? []));
            $viewName = 'grama_niladhari';
            break;

        case 'dmc':
            $pendingUsers = auth_pending_approval_users();
            $data['pending_users'] = $pendingUsers;
            $data['pending_count'] = count($pendingUsers);
            $data['gn_users'] = auth_list_grama_niladhari_users();
            $viewName = 'dmc';
            break;

        default:
            abort(403, 'Unsupported user role.');
    }

    view('dashboard::' . $viewName, $data, 'dashboard');
}
