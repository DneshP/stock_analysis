<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor/autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Config/Config.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'System/Config.php';

use App\System\Application;

$app = new Application();
$app->db->applyMigrations();