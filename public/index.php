<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . '/../vendor/autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '../system/Config.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '/../config/Config.php';

use App\Routes\Routes;
use App\System\Application;

$app = new Application();
$routes = new Routes($app);
$routes->init();

$app->run();