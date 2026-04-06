<?php

/**
 * Inventory Module - Models
 */

function inventory_table_exists(string $tableName): bool
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

function inventory_column_exists(string $tableName, string $columnName): bool
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

function inventory_index_exists(string $tableName, string $indexName): bool
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

function inventory_ensure_column(string $tableName, string $columnName, string $definition): void
{
    if (!inventory_column_exists($tableName, $columnName)) {
        db_query("ALTER TABLE {$tableName} ADD COLUMN {$columnName} {$definition}");
    }
}

function inventory_ensure_schema(): void
{
    static $ensured = false;
    if ($ensured) {
        return;
    }

    if (!inventory_table_exists('inventory')) {
        db_query(
            "CREATE TABLE inventory (
                inventory_id INT NOT NULL AUTO_INCREMENT,
                ngo_id INT NOT NULL,
                collection_point_id INT NOT NULL,
                item_id INT NOT NULL,
                quantity INT DEFAULT 0,
                status ENUM('In Stock','Low on Stock','Out of Stock') GENERATED ALWAYS AS (
                    (CASE
                        WHEN quantity = 0 THEN 'Out of Stock'
                        WHEN quantity < 20 THEN 'Low on Stock'
                        ELSE 'In Stock'
                    END)
                ) STORED,
                last_updated TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (inventory_id),
                UNIQUE KEY uq_inventory_ngo_cp_item (ngo_id, collection_point_id, item_id)
            ) ENGINE=InnoDB"
        );
    }

    inventory_ensure_column('inventory', 'last_updated', 'TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

    if (!inventory_index_exists('inventory', 'uq_inventory_ngo_cp_item')) {
        db_query('ALTER TABLE inventory ADD UNIQUE INDEX uq_inventory_ngo_cp_item (ngo_id, collection_point_id, item_id)');
    }

    $ensured = true;
}

function inventory_ensure_ngo_baseline_rows(int $ngoUserId): void
{
    inventory_ensure_schema();

    db_query(
        'INSERT INTO inventory (ngo_id, collection_point_id, item_id, quantity)
         SELECT cp.ngo_id,
                cp.collection_point_id,
                c.item_id,
                0
         FROM collection_points cp
         CROSS JOIN donation_items_catalog c
         WHERE cp.ngo_id = ?
           AND NOT EXISTS (
                SELECT 1
                FROM inventory i
                WHERE i.ngo_id = cp.ngo_id
                  AND i.collection_point_id = cp.collection_point_id
                  AND i.item_id = c.item_id
           )',
        [$ngoUserId]
    );
}

function inventory_collection_point_options(int $ngoUserId): array
{
    inventory_ensure_schema();

    return db_fetch_all(
        'SELECT collection_point_id, name
         FROM collection_points
         WHERE ngo_id = ?
         ORDER BY name ASC',
        [$ngoUserId]
    );
}

function inventory_list_for_ngo(int $ngoUserId): array
{
    inventory_ensure_schema();

    return db_fetch_all(
        "SELECT i.inventory_id,
                i.ngo_id,
                i.collection_point_id,
                i.item_id,
                i.quantity,
                i.status,
                i.last_updated,
                cp.name AS collection_point_name,
                c.item_name,
                c.category
         FROM inventory i
         INNER JOIN collection_points cp
             ON cp.collection_point_id = i.collection_point_id
         INNER JOIN donation_items_catalog c
             ON c.item_id = i.item_id
         WHERE i.ngo_id = ?
         ORDER BY cp.name ASC,
                  FIELD(c.category, 'Medicine', 'Food', 'Shelter'),
                  c.item_name ASC",
        [$ngoUserId]
    );
}

function inventory_list_central_totals_for_ngo(int $ngoUserId): array
{
    inventory_ensure_schema();

    return db_fetch_all(
        "SELECT c.item_id,
                c.item_name,
                c.category,
                COALESCE(SUM(i.quantity), 0) AS quantity,
                CASE
                    WHEN COALESCE(SUM(i.quantity), 0) = 0 THEN 'Out of Stock'
                    WHEN COALESCE(SUM(i.quantity), 0) < 20 THEN 'Low on Stock'
                    ELSE 'In Stock'
                END AS status,
                MAX(i.last_updated) AS last_updated
         FROM donation_items_catalog c
         LEFT JOIN inventory i
            ON i.item_id = c.item_id
           AND i.ngo_id = ?
         GROUP BY c.item_id, c.item_name, c.category
         ORDER BY FIELD(c.category, 'Medicine', 'Food', 'Shelter'), c.item_name ASC",
        [$ngoUserId]
    );
}

function inventory_summary_counts(int $ngoUserId): array
{
    inventory_ensure_schema();

    $row = db_fetch(
        'SELECT COUNT(*) AS inventory_rows,
                SUM(quantity) AS total_units,
                COUNT(DISTINCT collection_point_id) AS covered_points,
                SUM(CASE WHEN status = \'In Stock\' THEN 1 ELSE 0 END) AS in_stock_rows,
                SUM(CASE WHEN status = \'Low on Stock\' THEN 1 ELSE 0 END) AS low_stock_rows,
                SUM(CASE WHEN status = \'Out of Stock\' THEN 1 ELSE 0 END) AS out_stock_rows
         FROM inventory
         WHERE ngo_id = ?',
        [$ngoUserId]
    ) ?? [];

    return [
        'inventory_rows' => (int) ($row['inventory_rows'] ?? 0),
        'total_units' => (int) ($row['total_units'] ?? 0),
        'covered_points' => (int) ($row['covered_points'] ?? 0),
        'in_stock_rows' => (int) ($row['in_stock_rows'] ?? 0),
        'low_stock_rows' => (int) ($row['low_stock_rows'] ?? 0),
        'out_stock_rows' => (int) ($row['out_stock_rows'] ?? 0),
    ];
}

function inventory_find_for_ngo(int $inventoryId, int $ngoUserId): ?array
{
    inventory_ensure_schema();

    return db_fetch(
        'SELECT inventory_id, ngo_id, collection_point_id, item_id, quantity, status, last_updated
         FROM inventory
         WHERE inventory_id = ?
           AND ngo_id = ?
         LIMIT 1',
        [$inventoryId, $ngoUserId]
    );
}

function inventory_update_quantity_for_ngo(int $inventoryId, int $ngoUserId, int $quantity): int
{
    inventory_ensure_schema();

    if ($quantity < 0) {
        return 0;
    }

    return db_query(
        'UPDATE inventory
         SET quantity = ?
         WHERE inventory_id = ?
           AND ngo_id = ?
         LIMIT 1',
        [$quantity, $inventoryId, $ngoUserId]
    )->rowCount();
}
