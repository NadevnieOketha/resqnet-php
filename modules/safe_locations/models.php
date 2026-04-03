<?php

/**
 * Safe Locations Module - Models
 */

function safe_locations_district_map(): array
{
    return (array) config('auth_options.gn_divisions', []);
}

function safe_locations_district_list(): array
{
    return array_keys(safe_locations_district_map());
}

function safe_locations_occupancy_categories(): array
{
    return [
        'toddlers',
        'children',
        'adults',
        'elderly',
        'pregnant_women',
    ];
}

function safe_locations_table_exists(string $tableName): bool
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

function safe_locations_column_exists(string $tableName, string $columnName): bool
{
    $row = db_fetch(
        'SELECT COUNT(*) AS cnt
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = ?
           AND COLUMN_NAME = ?',
        [$tableName, $columnName]
    );

    return ((int) ($row['cnt'] ?? 0)) > 0;
}

function safe_locations_index_exists(string $tableName, string $indexName): bool
{
    $row = db_fetch(
        'SELECT COUNT(*) AS cnt
         FROM information_schema.STATISTICS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = ?
           AND INDEX_NAME = ?',
        [$tableName, $indexName]
    );

    return ((int) ($row['cnt'] ?? 0)) > 0;
}

function safe_locations_ensure_column(string $tableName, string $columnName, string $definition): void
{
    if (!safe_locations_column_exists($tableName, $columnName)) {
        db_query("ALTER TABLE {$tableName} ADD COLUMN {$columnName} {$definition}");
    }
}

function safe_locations_ensure_schema(): void
{
    static $ensured = false;
    if ($ensured) {
        return;
    }

    if (!safe_locations_table_exists('safe_locations')) {
        db_query(
            "CREATE TABLE safe_locations (
                location_id INT NOT NULL AUTO_INCREMENT,
                location_name VARCHAR(255) NOT NULL,
                address_house_no VARCHAR(50) DEFAULT NULL,
                address_street VARCHAR(120) DEFAULT NULL,
                address_city VARCHAR(120) DEFAULT NULL,
                district VARCHAR(100) DEFAULT NULL,
                gn_division VARCHAR(150) DEFAULT NULL,
                latitude DECIMAL(10,8) NOT NULL,
                longitude DECIMAL(11,8) NOT NULL,
                max_capacity INT NOT NULL DEFAULT 0,
                assigned_gn_user_id INT DEFAULT NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (location_id)
            ) ENGINE=InnoDB"
        );
    } else {
        safe_locations_ensure_column('safe_locations', 'address_house_no', 'VARCHAR(50) DEFAULT NULL');
        safe_locations_ensure_column('safe_locations', 'address_street', 'VARCHAR(120) DEFAULT NULL');
        safe_locations_ensure_column('safe_locations', 'address_city', 'VARCHAR(120) DEFAULT NULL');
        safe_locations_ensure_column('safe_locations', 'district', 'VARCHAR(100) DEFAULT NULL');
        safe_locations_ensure_column('safe_locations', 'gn_division', 'VARCHAR(150) DEFAULT NULL');
        safe_locations_ensure_column('safe_locations', 'max_capacity', 'INT NOT NULL DEFAULT 0');
        safe_locations_ensure_column('safe_locations', 'assigned_gn_user_id', 'INT DEFAULT NULL');
        safe_locations_ensure_column('safe_locations', 'updated_at', 'TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    }

    if (!safe_locations_index_exists('safe_locations', 'idx_safe_locations_area')) {
        db_query('ALTER TABLE safe_locations ADD INDEX idx_safe_locations_area (district, gn_division)');
    }

    if (!safe_locations_index_exists('safe_locations', 'idx_safe_locations_gn')) {
        db_query('ALTER TABLE safe_locations ADD INDEX idx_safe_locations_gn (assigned_gn_user_id)');
    }

    db_query(
        "CREATE TABLE IF NOT EXISTS safe_location_occupancy (
            location_id INT NOT NULL,
            toddlers INT NOT NULL DEFAULT 0,
            children INT NOT NULL DEFAULT 0,
            adults INT NOT NULL DEFAULT 0,
            elderly INT NOT NULL DEFAULT 0,
            pregnant_women INT NOT NULL DEFAULT 0,
            updated_by_user_id INT DEFAULT NULL,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (location_id)
        ) ENGINE=InnoDB"
    );

    $ensured = true;
}

function safe_locations_list_gn_officers(): array
{
    return db_fetch_all(
        "SELECT g.user_id, g.name, g.gn_division, u.email
         FROM grama_niladhari g
         INNER JOIN users u ON u.user_id = g.user_id
         WHERE u.role = 'grama_niladhari'
           AND u.active = 1
         ORDER BY g.name ASC"
    );
}

function safe_locations_find_gn_officer(int $gnUserId): ?array
{
    if ($gnUserId <= 0) {
        return null;
    }

    return db_fetch(
        "SELECT g.user_id, g.name, g.gn_division
         FROM grama_niladhari g
         INNER JOIN users u ON u.user_id = g.user_id
         WHERE g.user_id = ?
           AND u.role = 'grama_niladhari'
           AND u.active = 1
         LIMIT 1",
        [$gnUserId]
    );
}

function safe_locations_normalize_occupancy(array $counts): array
{
    $normalized = [];
    foreach (safe_locations_occupancy_categories() as $category) {
        $value = (int) ($counts[$category] ?? 0);
        $normalized[$category] = max(0, $value);
    }

    return $normalized;
}

function safe_locations_total_occupancy(array $counts): int
{
    $total = 0;
    foreach (safe_locations_occupancy_categories() as $category) {
        $total += (int) ($counts[$category] ?? 0);
    }

    return $total;
}

function safe_locations_create(array $payload): int
{
    safe_locations_ensure_schema();

    db_query(
        'INSERT INTO safe_locations
         (location_name, address_house_no, address_street, address_city, district, gn_division, latitude, longitude, max_capacity, assigned_gn_user_id)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
        [
            (string) $payload['location_name'],
            $payload['address_house_no'] !== '' ? (string) $payload['address_house_no'] : null,
            $payload['address_street'] !== '' ? (string) $payload['address_street'] : null,
            $payload['address_city'] !== '' ? (string) $payload['address_city'] : null,
            $payload['district'] !== '' ? (string) $payload['district'] : null,
            $payload['gn_division'] !== '' ? (string) $payload['gn_division'] : null,
            (float) $payload['latitude'],
            (float) $payload['longitude'],
            (int) $payload['max_capacity'],
            (int) $payload['assigned_gn_user_id'] > 0 ? (int) $payload['assigned_gn_user_id'] : null,
        ]
    );

    $locationId = (int) db_connect()->lastInsertId();

    db_query(
        'INSERT INTO safe_location_occupancy (location_id, toddlers, children, adults, elderly, pregnant_women, updated_by_user_id)
         VALUES (?, 0, 0, 0, 0, 0, ?)',
        [$locationId, (int) ($payload['updated_by_user_id'] ?? 0) > 0 ? (int) $payload['updated_by_user_id'] : null]
    );

    return $locationId;
}

function safe_locations_update(int $locationId, array $payload): int
{
    safe_locations_ensure_schema();

    return db_query(
        'UPDATE safe_locations
         SET location_name = ?,
             address_house_no = ?,
             address_street = ?,
             address_city = ?,
             district = ?,
             gn_division = ?,
             latitude = ?,
             longitude = ?,
             max_capacity = ?,
             assigned_gn_user_id = ?
         WHERE location_id = ?
         LIMIT 1',
        [
            (string) $payload['location_name'],
            $payload['address_house_no'] !== '' ? (string) $payload['address_house_no'] : null,
            $payload['address_street'] !== '' ? (string) $payload['address_street'] : null,
            $payload['address_city'] !== '' ? (string) $payload['address_city'] : null,
            $payload['district'] !== '' ? (string) $payload['district'] : null,
            $payload['gn_division'] !== '' ? (string) $payload['gn_division'] : null,
            (float) $payload['latitude'],
            (float) $payload['longitude'],
            (int) $payload['max_capacity'],
            (int) $payload['assigned_gn_user_id'] > 0 ? (int) $payload['assigned_gn_user_id'] : null,
            $locationId,
        ]
    )->rowCount();
}

function safe_locations_delete(int $locationId): int
{
    safe_locations_ensure_schema();

    db_query('DELETE FROM safe_location_occupancy WHERE location_id = ?', [$locationId]);

    return db_query(
        'DELETE FROM safe_locations WHERE location_id = ? LIMIT 1',
        [$locationId]
    )->rowCount();
}

function safe_locations_is_assigned_to_gn(int $locationId, int $gnUserId): bool
{
    safe_locations_ensure_schema();

    $row = db_fetch(
        'SELECT location_id FROM safe_locations WHERE location_id = ? AND assigned_gn_user_id = ? LIMIT 1',
        [$locationId, $gnUserId]
    );

    return $row !== null;
}

function safe_locations_find_by_id(int $locationId): ?array
{
    safe_locations_ensure_schema();

    return db_fetch(
        "SELECT sl.location_id,
                sl.location_name,
                sl.address_house_no,
                sl.address_street,
                sl.address_city,
                sl.district,
                sl.gn_division,
                sl.latitude,
                sl.longitude,
                sl.max_capacity,
                sl.assigned_gn_user_id,
                COALESCE(so.toddlers, 0) AS toddlers,
                COALESCE(so.children, 0) AS children,
                COALESCE(so.adults, 0) AS adults,
                COALESCE(so.elderly, 0) AS elderly,
                COALESCE(so.pregnant_women, 0) AS pregnant_women,
                (
                    COALESCE(so.toddlers, 0)
                    + COALESCE(so.children, 0)
                    + COALESCE(so.adults, 0)
                    + COALESCE(so.elderly, 0)
                    + COALESCE(so.pregnant_women, 0)
                ) AS current_occupancy
         FROM safe_locations sl
         LEFT JOIN safe_location_occupancy so ON so.location_id = sl.location_id
         WHERE sl.location_id = ?
         LIMIT 1",
        [$locationId]
    );
}

function safe_locations_upsert_occupancy(int $locationId, array $counts, int $updatedByUserId): array
{
    safe_locations_ensure_schema();

    $location = safe_locations_find_by_id($locationId);
    if (!$location) {
        return ['ok' => false, 'message' => 'Safe location not found.'];
    }

    $normalized = safe_locations_normalize_occupancy($counts);
    $total = safe_locations_total_occupancy($normalized);

    if ($total > (int) ($location['max_capacity'] ?? 0)) {
        return ['ok' => false, 'message' => 'Current occupancy cannot exceed maximum capacity.'];
    }

    $existing = db_fetch('SELECT location_id FROM safe_location_occupancy WHERE location_id = ? LIMIT 1', [$locationId]);

    if ($existing) {
        db_query(
            'UPDATE safe_location_occupancy
             SET toddlers = ?, children = ?, adults = ?, elderly = ?, pregnant_women = ?, updated_by_user_id = ?
             WHERE location_id = ?
             LIMIT 1',
            [
                $normalized['toddlers'],
                $normalized['children'],
                $normalized['adults'],
                $normalized['elderly'],
                $normalized['pregnant_women'],
                $updatedByUserId > 0 ? $updatedByUserId : null,
                $locationId,
            ]
        );
    } else {
        db_query(
            'INSERT INTO safe_location_occupancy
             (location_id, toddlers, children, adults, elderly, pregnant_women, updated_by_user_id)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            [
                $locationId,
                $normalized['toddlers'],
                $normalized['children'],
                $normalized['adults'],
                $normalized['elderly'],
                $normalized['pregnant_women'],
                $updatedByUserId > 0 ? $updatedByUserId : null,
            ]
        );
    }

    return [
        'ok' => true,
        'message' => 'Occupancy updated successfully.',
        'current_occupancy' => $total,
    ];
}

function safe_locations_address_line(array $row): string
{
    $parts = [];

    foreach (['address_house_no', 'address_street', 'address_city', 'district', 'gn_division'] as $key) {
        $value = trim((string) ($row[$key] ?? ''));
        if ($value !== '') {
            $parts[] = $value;
        }
    }

    return implode(', ', $parts);
}

function safe_locations_base_select_sql(): string
{
    return "SELECT sl.location_id,
                   sl.location_name,
                   sl.address_house_no,
                   sl.address_street,
                   sl.address_city,
                   sl.district,
                   sl.gn_division,
                   sl.latitude,
                   sl.longitude,
                   sl.max_capacity,
                   sl.assigned_gn_user_id,
                   sl.created_at,
                   sl.updated_at,
                   COALESCE(so.toddlers, 0) AS toddlers,
                   COALESCE(so.children, 0) AS children,
                   COALESCE(so.adults, 0) AS adults,
                   COALESCE(so.elderly, 0) AS elderly,
                   COALESCE(so.pregnant_women, 0) AS pregnant_women,
                   (
                       COALESCE(so.toddlers, 0)
                       + COALESCE(so.children, 0)
                       + COALESCE(so.adults, 0)
                       + COALESCE(so.elderly, 0)
                       + COALESCE(so.pregnant_women, 0)
                   ) AS current_occupancy,
                   (
                       sl.max_capacity - (
                           COALESCE(so.toddlers, 0)
                           + COALESCE(so.children, 0)
                           + COALESCE(so.adults, 0)
                           + COALESCE(so.elderly, 0)
                           + COALESCE(so.pregnant_women, 0)
                       )
                   ) AS available_space,
                   g.name AS assigned_gn_name,
                   g.contact_number AS assigned_gn_contact
            FROM safe_locations sl
            LEFT JOIN safe_location_occupancy so ON so.location_id = sl.location_id
            LEFT JOIN grama_niladhari g ON g.user_id = sl.assigned_gn_user_id";
}

function safe_locations_list_for_dmc(): array
{
    safe_locations_ensure_schema();

    return db_fetch_all(
        safe_locations_base_select_sql() . ' ORDER BY sl.created_at DESC, sl.location_id DESC'
    );
}

function safe_locations_list_for_gn(int $gnUserId): array
{
    safe_locations_ensure_schema();

    return db_fetch_all(
        safe_locations_base_select_sql()
            . ' WHERE sl.assigned_gn_user_id = ? ORDER BY sl.created_at DESC, sl.location_id DESC',
        [$gnUserId]
    );
}

function safe_locations_list_for_public(?string $district = null, ?string $gnDivision = null, bool $onlyAvailable = false): array
{
    safe_locations_ensure_schema();

    $params = [];
    $where = [];

    $district = trim((string) $district);
    $gnDivision = trim((string) $gnDivision);

    if ($district !== '') {
        $where[] = 'sl.district = ?';
        $params[] = $district;
    }

    if ($gnDivision !== '') {
        $where[] = 'sl.gn_division = ?';
        $params[] = $gnDivision;
    }

    if ($onlyAvailable) {
        $where[] = 'sl.max_capacity > (
            COALESCE(so.toddlers, 0)
            + COALESCE(so.children, 0)
            + COALESCE(so.adults, 0)
            + COALESCE(so.elderly, 0)
            + COALESCE(so.pregnant_women, 0)
        )';
    }

    $sql = safe_locations_base_select_sql();
    if (!empty($where)) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= ' ORDER BY sl.district ASC, sl.gn_division ASC, sl.location_name ASC';

    return db_fetch_all($sql, $params);
}
