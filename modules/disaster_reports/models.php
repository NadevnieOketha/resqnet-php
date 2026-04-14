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

function disaster_reports_find_district_for_gn_division(string $gnDivision): string
{
    $target = trim($gnDivision);
    if ($target === '') {
        return '';
    }

    foreach (disaster_reports_district_map() as $district => $divisions) {
        foreach ((array) $divisions as $division) {
            if ((string) $division === $target) {
                return (string) $district;
            }
        }
    }

    return '';
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

function disaster_reports_list_gn_active_notifications(string $gnDivision): array
{
    $trimmed = trim($gnDivision);
    if ($trimmed === '') {
        return [];
    }

    return db_fetch_all(
        "SELECT dr.report_id,
                dr.disaster_type,
                dr.other_disaster_type,
                dr.gn_division,
                dr.location,
                dr.verified_at,
                COUNT(vt.id) AS total_tasks,
                SUM(CASE WHEN vt.status = 'Verified' THEN 1 ELSE 0 END) AS verified_tasks
         FROM disaster_reports dr
         LEFT JOIN volunteer_task vt ON vt.disaster_id = dr.report_id
         WHERE dr.status = 'Approved'
           AND dr.gn_division = ?
         GROUP BY dr.report_id,
                  dr.disaster_type,
                  dr.other_disaster_type,
                  dr.gn_division,
                  dr.location,
                  dr.verified_at
         HAVING NOT (
             COUNT(vt.id) > 0
             AND COUNT(vt.id) = SUM(CASE WHEN vt.status = 'Verified' THEN 1 ELSE 0 END)
         )
         ORDER BY dr.verified_at DESC, dr.report_id DESC",
        [$trimmed]
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

function disaster_reports_find_by_id(int $reportId): ?array
{
    return db_fetch(
        'SELECT report_id, reporter_name, contact_number, disaster_type, other_disaster_type,
                disaster_datetime, district, gn_division, location, description, status, submitted_at, verified_at
         FROM disaster_reports
         WHERE report_id = ?
         LIMIT 1',
        [$reportId]
    );
}

function disaster_reports_find_gn_contacts_for_division(string $gnDivision): array
{
    $trimmed = trim($gnDivision);
    if ($trimmed === '') {
        return [];
    }

    return db_fetch_all(
        "SELECT u.user_id, u.email, g.name, g.gn_division
         FROM grama_niladhari g
         INNER JOIN users u ON u.user_id = g.user_id
         WHERE u.role = 'grama_niladhari'
           AND u.active = 1
           AND g.gn_division = ?
         ORDER BY g.name ASC",
        [$trimmed]
    );
}

function disaster_reports_assigned_volunteer_count(int $reportId): int
{
    $row = db_fetch('SELECT COUNT(*) AS cnt FROM volunteer_task WHERE disaster_id = ?', [$reportId]);
    return (int) ($row['cnt'] ?? 0);
}

function disaster_reports_assignment_role_for_type(string $disasterType): string
{
    return match ($disasterType) {
        'Flood' => 'Flood Response',
        'Landslide' => 'Landslide Response',
        'Fire' => 'Fire Support',
        'Earthquake' => 'Urban Search & Rescue',
        'Tsunami' => 'Coastal Response',
        default => 'Disaster Response',
    };
}

function disaster_reports_priority_skills_for_type(string $disasterType): array
{
    return match ($disasterType) {
        'Flood' => ['Swimming / Lifesaving', 'Rescue & Handling', 'Search & Rescue', 'Disaster Management Training'],
        'Landslide' => ['Search & Rescue', 'Rescue & Handling', 'First Aid Certified', 'Disaster Management Training'],
        'Fire' => ['Firefighting', 'First Aid Certified', 'Medical Professional'],
        'Earthquake' => ['Search & Rescue', 'Medical Professional', 'First Aid Certified', 'Disaster Management Training'],
        'Tsunami' => ['Swimming / Lifesaving', 'Search & Rescue', 'Medical Professional'],
        default => [],
    };
}

function disaster_reports_fetch_assignment_candidates(string $district = ''): array
{
    $params = [];
    $districtSql = '';

    if (trim($district) !== '') {
        $districtSql = 'AND v.district = ?';
        $params[] = trim($district);
    }

    return db_fetch_all(
        "SELECT v.user_id,
                v.name,
                v.district,
                v.gn_division,
                u.email,
                (
                    SELECT COUNT(*)
                    FROM volunteer_task vt
                    WHERE vt.volunteer_id = v.user_id
                      AND vt.status IN ('Pending', 'Assigned', 'Accepted', 'In Progress')
                ) AS active_tasks,
                (
                    SELECT GROUP_CONCAT(DISTINCT s.skill_name SEPARATOR '||')
                    FROM skills_volunteers sv
                    INNER JOIN skills s ON s.skill_id = sv.skill_id
                    WHERE sv.user_id = v.user_id
                ) AS skills
         FROM volunteers v
         INNER JOIN users u ON u.user_id = v.user_id
         WHERE u.role = 'volunteer'
           AND u.active = 1
           {$districtSql}
         ORDER BY v.user_id ASC",
        $params
    );
}

function disaster_reports_assign_volunteers_to_report(int $reportId, int $minimumTotal = 5): array
{
    $report = disaster_reports_find_by_id($reportId);
    if (!$report) {
        return ['assigned' => [], 'message' => 'Disaster report not found.'];
    }

    if (($report['status'] ?? '') !== 'Approved') {
        return ['assigned' => [], 'message' => 'Only approved reports can be assigned.'];
    }

    $existing = db_fetch_all('SELECT volunteer_id FROM volunteer_task WHERE disaster_id = ?', [$reportId]);
    $existingIds = array_map(static fn(array $row): int => (int) ($row['volunteer_id'] ?? 0), $existing);
    $existingIds = array_values(array_filter($existingIds, static fn(int $id): bool => $id > 0));

    $minimumTotal = max(1, $minimumTotal);
    $existingCount = count($existingIds);
    $needed = $minimumTotal - $existingCount;
    if ($needed <= 0) {
        return [
            'assigned' => [],
            'message' => 'This report already has ' . $existingCount . ' volunteer(s) assigned.',
            'report' => $report,
            'total_assigned' => $existingCount,
            'required_minimum' => $minimumTotal,
        ];
    }

    $district = (string) ($report['district'] ?? '');
    $gnDivision = (string) ($report['gn_division'] ?? '');
    $candidates = disaster_reports_fetch_assignment_candidates($district);

    if (empty($candidates)) {
        $candidates = disaster_reports_fetch_assignment_candidates('');
    }

    $prioritySkills = disaster_reports_priority_skills_for_type((string) ($report['disaster_type'] ?? ''));
    $priorityMap = array_flip($prioritySkills);

    $scored = [];
    foreach ($candidates as $candidate) {
        $userId = (int) ($candidate['user_id'] ?? 0);
        if ($userId <= 0) {
            continue;
        }
        if (in_array($userId, $existingIds, true)) {
            continue;
        }

        $candidateSkills = (string) ($candidate['skills'] ?? '');
        $skillList = $candidateSkills !== '' ? explode('||', $candidateSkills) : [];

        $score = 0;
        if (($candidate['district'] ?? '') === $district) {
            $score += 2;
        }
        if (($candidate['gn_division'] ?? '') === $gnDivision) {
            $score += 3;
        }

        foreach ($skillList as $skill) {
            if (isset($priorityMap[$skill])) {
                $score += 2;
            }
        }

        $activeTasks = (int) ($candidate['active_tasks'] ?? 0);
        if ($activeTasks === 0) {
            $score += 1;
        }

        $candidate['score'] = $score;
        $candidate['active_tasks'] = $activeTasks;
        $scored[] = $candidate;
    }

    usort($scored, static function (array $a, array $b): int {
        $scoreCmp = ((int) ($b['score'] ?? 0)) <=> ((int) ($a['score'] ?? 0));
        if ($scoreCmp !== 0) {
            return $scoreCmp;
        }

        $loadCmp = ((int) ($a['active_tasks'] ?? 0)) <=> ((int) ($b['active_tasks'] ?? 0));
        if ($loadCmp !== 0) {
            return $loadCmp;
        }

        return ((int) ($a['user_id'] ?? 0)) <=> ((int) ($b['user_id'] ?? 0));
    });

    $selected = array_slice($scored, 0, $needed);
    if (empty($selected)) {
        return [
            'assigned' => [],
            'message' => 'No eligible volunteers were found for assignment.',
            'report' => $report,
            'total_assigned' => $existingCount,
            'required_minimum' => $minimumTotal,
        ];
    }

    $assignmentRole = disaster_reports_assignment_role_for_type((string) ($report['disaster_type'] ?? ''));
    $assigned = [];

    foreach ($selected as $candidate) {
        $volunteerId = (int) ($candidate['user_id'] ?? 0);
        if ($volunteerId <= 0) {
            continue;
        }

        db_query(
            'INSERT INTO volunteer_task (volunteer_id, disaster_id, role, date_assigned, status)
             VALUES (?, ?, ?, NOW(), ?)',
            [$volunteerId, $reportId, $assignmentRole, 'Assigned']
        );

        $assigned[] = [
            'user_id' => $volunteerId,
            'name' => (string) ($candidate['name'] ?? 'Volunteer'),
            'email' => (string) ($candidate['email'] ?? ''),
        ];
    }

    $totalAssigned = $existingCount + count($assigned);
    $message = count($assigned) . ' volunteer(s) assigned automatically.';
    if ($totalAssigned >= $minimumTotal) {
        $message .= ' Total assigned: ' . $totalAssigned . '.';
    } else {
        $remaining = $minimumTotal - $totalAssigned;
        $message .= ' Total assigned: ' . $totalAssigned . '. Still need ' . $remaining . ' more when volunteers become available.';
    }

    return [
        'assigned' => $assigned,
        'message' => $message,
        'report' => $report,
        'total_assigned' => $totalAssigned,
        'required_minimum' => $minimumTotal,
    ];
}

function disaster_reports_list_tasks_for_volunteer(int $volunteerId): array
{
    return db_fetch_all(
        "SELECT vt.id,
                vt.role,
                vt.status,
                vt.date_assigned,
                dr.report_id,
                dr.disaster_type,
                dr.other_disaster_type,
                dr.disaster_datetime,
                dr.district,
                dr.gn_division,
                dr.location,
                dr.description
         FROM volunteer_task vt
         INNER JOIN disaster_reports dr ON dr.report_id = vt.disaster_id
         WHERE vt.volunteer_id = ?
         ORDER BY vt.date_assigned DESC, vt.id DESC",
        [$volunteerId]
    );
}

function disaster_reports_list_assignments_by_report_ids(array $reportIds): array
{
    $ids = array_values(array_unique(array_map(
        static fn($id): int => (int) $id,
        $reportIds
    )));
    $ids = array_values(array_filter($ids, static fn(int $id): bool => $id > 0));

    if (empty($ids)) {
        return [];
    }

    $placeholders = implode(', ', array_fill(0, count($ids), '?'));
    $rows = db_fetch_all(
        "SELECT vt.id AS task_id,
                vt.disaster_id AS report_id,
                vt.volunteer_id,
                vt.status,
                vt.date_assigned,
                v.name AS volunteer_name
         FROM volunteer_task vt
         INNER JOIN volunteers v ON v.user_id = vt.volunteer_id
         WHERE vt.disaster_id IN ({$placeholders})
         ORDER BY vt.disaster_id ASC, vt.date_assigned ASC, vt.id ASC",
        $ids
    );

    $taskIds = array_map(static fn(array $row): int => (int) ($row['task_id'] ?? 0), $rows);
    $notesByTask = disaster_reports_list_notes_by_task_ids($taskIds);

    $grouped = [];
    foreach ($ids as $id) {
        $grouped[$id] = [];
    }

    foreach ($rows as $row) {
        $taskId = (int) ($row['task_id'] ?? 0);
        $reportId = (int) ($row['report_id'] ?? 0);
        if ($taskId <= 0 || $reportId <= 0) {
            continue;
        }

        $entry = [
            'task_id' => $taskId,
            'volunteer_id' => (int) ($row['volunteer_id'] ?? 0),
            'volunteer_name' => (string) ($row['volunteer_name'] ?? 'Volunteer'),
            'status' => (string) ($row['status'] ?? ''),
            'date_assigned' => (string) ($row['date_assigned'] ?? ''),
            'notes' => $notesByTask[$taskId] ?? [],
        ];

        if (!array_key_exists($reportId, $grouped)) {
            $grouped[$reportId] = [];
        }
        $grouped[$reportId][] = $entry;
    }

    return $grouped;
}

function disaster_reports_list_notes_by_task_ids(array $taskIds): array
{
    $ids = array_values(array_unique(array_map(
        static fn($id): int => (int) $id,
        $taskIds
    )));
    $ids = array_values(array_filter($ids, static fn(int $id): bool => $id > 0));

    if (empty($ids)) {
        return [];
    }

    if (!disaster_reports_table_exists('volunteer_field_updates')) {
        return [];
    }

    $hasStageStatus = disaster_reports_table_has_column('volunteer_field_updates', 'stage_status');
    $hasCreatedAt = disaster_reports_table_has_column('volunteer_field_updates', 'created_at');

    $stageExpr = $hasStageStatus ? 'stage_status' : "'' AS stage_status";
    $createdExpr = $hasCreatedAt ? 'created_at' : 'NULL AS created_at';
    $orderSql = $hasCreatedAt ? ' ORDER BY created_at ASC' : '';

    $placeholders = implode(', ', array_fill(0, count($ids), '?'));
    $rows = db_fetch_all(
        "SELECT task_id, {$stageExpr}, update_text, {$createdExpr}
         FROM volunteer_field_updates
         WHERE task_id IN ({$placeholders}){$orderSql}",
        $ids
    );

    $grouped = [];
    foreach ($rows as $row) {
        $taskId = (int) ($row['task_id'] ?? 0);
        if ($taskId <= 0) {
            continue;
        }

        $grouped[$taskId][] = [
            'stage_status' => trim((string) ($row['stage_status'] ?? '')),
            'update_text' => trim((string) ($row['update_text'] ?? '')),
            'created_at' => (string) ($row['created_at'] ?? ''),
        ];
    }

    return $grouped;
}

function disaster_reports_task_status_counters(array $rows): array
{
    $counters = [
        'pending' => 0,
        'assigned' => 0,
        'accepted' => 0,
        'in_progress' => 0,
        'completed' => 0,
        'verified' => 0,
        'declined' => 0,
    ];

    foreach ($rows as $row) {
        $status = strtolower((string) ($row['status'] ?? ''));
        $key = str_replace(' ', '_', $status);
        if (array_key_exists($key, $counters)) {
            $counters[$key]++;
        }
    }

    return $counters;
}

function disaster_reports_update_volunteer_task_status(int $taskId, int $volunteerId, string $nextStatus): array
{
    $task = db_fetch(
        'SELECT id, status FROM volunteer_task WHERE id = ? AND volunteer_id = ? LIMIT 1',
        [$taskId, $volunteerId]
    );

    if (!$task) {
        return ['ok' => false, 'message' => 'Task not found.'];
    }

    $current = (string) ($task['status'] ?? '');
    $transitions = [
        'Pending' => ['Assigned', 'Accepted', 'Declined'],
        'Assigned' => ['Accepted', 'Declined'],
        'Accepted' => ['In Progress', 'Declined'],
        'In Progress' => ['Completed'],
        'Completed' => [],
        'Verified' => [],
        'Declined' => ['Assigned'],
    ];

    $allowed = $transitions[$current] ?? [];
    if (!in_array($nextStatus, $allowed, true)) {
        return ['ok' => false, 'message' => 'Invalid task status transition.'];
    }

    $affected = db_query(
        'UPDATE volunteer_task SET status = ? WHERE id = ? AND volunteer_id = ? LIMIT 1',
        [$nextStatus, $taskId, $volunteerId]
    )->rowCount();

    if ($affected <= 0) {
        return ['ok' => false, 'message' => 'Unable to update task status.'];
    }

    return ['ok' => true, 'message' => 'Task status updated to ' . $nextStatus . '.'];
}

function disaster_reports_list_all_tasks_for_dmc(?string $statusFilter = null): array
{
    $params = [];
    $filterSql = '';

    if ($statusFilter !== null && trim($statusFilter) !== '') {
        $filterSql = 'WHERE vt.status = ?';
        $params[] = trim($statusFilter);
    }

    return db_fetch_all(
        "SELECT vt.id,
                vt.role,
                vt.status,
                vt.date_assigned,
                vt.volunteer_id,
                v.name AS volunteer_name,
                v.district AS volunteer_district,
                dr.report_id,
                dr.disaster_type,
                dr.other_disaster_type,
                dr.disaster_datetime,
                dr.district,
                dr.gn_division,
                dr.location
         FROM volunteer_task vt
         INNER JOIN volunteers v ON v.user_id = vt.volunteer_id
         INNER JOIN disaster_reports dr ON dr.report_id = vt.disaster_id
         {$filterSql}
         ORDER BY vt.date_assigned DESC, vt.id DESC",
        $params
    );
}

function disaster_reports_list_active_volunteers(?string $district = null): array
{
    $params = [];
    $districtSql = '';

    if ($district !== null && trim($district) !== '') {
        $districtSql = 'AND v.district = ?';
        $params[] = trim($district);
    }

    return db_fetch_all(
        "SELECT v.user_id, v.name, v.district, v.gn_division
         FROM volunteers v
         INNER JOIN users u ON u.user_id = v.user_id
         WHERE u.role = 'volunteer'
           AND u.active = 1
           {$districtSql}
         ORDER BY v.name ASC",
        $params
    );
}

function disaster_reports_reassign_task(int $taskId, int $newVolunteerId): array
{
    $task = db_fetch(
        'SELECT vt.id, vt.disaster_id, dr.district
         FROM volunteer_task vt
         INNER JOIN disaster_reports dr ON dr.report_id = vt.disaster_id
         WHERE vt.id = ?
         LIMIT 1',
        [$taskId]
    );

    if (!$task) {
        return ['ok' => false, 'message' => 'Task not found.'];
    }

    $volunteer = db_fetch(
        "SELECT v.user_id
         FROM volunteers v
         INNER JOIN users u ON u.user_id = v.user_id
         WHERE v.user_id = ?
           AND u.role = 'volunteer'
           AND u.active = 1
         LIMIT 1",
        [$newVolunteerId]
    );

    if (!$volunteer) {
        return ['ok' => false, 'message' => 'Selected volunteer is not available.'];
    }

    db_query(
        'UPDATE volunteer_task SET volunteer_id = ?, status = ? WHERE id = ? LIMIT 1',
        [$newVolunteerId, 'Assigned', $taskId]
    );

    return ['ok' => true, 'message' => 'Task reassigned successfully.'];
}

function disaster_reports_verify_task_completion(int $taskId): array
{
    $affected = db_query(
        "UPDATE volunteer_task
         SET status = 'Verified'
         WHERE id = ?
           AND status = 'Completed'
         LIMIT 1",
        [$taskId]
    )->rowCount();

    if ($affected <= 0) {
        return ['ok' => false, 'message' => 'Only completed tasks can be verified.'];
    }

    return ['ok' => true, 'message' => 'Task marked as Verified.'];
}

function disaster_reports_table_exists(string $tableName, bool $forceRefresh = false): bool
{
    static $cache = [];

    if ($forceRefresh) {
        unset($cache[$tableName]);
    }

    if (array_key_exists($tableName, $cache)) {
        return $cache[$tableName];
    }

    $row = db_fetch(
        'SELECT COUNT(*) AS cnt
         FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = ?',
        [$tableName]
    );

    $exists = ((int) ($row['cnt'] ?? 0)) > 0;
    $cache[$tableName] = $exists;

    return $exists;
}

function disaster_reports_table_has_column(string $tableName, string $columnName, bool $forceRefresh = false): bool
{
    static $cache = [];

    $key = $tableName . '.' . $columnName;
    if ($forceRefresh) {
        unset($cache[$key]);
    }

    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }

    $row = db_fetch(
        'SELECT COUNT(*) AS cnt
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = ?
           AND COLUMN_NAME = ?',
        [$tableName, $columnName]
    );

    $exists = ((int) ($row['cnt'] ?? 0)) > 0;
    $cache[$key] = $exists;
    return $exists;
}

function disaster_reports_ensure_volunteer_updates_table(): bool
{
    if (!disaster_reports_table_exists('volunteer_field_updates')) {
        db_query(
            "CREATE TABLE IF NOT EXISTS volunteer_field_updates (
                id INT NOT NULL AUTO_INCREMENT,
                task_id INT NOT NULL,
                volunteer_id INT NOT NULL,
                stage_status VARCHAR(50) DEFAULT NULL,
                update_text TEXT NOT NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_vfu_task (task_id),
                KEY idx_vfu_volunteer (volunteer_id)
            ) ENGINE=InnoDB"
        );
    }

    return disaster_reports_table_exists('volunteer_field_updates', true);
}

function disaster_reports_log_field_update(int $taskId, int $volunteerId, string $stageStatus, string $note): void
{
    $trimmed = trim($note);
    if ($trimmed === '') {
        return;
    }

    if (!disaster_reports_ensure_volunteer_updates_table()) {
        return;
    }

    $cleanStatus = trim($stageStatus);
    $hasStageStatus = disaster_reports_table_has_column('volunteer_field_updates', 'stage_status', true);

    if ($hasStageStatus) {
        db_query(
            'INSERT INTO volunteer_field_updates (task_id, volunteer_id, stage_status, update_text)
             VALUES (?, ?, ?, ?)',
            [$taskId, $volunteerId, $cleanStatus !== '' ? $cleanStatus : null, $trimmed]
        );
        return;
    }

    db_query(
        'INSERT INTO volunteer_field_updates (task_id, volunteer_id, update_text)
         VALUES (?, ?, ?)',
        [$taskId, $volunteerId, $trimmed]
    );
}
