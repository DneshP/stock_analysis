<?php


use App\System\Application;
use App\System\Database;

class m0001_stock_data
{

    /**
     * Build
     */
    public function up()
    {
        $SQL = "CREATE TABLE stock_data (
                id_no INT AUTO_INCREMENT PRIMARY KEY,
                date DATE,
                stock_name VARCHAR(255),
                price FLOAT
            )  ENGINE=INNODB;";
        $this->db()->pdo->exec($SQL);
    }

    /**
     * Tear down
     */
    public  function down()
    {
        $SQL = "DROP TABLE stock_data IF EXISTS";
        $this->db()->pdo->exec($SQL);
    }

    /**
     * Database instance
     * @return Database
     */
    protected function db(): Database
    {
        return Application::$app->db;
    }
}