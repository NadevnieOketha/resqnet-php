<?php

/**
 * Mail configuration (Gmail SMTP defaults).
 */

return [
    'host' => env('MAIL_HOST', 'smtp.gmail.com'),
    'port' => (int) env('MAIL_PORT', 587),
    'encryption' => env('MAIL_ENCRYPTION', 'tls'),
    'username' => env('GMAIL_USERNAME', ''),
    'password' => env('GMAIL_APP_PASSWORD', ''),
    'from_address' => env('MAIL_FROM_ADDRESS', env('GMAIL_USERNAME', '')),
    'from_name' => env('MAIL_FROM_NAME', env('APP_NAME', 'resqnet')),
];
