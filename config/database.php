<?php

/**
 * Database Configuration
 */

return [
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'resqnet'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'connect_timeout' => (int) env('DB_CONNECT_TIMEOUT', 10),
    'ssl_mode' => env('DB_SSL_MODE', 'disable'),
    'ssl_ca' => env('DB_SSL_CA', ''),
    'ssl_cert' => env('DB_SSL_CERT', ''),
    'ssl_key' => env('DB_SSL_KEY', ''),
];
