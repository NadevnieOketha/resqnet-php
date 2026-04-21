<?php

/**
 * Collection Points Module - Models
 */

function collection_points_district_map(): array
{
    return (array) config('auth_options.gn_divisions', []);
}

function collection_points_district_list(): array
{
    return array_keys(collection_points_district_map());
}

function collection_points_table_exists(string $tableName): bool
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

function collection_points_column_exists(string $tableName, string $columnName): bool
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

function collection_points_index_exists(string $tableName, string $indexName): bool
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

function collection_points_ensure_column(string $tableName, string $columnName, string $definition): void
{
    if (!collection_points_column_exists($tableName, $columnName)) {
        db_query("ALTER TABLE {$tableName} ADD COLUMN {$columnName} {$definition}");
    }
}

function collection_points_ensure_schema(): void
{
    static $ensured = false;
    if ($ensured) {
        return;
    }

    if (!collection_points_table_exists('collection_points')) {
        db_query(
            "CREATE TABLE collection_points (
                collection_point_id INT NOT NULL AUTO_INCREMENT,
                ngo_id INT NOT NULL,
                name VARCHAR(150) NOT NULL,
                address_house_no VARCHAR(50) DEFAULT NULL,
                address_street VARCHAR(120) NOT NULL,
                address_city VARCHAR(120) NOT NULL,
                district VARCHAR(100) NOT NULL,
                gn_division VARCHAR(150) NOT NULL,
                location_landmark VARCHAR(150) DEFAULT NULL,
                full_address VARCHAR(255) NOT NULL,
                contact_person VARCHAR(100) DEFAULT NULL,
                contact_number VARCHAR(20) DEFAULT NULL,
                active TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (collection_point_id)
            ) ENGINE=InnoDB"
        );
    }

    collection_points_ensure_column('collection_points', 'address_house_no', 'VARCHAR(50) DEFAULT NULL AFTER name');
    collection_points_ensure_column('collection_points', 'address_street', 'VARCHAR(120) DEFAULT NULL AFTER address_house_no');
    collection_points_ensure_column('collection_points', 'address_city', 'VARCHAR(120) DEFAULT NULL AFTER address_street');
    collection_points_ensure_column('collection_points', 'district', 'VARCHAR(100) DEFAULT NULL AFTER address_city');
    collection_points_ensure_column('collection_points', 'gn_division', 'VARCHAR(150) DEFAULT NULL AFTER district');
    collection_points_ensure_column('collection_points', 'active', 'TINYINT(1) NOT NULL DEFAULT 1 AFTER contact_number');
    collection_points_ensure_column('collection_points', 'created_at', 'TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP');
    collection_points_ensure_column('collection_points', 'updated_at', 'TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

    if (!collection_points_index_exists('collection_points', 'idx_collection_points_ngo')) {
        db_query('ALTER TABLE collection_points ADD INDEX idx_collection_points_ngo (ngo_id)');
    }

    $ensured = true;
}

function collection_points_compose_full_address(array $payload): string
{
    $parts = [];
    foreach (['address_house_no', 'address_street', 'address_city', 'district', 'gn_division'] as $key) {
        $value = trim((string) ($payload[$key] ?? ''));
        if ($value !== '') {
            $parts[] = $value;
        }
    }

    return implode(', ', $parts);
}

function collection_points_resolve_location_inputs(array $input): array
{
    $district = trim((string) ($input['district'] ?? ''));
    $districtOther = trim((string) ($input['district_other'] ?? ''));
    if ($district === '__other__') {
        $district = $districtOther;
    }

    $gnDivision = trim((string) ($input['gn_division'] ?? ''));
    $gnOther = trim((string) ($input['gn_division_other'] ?? ''));
    if ($gnDivision === '__other__') {
        $gnDivision = $gnOther;
    }

    return [
        'district' => $district,
        'gn_division' => $gnDivision,
    ];
}

function collection_points_validate_payload(array $payload): array
{
    $errors = [];

    if (trim((string) ($payload['name'] ?? '')) === '') {
        $errors[] = 'Collection point name is required.';
    }

    if (trim((string) ($payload['address_street'] ?? '')) === '') {
        $errors[] = 'Street is required.';
    }

    if (trim((string) ($payload['address_city'] ?? '')) === '') {
        $errors[] = 'City is required.';
    }

    if (trim((string) ($payload['district'] ?? '')) === '') {
        $errors[] = 'District is required.';
    }

    if (trim((string) ($payload['gn_division'] ?? '')) === '') {
        $errors[] = 'GN division is required.';
    }

    if (trim((string) ($payload['full_address'] ?? '')) === '') {
        $errors[] = 'A valid address is required.';
    }

    $contactNumber = trim((string) ($payload['contact_number'] ?? ''));
    if ($contactNumber !== '' && !preg_match('/^[0-9+\-\s()]{7,20}$/', $contactNumber)) {
        $errors[] = 'Contact number format is invalid.';
    }

    return $errors;
}

function collection_points_normalize_active_value($value): int
{
    return (string) $value === '1' ? 1 : 0;
}

function collection_points_list_for_ngo(int $ngoUserId): array
{
    collection_points_ensure_schema();

    return db_fetch_all(
        'SELECT collection_point_id,
                ngo_id,
                name,
                address_house_no,
                address_street,
                address_city,
                district,
                gn_division,
                location_landmark,
                full_address,
                contact_person,
                contact_number,
                active,
                created_at,
                updated_at
         FROM collection_points
         WHERE ngo_id = ?
         ORDER BY collection_point_id DESC',
        [$ngoUserId]
    );
}

function collection_points_find_for_ngo(int $collectionPointId, int $ngoUserId): ?array
{
    collection_points_ensure_schema();

    return db_fetch(
        'SELECT collection_point_id,
                ngo_id,
                name,
                address_house_no,
                address_street,
                address_city,
                district,
                gn_division,
                location_landmark,
                full_address,
                contact_person,
                contact_number,
                active,
                created_at,
                updated_at
         FROM collection_points
         WHERE collection_point_id = ?
           AND ngo_id = ?
         LIMIT 1',
        [$collectionPointId, $ngoUserId]
    );
}

function collection_points_create(int $ngoUserId, array $payload): int
{
    collection_points_ensure_schema();

    db_query(
        'INSERT INTO collection_points
         (ngo_id, name, address_house_no, address_street, address_city, district, gn_division, location_landmark, full_address, contact_person, contact_number, active)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
        [
            $ngoUserId,
            (string) $payload['name'],
            $payload['address_house_no'] !== '' ? (string) $payload['address_house_no'] : null,
            (string) $payload['address_street'],
            (string) $payload['address_city'],
            (string) $payload['district'],
            (string) $payload['gn_division'],
            $payload['location_landmark'] !== '' ? (string) $payload['location_landmark'] : null,
            (string) $payload['full_address'],
            $payload['contact_person'] !== '' ? (string) $payload['contact_person'] : null,
            $payload['contact_number'] !== '' ? (string) $payload['contact_number'] : null,
            collection_points_normalize_active_value($payload['active'] ?? 1),
        ]
    );

    return (int) db_connect()->lastInsertId();
}

function collection_points_update_for_ngo(int $collectionPointId, int $ngoUserId, array $payload): int
{
    collection_points_ensure_schema();

    return db_query(
        'UPDATE collection_points
         SET name = ?,
             address_house_no = ?,
             address_street = ?,
             address_city = ?,
             district = ?,
             gn_division = ?,
             location_landmark = ?,
             full_address = ?,
             contact_person = ?,
             contact_number = ?,
             active = ?
         WHERE collection_point_id = ?
           AND ngo_id = ?
         LIMIT 1',
        [
            (string) $payload['name'],
            $payload['address_house_no'] !== '' ? (string) $payload['address_house_no'] : null,
            (string) $payload['address_street'],
            (string) $payload['address_city'],
            (string) $payload['district'],
            (string) $payload['gn_division'],
            $payload['location_landmark'] !== '' ? (string) $payload['location_landmark'] : null,
            (string) $payload['full_address'],
            $payload['contact_person'] !== '' ? (string) $payload['contact_person'] : null,
            $payload['contact_number'] !== '' ? (string) $payload['contact_number'] : null,
            collection_points_normalize_active_value($payload['active'] ?? 1),
            $collectionPointId,
            $ngoUserId,
        ]
    )->rowCount();
}

function collection_points_delete_for_ngo(int $collectionPointId, int $ngoUserId): int
{
    collection_points_ensure_schema();

    return db_query(
        'DELETE FROM collection_points
         WHERE collection_point_id = ?
           AND ngo_id = ?
         LIMIT 1',
        [$collectionPointId, $ngoUserId]
    )->rowCount();
}
