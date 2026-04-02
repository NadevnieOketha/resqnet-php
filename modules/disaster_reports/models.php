<?php

/**
 * Disaster Reports Module - Models
 */

function disaster_reports_district_map(): array
{
    return (array) config('auth_options.gn_divisions', []);
}

function disaster_reports_district_list(): array
{
    return array_keys(disaster_reports_district_map());
}

function disaster_reports_insert(array $payload): int
{
    return (int) db_insert('disaster_reports', [
        'user_id' => (int) $payload['user_id'],
        'reporter_name' => (string) $payload['reporter_name'],
        'contact_number' => (string) $payload['contact_number'],
        'disaster_type' => (string) $payload['disaster_type'],
        'other_disaster_type' => $payload['other_disaster_type'] !== '' ? (string) $payload['other_disaster_type'] : null,
        'disaster_datetime' => (string) $payload['disaster_datetime'],
        'location' => $payload['location'] !== '' ? (string) $payload['location'] : null,
        'district' => (string) $payload['district'],
        'gn_division' => (string) $payload['gn_division'],
        'proof_image_path' => $payload['proof_image_path'] !== '' ? (string) $payload['proof_image_path'] : null,
        'confirmation' => 1,
        'status' => 'Pending',
        'description' => $payload['description'] !== '' ? (string) $payload['description'] : null,
    ]);
}

function disaster_reports_user_fk_target_table(): ?string
{
    static $cached = null;

    if ($cached !== null) {
        return $cached;
    }

    $row = db_fetch(
        "SELECT kcu.REFERENCED_TABLE_NAME AS ref_table
         FROM information_schema.KEY_COLUMN_USAGE kcu
         WHERE kcu.TABLE_SCHEMA = DATABASE()
           AND kcu.TABLE_NAME = 'disaster_reports'
           AND kcu.COLUMN_NAME = 'user_id'
           AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
         LIMIT 1"
    );

    $cached = $row['ref_table'] ?? null;
    return $cached;
}

function disaster_reports_ensure_reporter_relation(
    int $userId,
    string $reporterName,
    string $contactNumber,
    string $district,
    string $gnDivision
): void {
    $fkTarget = disaster_reports_user_fk_target_table();

    if ($fkTarget !== 'general_user') {
        return;
    }

    $exists = db_fetch('SELECT user_id FROM general_user WHERE user_id = ? LIMIT 1', [$userId]);
    if ($exists) {
        return;
    }

    db_query(
        'INSERT INTO general_user (user_id, name, contact_number, house_no, street, city, district, gn_division, sms_alert)
         VALUES (?, ?, ?, NULL, NULL, NULL, ?, ?, 0)',
        [$userId, $reporterName, $contactNumber !== '' ? $contactNumber : null, $district !== '' ? $district : null, $gnDivision !== '' ? $gnDivision : null]
    );
}

function disaster_reports_list_pending(): array
{
    return db_fetch_all(
        "SELECT report_id, reporter_name, contact_number, disaster_type, other_disaster_type,
                disaster_datetime, district, gn_division, location, description, submitted_at
         FROM disaster_reports
         WHERE status = 'Pending'
         ORDER BY submitted_at DESC, report_id DESC"
    );
}

function disaster_reports_list_approved(): array
{
    return db_fetch_all(
        "SELECT report_id, reporter_name, contact_number, disaster_type, other_disaster_type,
                disaster_datetime, district, gn_division, location, description, submitted_at, verified_at
         FROM disaster_reports
         WHERE status = 'Approved'
         ORDER BY verified_at DESC, report_id DESC"
    );
}

function disaster_reports_update_status(int $reportId, string $status): int
{
    if (!in_array($status, ['Approved', 'Rejected'], true)) {
        return 0;
    }

    return db_query(
        'UPDATE disaster_reports SET status = ?, verified_at = NOW() WHERE report_id = ? AND status = ? LIMIT 1',
        [$status, $reportId, 'Pending']
    )->rowCount();
}

function disaster_reports_disaster_label(array $report): string
{
    $type = (string) ($report['disaster_type'] ?? '');
    if ($type === 'Other') {
        $other = trim((string) ($report['other_disaster_type'] ?? ''));
        if ($other !== '') {
            return 'Other - ' . $other;
        }
    }

    return $type;
}
