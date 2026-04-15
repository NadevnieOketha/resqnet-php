<?php

/**
 * Auth Module — Models
 */

function auth_allowed_roles(): array
{
    return ['general', 'volunteer', 'ngo', 'grama_niladhari', 'dmc'];
}

function auth_table_exists(string $tableName): bool
{
    $row = db_fetch(
        'SELECT COUNT(*) AS cnt
         FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = ?',
        [$tableName]
    );

    return ((int) ($row['cnt'] ?? 0)) > 0;
}

function auth_find_by_identifier(string $usernameOrEmail): ?array
{
    return db_fetch(
        'SELECT user_id, username, password_hash, email, role, active
         FROM users
         WHERE username = ? OR email = ?
         LIMIT 1',
        [$usernameOrEmail, $usernameOrEmail]
    );
}

function auth_find_user_by_id(int $userId): ?array
{
    return db_fetch(
        'SELECT user_id, username, password_hash, email, role, active
         FROM users
         WHERE user_id = ?',
        [$userId]
    );
}

function auth_username_exists(string $username, ?int $ignoreUserId = null): bool
{
    if ($ignoreUserId === null) {
        $row = db_fetch('SELECT user_id FROM users WHERE username = ? LIMIT 1', [$username]);
        return $row !== null;
    }

    $row = db_fetch(
        'SELECT user_id FROM users WHERE username = ? AND user_id != ? LIMIT 1',
        [$username, $ignoreUserId]
    );
    return $row !== null;
}

function auth_email_exists(string $email, ?int $ignoreUserId = null): bool
{
    if ($ignoreUserId === null) {
        $row = db_fetch('SELECT user_id FROM users WHERE email = ? LIMIT 1', [$email]);
        return $row !== null;
    }

    $row = db_fetch(
        'SELECT user_id FROM users WHERE email = ? AND user_id != ? LIMIT 1',
        [$email, $ignoreUserId]
    );
    return $row !== null;
}

function auth_get_profile(int $userId, string $role): ?array
{
    $user = auth_find_user_by_id($userId);
    if (!$user) return null;

    switch ($role) {
        case 'general':
            $profile = db_fetch('SELECT * FROM general_user WHERE user_id = ?', [$userId]) ?? [];
            return array_merge($user, $profile);

        case 'volunteer':
            $profile = db_fetch('SELECT * FROM volunteers WHERE user_id = ?', [$userId]) ?? [];
            $skills = db_fetch_all(
                'SELECT s.skill_name
                 FROM skills s
                 INNER JOIN skills_volunteers sv ON sv.skill_id = s.skill_id
                 WHERE sv.user_id = ?
                 ORDER BY s.skill_name ASC',
                [$userId]
            );
            $preferences = db_fetch_all(
                'SELECT vp.preference_name
                 FROM volunteer_preferences vp
                 INNER JOIN volunteer_preference_volunteers vpv ON vpv.preference_id = vp.preference_id
                 WHERE vpv.user_id = ?
                 ORDER BY vp.preference_name ASC',
                [$userId]
            );

            $profile['skills'] = array_column($skills, 'skill_name');
            $profile['preferences'] = array_column($preferences, 'preference_name');
            return array_merge($user, $profile);

        case 'ngo':
            $profile = db_fetch('SELECT * FROM ngos WHERE user_id = ?', [$userId]) ?? [];
            return array_merge($user, $profile);

        case 'grama_niladhari':
            $profile = db_fetch('SELECT * FROM grama_niladhari WHERE user_id = ?', [$userId]) ?? [];
            return array_merge($user, $profile);

        default:
            return $user;
    }
}

function auth_resolve_display_name(int $userId, string $role, string $fallback = 'User'): string
{
    return match ($role) {
        'general' => (string) (db_fetch('SELECT name FROM general_user WHERE user_id = ?', [$userId])['name'] ?? $fallback),
        'volunteer' => (string) (db_fetch('SELECT name FROM volunteers WHERE user_id = ?', [$userId])['name'] ?? $fallback),
        'ngo' => (string) (db_fetch('SELECT organization_name FROM ngos WHERE user_id = ?', [$userId])['organization_name'] ?? $fallback),
        'grama_niladhari' => (string) (db_fetch('SELECT name FROM grama_niladhari WHERE user_id = ?', [$userId])['name'] ?? $fallback),
        default => $fallback,
    };
}

function auth_create_user(array $data, string $role): int
{
    if (!in_array($role, auth_allowed_roles(), true)) {
        throw new InvalidArgumentException('Invalid role.');
    }

    $pdo = db_connect();
    $pdo->beginTransaction();

    try {
        db_query(
            'INSERT INTO users (username, password_hash, email, role, active) VALUES (?, ?, ?, ?, ?)',
            [
                $data['username'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['email'],
                $role,
                (int) ($data['active'] ?? 1),
            ]
        );

        $userId = (int) $pdo->lastInsertId();

        switch ($role) {
            case 'general':
                db_query(
                    'INSERT INTO general_user (user_id, name, contact_number, house_no, street, city, district, gn_division, sms_alert)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
                    [
                        $userId,
                        $data['name'],
                        $data['contact_number'] ?? null,
                        $data['house_no'] ?? null,
                        $data['street'] ?? null,
                        $data['city'] ?? null,
                        $data['district'] ?? null,
                        $data['gn_division'] ?? null,
                        (int) ($data['sms_alert'] ?? 0),
                    ]
                );
                break;

            case 'volunteer':
                db_query(
                    'INSERT INTO volunteers (user_id, name, age, gender, contact_number, house_no, street, city, district, gn_division)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                    [
                        $userId,
                        $data['name'],
                        $data['age'] !== '' ? (int) $data['age'] : null,
                        $data['gender'] ?? null,
                        $data['contact_number'] ?? null,
                        $data['house_no'] ?? null,
                        $data['street'] ?? null,
                        $data['city'] ?? null,
                        $data['district'] ?? null,
                        $data['gn_division'] ?? null,
                    ]
                );

                auth_sync_volunteer_preferences($userId, $data['preferences'] ?? []);
                auth_sync_volunteer_skills($userId, $data['skills'] ?? []);
                break;

            case 'ngo':
                db_query(
                    'INSERT INTO ngos (user_id, organization_name, registration_number, years_of_operation, address, contact_person_name, contact_person_telephone, contact_person_email)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
                    [
                        $userId,
                        $data['organization_name'],
                        $data['registration_number'],
                        $data['years_of_operation'] !== '' ? (int) $data['years_of_operation'] : null,
                        $data['address'] ?? null,
                        $data['contact_person_name'] ?? null,
                        $data['contact_person_telephone'] ?? null,
                        $data['contact_person_email'] ?? null,
                    ]
                );
                break;

            case 'grama_niladhari':
                db_query(
                    'INSERT INTO grama_niladhari (user_id, name, contact_number, address, gn_division, service_number, gn_division_number)
                     VALUES (?, ?, ?, ?, ?, ?, ?)',
                    [
                        $userId,
                        $data['name'],
                        $data['contact_number'] ?? null,
                        $data['address'] ?? null,
                        $data['gn_division'] ?? null,
                        $data['service_number'] ?? null,
                        $data['gn_division_number'] ?? null,
                    ]
                );
                break;

            case 'dmc':
                // No profile table in current schema for dmc.
                break;
        }

        $pdo->commit();
        return $userId;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function auth_update_profile(int $userId, string $role, array $data): void
{
    $pdo = db_connect();
    $pdo->beginTransaction();

    try {
        $userData = [
            'username' => $data['username'],
            'email' => $data['email'],
        ];

        if (!empty($data['password'])) {
            $userData['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        db_update('users', $userData, ['user_id' => $userId]);

        switch ($role) {
            case 'general':
                db_update('general_user', [
                    'name' => $data['name'],
                    'contact_number' => $data['contact_number'] ?? null,
                    'house_no' => $data['house_no'] ?? null,
                    'street' => $data['street'] ?? null,
                    'city' => $data['city'] ?? null,
                    'district' => $data['district'] ?? null,
                    'gn_division' => $data['gn_division'] ?? null,
                ], ['user_id' => $userId]);
                break;

            case 'volunteer':
                db_update('volunteers', [
                    'name' => $data['name'],
                    'age' => $data['age'] !== '' ? (int) $data['age'] : null,
                    'gender' => $data['gender'] ?? null,
                    'contact_number' => $data['contact_number'] ?? null,
                    'house_no' => $data['house_no'] ?? null,
                    'street' => $data['street'] ?? null,
                    'city' => $data['city'] ?? null,
                    'district' => $data['district'] ?? null,
                    'gn_division' => $data['gn_division'] ?? null,
                ], ['user_id' => $userId]);

                auth_sync_volunteer_preferences($userId, $data['preferences'] ?? []);
                auth_sync_volunteer_skills($userId, $data['skills'] ?? []);
                break;

            case 'ngo':
                db_update('ngos', [
                    'organization_name' => $data['organization_name'],
                    'registration_number' => $data['registration_number'],
                    'years_of_operation' => $data['years_of_operation'] !== '' ? (int) $data['years_of_operation'] : null,
                    'address' => $data['address'] ?? null,
                    'contact_person_name' => $data['contact_person_name'] ?? null,
                    'contact_person_telephone' => $data['contact_person_telephone'] ?? null,
                    'contact_person_email' => $data['contact_person_email'] ?? null,
                ], ['user_id' => $userId]);
                break;

            case 'grama_niladhari':
                db_update('grama_niladhari', [
                    'name' => $data['name'],
                    'contact_number' => $data['contact_number'] ?? null,
                    'address' => $data['address'] ?? null,
                    'gn_division' => $data['gn_division'] ?? null,
                    'service_number' => $data['service_number'] ?? null,
                    'gn_division_number' => $data['gn_division_number'] ?? null,
                ], ['user_id' => $userId]);
                break;
        }

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function auth_set_general_sms_alert(int $userId, bool $enabled): int
{
    return db_update('general_user', ['sms_alert' => $enabled ? 1 : 0], ['user_id' => $userId]);
}

function auth_convert_general_to_volunteer(int $userId, array $data): void
{
    $pdo = db_connect();
    $pdo->beginTransaction();

    try {
        $user = auth_find_user_by_id($userId);
        if (!$user) {
            throw new RuntimeException('User not found for conversion.');
        }

        if ((string) ($user['role'] ?? '') !== 'general') {
            throw new RuntimeException('Only general users can be converted to volunteer.');
        }

        $volunteerData = [
            'name' => (string) ($data['name'] ?? ''),
            'age' => ($data['age'] ?? '') !== '' ? (int) $data['age'] : null,
            'gender' => ($data['gender'] ?? '') !== '' ? (string) $data['gender'] : null,
            'contact_number' => (string) ($data['contact_number'] ?? ''),
            'house_no' => (string) ($data['house_no'] ?? ''),
            'street' => (string) ($data['street'] ?? ''),
            'city' => (string) ($data['city'] ?? ''),
            'district' => (string) ($data['district'] ?? ''),
            'gn_division' => (string) ($data['gn_division'] ?? ''),
        ];

        $existingVolunteer = db_fetch('SELECT user_id FROM volunteers WHERE user_id = ?', [$userId]);
        if ($existingVolunteer) {
            db_update('volunteers', $volunteerData, ['user_id' => $userId]);
        } else {
            db_query(
                'INSERT INTO volunteers (user_id, name, age, gender, contact_number, house_no, street, city, district, gn_division)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    $userId,
                    $volunteerData['name'],
                    $volunteerData['age'],
                    $volunteerData['gender'],
                    $volunteerData['contact_number'],
                    $volunteerData['house_no'],
                    $volunteerData['street'],
                    $volunteerData['city'],
                    $volunteerData['district'],
                    $volunteerData['gn_division'],
                ]
            );
        }

        auth_sync_volunteer_preferences($userId, (array) ($data['preferences'] ?? []));
        auth_sync_volunteer_skills($userId, (array) ($data['skills'] ?? []));

        db_update('users', ['role' => 'volunteer'], ['user_id' => $userId]);

        if (auth_table_exists('forecast_sms_alert_subscription')) {
            db_query(
                'UPDATE forecast_sms_alert_subscription
                 SET role = ?
                 WHERE user_id = ?',
                ['volunteer', $userId]
            );
        }

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function auth_create_password_reset_token(int $userId, int $ttlMinutes = 30): string
{
    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', time() + ($ttlMinutes * 60));

    db_query('UPDATE password_reset_tokens SET used = 1 WHERE user_id = ? AND used = 0', [$userId]);
    db_query(
        'INSERT INTO password_reset_tokens (token, user_id, expires_at, used) VALUES (?, ?, ?, 0)',
        [$token, $userId, $expiresAt]
    );

    return $token;
}

function auth_find_valid_password_reset_token(string $token): ?array
{
    return db_fetch(
        'SELECT prt.token, prt.user_id, prt.expires_at, prt.used, u.email, u.username
         FROM password_reset_tokens prt
         INNER JOIN users u ON u.user_id = prt.user_id
         WHERE prt.token = ? AND prt.used = 0 AND prt.expires_at > NOW()
         LIMIT 1',
        [$token]
    );
}

function auth_mark_password_reset_token_used(string $token): void
{
    db_query('UPDATE password_reset_tokens SET used = 1 WHERE token = ?', [$token]);
}

function auth_reset_user_password(int $userId, string $password): void
{
    db_update('users', ['password_hash' => password_hash($password, PASSWORD_DEFAULT)], ['user_id' => $userId]);
}

function auth_pending_approval_users(): array
{
    return db_fetch_all(
        "SELECT u.user_id, u.username, u.email, u.role, u.active,
                COALESCE(v.name, n.organization_name, u.username) AS display_name
         FROM users u
         LEFT JOIN volunteers v ON v.user_id = u.user_id
         LEFT JOIN ngos n ON n.user_id = u.user_id
         WHERE u.active = 0 AND u.role IN ('volunteer', 'ngo')
         ORDER BY u.user_id DESC"
    );
}

function auth_approve_user(int $userId): int
{
    return db_query(
        "UPDATE users SET active = 1 WHERE user_id = ? AND role IN ('volunteer', 'ngo') AND active = 0",
        [$userId]
    )->rowCount();
}

function auth_create_grama_niladhari_account(array $data): int
{
    // GN accounts stay inactive until the officer confirms access via the email link.
    $data['active'] = 0;
    return auth_create_user($data, 'grama_niladhari');
}

function auth_set_grama_niladhari_active_state(int $userId, bool $active): int
{
    return db_query(
        'UPDATE users
         SET active = ?
         WHERE user_id = ?
           AND role = ?',
        [$active ? 1 : 0, $userId, 'grama_niladhari']
    )->rowCount();
}

function auth_list_grama_niladhari_users(): array
{
    return db_fetch_all(
        "SELECT u.user_id, u.username, u.email, u.active, g.name, g.contact_number, g.gn_division
         FROM users u
         INNER JOIN grama_niladhari g ON g.user_id = u.user_id
         WHERE u.role = 'grama_niladhari'
         ORDER BY u.user_id DESC"
    );
}

function auth_sync_volunteer_preferences(int $userId, array $preferenceNames): void
{
    db_query('DELETE FROM volunteer_preference_volunteers WHERE user_id = ?', [$userId]);

    foreach (auth_clean_string_array($preferenceNames) as $name) {
        $preferenceId = auth_ensure_preference_id($name);
        db_query(
            'INSERT INTO volunteer_preference_volunteers (user_id, preference_id) VALUES (?, ?)',
            [$userId, $preferenceId]
        );
    }
}

function auth_sync_volunteer_skills(int $userId, array $skillNames): void
{
    db_query('DELETE FROM skills_volunteers WHERE user_id = ?', [$userId]);

    foreach (auth_clean_string_array($skillNames) as $name) {
        $skillId = auth_ensure_skill_id($name);
        db_query(
            'INSERT INTO skills_volunteers (user_id, skill_id) VALUES (?, ?)',
            [$userId, $skillId]
        );
    }
}

function auth_ensure_preference_id(string $preferenceName): int
{
    $found = db_fetch(
        'SELECT preference_id FROM volunteer_preferences WHERE preference_name = ? LIMIT 1',
        [$preferenceName]
    );

    if ($found) {
        return (int) $found['preference_id'];
    }

    db_query(
        'INSERT INTO volunteer_preferences (preference_name) VALUES (?)',
        [$preferenceName]
    );

    return (int) db_connect()->lastInsertId();
}

function auth_ensure_skill_id(string $skillName): int
{
    $found = db_fetch(
        'SELECT skill_id FROM skills WHERE skill_name = ? LIMIT 1',
        [$skillName]
    );

    if ($found) {
        return (int) $found['skill_id'];
    }

    db_query(
        'INSERT INTO skills (skill_name) VALUES (?)',
        [$skillName]
    );

    return (int) db_connect()->lastInsertId();
}

function auth_clean_string_array(array $values): array
{
    $clean = [];
    foreach ($values as $value) {
        $item = trim((string) $value);
        if ($item !== '' && !in_array($item, $clean, true)) {
            $clean[] = $item;
        }
    }

    return $clean;
}
