<?php
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

define("BASE_URL", $_ENV['BASE_URL']);
const ASSETS_URL = BASE_URL . 'public/assets';
define("DB_USER", $_ENV['DB_USER']);
define("DB_PASSWORD", $_ENV['DB_PASSWORD']);
define("DB_DSN", $_ENV['DB_DSN']);