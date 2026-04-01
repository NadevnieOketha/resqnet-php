<?php

/**
 * Auth Module — Models
 */

function auth_table_columns(string $table): array
{
    static $cache = [];

    if (isset($cache[$table])) {
        return $cache[$table];
    }

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
        $cache[$table] = [];
        return [];
    }

    try {
        $rows = db_fetch_all("SHOW COLUMNS FROM `{$table}`");
    } catch (\PDOException) {
        $cache[$table] = [];
        return [];
    }

    $columns = [];
    foreach ($rows as $row) {
        $columns[$row['Field']] = $row;
    }

    $cache[$table] = $columns;
    return $columns;
}

function auth_table_exists(string $table): bool
{
    return !empty(auth_table_columns($table));
}

function auth_users_id_column(): string
{
    $columns = auth_table_columns('users');
    return isset($columns['user_id']) ? 'user_id' : 'id';
}

function auth_users_password_column(): string
{
    $columns = auth_table_columns('users');
    return isset($columns['password_hash']) ? 'password_hash' : 'password';
}

function auth_normalize_role(string $role): string
{
    return match ($role) {
        'general_public' => 'general',
        'dmc_admin' => 'dmc',
        default => $role,
    };
}

function auth_users_allowed_roles(): array
{
    $columns = auth_table_columns('users');
    $roleType = $columns['role']['Type'] ?? '';

    if (preg_match_all("/'([^']+)'/", $roleType, $matches)) {
        return $matches[1];
    }

    return [];
}

function auth_resolve_role_for_storage(string $requestedRole): string
{
    $role = auth_normalize_role($requestedRole);
    $allowed = auth_users_allowed_roles();

    if (empty($allowed)) {
        return $role;
    }

    if (in_array($role, $allowed, true)) {
        return $role;
    }

    $fallbacks = [
        'general' => ['general_public'],
        'general_public' => ['general'],
        'dmc' => ['dmc_admin'],
        'dmc_admin' => ['dmc'],
        'volunteer' => ['general', 'general_public'],
    ];

    foreach (($fallbacks[$requestedRole] ?? []) as $candidate) {
        if (in_array($candidate, $allowed, true)) {
            return $candidate;
        }
    }

    foreach (($fallbacks[$role] ?? []) as $candidate) {
        if (in_array($candidate, $allowed, true)) {
            return $candidate;
        }
    }

    return $allowed[0];
}

function auth_profile_table_for_role(string $role): ?string
{
    $canonical = auth_normalize_role($role);

    return match ($canonical) {
        'general' => 'general_user',
        'volunteer' => 'volunteers',
        'ngo' => 'ngos',
        'grama_niladhari' => 'grama_niladhari',
        'dmc' => 'dmc',
        default => null,
    };
}

function auth_find_by_identifier(string $usernameOrEmail): ?array
{
    $identifier = trim($usernameOrEmail);
    if ($identifier === '') {
        return null;
    }

    $columns = auth_table_columns('users');
    if (empty($columns)) {
        return null;
    }

    $conditions = [];
    $params = [];

    if (isset($columns['username'])) {
        $conditions[] = 'username = ?';
        $params[] = $identifier;
    }

    if (isset($columns['email'])) {
        $conditions[] = 'email = ?';
        $params[] = $identifier;
    }

    if (empty($conditions)) {
        return null;
    }

    $sql = 'SELECT * FROM users WHERE ' . implode(' OR ', $conditions) . ' LIMIT 1';
    return db_fetch($sql, $params);
}

function auth_find_user_by_id(int $userId): ?array
{
    $idColumn = auth_users_id_column();
    return db_fetch("SELECT * FROM users WHERE {$idColumn} = ? LIMIT 1", [$userId]);
}

function auth_user_exists(string $column, string $value, ?int $excludeUserId = null): bool
{
    $columns = auth_table_columns('users');
    if (!isset($columns[$column])) {
        return false;
    }

    $idColumn = auth_users_id_column();
    $sql = "SELECT {$idColumn} FROM users WHERE {$column} = ?";
    $params = [$value];

    if ($excludeUserId !== null) {
        $sql .= " AND {$idColumn} <> ?";
        $params[] = $excludeUserId;
    }

    $sql .= ' LIMIT 1';
    return db_fetch($sql, $params) !== null;
}

function auth_is_user_active(array $user): bool
{
    if (!array_key_exists('active', $user)) {
        return true;
    }

    return (int) $user['active'] === 1;
}

function auth_get_profile(int $userId, string $role): ?array
{
    $table = auth_profile_table_for_role($role);
    if ($table === null || !auth_table_exists($table)) {
        return null;
    }

    $columns = auth_table_columns($table);
    $fkColumn = isset($columns['user_id']) ? 'user_id' : (isset($columns['id']) ? 'id' : null);
    if ($fkColumn === null) {
        return null;
    }

    return db_fetch("SELECT * FROM {$table} WHERE {$fkColumn} = ? LIMIT 1", [$userId]);
}

function auth_compose_address(array $data): string
{
    $parts = [
        trim((string) ($data['house_no'] ?? '')),
        trim((string) ($data['street'] ?? '')),
        trim((string) ($data['city'] ?? '')),
    ];

    $parts = array_values(array_filter($parts, fn($part) => $part !== ''));
    return implode(', ', $parts);
}

function auth_insert_profile(int $userId, string $role, array $data): void
{
    $table = auth_profile_table_for_role($role);
    if ($table === null || !auth_table_exists($table)) {
        return;
    }

    $columns = auth_table_columns($table);
    $fullName = trim((string) ($data['full_name'] ?? $data['contact_person'] ?? $data['org_name'] ?? $data['username'] ?? ''));
    $contact = trim((string) ($data['contact_no'] ?? $data['telephone'] ?? ''));
    $address = trim((string) ($data['address'] ?? auth_compose_address($data)));

    $candidateValues = [
        'user_id' => $userId,
        'id' => $userId,
        'name' => $fullName,
        'full_name' => $fullName,
        'contact' => $contact,
        'contact_no' => $contact,
        'contact_number' => $contact,
        'telephone' => trim((string) ($data['telephone'] ?? $contact)),
        'email' => trim((string) ($data['email'] ?? '')),
        'address' => $address,
        'district' => trim((string) ($data['district'] ?? '')),
        'gn_division' => trim((string) ($data['gn_division'] ?? '')),
        'sms_alert' => !empty($data['sms_alert']) ? 1 : 0,
        'age' => isset($data['age']) ? (int) $data['age'] : null,
        'gender' => trim((string) ($data['gender'] ?? '')),
        'organization_name' => trim((string) ($data['org_name'] ?? '')),
        'org_name' => trim((string) ($data['org_name'] ?? '')),
        'registration_no' => trim((string) ($data['registration_no'] ?? '')),
        'registration_number' => trim((string) ($data['registration_no'] ?? '')),
        'reg_no' => trim((string) ($data['registration_no'] ?? '')),
        'years_of_operation' => isset($data['years_of_operation']) ? (int) $data['years_of_operation'] : null,
        'years' => isset($data['years_of_operation']) ? (int) $data['years_of_operation'] : null,
        'contact_person' => trim((string) ($data['contact_person'] ?? $fullName)),
        'contact_person_name' => trim((string) ($data['contact_person'] ?? $fullName)),
    ];

    $insertData = [];
    foreach ($columns as $column => $_meta) {
        if (!array_key_exists($column, $candidateValues)) {
            continue;
        }

        $value = $candidateValues[$column];
        if ($value === null || $value === '') {
            continue;
        }

        $insertData[$column] = $value;
    }

    if (empty($insertData)) {
        return;
    }

    db_insert($table, $insertData);
}

function auth_resolve_lookup_columns(string $table): array
{
    $columns = auth_table_columns($table);

    $idColumn = null;
    foreach (['id', 'skill_id', 'preference_id'] as $candidate) {
        if (isset($columns[$candidate])) {
            $idColumn = $candidate;
            break;
        }
    }

    if ($idColumn === null) {
        foreach ($columns as $name => $meta) {
            if (str_ends_with($name, '_id') && str_contains(strtolower((string) ($meta['Extra'] ?? '')), 'auto_increment')) {
                $idColumn = $name;
                break;
            }
        }
    }

    $nameColumn = null;
    foreach (['name', 'title', 'label', 'skill_name', 'preference_name'] as $candidate) {
        if (isset($columns[$candidate])) {
            $nameColumn = $candidate;
            break;
        }
    }

    return [$idColumn, $nameColumn];
}

function auth_find_or_create_lookup_value(string $table, string $value): ?int
{
    $value = trim($value);
    if ($value === '' || !auth_table_exists($table)) {
        return null;
    }

    [$idColumn, $nameColumn] = auth_resolve_lookup_columns($table);
    if ($idColumn === null || $nameColumn === null) {
        return null;
    }

    $existing = db_fetch("SELECT {$idColumn} FROM {$table} WHERE {$nameColumn} = ? LIMIT 1", [$value]);
    if ($existing) {
        return (int) $existing[$idColumn];
    }

    $newId = db_insert($table, [$nameColumn => $value]);
    return (int) $newId;
}

function auth_attach_lookup_values(int $userId, array $values, string $lookupTable, string $pivotTable): void
{
    if (!auth_table_exists($lookupTable) || !auth_table_exists($pivotTable)) {
        return;
    }

    $pivotColumns = auth_table_columns($pivotTable);
    $userColumn = isset($pivotColumns['volunteer_id']) ? 'volunteer_id' : (isset($pivotColumns['user_id']) ? 'user_id' : null);
    if ($userColumn === null) {
        return;
    }

    $lookupColumn = null;
    if (str_contains($lookupTable, 'skill') && isset($pivotColumns['skill_id'])) {
        $lookupColumn = 'skill_id';
    }
    if (str_contains($lookupTable, 'preference') && isset($pivotColumns['preference_id'])) {
        $lookupColumn = 'preference_id';
    }

    if ($lookupColumn === null) {
        foreach (array_keys($pivotColumns) as $column) {
            if ($column === $userColumn || !str_ends_with($column, '_id')) {
                continue;
            }
            $lookupColumn = $column;
            break;
        }
    }

    if ($lookupColumn === null) {
        return;
    }

    foreach (array_unique(array_filter(array_map('trim', $values))) as $label) {
        $lookupId = auth_find_or_create_lookup_value($lookupTable, $label);
        if ($lookupId === null) {
            continue;
        }

        $exists = db_fetch(
            "SELECT 1 FROM {$pivotTable} WHERE {$userColumn} = ? AND {$lookupColumn} = ? LIMIT 1",
            [$userId, $lookupId]
        );

        if ($exists) {
            continue;
        }

        db_insert($pivotTable, [
            $userColumn => $userId,
            $lookupColumn => $lookupId,
        ]);
    }
}

function auth_create_user(array $data, string $role): string
{
    $columns = auth_table_columns('users');
    if (empty($columns)) {
        throw new \RuntimeException('Users table is not available.');
    }

    $idColumn = auth_users_id_column();
    $passwordColumn = auth_users_password_column();
    $email = trim((string) ($data['email'] ?? ''));
    $requestedRole = trim((string) $role);
    $storedRole = auth_resolve_role_for_storage($requestedRole);
    $active = !empty($data['active']) ? 1 : 0;

    $username = trim((string) ($data['username'] ?? ''));
    if ($username === '' && $email !== '' && str_contains($email, '@')) {
        $username = strstr($email, '@', true) ?: '';
    }
    if ($username === '') {
        $username = 'user' . random_int(1000, 9999);
    }

    if (isset($columns['username'])) {
        $base = $username;
        $suffix = 1;
        while (auth_user_exists('username', $username)) {
            $username = $base . $suffix;
            $suffix++;
        }
    }

    $displayName = trim((string) ($data['full_name'] ?? $data['contact_person'] ?? $data['org_name'] ?? $username));

    $userInsertData = [];
    if (isset($columns['name'])) {
        $userInsertData['name'] = $displayName;
    }
    if (isset($columns['username'])) {
        $userInsertData['username'] = $username;
    }
    if (isset($columns['email'])) {
        $userInsertData['email'] = $email;
    }
    if (isset($columns[$passwordColumn])) {
        $userInsertData[$passwordColumn] = password_hash((string) ($data['password'] ?? ''), PASSWORD_DEFAULT);
    }
    if (isset($columns['role'])) {
        $userInsertData['role'] = $storedRole;
    }
    if (isset($columns['active'])) {
        $userInsertData['active'] = $active;
    }

    $pdo = db_connect();
    $pdo->beginTransaction();

    try {
        $userId = (int) db_insert('users', $userInsertData);

        auth_insert_profile($userId, $requestedRole, $data);

        if (auth_normalize_role($requestedRole) === 'volunteer') {
            auth_attach_lookup_values($userId, $data['skills'] ?? [], 'skills', 'skills_volunteers');
            auth_attach_lookup_values($userId, $data['preferences'] ?? [], 'volunteer_preferences', 'volunteer_preference_volunteers');
        }

        $pdo->commit();
        return (string) $userId;
    } catch (\Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function auth_create_password_reset_token(int $userId, string $token, string $expiresAt): bool
{
    if (!auth_table_exists('password_reset_tokens')) {
        return false;
    }

    $columns = auth_table_columns('password_reset_tokens');
    $insertData = [];

    if (isset($columns['token'])) {
        $insertData['token'] = $token;
    }
    if (isset($columns['user_id'])) {
        $insertData['user_id'] = $userId;
    }
    if (isset($columns['expires_at'])) {
        $insertData['expires_at'] = $expiresAt;
    }
    if (isset($columns['used'])) {
        $insertData['used'] = 0;
    }

    if (empty($insertData)) {
        return false;
    }

    db_insert('password_reset_tokens', $insertData);
    return true;
}

function auth_find_valid_password_reset_token(string $token): ?array
{
    if (!auth_table_exists('password_reset_tokens')) {
        return null;
    }

    $columns = auth_table_columns('password_reset_tokens');
    if (!isset($columns['token'])) {
        return null;
    }

    $where = ['token = ?'];
    $params = [$token];

    if (isset($columns['used'])) {
        $where[] = '(used = 0 OR used IS NULL)';
    }
    if (isset($columns['expires_at'])) {
        $where[] = 'expires_at > NOW()';
    }

    $sql = 'SELECT * FROM password_reset_tokens WHERE ' . implode(' AND ', $where) . ' LIMIT 1';
    return db_fetch($sql, $params);
}

function auth_mark_password_reset_token_used(string $token): void
{
    if (!auth_table_exists('password_reset_tokens')) {
        return;
    }

    $columns = auth_table_columns('password_reset_tokens');
    $updates = [];

    if (isset($columns['used'])) {
        $updates['used'] = 1;
    }
    if (isset($columns['used_at'])) {
        $updates['used_at'] = date('Y-m-d H:i:s');
    }

    if (empty($updates) || !isset($columns['token'])) {
        return;
    }

    db_update('password_reset_tokens', $updates, ['token' => $token]);
}

function auth_update_user_credentials(int $userId, array $data): bool
{
    $columns = auth_table_columns('users');
    if (empty($columns)) {
        return false;
    }

    $idColumn = auth_users_id_column();
    $passwordColumn = auth_users_password_column();
    $updates = [];

    if (array_key_exists('email', $data) && isset($columns['email'])) {
        $updates['email'] = trim((string) $data['email']);
    }
    if (array_key_exists('username', $data) && isset($columns['username'])) {
        $updates['username'] = trim((string) $data['username']);
    }
    if (!empty($data['password']) && isset($columns[$passwordColumn])) {
        $updates[$passwordColumn] = password_hash((string) $data['password'], PASSWORD_DEFAULT);
    }

    if (empty($updates)) {
        return false;
    }

    db_update('users', $updates, [$idColumn => $userId]);
    return true;
}

function auth_update_profile(int $userId, string $role, array $data): bool
{
    $table = auth_profile_table_for_role($role);
    if ($table === null || !auth_table_exists($table)) {
        return false;
    }

    $columns = auth_table_columns($table);
    $fkColumn = isset($columns['user_id']) ? 'user_id' : (isset($columns['id']) ? 'id' : null);
    if ($fkColumn === null) {
        return false;
    }

    $updates = [];
    $address = trim((string) ($data['address'] ?? auth_compose_address($data)));

    $mapping = [
        'name' => trim((string) ($data['full_name'] ?? '')),
        'full_name' => trim((string) ($data['full_name'] ?? '')),
        'contact' => trim((string) ($data['contact_no'] ?? '')),
        'contact_no' => trim((string) ($data['contact_no'] ?? '')),
        'contact_number' => trim((string) ($data['contact_no'] ?? '')),
        'telephone' => trim((string) ($data['telephone'] ?? '')),
        'address' => $address,
        'district' => trim((string) ($data['district'] ?? '')),
        'gn_division' => trim((string) ($data['gn_division'] ?? '')),
        'organization_name' => trim((string) ($data['org_name'] ?? '')),
        'org_name' => trim((string) ($data['org_name'] ?? '')),
        'registration_no' => trim((string) ($data['registration_no'] ?? '')),
        'registration_number' => trim((string) ($data['registration_no'] ?? '')),
        'reg_no' => trim((string) ($data['registration_no'] ?? '')),
        'years_of_operation' => isset($data['years_of_operation']) ? (int) $data['years_of_operation'] : null,
        'years' => isset($data['years_of_operation']) ? (int) $data['years_of_operation'] : null,
        'contact_person' => trim((string) ($data['contact_person'] ?? '')),
        'contact_person_name' => trim((string) ($data['contact_person'] ?? '')),
        'age' => isset($data['age']) ? (int) $data['age'] : null,
        'gender' => trim((string) ($data['gender'] ?? '')),
    ];

    foreach ($mapping as $column => $value) {
        if (!isset($columns[$column])) {
            continue;
        }

        if ($value === null || $value === '') {
            continue;
        }

        $updates[$column] = $value;
    }

    if (array_key_exists('sms_alert', $data) && isset($columns['sms_alert'])) {
        $updates['sms_alert'] = !empty($data['sms_alert']) ? 1 : 0;
    }

    if (empty($updates)) {
        return false;
    }

    db_update($table, $updates, [$fkColumn => $userId]);
    return true;
}

function auth_profile_display_name(array $user, ?array $profile): string
{
    if ($profile) {
        foreach (['name', 'full_name', 'contact_person', 'contact_person_name', 'organization_name', 'org_name'] as $key) {
            if (!empty($profile[$key])) {
                return (string) $profile[$key];
            }
        }
    }

    foreach (['name', 'username', 'email'] as $key) {
        if (!empty($user[$key])) {
            return (string) $user[$key];
        }
    }

    return 'User';
}

function auth_build_session_user(array $user, ?array $profile = null): array
{
    $idColumn = auth_users_id_column();
    $id = (int) ($user[$idColumn] ?? 0);

    return [
        'id' => $id,
        'user_id' => $id,
        'name' => auth_profile_display_name($user, $profile),
        'email' => (string) ($user['email'] ?? ''),
        'role' => (string) ($user['role'] ?? 'general'),
        'username' => (string) ($user['username'] ?? ''),
        'active' => auth_is_user_active($user) ? 1 : 0,
    ];
}
