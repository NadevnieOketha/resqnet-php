<?php

/**
 * Bootstrap
 * 
 * Loads environment, starts session, connects DB, and auto-includes
 * all module controllers and models.
 */

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Load core helpers
require BASE_PATH . '/core/helpers.php';

// Load .env (vanilla parser; no third-party dependency)
$envFile = BASE_PATH . '/.env';
if (is_file($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        if (strpos($line, '=') === false) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        if (str_starts_with($key, 'export ')) {
            $key = trim(substr($key, 7));
        }

        if (strlen($value) >= 2) {
            $first = $value[0];
            $last = $value[strlen($value) - 1];
            if (($first === '"' || $first === "'") && $first === $last) {
                $value = substr($value, 1, -1);
            }
        }

        $_ENV[$key] = $value;
        putenv($key . '=' . $value);
    }
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting based on debug mode
if (config('app.debug')) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Load core files
require BASE_PATH . '/core/database.php';
require BASE_PATH . '/core/router.php';
require BASE_PATH . '/core/middleware.php';
require BASE_PATH . '/core/mailer.php';

// Auto-include all module controllers and models
$modulesDir = BASE_PATH . '/modules';
if (is_dir($modulesDir)) {
    foreach (scandir($modulesDir) as $module) {
        if ($module === '.' || $module === '..') continue;

        $modulePath = $modulesDir . '/' . $module;
        if (!is_dir($modulePath)) continue;

        // Load models first (controllers may depend on them)
        $modelsFile = $modulePath . '/models.php';
        if (file_exists($modelsFile)) {
            require $modelsFile;
        }

        // Load controllers
        $controllersFile = $modulePath . '/controllers.php';
        if (file_exists($controllersFile)) {
            require $controllersFile;
        }
    }
}
