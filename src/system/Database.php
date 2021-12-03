<?php

namespace App\System;

use PDO;

/**
 * Class Database
 * @package App\System\Database
 */
class Database
{
     /** @var PDO  */
    public $pdo;

    public function __construct()
    {
        $this->pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Apply migrations
     */
    public function applyMigrations()
    {
        $this->createMigrationsTable();
        $appliedMigrations = $this->getAppliedMigrations();
        $files = scandir(MIGRATION_PATH);
        $toApply = array_diff($files, $appliedMigrations);
        $migrationsToSave = [];
        foreach ($toApply as $migration) {
            if ($migration === '.' || $migration === '..' || $migration === '.gitkeep') {
                continue;
            }
            $className = pathinfo(MIGRATION_PATH . DIRECTORY_SEPARATOR . $migration, PATHINFO_FILENAME);
            require_once MIGRATION_PATH . DIRECTORY_SEPARATOR . $migration;
            $Migration = new $className();

            $this->log("Applying migrations $className");
            $Migration->up();
            $migrationsToSave[] = $migration;
            $this->log("Applied migration $className");
        }

        if (!empty($migrationsToSave)) {
            $this->saveMigrations($migrationsToSave);
        } else {
            $this->log("All migrations applied");
        }
    }

    /**
     * Migration Table
     */
    protected function createMigrationsTable()
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )  ENGINE=INNODB;");
    }

    /**
     * Fetch applied migrations
     */
    protected function getAppliedMigrations()
    {
        $statement = $this->pdo->prepare("SELECT migration FROM migrations");
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Save applied migrations
     * @param $migrations
     */
    protected function saveMigrations($migrations)
    {
        $values = implode(',', array_map(function ($m) { return "('$m')";}, $migrations));
        $statement = $this->pdo->prepare("INSERT INTO migrations (migration) VALUES " . $values);
        $statement->execute();
    }

    /**
     * Echo message
     * @param $message
     */
    protected function log($message)
    {
        echo '['.date('Y-m-d H:i:s').']' . '-' . $message . PHP_EOL;
    }
}