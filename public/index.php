<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . '/../vendor/autoload.php';

use App\System\Application;

$app = new Application();

$app->run();