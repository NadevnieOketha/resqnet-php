<?php

/**
 * Make a Donation Module - Models
 */

function donations_table_exists(string $tableName): bool
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

function donations_column_exists(string $tableName, string $columnName): bool
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

function donations_index_exists(string $tableName, string $indexName): bool
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

function donations_ensure_column(string $tableName, string $columnName, string $definition): void
{
    if (!donations_column_exists($tableName, $columnName)) {
        db_query("ALTER TABLE {$tableName} ADD COLUMN {$columnName} {$definition}");
    }
}

function donations_ensure_schema(): void
{
    static $ensured = false;
    if ($ensured) {
        return;
    }

    if (!donations_table_exists('donations')) {
        db_query(
            "CREATE TABLE donations (
                donation_id INT NOT NULL AUTO_INCREMENT,
                user_id INT DEFAULT NULL,
                submitted_by_user_id INT DEFAULT NULL,
                submitted_by_role ENUM('general','volunteer','guest') NOT NULL DEFAULT 'guest',
                public_access_token VARCHAR(64) DEFAULT NULL,
                collection_point_id INT NOT NULL,
                name VARCHAR(150) NOT NULL,
                contact_number VARCHAR(20) NOT NULL,
                email VARCHAR(150) NOT NULL,
                address VARCHAR(255) NOT NULL,
                collection_date DATE NOT NULL,
                time_slot ENUM('9am–12pm','12pm–4pm','6pm–9pm') NOT NULL,
                special_notes TEXT,
                confirmation TINYINT(1) NOT NULL DEFAULT 1,
                status ENUM('Pending','Received','Cancelled','Delivered') DEFAULT 'Pending',
                submitted_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                received_at TIMESTAMP NULL DEFAULT NULL,
                cancelled_at TIMESTAMP NULL DEFAULT NULL,
                delivered_at TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (donation_id)
            ) ENGINE=InnoDB"
        );
    }

    donations_ensure_column('donations', 'submitted_by_user_id', 'INT DEFAULT NULL AFTER user_id');
    donations_ensure_column(
        'donations',
        'submitted_by_role',
        "ENUM('general','volunteer','guest') NOT NULL DEFAULT 'guest' AFTER submitted_by_user_id"
    );
    donations_ensure_column('donations', 'public_access_token', 'VARCHAR(64) DEFAULT NULL AFTER submitted_by_role');

    if (!donations_index_exists('donations', 'idx_donations_submitter')) {
        db_query('ALTER TABLE donations ADD INDEX idx_donations_submitter (submitted_by_user_id, submitted_by_role)');
    }

    if (!donations_index_exists('donations', 'idx_donations_public_token')) {
        db_query('ALTER TABLE donations ADD UNIQUE INDEX idx_donations_public_token (public_access_token)');
    }

    if (!donations_table_exists('donation_items_catalog')) {
        db_query(
            "CREATE TABLE donation_items_catalog (
                item_id INT NOT NULL AUTO_INCREMENT,
                item_name VARCHAR(100) NOT NULL,
                category ENUM('Medicine','Food','Shelter') NOT NULL,
                PRIMARY KEY (item_id),
                UNIQUE KEY uq_item_name (item_name)
            ) ENGINE=InnoDB"
        );
    }

    if (!donations_table_exists('donation_items')) {
        db_query(
            "CREATE TABLE donation_items (
                donation_item_id INT NOT NULL AUTO_INCREMENT,
                donation_id INT NOT NULL,
                item_id INT NOT NULL,
                quantity INT NOT NULL,
                PRIMARY KEY (donation_item_id)
            ) ENGINE=InnoDB"
        );
    }

    donations_seed_catalog_items();

    $ensured = true;
}

function donations_time_slots(): array
{
    return ['9am–12pm', '12pm–4pm', '6pm–9pm'];
}

function donations_catalog_seed_data(): array
{
    return [
        [1, 'Amoxicillin', 'Medicine'],
        [2, 'Antiseptic solution', 'Medicine'],
        [3, 'Bandages', 'Medicine'],
        [4, 'Paracetamol', 'Medicine'],
        [5, 'Aspirin', 'Medicine'],
        [6, 'Antifungal powder', 'Medicine'],
        [7, 'Mosquito repellent', 'Medicine'],
        [8, 'Diabetes tablets', 'Medicine'],
        [9, 'Sanitary pads', 'Medicine'],
        [10, 'Cotton wool', 'Medicine'],
        [11, 'Alcohol swabs', 'Medicine'],
        [12, 'Rice', 'Food'],
        [13, 'Dhal', 'Food'],
        [14, 'Canned fish', 'Food'],
        [15, 'Infant formula', 'Food'],
        [16, 'Baby food jars or sachets', 'Food'],
        [17, 'Drinking water', 'Food'],
        [18, 'Instant noodles', 'Food'],
        [19, 'Biscuits', 'Food'],
        [20, 'Canned meat', 'Food'],
        [21, 'Milk powder', 'Food'],
        [22, 'Cooking oil', 'Food'],
        [23, 'Sleeping mats', 'Shelter'],
        [24, 'Blankets', 'Shelter'],
        [25, 'Towels', 'Shelter'],
        [26, 'Toothbrush', 'Shelter'],
        [27, 'Soap', 'Shelter'],
        [28, 'Basic clothing', 'Shelter'],
        [29, 'Candles', 'Shelter'],
        [30, 'Plates, cups', 'Shelter'],
        [31, 'Baby diapers', 'Shelter'],
        [32, 'Baby clothes', 'Shelter'],
        [33, 'Disinfectant', 'Shelter'],
    ];
}

function donations_seed_catalog_items(): void
{
    foreach (donations_catalog_seed_data() as $row) {
        [, $itemName, $category] = $row;
        db_query(
            'INSERT INTO donation_items_catalog (item_name, category)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE category = VALUES(category)',
            [(string) $itemName, (string) $category]
        );
    }
}

function donations_logged_donor_defaults(int $userId, string $role): array
{
    donations_ensure_schema();

    $profile = auth_get_profile($userId, $role) ?? [];

    $addressParts = [];
    foreach (['house_no', 'street', 'city', 'district', 'gn_division'] as $key) {
        $value = trim((string) ($profile[$key] ?? ''));
        if ($value !== '') {
            $addressParts[] = $value;
        }
    }

    return [
        'name' => trim((string) ($profile['name'] ?? '')),
        'contact_number' => trim((string) ($profile['contact_number'] ?? '')),
        'email' => trim((string) ($profile['email'] ?? '')),
        'address' => implode(', ', $addressParts),
        'district' => trim((string) ($profile['district'] ?? '')),
        'gn_division' => trim((string) ($profile['gn_division'] ?? '')),
    ];
}

function donations_list_collection_points(?string $district = null, ?string $gnDivision = null): array
{
    donations_ensure_schema();
    if (function_exists('collection_points_ensure_schema')) {
        collection_points_ensure_schema();
    }

    $sql = 'SELECT collection_point_id,
                   ngo_id,
                   name,
                   full_address,
                   district,
                   gn_division,
                   location_landmark,
                   contact_person,
                   contact_number,
                   active
            FROM collection_points';

    $where = ['COALESCE(active, 1) = 1'];
    $params = [];

    $district = trim((string) $district);
    $gnDivision = trim((string) $gnDivision);

    if ($district !== '') {
        $where[] = 'district = ?';
        $params[] = $district;
    }

    if ($gnDivision !== '') {
        $where[] = 'gn_division = ?';
        $params[] = $gnDivision;
    }

    if (!empty($where)) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $sql .= ' ORDER BY district ASC, gn_division ASC, name ASC';

    return db_fetch_all($sql, $params);
}

function donations_find_collection_point_by_id(int $collectionPointId): ?array
{
    donations_ensure_schema();
    if (function_exists('collection_points_ensure_schema')) {
        collection_points_ensure_schema();
    }

    return db_fetch(
        'SELECT collection_point_id,
                ngo_id,
                name,
                full_address,
                district,
                gn_division,
                location_landmark,
                contact_person,
                contact_number,
                active
         FROM collection_points
         WHERE collection_point_id = ?
           AND COALESCE(active, 1) = 1
         LIMIT 1',
        [$collectionPointId]
    );
}

function donations_list_catalog_items(): array
{
    donations_ensure_schema();

    return db_fetch_all(
        "SELECT item_id, item_name, category
         FROM donation_items_catalog
         ORDER BY FIELD(category, 'Medicine', 'Food', 'Shelter'), item_name ASC"
    );
}

function donations_catalog_items_indexed(): array
{
    $items = donations_list_catalog_items();
    $indexed = [];

    foreach ($items as $item) {
        $indexed[(int) ($item['item_id'] ?? 0)] = $item;
    }

    return $indexed;
}

function donations_catalog_grouped(): array
{
    $grouped = [
        'Medicine' => [],
        'Food' => [],
        'Shelter' => [],
    ];

    foreach (donations_list_catalog_items() as $item) {
        $category = (string) ($item['category'] ?? 'Food');
        if (!array_key_exists($category, $grouped)) {
            $grouped[$category] = [];
        }
        $grouped[$category][] = $item;
    }

    return $grouped;
}

function donations_create(array $payload, array $itemQuantities): array
{
    donations_ensure_schema();

    $pdo = db_connect();
    $pdo->beginTransaction();

    try {
        db_query(
            'INSERT INTO donations
             (user_id, submitted_by_user_id, submitted_by_role, public_access_token, collection_point_id, name, contact_number, email, address, collection_date, time_slot, special_notes, confirmation, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $payload['user_id'] !== null ? (int) $payload['user_id'] : null,
                $payload['submitted_by_user_id'] !== null ? (int) $payload['submitted_by_user_id'] : null,
                (string) $payload['submitted_by_role'],
                $payload['public_access_token'] !== null ? (string) $payload['public_access_token'] : null,
                (int) $payload['collection_point_id'],
                (string) $payload['name'],
                (string) $payload['contact_number'],
                (string) $payload['email'],
                (string) $payload['address'],
                (string) $payload['collection_date'],
                (string) $payload['time_slot'],
                (string) ($payload['special_notes'] ?? ''),
                1,
                'Pending',
            ]
        );

        $donationId = (int) $pdo->lastInsertId();

        foreach ($itemQuantities as $itemId => $quantity) {
            db_query(
                'INSERT INTO donation_items (donation_id, item_id, quantity) VALUES (?, ?, ?)',
                [$donationId, (int) $itemId, (int) $quantity]
            );
        }

        $pdo->commit();

        return [
            'donation_id' => $donationId,
            'public_access_token' => $payload['public_access_token'],
        ];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function donations_items_for_donation(int $donationId): array
{
    donations_ensure_schema();

    return db_fetch_all(
        "SELECT di.item_id, di.quantity, c.item_name, c.category
         FROM donation_items di
         INNER JOIN donation_items_catalog c ON c.item_id = di.item_id
         WHERE di.donation_id = ?
         ORDER BY FIELD(c.category, 'Medicine', 'Food', 'Shelter'), c.item_name ASC",
        [$donationId]
    );
}

function donations_display_status_for_donor(string $status): string
{
    return $status === 'Received' ? 'Delivered' : $status;
}

function donations_list_for_donor(int $userId, string $role): array
{
    donations_ensure_schema();

    $sql = 'SELECT d.*, cp.name AS collection_point_name
            FROM donations d
            INNER JOIN collection_points cp ON cp.collection_point_id = d.collection_point_id
            WHERE d.submitted_by_user_id = ?
              AND d.submitted_by_role = ?';
    $params = [$userId, $role];

    if ($role === 'general') {
        $sql = 'SELECT d.*, cp.name AS collection_point_name
                FROM donations d
                INNER JOIN collection_points cp ON cp.collection_point_id = d.collection_point_id
                WHERE (d.submitted_by_user_id = ? AND d.submitted_by_role = ?)
                   OR d.user_id = ?';
        $params = [$userId, $role, $userId];
    }

    $sql .= ' ORDER BY d.submitted_at DESC, d.donation_id DESC';

    $rows = db_fetch_all($sql, $params);
    foreach ($rows as &$row) {
        $row['items'] = donations_items_for_donation((int) ($row['donation_id'] ?? 0));
        $row['display_status'] = donations_display_status_for_donor((string) ($row['status'] ?? 'Pending'));
    }
    unset($row);

    return $rows;
}

function donations_cancel_for_donor(int $donationId, int $userId, string $role): int
{
    donations_ensure_schema();

    if ($role === 'general') {
        return db_query(
            "UPDATE donations
             SET status = 'Cancelled', cancelled_at = NOW()
             WHERE donation_id = ?
               AND status = 'Pending'
               AND ((submitted_by_user_id = ? AND submitted_by_role = ?) OR user_id = ?)",
            [$donationId, $userId, $role, $userId]
        )->rowCount();
    }

    return db_query(
        "UPDATE donations
         SET status = 'Cancelled', cancelled_at = NOW()
         WHERE donation_id = ?
           AND status = 'Pending'
           AND submitted_by_user_id = ?
           AND submitted_by_role = ?",
        [$donationId, $userId, $role]
    )->rowCount();
}

function donations_find_by_public_token(string $token): ?array
{
    donations_ensure_schema();

    $donation = db_fetch(
        'SELECT d.*, cp.name AS collection_point_name
         FROM donations d
         INNER JOIN collection_points cp ON cp.collection_point_id = d.collection_point_id
         WHERE d.public_access_token = ?
         LIMIT 1',
        [$token]
    );

    if (!$donation) {
        return null;
    }

    $donation['items'] = donations_items_for_donation((int) ($donation['donation_id'] ?? 0));
    $donation['display_status'] = donations_display_status_for_donor((string) ($donation['status'] ?? 'Pending'));

    return $donation;
}

function donations_cancel_by_public_token(string $token): int
{
    donations_ensure_schema();

    return db_query(
        "UPDATE donations
         SET status = 'Cancelled', cancelled_at = NOW()
         WHERE public_access_token = ?
           AND status = 'Pending'",
        [$token]
    )->rowCount();
}

function donations_ngo_counts(int $ngoUserId): array
{
    donations_ensure_schema();

    $rows = db_fetch_all(
        'SELECT d.status, COUNT(*) AS cnt
         FROM donations d
         INNER JOIN collection_points cp ON cp.collection_point_id = d.collection_point_id
         WHERE cp.ngo_id = ?
         GROUP BY d.status',
        [$ngoUserId]
    );

    $counts = [
        'pending' => 0,
        'received' => 0,
        'cancelled' => 0,
    ];

    foreach ($rows as $row) {
        $status = strtolower((string) ($row['status'] ?? ''));
        if (array_key_exists($status, $counts)) {
            $counts[$status] = (int) ($row['cnt'] ?? 0);
        }
    }

    return $counts;
}

function donations_list_for_ngo(int $ngoUserId, string $status): array
{
    donations_ensure_schema();

    $allowed = ['Pending', 'Received', 'Cancelled'];
    if (!in_array($status, $allowed, true)) {
        $status = 'Pending';
    }

    $rows = db_fetch_all(
        'SELECT d.*, cp.name AS collection_point_name, cp.full_address AS collection_point_address
         FROM donations d
         INNER JOIN collection_points cp ON cp.collection_point_id = d.collection_point_id
         WHERE cp.ngo_id = ?
           AND d.status = ?
         ORDER BY d.submitted_at DESC, d.donation_id DESC',
        [$ngoUserId, $status]
    );

    foreach ($rows as &$row) {
        $row['items'] = donations_items_for_donation((int) ($row['donation_id'] ?? 0));
    }
    unset($row);

    return $rows;
}

function donations_mark_received_and_sync_inventory(int $donationId, int $ngoUserId): array
{
    donations_ensure_schema();

    $donation = db_fetch(
        'SELECT d.*, cp.ngo_id
         FROM donations d
         INNER JOIN collection_points cp ON cp.collection_point_id = d.collection_point_id
         WHERE d.donation_id = ?
           AND cp.ngo_id = ?
         LIMIT 1',
        [$donationId, $ngoUserId]
    );

    if (!$donation) {
        return ['ok' => false, 'message' => 'Donation not found or access denied.'];
    }

    if ((string) ($donation['status'] ?? '') !== 'Pending') {
        return ['ok' => false, 'message' => 'Only pending donations can be marked as received.'];
    }

    $items = donations_items_for_donation($donationId);

    $pdo = db_connect();
    $pdo->beginTransaction();

    try {
        $updated = db_query(
            "UPDATE donations
             SET status = 'Received',
                 received_at = COALESCE(received_at, NOW()),
                 delivered_at = COALESCE(delivered_at, NOW())
             WHERE donation_id = ?
               AND status = 'Pending'",
            [$donationId]
        )->rowCount();

        if ($updated < 1) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['ok' => false, 'message' => 'Donation was already updated by another user.'];
        }

        foreach ($items as $item) {
            $qty = max(0, (int) ($item['quantity'] ?? 0));
            if ($qty < 1) {
                continue;
            }

            db_query(
                'INSERT INTO inventory (ngo_id, collection_point_id, item_id, quantity)
                 VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)',
                [
                    $ngoUserId,
                    (int) ($donation['collection_point_id'] ?? 0),
                    (int) ($item['item_id'] ?? 0),
                    $qty,
                ]
            );
        }

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }

    $updatedDonation = db_fetch(
        'SELECT d.*, cp.name AS collection_point_name
         FROM donations d
         INNER JOIN collection_points cp ON cp.collection_point_id = d.collection_point_id
         WHERE d.donation_id = ?
         LIMIT 1',
        [$donationId]
    ) ?? $donation;

    return [
        'ok' => true,
        'message' => 'Donation marked as received and inventory updated.',
        'donation' => $updatedDonation,
        'items' => $items,
    ];
}
