<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'src/vendor/autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'src/Config/Config.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'src/System/Config.php';

use App\Routes\Routes;
use App\System\Application;

$app = new Application();
$routes = new Routes($app);
$routes->init();

$app->run();