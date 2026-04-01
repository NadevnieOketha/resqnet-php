<?php

/**
 * Auth Module — Controllers
 */

function auth_login(): void
{
    view('auth::login');
}

function auth_login_post(): void
{
    csrf_check();

    $identifier = trim((string) request_input('identifier', request_input('email', '')));
    $password = request_input('password', '');

    if ($identifier === '' || $password === '') {
        flash('error', 'Please fill in all fields.');
        flash_old_input();
        redirect('/login');
    }

    try {
        $user = auth_find_by_identifier($identifier);
    } catch (\PDOException $e) {
        flash('error', 'Database error. Please ensure the database is set up. Run database/schema.sql.');
        flash_old_input();
        redirect('/login');
    }

    $passwordColumn = auth_users_password_column();
    $passwordHash = $user[$passwordColumn] ?? '';

    if (!$user || !is_string($passwordHash) || !password_verify($password, $passwordHash)) {
        flash('error', 'Invalid username/email or password.');
        flash_old_input();
        redirect('/login');
    }

    if (!auth_is_user_active($user)) {
        flash('error', 'Your account is pending approval by DMC. Please try again later.');
        flash_old_input();
        redirect('/login');
    }

    $idColumn = auth_users_id_column();
    $userId = (int) ($user[$idColumn] ?? 0);
    $profile = auth_get_profile($userId, (string) ($user['role'] ?? 'general'));

    $_SESSION['user'] = auth_build_session_user($user, $profile);
    clear_old_input();

    flash('success', 'Welcome back, ' . $_SESSION['user']['name'] . '!');
    redirect('/dashboard');
}

function auth_register(): void
{
    view('auth::register');
}

function auth_register_general(): void
{
    view('auth::register_general');
}

function auth_register_ngo(): void
{
    view('auth::register_ngo');
}

function auth_register_volunteer(): void
{
    view('auth::register_volunteer');
}

function auth_register_form_path(string $role): string
{
    return match ($role) {
        'general' => '/register/general',
        'ngo' => '/register/ngo',
        'volunteer' => '/register/volunteer',
        default => '/register',
    };
}

function auth_register_post(): void
{
    csrf_check();

    $firstInput = static function (array $keys, mixed $default = ''): mixed {
        foreach ($keys as $key) {
            $value = request_input($key, null);
            if ($value !== null && $value !== '') {
                return $value;
            }
        }
        return $default;
    };

    $role = trim((string) $firstInput(['role'], 'general'));
    $allowedRoles = ['general', 'volunteer', 'ngo'];
    if (!in_array($role, $allowedRoles, true)) {
        flash('error', 'Please select a valid role.');
        flash_old_input();
        redirect('/register');
    }

    $formPath = auth_register_form_path($role);

    $username = trim((string) $firstInput(['username'], ''));
    $usernameProvided = $username !== '';
    $email    = trim((string) $firstInput(['email'], ''));
    $password = (string) $firstInput(['password'], '');
    $confirm  = (string) $firstInput(['password_confirmation', 'confirmPassword'], '');

    if ($username === '' && $email !== '' && str_contains($email, '@')) {
        $username = (string) strstr($email, '@', true);
    }

    $errors = [];
    if ($username !== '' && strlen($username) < 3) $errors[] = 'Username must be at least 3 characters.';
    if ($email === '') $errors[] = 'Email is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format.';
    if (strlen((string) $password) < 8) $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    $profileData = [
        'username' => $username,
        'email' => $email,
        'password' => $password,
        'role' => $role,
        'house_no' => trim((string) $firstInput(['house_no', 'houseNo'], '')),
        'street' => trim((string) $firstInput(['street'], '')),
        'city' => trim((string) $firstInput(['city'], '')),
        'district' => trim((string) $firstInput(['district'], '')),
        'gn_division' => trim((string) $firstInput(['gn_division', 'gnDivision'], '')),
        'contact_no' => trim((string) $firstInput(['contact_no', 'contactNo'], '')),
        'full_name' => trim((string) $firstInput(['full_name', 'fullName'], '')),
        'age' => $firstInput(['age'], null),
        'gender' => trim((string) $firstInput(['gender'], '')),
        'org_name' => trim((string) $firstInput(['org_name', 'orgName'], '')),
        'registration_no' => trim((string) $firstInput(['registration_no', 'regNo'], '')),
        'years_of_operation' => $firstInput(['years_of_operation', 'years'], null),
        'contact_person' => trim((string) $firstInput(['contact_person', 'contactPerson'], '')),
        'telephone' => trim((string) $firstInput(['telephone'], '')),
        'address' => trim((string) $firstInput(['address'], '')),
        'skills' => request_input('skills', request_input('skills[]', [])),
        'preferences' => request_input('preferences', request_input('preferences[]', [])),
        'sms_alert' => request_input('sms_alert') ? 1 : 0,
    ];

    if (!is_array($profileData['skills'])) {
        $profileData['skills'] = [];
    }
    if (!is_array($profileData['preferences'])) {
        $profileData['preferences'] = [];
    }

    if ($role === 'general') {
        if ($profileData['full_name'] === '') $errors[] = 'Name is required.';
        if ($profileData['contact_no'] === '') $errors[] = 'Contact number is required.';
        if ($profileData['district'] === '') $errors[] = 'District is required.';
        if ($profileData['gn_division'] === '') $errors[] = 'Grama Niladhari Division is required.';
    }

    if ($role === 'volunteer') {
        if ($profileData['full_name'] === '') $errors[] = 'Name is required.';
        if ($profileData['contact_no'] === '') $errors[] = 'Contact number is required.';
        if (empty($profileData['age']) || (int) $profileData['age'] < 16) $errors[] = 'Volunteer age must be at least 16.';
        if ($profileData['gender'] === '') $errors[] = 'Gender is required.';
        if ($profileData['district'] === '') $errors[] = 'District is required.';
        if (empty($profileData['preferences'])) $errors[] = 'Select at least one volunteer preference.';
        if (!request_input('consent')) $errors[] = 'You must accept the volunteer responsibilities statement.';
    }

    if ($role === 'ngo') {
        if ($profileData['org_name'] === '') $errors[] = 'Organization name is required.';
        if ($profileData['registration_no'] === '') $errors[] = 'Registration number is required.';
        if ($profileData['contact_person'] === '') $errors[] = 'Contact person name is required.';
        if ($profileData['telephone'] === '') $errors[] = 'Telephone is required.';
    }

    if (!empty($errors)) {
        flash('error', implode(' ', $errors));
        flash_old_input();
        redirect($formPath);
    }

    try {
        if (auth_user_exists('email', $email)) {
            flash('error', 'An account with this email already exists.');
            flash_old_input();
            redirect($formPath);
        }

        if ($usernameProvided && auth_user_exists('username', $username)) {
            flash('error', 'That username is already taken.');
            flash_old_input();
            redirect($formPath);
        }

        $profileData['active'] = in_array($role, ['ngo', 'volunteer'], true) ? 0 : 1;
        $userId = (int) auth_create_user($profileData, $role);

        if ($profileData['active'] === 0) {
            clear_old_input();
            flash('success', 'Registration submitted. Your account is pending DMC approval.');
            redirect('/login');
        }

        $user = auth_find_user_by_id($userId);
        $profile = auth_get_profile($userId, $role);
        if ($user) {
            $_SESSION['user'] = auth_build_session_user($user, $profile);
        }

        clear_old_input();
        flash('success', 'Account created successfully!');
        redirect('/dashboard');
    } catch (\Throwable $e) {
        flash('error', 'Unable to complete registration right now. Please verify the database schema and try again.');
        flash_old_input();
        redirect($formPath);
    }
}

function auth_forgot_password(): void
{
    view('auth::forgot_password', [], 'main');
}

function auth_forgot_password_post(): void
{
    csrf_check();

    $identifier = trim((string) request_input('identifier', ''));
    if ($identifier === '') {
        flash('error', 'Please enter your email or username.');
        flash_old_input();
        redirect('/forgot-password');
    }

    $genericMessage = 'If an account exists, a reset link has been generated.';

    try {
        $user = auth_find_by_identifier($identifier);
        if (!$user) {
            flash('success', $genericMessage);
            clear_old_input();
            redirect('/forgot-password');
        }

        $idColumn = auth_users_id_column();
        $userId = (int) ($user[$idColumn] ?? 0);
        if ($userId <= 0) {
            flash('success', $genericMessage);
            clear_old_input();
            redirect('/forgot-password');
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + 3600);
        $stored = auth_create_password_reset_token($userId, $token, $expiresAt);

        flash('success', $genericMessage);
        if ($stored) {
            flash('info', 'Development reset link: ' . base_url('/reset-password?token=' . urlencode($token)));
        }

        clear_old_input();
        redirect('/forgot-password');
    } catch (\Throwable $e) {
        flash('error', 'Unable to create reset token right now.');
        flash_old_input();
        redirect('/forgot-password');
    }
}

function auth_reset_password(): void
{
    $token = trim((string) request_input('token', ''));
    if ($token === '') {
        flash('error', 'Reset token is missing.');
        redirect('/forgot-password');
    }

    $record = auth_find_valid_password_reset_token($token);
    if (!$record) {
        flash('error', 'Reset link is invalid or expired.');
        redirect('/forgot-password');
    }

    view('auth::reset_password', ['token' => $token], 'main');
}

function auth_reset_password_post(): void
{
    csrf_check();

    $token = trim((string) request_input('token', ''));
    $password = (string) request_input('password', '');
    $confirm = (string) request_input('password_confirmation', '');

    if ($token === '') {
        flash('error', 'Reset token is missing.');
        redirect('/forgot-password');
    }

    if (strlen($password) < 8) {
        flash('error', 'Password must be at least 8 characters.');
        redirect('/reset-password?token=' . urlencode($token));
    }

    if ($password !== $confirm) {
        flash('error', 'Passwords do not match.');
        redirect('/reset-password?token=' . urlencode($token));
    }

    $record = auth_find_valid_password_reset_token($token);
    if (!$record) {
        flash('error', 'Reset link is invalid or expired.');
        redirect('/forgot-password');
    }

    $userId = (int) ($record['user_id'] ?? 0);
    if ($userId <= 0) {
        flash('error', 'Invalid reset token payload.');
        redirect('/forgot-password');
    }

    try {
        auth_update_user_credentials($userId, ['password' => $password]);
        auth_mark_password_reset_token_used($token);
        flash('success', 'Password reset successful. Please sign in.');
        redirect('/login');
    } catch (\Throwable $e) {
        flash('error', 'Unable to reset password right now.');
        redirect('/reset-password?token=' . urlencode($token));
    }
}

function auth_profile_edit(): void
{
    $user = auth_user();
    if (!$user) {
        redirect('/login');
    }

    $profile = auth_get_profile((int) $user['id'], (string) $user['role']);

    view('auth::profile', [
        'user' => $user,
        'profile' => $profile,
    ], 'dashboard');
}

function auth_profile_update(): void
{
    csrf_check();

    $user = auth_user();
    if (!$user) {
        redirect('/login');
    }

    $userId = (int) $user['id'];
    $role = (string) $user['role'];

    $email = trim((string) request_input('email', ''));
    $username = trim((string) request_input('username', ''));
    $newPassword = (string) request_input('password', '');
    $passwordConfirm = (string) request_input('password_confirmation', '');

    $errors = [];
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please provide a valid email address.';
    }

    if ($username !== '' && auth_user_exists('username', $username, $userId)) {
        $errors[] = 'That username is already in use.';
    }

    if ($email !== '' && auth_user_exists('email', $email, $userId)) {
        $errors[] = 'That email is already in use.';
    }

    if ($newPassword !== '') {
        if (strlen($newPassword) < 8) {
            $errors[] = 'New password must be at least 8 characters.';
        }
        if ($newPassword !== $passwordConfirm) {
            $errors[] = 'New password and confirmation do not match.';
        }
    }

    if (!empty($errors)) {
        flash('error', implode(' ', $errors));
        redirect('/profile');
    }

    $userUpdates = [];
    if ($email !== '') {
        $userUpdates['email'] = $email;
    }
    if ($username !== '') {
        $userUpdates['username'] = $username;
    }
    if ($newPassword !== '') {
        $userUpdates['password'] = $newPassword;
    }

    $profileUpdates = [
        'full_name' => trim((string) request_input('full_name', '')),
        'contact_no' => trim((string) request_input('contact_no', '')),
        'house_no' => trim((string) request_input('house_no', '')),
        'street' => trim((string) request_input('street', '')),
        'city' => trim((string) request_input('city', '')),
        'district' => trim((string) request_input('district', '')),
        'gn_division' => trim((string) request_input('gn_division', '')),
        'org_name' => trim((string) request_input('org_name', '')),
        'registration_no' => trim((string) request_input('registration_no', '')),
        'years_of_operation' => request_input('years_of_operation', null),
        'contact_person' => trim((string) request_input('contact_person', '')),
        'telephone' => trim((string) request_input('telephone', '')),
        'address' => trim((string) request_input('address', '')),
    ];

    if (auth_normalize_role($role) === 'general') {
        $profileUpdates['sms_alert'] = request_input('sms_alert') ? 1 : 0;
    }

    try {
        auth_update_user_credentials($userId, $userUpdates);
        auth_update_profile($userId, $role, $profileUpdates);

        $freshUser = auth_find_user_by_id($userId);
        $freshProfile = auth_get_profile($userId, $role);
        if ($freshUser) {
            $_SESSION['user'] = auth_build_session_user($freshUser, $freshProfile);
        }

        flash('success', 'Profile updated successfully.');
    } catch (\Throwable $e) {
        flash('error', 'Unable to update profile right now.');
    }

    redirect('/profile');
}

function auth_sms_opt_in_toggle(): void
{
    csrf_check();

    $user = auth_user();
    if (!$user) {
        redirect('/login');
    }

    $role = auth_normalize_role((string) $user['role']);
    if ($role !== 'general') {
        flash('error', 'SMS alert preference is currently available for general users only.');
        redirect('/profile');
    }

    $smsAlert = request_input('sms_alert') ? 1 : 0;

    try {
        auth_update_profile((int) $user['id'], (string) $user['role'], ['sms_alert' => $smsAlert]);
        $freshUser = auth_find_user_by_id((int) $user['id']);
        $freshProfile = auth_get_profile((int) $user['id'], (string) $user['role']);
        if ($freshUser) {
            $_SESSION['user'] = auth_build_session_user($freshUser, $freshProfile);
        }

        flash('success', $smsAlert ? 'SMS alerts enabled.' : 'SMS alerts disabled.');
    } catch (\Throwable $e) {
        flash('error', 'Unable to update SMS alert preference.');
    }

    redirect('/profile');
}

function auth_logout(): void
{
    session_destroy();
    // Start a new session for flash messages
    session_start();
    flash('success', 'You have been logged out.');
    redirect('/login');
}
