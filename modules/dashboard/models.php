<?php

/**
 * Dashboard Module - Models
 */

function dashboard_table_exists(string $tableName): bool
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

function dashboard_month_windows(int $months = 6): array
{
    $months = max(1, $months);
    $window = [];

    $cursor = new DateTimeImmutable('first day of this month');
    for ($i = $months - 1; $i >= 0; $i--) {
        $point = $cursor->modify('-' . $i . ' months');
        $window[] = [
            'month_key' => $point->format('Y-m'),
            'label' => $point->format('M Y'),
            'value' => 0,
        ];
    }

    return $window;
}

function dashboard_month_series(array $rows, int $months = 6): array
{
    $series = dashboard_month_windows($months);
    $index = [];

    foreach ($rows as $row) {
        $key = (string) ($row['month_key'] ?? '');
        if ($key === '') {
            continue;
        }

        $index[$key] = (int) ($row['value'] ?? 0);
    }

    foreach ($series as &$entry) {
        $key = (string) ($entry['month_key'] ?? '');
        $entry['value'] = (int) ($index[$key] ?? 0);
    }
    unset($entry);

    return $series;
}

function dashboard_rows_with_expected_labels(array $rows, array $expectedLabels): array
{
    $indexed = [];
    foreach ($rows as $row) {
        $label = trim((string) ($row['label'] ?? ''));
        if ($label === '') {
            continue;
        }

        $indexed[$label] = (int) ($row['value'] ?? 0);
    }

    $normalized = [];
    foreach ($expectedLabels as $label) {
        $normalized[] = [
            'label' => $label,
            'value' => (int) ($indexed[$label] ?? 0),
        ];
    }

    return $normalized;
}

function dashboard_dmc_analytics(int $pendingCount = 0): array
{
    $analytics = [
        'cards' => [
            'pending_approvals' => max(0, (int) $pendingCount),
            'pending_reports' => 0,
            'active_tasks' => 0,
            'shelter_utilization_pct' => 0.0,
            'low_stock_items' => 0,
            'active_sms_subscribers' => 0,
        ],
        'disasters' => [
            'status' => [],
            'types' => [],
            'districts' => [],
            'monthly' => dashboard_month_windows(6),
        ],
        'volunteers' => [
            'status' => [],
            'workload' => [],
            'decline_by_district' => [],
        ],
        'donations' => [
            'status' => [],
            'collection_points' => [],
            'inventory_categories' => [],
            'low_stock_items' => [],
        ],
        'shelters' => [
            'totals' => [
                'locations' => 0,
                'capacity' => 0,
                'occupancy' => 0,
                'utilization_pct' => 0.0,
            ],
            'locations' => [],
        ],
        'requirements' => [
            'status' => [],
            'districts' => [],
            'unfulfilled' => 0,
            'categories' => [],
        ],
        'users' => [
            'roles' => [],
            'activity' => [
                ['label' => 'Active', 'value' => 0],
                ['label' => 'Inactive', 'value' => 0],
            ],
        ],
        'sms' => [
            'sent_today' => 0,
            'sent_this_week' => 0,
            'sent_this_month' => 0,
            'severity' => [],
            'stations' => [],
            'monthly' => dashboard_month_windows(6),
        ],
    ];

    if (dashboard_table_exists('disaster_reports')) {
        $statusRows = db_fetch_all(
            'SELECT status AS label, COUNT(*) AS value
             FROM disaster_reports
             GROUP BY status'
        );

        $analytics['disasters']['status'] = dashboard_rows_with_expected_labels(
            $statusRows,
            ['Pending', 'Approved', 'Rejected']
        );

        foreach ($analytics['disasters']['status'] as $row) {
            if ($row['label'] === 'Pending') {
                $analytics['cards']['pending_reports'] = (int) $row['value'];
                break;
            }
        }

        $analytics['disasters']['types'] = db_fetch_all(
            "SELECT CASE
                        WHEN disaster_type = 'Other' AND COALESCE(NULLIF(other_disaster_type, ''), '') <> ''
                            THEN CONCAT('Other: ', other_disaster_type)
                        ELSE disaster_type
                     END AS label,
                     COUNT(*) AS value
             FROM disaster_reports
             GROUP BY label
             ORDER BY value DESC, label ASC
             LIMIT 8"
        );

        $analytics['disasters']['districts'] = db_fetch_all(
            "SELECT district AS label, COUNT(*) AS value
             FROM disaster_reports
             WHERE COALESCE(NULLIF(district, ''), '') <> ''
             GROUP BY district
             ORDER BY value DESC, district ASC
             LIMIT 8"
        );

        $monthlyRows = db_fetch_all(
            "SELECT DATE_FORMAT(submitted_at, '%Y-%m') AS month_key,
                    COUNT(*) AS value
             FROM disaster_reports
             WHERE submitted_at >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
             GROUP BY month_key
             ORDER BY month_key ASC"
        );
        $analytics['disasters']['monthly'] = dashboard_month_series($monthlyRows, 6);
    }

    if (dashboard_table_exists('volunteer_task')) {
        $taskStatusRows = db_fetch_all(
            'SELECT status AS label, COUNT(*) AS value
             FROM volunteer_task
             GROUP BY status'
        );

        $analytics['volunteers']['status'] = dashboard_rows_with_expected_labels(
            $taskStatusRows,
            ['Assigned', 'Accepted', 'In Progress', 'Completed', 'Verified', 'Declined', 'Pending']
        );

        $activeRow = db_fetch(
            "SELECT COUNT(*) AS value
             FROM volunteer_task
             WHERE status IN ('Assigned', 'Accepted', 'In Progress', 'Pending')"
        );
        $analytics['cards']['active_tasks'] = (int) ($activeRow['value'] ?? 0);

        if (dashboard_table_exists('volunteers')) {
            $analytics['volunteers']['workload'] = db_fetch_all(
                "SELECT v.name AS label, COUNT(*) AS value
                 FROM volunteer_task vt
                 INNER JOIN volunteers v ON v.user_id = vt.volunteer_id
                 WHERE vt.status IN ('Assigned', 'Accepted', 'In Progress', 'Pending')
                 GROUP BY vt.volunteer_id, v.name
                 ORDER BY value DESC, v.name ASC
                 LIMIT 8"
            );
        }

        if (dashboard_table_exists('disaster_reports')) {
            $declineRows = db_fetch_all(
                "SELECT dr.district AS label,
                        SUM(CASE WHEN vt.status = 'Declined' THEN 1 ELSE 0 END) AS declined,
                        COUNT(*) AS total
                 FROM volunteer_task vt
                 INNER JOIN disaster_reports dr ON dr.report_id = vt.disaster_id
                 WHERE COALESCE(NULLIF(dr.district, ''), '') <> ''
                 GROUP BY dr.district
                 HAVING COUNT(*) > 0
                 ORDER BY declined DESC, total DESC, dr.district ASC
                 LIMIT 8"
            );

            $analytics['volunteers']['decline_by_district'] = array_map(
                static function (array $row): array {
                    $declined = (int) ($row['declined'] ?? 0);
                    $total = (int) ($row['total'] ?? 0);
                    $rate = $total > 0 ? ($declined * 100 / $total) : 0;

                    return [
                        'label' => (string) ($row['label'] ?? '-'),
                        'declined' => $declined,
                        'total' => $total,
                        'value' => round($rate, 1),
                    ];
                },
                $declineRows
            );
        }
    }

    if (dashboard_table_exists('donations')) {
        $donationStatusRows = db_fetch_all(
            'SELECT status AS label, COUNT(*) AS value
             FROM donations
             GROUP BY status'
        );

        $analytics['donations']['status'] = dashboard_rows_with_expected_labels(
            $donationStatusRows,
            ['Pending', 'Received', 'Cancelled', 'Delivered']
        );

        if (dashboard_table_exists('collection_points')) {
            $analytics['donations']['collection_points'] = db_fetch_all(
                "SELECT cp.name AS label,
                        COUNT(d.donation_id) AS value
                 FROM collection_points cp
                 LEFT JOIN donations d ON d.collection_point_id = cp.collection_point_id
                 GROUP BY cp.collection_point_id, cp.name
                 ORDER BY value DESC, cp.name ASC
                 LIMIT 8"
            );
        }
    }

    if (dashboard_table_exists('inventory')) {
        if (dashboard_table_exists('donation_items_catalog')) {
            $analytics['donations']['inventory_categories'] = db_fetch_all(
                "SELECT c.category AS label,
                        COALESCE(SUM(i.quantity), 0) AS value
                 FROM donation_items_catalog c
                 LEFT JOIN inventory i ON i.item_id = c.item_id
                 GROUP BY c.category
                 ORDER BY FIELD(c.category, 'Medicine', 'Food', 'Shelter')"
            );
        }

        if (dashboard_table_exists('collection_points') && dashboard_table_exists('donation_items_catalog')) {
            $analytics['donations']['low_stock_items'] = db_fetch_all(
                "SELECT c.item_name,
                        c.category,
                        cp.name AS collection_point,
                        i.quantity,
                        i.status
                 FROM inventory i
                 INNER JOIN donation_items_catalog c ON c.item_id = i.item_id
                 INNER JOIN collection_points cp ON cp.collection_point_id = i.collection_point_id
                 WHERE i.quantity < 20
                 ORDER BY i.quantity ASC, c.item_name ASC
                 LIMIT 10"
            );

            $lowStockCountRow = db_fetch(
                'SELECT COUNT(*) AS value FROM inventory WHERE quantity < 20'
            );
            $analytics['cards']['low_stock_items'] = (int) ($lowStockCountRow['value'] ?? 0);
        }
    }

    if (dashboard_table_exists('safe_locations')) {
        $totalsRow = db_fetch(
            'SELECT COUNT(*) AS locations, COALESCE(SUM(max_capacity), 0) AS capacity
             FROM safe_locations'
        ) ?? [];

        $occupancy = 0;
        if (dashboard_table_exists('safe_location_occupancy')) {
            $occupancyRow = db_fetch(
                'SELECT COALESCE(SUM(toddlers + children + adults + elderly + pregnant_women), 0) AS occupancy
                 FROM safe_location_occupancy'
            ) ?? [];
            $occupancy = (int) ($occupancyRow['occupancy'] ?? 0);
        }

        $capacity = (int) ($totalsRow['capacity'] ?? 0);
        $utilization = $capacity > 0 ? ($occupancy * 100 / $capacity) : 0;

        $analytics['shelters']['totals'] = [
            'locations' => (int) ($totalsRow['locations'] ?? 0),
            'capacity' => $capacity,
            'occupancy' => $occupancy,
            'utilization_pct' => round($utilization, 1),
        ];

        $analytics['cards']['shelter_utilization_pct'] = round($utilization, 1);

        if (dashboard_table_exists('safe_location_occupancy')) {
            $locationRows = db_fetch_all(
                'SELECT sl.location_name,
                        sl.max_capacity,
                        COALESCE(o.toddlers + o.children + o.adults + o.elderly + o.pregnant_women, 0) AS occupancy
                 FROM safe_locations sl
                 LEFT JOIN safe_location_occupancy o ON o.location_id = sl.location_id
                 ORDER BY sl.location_name ASC'
            );

            $analytics['shelters']['locations'] = array_map(
                static function (array $row): array {
                    $capacityLocal = max(0, (int) ($row['max_capacity'] ?? 0));
                    $occupancyLocal = max(0, (int) ($row['occupancy'] ?? 0));
                    $pct = $capacityLocal > 0 ? ($occupancyLocal * 100 / $capacityLocal) : 0;

                    $status = 'green';
                    if ($pct >= 95) {
                        $status = 'red';
                    } elseif ($pct >= 75) {
                        $status = 'yellow';
                    }

                    return [
                        'label' => (string) ($row['location_name'] ?? '-'),
                        'capacity' => $capacityLocal,
                        'occupancy' => $occupancyLocal,
                        'value' => round($pct, 1),
                        'status' => $status,
                    ];
                },
                $locationRows
            );
        }
    }

    if (dashboard_table_exists('donation_request_requirements')) {
        $requirementStatusRows = db_fetch_all(
            'SELECT fulfillment_status AS label, COUNT(*) AS value
             FROM donation_request_requirements
             GROUP BY fulfillment_status'
        );

        $analytics['requirements']['status'] = dashboard_rows_with_expected_labels(
            $requirementStatusRows,
            ['Open', 'Reserved', 'Fulfilled']
        );

        $unfulfilled = 0;
        foreach ($analytics['requirements']['status'] as $statusRow) {
            $label = (string) ($statusRow['label'] ?? '');
            if ($label === 'Open' || $label === 'Reserved') {
                $unfulfilled += (int) ($statusRow['value'] ?? 0);
            }
        }
        $analytics['requirements']['unfulfilled'] = $unfulfilled;

        if (dashboard_table_exists('safe_locations')) {
            $analytics['requirements']['districts'] = db_fetch_all(
                "SELECT sl.district AS label, COUNT(*) AS value
                 FROM donation_request_requirements rr
                 INNER JOIN safe_locations sl ON sl.location_id = rr.location_id
                 WHERE COALESCE(NULLIF(sl.district, ''), '') <> ''
                 GROUP BY sl.district
                 ORDER BY value DESC, sl.district ASC
                 LIMIT 8"
            );
        }

        if (dashboard_table_exists('donation_request_requirement_items')) {
            $analytics['requirements']['categories'] = db_fetch_all(
                "SELECT item_category AS label,
                        COALESCE(SUM(quantity), 0) AS value
                 FROM donation_request_requirement_items
                 GROUP BY item_category
                 ORDER BY FIELD(item_category, 'Medicine', 'Food', 'Shelter')"
            );
        }
    }

    if (dashboard_table_exists('users')) {
        $analytics['users']['roles'] = db_fetch_all(
            "SELECT role AS label, COUNT(*) AS value
             FROM users
             GROUP BY role
             ORDER BY FIELD(role, 'dmc', 'grama_niladhari', 'ngo', 'volunteer', 'general')"
        );

        $activity = db_fetch(
            'SELECT
                SUM(CASE WHEN active = 1 THEN 1 ELSE 0 END) AS active_count,
                SUM(CASE WHEN active = 0 THEN 1 ELSE 0 END) AS inactive_count
             FROM users'
        ) ?? [];

        $analytics['users']['activity'] = [
            ['label' => 'Active', 'value' => (int) ($activity['active_count'] ?? 0)],
            ['label' => 'Inactive', 'value' => (int) ($activity['inactive_count'] ?? 0)],
        ];
    }

    if (function_exists('forecast_sms_alert_ensure_table')) {
        forecast_sms_alert_ensure_table();
    }
    if (function_exists('sms_alert_forecast_ensure_delivery_table')) {
        sms_alert_forecast_ensure_delivery_table();
    }

    if (dashboard_table_exists('forecast_sms_alert_subscription')) {
        $activeSubRow = db_fetch(
            'SELECT COUNT(*) AS value
             FROM forecast_sms_alert_subscription
             WHERE sms_alert = 1'
        );

        $analytics['cards']['active_sms_subscribers'] = (int) ($activeSubRow['value'] ?? 0);
    }

    if (dashboard_table_exists('forecast_sms_alert_delivery_log')) {
        $todayRow = db_fetch(
            "SELECT COUNT(*) AS value
             FROM forecast_sms_alert_delivery_log
             WHERE delivery_status = 'sent'
               AND DATE(created_at) = CURDATE()"
        );
        $analytics['sms']['sent_today'] = (int) ($todayRow['value'] ?? 0);

        $weekRow = db_fetch(
            "SELECT COUNT(*) AS value
             FROM forecast_sms_alert_delivery_log
             WHERE delivery_status = 'sent'
               AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)"
        );
        $analytics['sms']['sent_this_week'] = (int) ($weekRow['value'] ?? 0);

        $monthRow = db_fetch(
            "SELECT COUNT(*) AS value
             FROM forecast_sms_alert_delivery_log
             WHERE delivery_status = 'sent'
               AND DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')"
        );
        $analytics['sms']['sent_this_month'] = (int) ($monthRow['value'] ?? 0);

        $severityRows = db_fetch_all(
            "SELECT forecast_level AS label, COUNT(*) AS value
             FROM forecast_sms_alert_delivery_log
             WHERE delivery_status = 'sent'
             GROUP BY forecast_level"
        );
        $analytics['sms']['severity'] = dashboard_rows_with_expected_labels(
            $severityRows,
            ['alert', 'minor', 'major']
        );

        $analytics['sms']['stations'] = db_fetch_all(
            "SELECT station_key AS label, COUNT(*) AS value
             FROM forecast_sms_alert_delivery_log
             WHERE delivery_status = 'sent'
             GROUP BY station_key
             ORDER BY value DESC, station_key ASC
             LIMIT 8"
        );

        $smsMonthly = db_fetch_all(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month_key,
                    COUNT(*) AS value
             FROM forecast_sms_alert_delivery_log
             WHERE delivery_status = 'sent'
               AND created_at >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
             GROUP BY month_key
             ORDER BY month_key ASC"
        );
        $analytics['sms']['monthly'] = dashboard_month_series($smsMonthly, 6);
    }

    return $analytics;
}

function dashboard_general_snapshot(int $userId, array $profile = []): array
{
    $snapshot = [
        'my_reports' => 0,
        'my_donations_total' => 0,
        'my_donations_pending' => 0,
        'my_donations_received' => 0,
        'nearest_shelter_name' => '-',
        'nearest_shelter_available' => 0,
    ];

    if ($userId <= 0) {
        return $snapshot;
    }

    if (dashboard_table_exists('disaster_reports')) {
        $row = db_fetch('SELECT COUNT(*) AS value FROM disaster_reports WHERE user_id = ?', [$userId]);
        $snapshot['my_reports'] = (int) ($row['value'] ?? 0);
    }

    if (dashboard_table_exists('donations')) {
        $row = db_fetch(
            "SELECT COUNT(*) AS total,
                    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending,
                    SUM(CASE WHEN status = 'Received' THEN 1 ELSE 0 END) AS received
             FROM donations
             WHERE (submitted_by_user_id = ? AND submitted_by_role = 'general')
                OR user_id = ?",
            [$userId, $userId]
        ) ?? [];

        $snapshot['my_donations_total'] = (int) ($row['total'] ?? 0);
        $snapshot['my_donations_pending'] = (int) ($row['pending'] ?? 0);
        $snapshot['my_donations_received'] = (int) ($row['received'] ?? 0);
    }

    if (dashboard_table_exists('safe_locations')) {
        $district = trim((string) ($profile['district'] ?? ''));
        $gnDivision = trim((string) ($profile['gn_division'] ?? ''));

        $queries = [];
        if ($district !== '' && $gnDivision !== '') {
            $queries[] = ['sql' => 'sl.district = ? AND sl.gn_division = ?', 'params' => [$district, $gnDivision]];
        }
        if ($gnDivision !== '') {
            $queries[] = ['sql' => 'sl.gn_division = ?', 'params' => [$gnDivision]];
        }
        if ($district !== '') {
            $queries[] = ['sql' => 'sl.district = ?', 'params' => [$district]];
        }
        $queries[] = ['sql' => '1 = 1', 'params' => []];

        foreach ($queries as $query) {
            $row = db_fetch(
                'SELECT sl.location_name,
                        GREATEST(sl.max_capacity - COALESCE(o.toddlers + o.children + o.adults + o.elderly + o.pregnant_women, 0), 0) AS available_capacity
                 FROM safe_locations sl
                 LEFT JOIN safe_location_occupancy o ON o.location_id = sl.location_id
                 WHERE ' . $query['sql'] . '
                 ORDER BY available_capacity DESC, sl.max_capacity DESC, sl.location_name ASC
                 LIMIT 1',
                (array) ($query['params'] ?? [])
            );

            if ($row) {
                $snapshot['nearest_shelter_name'] = (string) ($row['location_name'] ?? '-');
                $snapshot['nearest_shelter_available'] = max(0, (int) ($row['available_capacity'] ?? 0));
                break;
            }
        }
    }

    return $snapshot;
}

function dashboard_volunteer_snapshot(int $userId): array
{
    $snapshot = [
        'active_tasks' => 0,
        'completed_tasks' => 0,
        'latest_task' => null,
    ];

    if ($userId <= 0 || !dashboard_table_exists('volunteer_task')) {
        return $snapshot;
    }

    $counts = db_fetch(
        "SELECT
            SUM(CASE WHEN status IN ('Assigned', 'Accepted', 'In Progress') THEN 1 ELSE 0 END) AS active_tasks,
            SUM(CASE WHEN status IN ('Completed', 'Verified') THEN 1 ELSE 0 END) AS completed_tasks
         FROM volunteer_task
         WHERE volunteer_id = ?",
        [$userId]
    ) ?? [];

    $snapshot['active_tasks'] = (int) ($counts['active_tasks'] ?? 0);
    $snapshot['completed_tasks'] = (int) ($counts['completed_tasks'] ?? 0);

    if (dashboard_table_exists('disaster_reports')) {
        $snapshot['latest_task'] = db_fetch(
            'SELECT vt.status,
                    vt.date_assigned,
                    dr.report_id,
                    dr.disaster_type,
                    dr.other_disaster_type,
                    dr.district,
                    dr.gn_division
             FROM volunteer_task vt
             LEFT JOIN disaster_reports dr ON dr.report_id = vt.disaster_id
             WHERE vt.volunteer_id = ?
             ORDER BY vt.date_assigned DESC, vt.id DESC
             LIMIT 1',
            [$userId]
        );
    } else {
        $snapshot['latest_task'] = db_fetch(
            'SELECT status, date_assigned
             FROM volunteer_task
             WHERE volunteer_id = ?
             ORDER BY date_assigned DESC, id DESC
             LIMIT 1',
            [$userId]
        );
    }

    return $snapshot;
}

function dashboard_ngo_snapshot(int $userId): array
{
    $snapshot = [
        'collection_points' => 0,
        'low_stock_items' => 0,
        'pending_donations' => 0,
        'open_requirements' => 0,
    ];

    if ($userId <= 0) {
        return $snapshot;
    }

    if (dashboard_table_exists('collection_points')) {
        $row = db_fetch('SELECT COUNT(*) AS value FROM collection_points WHERE ngo_id = ?', [$userId]);
        $snapshot['collection_points'] = (int) ($row['value'] ?? 0);
    }

    if (dashboard_table_exists('inventory')) {
        $row = db_fetch('SELECT COUNT(*) AS value FROM inventory WHERE ngo_id = ? AND quantity < 20', [$userId]);
        $snapshot['low_stock_items'] = (int) ($row['value'] ?? 0);
    }

    if (dashboard_table_exists('donations') && dashboard_table_exists('collection_points')) {
        $row = db_fetch(
            "SELECT COUNT(*) AS value
             FROM donations d
             INNER JOIN collection_points cp ON cp.collection_point_id = d.collection_point_id
             WHERE cp.ngo_id = ?
               AND d.status = 'Pending'",
            [$userId]
        );
        $snapshot['pending_donations'] = (int) ($row['value'] ?? 0);
    }

    if (dashboard_table_exists('donation_request_requirements')) {
        $row = db_fetch(
            "SELECT COUNT(*) AS value
             FROM donation_request_requirements
             WHERE fulfillment_status = 'Open'"
        );
        $snapshot['open_requirements'] = (int) ($row['value'] ?? 0);
    }

    return $snapshot;
}

function dashboard_gn_snapshot(int $userId, array $profile = []): array
{
    $snapshot = [
        'occupancy_total' => 0,
        'capacity_total' => 0,
        'open_requests' => 0,
        'active_reports' => 0,
    ];

    if ($userId <= 0) {
        return $snapshot;
    }

    if (dashboard_table_exists('safe_locations')) {
        $row = db_fetch(
            'SELECT COALESCE(SUM(sl.max_capacity), 0) AS capacity_total,
                    COALESCE(SUM(o.toddlers + o.children + o.adults + o.elderly + o.pregnant_women), 0) AS occupancy_total
             FROM safe_locations sl
             LEFT JOIN safe_location_occupancy o ON o.location_id = sl.location_id
             WHERE sl.assigned_gn_user_id = ?',
            [$userId]
        ) ?? [];

        $snapshot['capacity_total'] = (int) ($row['capacity_total'] ?? 0);
        $snapshot['occupancy_total'] = (int) ($row['occupancy_total'] ?? 0);
    }

    if (dashboard_table_exists('donation_request_requirements')) {
        $row = db_fetch(
            "SELECT COUNT(*) AS value
             FROM donation_request_requirements
             WHERE gn_user_id = ?
               AND fulfillment_status IN ('Open', 'Reserved')",
            [$userId]
        );
        $snapshot['open_requests'] = (int) ($row['value'] ?? 0);
    }

    if (dashboard_table_exists('disaster_reports')) {
        $gnDivision = trim((string) ($profile['gn_division'] ?? ''));
        if ($gnDivision !== '') {
            $row = db_fetch(
                "SELECT COUNT(*) AS value
                 FROM disaster_reports
                 WHERE status = 'Approved'
                   AND gn_division = ?",
                [$gnDivision]
            );
            $snapshot['active_reports'] = (int) ($row['value'] ?? 0);
        }
    }

    return $snapshot;
}

function dashboard_report_available_districts(): array
{
    $queries = [];

    if (dashboard_table_exists('safe_locations')) {
        $queries[] = "SELECT DISTINCT district AS district
                      FROM safe_locations
                      WHERE COALESCE(NULLIF(district, ''), '') <> ''";
    }

    if (dashboard_table_exists('disaster_reports')) {
        $queries[] = "SELECT DISTINCT district AS district
                      FROM disaster_reports
                      WHERE COALESCE(NULLIF(district, ''), '') <> ''";
    }

    if (dashboard_table_exists('collection_points')) {
        $queries[] = "SELECT DISTINCT district AS district
                      FROM collection_points
                      WHERE COALESCE(NULLIF(district, ''), '') <> ''";
    }

    if (empty($queries)) {
        return [];
    }

    $districtMap = [];
    foreach ($queries as $sql) {
        $rows = db_fetch_all($sql);
        foreach ($rows as $row) {
            $district = trim((string) ($row['district'] ?? ''));
            if ($district === '') {
                continue;
            }

            $key = dashboard_report_district_key($district);
            if (!isset($districtMap[$key])) {
                $districtMap[$key] = $district;
            }
        }
    }

    $districts = array_values($districtMap);
    usort($districts, static fn(string $a, string $b): int => strcasecmp($a, $b));

    return $districts;
}

function dashboard_report_district_key(string $district): string
{
    $value = trim($district);
    $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
    $value = preg_replace('/\s+district$/iu', '', $value) ?? $value;

    return strtolower(trim($value));
}

function dashboard_report_district_aliases(string $district): array
{
    $value = trim($district);
    if ($value === '') {
        return [];
    }

    $aliases = [$value];
    $base = trim((string) (preg_replace('/\s+district$/iu', '', $value) ?? $value));

    if ($base !== '') {
        $aliases[] = $base;
        $aliases[] = $base . ' District';
    }

    $map = [];
    foreach ($aliases as $alias) {
        $normalized = strtolower(trim($alias));
        if ($normalized === '' || isset($map[$normalized])) {
            continue;
        }
        $map[$normalized] = $normalized;
    }

    return array_values($map);
}

function dashboard_report_district_where(string $column, string $district, array &$params): string
{
    $aliases = dashboard_report_district_aliases($district);
    if (empty($aliases)) {
        return '1 = 0';
    }

    $placeholders = implode(', ', array_fill(0, count($aliases), '?'));
    foreach ($aliases as $alias) {
        $params[] = $alias;
    }

    return 'LOWER(TRIM(' . $column . ')) IN (' . $placeholders . ')';
}

function dashboard_report_parse_period(?string $fromInput, ?string $toInput): array
{
    $from = dashboard_report_parse_date($fromInput);
    $to = dashboard_report_parse_date($toInput);

    if ($from !== '' && $to !== '' && strcmp($from, $to) > 0) {
        [$from, $to] = [$to, $from];
    }

    return [
        'from' => $from,
        'to' => $to,
    ];
}

function dashboard_report_period_label(array $period): string
{
    $from = (string) ($period['from'] ?? '');
    $to = (string) ($period['to'] ?? '');

    if ($from !== '' && $to !== '') {
        return $from . ' to ' . $to;
    }

    if ($from !== '') {
        return 'From ' . $from;
    }

    if ($to !== '') {
        return 'Up to ' . $to;
    }

    return 'All available records';
}

function dashboard_report_generate_district_pdf(string $district, array $period): array
{
    $data = dashboard_report_district_data($district, $period);
    $lines = dashboard_report_district_lines($data);

    $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($district));
    $slug = trim((string) $slug, '-');
    if ($slug === '') {
        $slug = 'district';
    }

    return [
        'filename' => 'district-operational-report-' . $slug . '-' . date('Ymd_His') . '.pdf',
        'content' => dashboard_pdf_render_lines($lines),
    ];
}

function dashboard_report_generate_full_pdf(array $period): array
{
    $data = dashboard_report_full_data($period);
    $lines = dashboard_report_full_lines($data);

    return [
        'filename' => 'operational-report-full-' . date('Ymd_His') . '.pdf',
        'content' => dashboard_pdf_render_lines($lines),
    ];
}

function dashboard_report_parse_date(?string $raw): string
{
    $value = trim((string) $raw);
    if ($value === '') {
        return '';
    }

    $dt = DateTimeImmutable::createFromFormat('Y-m-d', $value);
    if (!$dt || $dt->format('Y-m-d') !== $value) {
        return '';
    }

    return $value;
}

function dashboard_report_date_where(string $column, array $period, array &$params): string
{
    $clauses = [];

    $from = (string) ($period['from'] ?? '');
    $to = (string) ($period['to'] ?? '');

    if ($from !== '') {
        $clauses[] = 'DATE(' . $column . ') >= ?';
        $params[] = $from;
    }

    if ($to !== '') {
        $clauses[] = 'DATE(' . $column . ') <= ?';
        $params[] = $to;
    }

    if (empty($clauses)) {
        return '1 = 1';
    }

    return implode(' AND ', $clauses);
}

function dashboard_report_district_data(string $district, array $period): array
{
    $district = trim($district);
    $summary = [
        'total_incidents' => 0,
        'pending_incidents' => 0,
        'approved_incidents' => 0,
        'rejected_incidents' => 0,
        'task_total' => 0,
        'task_active' => 0,
        'task_completed' => 0,
        'task_declined' => 0,
        'safe_locations' => 0,
        'shelter_capacity' => 0,
        'shelter_occupancy' => 0,
        'shelter_utilization_pct' => 0.0,
        'requirements_total' => 0,
        'requirements_open' => 0,
        'requirements_reserved' => 0,
        'requirements_fulfilled' => 0,
    ];

    $gnBreakdown = [];
    $safeLocations = [];
    $incidents = [];
    $requirements = [];
    $requirementCategories = [];

    if (dashboard_table_exists('disaster_reports')) {
        $params = [];
        $districtWhere = dashboard_report_district_where('district', $district, $params);
        $dateWhere = dashboard_report_date_where('submitted_at', $period, $params);
        $row = db_fetch(
            "SELECT COUNT(*) AS total_incidents,
                    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending_incidents,
                    SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) AS approved_incidents,
                    SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) AS rejected_incidents
             FROM disaster_reports
             WHERE {$districtWhere}
               AND {$dateWhere}",
            $params
        ) ?? [];

        $summary['total_incidents'] = (int) ($row['total_incidents'] ?? 0);
        $summary['pending_incidents'] = (int) ($row['pending_incidents'] ?? 0);
        $summary['approved_incidents'] = (int) ($row['approved_incidents'] ?? 0);
        $summary['rejected_incidents'] = (int) ($row['rejected_incidents'] ?? 0);

        $params = [];
        $districtWhere = dashboard_report_district_where('district', $district, $params);
        $dateWhere = dashboard_report_date_where('submitted_at', $period, $params);
        $incidents = db_fetch_all(
            "SELECT report_id,
                    disaster_type,
                    other_disaster_type,
                    status,
                    gn_division,
                    submitted_at
             FROM disaster_reports
                         WHERE {$districtWhere}
               AND {$dateWhere}
             ORDER BY submitted_at DESC, report_id DESC
             LIMIT 40",
            $params
        );
    }

    if (dashboard_table_exists('volunteer_task') && dashboard_table_exists('disaster_reports')) {
        $params = [];
        $districtWhere = dashboard_report_district_where('dr.district', $district, $params);
        $dateWhere = dashboard_report_date_where('dr.submitted_at', $period, $params);
        $row = db_fetch(
            "SELECT COUNT(*) AS task_total,
                    SUM(CASE WHEN vt.status IN ('Assigned', 'Accepted', 'In Progress', 'Pending') THEN 1 ELSE 0 END) AS task_active,
                    SUM(CASE WHEN vt.status IN ('Completed', 'Verified') THEN 1 ELSE 0 END) AS task_completed,
                    SUM(CASE WHEN vt.status = 'Declined' THEN 1 ELSE 0 END) AS task_declined
             FROM volunteer_task vt
             INNER JOIN disaster_reports dr ON dr.report_id = vt.disaster_id
             WHERE {$districtWhere}
               AND {$dateWhere}",
            $params
        ) ?? [];

        $summary['task_total'] = (int) ($row['task_total'] ?? 0);
        $summary['task_active'] = (int) ($row['task_active'] ?? 0);
        $summary['task_completed'] = (int) ($row['task_completed'] ?? 0);
        $summary['task_declined'] = (int) ($row['task_declined'] ?? 0);
    }

    if (dashboard_table_exists('safe_locations')) {
        $params = [];
        $districtWhere = dashboard_report_district_where('sl.district', $district, $params);
        $totalsRow = db_fetch(
            'SELECT COUNT(*) AS safe_locations,
                    COALESCE(SUM(sl.max_capacity), 0) AS shelter_capacity,
                    COALESCE(SUM(o.toddlers + o.children + o.adults + o.elderly + o.pregnant_women), 0) AS shelter_occupancy
             FROM safe_locations sl
             LEFT JOIN safe_location_occupancy o ON o.location_id = sl.location_id
             WHERE ' . $districtWhere,
            $params
        ) ?? [];

        $summary['safe_locations'] = (int) ($totalsRow['safe_locations'] ?? 0);
        $summary['shelter_capacity'] = (int) ($totalsRow['shelter_capacity'] ?? 0);
        $summary['shelter_occupancy'] = (int) ($totalsRow['shelter_occupancy'] ?? 0);
        if ($summary['shelter_capacity'] > 0) {
            $summary['shelter_utilization_pct'] = round(
                ($summary['shelter_occupancy'] * 100) / $summary['shelter_capacity'],
                1
            );
        }

        $params = [];
        $districtWhere = dashboard_report_district_where('sl.district', $district, $params);
        $safeLocations = db_fetch_all(
            'SELECT sl.location_name,
                    COALESCE(NULLIF(g.name, \'\'), u.username, \'Unassigned\') AS gn_name,
                    COALESCE(NULLIF(sl.gn_division, \'\'), \'-\') AS gn_division,
                    sl.max_capacity AS capacity,
                    COALESCE(o.toddlers + o.children + o.adults + o.elderly + o.pregnant_women, 0) AS occupancy
             FROM safe_locations sl
             LEFT JOIN safe_location_occupancy o ON o.location_id = sl.location_id
             LEFT JOIN grama_niladhari g ON g.user_id = sl.assigned_gn_user_id
             LEFT JOIN users u ON u.user_id = sl.assigned_gn_user_id
             WHERE ' . $districtWhere . '
             ORDER BY sl.location_name ASC',
            $params
        );

        $params = [];
        $districtWhere = dashboard_report_district_where('sl.district', $district, $params);
        $gnBreakdown = db_fetch_all(
            'SELECT sl.assigned_gn_user_id,
                    COALESCE(NULLIF(g.name, \'\'), u.username, \'Unassigned\') AS gn_name,
                    COALESCE(NULLIF(g.gn_division, \'\'), NULLIF(sl.gn_division, \'\'), \'-\') AS gn_division,
                    COUNT(DISTINCT sl.location_id) AS safe_location_count,
                    COALESCE(SUM(sl.max_capacity), 0) AS shelter_capacity,
                    COALESCE(SUM(o.toddlers + o.children + o.adults + o.elderly + o.pregnant_women), 0) AS shelter_occupancy
             FROM safe_locations sl
             LEFT JOIN safe_location_occupancy o ON o.location_id = sl.location_id
             LEFT JOIN grama_niladhari g ON g.user_id = sl.assigned_gn_user_id
             LEFT JOIN users u ON u.user_id = sl.assigned_gn_user_id
             WHERE ' . $districtWhere . '
             GROUP BY sl.assigned_gn_user_id, gn_name, gn_division
             ORDER BY gn_name ASC',
            $params
        );

        foreach ($gnBreakdown as &$row) {
            $cap = max(0, (int) ($row['shelter_capacity'] ?? 0));
            $occ = max(0, (int) ($row['shelter_occupancy'] ?? 0));
            $row['utilization_pct'] = $cap > 0 ? round(($occ * 100) / $cap, 1) : 0.0;
            $row['incident_count'] = 0;
        }
        unset($row);
    }

    if (dashboard_table_exists('disaster_reports') && !empty($gnBreakdown)) {
                $params = [];
                $districtWhere = dashboard_report_district_where('district', $district, $params);
        $dateWhere = dashboard_report_date_where('submitted_at', $period, $params);
        $gnIncidents = db_fetch_all(
            "SELECT gn_division, COUNT(*) AS value
             FROM disaster_reports
                         WHERE {$districtWhere}
               AND {$dateWhere}
               AND COALESCE(NULLIF(gn_division, ''), '') <> ''
             GROUP BY gn_division",
            $params
        );

        $incidentMap = [];
        foreach ($gnIncidents as $row) {
            $key = trim((string) ($row['gn_division'] ?? ''));
            if ($key === '') {
                continue;
            }
            $incidentMap[$key] = (int) ($row['value'] ?? 0);
        }

        foreach ($gnBreakdown as &$row) {
            $division = trim((string) ($row['gn_division'] ?? ''));
            $row['incident_count'] = (int) ($incidentMap[$division] ?? 0);
        }
        unset($row);
    }

    if (dashboard_table_exists('donation_request_requirements') && dashboard_table_exists('safe_locations')) {
        $params = [];
        $districtWhere = dashboard_report_district_where('sl.district', $district, $params);
        $dateWhere = dashboard_report_date_where('rr.created_at', $period, $params);
        $row = db_fetch(
            "SELECT COUNT(*) AS requirements_total,
                    SUM(CASE WHEN rr.fulfillment_status = 'Open' THEN 1 ELSE 0 END) AS requirements_open,
                    SUM(CASE WHEN rr.fulfillment_status = 'Reserved' THEN 1 ELSE 0 END) AS requirements_reserved,
                    SUM(CASE WHEN rr.fulfillment_status = 'Fulfilled' THEN 1 ELSE 0 END) AS requirements_fulfilled
             FROM donation_request_requirements rr
             INNER JOIN safe_locations sl ON sl.location_id = rr.location_id
                      WHERE {$districtWhere}
               AND {$dateWhere}",
            $params
        ) ?? [];

        $summary['requirements_total'] = (int) ($row['requirements_total'] ?? 0);
        $summary['requirements_open'] = (int) ($row['requirements_open'] ?? 0);
        $summary['requirements_reserved'] = (int) ($row['requirements_reserved'] ?? 0);
        $summary['requirements_fulfilled'] = (int) ($row['requirements_fulfilled'] ?? 0);

        $params = [];
        $districtWhere = dashboard_report_district_where('sl.district', $district, $params);
        $dateWhere = dashboard_report_date_where('rr.created_at', $period, $params);
        $requirements = db_fetch_all(
            "SELECT rr.requirement_id,
                    rr.relief_center_name,
                    rr.fulfillment_status,
                    rr.days_count,
                    rr.created_at,
                    COALESCE(SUM(ri.quantity), 0) AS requested_qty
             FROM donation_request_requirements rr
             INNER JOIN safe_locations sl ON sl.location_id = rr.location_id
             LEFT JOIN donation_request_requirement_items ri ON ri.requirement_id = rr.requirement_id
                         WHERE {$districtWhere}
               AND {$dateWhere}
             GROUP BY rr.requirement_id, rr.relief_center_name, rr.fulfillment_status, rr.days_count, rr.created_at
             ORDER BY rr.created_at DESC
             LIMIT 30",
            $params
        );

        if (dashboard_table_exists('donation_request_requirement_items')) {
                        $params = [];
                        $districtWhere = dashboard_report_district_where('sl.district', $district, $params);
            $dateWhere = dashboard_report_date_where('rr.created_at', $period, $params);
            $requirementCategories = db_fetch_all(
                "SELECT ri.item_category AS label, COALESCE(SUM(ri.quantity), 0) AS value
                 FROM donation_request_requirement_items ri
                 INNER JOIN donation_request_requirements rr ON rr.requirement_id = ri.requirement_id
                 INNER JOIN safe_locations sl ON sl.location_id = rr.location_id
                                 WHERE {$districtWhere}
                   AND {$dateWhere}
                 GROUP BY ri.item_category
                 ORDER BY value DESC, ri.item_category ASC",
                $params
            );
        }
    }

    return [
        'district' => $district,
        'generated_at' => date('Y-m-d H:i:s'),
        'period_label' => dashboard_report_period_label($period),
        'summary' => $summary,
        'gn_breakdown' => $gnBreakdown,
        'safe_locations' => $safeLocations,
        'incidents' => $incidents,
        'requirements' => $requirements,
        'requirement_categories' => $requirementCategories,
    ];
}

function dashboard_report_district_lines(array $data): array
{
    $summary = (array) ($data['summary'] ?? []);
    $gnBreakdown = (array) ($data['gn_breakdown'] ?? []);
    $safeLocations = (array) ($data['safe_locations'] ?? []);
    $incidents = (array) ($data['incidents'] ?? []);
    $requirements = (array) ($data['requirements'] ?? []);
    $requirementCategories = (array) ($data['requirement_categories'] ?? []);

    $lines = [];
    $lines[] = 'ResQnet District Operational Report';
    $lines[] = 'District: ' . (string) ($data['district'] ?? '-');
    $lines[] = 'Generated at: ' . (string) ($data['generated_at'] ?? '-');
    $lines[] = 'Reporting period: ' . (string) ($data['period_label'] ?? '-');
    $lines[] = '';

    $lines[] = '1. Executive Summary';
    $lines[] = '- Total incidents: ' . number_format((int) ($summary['total_incidents'] ?? 0));
    $lines[] = '- Incident status: Pending ' . number_format((int) ($summary['pending_incidents'] ?? 0))
        . ', Approved ' . number_format((int) ($summary['approved_incidents'] ?? 0))
        . ', Rejected ' . number_format((int) ($summary['rejected_incidents'] ?? 0));
    $lines[] = '- Volunteer assignments: Total ' . number_format((int) ($summary['task_total'] ?? 0))
        . ', Active ' . number_format((int) ($summary['task_active'] ?? 0))
        . ', Completed/Verified ' . number_format((int) ($summary['task_completed'] ?? 0))
        . ', Declined ' . number_format((int) ($summary['task_declined'] ?? 0));
    $lines[] = '- Safe locations: ' . number_format((int) ($summary['safe_locations'] ?? 0))
        . ' (Occupancy ' . number_format((int) ($summary['shelter_occupancy'] ?? 0))
        . ' / Capacity ' . number_format((int) ($summary['shelter_capacity'] ?? 0))
        . ', Utilization ' . number_format((float) ($summary['shelter_utilization_pct'] ?? 0.0), 1) . '%)';
    $lines[] = '- Donation requirements: Total ' . number_format((int) ($summary['requirements_total'] ?? 0))
        . ', Open ' . number_format((int) ($summary['requirements_open'] ?? 0))
        . ', Reserved ' . number_format((int) ($summary['requirements_reserved'] ?? 0))
        . ', Fulfilled ' . number_format((int) ($summary['requirements_fulfilled'] ?? 0));
    $lines[] = '';

    $lines[] = '2. GN Breakdown';
    if (empty($gnBreakdown)) {
        $lines[] = 'No GN linked safe location data was found for this district.';
    } else {
        foreach ($gnBreakdown as $row) {
            $lines[] = '- ' . (string) ($row['gn_name'] ?? 'Unassigned')
                . ' | Division: ' . (string) ($row['gn_division'] ?? '-')
                . ' | Safe locations: ' . number_format((int) ($row['safe_location_count'] ?? 0))
                . ' | Occupancy/Capacity: ' . number_format((int) ($row['shelter_occupancy'] ?? 0))
                . '/' . number_format((int) ($row['shelter_capacity'] ?? 0))
                . ' | Utilization: ' . number_format((float) ($row['utilization_pct'] ?? 0.0), 1) . '%'
                . ' | Reported incidents: ' . number_format((int) ($row['incident_count'] ?? 0));
        }
    }
    $lines[] = '';

    $lines[] = '3. Safe Location Details';
    if (empty($safeLocations)) {
        $lines[] = 'No safe locations are registered for this district.';
    } else {
        foreach ($safeLocations as $location) {
            $capacity = max(0, (int) ($location['capacity'] ?? 0));
            $occupancy = max(0, (int) ($location['occupancy'] ?? 0));
            $utilization = $capacity > 0 ? round(($occupancy * 100) / $capacity, 1) : 0.0;

            $lines[] = '- ' . (string) ($location['location_name'] ?? '-')
                . ' | GN: ' . (string) ($location['gn_name'] ?? 'Unassigned')
                . ' | Division: ' . (string) ($location['gn_division'] ?? '-')
                . ' | Occupancy/Capacity: ' . number_format($occupancy)
                . '/' . number_format($capacity)
                . ' (' . number_format($utilization, 1) . '%)';
        }
    }
    $lines[] = '';

    $lines[] = '4. Reported Incidents';
    if (empty($incidents)) {
        $lines[] = 'No incidents found for the selected district and period.';
    } else {
        foreach ($incidents as $incident) {
            $type = (string) ($incident['disaster_type'] ?? '-');
            if ($type === 'Other') {
                $other = trim((string) ($incident['other_disaster_type'] ?? ''));
                if ($other !== '') {
                    $type = 'Other: ' . $other;
                }
            }

            $submittedAt = (string) ($incident['submitted_at'] ?? '-');
            $lines[] = '- Report #' . number_format((int) ($incident['report_id'] ?? 0))
                . ' | ' . $type
                . ' | Status: ' . (string) ($incident['status'] ?? '-')
                . ' | GN Division: ' . (string) ($incident['gn_division'] ?? '-')
                . ' | Submitted: ' . $submittedAt;
        }
    }
    $lines[] = '';

    $lines[] = '5. Donation Requirements';
    if (!empty($requirementCategories)) {
        $lines[] = 'Requirement quantities by category:';
        foreach ($requirementCategories as $category) {
            $lines[] = '- ' . (string) ($category['label'] ?? '-') . ': '
                . number_format((float) ($category['value'] ?? 0), 2);
        }
    }

    if (empty($requirements)) {
        $lines[] = 'No district requirement records found for the selected period.';
    } else {
        foreach ($requirements as $req) {
            $lines[] = '- Requirement #' . number_format((int) ($req['requirement_id'] ?? 0))
                . ' | Relief center: ' . (string) ($req['relief_center_name'] ?? '-')
                . ' | Status: ' . (string) ($req['fulfillment_status'] ?? '-')
                . ' | Requested qty: ' . number_format((float) ($req['requested_qty'] ?? 0), 2)
                . ' | Days covered: ' . number_format((int) ($req['days_count'] ?? 0))
                . ' | Created: ' . (string) ($req['created_at'] ?? '-');
        }
    }

    return $lines;
}

function dashboard_report_full_data(array $period): array
{
    $pendingCount = 0;
    if (function_exists('auth_pending_approval_users')) {
        $pendingCount = count((array) auth_pending_approval_users());
    }

    $analytics = dashboard_dmc_analytics($pendingCount);

    $totals = [
        'reports_total' => 0,
        'reports_pending' => 0,
        'reports_approved' => 0,
        'reports_rejected' => 0,
        'tasks_total' => 0,
        'tasks_active' => 0,
        'tasks_completed' => 0,
        'tasks_declined' => 0,
        'donations_total' => 0,
        'donations_pending' => 0,
        'donations_received' => 0,
        'donations_cancelled' => 0,
        'donations_delivered' => 0,
        'requirements_total' => 0,
        'requirements_open' => 0,
        'requirements_reserved' => 0,
        'requirements_fulfilled' => 0,
        'sms_sent_total' => 0,
    ];

    if (dashboard_table_exists('disaster_reports')) {
        $params = [];
        $dateWhere = dashboard_report_date_where('submitted_at', $period, $params);
        $row = db_fetch(
            "SELECT COUNT(*) AS reports_total,
                    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS reports_pending,
                    SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) AS reports_approved,
                    SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) AS reports_rejected
             FROM disaster_reports
             WHERE {$dateWhere}",
            $params
        ) ?? [];

        $totals['reports_total'] = (int) ($row['reports_total'] ?? 0);
        $totals['reports_pending'] = (int) ($row['reports_pending'] ?? 0);
        $totals['reports_approved'] = (int) ($row['reports_approved'] ?? 0);
        $totals['reports_rejected'] = (int) ($row['reports_rejected'] ?? 0);
    }

    if (dashboard_table_exists('volunteer_task')) {
        $params = [];
        $dateWhere = dashboard_report_date_where('date_assigned', $period, $params);
        $row = db_fetch(
            "SELECT COUNT(*) AS tasks_total,
                    SUM(CASE WHEN status IN ('Assigned', 'Accepted', 'In Progress', 'Pending') THEN 1 ELSE 0 END) AS tasks_active,
                    SUM(CASE WHEN status IN ('Completed', 'Verified') THEN 1 ELSE 0 END) AS tasks_completed,
                    SUM(CASE WHEN status = 'Declined' THEN 1 ELSE 0 END) AS tasks_declined
             FROM volunteer_task
             WHERE {$dateWhere}",
            $params
        ) ?? [];

        $totals['tasks_total'] = (int) ($row['tasks_total'] ?? 0);
        $totals['tasks_active'] = (int) ($row['tasks_active'] ?? 0);
        $totals['tasks_completed'] = (int) ($row['tasks_completed'] ?? 0);
        $totals['tasks_declined'] = (int) ($row['tasks_declined'] ?? 0);
    }

    if (dashboard_table_exists('donations')) {
        $params = [];
        $dateWhere = dashboard_report_date_where('submitted_at', $period, $params);
        $row = db_fetch(
            "SELECT COUNT(*) AS donations_total,
                    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS donations_pending,
                    SUM(CASE WHEN status = 'Received' THEN 1 ELSE 0 END) AS donations_received,
                    SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) AS donations_cancelled,
                    SUM(CASE WHEN status = 'Delivered' THEN 1 ELSE 0 END) AS donations_delivered
             FROM donations
             WHERE {$dateWhere}",
            $params
        ) ?? [];

        $totals['donations_total'] = (int) ($row['donations_total'] ?? 0);
        $totals['donations_pending'] = (int) ($row['donations_pending'] ?? 0);
        $totals['donations_received'] = (int) ($row['donations_received'] ?? 0);
        $totals['donations_cancelled'] = (int) ($row['donations_cancelled'] ?? 0);
        $totals['donations_delivered'] = (int) ($row['donations_delivered'] ?? 0);
    }

    if (dashboard_table_exists('donation_request_requirements')) {
        $params = [];
        $dateWhere = dashboard_report_date_where('created_at', $period, $params);
        $row = db_fetch(
            "SELECT COUNT(*) AS requirements_total,
                    SUM(CASE WHEN fulfillment_status = 'Open' THEN 1 ELSE 0 END) AS requirements_open,
                    SUM(CASE WHEN fulfillment_status = 'Reserved' THEN 1 ELSE 0 END) AS requirements_reserved,
                    SUM(CASE WHEN fulfillment_status = 'Fulfilled' THEN 1 ELSE 0 END) AS requirements_fulfilled
             FROM donation_request_requirements
             WHERE {$dateWhere}",
            $params
        ) ?? [];

        $totals['requirements_total'] = (int) ($row['requirements_total'] ?? 0);
        $totals['requirements_open'] = (int) ($row['requirements_open'] ?? 0);
        $totals['requirements_reserved'] = (int) ($row['requirements_reserved'] ?? 0);
        $totals['requirements_fulfilled'] = (int) ($row['requirements_fulfilled'] ?? 0);
    }

    if (dashboard_table_exists('forecast_sms_alert_delivery_log')) {
        $params = [];
        $dateWhere = dashboard_report_date_where('created_at', $period, $params);
        $row = db_fetch(
            "SELECT COUNT(*) AS sms_sent_total
             FROM forecast_sms_alert_delivery_log
             WHERE delivery_status = 'sent'
               AND {$dateWhere}",
            $params
        ) ?? [];

        $totals['sms_sent_total'] = (int) ($row['sms_sent_total'] ?? 0);
    }

    $accountByRole = [];
    if (dashboard_table_exists('users')) {
        $accountByRole = db_fetch_all(
            "SELECT role,
                    SUM(CASE WHEN active = 1 THEN 1 ELSE 0 END) AS active_count,
                    SUM(CASE WHEN active = 0 THEN 1 ELSE 0 END) AS inactive_count,
                    COUNT(*) AS total_count
             FROM users
             GROUP BY role
             ORDER BY FIELD(role, 'dmc', 'grama_niladhari', 'ngo', 'volunteer', 'general')"
        );
    }

    return [
        'generated_at' => date('Y-m-d H:i:s'),
        'period_label' => dashboard_report_period_label($period),
        'analytics' => $analytics,
        'totals' => $totals,
        'account_by_role' => $accountByRole,
    ];
}

function dashboard_report_full_lines(array $data): array
{
    $analytics = (array) ($data['analytics'] ?? []);
    $cards = (array) ($analytics['cards'] ?? []);
    $disasters = (array) ($analytics['disasters'] ?? []);
    $volunteers = (array) ($analytics['volunteers'] ?? []);
    $donations = (array) ($analytics['donations'] ?? []);
    $shelters = (array) ($analytics['shelters'] ?? []);
    $requirements = (array) ($analytics['requirements'] ?? []);
    $sms = (array) ($analytics['sms'] ?? []);
    $totals = (array) ($data['totals'] ?? []);
    $accountByRole = (array) ($data['account_by_role'] ?? []);

    $lines = [];
    $lines[] = 'ResQnet Full Operational Report';
    $lines[] = 'Generated at: ' . (string) ($data['generated_at'] ?? '-');
    $lines[] = 'Reporting period: ' . (string) ($data['period_label'] ?? '-');
    $lines[] = '';

    $lines[] = '1. Executive Summary';
    $lines[] = '- Pending approvals: ' . number_format((int) ($cards['pending_approvals'] ?? 0));
    $lines[] = '- Incidents (period): ' . number_format((int) ($totals['reports_total'] ?? 0));
    $lines[] = '- Active volunteer tasks (period): ' . number_format((int) ($totals['tasks_active'] ?? 0));
    $lines[] = '- Donations submitted (period): ' . number_format((int) ($totals['donations_total'] ?? 0));
    $lines[] = '- Unfulfilled requirements (snapshot): ' . number_format((int) ($requirements['unfulfilled'] ?? 0));
    $lines[] = '- Shelter utilization (snapshot): ' . number_format((float) ($cards['shelter_utilization_pct'] ?? 0.0), 1) . '%';
    $lines[] = '';

    $lines[] = '2. Disaster Report Details';
    $lines[] = '- Status totals (period): Pending ' . number_format((int) ($totals['reports_pending'] ?? 0))
        . ', Approved ' . number_format((int) ($totals['reports_approved'] ?? 0))
        . ', Rejected ' . number_format((int) ($totals['reports_rejected'] ?? 0));
    $lines[] = '- Top districts (snapshot):';
    foreach ((array) ($disasters['districts'] ?? []) as $row) {
        $lines[] = '  * ' . (string) ($row['label'] ?? '-') . ': ' . number_format((int) ($row['value'] ?? 0));
    }
    $lines[] = '- Top disaster types (snapshot):';
    foreach ((array) ($disasters['types'] ?? []) as $row) {
        $lines[] = '  * ' . (string) ($row['label'] ?? '-') . ': ' . number_format((int) ($row['value'] ?? 0));
    }
    $lines[] = '';

    $lines[] = '3. Volunteer Assignment Statistics';
    $lines[] = '- Task totals (period): Total ' . number_format((int) ($totals['tasks_total'] ?? 0))
        . ', Active ' . number_format((int) ($totals['tasks_active'] ?? 0))
        . ', Completed/Verified ' . number_format((int) ($totals['tasks_completed'] ?? 0))
        . ', Declined ' . number_format((int) ($totals['tasks_declined'] ?? 0));
    $lines[] = '- Task status mix (snapshot):';
    foreach ((array) ($volunteers['status'] ?? []) as $row) {
        $lines[] = '  * ' . (string) ($row['label'] ?? '-') . ': ' . number_format((int) ($row['value'] ?? 0));
    }
    $lines[] = '';

    $lines[] = '4. Donation and Requirement Breakdown';
    $lines[] = '- Donations (period): Pending ' . number_format((int) ($totals['donations_pending'] ?? 0))
        . ', Received ' . number_format((int) ($totals['donations_received'] ?? 0))
        . ', Cancelled ' . number_format((int) ($totals['donations_cancelled'] ?? 0))
        . ', Delivered ' . number_format((int) ($totals['donations_delivered'] ?? 0));
    $lines[] = '- Requirement status (period): Open ' . number_format((int) ($totals['requirements_open'] ?? 0))
        . ', Reserved ' . number_format((int) ($totals['requirements_reserved'] ?? 0))
        . ', Fulfilled ' . number_format((int) ($totals['requirements_fulfilled'] ?? 0));
    $lines[] = '- Requirement categories (snapshot):';
    foreach ((array) ($requirements['categories'] ?? []) as $row) {
        $lines[] = '  * ' . (string) ($row['label'] ?? '-') . ': ' . number_format((float) ($row['value'] ?? 0), 2);
    }
    $lines[] = '';

    $lines[] = '5. Shelter Capacity and Utilization';
    $shelterTotals = (array) ($shelters['totals'] ?? []);
    $lines[] = '- Safe locations: ' . number_format((int) ($shelterTotals['locations'] ?? 0));
    $lines[] = '- Occupancy/Capacity: ' . number_format((int) ($shelterTotals['occupancy'] ?? 0))
        . '/' . number_format((int) ($shelterTotals['capacity'] ?? 0))
        . ' (' . number_format((float) ($shelterTotals['utilization_pct'] ?? 0.0), 1) . '%)';
    $lines[] = '- High utilization locations (top 10):';
    $locations = (array) ($shelters['locations'] ?? []);
    usort(
        $locations,
        static fn(array $a, array $b): int => ((float) ($b['value'] ?? 0.0)) <=> ((float) ($a['value'] ?? 0.0))
    );
    foreach (array_slice($locations, 0, 10) as $row) {
        $lines[] = '  * ' . (string) ($row['label'] ?? '-')
            . ': ' . number_format((int) ($row['occupancy'] ?? 0))
            . '/' . number_format((int) ($row['capacity'] ?? 0))
            . ' (' . number_format((float) ($row['value'] ?? 0.0), 1) . '%, ' . (string) ($row['status'] ?? 'green') . ')';
    }
    $lines[] = '';

    $lines[] = '6. Inventory Status';
    $lines[] = '- Low/out stock count: ' . number_format((int) ($cards['low_stock_items'] ?? 0));
    $lines[] = '- Inventory by category (snapshot):';
    foreach ((array) ($donations['inventory_categories'] ?? []) as $row) {
        $lines[] = '  * ' . (string) ($row['label'] ?? '-') . ': ' . number_format((float) ($row['value'] ?? 0), 2);
    }
    $lines[] = '- Critical inventory rows (top 15 by lowest quantity):';
    $inventoryRows = (array) ($donations['low_stock_items'] ?? []);
    usort(
        $inventoryRows,
        static fn(array $a, array $b): int => ((int) ($a['quantity'] ?? 0)) <=> ((int) ($b['quantity'] ?? 0))
    );
    foreach (array_slice($inventoryRows, 0, 15) as $row) {
        $lines[] = '  * ' . (string) ($row['item_name'] ?? '-')
            . ' | ' . (string) ($row['category'] ?? '-')
            . ' | Point: ' . (string) ($row['collection_point'] ?? '-')
            . ' | Qty: ' . number_format((int) ($row['quantity'] ?? 0))
            . ' | Status: ' . (string) ($row['status'] ?? '-');
    }
    $lines[] = '';

    $lines[] = '7. Flood Alert Messaging';
    $lines[] = '- Active SMS subscribers: ' . number_format((int) ($cards['active_sms_subscribers'] ?? 0));
    $lines[] = '- Sent alerts (period): ' . number_format((int) ($totals['sms_sent_total'] ?? 0));
    $lines[] = '- Snapshot throughput: Today ' . number_format((int) ($sms['sent_today'] ?? 0))
        . ', This week ' . number_format((int) ($sms['sent_this_week'] ?? 0))
        . ', This month ' . number_format((int) ($sms['sent_this_month'] ?? 0));
    $lines[] = '- Top stations (snapshot):';
    foreach ((array) ($sms['stations'] ?? []) as $row) {
        $lines[] = '  * ' . (string) ($row['label'] ?? '-') . ': ' . number_format((int) ($row['value'] ?? 0));
    }
    $lines[] = '';

    $lines[] = '8. Account Management';
    $lines[] = '- Pending approvals: ' . number_format((int) ($cards['pending_approvals'] ?? 0));
    foreach ($accountByRole as $row) {
        $lines[] = '- ' . strtoupper((string) ($row['role'] ?? 'unknown'))
            . ': total ' . number_format((int) ($row['total_count'] ?? 0))
            . ', active ' . number_format((int) ($row['active_count'] ?? 0))
            . ', inactive ' . number_format((int) ($row['inactive_count'] ?? 0));
    }

    return $lines;
}

function dashboard_pdf_render_lines(array $lines): string
{
    $prepared = [];
    foreach ($lines as $line) {
        $normalized = dashboard_pdf_normalize_text((string) $line);
        if ($normalized === '') {
            $prepared[] = ' ';
            continue;
        }

        $wrapped = explode("\n", wordwrap($normalized, 100, "\n", true));
        foreach ($wrapped as $segment) {
            $prepared[] = $segment === '' ? ' ' : $segment;
        }
    }

    if (empty($prepared)) {
        $prepared[] = 'No report data available.';
    }

    $linesPerPage = 50;
    $pages = array_chunk($prepared, $linesPerPage);
    $totalPages = count($pages);
    $streams = [];

    foreach ($pages as $index => $pageLines) {
        $pageNumber = $index + 1;
        $stream = "BT\n/F1 10 Tf\n40 800 Td\n";

        $first = true;
        foreach ($pageLines as $line) {
            $escaped = dashboard_pdf_escape_text((string) $line);
            if ($first) {
                $stream .= '(' . $escaped . ") Tj\n";
                $first = false;
            } else {
                $stream .= "0 -14 Td\n(" . $escaped . ") Tj\n";
            }
        }

        $stream .= "ET\n";
        $stream .= "BT\n/F1 9 Tf\n470 20 Td\n(";
        $stream .= dashboard_pdf_escape_text('Page ' . $pageNumber . ' of ' . $totalPages);
        $stream .= ") Tj\nET\n";

        $streams[] = $stream;
    }

    return dashboard_pdf_build_document($streams);
}

function dashboard_pdf_escape_text(string $value): string
{
    $value = str_replace('\\', '\\\\', $value);
    $value = str_replace('(', '\\(', $value);
    $value = str_replace(')', '\\)', $value);
    $value = str_replace(["\r", "\n"], ' ', $value);

    return $value;
}

function dashboard_pdf_normalize_text(string $value): string
{
    $value = str_replace(["\r\n", "\r", "\t"], ["\n", "\n", '    '], $value);
    $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $value);
    if ($converted !== false) {
        $value = $converted;
    }

    return trim($value);
}

function dashboard_pdf_build_document(array $pageStreams): string
{
    if (empty($pageStreams)) {
        $pageStreams = ['BT\n/F1 10 Tf\n40 800 Td\n(No data) Tj\nET\n'];
    }

    $objects = [];
    $objects[] = ''; // 0 index placeholder
    $objects[] = '<< /Type /Catalog /Pages 2 0 R >>';
    $objects[] = ''; // Pages object placeholder
    $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';

    $kids = [];
    foreach ($pageStreams as $stream) {
        $pageId = count($objects);
        $objects[] = '';

        $contentId = count($objects);
        $objects[] = "<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "endstream";

        $kids[] = $pageId . ' 0 R';
        $objects[$pageId] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842]'
            . ' /Resources << /Font << /F1 3 0 R >> >> /Contents ' . $contentId . ' 0 R >>';
    }

    $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . count($kids) . ' >>';

    $pdf = "%PDF-1.4\n";
    $offsets = [0];

    $objectCount = count($objects) - 1;
    for ($i = 1; $i <= $objectCount; $i++) {
        $offsets[$i] = strlen($pdf);
        $pdf .= $i . " 0 obj\n" . $objects[$i] . "\nendobj\n";
    }

    $xrefOffset = strlen($pdf);
    $pdf .= 'xref' . "\n";
    $pdf .= '0 ' . ($objectCount + 1) . "\n";
    $pdf .= "0000000000 65535 f \n";

    for ($i = 1; $i <= $objectCount; $i++) {
        $pdf .= sprintf("%010d 00000 n \n", (int) ($offsets[$i] ?? 0));
    }

    $pdf .= "trailer\n<< /Size " . ($objectCount + 1) . " /Root 1 0 R >>\n";
    $pdf .= "startxref\n" . $xrefOffset . "\n%%EOF";

    return $pdf;
}
