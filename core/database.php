<?php

/**
 * Database Layer
 *
 * PDO-based database helpers.
 */

function db_connect(bool $forceReconnect = false): PDO
{
    static $pdo = null;

    if ($forceReconnect) {
        $pdo = null;
    }

    if ($pdo === null) {
        $config = require BASE_PATH . '/config/database.php';

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => (int) ($config['connect_timeout'] ?? 10),
        ];

        // ✅ SSL FIX (PHP 8.5 compatible)
        $sslMode = strtolower((string) ($config['ssl_mode'] ?? 'disable'));

        if ($sslMode !== '' && $sslMode !== 'disable') {

            // Use new constants if available, else fallback
            $sslVerifyConst = class_exists('Pdo\\Mysql') && defined('Pdo\\Mysql::ATTR_SSL_VERIFY_SERVER_CERT')
                ? Pdo\Mysql::ATTR_SSL_VERIFY_SERVER_CERT
                : (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT') ? PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT : null);

            $sslCaConst = class_exists('Pdo\\Mysql') && defined('Pdo\\Mysql::ATTR_SSL_CA')
                ? Pdo\Mysql::ATTR_SSL_CA
                : (defined('PDO::MYSQL_ATTR_SSL_CA') ? PDO::MYSQL_ATTR_SSL_CA : null);

            // Disable strict verification (safe for DO managed DB)
            if ($sslVerifyConst !== null) {
                $options[$sslVerifyConst] = false;
            }

            // Load CA file if exists
            $sslCa = (string) ($config['ssl_ca'] ?? '');

            if ($sslCa !== '' && file_exists($sslCa) && $sslCaConst !== null) {
                $options[$sslCaConst] = $sslCa;
            }
        }

        try {
            $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    return $pdo;
}

/**
 * Execute query
 */
function db_query(string $sql, array $params = []): PDOStatement
{
    $pdo = null;

    try {
        $pdo = db_connect();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        if (!db_is_connection_lost($e) || ($pdo instanceof PDO && $pdo->inTransaction())) {
            throw $e;
        }

        $stmt = db_connect(true)->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}

/**
 * Detect lost connection
 */
function db_is_connection_lost(PDOException $e): bool
{
    $code = (string) $e->getCode();
    $message = strtolower($e->getMessage());

    return $code === '2006'
        || $code === '2013'
        || str_contains($message, 'mysql server has gone away')
        || str_contains($message, 'lost connection to mysql server');
}

/**
 * Fetch single row
 */
function db_fetch(string $sql, array $params = []): ?array
{
    $result = db_query($sql, $params)->fetch();
    return $result ?: null;
}

/**
 * Fetch all rows
 */
function db_fetch_all(string $sql, array $params = []): array
{
    return db_query($sql, $params)->fetchAll();
}

/**
 * Insert
 */
function db_insert(string $table, array $data): string
{
    $columns = implode(', ', array_keys($data));
    $placeholders = implode(', ', array_fill(0, count($data), '?'));

    $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
    db_query($sql, array_values($data));

    return db_connect()->lastInsertId();
}

/**
 * Update
 */
function db_update(string $table, array $data, array $where): int
{
    $setParts = [];
    $params = [];

    foreach ($data as $col => $val) {
        $setParts[] = "{$col} = ?";
        $params[] = $val;
    }

    $whereParts = [];
    foreach ($where as $col => $val) {
        $whereParts[] = "{$col} = ?";
        $params[] = $val;
    }

    $sql = "UPDATE {$table} SET " . implode(', ', $setParts)
         . " WHERE " . implode(' AND ', $whereParts);

    return db_query($sql, $params)->rowCount();
}

/**
 * Delete
 */
function db_delete(string $table, array $where): int
{
    $whereParts = [];
    $params = [];

    foreach ($where as $col => $val) {
        $whereParts[] = "{$col} = ?";
        $params[] = $val;
    }

    $sql = "DELETE FROM {$table} WHERE " . implode(' AND ', $whereParts);
    return db_query($sql, $params)->rowCount();
}

/**
 * Count
 */
function db_count(string $table, array $where = []): int
{
    if (empty($where)) {
        return (int) db_fetch("SELECT COUNT(*) AS cnt FROM {$table}")['cnt'];
    }

    $whereParts = [];
    $params = [];

    foreach ($where as $col => $val) {
        $whereParts[] = "{$col} = ?";
        $params[] = $val;
    }

    $sql = "SELECT COUNT(*) AS cnt FROM {$table} WHERE " . implode(' AND ', $whereParts);
    return (int) db_fetch($sql, $params)['cnt'];
}