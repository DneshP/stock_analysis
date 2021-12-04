<?php

namespace App\Database;

require_once __DIR__ . DIRECTORY_SEPARATOR;

use App\Models\Welcome;
use PDO;

class StockDataSeed
{
    /** @var int  */
    public $minimiseLossData = 1;

    /** @var int  */
    public $zeroStockPrice = 2;

    /** @var PDO  */
    public $pdo;

    public function __construct()
    {
        $this->pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Truncate
     */
    private function fresh()
    {
        $tableName = Welcome::$TABLE_NAME;
        $SQL = "TRUNCATE TABLE $tableName";
        $this->pdo->exec($SQL);
    }

    /**
     * Seed with initial data to test
     */
    public function seedStockData($mode = 0)
    {
        $this->fresh();
        //Ideally we would have factories but to simplify
        if ($mode == $this->minimiseLossData) {
            $SQL = $this->minimiseLossData();
        } else if ($mode === $this->zeroStockPrice) {
            $SQL = $this->zeroStockPrice();
        } else {
            $SQL = $this->initialStockData();
        }
        $this->pdo->exec($SQL);
    }

    /**
     * SQL with zero stock price
     * @return string
     */
    private function zeroStockPrice(): string
    {
        return "INSERT INTO stock_data (`date`, `stock_name`, `price`) VALUES
                    ('2020-02-11', 'aapl', 320),
                    ('2020-02-11', 'googl', 1400),
                    ('2020-02-11', 'msft', 185),
                    ('2020-02-12', 'googl', 0),
                    ('2020-02-12', 'msft', 184),
                    ('2020-02-13', 'aapl', 324),
                    ('2020-02-14', 'googl', 1520),
                    ('2020-02-15', 'aapl', 319),
                    ('2020-02-15', 'googl', 1523),
                    ('2020-02-15', 'msft', 189),
                    ('2020-02-16', 'googl', 1530),
                    ('2020-02-18', 'aapl', 319),
                    ('2020-02-18', 'msft', 187),
                    ('2020-02-19', 'aapl', 323),
                    ('2020-02-21', 'aapl', 313),
                    ('2020-02-21', 'googl', 1483),
                    ('2020-02-21', 'msft', 178),
                    ('2020-02-22', 'googl', 1485),
                    ('2020-02-22', 'msft', 180),
                    ('2020-02-23', 'aapl', 320)";
    }

    /**
     * SQL initial data
     * @return string
     */
    private function initialStockData(): string
    {
        return "INSERT INTO stock_data (`date`, `stock_name`, `price`) VALUES
                    ('2020-02-11', 'aapl', 320),
                    ('2020-02-11', 'googl', 1510),
                    ('2020-02-11', 'msft', 185),
                    ('2020-02-12', 'googl', 1518),
                    ('2020-02-12', 'msft', 184),
                    ('2020-02-13', 'aapl', 324),
                    ('2020-02-14', 'googl', 1520),
                    ('2020-02-15', 'aapl', 319),
                    ('2020-02-15', 'googl', 1523),
                    ('2020-02-15', 'msft', 189),
                    ('2020-02-16', 'googl', 1530),
                    ('2020-02-18', 'aapl', 319),
                    ('2020-02-18', 'msft', 187),
                    ('2020-02-19', 'aapl', 323),
                    ('2020-02-21', 'aapl', 313),
                    ('2020-02-21', 'googl', 1483),
                    ('2020-02-21', 'msft', 178),
                    ('2020-02-22', 'googl', 1485),
                    ('2020-02-22', 'msft', 180),
                    ('2020-02-23', 'aapl', 320)";
    }

    /**
     * Minimise Loss data
     * @return string
     */
    private function minimiseLossData(): string
    {
        return "INSERT INTO `stock_data` (`date`, `stock_name`, `price`) VALUES
                    ('2020-02-11', 'aapl', 320),
                    ('2020-02-11', 'googl', 1700),
                    ('2020-02-11', 'msft', 185),
                    ('2020-02-12', 'googl', 1699),
                    ('2020-02-12', 'msft', 184),
                    ('2020-02-13', 'aapl', 324),
                    ('2020-02-14', 'googl', 1520),
                    ('2020-02-15', 'aapl', 319),
                    ('2020-02-15', 'googl', 1467),
                    ('2020-02-15', 'msft', 189),
                    ('2020-02-16', 'googl', 1300),
                    ('2020-02-18', 'aapl', 319),
                    ('2020-02-18', 'msft', 187),
                    ('2020-02-19', 'aapl', 323),
                    ('2020-02-21', 'aapl', 313),
                    ('2020-02-21', 'googl', 1299),
                    ('2020-02-21', 'msft', 178),
                    ('2020-02-22', 'googl', 1200),
                    ('2020-02-22', 'msft', 180),
                    ('2020-02-23', 'aapl', 320)";
    }
}