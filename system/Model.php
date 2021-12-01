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

    public function fetch($params)
    {
        $select = '*';
        $where = '';
        if (!empty($params->select)) {
            $select = implode(',', $params->select);
        }
        if (!empty($params->where)) {
            $count = count($params->where) - 1;
            foreach ($params->where as $whereKey => $column) {
                $key = array_keys($column)[0];
                $where .= $key . '=' . $column[$key];
                if ($whereKey < $count) {
                    $where .= 'AND';
                }
            }
        } else {
            $where = 'TRUE';
        }

        $tableName = $this->tableName();
        $SQL = "SELECT $select FROM $tableName WHERE $where";
        $statement = $this->prepare($SQL);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_COLUMN);
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
     * Select = [column1, column2]
     * Where = [[key => value], [key => value]]
     */
    protected function fetchParams(): stdClass
    {
        $params = new stdClass();
        $params->select = [];
        $params->where = [];
        return $params;
    }
}