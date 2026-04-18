<?php

/**
 * Routes
 *
 * Central route definitions.
 */

// Public
route('GET', '/', 'home_index');
route('GET', '/forum', 'forum_public_index');
route('GET', '/safe-locations', 'safe_locations_public_index');
route('GET', '/safe-locations/data', 'safe_locations_public_data');
route('GET', '/make-donation', 'donations_make_form');
route('POST', '/make-donation', 'donations_store');
route('GET', '/donations/guest/{token}', 'donations_guest_view');
route('POST', '/donations/guest/{token}/cancel', 'donations_guest_cancel');

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
route('GET',  '/dashboard/forum',     'forum_dashboard_index',   ['middleware_auth']);
route('GET',  '/dashboard/export/district-report', 'dashboard_export_district_pdf', ['middleware_auth', fn() => middleware_role('dmc')]);
route('GET',  '/dashboard/export/district-report.pdf', 'dashboard_export_district_pdf', ['middleware_auth', fn() => middleware_role('dmc')]);
route('GET',  '/dashboard/export/full-report', 'dashboard_export_full_pdf', ['middleware_auth', fn() => middleware_role('dmc')]);
route('GET',  '/dashboard/export/full-report.pdf', 'dashboard_export_full_pdf', ['middleware_auth', fn() => middleware_role('dmc')]);
route('GET',  '/dashboard/forecast',  'forecast_dashboard_index',['middleware_auth']);
route('POST', '/dashboard/forecast/sms-alert', 'forecast_sms_alert_update', ['middleware_auth']);
route('GET',  '/profile',             'auth_profile',            ['middleware_auth']);
route('POST', '/profile',             'auth_profile_post',       ['middleware_auth']);
route('POST', '/profile/delete',      'auth_delete_account_post',['middleware_auth', fn() => middleware_roles(['general', 'volunteer', 'ngo'])]);
route('GET',  '/dashboard/become-volunteer', 'auth_become_volunteer_form', ['middleware_auth', fn() => middleware_role('general')]);
route('POST', '/dashboard/become-volunteer', 'auth_become_volunteer_post', ['middleware_auth', fn() => middleware_role('general')]);

// Disaster reporting
route('GET',  '/report-disaster',                 'disaster_reports_create_form',   ['middleware_auth', fn() => middleware_roles(['general', 'volunteer', 'grama_niladhari', 'dmc'])]);
route('POST', '/report-disaster',                 'disaster_reports_store_action',  ['middleware_auth', fn() => middleware_roles(['general', 'volunteer', 'grama_niladhari', 'dmc'])]);
route('GET',  '/dashboard/reports',               'disaster_reports_review_index',  ['middleware_auth', fn() => middleware_role('dmc')]);
route('GET',  '/dashboard/reports/{reportId}',    'disaster_reports_detail',        ['middleware_auth', fn() => middleware_role('dmc')]);
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

// Safe locations
route('GET',  '/dashboard/admin/safe-locations',                      'safe_locations_dmc_manage_index',        ['middleware_auth', fn() => middleware_role('dmc')]);
route('POST', '/dashboard/admin/safe-locations/create',               'safe_locations_dmc_create_action',       ['middleware_auth', fn() => middleware_role('dmc')]);
route('POST', '/dashboard/admin/safe-locations/{locationId}/update',  'safe_locations_dmc_update_action',       ['middleware_auth', fn() => middleware_role('dmc')]);
route('POST', '/dashboard/admin/safe-locations/{locationId}/delete',  'safe_locations_dmc_delete_action',       ['middleware_auth', fn() => middleware_role('dmc')]);
route('GET',  '/dashboard/safe-locations',                            'safe_locations_gn_index',                ['middleware_auth', fn() => middleware_role('grama_niladhari')]);
route('POST', '/dashboard/safe-locations/{locationId}/occupancy',     'safe_locations_gn_update_occupancy_action', ['middleware_auth', fn() => middleware_role('grama_niladhari')]);

// NGO collection point management
route('GET',  '/dashboard/collection-points',                         'collection_points_ngo_index',            ['middleware_auth', fn() => middleware_role('ngo')]);
route('POST', '/dashboard/collection-points/create',                  'collection_points_ngo_create_action',    ['middleware_auth', fn() => middleware_role('ngo')]);
route('POST', '/dashboard/collection-points/{collectionPointId}/update', 'collection_points_ngo_update_action', ['middleware_auth', fn() => middleware_role('ngo')]);
route('POST', '/dashboard/collection-points/{collectionPointId}/delete', 'collection_points_ngo_delete_action', ['middleware_auth', fn() => middleware_role('ngo')]);
route('GET',  '/dashboard/ngo/donations',                             'donations_ngo_manage',                   ['middleware_auth', fn() => middleware_role('ngo')]);
route('POST', '/dashboard/ngo/donations/{donationId}/receive',        'donations_ngo_mark_received',            ['middleware_auth', fn() => middleware_role('ngo')]);
route('GET',  '/dashboard/ngo/inventory',                             'inventory_ngo_index',                    ['middleware_auth', fn() => middleware_role('ngo')]);
route('POST', '/dashboard/ngo/inventory/{inventoryId}/quantity',      'inventory_ngo_update_quantity_action',   ['middleware_auth', fn() => middleware_role('ngo')]);

// My donations (general and volunteer)
route('GET',  '/dashboard/my-donations',                              'donations_my_index',                     ['middleware_auth', fn() => middleware_roles(['general', 'volunteer'])]);
route('POST', '/dashboard/my-donations/{donationId}/cancel',          'donations_my_cancel',                    ['middleware_auth', fn() => middleware_roles(['general', 'volunteer'])]);

// Donation requests and requirement aggregation
route('GET',  '/donation-requests/create',                            'donation_requests_general_create',       ['middleware_auth', fn() => middleware_role('general')]);
route('POST', '/donation-requests/submit',                            'donation_requests_general_store',        ['middleware_auth', fn() => middleware_role('general')]);
route('GET',  '/dashboard/gn/donation-requests',                      'donation_requests_gn_index',             ['middleware_auth', fn() => middleware_role('grama_niladhari')]);
route('GET',  '/dashboard/gn/donation-requests/{locationId}/gather',  'donation_requests_gn_gather_form',       ['middleware_auth', fn() => middleware_role('grama_niladhari')]);
route('POST', '/dashboard/gn/donation-requests/{locationId}/gather',  'donation_requests_gn_gather_store',      ['middleware_auth', fn() => middleware_role('grama_niladhari')]);
route('POST', '/dashboard/gn/donation-requests/{locationId}/fulfilled','donation_requests_gn_mark_fulfilled',    ['middleware_auth', fn() => middleware_role('grama_niladhari')]);
route('GET',  '/dashboard/donation-requirements',                     'donation_requests_feed_index',           ['middleware_auth', fn() => middleware_roles(['dmc', 'ngo'])]);
route('GET',  '/dashboard/donation-requirements/{requirementId}',     'donation_requests_feed_details',         ['middleware_auth', fn() => middleware_roles(['dmc', 'ngo'])]);
route('POST', '/dashboard/donation-requirements/{requirementId}/reserve', 'donation_requests_feed_reserve',     ['middleware_auth', fn() => middleware_role('ngo')]);

// DMC Auth Operations
route('GET',  '/dashboard/admin/pending',                        'auth_dmc_pending_approvals',         ['middleware_auth', fn() => middleware_role('dmc')]);
route('POST', '/dashboard/admin/approve/{userId}',               'auth_dmc_approve_user_action',       ['middleware_auth', fn() => middleware_role('dmc')]);
route('GET',  '/dashboard/admin/grama-niladhari/accounts',       'auth_dmc_gn_accounts_index',         ['middleware_auth', fn() => middleware_role('dmc')]);
route('GET',  '/dashboard/admin/grama-niladhari/create',         'auth_dmc_create_gn_form',            ['middleware_auth', fn() => middleware_role('dmc')]);
route('POST', '/dashboard/admin/grama-niladhari/create',         'auth_dmc_create_gn_post',            ['middleware_auth', fn() => middleware_role('dmc')]);
route('POST', '/dashboard/admin/grama-niladhari/{userId}/resend','auth_dmc_resend_gn_credentials',     ['middleware_auth', fn() => middleware_role('dmc')]);
route('POST', '/dashboard/admin/grama-niladhari/{userId}/activate','auth_dmc_activate_gn_account_action',['middleware_auth', fn() => middleware_role('dmc')]);
route('POST', '/dashboard/admin/grama-niladhari/{userId}/deactivate','auth_dmc_deactivate_gn_account_action',['middleware_auth', fn() => middleware_role('dmc')]);

// DMC forum post management
route('GET',  '/dashboard/admin/forum-posts',                        'forum_dmc_manage_index',      ['middleware_auth', fn() => middleware_role('dmc')]);
route('POST', '/dashboard/admin/forum-posts/create',                 'forum_dmc_create_action',     ['middleware_auth', fn() => middleware_role('dmc')]);
route('POST', '/dashboard/admin/forum-posts/{postId}/update',        'forum_dmc_update_action',     ['middleware_auth', fn() => middleware_role('dmc')]);
route('POST', '/dashboard/admin/forum-posts/{postId}/delete',        'forum_dmc_delete_action',     ['middleware_auth', fn() => middleware_role('dmc')]);
