<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor/autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR;
require_once __DIR__ . DIRECTORY_SEPARATOR;

use App\System\Application;

$app = new Application();
$app->db->applyMigrations();