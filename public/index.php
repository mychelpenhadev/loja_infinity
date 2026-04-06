<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

if (!file_exists(__DIR__ . '/../storage/database.sqlite')) {
    if (!is_dir(__DIR__ . '/../storage')) mkdir(__DIR__ . '/../storage', 0755, true);
    touch(__DIR__ . '/../storage/database.sqlite');
    chmod(__DIR__ . '/../storage/database.sqlite', 0666);
}

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__ . '/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->handleRequest(Request::capture());
