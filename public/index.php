<?php

if (str_contains($_SERVER['REQUEST_URI'] ?? '', 'test-vars') || str_contains($_SERVER['HTTP_X_ORIGINAL_URL'] ?? '', 'test-vars')) {
    header('Content-Type: application/json');
    echo json_encode([
        'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? null,
        'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? null,
        'PATH_INFO' => $_SERVER['PATH_INFO'] ?? null,
        'PHP_SELF' => $_SERVER['PHP_SELF'] ?? null,
        'UNENCODED_URL' => $_SERVER['UNENCODED_URL'] ?? null,
        'HTTP_X_ORIGINAL_URL' => $_SERVER['HTTP_X_ORIGINAL_URL'] ?? null,
        'SERVER' => $_SERVER,
    ]);
    exit;
}

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

if (isset($_SERVER['HTTP_X_ORIGINAL_URL'])) {
    $_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_ORIGINAL_URL'];
}

$app->handleRequest(Request::capture());

