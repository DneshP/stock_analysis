<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'src/vendor/autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'src/system/Config.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'src/config/Config.php';

use App\Routes\Routes;
use App\System\Application;

$app = new Application();
$routes = new Routes($app);
$routes->init();

$app->run();