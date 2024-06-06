<?php

namespace App\Core;

use Exception;
use PDO;

class Builder
{
    private $pdo;
    private $table;
    private $select = '*';
    private $where = [];
    private $orWhere = [];
    private $orderBy = [];
    private $limit;
    private $offset;
    private $query;

    public function __construct($pdo, $table)
    {
        $this->pdo = $pdo;
        $this->table = $table;
    }


    public function select(...$select)
    {
        $this->select = implode(', ', $select);
        return $this;
    }

    public function where(...$args)
    {
        $columnName = $args[0];
        $operator = isset($args[2]) ? $args[1] : '=';
        $value = isset($args[2]) ? $args[2] : $args[1];


        $this->where[$columnName] = $operator;
        $this->query['whereValues'][] = $value;

        return $this;
    }

    public function orWhere(...$args)
    {
        $columnName = $args[0];
        $operator = isset($args[2]) ? $args[1] : '=';
        $value = isset($args[2]) ? $args[2] : $args[1];

        $this->orWhere[$columnName] = $operator;
        $this->query['orWhereValues'][] = $value;

        return $this;
    }

    public function orderBy($columnName, $orderType = 'asc')
    {
        $this->orderBy[$columnName] = $orderType;
        return $this;
    }

    public function orderByAsc($columnName)
    {
        $this->orderBy[$columnName] = 'asc';
        return $this;
    }

    public function orderByDesc($columnName)
    {
        $this->orderBy[$columnName] = 'desc';
        return $this;
    }

    public function limit($limit = 1)
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset($offset = 0)
    {
        $this->offset = $offset;
        return $this;
    }

    public function find($id)
    {
        return $this->where('id', $id)->first();
    }

    public function first()
    {
        $this->setLimit();
        $sql =  $this->getQuery();
        $sql->execute();
        return $sql->fetch(PDO::FETCH_OBJ);
    }

    public function get()
    {
        $sql =  $this->getQuery();
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_OBJ);
    }

    public function value($columnName)
    {
        $result = $this->first();
        return $result->$columnName;
    }

    public function pluck(...$args)
    {
        if (isset($args[2])) {
            throw new Exception('Max two params needed');
        }
        if (isset($args[1])) {
            return array_column($this->get(), $args[0], $args[1]);
        } else {
            return array_column($this->get(), $args[0]);
        }
    }

    public function toSql()
    {
        $sql = $this->getQuery();
        return $sql->queryString;
    }

    private function getQuery()
    {
        $this->setQuery();
        $sql = $this->pdo->prepare($this->query['sql']);
        $sql = $this->bindValues($sql);
        return $sql;
    }

    private function bindValues($sql)
    {
        $this->query['values'] = array_merge($this->query['whereValues'] ?? [], $this->query['orWhereValues'] ?? []);
        foreach ($this->query['values'] as $key => $value) {
            // PDO parameters are 1-indexed, so add 1 to $key
            $sql->bindValue($key + 1, $value);
        }
        return $sql;
    }

    private function setQuery()
    {
        $whereQuery = count($this->where) ? $this->getWhereQuery() : '';
        $orWhereQuery = (count($this->where) && count($this->orWhere)) ? $this->getOrWhereQuery() : '';
        $orderByQuery = count($this->orderBy) ? $this->getOrderByQuery() : '';
        $limitQuery = isset($this->limit) ? sprintf('LIMIT %s', $this->limit) : '';
        $offsetQuery = isset($this->offset) ? sprintf('OFFSET %s', $this->offset) : '';

        $query = sprintf('SELECT %s FROM %s %s %s %s %s %s', $this->select, $this->table, $whereQuery, $orWhereQuery, $orderByQuery, $limitQuery, $offsetQuery);
        $this->query['sql'] = $query;
    }

    private function getWhereQuery()
    {
        $count = count($this->where);
        $whereQuery =  '';

        if (!$count) {
            return $whereQuery;
        } else if ($count == 1) {
            $keys = array_keys($this->where);
            $values = array_values($this->where);
            $columnName = $keys[0];
            $operator = $values[0];
            $whereQuery = sprintf('WHERE %s %s ?', $columnName, $operator);
        } else {
            $whereQuery = 'WHERE ( ';
            foreach ($this->where ?? [] as $key => $val) {
                if (--$count == 0) {
                    $temp = sprintf('%s %s ? ) ', $key, $val);
                    $whereQuery .= $temp;
                } else {
                    $temp = sprintf('%s %s ? AND ', $key, $val);
                    $whereQuery .= $temp;
                }
            }
        }

        return $whereQuery;
    }

    private function getOrWhereQuery()
    {
        $count = count($this->orWhere);
        $orWhereQuery =  count($this->where) ? ' OR ' : '';

        if (!$count) {
            return $orWhereQuery;
        } else if ($count == 1) {
            $keys = array_keys($this->orWhere);
            $values = array_values($this->orWhere);
            $columnName = $keys[0];
            $operator = $values[0];
            $orWhereQuery = sprintf('%s %s ?', $columnName, $operator);
        } else {
            $orWhereQuery = ' OR ( ';
            foreach ($this->orWhere ?? [] as $key => $val) {
                if (--$count == 0) {
                    $temp = sprintf('%s %s ? ) ', $key, $val);
                    $orWhereQuery .= $temp;
                } else {
                    $temp = sprintf('%s %s ? OR ', $key, $val);
                    $orWhereQuery .= $temp;
                }
            }
        }

        return $orWhereQuery;
    }

    private function getOrderByQuery()
    {
        $count = count($this->orderBy);
        $orderByQuery = $count ? ' Order By ' : '';
        if (!$count) {
            return $orderByQuery;
        } else if ($count == 1) {
            $keys = array_keys($this->orderBy);
            $values = array_values($this->orderBy);
            $columnName = $keys[0];
            $operator = $values[0];
            $orderByQuery = sprintf('%s %s', $columnName, $operator);
        } else {

            foreach ($this->orderBy ?? [] as $key => $val) {
                if (--$count == 0) {
                    $temp = sprintf('%s %s ', $key, $val);
                    $orderByQuery .= $temp;
                } else {
                    $temp = sprintf('%s %s, ', $key, $val);
                    $orderByQuery .= $temp;
                }
            }
        }

        return $orderByQuery;
    }

    private function setOffset($offset = 0)
    {
        $this->offset($offset);
    }

    private function setLimit($limit = 1)
    {
        $this->limit($limit);
    }
}
