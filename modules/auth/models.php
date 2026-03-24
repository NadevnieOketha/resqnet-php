<?php

/**
 * Auth Module — Models
 */

function auth_find_by_email(string $email): ?array
{
    return db_fetch('SELECT * FROM users WHERE email = ?', [$email]);
}

function auth_create_user(array $data): string
{
    $allowedRoles = ['general_public', 'grama_niladhari', 'ngo', 'dmc_admin'];
    $role = $data['role'] ?? 'general_public';
    if (!in_array($role, $allowedRoles, true)) {
        $role = 'general_public';
    }

    return db_insert('users', [
        'name'     => $data['name'],
        'email'    => $data['email'],
        'password' => password_hash($data['password'], PASSWORD_DEFAULT),
        'role'     => $role,
    ]);
}
