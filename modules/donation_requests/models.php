<?php

/**
 * Donation Requests Module - Models
 */

function donation_requests_table_exists(string $tableName): bool
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

function donation_requests_column_exists(string $tableName, string $columnName): bool
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

function donation_requests_index_exists(string $tableName, string $indexName): bool
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

function donation_requests_ensure_column(string $tableName, string $columnName, string $definition): void
{
    if (!donation_requests_column_exists($tableName, $columnName)) {
        db_query("ALTER TABLE {$tableName} ADD COLUMN {$columnName} {$definition}");
    }
}

function donation_requests_ensure_schema(): void
{
    static $ensured = false;
    if ($ensured) {
        return;
    }

    if (!donation_requests_table_exists('donation_requests')) {
        db_query(
            "CREATE TABLE donation_requests (
                request_id INT NOT NULL AUTO_INCREMENT,
                user_id INT NOT NULL,
                relief_center_name VARCHAR(150) NOT NULL,
                status ENUM('Pending','Approved') DEFAULT 'Pending',
                special_notes TEXT,
                submitted_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                approved_at TIMESTAMP NULL DEFAULT NULL,
                location VARCHAR(255) DEFAULT NULL,
                contact_number VARCHAR(20) DEFAULT NULL,
                situation TEXT,
                PRIMARY KEY (request_id)
            ) ENGINE=InnoDB"
        );
    }

    donation_requests_ensure_column('donation_requests', 'safe_location_id', 'INT DEFAULT NULL AFTER user_id');
    donation_requests_ensure_column('donation_requests', 'assigned_gn_user_id', 'INT DEFAULT NULL AFTER safe_location_id');
    donation_requests_ensure_column(
        'donation_requests',
        'processing_status',
        "ENUM('requested','requirement_gathered','fulfilled') NOT NULL DEFAULT 'requested' AFTER status"
    );
    donation_requests_ensure_column('donation_requests', 'fulfilled_at', 'TIMESTAMP NULL DEFAULT NULL AFTER approved_at');

    if (!donation_requests_index_exists('donation_requests', 'idx_donation_requests_safe_location')) {
        db_query('ALTER TABLE donation_requests ADD INDEX idx_donation_requests_safe_location (safe_location_id)');
    }

    if (!donation_requests_index_exists('donation_requests', 'idx_donation_requests_assigned_gn')) {
        db_query('ALTER TABLE donation_requests ADD INDEX idx_donation_requests_assigned_gn (assigned_gn_user_id)');
    }

    if (!donation_requests_index_exists('donation_requests', 'idx_donation_requests_processing_status')) {
        db_query('ALTER TABLE donation_requests ADD INDEX idx_donation_requests_processing_status (processing_status)');
    }

    db_query(
        "CREATE TABLE IF NOT EXISTS donation_request_requirements (
            requirement_id INT NOT NULL AUTO_INCREMENT,
            location_id INT NOT NULL,
            gn_user_id INT NOT NULL,
            relief_center_name VARCHAR(150) NOT NULL,
            location_label VARCHAR(255) NOT NULL,
            contact_number VARCHAR(20) NOT NULL,
            situation_description TEXT NOT NULL,
            special_notes TEXT,
            days_count INT NOT NULL DEFAULT 1,
            packs_toddlers INT NOT NULL DEFAULT 0,
            packs_children INT NOT NULL DEFAULT 0,
            packs_adults INT NOT NULL DEFAULT 0,
            packs_elderly INT NOT NULL DEFAULT 0,
            packs_pregnant_women INT NOT NULL DEFAULT 0,
            status ENUM('Gathered','Fulfilled') NOT NULL DEFAULT 'Gathered',
            fulfilled_at TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (requirement_id),
            INDEX idx_drr_location (location_id),
            INDEX idx_drr_status (status),
            INDEX idx_drr_created_at (created_at)
        ) ENGINE=InnoDB"
    );

    db_query(
        "CREATE TABLE IF NOT EXISTS donation_request_requirement_items (
            requirement_item_id INT NOT NULL AUTO_INCREMENT,
            requirement_id INT NOT NULL,
            item_category ENUM('Medicine','Food','Shelter') NOT NULL,
            item_name VARCHAR(160) NOT NULL,
            quantity DECIMAL(12,2) NOT NULL DEFAULT 0,
            unit VARCHAR(30) NOT NULL DEFAULT 'units',
            source ENUM('pack','extra') NOT NULL DEFAULT 'pack',
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (requirement_item_id),
            INDEX idx_drri_requirement (requirement_id),
            INDEX idx_drri_category (item_category)
        ) ENGINE=InnoDB"
    );

    $ensured = true;
}

function donation_requests_pack_definitions(): array
{
    return [
        'toddlers' => [
            'label' => 'Toddlers (1-4 yrs)',
            'items' => [
                ['item_category' => 'Food', 'item_name' => 'Water', 'quantity' => 1.2, 'unit' => 'L', 'cadence' => 'daily'],
                ['item_category' => 'Food', 'item_name' => 'Infant cereal / Porridge', 'quantity' => 200, 'unit' => 'g', 'cadence' => 'daily'],
                ['item_category' => 'Food', 'item_name' => 'Milk formula', 'quantity' => 55, 'unit' => 'g', 'cadence' => 'daily'],
                ['item_category' => 'Food', 'item_name' => 'Fruit puree', 'quantity' => 80, 'unit' => 'g', 'cadence' => 'daily'],
                ['item_category' => 'Food', 'item_name' => 'Soft snacks', 'quantity' => 50, 'unit' => 'g', 'cadence' => 'daily'],
                ['item_category' => 'Medicine', 'item_name' => 'ORS', 'quantity' => 1, 'unit' => 'sachet', 'cadence' => 'daily'],
                ['item_category' => 'Medicine', 'item_name' => 'Baby paracetamol', 'quantity' => 1, 'unit' => 'dose', 'cadence' => 'daily'],
                ['item_category' => 'Shelter', 'item_name' => 'Diapers', 'quantity' => 5, 'unit' => 'pcs', 'cadence' => 'daily'],
                ['item_category' => 'Shelter', 'item_name' => 'Baby wipes', 'quantity' => 10, 'unit' => 'pcs', 'cadence' => 'daily'],
                ['item_category' => 'Shelter', 'item_name' => 'Baby soap', 'quantity' => 1, 'unit' => 'bar', 'cadence' => 'daily'],
                ['item_category' => 'Shelter', 'item_name' => 'Baby blanket', 'quantity' => 1, 'unit' => 'pc', 'cadence' => 'weekly'],
            ],
        ],
        'children' => [
            'label' => 'Children (5-17 yrs)',
            'items' => [
                ['item_category' => 'Food', 'item_name' => 'Water', 'quantity' => 2, 'unit' => 'L', 'cadence' => 'daily'],
                ['item_category' => 'Food', 'item_name' => 'Rice / Rotis / Noodles', 'quantity' => 350, 'unit' => 'g', 'cadence' => 'daily'],
                ['item_category' => 'Food', 'item_name' => 'High-Energy Biscuits', 'quantity' => 150, 'unit' => 'g', 'cadence' => 'daily'],
                ['item_category' => 'Food', 'item_name' => 'Protein - Eggs / Beans', 'quantity' => 100, 'unit' => 'g', 'cadence' => 'daily'],
                ['item_category' => 'Food', 'item_name' => 'Milk powder', 'quantity' => 30, 'unit' => 'g', 'cadence' => 'daily'],
                ['item_category' => 'Medicine', 'item_name' => 'ORS', 'quantity' => 1, 'unit' => 'sachet', 'cadence' => 'daily'],
                ['item_category' => 'Medicine', 'item_name' => 'Child-dose Paracetamol', 'quantity' => 2, 'unit' => 'tablets', 'cadence' => 'daily'],
                ['item_category' => 'Medicine', 'item_name' => 'Basic first aid ointment', 'quantity' => 1, 'unit' => 'portion', 'cadence' => 'daily'],
                ['item_category' => 'Shelter', 'item_name' => 'Blanket', 'quantity' => 1, 'unit' => 'pc', 'cadence' => 'weekly'],
                ['item_category' => 'Shelter', 'item_name' => 'Soap', 'quantity' => 0.25, 'unit' => 'bar', 'cadence' => 'daily'],
                ['item_category' => 'Shelter', 'item_name' => 'Wet wipes', 'quantity' => 3, 'unit' => 'pcs', 'cadence' => 'daily'],
                ['item_category' => 'Shelter', 'item_name' => 'Toothpaste & brush', 'quantity' => 1, 'unit' => 'kit', 'cadence' => 'weekly'],
            ],
        ],
        'adults' => [
            'label' => 'Adults (18-49 yrs)',
            'items' => [
                ['item_category' => 'Food', 'item_name' => 'Water', 'quantity' => 2.5, 'unit' => 'L', 'cadence' => 'daily'],
                ['item_category' => 'Food', 'item_name' => 'Rice / Noodles / Rotis', 'quantity' => 400, 'unit' => 'g', 'cadence' => 'daily'],
                ['item_category' => 'Food', 'item_name' => 'High-Energy Biscuits', 'quantity' => 200, 'unit' => 'g', 'cadence' => 'daily'],
                ['item_category' => 'Food', 'item_name' => 'Protein - Fish / Eggs / Beans', 'quantity' => 150, 'unit' => 'g', 'cadence' => 'daily'],
                ['item_category' => 'Food', 'item_name' => 'Tea / Sugar', 'quantity' => 30, 'unit' => 'g', 'cadence' => 'daily'],
                ['item_category' => 'Medicine', 'item_name' => 'First aid basics', 'quantity' => 1, 'unit' => 'portion', 'cadence' => 'daily'],
                ['item_category' => 'Medicine', 'item_name' => 'ORS', 'quantity' => 1, 'unit' => 'sachet', 'cadence' => 'daily'],
                ['item_category' => 'Medicine', 'item_name' => 'Pain relief tablets', 'quantity' => 2, 'unit' => 'tablets', 'cadence' => 'daily'],
                ['item_category' => 'Shelter', 'item_name' => 'Blanket', 'quantity' => 1, 'unit' => 'pc', 'cadence' => 'weekly'],
                ['item_category' => 'Shelter', 'item_name' => 'Soap', 'quantity' => 0.33, 'unit' => 'bar', 'cadence' => 'daily'],
                ['item_category' => 'Shelter', 'item_name' => 'Wet wipes', 'quantity' => 3, 'unit' => 'pcs', 'cadence' => 'daily'],
                ['item_category' => 'Shelter', 'item_name' => 'Toothpaste & brush', 'quantity' => 1, 'unit' => 'kit', 'cadence' => 'weekly'],
            ],
        ],
        'pregnant_women' => [
            'label' => 'Pregnant Women',
            'items' => [
                ['item_category' => 'Food', 'item_name' => 'Water', 'quantity' => 3, 'unit' => 'L', 'cadence' => 'daily'],
                ['item_category' => 'Food', 'item_name' => 'Rice / Porridge', 'quantity' => 400, 'unit' => 'g', 'cadence' => 'daily'],
                ['item_category' => 'Food', 'item_name' => 'High-Energy Biscuits', 'quantity' => 250, 'unit' => 'g', 'cadence' => 'daily'],
                ['item_category' => 'Food', 'item_name' => 'Protein - Eggs / Beans / Fish', 'quantity' => 150, 'unit' => 'g', 'cadence' => 'daily'],
                ['item_category' => 'Food', 'item_name' => 'Milk powder', 'quantity' => 50, 'unit' => 'g', 'cadence' => 'daily'],
                ['item_category' => 'Medicine', 'item_name' => 'Prenatal vitamins', 'quantity' => 1, 'unit' => 'dose', 'cadence' => 'daily'],
                ['item_category' => 'Medicine', 'item_name' => 'Iron Folic Acid tablets', 'quantity' => 1, 'unit' => 'dose', 'cadence' => 'daily'],
                ['item_category' => 'Medicine', 'item_name' => 'ORS', 'quantity' => 1, 'unit' => 'sachet', 'cadence' => 'daily'],
                ['item_category' => 'Medicine', 'item_name' => 'Paracetamol', 'quantity' => 2, 'unit' => 'tablets', 'cadence' => 'daily'],
                ['item_category' => 'Shelter', 'item_name' => 'Blanket', 'quantity' => 1, 'unit' => 'pc', 'cadence' => 'weekly'],
                ['item_category' => 'Shelter', 'item_name' => 'Soap', 'quantity' => 0.25, 'unit' => 'bar', 'cadence' => 'daily'],
                ['item_category' => 'Shelter', 'item_name' => 'Wet wipes', 'quantity' => 5, 'unit' => 'pcs', 'cadence' => 'daily'],
                ['item_category' => 'Shelter', 'item_name' => 'Sanitary pads', 'quantity' => 3, 'unit' => 'pcs', 'cadence' => 'daily'],
                ['item_category' => 'Shelter', 'item_name' => 'Toothpaste & brush', 'quantity' => 1, 'unit' => 'kit', 'cadence' => 'weekly'],
            ],
        ],
        'elderly' => [
            'label' => 'Elderly (50+)',
            'items' => [
                ['item_category' => 'Food', 'item_name' => 'Water', 'quantity' => 2.5, 'unit' => 'L', 'cadence' => 'daily'],
                ['item_category' => 'Food', 'item_name' => 'Soft foods / Porridge', 'quantity' => 350, 'unit' => 'g', 'cadence' => 'daily'],
                ['item_category' => 'Food', 'item_name' => 'High-Energy Biscuits', 'quantity' => 200, 'unit' => 'g', 'cadence' => 'daily'],
                ['item_category' => 'Food', 'item_name' => 'Protein - Fish / Eggs / Beans', 'quantity' => 120, 'unit' => 'g', 'cadence' => 'daily'],
                ['item_category' => 'Food', 'item_name' => 'Fruit puree', 'quantity' => 100, 'unit' => 'g', 'cadence' => 'daily'],
                ['item_category' => 'Medicine', 'item_name' => 'Chronic meds - BP / Diabetes', 'quantity' => 1, 'unit' => 'dose', 'cadence' => 'daily'],
                ['item_category' => 'Medicine', 'item_name' => 'ORS', 'quantity' => 1, 'unit' => 'sachet', 'cadence' => 'daily'],
                ['item_category' => 'Medicine', 'item_name' => 'Paracetamol', 'quantity' => 2, 'unit' => 'tablets', 'cadence' => 'daily'],
                ['item_category' => 'Shelter', 'item_name' => 'Blanket', 'quantity' => 1, 'unit' => 'pc', 'cadence' => 'weekly'],
                ['item_category' => 'Shelter', 'item_name' => 'Soap', 'quantity' => 0.5, 'unit' => 'bar', 'cadence' => 'daily'],
                ['item_category' => 'Shelter', 'item_name' => 'Wet wipes', 'quantity' => 4, 'unit' => 'pcs', 'cadence' => 'daily'],
                ['item_category' => 'Shelter', 'item_name' => 'Adult diapers', 'quantity' => 2, 'unit' => 'pcs', 'cadence' => 'daily'],
                ['item_category' => 'Shelter', 'item_name' => 'Toothpaste & brush', 'quantity' => 1, 'unit' => 'kit', 'cadence' => 'weekly'],
            ],
        ],
    ];
}

function donation_requests_additional_item_catalog(): array
{
    return [
        'Medicine' => [
            'Amoxicillin',
            'Antiseptic solution',
            'Bandages',
            'Paracetamol',
            'Aspirin',
            'Antifungal powder',
            'Mosquito repellent',
            'Diabetes tablets',
            'Sanitary pads',
            'Cotton wool',
            'Alcohol swabs',
        ],
        'Food' => [
            'Rice',
            'Dhal',
            'Canned fish',
            'Infant formula',
            'Baby food jars or sachets',
            'Drinking water',
            'Instant noodles',
            'Biscuits',
            'Canned meat',
            'Milk powder',
            'Cooking oil',
        ],
        'Shelter' => [
            'Sleeping mats',
            'Blankets',
            'Towels',
            'Toothbrush',
            'Soap',
            'Basic clothing',
            'Candles',
            'Plates, cups',
            'Baby diapers',
            'Baby clothes',
            'Disinfectant',
        ],
    ];
}

function donation_requests_find_general_profile(int $userId): ?array
{
    donation_requests_ensure_schema();

    return db_fetch(
        'SELECT user_id, name, contact_number, district, gn_division
         FROM general_user
         WHERE user_id = ?
         LIMIT 1',
        [$userId]
    );
}

function donation_requests_list_safe_locations_for_general(int $userId): array
{
    donation_requests_ensure_schema();

    $profile = donation_requests_find_general_profile($userId);
    if (!$profile) {
        return [];
    }

    $sql = 'SELECT sl.location_id, sl.location_name, sl.district, sl.gn_division, sl.assigned_gn_user_id
            FROM safe_locations sl
            WHERE sl.gn_division = ?';
    $params = [(string) ($profile['gn_division'] ?? '')];

    $district = trim((string) ($profile['district'] ?? ''));
    if ($district !== '') {
        $sql .= ' AND sl.district = ?';
        $params[] = $district;
    }

    $sql .= ' ORDER BY sl.location_name ASC';

    return db_fetch_all($sql, $params);
}

function donation_requests_find_safe_location_for_general(int $userId, int $locationId): ?array
{
    donation_requests_ensure_schema();

    $profile = donation_requests_find_general_profile($userId);
    if (!$profile) {
        return null;
    }

    $sql = 'SELECT sl.location_id, sl.location_name, sl.district, sl.gn_division, sl.assigned_gn_user_id
            FROM safe_locations sl
            WHERE sl.location_id = ?
              AND sl.gn_division = ?';
    $params = [$locationId, (string) ($profile['gn_division'] ?? '')];

    $district = trim((string) ($profile['district'] ?? ''));
    if ($district !== '') {
        $sql .= ' AND sl.district = ?';
        $params[] = $district;
    }

    $sql .= ' LIMIT 1';

    return db_fetch($sql, $params);
}

function donation_requests_create_general_request(int $userId, int $locationId): int
{
    donation_requests_ensure_schema();

    $location = donation_requests_find_safe_location_for_general($userId, $locationId);
    if (!$location) {
        return 0;
    }

    $profile = donation_requests_find_general_profile($userId);
    $locationLabel = trim((string) ($location['district'] ?? '') . ' / ' . (string) ($location['gn_division'] ?? ''));
    $situation = 'Donation requested by affected user.';

    db_query(
        'INSERT INTO donation_requests
         (user_id, safe_location_id, assigned_gn_user_id, relief_center_name, status, processing_status, special_notes, submitted_at, location, contact_number, situation)
         VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)',
        [
            $userId,
            (int) $location['location_id'],
            (int) ($location['assigned_gn_user_id'] ?? 0) > 0 ? (int) $location['assigned_gn_user_id'] : null,
            (string) $location['location_name'],
            'Pending',
            'requested',
            null,
            $locationLabel,
            (string) ($profile['contact_number'] ?? ''),
            $situation,
        ]
    );

    return (int) db_connect()->lastInsertId();
}

function donation_requests_find_assigned_location_for_gn(int $gnUserId, int $locationId): ?array
{
    donation_requests_ensure_schema();

    return db_fetch(
        'SELECT location_id, location_name, district, gn_division, assigned_gn_user_id
         FROM safe_locations
         WHERE location_id = ?
           AND assigned_gn_user_id = ?
         LIMIT 1',
        [$locationId, $gnUserId]
    );
}

function donation_requests_list_location_groups_for_gn(int $gnUserId): array
{
    donation_requests_ensure_schema();

    return db_fetch_all(
        "SELECT sl.location_id,
                sl.location_name,
                sl.district,
                sl.gn_division,
                COUNT(dr.request_id) AS total_requests,
                SUM(CASE WHEN dr.processing_status = 'requested' THEN 1 ELSE 0 END) AS requested_count,
                SUM(CASE WHEN dr.processing_status = 'requirement_gathered' THEN 1 ELSE 0 END) AS gathered_count,
                SUM(CASE WHEN dr.processing_status = 'fulfilled' THEN 1 ELSE 0 END) AS fulfilled_count,
                MAX(dr.submitted_at) AS latest_request_at,
                (
                    SELECT rr.status
                    FROM donation_request_requirements rr
                    WHERE rr.location_id = sl.location_id
                    ORDER BY rr.requirement_id DESC
                    LIMIT 1
                ) AS latest_requirement_status
         FROM safe_locations sl
         LEFT JOIN donation_requests dr ON dr.safe_location_id = sl.location_id
         WHERE sl.assigned_gn_user_id = ?
         GROUP BY sl.location_id, sl.location_name, sl.district, sl.gn_division
         ORDER BY sl.location_name ASC",
        [$gnUserId]
    );
}

function donation_requests_list_pending_requests_by_location(int $locationId): array
{
    donation_requests_ensure_schema();

    return db_fetch_all(
        "SELECT dr.request_id,
                dr.submitted_at,
                dr.processing_status,
                dr.contact_number,
                gu.name AS requester_name
         FROM donation_requests dr
         INNER JOIN general_user gu ON gu.user_id = dr.user_id
         WHERE dr.safe_location_id = ?
           AND dr.processing_status = 'requested'
         ORDER BY dr.submitted_at DESC",
        [$locationId]
    );
}

function donation_requests_pack_counts_from_input(array $input): array
{
    return [
        'toddlers' => max(0, (int) ($input['toddlers'] ?? 0)),
        'children' => max(0, (int) ($input['children'] ?? 0)),
        'adults' => max(0, (int) ($input['adults'] ?? 0)),
        'elderly' => max(0, (int) ($input['elderly'] ?? 0)),
        'pregnant_women' => max(0, (int) ($input['pregnant_women'] ?? 0)),
    ];
}

function donation_requests_has_any_requirement_input(array $packCounts, array $extras): bool
{
    foreach ($packCounts as $count) {
        if ((int) $count > 0) {
            return true;
        }
    }

    foreach ($extras as $categoryItems) {
        if (!is_array($categoryItems)) {
            continue;
        }

        foreach ($categoryItems as $qty) {
            if ((int) $qty > 0) {
                return true;
            }
        }
    }

    return false;
}

function donation_requests_compute_requirement_items(array $packCounts, int $daysCount, array $extras): array
{
    $packDefs = donation_requests_pack_definitions();
    $catalog = donation_requests_additional_item_catalog();

    $days = max(1, $daysCount);
    $weeklyMultiplier = (int) ceil($days / 7);

    $totals = [];

    foreach ($packDefs as $packKey => $packDef) {
        $count = max(0, (int) ($packCounts[$packKey] ?? 0));
        if ($count === 0) {
            continue;
        }

        foreach ((array) ($packDef['items'] ?? []) as $item) {
            $cadence = (string) ($item['cadence'] ?? 'daily');
            $multiplier = $cadence === 'weekly' ? $weeklyMultiplier : $days;

            $itemCategory = (string) ($item['item_category'] ?? 'Food');
            $itemName = (string) ($item['item_name'] ?? 'Unknown Item');
            $unit = (string) ($item['unit'] ?? 'units');
            $quantity = (float) ($item['quantity'] ?? 0);

            $key = $itemCategory . '|' . $itemName . '|' . $unit;

            if (!isset($totals[$key])) {
                $totals[$key] = [
                    'item_category' => $itemCategory,
                    'item_name' => $itemName,
                    'quantity' => 0.0,
                    'unit' => $unit,
                    'source' => 'pack',
                ];
            }

            $totals[$key]['quantity'] += $quantity * $count * $multiplier;
        }
    }

    foreach ($catalog as $category => $items) {
        foreach ($items as $itemName) {
            $qty = max(0, (int) ($extras[$category][$itemName] ?? 0));
            if ($qty <= 0) {
                continue;
            }

            $key = $category . '|' . $itemName . '|units';
            if (!isset($totals[$key])) {
                $totals[$key] = [
                    'item_category' => $category,
                    'item_name' => $itemName,
                    'quantity' => 0.0,
                    'unit' => 'units',
                    'source' => 'extra',
                ];
            }

            $totals[$key]['quantity'] += $qty;
        }
    }

    foreach ($totals as &$row) {
        $row['quantity'] = round((float) $row['quantity'], 2);
    }
    unset($row);

    usort($totals, static function (array $a, array $b): int {
        $left = (string) ($a['item_category'] ?? '') . '|' . (string) ($a['item_name'] ?? '');
        $right = (string) ($b['item_category'] ?? '') . '|' . (string) ($b['item_name'] ?? '');
        return $left <=> $right;
    });

    return $totals;
}

function donation_requests_create_requirement(array $payload, array $items): int
{
    donation_requests_ensure_schema();

    $pdo = db_connect();
    $pdo->beginTransaction();

    try {
        db_query(
            'INSERT INTO donation_request_requirements
             (location_id, gn_user_id, relief_center_name, location_label, contact_number, situation_description, special_notes, days_count, packs_toddlers, packs_children, packs_adults, packs_elderly, packs_pregnant_women, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                (int) $payload['location_id'],
                (int) $payload['gn_user_id'],
                (string) $payload['relief_center_name'],
                (string) $payload['location_label'],
                (string) $payload['contact_number'],
                (string) $payload['situation_description'],
                (string) ($payload['special_notes'] ?? ''),
                (int) $payload['days_count'],
                (int) $payload['packs_toddlers'],
                (int) $payload['packs_children'],
                (int) $payload['packs_adults'],
                (int) $payload['packs_elderly'],
                (int) $payload['packs_pregnant_women'],
                'Gathered',
            ]
        );

        $requirementId = (int) $pdo->lastInsertId();

        foreach ($items as $item) {
            db_query(
                'INSERT INTO donation_request_requirement_items
                 (requirement_id, item_category, item_name, quantity, unit, source)
                 VALUES (?, ?, ?, ?, ?, ?)',
                [
                    $requirementId,
                    (string) ($item['item_category'] ?? 'Food'),
                    (string) ($item['item_name'] ?? ''),
                    (float) ($item['quantity'] ?? 0),
                    (string) ($item['unit'] ?? 'units'),
                    (string) ($item['source'] ?? 'pack'),
                ]
            );
        }

        db_query(
            "UPDATE donation_requests
             SET processing_status = 'requirement_gathered'
             WHERE safe_location_id = ?
               AND processing_status = 'requested'",
            [(int) $payload['location_id']]
        );

        $pdo->commit();
        return $requirementId;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }
}

function donation_requests_mark_location_fulfilled(int $locationId): void
{
    donation_requests_ensure_schema();

    $pdo = db_connect();
    $pdo->beginTransaction();

    try {
        db_query(
            "UPDATE donation_request_requirements
             SET status = 'Fulfilled', fulfilled_at = NOW(), updated_at = NOW()
             WHERE location_id = ?
               AND status = 'Gathered'",
            [$locationId]
        );

        db_query(
            "UPDATE donation_requests
             SET processing_status = 'fulfilled',
                 status = 'Approved',
                 approved_at = COALESCE(approved_at, NOW()),
                 fulfilled_at = NOW()
             WHERE safe_location_id = ?
               AND processing_status <> 'fulfilled'",
            [$locationId]
        );

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }
}

function donation_requests_list_requirement_feed(): array
{
    donation_requests_ensure_schema();

    $requirements = db_fetch_all(
        'SELECT rr.requirement_id,
                rr.location_id,
                rr.gn_user_id,
                rr.relief_center_name,
                rr.location_label,
                rr.contact_number,
                rr.situation_description,
                rr.special_notes,
                rr.days_count,
                rr.packs_toddlers,
                rr.packs_children,
                rr.packs_adults,
                rr.packs_elderly,
                rr.packs_pregnant_women,
                rr.status,
                rr.created_at,
                rr.updated_at,
                rr.fulfilled_at,
                sl.location_name,
                sl.district,
                sl.gn_division,
                gn.name AS gn_name
         FROM donation_request_requirements rr
         INNER JOIN safe_locations sl ON sl.location_id = rr.location_id
         LEFT JOIN grama_niladhari gn ON gn.user_id = rr.gn_user_id
         ORDER BY rr.created_at DESC, rr.requirement_id DESC'
    );

    foreach ($requirements as &$requirement) {
        $items = db_fetch_all(
            "SELECT item_category, item_name, quantity, unit, source
             FROM donation_request_requirement_items
             WHERE requirement_id = ?
             ORDER BY FIELD(item_category, 'Food', 'Medicine', 'Shelter'), item_name ASC",
            [(int) ($requirement['requirement_id'] ?? 0)]
        );

        $groupedItems = [
            'Food' => [],
            'Medicine' => [],
            'Shelter' => [],
        ];

        foreach ($items as $item) {
            $category = (string) ($item['item_category'] ?? 'Food');
            if (!array_key_exists($category, $groupedItems)) {
                $groupedItems[$category] = [];
            }
            $groupedItems[$category][] = $item;
        }

        $requirement['items'] = $items;
        $requirement['items_grouped'] = $groupedItems;
    }
    unset($requirement);

    return $requirements;
}
