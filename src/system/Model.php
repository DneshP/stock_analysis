<?php

namespace App\System;

use PDOStatement;
use stdClass;
use PDO;

/**
 * Class Model
 * @package App\System\Model
 */
abstract class Model
{
    protected $db;

    public function __construct()
    {
        $this->db = Application::$app->db;
    }

    abstract public function tableName();

    abstract public function columns();

    public function fetch($filter, $mode = PDO::FETCH_OBJ)
    {
        $select = '*';
        $where = '';
        $orderBy = '';
        if (!empty($filter->select)) {
            $select = implode(',', $filter->select);
        }
        if (!empty($filter->where)) {
            $count = count($filter->where) - 1;
            foreach ($filter->where as $whereKey => $column) {
                $key = array_keys($column)[0];
                $data = $column[$key];
                $operation = $data[0];
                $value = $data[1];
                $where .= " $key $operation '" . $value . "' ";
                if ($whereKey < $count) {
                    $where .= ' AND ';
                }
            }
        } else {
            $where = 'TRUE';
        }
        if (!empty($filter->orderBy)) {
            $count = count($filter->orderBy) - 1;
            foreach ($filter->orderBy as $key => $condition) {
                $orderBy .= " $condition ";
                if ($key < $count) {
                    $orderBy .= ' AND ';
                }
            }
        }

        $tableName = $this->tableName();
        $SQL = "SELECT $select FROM $tableName WHERE $where $orderBy ";
        try {
            $statement = $this->prepare($SQL);
            $statement->execute();
            return $statement->fetchAll($mode);
        } catch (\Exception $exception) {
            $this->log($exception);
            return false;
        }
    }

    /**
     * @param $SQL
     * @return bool
     */
    public function persist($SQL): bool
    {
        return $this->prepare($SQL)->execute();
    }

    /**
     * @param $sql
     * @return false|PDOStatement
     */
    protected function prepare($sql)
    {
        return $this->db->pdo->prepare($sql);
    }

    /**
     * Truncate table
     */
    public function fresh()
    {
        $tableName = $this->tableName();
        $SQL = "TRUNCATE TABLE $tableName";
        $this->db->pdo->exec($SQL);
    }

    /**
     * Select = [column1, column2]
     * Where = [[key => [operation, value, unquote]], [key => [operation,value, unquote]]
     * where condition applied as where key >= 'value'; by default
     * if unquote false where key >= value
     */
    protected function filterParams(): stdClass
    {
        $params = new stdClass();
        $params->select = [];
        $params->where = [];
        $params->orderBy = [];
        return $params;
    }

    /**
     * Log Errors
     * @param $message
     */
    protected function log($message)
    {
        $error_log = RUNTIME_PATH . DIRECTORY_SEPARATOR . 'log.txt';
        $log = '['.date('Y-m-d H:i:s').']' . '-' . $message . PHP_EOL;
        file_put_contents($error_log, $log, FILE_APPEND);
    }
}