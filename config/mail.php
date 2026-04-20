<?php

/**
 * Mail configuration (Gmail SMTP defaults).
 */

return [
    'host' => env('MAIL_HOST', 'smtp.gmail.com'),
    'port' => (int) env('MAIL_PORT', 465),
    'encryption' => env('MAIL_ENCRYPTION', 'ssl'),
    'username' => env('MAIL_USERNAME', env('GMAIL_USERNAME', '')),
    'password' => env('MAIL_PASSWORD', env('GMAIL_APP_PASSWORD', '')),
    'from_address' => env('MAIL_FROM_ADDRESS', env('MAIL_USERNAME', env('GMAIL_USERNAME', ''))),
    'from_name' => env('MAIL_FROM_NAME', env('APP_NAME', 'resqnet')),
];
