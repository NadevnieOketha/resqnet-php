<?php

/**
 * Database Layer
 * 
 * PDO-based database helpers. No ORM — just clean prepared statements.
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
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => (int) ($config['connect_timeout'] ?? 10),
        ];

        $sslMode = strtolower((string) ($config['ssl_mode'] ?? 'disable'));
        if ($sslMode !== '' && $sslMode !== 'disable') {
            if (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
                // Managed DB providers often use CA-signed certs; allow disabling strict verify when CA isn't configured.
                $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
            }

            $sslCa = (string) ($config['ssl_ca'] ?? '');
            if ($sslCa === '') {
                // Common CA bundle locations on macOS/Linux.
                $candidateCAs = [
                    '/etc/ssl/cert.pem',
                    '/etc/ssl/certs/ca-certificates.crt',
                    '/opt/homebrew/etc/openssl@3/cert.pem',
                ];

                foreach ($candidateCAs as $candidate) {
                    if (file_exists($candidate)) {
                        $sslCa = $candidate;
                        break;
                    }
                }
            }

            if ($sslCa !== '' && file_exists($sslCa) && defined('PDO::MYSQL_ATTR_SSL_CA')) {
                $options[PDO::MYSQL_ATTR_SSL_CA] = $sslCa;
            }

            $sslCert = (string) ($config['ssl_cert'] ?? '');
            if ($sslCert !== '' && file_exists($sslCert) && defined('PDO::MYSQL_ATTR_SSL_CERT')) {
                $options[PDO::MYSQL_ATTR_SSL_CERT] = $sslCert;
            }

            $sslKey = (string) ($config['ssl_key'] ?? '');
            if ($sslKey !== '' && file_exists($sslKey) && defined('PDO::MYSQL_ATTR_SSL_KEY')) {
                $options[PDO::MYSQL_ATTR_SSL_KEY] = $sslKey;
            }
        }

        try {
            $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        } catch (PDOException $e) {
            if (config('app.debug')) {
                $msg = 'Database connection failed: ' . $e->getMessage();
                if (str_contains($e->getMessage(), '2006')) {
                    $msg .= ' (Check DB host/port reachability and SSL settings in .env: DB_SSL_MODE/DB_SSL_CA.)';
                }
                die($msg);
            }
            die('Database connection failed.');
        }
    }

    return $pdo;
}

/**
 * Execute a query and return the PDOStatement.
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

        // Retry once with a fresh PDO instance when MySQL drops idle connections.
        $stmt = db_connect(true)->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}

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
 * Fetch a single row.
 */
function db_fetch(string $sql, array $params = []): ?array
{
    $result = db_query($sql, $params)->fetch();
    return $result ?: null;
}

/**
 * Fetch all rows.
 */
function db_fetch_all(string $sql, array $params = []): array
{
    return db_query($sql, $params)->fetchAll();
}

/**
 * Insert a row and return the last insert ID.
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
 * Update rows. $where is an assoc array of column => value for the WHERE clause.
 */
function db_update(string $table, array $data, array $where): int
{
    $setParts = [];
    $params   = [];

    foreach ($data as $col => $val) {
        $setParts[] = "{$col} = ?";
        $params[]   = $val;
    }

    $whereParts = [];
    foreach ($where as $col => $val) {
        $whereParts[] = "{$col} = ?";
        $params[]     = $val;
    }

    $sql = "UPDATE {$table} SET " . implode(', ', $setParts)
         . " WHERE " . implode(' AND ', $whereParts);

    return db_query($sql, $params)->rowCount();
}

/**
 * Delete rows. $where is an assoc array of column => value.
 */
function db_delete(string $table, array $where): int
{
    $whereParts = [];
    $params     = [];

    foreach ($where as $col => $val) {
        $whereParts[] = "{$col} = ?";
        $params[]     = $val;
    }

    $sql = "DELETE FROM {$table} WHERE " . implode(' AND ', $whereParts);
    return db_query($sql, $params)->rowCount();
}

/**
 * Count rows matching conditions.
 */
function db_count(string $table, array $where = []): int
{
    if (empty($where)) {
        return (int) db_fetch("SELECT COUNT(*) as cnt FROM {$table}")['cnt'];
    }

    $whereParts = [];
    $params     = [];
    foreach ($where as $col => $val) {
        $whereParts[] = "{$col} = ?";
        $params[]     = $val;
    }

    $sql = "SELECT COUNT(*) as cnt FROM {$table} WHERE " . implode(' AND ', $whereParts);
    return (int) db_fetch($sql, $params)['cnt'];
}
