<?php
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

const BASE_URL = "http://localhost:8082";
const ASSETS_URL = BASE_URL . '/assets';
define("DB_USER", $_ENV['DB_USER']);
define("DB_PASSWORD", $_ENV['DB_PASSWORD']);
define("DB_DSN", $_ENV['DB_DSN']);