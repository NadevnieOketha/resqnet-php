<?php

/**
 * Routes
 *
 * Central route definitions for the entire application.
 */

// Public pages
route('GET', '/', 'home_index');
route('GET', '/warnings', 'warnings_public_index');
route('GET', '/donations', 'donations_public_index');
route('GET', '/donations/{id}', 'donations_public_show');
route('POST', '/donations/{id}/contribute', 'donations_contribute');

// Auth
route('GET',  '/login',    'auth_login',          ['middleware_guest']);
route('POST', '/login',    'auth_login_post',     ['middleware_guest']);
route('GET',  '/register', 'auth_register',       ['middleware_guest']);
route('POST', '/register', 'auth_register_post',  ['middleware_guest']);
route('GET',  '/logout',   'auth_logout',         ['middleware_auth']);

// Dashboard
route('GET', '/dashboard', 'dashboard_index', ['middleware_auth']);

// Early warnings management (Grama Niladhari + DMC)
route('GET',  '/dashboard/warnings',              'warnings_manage_index',  ['middleware_auth', fn() => middleware_roles(['grama_niladhari', 'dmc_admin'])]);
route('GET',  '/dashboard/warnings/create',       'warnings_create_form',   ['middleware_auth', fn() => middleware_roles(['grama_niladhari', 'dmc_admin'])]);
route('POST', '/dashboard/warnings',              'warnings_store',         ['middleware_auth', fn() => middleware_roles(['grama_niladhari', 'dmc_admin'])]);
route('GET',  '/dashboard/warnings/{id}/edit',    'warnings_edit_form',     ['middleware_auth', fn() => middleware_roles(['grama_niladhari', 'dmc_admin'])]);
route('POST', '/dashboard/warnings/{id}',         'warnings_update_action', ['middleware_auth', fn() => middleware_roles(['grama_niladhari', 'dmc_admin'])]);
route('POST', '/dashboard/warnings/{id}/delete',  'warnings_delete_action', ['middleware_auth', fn() => middleware_roles(['grama_niladhari', 'dmc_admin'])]);

// Donation request management (NGO + DMC)
route('GET',  '/dashboard/donations/manage',      'donations_manage_index',  ['middleware_auth', fn() => middleware_roles(['ngo', 'dmc_admin'])]);
route('GET',  '/dashboard/donations/create',      'donations_create_form',   ['middleware_auth', fn() => middleware_roles(['ngo', 'dmc_admin'])]);
route('POST', '/dashboard/donations',             'donations_store',         ['middleware_auth', fn() => middleware_roles(['ngo', 'dmc_admin'])]);
route('GET',  '/dashboard/donations/{id}/edit',   'donations_edit_form',     ['middleware_auth', fn() => middleware_roles(['ngo', 'dmc_admin'])]);
route('POST', '/dashboard/donations/{id}',        'donations_update_action', ['middleware_auth', fn() => middleware_roles(['ngo', 'dmc_admin'])]);
route('POST', '/dashboard/donations/{id}/delete', 'donations_delete_action', ['middleware_auth', fn() => middleware_roles(['ngo', 'dmc_admin'])]);
