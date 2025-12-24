<?php

// use Illuminate\Foundation\Application;
// use Illuminate\Http\Request;

// define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
// if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
//     require $maintenance;
// }

// Register the Composer autoloader...
// require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
// /** @var Application $app */
// $app = require_once __DIR__.'/../bootstrap/app.php';

// $app->handleRequest(Request::capture());


use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

require_once __DIR__.'/../staff.app/app/helpers.php';

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../staff.app/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../staff.app/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../staff.app/bootstrap/app.php';

$app->handleRequest(Request::capture());
