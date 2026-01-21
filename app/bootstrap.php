<?php

declare(strict_types=1);

/**
 * Application Bootstrap
 *
 * Initializes the application environment, loads configuration,
 * sets up error handling, and returns the Application instance.
 */

// Define base paths
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', __DIR__);
define('PUBLIC_PATH', ROOT_PATH . '/public_html');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('CONFIG_PATH', APP_PATH . '/Config');
define('VIEWS_PATH', APP_PATH . '/Views');

// Load autoloader
require_once APP_PATH . '/autoload.php';

// Load helper functions
require_once APP_PATH . '/Helpers/helpers.php';

// Load configuration
$config = require CONFIG_PATH . '/config.php';

// Set timezone
date_default_timezone_set($config['app']['timezone'] ?? 'UTC');

// Configure error reporting based on environment
if ($config['app']['debug'] ?? false) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', '0');
}

// Set up error and exception handlers
set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function (Throwable $e): void {
    $config = require CONFIG_PATH . '/config.php';

    // Log the error
    $logMessage = sprintf(
        "[%s] %s: %s in %s:%d\nStack trace:\n%s\n",
        date('Y-m-d H:i:s'),
        get_class($e),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    );

    $logFile = STORAGE_PATH . '/logs/error.log';
    if (is_writable(dirname($logFile))) {
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }

    // Display error
    if ($config['app']['debug'] ?? false) {
        http_response_code(500);
        echo '<h1>Error</h1>';
        echo '<p><strong>' . htmlspecialchars(get_class($e)) . ':</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</p>';
        echo '<h2>Stack Trace</h2>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    } else {
        http_response_code(500);
        include VIEWS_PATH . '/errors/500.php';
    }
    exit(1);
});

// Start session with secure settings
if (session_status() === PHP_SESSION_NONE) {
    $sessionConfig = $config['session'] ?? [];

    session_set_cookie_params([
        'lifetime' => $sessionConfig['lifetime'] ?? 7200,
        'path' => '/',
        'domain' => '',
        'secure' => $sessionConfig['secure'] ?? false,
        'httponly' => $sessionConfig['httponly'] ?? true,
        'samesite' => $sessionConfig['samesite'] ?? 'Lax',
    ]);

    session_start();
}

// Check installation status and redirect if needed
$installedLockFile = STORAGE_PATH . '/installed.lock';
$databaseConfigFile = CONFIG_PATH . '/database.php';
$isInstalled = file_exists($installedLockFile) && file_exists($databaseConfigFile);
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$isInstallRoute = str_starts_with($requestPath, '/install');
$isAssetRoute = preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$/i', $requestPath);

// Redirect logic (skip for assets and CLI)
if (php_sapi_name() !== 'cli' && !$isAssetRoute) {
    if (!$isInstalled && !$isInstallRoute) {
        // Not installed and not on install route - redirect to installer
        if (!headers_sent()) {
            header('Location: /install');
            exit;
        }
    }

    if ($isInstalled && $isInstallRoute) {
        // Already installed and trying to access install route - redirect to home
        if (!headers_sent()) {
            header('Location: /');
            exit;
        }
    }
}

// Create and return Application instance
return new App\Core\Application($config);
