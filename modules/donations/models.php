<?php

/**
 * Donations Module — Models
 */

function donations_requests(?string $status = null): array
{
    $sql = 'SELECT dr.*, c.name AS creator_name, ngo.name AS ngo_name
            FROM donation_requests dr
            LEFT JOIN users c ON c.id = dr.created_by
            LEFT JOIN users ngo ON ngo.id = dr.assigned_ngo';

    $params = [];
    if ($status !== null) {
        $sql .= ' WHERE dr.status = ?';
        $params[] = $status;
    }

    $sql .= ' ORDER BY dr.created_at DESC';

    return db_fetch_all($sql, $params);
}

function donations_recent_requests(int $limit = 6, ?string $status = null): array
{
    $sql = 'SELECT dr.*, c.name AS creator_name, ngo.name AS ngo_name
            FROM donation_requests dr
            LEFT JOIN users c ON c.id = dr.created_by
            LEFT JOIN users ngo ON ngo.id = dr.assigned_ngo';

    $params = [];
    if ($status !== null) {
        $sql .= ' WHERE dr.status = ?';
        $params[] = $status;
    }

    $sql .= ' ORDER BY dr.created_at DESC LIMIT ' . (int) max(1, $limit);

    return db_fetch_all($sql, $params);
}

function donations_recent_requests_for_ngo(int $ngoUserId, int $limit = 8): array
{
    $sql = 'SELECT dr.*, c.name AS creator_name, ngo.name AS ngo_name
            FROM donation_requests dr
            LEFT JOIN users c ON c.id = dr.created_by
            LEFT JOIN users ngo ON ngo.id = dr.assigned_ngo
            WHERE dr.assigned_ngo = ? OR dr.created_by = ?
            ORDER BY dr.created_at DESC
            LIMIT ' . (int) max(1, $limit);

    return db_fetch_all($sql, [$ngoUserId, $ngoUserId]);
}

function donations_manage_requests(?int $ngoUserId = null): array
{
    if ($ngoUserId === null) {
        return donations_requests();
    }

    return db_fetch_all(
        'SELECT dr.*, c.name AS creator_name, ngo.name AS ngo_name
         FROM donation_requests dr
         LEFT JOIN users c ON c.id = dr.created_by
         LEFT JOIN users ngo ON ngo.id = dr.assigned_ngo
         WHERE dr.assigned_ngo = ? OR dr.created_by = ?
         ORDER BY dr.created_at DESC',
        [$ngoUserId, $ngoUserId]
    );
}

function donations_find_request(int $id): ?array
{
    return db_fetch(
        'SELECT dr.*, c.name AS creator_name, ngo.name AS ngo_name
         FROM donation_requests dr
         LEFT JOIN users c ON c.id = dr.created_by
         LEFT JOIN users ngo ON ngo.id = dr.assigned_ngo
         WHERE dr.id = ?',
        [$id]
    );
}

function donations_create_request(array $data): string
{
    return db_insert('donation_requests', [
        'title' => $data['title'],
        'description' => $data['description'],
        'needed_location' => $data['needed_location'],
        'target_amount' => (float) $data['target_amount'],
        'collected_amount' => (float) ($data['collected_amount'] ?? 0),
        'status' => $data['status'] ?? 'open',
        'created_by' => $data['created_by'] ?? auth_id(),
        'assigned_ngo' => $data['assigned_ngo'] ?? null,
    ]);
}

function donations_update_request(int $id, array $data): int
{
    return db_update('donation_requests', [
        'title' => $data['title'],
        'description' => $data['description'],
        'needed_location' => $data['needed_location'],
        'target_amount' => (float) $data['target_amount'],
        'status' => $data['status'] ?? 'open',
        'assigned_ngo' => $data['assigned_ngo'] ?? null,
    ], ['id' => $id]);
}

function donations_delete_request(int $id): int
{
    return db_delete('donation_requests', ['id' => $id]);
}

function donations_request_contributions(int $requestId, int $limit = 20): array
{
    return db_fetch_all(
        'SELECT d.*, u.name AS donor_user_name
         FROM donations d
         LEFT JOIN users u ON u.id = d.donor_id
         WHERE d.donation_request_id = ?
         ORDER BY d.created_at DESC
         LIMIT ' . (int) max(1, $limit),
        [$requestId]
    );
}

function donations_add_contribution(int $requestId, array $data): string
{
    $donationId = db_insert('donations', [
        'donation_request_id' => $requestId,
        'donor_id' => $data['donor_id'] ?? null,
        'donor_name' => $data['donor_name'],
        'donor_email' => $data['donor_email'] ?? null,
        'amount' => (float) $data['amount'],
        'message' => $data['message'] ?? '',
    ]);

    $totals = db_fetch('SELECT COALESCE(SUM(amount), 0) AS total FROM donations WHERE donation_request_id = ?', [$requestId]);
    $newTotal = (float) ($totals['total'] ?? 0);

    $request = donations_find_request($requestId);
    if ($request) {
        $status = $request['status'];
        if ($newTotal >= (float) $request['target_amount'] && $status === 'open') {
            $status = 'fulfilled';
        }

        db_update('donation_requests', [
            'collected_amount' => $newTotal,
            'status' => $status,
        ], ['id' => $requestId]);
    }

    return $donationId;
}

function donations_count_requests_for_ngo(int $ngoUserId): int
{
    $row = db_fetch(
        'SELECT COUNT(*) AS cnt
         FROM donation_requests
         WHERE assigned_ngo = ? OR created_by = ?',
        [$ngoUserId, $ngoUserId]
    );

    return (int) ($row['cnt'] ?? 0);
}

function donations_total_for_ngo(int $ngoUserId): float
{
    $row = db_fetch(
        'SELECT COALESCE(SUM(d.amount), 0) AS total
         FROM donations d
         INNER JOIN donation_requests dr ON dr.id = d.donation_request_id
         WHERE dr.assigned_ngo = ? OR dr.created_by = ?',
        [$ngoUserId, $ngoUserId]
    );

    return (float) ($row['total'] ?? 0);
}

function donations_ngo_users(): array
{
    return db_fetch_all('SELECT id, name, email FROM users WHERE role = ? ORDER BY name ASC', ['ngo']);
}
