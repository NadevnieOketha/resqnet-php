<?php

/**
 * Dashboard Module — Controllers
 */

function dashboard_index(): void
{
    $user = auth_user();
    $role = $user['role'];
    $data = ['user' => $user];

    try {
        $data['published_warning_count'] = db_count('warnings', ['status' => 'published']);
        $data['open_request_count'] = db_count('donation_requests', ['status' => 'open']);
        $data['total_contributions'] = (float) (db_fetch('SELECT COALESCE(SUM(amount), 0) AS total FROM donations')['total'] ?? 0);
    } catch (\PDOException) {
        $data['published_warning_count'] = 0;
        $data['open_request_count'] = 0;
        $data['total_contributions'] = 0;
    }

    switch ($role) {
        case 'dmc':
        case 'dmc_admin':
            try {
                $data['user_count'] = db_count('users');
                $data['warnings'] = warnings_recent(6, null);
                $data['requests'] = donations_recent_requests(6, null);
            } catch (\PDOException) {
                $data['user_count'] = 0;
                $data['warnings'] = [];
                $data['requests'] = [];
            }
            $viewName = 'dmc_admin';
            break;

        case 'grama_niladhari':
            try {
                $data['my_warning_count'] = db_count('warnings', ['issued_by' => auth_id()]);
                $data['warnings'] = warnings_recent(8, auth_id());
            } catch (\PDOException) {
                $data['my_warning_count'] = 0;
                $data['warnings'] = [];
            }
            $viewName = 'grama_niladhari';
            break;

        case 'ngo':
            try {
                $data['my_request_count'] = donations_count_requests_for_ngo((int) auth_id());
                $data['my_collected_total'] = donations_total_for_ngo((int) auth_id());
                $data['requests'] = donations_recent_requests_for_ngo((int) auth_id(), 8);
            } catch (\PDOException) {
                $data['my_request_count'] = 0;
                $data['my_collected_total'] = 0;
                $data['requests'] = [];
            }
            $viewName = 'ngo';
            break;

        case 'volunteer':
            try {
                $data['warnings'] = warnings_recent(6, null, 'published');
                $data['requests'] = donations_recent_requests(6, 'open');
            } catch (\PDOException) {
                $data['warnings'] = [];
                $data['requests'] = [];
            }
            $viewName = 'general_public';
            break;

        case 'general':
        default: // general_public
            try {
                $data['warnings'] = warnings_recent(6, null, 'published');
                $data['requests'] = donations_recent_requests(6, 'open');
            } catch (\PDOException) {
                $data['warnings'] = [];
                $data['requests'] = [];
            }
            $viewName = 'general_public';
            break;
    }

    view('dashboard::' . $viewName, $data, 'dashboard');
}
