<?php

/**
 * Auth Module — Controllers
 */

function auth_login(): void
{
    view('auth::login', [
        'hide_header' => true,
        'page_title' => 'Login',
    ], 'main');
}

function auth_login_post(): void
{
    csrf_check();

    $identifier = trim(request_input('identifier', ''));
    $password = (string) request_input('password', '');

    if ($identifier === '' || $password === '') {
        flash('error', 'Please enter your username/email and password.');
        flash_old_input();
        redirect('/login');
    }

    try {
        $user = auth_find_by_identifier($identifier);
    } catch (\PDOException) {
        flash('error', 'Database error. Please ensure the database is set up using database/schema.sql.');
        flash_old_input();
        redirect('/login');
    }

    if (!$user || !password_verify($password, $user['password_hash'])) {
        flash('error', 'Invalid login credentials.');
        flash_old_input();
        redirect('/login');
    }

    if ((int) $user['active'] !== 1) {
        flash('error', 'Your account is pending DMC approval. Please wait for activation.');
        flash_old_input();
        redirect('/login');
    }

    $_SESSION['user'] = auth_build_session_user($user);
    clear_old_input();

    flash('success', 'Welcome back, ' . auth_display_name() . '!');
    redirect('/dashboard');
}

function auth_register(): void
{
    $options = auth_registration_options();
    view('auth::register', array_merge($options, [
        'page_title' => 'Register',
    ]), 'main');
}

function auth_register_post(): void
{
    csrf_check();

    $role = trim((string) request_input('role', 'general'));
    if (!in_array($role, ['general', 'volunteer', 'ngo'], true)) {
        flash('error', 'Invalid registration role selected.');
        flash_old_input();
        redirect('/register');
    }

    $username = trim((string) request_input('username', ''));
    $password = (string) request_input('password', '');
    $confirm = (string) request_input('password_confirmation', '');

    $email = $role === 'ngo'
        ? trim((string) request_input('contact_person_email', ''))
        : trim((string) request_input('email', ''));

    $errors = [];

    if ($username === '') $errors[] = 'Username is required.';
    if (!preg_match('/^[A-Za-z0-9_.-]{4,100}$/', $username)) {
        $errors[] = 'Username must be 4-100 chars and use only letters, numbers, dot, underscore, or dash.';
    }

    if ($email === '') $errors[] = 'Email is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format.';

    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if (auth_username_exists($username)) $errors[] = 'Username is already taken.';
    if (auth_email_exists($email)) $errors[] = 'Email is already registered.';

    $payload = [
        'username' => $username,
        'password' => $password,
        'email' => $email,
    ];

    if ($role === 'general') {
        $payload = array_merge($payload, auth_collect_general_fields($errors));
        $payload['active'] = 1;
    }

    if ($role === 'volunteer') {
        $payload = array_merge($payload, auth_collect_volunteer_fields($errors));
        $payload['active'] = 0;
    }

    if ($role === 'ngo') {
        $payload = array_merge($payload, auth_collect_ngo_fields($errors));
        $payload['active'] = 0;
    }

    if (!empty($errors)) {
        flash('error', implode(' ', $errors));
        flash_old_input();
        redirect('/register');
    }

    try {
        $userId = auth_create_user($payload, $role);
    } catch (\Throwable $e) {
        flash('error', 'Registration failed. Please try again.');
        flash_old_input();
        redirect('/register');
    }

    clear_old_input();

    if ($role === 'general') {
        $user = auth_find_user_by_id($userId);
        if ($user) {
            $_SESSION['user'] = auth_build_session_user($user);
            flash('success', 'Account created successfully.');
            redirect('/dashboard');
        }
    }

    flash('success', 'Registration submitted successfully. Your account will be activated by DMC.');
    redirect('/login');
}

function auth_forgot_password(): void
{
    view('auth::forgot_password', [
        'hide_header' => true,
        'page_title' => 'Forgot Password',
    ], 'main');
}

function auth_forgot_password_post(): void
{
    csrf_check();

    $identifier = trim((string) request_input('identifier', ''));
    if ($identifier === '') {
        flash('error', 'Please enter your username or email.');
        flash_old_input();
        redirect('/forgot-password');
    }

    $user = auth_find_by_identifier($identifier);
    if (!$user) {
        flash('success', 'If an account exists, a reset link has been sent.');
        redirect('/forgot-password');
    }

    $resetTtlMinutes = auth_password_reset_ttl_minutes();
    $token = auth_create_password_reset_token((int) $user['user_id'], $resetTtlMinutes);
    $resetLink = base_url('/reset-password?token=' . urlencode($token));
    $resetTtlLabel = $resetTtlMinutes . ' minutes';
    if ($resetTtlMinutes % 60 === 0) {
        $hours = (int) ($resetTtlMinutes / 60);
        $resetTtlLabel = $hours . ' hour' . ($hours === 1 ? '' : 's');
    }

    $subject = 'resqnet password reset';
    $html = '<p>Hello ' . e($user['username']) . ',</p>'
        . '<p>Use this link to reset your password:</p>'
        . '<p><a href="' . e($resetLink) . '">' . e($resetLink) . '</a></p>'
        . '<p>This link expires in ' . e($resetTtlLabel) . '.</p>';

    $sent = mail_send((string) $user['email'], $subject, $html, "Reset link: {$resetLink}");

    if ($sent) {
        flash('success', 'A reset link has been sent to your email.');
    } else {
        $mailError = mail_last_error();
        $reason = $mailError !== '' ? ' Reason: ' . $mailError : '';
        flash('warning', 'Email sending failed. Check storage/logs/mail.log for SMTP details.' . $reason . ' Use this reset link: ' . $resetLink);
    }

    clear_old_input();
    redirect('/forgot-password');
}

function auth_reset_password(): void
{
    $token = trim((string) request_query('token', ''));
    $tokenData = $token === '' ? null : auth_find_valid_password_reset_token($token);

    view('auth::reset_password', [
        'hide_header' => true,
        'page_title' => 'Reset Password',
        'token' => $token,
        'token_valid' => $tokenData !== null,
    ], 'main');
}

function auth_reset_password_post(): void
{
    csrf_check();

    $token = trim((string) request_input('token', ''));
    $password = (string) request_input('password', '');
    $confirm = (string) request_input('password_confirmation', '');

    $tokenData = $token === '' ? null : auth_find_valid_password_reset_token($token);
    if (!$tokenData) {
        flash('error', 'Invalid, expired, or superseded reset token. Please request a new link.');
        redirect('/reset-password?token=' . urlencode($token));
    }

    if (strlen($password) < 6) {
        flash('error', 'Password must be at least 6 characters.');
        redirect('/reset-password?token=' . urlencode($token));
    }

    if ($password !== $confirm) {
        flash('error', 'Passwords do not match.');
        redirect('/reset-password?token=' . urlencode($token));
    }

    auth_reset_user_password((int) $tokenData['user_id'], $password);
    auth_mark_password_reset_token_used($token);

    $userRecord = auth_find_user_by_id((int) $tokenData['user_id']);
    $autoActivated = false;
    if ($userRecord && (string) ($userRecord['role'] ?? '') === 'grama_niladhari' && (int) ($userRecord['active'] ?? 0) !== 1) {
        auth_set_grama_niladhari_active_state((int) $tokenData['user_id'], true);
        $autoActivated = true;
    }

    if ($autoActivated) {
        flash('success', 'Password updated successfully. Your GN account is now active. Please sign in.');
    } else {
        flash('success', 'Password updated successfully. Please sign in.');
    }
    redirect('/login');
}

function auth_profile(): void
{
    $user = auth_user();
    $role = (string) ($user['role'] ?? '');
    $profile = auth_get_profile((int) auth_id(), $role);

    view('auth::profile', array_merge(auth_registration_options(), [
        'profile' => $profile,
        'role' => $role,
        'breadcrumb' => 'Profile',
    ]), 'dashboard');
}

function auth_profile_post(): void
{
    csrf_check();

    $userId = (int) auth_id();
    $role = (string) user_role();

    $username = trim((string) request_input('username', ''));
    $inputEmail = trim((string) request_input('email', ''));
    $email = $role === 'ngo'
        ? trim((string) request_input('contact_person_email', $inputEmail))
        : $inputEmail;
    $password = (string) request_input('password', '');
    $passwordConfirmation = (string) request_input('password_confirmation', '');

    $errors = [];

    if ($username === '') $errors[] = 'Username is required.';
    if (!preg_match('/^[A-Za-z0-9_.-]{4,100}$/', $username)) {
        $errors[] = 'Username format is invalid.';
    }

    if ($email === '') $errors[] = 'Email is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format.';

    if (auth_username_exists($username, $userId)) $errors[] = 'Username is already taken.';
    if (auth_email_exists($email, $userId)) $errors[] = 'Email is already registered.';

    if ($password !== '' && strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    if ($password !== '' && $password !== $passwordConfirmation) {
        $errors[] = 'Passwords do not match.';
    }

    $payload = [
        'username' => $username,
        'email' => $email,
        'password' => $password,
    ];

    if ($role === 'general') {
        $payload = array_merge($payload, auth_collect_general_fields($errors));
    }

    if ($role === 'volunteer') {
        $payload = array_merge($payload, auth_collect_volunteer_fields($errors));
    }

    if ($role === 'ngo') {
        $payload = array_merge($payload, auth_collect_ngo_fields($errors));
        $payload['email'] = $payload['contact_person_email'];
    }

    if ($role === 'grama_niladhari') {
        $payload = array_merge($payload, auth_collect_grama_niladhari_fields($errors));
    }

    if (!empty($errors)) {
        flash('error', implode(' ', $errors));
        flash_old_input();
        redirect('/profile');
    }

    try {
        auth_update_profile($userId, $role, $payload);
    } catch (\Throwable) {
        flash('error', 'Profile update failed.');
        flash_old_input();
        redirect('/profile');
    }

    $freshUser = auth_find_user_by_id($userId);
    if ($freshUser) {
        $_SESSION['user'] = auth_build_session_user($freshUser);
    }

    clear_old_input();
    flash('success', 'Profile updated successfully.');
    redirect('/profile');
}

function auth_delete_account_post(): void
{
    csrf_check();

    if (!auth_check()) {
        redirect('/login');
    }

    $userId = (int) auth_id();
    $role = (string) (user_role() ?? '');

    if (!in_array($role, ['general', 'volunteer', 'ngo'], true)) {
        abort(403, 'Only General users, Volunteers, and NGOs can delete their own accounts.');
    }

    if ((string) request_input('confirm_delete', '0') !== '1') {
        flash('error', 'Account deletion confirmation is missing.');
        redirect('/profile');
    }

    try {
        $deleted = auth_delete_account($userId, $role);
        if (!$deleted) {
            throw new RuntimeException('Unable to delete account.');
        }
    } catch (Throwable $e) {
        flash('error', 'Unable to delete account right now. Please try again.');
        redirect('/profile');
    }

    session_destroy();
    session_start();
    flash('success', 'Your account has been deleted successfully.');
    redirect('/login');
}

function auth_become_volunteer_form(): void
{
    $userId = (int) auth_id();
    $profile = auth_get_profile($userId, 'general');

    if (!$profile) {
        flash('error', 'Unable to load your profile details for volunteer conversion.');
        redirect('/dashboard');
    }

    view('auth::become_volunteer', array_merge(auth_registration_options(), [
        'profile' => $profile,
        'breadcrumb' => 'Become Volunteer',
    ]), 'dashboard');
}

function auth_become_volunteer_post(): void
{
    csrf_check();

    $userId = (int) auth_id();
    $user = auth_find_user_by_id($userId);
    if (!$user || (string) ($user['role'] ?? '') !== 'general') {
        abort(403, 'Only general users can use this action.');
    }

    $profile = auth_get_profile($userId, 'general') ?? [];
    $errors = [];
    $payload = auth_collect_become_volunteer_fields($errors, (array) $profile);

    if (!empty($errors)) {
        flash('error', implode(' ', $errors));
        flash_old_input();
        redirect('/dashboard/become-volunteer');
    }

    try {
        auth_convert_general_to_volunteer($userId, $payload);
    } catch (\Throwable) {
        flash('error', 'Unable to convert account to volunteer at this time.');
        flash_old_input();
        redirect('/dashboard/become-volunteer');
    }

    $freshUser = auth_find_user_by_id($userId);
    if ($freshUser) {
        $_SESSION['user'] = auth_build_session_user($freshUser);
    }

    clear_old_input();
    flash('success', 'Your account has been converted to Volunteer successfully.');
    redirect('/dashboard');
}

function auth_dmc_pending_approvals(): void
{
    $pendingUsers = auth_pending_approval_users();
    $pendingVolunteers = array_values(array_filter(
        $pendingUsers,
        static fn(array $row): bool => (string) ($row['role'] ?? '') === 'volunteer'
    ));
    $pendingNgos = array_values(array_filter(
        $pendingUsers,
        static fn(array $row): bool => (string) ($row['role'] ?? '') === 'ngo'
    ));

    view('auth::dmc_pending', [
        'pending_volunteers' => $pendingVolunteers,
        'pending_ngos' => $pendingNgos,
        'breadcrumb' => 'Pending Approvals',
    ], 'dashboard');
}

function auth_dmc_gn_accounts_index(): void
{
    $gnUsers = auth_list_grama_niladhari_users();

    view('auth::dmc_gn_accounts', [
        'gn_users' => $gnUsers,
        'breadcrumb' => 'GN Accounts',
    ], 'dashboard');
}

function auth_dmc_approve_user_action(string $userId): void
{
    csrf_check();

    $affected = auth_approve_user((int) $userId);
    if ($affected > 0) {
        flash('success', 'Account approved successfully.');
    } else {
        flash('error', 'Unable to approve this account.');
    }

    redirect('/dashboard/admin/pending');
}

function auth_dmc_create_gn_form(): void
{
    view('auth::dmc_create_gn', array_merge(auth_registration_options(), [
        'breadcrumb' => 'GN Accounts',
    ]), 'dashboard');
}

function auth_dmc_create_gn_post(): void
{
    csrf_check();

    $username = trim((string) request_input('username', ''));
    $email = trim((string) request_input('email', ''));
    $password = (string) request_input('password', '');
    $confirm = (string) request_input('password_confirmation', '');

    $errors = [];

    if ($username === '') $errors[] = 'Username is required.';
    if (!preg_match('/^[A-Za-z0-9_.-]{4,100}$/', $username)) $errors[] = 'Invalid username format.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';
    if (auth_username_exists($username)) $errors[] = 'Username is already taken.';
    if (auth_email_exists($email)) $errors[] = 'Email is already registered.';

    $payload = array_merge([
        'username' => $username,
        'email' => $email,
        'password' => $password,
    ], auth_collect_grama_niladhari_fields($errors));

    if (!empty($errors)) {
        flash('error', implode(' ', $errors));
        flash_old_input();
        redirect('/dashboard/admin/grama-niladhari/create');
    }

    try {
        $userId = auth_create_grama_niladhari_account($payload);
    } catch (\Throwable) {
        flash('error', 'Failed to create Grama Niladhari account.');
        flash_old_input();
        redirect('/dashboard/admin/grama-niladhari/create');
    }

    $token = auth_create_password_reset_token($userId, auth_password_reset_ttl_minutes());
    $resetLink = base_url('/reset-password?token=' . urlencode($token));

    $subject = 'resqnet Grama Niladhari account access confirmation';
    $html = '<p>Your Grama Niladhari account has been created by DMC.</p>'
        . '<p>Username: <strong>' . e($username) . '</strong></p>'
        . '<p>Use this link to set your password and confirm account access:</p>'
        . '<p><a href="' . e($resetLink) . '">' . e($resetLink) . '</a></p>'
        . '<p>After this step, your account becomes active.</p>';

    $sent = mail_send($email, $subject, $html, "Username: {$username}\nActivation link: {$resetLink}");

    clear_old_input();
    if ($sent) {
        flash('success', 'GN account created. Activation link sent to email; account becomes active after confirmation.');
    } else {
        $mailError = mail_last_error();
        $reason = $mailError !== '' ? ' Reason: ' . $mailError : '';
        flash('warning', 'GN account created, but email failed.' . $reason . ' Share this activation link manually: ' . $resetLink);
    }

    redirect('/dashboard/admin/grama-niladhari/accounts');
}

function auth_dmc_resend_gn_credentials(string $userId): void
{
    csrf_check();

    $user = auth_find_user_by_id((int) $userId);
    if (!$user || $user['role'] !== 'grama_niladhari') {
        flash('error', 'Selected user is not a Grama Niladhari account.');
        redirect('/dashboard/admin/grama-niladhari/accounts');
    }

    $token = auth_create_password_reset_token((int) $user['user_id'], auth_password_reset_ttl_minutes());
    $resetLink = base_url('/reset-password?token=' . urlencode($token));

    $subject = 'resqnet Grama Niladhari account access';
    $html = '<p>Account access details:</p>'
        . '<p>Username: <strong>' . e($user['username']) . '</strong></p>'
        . '<p>Use this reset link to set a new password:</p>'
        . '<p><a href="' . e($resetLink) . '">' . e($resetLink) . '</a></p>';

    $sent = mail_send((string) $user['email'], $subject, $html, "Username: {$user['username']}\nReset link: {$resetLink}");

    if ($sent) {
        flash('success', 'Credential email sent successfully.');
    } else {
        $mailError = mail_last_error();
        $reason = $mailError !== '' ? ' Reason: ' . $mailError : '';
        flash('warning', 'Email failed.' . $reason . ' Share this reset link manually: ' . $resetLink);
    }

    redirect('/dashboard/admin/grama-niladhari/accounts');
}

function auth_dmc_activate_gn_account_action(string $userId): void
{
    csrf_check();

    $affected = auth_set_grama_niladhari_active_state((int) $userId, true);
    if ($affected > 0) {
        flash('success', 'GN account activated.');
    } else {
        flash('warning', 'No GN account was activated.');
    }

    redirect('/dashboard/admin/grama-niladhari/accounts');
}

function auth_dmc_deactivate_gn_account_action(string $userId): void
{
    csrf_check();

    $affected = auth_set_grama_niladhari_active_state((int) $userId, false);
    if ($affected > 0) {
        flash('success', 'GN account deactivated.');
    } else {
        flash('warning', 'No GN account was deactivated.');
    }

    redirect('/dashboard/admin/grama-niladhari/accounts');
}

function auth_logout(): void
{
    session_destroy();
    session_start();
    flash('success', 'You have been logged out.');
    redirect('/login');
}

function auth_registration_options(): array
{
    return [
        'districts' => config('auth_options.districts', []),
        'gn_divisions' => config('auth_options.gn_divisions', []),
        'volunteer_preferences' => config('auth_options.volunteer_preferences', []),
        'volunteer_skills' => config('auth_options.volunteer_skills', []),
        'genders' => config('auth_options.genders', []),
    ];
}

function auth_build_session_user(array $user): array
{
    $displayName = auth_resolve_display_name((int) $user['user_id'], (string) $user['role'], (string) $user['username']);

    return [
        'user_id' => (int) $user['user_id'],
        'username' => (string) $user['username'],
        'email' => (string) $user['email'],
        'role' => (string) $user['role'],
        'active' => (int) $user['active'],
        'display_name' => $displayName,
    ];
}

function auth_collect_general_fields(array &$errors): array
{
    $name = trim((string) request_input('name', ''));
    $contact = trim((string) request_input('contact_number', ''));
    $houseNo = trim((string) request_input('house_no', ''));
    $street = trim((string) request_input('street', ''));
    $city = trim((string) request_input('city', ''));
    $district = trim((string) request_input('district', ''));
    $gnDivision = auth_resolve_gn_division_input();

    if ($name === '') $errors[] = 'Name is required.';
    if ($contact === '') $errors[] = 'Contact number is required.';
    if ($houseNo === '') $errors[] = 'House number is required.';
    if ($street === '') $errors[] = 'Street is required.';
    if ($city === '') $errors[] = 'City is required.';
    if ($district === '') $errors[] = 'District is required.';
    if ($gnDivision === '') $errors[] = 'Grama Niladhari division is required.';

    return [
        'name' => $name,
        'contact_number' => $contact,
        'house_no' => $houseNo,
        'street' => $street,
        'city' => $city,
        'district' => $district,
        'gn_division' => $gnDivision,
    ];
}

function auth_collect_volunteer_fields(array &$errors): array
{
    $name = trim((string) request_input('name', ''));
    $age = trim((string) request_input('age', ''));
    $gender = trim((string) request_input('gender', ''));
    $contact = trim((string) request_input('contact_number', ''));
    $houseNo = trim((string) request_input('house_no', ''));
    $street = trim((string) request_input('street', ''));
    $city = trim((string) request_input('city', ''));
    $district = trim((string) request_input('district', ''));
    $gnDivision = auth_resolve_gn_division_input();

    $preferences = request_input('preferences', []);
    $skills = request_input('skills', []);

    if (!is_array($preferences)) $preferences = [];
    if (!is_array($skills)) $skills = [];

    if ($name === '') $errors[] = 'Name is required.';
    if ($age === '' || !ctype_digit($age)) $errors[] = 'Valid age is required.';
    if (!in_array($gender, config('auth_options.genders', []), true)) $errors[] = 'Valid gender is required.';
    if ($contact === '') $errors[] = 'Contact number is required.';
    if ($houseNo === '') $errors[] = 'House number is required.';
    if ($street === '') $errors[] = 'Street is required.';
    if ($city === '') $errors[] = 'City is required.';
    if ($district === '') $errors[] = 'District is required.';
    if ($gnDivision === '') $errors[] = 'Grama Niladhari division is required.';

    return [
        'name' => $name,
        'age' => $age,
        'gender' => $gender,
        'contact_number' => $contact,
        'house_no' => $houseNo,
        'street' => $street,
        'city' => $city,
        'district' => $district,
        'gn_division' => $gnDivision,
        'preferences' => array_map('strval', $preferences),
        'skills' => array_map('strval', $skills),
    ];
}

function auth_collect_ngo_fields(array &$errors): array
{
    $orgName = trim((string) request_input('organization_name', ''));
    $regNumber = trim((string) request_input('registration_number', ''));
    $years = trim((string) request_input('years_of_operation', ''));
    $address = trim((string) request_input('address', ''));
    $contactName = trim((string) request_input('contact_person_name', ''));
    $contactEmail = trim((string) request_input('contact_person_email', ''));
    $contactTel = trim((string) request_input('contact_person_telephone', ''));

    if ($orgName === '') $errors[] = 'Organization name is required.';
    if ($regNumber === '') $errors[] = 'Registration number is required.';
    if ($address === '') $errors[] = 'Organization address is required.';
    if ($contactName === '') $errors[] = 'Contact person name is required.';
    if ($contactEmail === '' || !filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid contact person email is required.';
    }
    if ($contactTel === '') $errors[] = 'Contact person telephone is required.';
    if ($years !== '' && !ctype_digit($years)) $errors[] = 'Years of operation must be a number.';

    return [
        'organization_name' => $orgName,
        'registration_number' => $regNumber,
        'years_of_operation' => $years,
        'address' => $address,
        'contact_person_name' => $contactName,
        'contact_person_email' => $contactEmail,
        'contact_person_telephone' => $contactTel,
        'email' => $contactEmail,
    ];
}

function auth_collect_grama_niladhari_fields(array &$errors): array
{
    $name = trim((string) request_input('name', ''));
    $contact = trim((string) request_input('contact_number', ''));
    $address = trim((string) request_input('address', ''));
    $gnDivision = auth_resolve_gn_division_input();
    $serviceNumber = trim((string) request_input('service_number', ''));
    $gnDivisionNumber = trim((string) request_input('gn_division_number', ''));

    if ($name === '') $errors[] = 'Name is required.';
    if ($contact === '') $errors[] = 'Contact number is required.';
    if ($address === '') $errors[] = 'Address is required.';
    if ($gnDivision === '') $errors[] = 'Grama Niladhari division is required.';
    if ($serviceNumber === '') $errors[] = 'Service number is required.';
    if ($gnDivisionNumber === '') $errors[] = 'GN division number is required.';

    return [
        'name' => $name,
        'contact_number' => $contact,
        'address' => $address,
        'gn_division' => $gnDivision,
        'service_number' => $serviceNumber,
        'gn_division_number' => $gnDivisionNumber,
    ];
}

function auth_resolve_gn_division_input(): string
{
    $selected = trim((string) request_input('gn_division', ''));
    $other = trim((string) request_input('gn_division_other', ''));

    if ($selected === '__other__') {
        return $other;
    }

    if ($selected === '' && $other !== '') {
        return $other;
    }

    return $selected;
}

function auth_collect_become_volunteer_fields(array &$errors, array $profile = []): array
{
    $name = trim((string) request_input('name', (string) ($profile['name'] ?? '')));
    $age = trim((string) request_input('age', ''));
    $gender = trim((string) request_input('gender', ''));
    $contact = trim((string) request_input('contact_number', (string) ($profile['contact_number'] ?? '')));
    $houseNo = trim((string) request_input('house_no', (string) ($profile['house_no'] ?? '')));
    $street = trim((string) request_input('street', (string) ($profile['street'] ?? '')));
    $city = trim((string) request_input('city', (string) ($profile['city'] ?? '')));
    $district = trim((string) request_input('district', (string) ($profile['district'] ?? '')));
    $gnDivision = auth_resolve_gn_division_input();

    if ($gnDivision === '') {
        $gnDivision = trim((string) ($profile['gn_division'] ?? ''));
    }

    $preferences = request_input('preferences', []);
    $skills = request_input('skills', []);

    if (!is_array($preferences)) {
        $preferences = [];
    }
    if (!is_array($skills)) {
        $skills = [];
    }

    if ($name === '') $errors[] = 'Name is required.';
    if ($contact === '') $errors[] = 'Contact number is required.';
    if ($houseNo === '') $errors[] = 'House number is required.';
    if ($street === '') $errors[] = 'Street is required.';
    if ($city === '') $errors[] = 'City is required.';
    if ($district === '') $errors[] = 'District is required.';
    if ($gnDivision === '') $errors[] = 'Grama Niladhari division is required.';

    if ($age !== '') {
        if (!ctype_digit($age)) {
            $errors[] = 'Age must be a valid number.';
        } elseif ((int) $age < 16 || (int) $age > 100) {
            $errors[] = 'Age must be between 16 and 100.';
        }
    }

    if ($gender !== '' && !in_array($gender, config('auth_options.genders', []), true)) {
        $errors[] = 'Please select a valid gender.';
    }

    $skills = array_map('strval', $skills);
    $preferences = array_map('strval', $preferences);

    if (count(array_filter($skills, static fn(string $item): bool => trim($item) !== '')) === 0) {
        $errors[] = 'Please select at least one specialized skill.';
    }

    return [
        'name' => $name,
        'age' => $age,
        'gender' => $gender,
        'contact_number' => $contact,
        'house_no' => $houseNo,
        'street' => $street,
        'city' => $city,
        'district' => $district,
        'gn_division' => $gnDivision,
        'preferences' => $preferences,
        'skills' => $skills,
    ];
}
