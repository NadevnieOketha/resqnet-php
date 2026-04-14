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
