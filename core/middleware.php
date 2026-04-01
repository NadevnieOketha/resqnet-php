<?php

/**
 * Middleware Functions
 * 
 * Route guards for authentication and role-based access control.
 */

/**
 * Require authentication. Redirects to /login if not logged in.
 */
function middleware_auth(): void
{
    if (!auth_check()) {
        flash('error', 'Please log in to continue.');
        redirect('/login');
    }

    if ((int) (auth_user()['active'] ?? 0) !== 1) {
        session_destroy();
        session_start();
        flash('error', 'Your account is inactive. Please contact DMC.');
        redirect('/login');
    }
}

/**
 * Require guest (not logged in). Redirects to /dashboard if already authenticated.
 */
function middleware_guest(): void
{
    if (auth_check()) {
        redirect('/dashboard');
    }
}

/**
 * Require a specific role.
 * 
 * Usage in routes:
 *   route('GET', '/dashboard/admin/pending', 'auth_dmc_pending_approvals', ['middleware_auth', fn() => middleware_role('dmc')]);
 */
function middleware_role(string $role): void
{
    if (!is_role($role)) {
        abort(403, 'You do not have permission to access this page.');
    }
}

/**
 * Require any one role from a list.
 */
function middleware_roles(array $roles): void
{
    if (!is_any_role($roles)) {
        abort(403, 'You do not have permission to access this page.');
    }
}
