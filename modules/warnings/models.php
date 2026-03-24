<?php

/**
 * Warnings Module — Models
 */

function warnings_all(?string $status = null): array
{
    $sql = 'SELECT w.*, u.name AS issuer_name FROM warnings w LEFT JOIN users u ON u.id = w.issued_by';
    $params = [];

    if ($status !== null) {
        $sql .= ' WHERE w.status = ?';
        $params[] = $status;
    }

    $sql .= ' ORDER BY COALESCE(w.issued_at, w.created_at) DESC';

    return db_fetch_all($sql, $params);
}

function warnings_recent(int $limit = 6, ?int $issuerId = null, ?string $status = null): array
{
    $sql = 'SELECT w.*, u.name AS issuer_name FROM warnings w LEFT JOIN users u ON u.id = w.issued_by';
    $where = [];
    $params = [];

    if ($issuerId !== null) {
        $where[] = 'w.issued_by = ?';
        $params[] = $issuerId;
    }

    if ($status !== null) {
        $where[] = 'w.status = ?';
        $params[] = $status;
    }

    if (!empty($where)) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $sql .= ' ORDER BY COALESCE(w.issued_at, w.created_at) DESC LIMIT ' . (int) max(1, $limit);

    return db_fetch_all($sql, $params);
}

function warnings_find(int $id): ?array
{
    return db_fetch(
        'SELECT w.*, u.name AS issuer_name FROM warnings w LEFT JOIN users u ON u.id = w.issued_by WHERE w.id = ?',
        [$id]
    );
}

function warnings_create(array $data): string
{
    return db_insert('warnings', [
        'title' => $data['title'],
        'message' => $data['message'],
        'location' => $data['location'],
        'severity' => $data['severity'] ?? 'medium',
        'status' => $data['status'] ?? 'draft',
        'issued_by' => $data['issued_by'] ?? auth_id(),
        'issued_at' => $data['issued_at'] ?? null,
    ]);
}

function warnings_update_data(int $id, array $data): int
{
    return db_update('warnings', [
        'title' => $data['title'],
        'message' => $data['message'],
        'location' => $data['location'],
        'severity' => $data['severity'] ?? 'medium',
        'status' => $data['status'] ?? 'draft',
        'issued_at' => $data['issued_at'] ?? null,
    ], ['id' => $id]);
}

function warnings_delete_by_id(int $id): int
{
    return db_delete('warnings', ['id' => $id]);
}
