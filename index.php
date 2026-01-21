<?php

declare(strict_types=1);

/**
 * FWP Image Gallery - Front Controller
 *
 * All requests are routed through this file.
 */

// Early install check - before loading anything else
$rootPath = __DIR__;
$installedLock = $rootPath . '/storage/installed.lock';
$databaseConfig = $rootPath . '/app/Config/database.php';
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

if (!file_exists($installedLock) || !file_exists($databaseConfig)) {
    // Not installed - redirect to installer unless already there
    if (!str_starts_with($requestPath, '/install')) {
        header('Location: /install');
        exit;
    }
}

// Bootstrap the application
$app = require_once __DIR__ . '/app/bootstrap.php';

// Load routes
$router = $app->getRouter();
require_once APP_PATH . '/Config/routes.php';

// Run the application
$app->run();
