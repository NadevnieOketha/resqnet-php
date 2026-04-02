<?php

/**
 * Routes
 *
 * Central route definitions.
 */

// Public
route('GET', '/', 'home_index');

// Auth (guest)
route('GET',  '/login',            'auth_login',               ['middleware_guest']);
route('POST', '/login',            'auth_login_post',          ['middleware_guest']);
route('GET',  '/register',         'auth_register',            ['middleware_guest']);
route('POST', '/register',         'auth_register_post',       ['middleware_guest']);
route('GET',  '/forgot-password',  'auth_forgot_password',     ['middleware_guest']);
route('POST', '/forgot-password',  'auth_forgot_password_post',['middleware_guest']);
route('GET',  '/reset-password',   'auth_reset_password',      ['middleware_guest']);
route('POST', '/reset-password',   'auth_reset_password_post', ['middleware_guest']);

// Authenticated
route('GET',  '/logout',              'auth_logout',             ['middleware_auth']);
route('GET',  '/dashboard',           'dashboard_index',         ['middleware_auth']);
route('GET',  '/profile',             'auth_profile',            ['middleware_auth']);
route('POST', '/profile',             'auth_profile_post',       ['middleware_auth']);
route('POST', '/profile/sms-alert',   'auth_profile_sms_toggle', ['middleware_auth']);

// Disaster reporting
route('GET',  '/report-disaster',                 'disaster_reports_create_form',   ['middleware_auth', fn() => middleware_roles(['general', 'volunteer'])]);
route('POST', '/report-disaster',                 'disaster_reports_store_action',  ['middleware_auth', fn() => middleware_roles(['general', 'volunteer'])]);
route('GET',  '/dashboard/reports',               'disaster_reports_review_index',  ['middleware_auth', fn() => middleware_role('dmc')]);
route('POST', '/dashboard/reports/{reportId}/verify', 'disaster_reports_verify_action', ['middleware_auth', fn() => middleware_role('dmc')]);
route('POST', '/dashboard/reports/{reportId}/reject', 'disaster_reports_reject_action', ['middleware_auth', fn() => middleware_role('dmc')]);
route('POST', '/dashboard/reports/{reportId}/assign-volunteers', 'disaster_reports_assign_volunteers_action', ['middleware_auth', fn() => middleware_role('dmc')]);

// Volunteer task lifecycle
route('GET',  '/dashboard/volunteer-tasks',            'disaster_reports_volunteer_tasks_index', ['middleware_auth', fn() => middleware_role('volunteer')]);
route('POST', '/dashboard/volunteer-tasks/{taskId}/status', 'disaster_reports_volunteer_task_status_action', ['middleware_auth', fn() => middleware_role('volunteer')]);

// DMC assignment oversight
route('GET',  '/dashboard/admin/volunteer-tasks',               'disaster_reports_dmc_tasks_index', ['middleware_auth', fn() => middleware_role('dmc')]);
route('POST', '/dashboard/admin/volunteer-tasks/{taskId}/reassign', 'disaster_reports_dmc_task_reassign_action', ['middleware_auth', fn() => middleware_role('dmc')]);
route('POST', '/dashboard/admin/volunteer-tasks/{taskId}/verify',   'disaster_reports_dmc_task_verify_action', ['middleware_auth', fn() => middleware_role('dmc')]);

// DMC Auth Operations
route('GET',  '/dashboard/admin/pending',                        'auth_dmc_pending_approvals',         ['middleware_auth', fn() => middleware_role('dmc')]);
route('POST', '/dashboard/admin/approve/{userId}',               'auth_dmc_approve_user_action',       ['middleware_auth', fn() => middleware_role('dmc')]);
route('GET',  '/dashboard/admin/grama-niladhari/create',         'auth_dmc_create_gn_form',            ['middleware_auth', fn() => middleware_role('dmc')]);
route('POST', '/dashboard/admin/grama-niladhari/create',         'auth_dmc_create_gn_post',            ['middleware_auth', fn() => middleware_role('dmc')]);
route('POST', '/dashboard/admin/grama-niladhari/{userId}/resend','auth_dmc_resend_gn_credentials',     ['middleware_auth', fn() => middleware_role('dmc')]);
